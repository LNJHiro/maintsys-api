<?php

/**
 * CONTROLLER: DashboardController
 *
 * Responsável por exibir o dashboard principal do sistema.
 * Fornece visão geral de:
 * - Quantidade de máquinas por status
 * - Quantidade de técnicos ativos
 * - Quantidade de O.S. por status
 * - Ordens pendentes (abertas/em andamento)
 * - Alertas de máquinas em parada crítica
 * - Histórico recente de manutenções
 *
 * Método único: index() - retorna view com todos os dados agregados
 */

namespace App\Http\Controllers;

use App\Models\Maquina;
use App\Models\Tecnico;
use App\Models\OrdemServico;
use App\Models\HistoricoManutencao;

class DashboardController extends Controller
{
    /**
     * FUNÇÃO: index()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Calcula estatísticas gerais do sistema:
     *      - Total de máquinas e contagem por status
     *      - Técnicos ativos no sistema
     *      - O.S. abertas, em andamento e concluídas hoje
     *   2. Busca máquinas em parada crítica com O.S. ativas
     *   3. Busca 8 O.S. pendentes ordenadas por prioridade
     *   4. Busca 5 últimas manutenções realizadas
     *   5. Passa todos os dados para view do dashboard
     * SAÍDA: View 'dashboard' com dados agregados para exibição
     * USO: GET /dashboard (rota protegida por auth)
     */
    public function index()
    {
        // Coleta estatísticas gerais para exibição em cards
        $stats = [
            'maquinas_total'      => Maquina::count(),                                      // Total de máquinas
            'operacionais'        => Maquina::operacional()->count(),                      // Status operacional
            'em_manutencao'       => Maquina::emManutencao()->count(),                     // Status em manutenção
            'parada_critica'      => Maquina::paradaCritica()->count(),                    // Status parada crítica
            'tecnicos_ativos'     => Tecnico::where('ativo', true)->count(),               // Técnicos ativos
            'os_abertas'          => OrdemServico::where('status', 'aberta')->count(),     // O.S. aguardando execução
            'os_em_andamento'     => OrdemServico::where('status', 'em_andamento')->count(), // O.S. sendo executadas
            'os_concluidas_hoje'  => OrdemServico::where('status', 'concluida')           // O.S. finalizadas hoje
                                        ->whereDate('data_conclusao', today())->count(),
        ];

        // Busca máquinas em parada crítica para exibir alertas
        // Inclui O.S. ativas (aberta/em_andamento) relacionadas
        $alertas = Maquina::paradaCritica()
            ->with(['ordens' => fn($q) => $q->whereIn('status', ['aberta', 'em_andamento'])])
            ->get();

        // Busca ordens pendentes (abertas ou sendo executadas) ordenadas por prioridade (críticas primeiro)
        $ordensRecentes = OrdemServico::with(['maquina', 'tecnico'])
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->orderBy('prioridade', 'desc')  // Críticas aparecem primeiro
            ->limit(8)  // Mostra apenas 8 mais importantes
            ->get();

        // Busca últimas 5 manutenções realizadas para exibição no histórico
        $ultimasManutencoes = HistoricoManutencao::with(['maquina', 'tecnico'])
            ->latest()  // Ordena pelas mais recentes
            ->limit(5)
            ->get();

        // Retorna view com todos os dados agregados
        return view('dashboard', compact('stats', 'alertas', 'ordensRecentes', 'ultimasManutencoes'));
    }
}