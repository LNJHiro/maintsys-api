<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistoricoManutencao;
use App\Models\Maquina;
use App\Models\Tecnico;

class HistoricoController extends Controller
{
    public function index(Request $request)
    {
        $query = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem']);
        if ($request->filled('maquina_id')) $query->where('maquina_id', $request->maquina_id);
        if ($request->filled('tipo'))       $query->where('tipo', $request->tipo);
        if ($request->filled('tecnico_id')) $query->where('tecnico_id', $request->tecnico_id);
        if ($request->filled('data_inicio')) $query->whereDate('data_inicio', '>=', $request->data_inicio);
        if ($request->filled('data_fim'))    $query->whereDate('data_inicio', '<=', $request->data_fim);
        $historicos = $query->latest()->paginate(20);
        $maquinas   = Maquina::orderBy('modelo')->get();
        $tecnicos   = Tecnico::orderBy('nome')->get();
        return view('historico.index', compact('historicos', 'maquinas', 'tecnicos'));
    }

    public function show(string $id)
    {
        $historico = HistoricoManutencao::with(['maquina', 'tecnico', 'ordem'])->findOrFail($id);
        return view('historico.show', compact('historico'));
    }

    public function porMaquina(string $maquinaId)
    {
        $maquina    = Maquina::findOrFail($maquinaId);
        $historicos = HistoricoManutencao::with(['tecnico', 'ordem'])
            ->where('maquina_id', $maquinaId)->latest()->paginate(20);
        $reincidencias = HistoricoManutencao::where('maquina_id', $maquinaId)
            ->where('tipo', 'corretiva')
            ->selectRaw('COUNT(*) as total, MONTH(data_inicio) as mes, YEAR(data_inicio) as ano')
            ->groupByRaw('MONTH(data_inicio), YEAR(data_inicio)')
            ->orderByRaw('ano DESC, mes DESC')->get();
        return view('historico.por-maquina', compact('maquina', 'historicos', 'reincidencias'));
    }

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
            'data_fim'           => 'nullable|date',
            'observacoes'        => 'nullable|string',
        ]);
        HistoricoManutencao::create($data);
        return redirect()->route('historico.index')->with('success', 'Registro adicionado ao histórico!');
    }

    public function destroy(string $id)
    {
        HistoricoManutencao::findOrFail($id)->delete();
        return redirect()->route('historico.index')->with('success', 'Registro excluído.');
    }
}