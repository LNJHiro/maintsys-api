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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OrdemServico;
use App\Models\Maquina;
use App\Models\Tecnico;
use App\Models\HistoricoManutencao;
use App\Notifications\OrdemServicoAtribuida;

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
        // Eager load para evitar problema N+1 (múltiplas queries)
        $ordens = OrdemServico::with(['maquina', 'tecnico'])->latest()->paginate(15);

        // Calcula estatísticas por status para exibição em dashboard
        $stats = [
            'abertas'      => OrdemServico::where('status', 'aberta')->count(),
            'em_andamento' => OrdemServico::where('status', 'em_andamento')->count(),
            'concluidas'   => OrdemServico::where('status', 'concluida')->count(),
            // Críticas são aquelas não concluídas com prioridade crítica
            'criticas'     => OrdemServico::where('prioridade', 'critica')
                ->whereIn('status', ['aberta', 'em_andamento'])
                ->count(),
        ];

        return view('ordens.index', compact('ordens', 'stats'));
    }

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
        // Busca máquinas ordenadas para popular dropdown
        $maquinas = Maquina::orderBy('modelo')->get();
        // Busca apenas técnicos ativos
        $tecnicos = Tecnico::where('ativo', true)->orderBy('nome')->get();

        return view('ordens.create', compact('maquinas', 'tecnicos'));
    }

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
        // Valida dados do formulário
        $data = $request->validate([
            'tipo'          => 'required|in:preventiva,corretiva',
            'prioridade'    => 'required|in:baixa,media,alta,critica',
            'descricao'     => 'required|string',
            'maquina_id'    => 'required|exists:maquinas,id',      // Valida que máquina existe
            'tecnico_id'    => 'required|exists:tecnicos,id',      // Valida que técnico existe
            'data_prevista' => 'nullable|date',
        ]);

        $alertas = [];

        // Transação para garantir que número de O.S. seja gerado atomicamente
        $ordem = DB::transaction(function () use ($data, &$alertas) {
            // Gera número único da O.S. (com lock para evitar colisão)
            $data['numero']        = OrdemServico::gerarNumero();
            $data['status']        = 'aberta';           // O.S. começa aberta
            $data['data_abertura'] = now();              // Registra data/hora atual

            // Cria O.S. no banco
            $ordem = OrdemServico::create($data);

            // Sincroniza status da máquina (muda para em_manutencao se houver O.S. ativa)
            if ($alerta = $this->sincronizarStatusMaquina($ordem->maquina_id)) {
                $alertas[] = $alerta;
            }

            return $ordem;
        });

        // Exibe alertas em flash message se houver (ex: máquina passou para manutenção)
        if (!empty($alertas)) {
            session()->flash('alerta', implode(' | ', $alertas));
        }

        // Notifica técnico que foi atribuído a uma O.S.
        $this->notificarTecnicoAtribuido($ordem);

        return redirect()->route('ordens.index')->with('success', "O.S. {$ordem->numero} criada com sucesso!");
    }

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
        // Eager load para evitar N queries
        $ordem = OrdemServico::with(['maquina', 'tecnico', 'historico'])->findOrFail($id);

        return view('ordens.show', compact('ordem'));
    }

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
        $ordem    = OrdemServico::findOrFail($id);
        $maquinas = Maquina::orderBy('modelo')->get();
        $tecnicos = Tecnico::where('ativo', true)->orderBy('nome')->get();

        return view('ordens.edit', compact('ordem', 'maquinas', 'tecnicos'));
    }

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
     *   - Uma O.S. concluída NÃO pode ser reabertas
     *   - Ao concluir, cria-se automaticamente um HistoricoManutencao
     *   - Se preventiva, pode gerar automaticamente próxima manutenção
     */
    public function update(Request $request, string $id)
    {
        $ordem = OrdemServico::with('historico')->findOrFail($id);
        // Armazena valores anteriores para comparações
        $statusAnterior = $ordem->status;
        $maquinaAnteriorId = $ordem->maquina_id;
        $tecnicoAnteriorId = $ordem->tecnico_id;

        // Valida todos os dados do formulário
        $data = $request->validate([
            'tipo'                => 'required|in:preventiva,corretiva',
            'prioridade'          => 'required|in:baixa,media,alta,critica',
            'status'              => 'required|in:aberta,em_andamento,concluida,cancelada',
            'descricao'           => 'required|string',
            'solucao'             => 'nullable|string',
            'maquina_id'          => 'required|exists:maquinas,id',
            'tecnico_id'          => 'required|exists:tecnicos,id',
            'data_prevista'       => 'nullable|date',
            'proxima_preventiva'  => 'nullable|date|after_or_equal:today',  // Próxima manutenção
            'tempo_parada_horas'  => 'nullable|numeric|min:0',              // Horas de parada
            'custo'               => 'nullable|numeric|min:0',              // Custo total
            'pecas_utilizadas'    => 'nullable|string',                     // Peças usadas
        ]);

        // Validação: não permite reabrir O.S. já concluída
        if ($statusAnterior === 'concluida' && $data['status'] !== 'concluida') {
            return back()->withInput()->with('error',
                'Nao e possivel alterar o status de uma O.S. ja concluida. Crie uma nova O.S. se necessario.');
        }

        // Extrai dados de conclusão que não vão para tabela ordens_servico
        $proximaPreventiva = $data['proxima_preventiva'] ?? null;
        $dadosConclusao = [
            'tempo_parada_horas' => $data['tempo_parada_horas'] ?? 0,
            'custo'              => $data['custo'] ?? 0,
            'pecas_utilizadas'   => $data['pecas_utilizadas'] ?? null,
        ];

        // Remove campos que não pertencem a OrdemServico do array $data
        unset(
            $data['proxima_preventiva'],
            $data['tempo_parada_horas'],
            $data['custo'],
            $data['pecas_utilizadas'],
        );

        $alertas = [];
        $ordensParaNotificar = [];

        // Transação garante consistência entre OrdemServico, HistoricoManutencao e Maquina
        DB::transaction(function () use ($ordem, $statusAnterior, $maquinaAnteriorId, $tecnicoAnteriorId, &$data, $dadosConclusao, $proximaPreventiva, &$alertas, &$ordensParaNotificar) {
            // Verifica se está sendo concluída nesta atualização
            $concluindoAgora = $data['status'] === 'concluida' && $statusAnterior !== 'concluida';

            // Se está concluindo agora, registra data/hora atual
            if ($concluindoAgora) {
                $data['data_conclusao'] = now();
            }

            // Atualiza O.S. no banco
            $ordem->update($data);
            $ordem->refresh();  // Recarrega do banco para ter dados atualizados

            // Se técnico foi alterado, adiciona à lista de notificação
            if ((int) $tecnicoAnteriorId !== (int) $ordem->tecnico_id) {
                $ordensParaNotificar[] = $ordem->fresh();
            }

            // Se está sendo concluída agora, cria registro no histórico
            if ($concluindoAgora) {
                HistoricoManutencao::create([
                    'maquina_id'         => $ordem->maquina_id,
                    'tecnico_id'         => $ordem->tecnico_id,
                    'ordem_id'           => $ordem->id,
                    'tipo'               => $ordem->tipo,
                    'descricao'          => $ordem->descricao,
                    'solucao'            => $ordem->solucao,
                    'tempo_parada_horas' => $dadosConclusao['tempo_parada_horas'],
                    'custo'              => $dadosConclusao['custo'],
                    'pecas_utilizadas'   => $dadosConclusao['pecas_utilizadas'],
                    'data_inicio'        => $ordem->data_abertura,
                    'data_fim'           => $ordem->data_conclusao,
                ]);
            } elseif ($ordem->status === 'concluida' && $ordem->historico) {
                // Se era concluída e está atualizando dados, atualiza também o histórico
                $ordem->historico->update([
                    'maquina_id' => $ordem->maquina_id,
                    'tecnico_id' => $ordem->tecnico_id,
                    'tipo'       => $ordem->tipo,
                    'descricao'  => $ordem->descricao,
                    'solucao'    => $ordem->solucao,
                ]);
            }

            // Sincroniza status de ambas as máquinas (antiga e nova) se mudou a máquina
            foreach (array_unique([$maquinaAnteriorId, $ordem->maquina_id]) as $maquinaId) {
                if ($alerta = $this->sincronizarStatusMaquina($maquinaId)) {
                    $alertas[] = $alerta;
                }
            }

            // Se concluindo preventiva COM data para próxima: cria O.S. próxima automaticamente
            if ($concluindoAgora && $ordem->tipo === 'preventiva' && $proximaPreventiva) {
                $proximaOrdem = OrdemServico::create([
                    'numero'        => OrdemServico::gerarNumero(),
                    'tipo'          => 'preventiva',
                    'status'        => 'aberta',
                    'prioridade'    => $ordem->prioridade,
                    'descricao'     => 'Manutencao preventiva programada (gerada automaticamente)',
                    'maquina_id'    => $ordem->maquina_id,
                    'tecnico_id'    => $ordem->tecnico_id,
                    'data_abertura' => now(),
                    'data_prevista' => $proximaPreventiva,
                ]);

                $ordensParaNotificar[] = $proximaOrdem;
                $alertas[] = "Proxima manutencao preventiva agendada para {$proximaPreventiva}.";
            }
        });

        // Exibe alertas se houver
        if (!empty($alertas)) {
            session()->flash('alerta', implode(' | ', $alertas));
        }

        // Notifica técnicos de O.S. que foram atribuídas
        foreach ($ordensParaNotificar as $ordemAtribuida) {
            $this->notificarTecnicoAtribuido($ordemAtribuida);
        }

        return redirect()->route('ordens.index')->with('success', "O.S. {$ordem->numero} atualizada!");
    }

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
        $ordem = OrdemServico::with(['maquina', 'tecnico', 'historico'])->findOrFail($id);

        return response()->streamDownload(function () use ($ordem) {
            // Abre output como arquivo CSV
            $f = fopen('php://output', 'w');
            // Escreve BOM UTF-8 para compatibilidade com Excel
            fputs($f, "\xEF\xBB\xBF");
            fputcsv($f, ['Campo', 'Valor'], ';');

            // Prepara linhas de dados
            $rows = [
                ['Número',              $ordem->numero],
                ['Tipo',                $ordem->tipo_label],
                ['Status',              $ordem->status_label],
                ['Prioridade',          $ordem->prioridade_label],
                ['Máquina',             $ordem->maquina->modelo ?? ''],
                ['Nº Série',            $ordem->maquina->numero_serie ?? ''],
                ['Localização',         $ordem->maquina->localizacao ?? ''],
                ['Técnico',             $ordem->tecnico->nome ?? ''],
                ['Abertura',            $ordem->data_abertura->format('d/m/Y H:i')],
                ['Prevista',            $ordem->data_prevista ? $ordem->data_prevista->format('d/m/Y') : ''],
                ['Conclusão',           $ordem->data_conclusao ? $ordem->data_conclusao->format('d/m/Y H:i') : ''],
                ['Descrição',           $ordem->descricao],
                ['Solução',             $ordem->solucao ?? ''],
            ];

            // Se tem histórico (foi concluída), adiciona dados do histórico
            if ($ordem->historico) {
                $rows[] = ['Início (Histórico)', $ordem->historico->data_inicio?->format('d/m/Y H:i') ?? ''];
                $rows[] = ['Fim (Histórico)',    $ordem->historico->data_fim?->format('d/m/Y H:i') ?? ''];
                $rows[] = ['Parada (h)',         $ordem->historico->tempo_parada_horas > 0 ? number_format($ordem->historico->tempo_parada_horas, 1, ',', '.') : ''];
                $rows[] = ['Custo (R$)',         $ordem->historico->custo > 0 ? number_format($ordem->historico->custo, 2, ',', '.') : ''];
                $rows[] = ['Peças Utilizadas',   $ordem->historico->pecas_utilizadas ?? ''];
            }

            // Escreve todas as linhas no CSV
            foreach ($rows as $row) {
                fputcsv($f, $row, ';');
            }
            fclose($f);
        }, $ordem->numero . '-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

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
        // Busca todas as O.S. com máquina e técnico
        $ordens = OrdemServico::with(['maquina', 'tecnico'])->latest()->get();

        return response()->streamDownload(function () use ($ordens) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF");  // BOM UTF-8
            // Cabeçalhos da planilha
            fputcsv($f, ['Número','Tipo','Máquina','Técnico','Prioridade','Status','Abertura','Prevista','Conclusão'], ';');

            // Escreve cada O.S. em uma linha
            foreach ($ordens as $os) {
                fputcsv($f, [
                    $os->numero,
                    $os->tipo_label,
                    $os->maquina->modelo ?? '',
                    $os->tecnico->nome ?? '',
                    $os->prioridade_label,
                    $os->status_label,
                    $os->data_abertura->format('d/m/Y'),
                    $os->data_prevista ? $os->data_prevista->format('d/m/Y') : '',
                    $os->data_conclusao ? $os->data_conclusao->format('d/m/Y') : '',
                ], ';');
            }
            fclose($f);
        }, 'ordens-servico-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

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
        $ordem = OrdemServico::findOrFail($id);
        $maquinaId = $ordem->maquina_id;
        $alertas = [];

        // Transação para garantir consistência
        DB::transaction(function () use ($ordem, $maquinaId, &$alertas) {
            // Deleta a O.S.
            $ordem->delete();

            // Sincroniza status da máquina (pode voltar para operacional se não tem mais O.S.)
            if ($alerta = $this->sincronizarStatusMaquina($maquinaId)) {
                $alertas[] = $alerta;
            }
        });

        // Exibe alertas se houver
        if (!empty($alertas)) {
            session()->flash('alerta', implode(' | ', $alertas));
        }

        return redirect()->route('ordens.index')->with('success', 'Ordem de Servico excluida.');
    }

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
        // Busca máquina
        $maquina = Maquina::find($maquinaId);

        if (!$maquina) {
            return null;
        }

        // Verifica se EXISTE O.S. ativa que requer manutenção
        $temOrdemAtiva = OrdemServico::where('maquina_id', $maquinaId)
            ->whereIn('status', ['aberta', 'em_andamento'])
            // Condição: tem O.S. ativa se atende qualquer um desses:
            ->where(function ($query) {
                $query->where('status', 'em_andamento')     // Técnico está executando
                    ->orWhere('tipo', 'corretiva')          // Manutenção emergencial
                    ->orWhereNull('data_prevista')          // Sem prazo (urgente)
                    ->orWhereDate('data_prevista', '<=', today()); // Prazo vencido
            })
            ->exists();

        // Se tem O.S. ativa e máquina está operacional: muda para em_manutencao
        if ($temOrdemAtiva && $maquina->status === 'operacional') {
            $maquina->update(['status' => 'em_manutencao']);

            return "Maquina {$maquina->modelo} passou para Em Manutencao.";
        }

        // Se NÃO tem O.S. ativa e máquina está em_manutencao: volta para operacional
        if (!$temOrdemAtiva && $maquina->status === 'em_manutencao') {
            $maquina->update(['status' => 'operacional']);

            return "Maquina {$maquina->modelo} voltou a ser Operacional.";
        }

        // Se máquina está em parada_critica ou inativa, não muda automaticamente
        return null;
    }

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
        // Carrega relacionamentos que podem não estar em memória
        $ordem->loadMissing(['maquina', 'tecnico.user']);

        // Obtém usuário do técnico
        $user = $ordem->tecnico?->user;

        // Se técnico não tem usuário, sai
        if (!$user) {
            return;
        }

        // Envia notificação usando classe OrdemServicoAtribuida
        $user->notify(new OrdemServicoAtribuida($ordem));
    }
}
