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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistoricoManutencao;
use App\Models\Maquina;
use App\Models\Tecnico;

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
        // Constrói query base com eager loading
        $query = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem']);

        // Aplica filtros dinamicamente se foram preenchidos no formulário
        if ($request->filled('maquina_id')) $query->where('maquina_id', $request->maquina_id);
        if ($request->filled('tipo'))       $query->where('tipo', $request->tipo);
        if ($request->filled('tecnico_id')) $query->where('tecnico_id', $request->tecnico_id);
        if ($request->filled('data_inicio')) $query->whereDate('data_inicio', '>=', $request->data_inicio);
        if ($request->filled('data_fim'))    $query->whereDate('data_inicio', '<=', $request->data_fim);

        // Executa query e pagina resultados
        $historicos = $query->latest()->paginate(20);

        // Busca opções para dropdowns de filtro
        $maquinas   = Maquina::orderBy('modelo')->get();
        $tecnicos   = Tecnico::orderBy('nome')->get();

        return view('historico.index', compact('historicos', 'maquinas', 'tecnicos'));
    }

    /**
     * FUNÇÃO: show($id)
     * ENTRADA: $id (string) - ID do registro de histórico
     * PROCESSAMENTO: Busca histórico com maquina, tecnico e ordem relacionados
     * SAÍDA: View com detalhes completos do histórico
     * USO: GET /historico/{id}
     */
    public function show(string $id)
    {
        $historico = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem'])->findOrFail($id);
        return view('historico.show', compact('historico'));
    }

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
        $maquina    = Maquina::findOrFail($maquinaId);

        // Busca histórico paginado desta máquina
        $historicos = HistoricoManutencao::with(['tecnico', 'ordem'])
            ->where('maquina_id', $maquinaId)->latest()->paginate(20);

        // Calcula reincidências: quantas manutenções corretivas por mês
        // Ajuda a identificar se máquina tem problemas crônicos
        $reincidencias = HistoricoManutencao::where('maquina_id', $maquinaId)
            ->where('tipo', 'corretiva')
            ->selectRaw('COUNT(*) as total, MONTH(data_inicio) as mes, YEAR(data_inicio) as ano')
            ->groupByRaw('MONTH(data_inicio), YEAR(data_inicio)')
            ->orderByRaw('ano DESC, mes DESC')->get();

        return view('historico.por-maquina', compact('maquina', 'historicos', 'reincidencias'));
    }

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
        // Constrói query com mesmos filtros do index
        $query = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem']);
        if ($request->filled('maquina_id')) $query->where('maquina_id', $request->maquina_id);
        if ($request->filled('tipo'))       $query->where('tipo', $request->tipo);
        if ($request->filled('tecnico_id')) $query->where('tecnico_id', $request->tecnico_id);
        if ($request->filled('data_inicio')) $query->whereDate('data_inicio', '>=', $request->data_inicio);
        if ($request->filled('data_fim'))    $query->whereDate('data_inicio', '<=', $request->data_fim);
        $historicos = $query->latest()->get();  // Sem paginação para exportar tudo

        return response()->streamDownload(function () use ($historicos) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF");  // BOM UTF-8
            fputcsv($f, ['ID','Máquina','Tipo','Técnico','O.S. Vinculada','Início','Fim','Parada (h)','Custo (R$)'], ';');
            foreach ($historicos as $h) {
                fputcsv($f, [
                    $h->id,
                    $h->maquina->modelo ?? '',
                    ucfirst($h->tipo),
                    $h->tecnico->nome ?? '',
                    $h->ordem->numero ?? '',
                    $h->data_inicio->format('d/m/Y H:i'),
                    $h->data_fim ? $h->data_fim->format('d/m/Y H:i') : '',
                    $h->tempo_parada_horas > 0 ? number_format($h->tempo_parada_horas, 1, ',', '.') : '',
                    $h->custo > 0 ? number_format($h->custo, 2, ',', '.') : '',
                ], ';');
            }
            fclose($f);
        }, 'historico-manutencoes-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * FUNÇÃO: store($request)
     * ENTRADA: Dados de um novo registro de histórico
     * PROCESSAMENTO: Valida e cria novo HistoricoManutencao (geralmente criado automaticamente)
     * SAÍDA: Redirecionamento com sucesso
     * USO: POST /historico (raro, pois históricos são criados ao concluir O.S.)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'maquina_id'         => 'required|exists:maquinas,id',
            'tecnico_id'         => 'required|exists:tecnicos,id',
            'ordem_id'           => 'nullable|exists:ordens_servico,id',
            'tipo'               => 'required|in:preventiva,corretiva',
            'descricao'          => 'required|string',
            'solucao'            => 'nullable|string',
            'pecas_utilizadas'   => 'nullable|string',
            'tempo_parada_horas' => 'nullable|numeric|min:0',
            'custo'              => 'nullable|numeric|min:0',
            'data_inicio'        => 'required|date',
            'data_fim'           => 'nullable|date|after_or_equal:data_inicio',
            'observacoes'        => 'nullable|string',
        ]);
        HistoricoManutencao::create($data);
        return redirect()->route('historico.index')->with('success', 'Registro adicionado ao histórico!');
    }

    /**
     * FUNÇÃO: destroy($id)
     * ENTRADA: $id (string) - ID do histórico a deletar
     * PROCESSAMENTO: Deleta registro de HistoricoManutencao
     * SAÍDA: Redirecionamento com sucesso
     * USO: DELETE /historico/{id}
     */
    public function destroy(string $id)
    {
        HistoricoManutencao::findOrFail($id)->delete();
        return redirect()->route('historico.index')->with('success', 'Registro excluído.');
    }
}
