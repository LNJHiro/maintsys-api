<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maquina;

class MaquinaController extends Controller
{
    public function index()
    {
        $maquinas = Maquina::withCount('ordens')->latest()->paginate(15);
        $stats = [
            'total'          => Maquina::count(),
            'operacional'    => Maquina::operacional()->count(),
            'em_manutencao'  => Maquina::emManutencao()->count(),
            'parada_critica' => Maquina::paradaCritica()->count(),
        ];
        return view('maquinas.index', compact('maquinas', 'stats'));
    }

    public function create()
    {
        return view('maquinas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_serie'    => 'required|string|max:100|unique:maquinas,numero_serie',
            'modelo'          => 'required|string|max:255',
            'fabricante'      => 'nullable|string|max:255',
            'localizacao'     => 'required|string|max:255',
            'data_instalacao' => 'nullable|date',
            'status'          => 'required|in:operacional,em_manutencao,parada_critica,inativa',
            'descricao'       => 'nullable|string',
        ]);
        $maquina = Maquina::create($data);
        if ($maquina->status === 'parada_critica') {
            session()->flash('alerta', "⚠️ Máquina {$maquina->modelo} cadastrada em Parada Crítica!");
        }
        return redirect()->route('maquinas.index')->with('success', 'Máquina cadastrada com sucesso!');
    }

    public function show(string $id)
    {
        $maquina = Maquina::with(['ordens.tecnico', 'historicos.tecnico'])->findOrFail($id);
        return view('maquinas.show', compact('maquina'));
    }

    public function edit(string $id)
    {
        $maquina = Maquina::findOrFail($id);
        return view('maquinas.edit', compact('maquina'));
    }

    public function update(Request $request, string $id)
    {
        $maquina = Maquina::findOrFail($id);
        $statusAnterior = $maquina->status;
        $data = $request->validate([
            'numero_serie'    => 'required|string|max:100|unique:maquinas,numero_serie,' . $id,
            'modelo'          => 'required|string|max:255',
            'fabricante'      => 'nullable|string|max:255',
            'localizacao'     => 'required|string|max:255',
            'data_instalacao' => 'nullable|date',
            'status'          => 'required|in:operacional,em_manutencao,parada_critica,inativa',
            'descricao'       => 'nullable|string',
        ]);
        $maquina->update($data);
        if ($statusAnterior !== $maquina->status) {
            $msg = match($maquina->status) {
                'parada_critica' => "🚨 {$maquina->modelo} entrou em Parada Crítica!",
                'operacional'    => "✅ {$maquina->modelo} voltou a ser Operacional!",
                'em_manutencao'  => "🔧 {$maquina->modelo} está em Manutenção.",
                default          => "ℹ️ Status da {$maquina->modelo} atualizado.",
            };
            session()->flash('alerta', $msg);
        }
        return redirect()->route('maquinas.index')->with('success', 'Máquina atualizada com sucesso!');
    }

    public function destroy(string $id)
    {
        $maquina = Maquina::findOrFail($id);
        if ($maquina->ordens()->exists()) {
            return redirect()->route('maquinas.index')
                ->with('error', 'Não é possível excluir: existem Ordens de Serviço vinculadas.');
        }
        $maquina->delete();
        return redirect()->route('maquinas.index')->with('success', 'Máquina excluída com sucesso!');
    }
}