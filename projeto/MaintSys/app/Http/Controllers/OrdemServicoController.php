<?php

/**
 * CONTROLLER: OrdemServicoController
 *
 * Responsável pela gestão completa de Ordens de Serviço (O.S.) no sistema:
 * - Criar e listar ordens de manutenção
 * - Atualizar status de ordens (aberta, em andamento, concluída)
 * - Gerar históricos de manutenção automaticamente
 * - Sincronizar status de máquinas automaticamente
 * - Criar manutenções preventivas futuras automaticamente
 * - Exportar dados em CSV
 * - Notificar técnicos quando atribuídos
 *
 * Funcionalidades principais:
 * - Sistema de numeração automática (OS-YYYYMMDD-NNNN)
 * - Sincronização automática de status de máquina
 * - Criação de histórico ao concluir O.S.
 * - Geração automática de próxima preventiva
 * - Notificação de técnico quando O.S. é atribuída
 * - Exportação em CSV (individual e em lote)
 */

// Define o namespace do controller dentro da estrutura de controllers HTTP do Laravel
namespace App\Http\Controllers;

// Importa a classe Request do Laravel para lidar com dados da requisição HTTP
use Illuminate\Http\Request;
// Importa a facade DB para executar transações e queries diretas no banco
use Illuminate\Support\Facades\DB;
// Importa o model OrdemServico para interagir com a tabela de ordens de serviço
use App\Models\OrdemServico;
// Importa o model Maquina para interagir com a tabela de máquinas
use App\Models\Maquina;
// Importa o model Tecnico para interagir com a tabela de técnicos
use App\Models\Tecnico;
// Importa o model HistoricoManutencao para registrar históricos ao concluir O.S.
use App\Models\HistoricoManutencao;
// Importa a classe de notificação enviada ao técnico quando uma O.S. é atribuída
use App\Notifications\OrdemServicoAtribuida;

// Declara a classe OrdemServicoController herdando de Controller (base do Laravel)
class OrdemServicoController extends Controller
{
    /**
     * FUNÇÃO: index()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Busca todas as O.S. com eager load de maquina e tecnico
     *   2. Ordena pelas mais recentes
     *   3. Pagina em 15 registros
     *   4. Calcula estatísticas:
     *      - Aberta: aguardando execução
     *      - Em andamento: técnico executando
     *      - Concluída: finalizada
     *      - Críticas: prioridade crítica que ainda não foram concluídas
     * SAÍDA: View com lista de O.S. e estatísticas
     * USO: GET /ordens
     */
    public function index()
    {
        // Carrega O.S. com eager load para evitar problema N+1 (múltiplas queries desnecessárias)
        $ordens = OrdemServico::with(['maquina', 'tecnico'])->latest()->paginate(15);

        // Inicializa array de estatísticas para exibição no painel/dashboard
        $stats = [
            // Conta quantas O.S. estão com status 'aberta' (aguardando execução)
            'abertas'      => OrdemServico::where('status', 'aberta')->count(),
            // Conta quantas O.S. estão em andamento (técnico executando)
            'em_andamento' => OrdemServico::where('status', 'em_andamento')->count(),
            // Conta quantas O.S. já foram concluídas
            'concluidas'   => OrdemServico::where('status', 'concluida')->count(),
            // Conta O.S. com prioridade crítica que ainda não foram concluídas (abertas ou em andamento)
            'criticas'     => OrdemServico::where('prioridade', 'critica')
                ->whereIn('status', ['aberta', 'em_andamento'])
                ->count(),
        ]; // fim do array de estatísticas

        // Retorna a view de listagem passando as ordens paginadas e as estatísticas
        return view('ordens.index', compact('ordens', 'stats'));
    } // fim do método index

    /**
     * FUNÇÃO: create()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Busca todas as máquinas ordenadas por modelo
     *   2. Busca técnicos ativos ordenados por nome
     *   3. Passa para view com formulário vazio
     * SAÍDA: View 'ordens.create' com dropdowns preenchidos
     * USO: GET /ordens/create
     */
    public function create()
    {
        // Busca todas as máquinas cadastradas, ordenadas por modelo, para popular o dropdown
        $maquinas = Maquina::orderBy('modelo')->get();
        // Busca apenas técnicos com flag 'ativo' igual a true, ordenados por nome
        $tecnicos = Tecnico::where('ativo', true)->orderBy('nome')->get();

        // Retorna a view de criação passando as listas de máquinas e técnicos
        return view('ordens.create', compact('maquinas', 'tecnicos'));
    } // fim do método create

    /**
     * FUNÇÃO: store($request)
     * ENTRADA: Request com dados do formulário:
     *   - tipo (obrigatório: preventiva ou corretiva)
     *   - prioridade (obrigatório: baixa, media, alta, critica)
     *   - descricao (obrigatório)
     *   - maquina_id (obrigatório, deve existir na tabela maquinas)
     *   - tecnico_id (obrigatório, deve existir na tabela tecnicos)
     *   - data_prevista (opcional, data de conclusão esperada)
     * PROCESSAMENTO:
     *   1. Valida todos os dados do formulário
     *   2. Em transação DB:
     *      a. Gera número único da O.S. (OS-YYYYMMDD-NNNN)
     *      b. Define status inicial como 'aberta'
     *      c. Registra data/hora de abertura
     *      d. Cria ordem no banco
     *      e. Sincroniza status da máquina (muda para em_manutencao se preciso)
     *   3. Notifica técnico que foi atribuído a uma O.S.
     * SAÍDA: Redirecionamento com mensagem de sucesso
     * USO: POST /ordens
     */
    public function store(Request $request)
    {
        // Valida os dados recebidos do formulário, garantindo tipos e existência de registros
        $data = $request->validate([
            // Tipo da O.S.: somente 'preventiva' ou 'corretiva' são aceitos
            'tipo'          => 'required|in:preventiva,corretiva',
            // Prioridade da O.S.: define urgência do atendimento
            'prioridade'    => 'required|in:baixa,media,alta,critica',
            // Descrição detalhada do problema ou serviço a executar
            'descricao'     => 'required|string',
            // ID da máquina: deve existir na tabela maquinas
            'maquina_id'    => 'required|exists:maquinas,id',
            // ID do técnico responsável: deve existir na tabela tecnicos
            'tecnico_id'    => 'required|exists:tecnicos,id',
            // Data prevista para conclusão (opcional, pode ser nula)
            'data_prevista' => 'nullable|date',
        ]); // fim da validação

        // Inicializa array de alertas para mensagens informativas ao usuário
        $alertas = [];

        // Executa criação dentro de transação para garantir atomicidade (número de O.S. sem colisão)
        $ordem = DB::transaction(function () use ($data, &$alertas) {
            // Gera número único e sequencial para a O.S. no formato OS-YYYYMMDD-NNNN
            $data['numero']        = OrdemServico::gerarNumero();
            // Define status inicial como 'aberta', pois ainda não foi iniciada
            $data['status']        = 'aberta';
            // Registra a data e hora atual como momento de abertura da O.S.
            $data['data_abertura'] = now();

            // Persiste a nova O.S. no banco de dados com todos os dados validados
            $ordem = OrdemServico::create($data);

            // Verifica e ajusta o status da máquina (pode mudar para 'em_manutencao')
            if ($alerta = $this->sincronizarStatusMaquina($ordem->maquina_id)) {
                // Adiciona mensagem de alerta caso o status da máquina tenha sido alterado
                $alertas[] = $alerta;
            } // fim da verificação de alerta

            // Retorna a O.S. criada para uso fora da transação
            return $ordem;
        }); // fim da transação

        // Se há alertas gerados (ex: máquina passou para manutenção), exibe como flash message
        if (!empty($alertas)) {
            // Concatena múltiplos alertas separados por ' | ' e armazena na sessão
            session()->flash('alerta', implode(' | ', $alertas));
        } // fim da verificação de alertas

        // Envia notificação ao técnico atribuído informando sobre a nova O.S.
        $this->notificarTecnicoAtribuido($ordem);

        // Redireciona para a listagem de O.S. com mensagem de sucesso
        return redirect()->route('ordens.index')->with('success', "O.S. {$ordem->numero} criada com sucesso!");
    } // fim do método store

    /**
     * FUNÇÃO: show($id)
     * ENTRADA: $id (string) - ID da ordem de serviço
     * PROCESSAMENTO:
     *   1. Busca O.S. pelo ID com eager load de:
     *      - maquina (máquina relacionada)
     *      - tecnico (técnico responsável)
     *      - historico (registro histórico se concluída)
     *   2. Passa para view de detalhes
     * SAÍDA: View com detalhes completos da O.S.
     * USO: GET /ordens/{id}
     */
    public function show(string $id)
    {
        // Busca a O.S. pelo ID carregando os relacionamentos; lança 404 se não encontrar
        $ordem = OrdemServico::with(['maquina', 'tecnico', 'historico'])->findOrFail($id);

        // Retorna a view de detalhes passando a O.S. completa com seus relacionamentos
        return view('ordens.show', compact('ordem'));
    } // fim do método show

    /**
     * FUNÇÃO: edit($id)
     * ENTRADA: $id (string) - ID da ordem a editar
     * PROCESSAMENTO:
     *   1. Busca O.S. pelo ID
     *   2. Busca máquinas e técnicos para dropdowns
     *   3. Passa para view com formulário pré-preenchido
     * SAÍDA: View com formulário de edição
     * USO: GET /ordens/{id}/edit
     */
    public function edit(string $id)
    {
        // Busca a O.S. pelo ID para pré-preencher o formulário; lança 404 se não existir
        $ordem    = OrdemServico::findOrFail($id);
        // Busca máquinas para popular o dropdown de seleção
        $maquinas = Maquina::orderBy('modelo')->get();
        // Busca apenas técnicos ativos para popular o dropdown de responsável
        $tecnicos = Tecnico::where('ativo', true)->orderBy('nome')->get();

        // Retorna a view de edição com os dados da O.S. e as listas de máquinas e técnicos
        return view('ordens.edit', compact('ordem', 'maquinas', 'tecnicos'));
    } // fim do método edit

    /**
     * FUNÇÃO: update($request, $id)
     * ENTRADA:
     *   - $request com dados atualizados (pode incluir conclusão da O.S.)
     *   - $id (string) - ID da ordem a atualizar
     * PROCESSAMENTO (COMPLEXO - leia com atenção):
     *   1. Busca O.S. com histórico e armazena valores anteriores
     *   2. Valida dados incluindo campos de conclusão (tempo_parada_horas, custo, etc)
     *   3. Validação: não permite reabrir O.S. já concluída
     *   4. Extrai dados de conclusão (tempo_parada, custo) para uso posterior
     *   5. Em transação DB executa:
     *      a. Se muda para concluído: registra data_conclusao agora()
     *      b. Atualiza O.S. com novos dados
     *      c. Se técnico mudou, adiciona para notificação
     *      d. Se foi concluída agora (não estava antes):
     *         - Cria registro HistoricoManutencao com dados da O.S.
     *         - Se é preventiva E tem data para próxima: cria O.S. próxima automaticamente
     *      e. Se era concluída e está alterando histórico concluído: atualiza histórico
     *      f. Sincroniza status de máquinas (antigas e nova)
     *   6. Notifica técnico se for novo responsável
     * SAÍDA: Redirecionamento com mensagens de sucesso e alertas
     * USO: PUT /ordens/{id}
     * NOTAS IMPORTANTES:
     *   - Uma O.S. concluída NÃO pode ser reaberta
     *   - Ao concluir, cria-se automaticamente um HistoricoManutencao
     *   - Se preventiva, pode gerar automaticamente próxima manutenção
     */
    public function update(Request $request, string $id)
    {
        // Busca a O.S. pelo ID já carregando o histórico vinculado (se existir)
        $ordem = OrdemServico::with('historico')->findOrFail($id);
        // Guarda o status atual antes da atualização para comparar se houve mudança
        $statusAnterior = $ordem->status;
        // Guarda o ID da máquina atual antes de possível troca
        $maquinaAnteriorId = $ordem->maquina_id;
        // Guarda o ID do técnico atual antes de possível troca
        $tecnicoAnteriorId = $ordem->tecnico_id;

        // Valida todos os campos do formulário de edição, incluindo campos de conclusão
        $data = $request->validate([
            // Tipo da O.S.: preventiva ou corretiva
            'tipo'                => 'required|in:preventiva,corretiva',
            // Prioridade: define urgência do atendimento
            'prioridade'          => 'required|in:baixa,media,alta,critica',
            // Novo status da O.S. (inclui 'cancelada' como opção extra na edição)
            'status'              => 'required|in:aberta,em_andamento,concluida,cancelada',
            // Descrição do problema ou serviço executado
            'descricao'           => 'required|string',
            // Solução aplicada (preenchida ao concluir)
            'solucao'             => 'nullable|string',
            // ID da máquina: deve existir na tabela maquinas
            'maquina_id'          => 'required|exists:maquinas,id',
            // ID do técnico responsável: deve existir na tabela tecnicos
            'tecnico_id'          => 'required|exists:tecnicos,id',
            // Data prevista para conclusão (opcional)
            'data_prevista'       => 'nullable|date',
            // Data para agendamento da próxima preventiva (não pode ser no passado)
            'proxima_preventiva'  => 'nullable|date|after_or_equal:today',
            // Horas que a máquina ficou parada durante a manutenção
            'tempo_parada_horas'  => 'nullable|numeric|min:0',
            // Custo total da manutenção (peças + mão de obra)
            'custo'               => 'nullable|numeric|min:0',
            // Lista de peças utilizadas na manutenção
            'pecas_utilizadas'    => 'nullable|string',
        ]); // fim da validação

        // Impede que uma O.S. já concluída seja reaberta (regra de negócio crítica)
        if ($statusAnterior === 'concluida' && $data['status'] !== 'concluida') {
            // Retorna ao formulário com mensagem de erro explicando a restrição
            return back()->withInput()->with('error',
                'Nao e possivel alterar o status de uma O.S. ja concluida. Crie uma nova O.S. se necessario.');
        } // fim da verificação de status concluída

        // Extrai a data da próxima preventiva do array (vai para outra O.S., não para esta)
        $proximaPreventiva = $data['proxima_preventiva'] ?? null;
        // Agrupa dados de conclusão separadamente, pois vão para a tabela historico_manutencoes
        $dadosConclusao = [
            // Horas de parada: usa 0 se não informado
            'tempo_parada_horas' => $data['tempo_parada_horas'] ?? 0,
            // Custo total: usa 0 se não informado
            'custo'              => $data['custo'] ?? 0,
            // Peças utilizadas: pode ser nulo se não informado
            'pecas_utilizadas'   => $data['pecas_utilizadas'] ?? null,
        ]; // fim do array de dados de conclusão

        // Remove campos que pertencem ao histórico (não existem em ordens_servico)
        unset(
            $data['proxima_preventiva'],   // Campo de agendamento futuro
            $data['tempo_parada_horas'],   // Campo do histórico de manutenção
            $data['custo'],                // Campo do histórico de manutenção
            $data['pecas_utilizadas'],     // Campo do histórico de manutenção
        ); // fim do unset de campos não pertencentes a OrdemServico

        // Inicializa array de alertas informativos para o usuário
        $alertas = [];
        // Inicializa lista de O.S. cujo técnico foi alterado e precisa ser notificado
        $ordensParaNotificar = [];

        // Executa todas as operações em transação para garantir consistência total dos dados
        DB::transaction(function () use ($ordem, $statusAnterior, $maquinaAnteriorId, $tecnicoAnteriorId, &$data, $dadosConclusao, $proximaPreventiva, &$alertas, &$ordensParaNotificar) {
            // Verifica se a O.S. está sendo concluída nesta atualização (transição de estado)
            $concluindoAgora = $data['status'] === 'concluida' && $statusAnterior !== 'concluida';

            // Se está sendo concluída agora, registra a data e hora de conclusão
            if ($concluindoAgora) {
                // Salva o timestamp atual como data de conclusão da O.S.
                $data['data_conclusao'] = now();
            } // fim da verificação de conclusão

            // Persiste as alterações na O.S. no banco de dados
            $ordem->update($data);
            // Recarrega o model do banco para garantir que os dados estejam atualizados
            $ordem->refresh();

            // Verifica se o técnico responsável foi alterado nesta edição
            if ((int) $tecnicoAnteriorId !== (int) $ordem->tecnico_id) {
                // Adiciona a O.S. à lista de notificações (novo técnico será notificado)
                $ordensParaNotificar[] = $ordem->fresh();
            } // fim da verificação de mudança de técnico

            // Se está sendo concluída agora, cria o registro permanente no histórico de manutenções
            if ($concluindoAgora) {
                // Cria o histórico com todos os dados da O.S. e os dados de conclusão
                HistoricoManutencao::create([
                    // ID da máquina que foi mantida
                    'maquina_id'         => $ordem->maquina_id,
                    // ID do técnico que executou a manutenção
                    'tecnico_id'         => $ordem->tecnico_id,
                    // Referência à O.S. que gerou este histórico
                    'ordem_id'           => $ordem->id,
                    // Tipo de manutenção: preventiva ou corretiva
                    'tipo'               => $ordem->tipo,
                    // Descrição do problema ou serviço executado
                    'descricao'          => $ordem->descricao,
                    // Solução adotada para resolver o problema
                    'solucao'            => $ordem->solucao,
                    // Horas que a máquina ficou parada (impacto na produção)
                    'tempo_parada_horas' => $dadosConclusao['tempo_parada_horas'],
                    // Custo total da manutenção (peças + serviço)
                    'custo'              => $dadosConclusao['custo'],
                    // Lista de peças substituídas ou utilizadas
                    'pecas_utilizadas'   => $dadosConclusao['pecas_utilizadas'],
                    // Data de abertura da O.S. usada como início do histórico
                    'data_inicio'        => $ordem->data_abertura,
                    // Data de conclusão usada como fim do histórico
                    'data_fim'           => $ordem->data_conclusao,
                ]); // fim da criação do histórico
            } elseif ($ordem->status === 'concluida' && $ordem->historico) {
                // Se a O.S. já era concluída, atualiza o histórico existente com os novos dados
                $ordem->historico->update([
                    // Atualiza máquina caso tenha sido alterada
                    'maquina_id' => $ordem->maquina_id,
                    // Atualiza técnico caso tenha sido alterado
                    'tecnico_id' => $ordem->tecnico_id,
                    // Atualiza tipo caso tenha sido alterado
                    'tipo'       => $ordem->tipo,
                    // Atualiza descrição caso tenha sido alterada
                    'descricao'  => $ordem->descricao,
                    // Atualiza solução caso tenha sido alterada
                    'solucao'    => $ordem->solucao,
                ]); // fim da atualização do histórico existente
            } // fim do bloco de criação/atualização de histórico

            // Sincroniza status de todas as máquinas envolvidas (antiga e nova, caso tenha mudado)
            foreach (array_unique([$maquinaAnteriorId, $ordem->maquina_id]) as $maquinaId) {
                // Chama sincronização para cada máquina e coleta alertas gerados
                if ($alerta = $this->sincronizarStatusMaquina($maquinaId)) {
                    // Adiciona alerta ao array caso o status da máquina tenha mudado
                    $alertas[] = $alerta;
                } // fim da verificação de alerta de máquina
            } // fim do foreach de sincronização de máquinas

            // Se está concluindo uma preventiva E foi informada data para próxima: cria nova O.S.
            if ($concluindoAgora && $ordem->tipo === 'preventiva' && $proximaPreventiva) {
                // Cria automaticamente uma nova O.S. preventiva para a data programada
                $proximaOrdem = OrdemServico::create([
                    // Gera novo número sequencial para a próxima O.S.
                    'numero'        => OrdemServico::gerarNumero(),
                    // Mantém o tipo como preventiva para a próxima manutenção
                    'tipo'          => 'preventiva',
                    // Nova O.S. começa com status 'aberta'
                    'status'        => 'aberta',
                    // Mantém a mesma prioridade da O.S. atual
                    'prioridade'    => $ordem->prioridade,
                    // Descrição padrão indicando que foi gerada automaticamente
                    'descricao'     => 'Manutencao preventiva programada (gerada automaticamente)',
                    // Associa à mesma máquina da O.S. atual
                    'maquina_id'    => $ordem->maquina_id,
                    // Mantém o mesmo técnico responsável
                    'tecnico_id'    => $ordem->tecnico_id,
                    // Registra o momento de criação como data de abertura
                    'data_abertura' => now(),
                    // Define a data prevista conforme informada no formulário
                    'data_prevista' => $proximaPreventiva,
                ]); // fim da criação da próxima O.S. preventiva

                // Adiciona nova O.S. à lista de notificações (técnico será notificado)
                $ordensParaNotificar[] = $proximaOrdem;
                // Adiciona alerta informando que a próxima preventiva foi agendada
                $alertas[] = "Proxima manutencao preventiva agendada para {$proximaPreventiva}.";
            } // fim da criação automática da próxima preventiva
        }); // fim da transação DB

        // Se há alertas gerados durante a transação, exibe como flash message
        if (!empty($alertas)) {
            // Concatena todos os alertas com separador ' | ' e armazena na sessão
            session()->flash('alerta', implode(' | ', $alertas));
        } // fim da verificação de alertas

        // Itera sobre as O.S. que precisam notificar técnico (novo responsável atribuído)
        foreach ($ordensParaNotificar as $ordemAtribuida) {
            // Envia notificação ao técnico responsável pela O.S. atribuída
            $this->notificarTecnicoAtribuido($ordemAtribuida);
        } // fim do foreach de notificações

        // Redireciona para a listagem com mensagem de sucesso
        return redirect()->route('ordens.index')->with('success', "O.S. {$ordem->numero} atualizada!");
    } // fim do método update

    /**
     * FUNÇÃO: exportarSingle($id)
     * ENTRADA: $id (string) - ID da ordem a exportar
     * PROCESSAMENTO:
     *   1. Busca O.S. com máquina, técnico e histórico
     *   2. Abre stream CSV em memória
     *   3. Escreve BOM UTF-8 para compatibilidade
     *   4. Escreve cabeçalhos: Campo, Valor
     *   5. Escreve todos os dados da O.S. em pares campo-valor
     *   6. Se tem histórico, escreve dados adicionais (parada, custo, peças)
     *   7. Envia arquivo como download direto
     * SAÍDA: Stream CSV com nome "OS-YYYYMMDD-NNNN-YYYY-MM-DD.csv"
     * USO: GET /ordens/{id}/exportar
     * OBSERVAÇÃO: Usa streamDownload para não sobrecarregar memória
     */
    public function exportarSingle(string $id)
    {
        // Busca a O.S. com todos os relacionamentos necessários para o CSV
        $ordem = OrdemServico::with(['maquina', 'tecnico', 'historico'])->findOrFail($id);

        // Retorna resposta como stream de download (não carrega tudo na memória)
        return response()->streamDownload(function () use ($ordem) {
            // Abre o output padrão do PHP como arquivo para escrita do CSV
            $f = fopen('php://output', 'w');
            // Escreve o BOM (Byte Order Mark) UTF-8 para garantir compatibilidade com Excel
            fputs($f, "\xEF\xBB\xBF");
            // Escreve a linha de cabeçalho do CSV com separador ponto e vírgula
            fputcsv($f, ['Campo', 'Valor'], ';');

            // Prepara todas as linhas de dados da O.S. em formato par campo-valor
            $rows = [
                // Número único da O.S. no formato OS-YYYYMMDD-NNNN
                ['Número',              $ordem->numero],
                // Tipo formatado para exibição (ex: "Preventiva" ou "Corretiva")
                ['Tipo',                $ordem->tipo_label],
                // Status atual formatado (ex: "Aberta", "Em Andamento", "Concluída")
                ['Status',              $ordem->status_label],
                // Prioridade formatada (ex: "Crítica", "Alta", "Média", "Baixa")
                ['Prioridade',          $ordem->prioridade_label],
                // Modelo da máquina relacionada (ou vazio se não houver)
                ['Máquina',             $ordem->maquina->modelo ?? ''],
                // Número de série da máquina para rastreabilidade
                ['Nº Série',            $ordem->maquina->numero_serie ?? ''],
                // Localização física da máquina na planta industrial
                ['Localização',         $ordem->maquina->localizacao ?? ''],
                // Nome do técnico responsável pela O.S.
                ['Técnico',             $ordem->tecnico->nome ?? ''],
                // Data e hora de abertura da O.S. formatada para pt-BR
                ['Abertura',            $ordem->data_abertura->format('d/m/Y H:i')],
                // Data prevista formatada (ou vazio se não foi definida)
                ['Prevista',            $ordem->data_prevista ? $ordem->data_prevista->format('d/m/Y') : ''],
                // Data e hora de conclusão (ou vazio se ainda não foi concluída)
                ['Conclusão',           $ordem->data_conclusao ? $ordem->data_conclusao->format('d/m/Y H:i') : ''],
                // Descrição completa do problema ou serviço a executar
                ['Descrição',           $ordem->descricao],
                // Solução registrada (ou vazio se ainda não foi concluída)
                ['Solução',             $ordem->solucao ?? ''],
            ]; // fim do array de linhas de dados básicos

            // Se a O.S. tem histórico vinculado (foi concluída), adiciona dados extras
            if ($ordem->historico) {
                // Data e hora de início da manutenção conforme registrado no histórico
                $rows[] = ['Início (Histórico)', $ordem->historico->data_inicio?->format('d/m/Y H:i') ?? ''];
                // Data e hora de término da manutenção conforme registrado no histórico
                $rows[] = ['Fim (Histórico)',    $ordem->historico->data_fim?->format('d/m/Y H:i') ?? ''];
                // Horas de parada da máquina formatadas com 1 casa decimal (ou vazio se zero)
                $rows[] = ['Parada (h)',         $ordem->historico->tempo_parada_horas > 0 ? number_format($ordem->historico->tempo_parada_horas, 1, ',', '.') : ''];
                // Custo total da manutenção formatado em reais com 2 casas decimais (ou vazio se zero)
                $rows[] = ['Custo (R$)',         $ordem->historico->custo > 0 ? number_format($ordem->historico->custo, 2, ',', '.') : ''];
                // Lista de peças utilizadas na manutenção (ou vazio se não informado)
                $rows[] = ['Peças Utilizadas',   $ordem->historico->pecas_utilizadas ?? ''];
            } // fim da adição de dados do histórico

            // Itera sobre todas as linhas e escreve cada uma no arquivo CSV
            foreach ($rows as $row) {
                // Escreve a linha no arquivo usando ponto e vírgula como separador
                fputcsv($f, $row, ';');
            } // fim do foreach de escrita das linhas
            // Fecha o arquivo/stream após concluir a escrita
            fclose($f);
        // Define o nome do arquivo CSV incluindo o número da O.S. e a data atual
        }, $ordem->numero . '-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    } // fim do método exportarSingle

    /**
     * FUNÇÃO: exportar()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Busca todas as O.S. com máquina e técnico
     *   2. Abre stream CSV em memória
     *   3. Escreve cabeçalhos: Número, Tipo, Máquina, Técnico, Prioridade, Status, Datas
     *   4. Escreve cada O.S. em uma linha
     *   5. Envia arquivo como download direto
     * SAÍDA: Stream CSV com nome "ordens-servico-YYYY-MM-DD.csv"
     * USO: GET /ordens/exportar
     * OBSERVAÇÃO: Exporta todas as O.S., formato resumido (uma linha por O.S.)
     */
    public function exportar()
    {
        // Busca todas as O.S. com máquina e técnico, ordenadas das mais recentes
        $ordens = OrdemServico::with(['maquina', 'tecnico'])->latest()->get();

        // Retorna resposta como stream de download para não sobrecarregar memória
        return response()->streamDownload(function () use ($ordens) {
            // Abre o output padrão do PHP como arquivo para escrita do CSV
            $f = fopen('php://output', 'w');
            // Escreve o BOM UTF-8 para garantir compatibilidade com Excel
            fputs($f, "\xEF\xBB\xBF");
            // Escreve os cabeçalhos das colunas do CSV com separador ponto e vírgula
            fputcsv($f, ['Número','Tipo','Máquina','Técnico','Prioridade','Status','Abertura','Prevista','Conclusão'], ';');

            // Itera sobre todas as O.S. e escreve cada uma como uma linha no CSV
            foreach ($ordens as $os) {
                // Escreve os campos da O.S. atual como uma linha no CSV
                fputcsv($f, [
                    // Número único da O.S.
                    $os->numero,
                    // Tipo formatado para exibição
                    $os->tipo_label,
                    // Modelo da máquina ou vazio se não houver
                    $os->maquina->modelo ?? '',
                    // Nome do técnico ou vazio se não houver
                    $os->tecnico->nome ?? '',
                    // Prioridade formatada para exibição
                    $os->prioridade_label,
                    // Status formatado para exibição
                    $os->status_label,
                    // Data de abertura formatada para pt-BR
                    $os->data_abertura->format('d/m/Y'),
                    // Data prevista formatada ou vazio se não definida
                    $os->data_prevista ? $os->data_prevista->format('d/m/Y') : '',
                    // Data de conclusão formatada ou vazio se ainda em aberto
                    $os->data_conclusao ? $os->data_conclusao->format('d/m/Y') : '',
                ], ';'); // fim do fputcsv da O.S. atual
            } // fim do foreach de O.S.
            // Fecha o arquivo/stream após concluir a escrita
            fclose($f);
        // Define o nome do arquivo CSV com a data atual
        }, 'ordens-servico-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    } // fim do método exportar

    /**
     * FUNÇÃO: destroy($id)
     * ENTRADA: $id (string) - ID da ordem a deletar
     * PROCESSAMENTO:
     *   1. Busca O.S. pelo ID
     *   2. Armazena ID da máquina associada
     *   3. Em transação DB:
     *      a. Deleta a O.S.
     *      b. Sincroniza status da máquina (se não há mais O.S., volta para operacional)
     *   4. Exibe alerta se houver mudança de status
     * SAÍDA: Redirecionamento com mensagem de sucesso
     * USO: DELETE /ordens/{id}
     */
    public function destroy(string $id)
    {
        // Busca a O.S. pelo ID; lança 404 se não encontrar
        $ordem = OrdemServico::findOrFail($id);
        // Guarda o ID da máquina para sincronizar o status após a exclusão
        $maquinaId = $ordem->maquina_id;
        // Inicializa array de alertas gerados durante a operação
        $alertas = [];

        // Executa a exclusão dentro de transação para garantir consistência
        DB::transaction(function () use ($ordem, $maquinaId, &$alertas) {
            // Remove a O.S. permanentemente do banco de dados
            $ordem->delete();

            // Verifica e atualiza o status da máquina (pode voltar para 'operacional')
            if ($alerta = $this->sincronizarStatusMaquina($maquinaId)) {
                // Adiciona alerta caso o status da máquina tenha sido alterado
                $alertas[] = $alerta;
            } // fim da verificação de alerta de máquina
        }); // fim da transação

        // Se há alertas gerados (ex: máquina voltou para operacional), exibe como flash message
        if (!empty($alertas)) {
            // Concatena alertas e armazena na sessão para exibição na próxima requisição
            session()->flash('alerta', implode(' | ', $alertas));
        } // fim da verificação de alertas

        // Redireciona para a listagem com mensagem de confirmação de exclusão
        return redirect()->route('ordens.index')->with('success', 'Ordem de Servico excluida.');
    } // fim do método destroy

    /**
     * FUNÇÃO PRIVADA: sincronizarStatusMaquina($maquinaId)
     * ENTRADA: $maquinaId (int) - ID da máquina cujo status será sincronizado
     * PROCESSAMENTO:
     *   1. Busca máquina pelo ID (retorna null se não existe)
     *   2. Verifica se EXISTE alguma O.S. ativa para esta máquina:
     *      - Status 'aberta' ou 'em_andamento' AND
     *      - (uma das seguintes):
     *        * Status é 'em_andamento' (técnico executando), OU
     *        * Tipo é 'corretiva' (manutenção de emergência), OU
     *        * Não tem data_prevista (urgente, sem prazo), OU
     *        * data_prevista é hoje ou anterior (vencida)
     *   3. Lógica de sincronização:
     *      - Se HÁ O.S. ativa E máquina está 'operacional': muda para 'em_manutencao'
     *      - Se NÃO HÁ O.S. ativa E máquina está 'em_manutencao': muda para 'operacional'
     *      - Se máquina está em 'parada_critica' ou 'inativa': não muda (admin decide)
     * SAÍDA: String com mensagem de mudança ou null se não houve mudança
     * USO: Chamado automaticamente ao criar, atualizar ou deletar O.S.
     * CRÍTICO: Esta função mantém sincronismo entre O.S. e status de máquina
     */
    private function sincronizarStatusMaquina(int $maquinaId): ?string
    {
        // Tenta encontrar a máquina pelo ID (pode não existir se foi deletada)
        $maquina = Maquina::find($maquinaId);

        // Se a máquina não existe, retorna null sem fazer nada
        if (!$maquina) {
            return null;
        } // fim da verificação de existência da máquina

        // Verifica se existe alguma O.S. ativa que justifique a máquina estar em manutenção
        $temOrdemAtiva = OrdemServico::where('maquina_id', $maquinaId)
            // Considera apenas O.S. em estados ativos (não concluídas nem canceladas)
            ->whereIn('status', ['aberta', 'em_andamento'])
            // Ao menos uma das condições deve ser verdadeira para considerar como ativa urgente
            ->where(function ($query) {
                // Técnico já iniciou a execução da manutenção
                $query->where('status', 'em_andamento')
                    // Manutenção de emergência (corretiva) requer parada imediata
                    ->orWhere('tipo', 'corretiva')
                    // Sem prazo definido indica urgência não planejada
                    ->orWhereNull('data_prevista')
                    // Prazo vencido: manutenção deveria ter sido feita
                    ->orWhereDate('data_prevista', '<=', today());
            }) // fim do closure de condições
            // Retorna true se ao menos um registro corresponde
            ->exists();

        // Se há O.S. ativa urgente e a máquina ainda está operacional: muda para em manutenção
        if ($temOrdemAtiva && $maquina->status === 'operacional') {
            // Atualiza o status da máquina para refletir que está sendo mantida
            $maquina->update(['status' => 'em_manutencao']);

            // Retorna mensagem informando a mudança de status
            return "Maquina {$maquina->modelo} passou para Em Manutencao.";
        } // fim da verificação para mudar para em_manutencao

        // Se não há mais O.S. ativas e a máquina está em manutenção: volta para operacional
        if (!$temOrdemAtiva && $maquina->status === 'em_manutencao') {
            // Atualiza o status da máquina para operacional pois não há mais ordens ativas
            $maquina->update(['status' => 'operacional']);

            // Retorna mensagem informando a mudança de status
            return "Maquina {$maquina->modelo} voltou a ser Operacional.";
        } // fim da verificação para voltar para operacional

        // Se máquina está em parada_critica ou inativa, não altera automaticamente (requer decisão manual)
        return null;
    } // fim do método sincronizarStatusMaquina

    /**
     * FUNÇÃO PRIVADA: notificarTecnicoAtribuido($ordem)
     * ENTRADA: $ordem (OrdemServico) - A ordem que foi atribuída
     * PROCESSAMENTO:
     *   1. Carrega relacionamentos faltantes (maquina e tecnico.user)
     *   2. Obtém usuário do técnico (tecnico pode ter um user associado)
     *   3. Se técnico não tem usuário, sai (sem erro)
     *   4. Envia notificação usando classe OrdemServicoAtribuida
     *   5. Notificação é enfileirada (pode ser assíncrona se configurado)
     * SAÍDA: Nenhuma (void)
     * USO: Chamado ao criar ou mudar técnico responsável de uma O.S.
     * OBSERVAÇÃO: Usa sistema de notificações do Laravel (pode ser email, SMS, etc)
     */
    private function notificarTecnicoAtribuido(OrdemServico $ordem): void
    {
        // Carrega os relacionamentos necessários caso ainda não estejam em memória
        $ordem->loadMissing(['maquina', 'tecnico.user']);

        // Obtém o usuário vinculado ao técnico (operador null-safe para evitar erro se técnico for null)
        $user = $ordem->tecnico?->user;

        // Se o técnico não possui usuário cadastrado no sistema, não há como notificar
        if (!$user) {
            // Encerra a função sem lançar exceção (situação aceita)
            return;
        } // fim da verificação de usuário do técnico

        // Envia a notificação ao usuário informando que foi atribuído a esta O.S.
        $user->notify(new OrdemServicoAtribuida($ordem));
    } // fim do método notificarTecnicoAtribuido
} // fim da classe OrdemServicoController
