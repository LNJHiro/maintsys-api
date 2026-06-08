<?php

/**
 * CONTROLLER: HistoricoController
 *
 * Responsável pela visualização e gerenciamento do histórico de manutenções.
 * Funcionalidades:
 * - Listar históricos com filtros (máquina, tipo, técnico, período)
 * - Ver detalhes de manutenção por ID
 * - Ver histórico completo de uma máquina com análise de reincidências
 * - Exportar históricos em CSV
 * - Adicionar históricos manualmente (raro, geralmente criado automaticamente)
 * - Deletar registros de histórico
 */

// Define o namespace do controller dentro da estrutura de controllers HTTP do Laravel
namespace App\Http\Controllers;

// Importa a classe Request do Laravel para manipular dados da requisição HTTP
use Illuminate\Http\Request;
// Importa o model HistoricoManutencao para interagir com a tabela de históricos
use App\Models\HistoricoManutencao;
// Importa o model Maquina para buscar dados de máquinas nos dropdowns e filtros
use App\Models\Maquina;
// Importa o model Tecnico para buscar dados de técnicos nos dropdowns e filtros
use App\Models\Tecnico;

// Declara a classe HistoricoController herdando de Controller (base do Laravel)
class HistoricoController extends Controller
{
    /**
     * FUNÇÃO: index($request)
     * ENTRADA: Request com filtros opcionais (maquina_id, tipo, tecnico_id, data_inicio, data_fim)
     * PROCESSAMENTO:
     *   1. Constrói query com relacionamentos
     *   2. Aplica filtros dinamicamente se preenchidos
     *   3. Ordena por mais recentes
     *   4. Pagina em 20 registros
     *   5. Busca listas de máquinas e técnicos para dropdowns de filtro
     * SAÍDA: View com históricos paginados e filtros
     * USO: GET /historico
     */
    public function index(Request $request)
    {
        // Inicia a query base com eager loading para evitar N+1 queries
        $query = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem']);

        // Aplica filtro por máquina se o campo foi preenchido no formulário
        if ($request->filled('maquina_id')) $query->where('maquina_id', $request->maquina_id);
        // Aplica filtro por tipo de manutenção (preventiva ou corretiva) se preenchido
        if ($request->filled('tipo'))       $query->where('tipo', $request->tipo);
        // Aplica filtro por técnico responsável se o campo foi preenchido
        if ($request->filled('tecnico_id')) $query->where('tecnico_id', $request->tecnico_id);
        // Aplica filtro de data inicial: retorna apenas registros a partir desta data
        if ($request->filled('data_inicio')) $query->whereDate('data_inicio', '>=', $request->data_inicio);
        // Aplica filtro de data final: retorna apenas registros até esta data
        if ($request->filled('data_fim'))    $query->whereDate('data_inicio', '<=', $request->data_fim);

        // Executa a query ordenando pelos mais recentes e pagina em grupos de 20
        $historicos = $query->latest()->paginate(20);

        // Busca todas as máquinas ordenadas por modelo para popular o dropdown de filtro
        $maquinas   = Maquina::orderBy('modelo')->get();
        // Busca todos os técnicos ordenados por nome para popular o dropdown de filtro
        $tecnicos   = Tecnico::orderBy('nome')->get();

        // Retorna a view de listagem passando históricos paginados e listas de filtros
        return view('historico.index', compact('historicos', 'maquinas', 'tecnicos'));
    } // fim do método index

    /**
     * FUNÇÃO: show($id)
     * ENTRADA: $id (string) - ID do registro de histórico
     * PROCESSAMENTO: Busca histórico com maquina, tecnico e ordem relacionados
     * SAÍDA: View com detalhes completos do histórico
     * USO: GET /historico/{id}
     */
    public function show(string $id)
    {
        // Busca o histórico pelo ID com todos os relacionamentos; lança 404 se não encontrar
        $historico = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem'])->findOrFail($id);
        // Retorna a view de detalhes passando o registro de histórico completo
        return view('historico.show', compact('historico'));
    } // fim do método show

    /**
     * FUNÇÃO: porMaquina($maquinaId)
     * ENTRADA: $maquinaId (string) - ID da máquina
     * PROCESSAMENTO:
     *   1. Busca máquina
     *   2. Busca histórico da máquina paginado
     *   3. Calcula reincidências: conta manutenções corretivas agrupadas por mês/ano
     *      (ajuda a identificar se máquina tem problemas recorrentes)
     * SAÍDA: View com histórico completo da máquina + análise de reincidências
     * USO: GET /historico/maquina/{maquinaId}
     */
    public function porMaquina(string $maquinaId)
    {
        // Busca a máquina pelo ID; lança 404 se não existir
        $maquina    = Maquina::findOrFail($maquinaId);

        // Busca o histórico desta máquina com técnico e O.S. vinculada, paginado em 20 registros
        $historicos = HistoricoManutencao::with(['tecnico', 'ordem'])
            ->where('maquina_id', $maquinaId)->latest()->paginate(20);

        // Calcula reincidências de manutenções corretivas agrupadas por mês e ano
        // Permite identificar se a máquina apresenta falhas repetitivas (problema crônico)
        $reincidencias = HistoricoManutencao::where('maquina_id', $maquinaId)
            // Considera apenas manutenções corretivas (indicam falha real)
            ->where('tipo', 'corretiva')
            // Seleciona contagem e extrai mês e ano da data de início para agrupamento
            ->selectRaw('COUNT(*) as total, MONTH(data_inicio) as mes, YEAR(data_inicio) as ano')
            // Agrupa por mês e ano para calcular quantas corretivas ocorreram em cada período
            ->groupByRaw('MONTH(data_inicio), YEAR(data_inicio)')
            // Ordena do período mais recente para o mais antigo
            ->orderByRaw('ano DESC, mes DESC')->get();

        // Retorna a view com os dados da máquina, histórico paginado e análise de reincidências
        return view('historico.por-maquina', compact('maquina', 'historicos', 'reincidencias'));
    } // fim do método porMaquina

    /**
     * FUNÇÃO: exportar($request)
     * ENTRADA: Request com mesmos filtros de index
     * PROCESSAMENTO:
     *   1. Aplica filtros iguais ao index
     *   2. Busca TODOS os históricos (sem paginar)
     *   3. Cria CSV em memória e envia como download
     * SAÍDA: Stream CSV com nome "historico-manutencoes-YYYY-MM-DD.csv"
     * USO: GET /historico/exportar
     */
    public function exportar(Request $request)
    {
        // Inicia a query base com eager loading para garantir dados completos no CSV
        $query = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem']);
        // Aplica filtro por máquina se preenchido
        if ($request->filled('maquina_id')) $query->where('maquina_id', $request->maquina_id);
        // Aplica filtro por tipo de manutenção se preenchido
        if ($request->filled('tipo'))       $query->where('tipo', $request->tipo);
        // Aplica filtro por técnico se preenchido
        if ($request->filled('tecnico_id')) $query->where('tecnico_id', $request->tecnico_id);
        // Aplica filtro de data inicial se preenchido
        if ($request->filled('data_inicio')) $query->whereDate('data_inicio', '>=', $request->data_inicio);
        // Aplica filtro de data final se preenchido
        if ($request->filled('data_fim'))    $query->whereDate('data_inicio', '<=', $request->data_fim);
        // Executa a query sem paginação para exportar todos os registros filtrados
        $historicos = $query->latest()->get();

        // Retorna resposta como stream de download para não sobrecarregar a memória do servidor
        return response()->streamDownload(function () use ($historicos) {
            // Abre o output padrão do PHP como arquivo para escrita do CSV
            $f = fopen('php://output', 'w');
            // Escreve o BOM UTF-8 para garantir compatibilidade com Excel
            fputs($f, "\xEF\xBB\xBF");
            // Escreve a linha de cabeçalho do CSV com separador ponto e vírgula
            fputcsv($f, ['ID','Máquina','Tipo','Técnico','O.S. Vinculada','Início','Fim','Parada (h)','Custo (R$)'], ';');
            // Itera sobre todos os históricos e escreve cada um como uma linha no CSV
            foreach ($historicos as $h) {
                // Escreve os dados do histórico atual como uma linha no arquivo CSV
                fputcsv($f, [
                    // ID único do registro de histórico
                    $h->id,
                    // Modelo da máquina ou vazio se não houver
                    $h->maquina->modelo ?? '',
                    // Tipo com primeira letra maiúscula (ex: "Preventiva" ou "Corretiva")
                    ucfirst($h->tipo),
                    // Nome do técnico responsável ou vazio se não houver
                    $h->tecnico->nome ?? '',
                    // Número da O.S. vinculada ou vazio se não houver
                    $h->ordem->numero ?? '',
                    // Data e hora de início da manutenção formatada para pt-BR
                    $h->data_inicio->format('d/m/Y H:i'),
                    // Data e hora de fim da manutenção formatada ou vazio se não concluída
                    $h->data_fim ? $h->data_fim->format('d/m/Y H:i') : '',
                    // Horas de parada formatadas com 1 decimal ou vazio se zero
                    $h->tempo_parada_horas > 0 ? number_format($h->tempo_parada_horas, 1, ',', '.') : '',
                    // Custo formatado com 2 casas decimais em reais ou vazio se zero
                    $h->custo > 0 ? number_format($h->custo, 2, ',', '.') : '',
                ], ';'); // fim do fputcsv do histórico atual
            } // fim do foreach de históricos
            // Fecha o arquivo/stream após concluir toda a escrita
            fclose($f);
        // Define o nome do arquivo CSV exportado com a data atual
        }, 'historico-manutencoes-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    } // fim do método exportar

    /**
     * FUNÇÃO: store($request)
     * ENTRADA: Dados de um novo registro de histórico
     * PROCESSAMENTO: Valida e cria novo HistoricoManutencao (geralmente criado automaticamente)
     * SAÍDA: Redirecionamento com sucesso
     * USO: POST /historico (raro, pois históricos são criados ao concluir O.S.)
     */
    public function store(Request $request)
    {
        // Valida todos os campos do formulário de criação manual de histórico
        $data = $request->validate([
            // ID da máquina: deve existir na tabela maquinas
            'maquina_id'         => 'required|exists:maquinas,id',
            // ID do técnico responsável: deve existir na tabela tecnicos
            'tecnico_id'         => 'required|exists:tecnicos,id',
            // ID da O.S. vinculada (opcional, pois pode ser registro manual sem O.S.)
            'ordem_id'           => 'nullable|exists:ordens_servico,id',
            // Tipo da manutenção: preventiva ou corretiva
            'tipo'               => 'required|in:preventiva,corretiva',
            // Descrição detalhada do serviço ou problema
            'descricao'          => 'required|string',
            // Solução aplicada (pode ser nula se ainda em aberto)
            'solucao'            => 'nullable|string',
            // Lista de peças utilizadas na manutenção (opcional)
            'pecas_utilizadas'   => 'nullable|string',
            // Horas que a máquina ficou parada (impacto na produção)
            'tempo_parada_horas' => 'nullable|numeric|min:0',
            // Custo total da manutenção
            'custo'              => 'nullable|numeric|min:0',
            // Data e hora de início da manutenção (obrigatória)
            'data_inicio'        => 'required|date',
            // Data e hora de fim (deve ser igual ou posterior ao início)
            'data_fim'           => 'nullable|date|after_or_equal:data_inicio',
            // Observações adicionais sobre a manutenção (opcional)
            'observacoes'        => 'nullable|string',
        ]); // fim da validação
        // Persiste o novo registro de histórico com todos os dados validados
        HistoricoManutencao::create($data);
        // Redireciona para a listagem com mensagem de confirmação de criação
        return redirect()->route('historico.index')->with('success', 'Registro adicionado ao histórico!');
    } // fim do método store

    /**
     * FUNÇÃO: destroy($id)
     * ENTRADA: $id (string) - ID do histórico a deletar
     * PROCESSAMENTO: Deleta registro de HistoricoManutencao
     * SAÍDA: Redirecionamento com sucesso
     * USO: DELETE /historico/{id}
     */
    public function destroy(string $id)
    {
        // Busca o histórico pelo ID e o deleta; lança 404 se não encontrar
        HistoricoManutencao::findOrFail($id)->delete();
        // Redireciona para a listagem com mensagem de confirmação de exclusão
        return redirect()->route('historico.index')->with('success', 'Registro excluído.');
    } // fim do método destroy
} // fim da classe HistoricoController
