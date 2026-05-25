<?php

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

        $alertas = [];

        $ordem = DB::transaction(function () use ($data, &$alertas) {
            $data['numero']        = OrdemServico::gerarNumero();
            $data['status']        = 'aberta';
            $data['data_abertura'] = now();

            $ordem = OrdemServico::create($data);

            if ($alerta = $this->sincronizarStatusMaquina($ordem->maquina_id)) {
                $alertas[] = $alerta;
            }

            return $ordem;
        });

        if (!empty($alertas)) {
            session()->flash('alerta', implode(' | ', $alertas));
        }

        $this->notificarTecnicoAtribuido($ordem);

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
        $ordem = OrdemServico::with('historico')->findOrFail($id);
        $statusAnterior = $ordem->status;
        $maquinaAnteriorId = $ordem->maquina_id;
        $tecnicoAnteriorId = $ordem->tecnico_id;

        $data = $request->validate([
            'tipo'                => 'required|in:preventiva,corretiva',
            'prioridade'          => 'required|in:baixa,media,alta,critica',
            'status'              => 'required|in:aberta,em_andamento,concluida,cancelada',
            'descricao'           => 'required|string',
            'solucao'             => 'nullable|string',
            'maquina_id'          => 'required|exists:maquinas,id',
            'tecnico_id'          => 'required|exists:tecnicos,id',
            'data_prevista'       => 'nullable|date',
            'proxima_preventiva'  => 'nullable|date|after_or_equal:today',
            'tempo_parada_horas'  => 'nullable|numeric|min:0',
            'custo'               => 'nullable|numeric|min:0',
            'pecas_utilizadas'    => 'nullable|string',
        ]);

        if ($statusAnterior === 'concluida' && $data['status'] !== 'concluida') {
            return back()->withInput()->with('error',
                'Nao e possivel alterar o status de uma O.S. ja concluida. Crie uma nova O.S. se necessario.');
        }

        $proximaPreventiva = $data['proxima_preventiva'] ?? null;
        $dadosConclusao = [
            'tempo_parada_horas' => $data['tempo_parada_horas'] ?? 0,
            'custo'              => $data['custo'] ?? 0,
            'pecas_utilizadas'   => $data['pecas_utilizadas'] ?? null,
        ];

        unset(
            $data['proxima_preventiva'],
            $data['tempo_parada_horas'],
            $data['custo'],
            $data['pecas_utilizadas'],
        );

        $alertas = [];
        $ordensParaNotificar = [];

        DB::transaction(function () use ($ordem, $statusAnterior, $maquinaAnteriorId, $tecnicoAnteriorId, &$data, $dadosConclusao, $proximaPreventiva, &$alertas, &$ordensParaNotificar) {
            $concluindoAgora = $data['status'] === 'concluida' && $statusAnterior !== 'concluida';

            if ($concluindoAgora) {
                $data['data_conclusao'] = now();
            }

            $ordem->update($data);
            $ordem->refresh();

            if ((int) $tecnicoAnteriorId !== (int) $ordem->tecnico_id) {
                $ordensParaNotificar[] = $ordem->fresh();
            }

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
                $ordem->historico->update([
                    'maquina_id' => $ordem->maquina_id,
                    'tecnico_id' => $ordem->tecnico_id,
                    'tipo'       => $ordem->tipo,
                    'descricao'  => $ordem->descricao,
                    'solucao'    => $ordem->solucao,
                ]);
            }

            foreach (array_unique([$maquinaAnteriorId, $ordem->maquina_id]) as $maquinaId) {
                if ($alerta = $this->sincronizarStatusMaquina($maquinaId)) {
                    $alertas[] = $alerta;
                }
            }

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

        if (!empty($alertas)) {
            session()->flash('alerta', implode(' | ', $alertas));
        }

        foreach ($ordensParaNotificar as $ordemAtribuida) {
            $this->notificarTecnicoAtribuido($ordemAtribuida);
        }

        return redirect()->route('ordens.index')->with('success', "O.S. {$ordem->numero} atualizada!");
    }

    public function destroy(string $id)
    {
        $ordem = OrdemServico::findOrFail($id);
        $maquinaId = $ordem->maquina_id;
        $alertas = [];

        DB::transaction(function () use ($ordem, $maquinaId, &$alertas) {
            $ordem->delete();

            if ($alerta = $this->sincronizarStatusMaquina($maquinaId)) {
                $alertas[] = $alerta;
            }
        });

        if (!empty($alertas)) {
            session()->flash('alerta', implode(' | ', $alertas));
        }

        return redirect()->route('ordens.index')->with('success', 'Ordem de Servico excluida.');
    }

    private function sincronizarStatusMaquina(int $maquinaId): ?string
    {
        $maquina = Maquina::find($maquinaId);

        if (!$maquina) {
            return null;
        }

        $temOrdemAtiva = OrdemServico::where('maquina_id', $maquinaId)
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->where(function ($query) {
                $query->where('status', 'em_andamento')
                    ->orWhere('tipo', 'corretiva')
                    ->orWhereNull('data_prevista')
                    ->orWhereDate('data_prevista', '<=', today());
            })
            ->exists();

        if ($temOrdemAtiva && $maquina->status === 'operacional') {
            $maquina->update(['status' => 'em_manutencao']);

            return "Maquina {$maquina->modelo} passou para Em Manutencao.";
        }

        if (!$temOrdemAtiva && $maquina->status === 'em_manutencao') {
            $maquina->update(['status' => 'operacional']);

            return "Maquina {$maquina->modelo} voltou a ser Operacional.";
        }

        return null;
    }

    private function notificarTecnicoAtribuido(OrdemServico $ordem): void
    {
        $ordem->loadMissing(['maquina', 'tecnico.user']);

        $user = $ordem->tecnico?->user;

        if (!$user) {
            return;
        }

        $user->notify(new OrdemServicoAtribuida($ordem));
    }
}
