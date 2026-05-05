<?php

namespace App\Http\Controllers;

use App\Models\Maquina;
use App\Models\Tecnico;
use App\Models\OrdemServico;
use App\Models\HistoricoManutencao;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'maquinas_total'      => Maquina::count(),
            'operacionais'        => Maquina::operacional()->count(),
            'em_manutencao'       => Maquina::emManutencao()->count(),
            'parada_critica'      => Maquina::paradaCritica()->count(),
            'tecnicos_ativos'     => Tecnico::where('ativo', true)->count(),
            'os_abertas'          => OrdemServico::where('status', 'aberta')->count(),
            'os_em_andamento'     => OrdemServico::where('status', 'em_andamento')->count(),
            'os_concluidas_hoje'  => OrdemServico::where('status', 'concluida')
                                        ->whereDate('data_conclusao', today())->count(),
        ];

        // Máquinas em parada crítica (alertas)
        $alertas = Maquina::paradaCritica()
            ->with(['ordens' => fn($q) => $q->whereIn('status', ['aberta', 'em_andamento'])])
            ->get();

        // Ordens recentes
        $ordensRecentes = OrdemServico::with(['maquina', 'tecnico'])
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->orderBy('prioridade', 'desc')
            ->limit(8)
            ->get();

        // Últimos históricos
        $ultimasManutencoes = HistoricoManutencao::with(['maquina', 'tecnico'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'alertas', 'ordensRecentes', 'ultimasManutencoes'));
    }
} 