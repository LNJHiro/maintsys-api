<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\Maquina;
use App\Models\Tecnico;
use App\Models\HistoricoManutencao;

class OrdemServicoController extends Controller
{
    public function index()
    {
        $ordens = OrdemServico::with(['maquina', 'tecnico'])->latest()->paginate(15);
        $stats = [
            'abertas'      => OrdemServico::where('status', 'aberta')->count(),
            'em_andamento' => OrdemServico::where('status', 'em_andamento')->count(),
            'concluidas'   => OrdemServico::where('status', 'concluida')->count(),
            'criticas'     => OrdemServico::where('prioridade', 'critica')->whereIn('status', ['aberta', 'em_andamento'])->count(),
        ];
        return view('ordens.index', compact('ordens', 'stats'));
    }

    public function create()
    {
        $maquinas = Maquina::orderBy('modelo')->get();
        $tecnicos = Tecnico::where('ativo', true)->orderBy('nome')->get();
        return view('ordens.create', compact('maquinas', 'tecnicos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'          => 'required|in:preventiva,corretiva',
            'prioridade'    => 'required|in:baixa,media,alta,critica',
            'descricao'     => 'required|string',
            'maquina_id'    => 'required|exists:maquinas,id',
            'tecnico_id'    => 'required|exists:tecnicos,id',
            'data_prevista' => 'nullable|date',
        ]);
        $data['numero']        = OrdemServico::gerarNumero();
        $data['status']        = 'aberta';
        $data['data_abertura'] = now();
        $ordem = OrdemServico::create($data);
        $maquina = Maquina::find($data['maquina_id']);
        if ($maquina->status === 'operacional') {
            $maquina->update(['status' => 'em_manutencao']);
            session()->flash('alerta', "🔧 Máquina {$maquina->modelo} passou para Em Manutenção.");
        }
        return redirect()->route('ordens.index')->with('success', "O.S. {$ordem->numero} criada com sucesso!");
    }

    public function show(string $id)
    {
        $ordem = OrdemServico::with(['maquina', 'tecnico', 'historico'])->findOrFail($id);
        return view('ordens.show', compact('ordem'));
    }

    public function edit(string $id)
    {
        $ordem    = OrdemServico::findOrFail($id);
        $maquinas = Maquina::orderBy('modelo')->get();
        $tecnicos = Tecnico::where('ativo', true)->orderBy('nome')->get();
        return view('ordens.edit', compact('ordem', 'maquinas', 'tecnicos'));
    }

    public function update(Request $request, string $id)
    {
        $ordem = OrdemServico::findOrFail($id);
        $statusAnterior = $ordem->status;
        $data = $request->validate([
            'tipo'          => 'required|in:preventiva,corretiva',
            'prioridade'    => 'required|in:baixa,media,alta,critica',
            'status'        => 'required|in:aberta,em_andamento,concluida,cancelada',
            'descricao'     => 'required|string',
            'solucao'       => 'nullable|string',
            'maquina_id'    => 'required|exists:maquinas,id',
            'tecnico_id'    => 'required|exists:tecnicos,id',
            'data_prevista' => 'nullable|date',
        ]);
        if ($data['status'] === 'concluida' && $statusAnterior !== 'concluida') {
            $data['data_conclusao'] = now();
            HistoricoManutencao::create([
    'maquina_id'         => $ordem->maquina_id,
    'tecnico_id'         => $ordem->tecnico_id,
    'ordem_id'           => $ordem->id,
    'tipo'               => $ordem->tipo,
    'descricao'          => $ordem->descricao,
    'solucao'            => $data['solucao'] ?? null,
    'tempo_parada_horas' => $request->input('tempo_parada_horas', 0),
    'custo'              => $request->input('custo', 0),
    'pecas_utilizadas'   => $request->input('pecas_utilizadas'),
    'data_inicio'        => $ordem->data_abertura,
    'data_fim'           => now(),
]);
            $maquina = Maquina::find($ordem->maquina_id);
            $osAbertas = OrdemServico::where('maquina_id', $maquina->id)
                ->whereIn('status', ['aberta', 'em_andamento'])
                ->where('id', '!=', $ordem->id)->count();
            if ($osAbertas === 0) {
                $maquina->update(['status' => 'operacional']);
                session()->flash('alerta', "✅ {$maquina->modelo} voltou a ser Operacional.");
            }
        }
        $ordem->update($data);
        return redirect()->route('ordens.index')->with('success', "O.S. {$ordem->numero} atualizada!");
    }

    public function destroy(string $id)
    {
        OrdemServico::findOrFail($id)->delete();
        return redirect()->route('ordens.index')->with('success', 'Ordem de Serviço excluída.');
    }
}