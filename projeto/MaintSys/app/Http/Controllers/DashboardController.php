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

// Define o namespace do controller dentro da estrutura do Laravel
namespace App\Http\Controllers;

// Importa o model Maquina para consultar dados de máquinas
use App\Models\Maquina;
// Importa o model Tecnico para consultar dados de técnicos
use App\Models\Tecnico;
// Importa o model OrdemServico para consultar ordens de serviço
use App\Models\OrdemServico;
// Importa o model HistoricoManutencao para consultar histórico de manutenções
use App\Models\HistoricoManutencao;

// Declara o controller que herda funcionalidades base do Controller do Laravel
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
        // Monta array associativo com as estatísticas gerais para exibição nos cards do dashboard
        $stats = [
            // Conta o total absoluto de máquinas cadastradas no sistema
            'maquinas_total'      => Maquina::count(),
            // Conta máquinas com status 'operacional' usando o scope local do model
            'operacionais'        => Maquina::operacional()->count(),
            // Conta máquinas com status 'em_manutencao' usando o scope local do model
            'em_manutencao'       => Maquina::emManutencao()->count(),
            // Conta máquinas com status 'parada_critica' usando o scope local do model
            'parada_critica'      => Maquina::paradaCritica()->count(),
            // Conta técnicos com o campo 'ativo' igual a true (técnicos habilitados)
            'tecnicos_ativos'     => Tecnico::where('ativo', true)->count(),
            // Conta ordens de serviço com status 'aberta' (aguardando atendimento)
            'os_abertas'          => OrdemServico::where('status', 'aberta')->count(),
            // Conta ordens de serviço com status 'em_andamento' (sendo executadas agora)
            'os_em_andamento'     => OrdemServico::where('status', 'em_andamento')->count(),
            // Conta ordens de serviço concluídas cuja data de conclusão é a data de hoje
            'os_concluidas_hoje'  => OrdemServico::where('status', 'concluida')
                                        ->whereDate('data_conclusao', today())->count(),
        ]; // fim do array $stats

        // Busca as máquinas em parada crítica para gerar alertas visuais no dashboard
        // Utiliza eager loading com closure para trazer apenas as O.S. ainda ativas de cada máquina
        $alertas = Maquina::paradaCritica()
            // Carrega relacionamento 'ordens' filtrando apenas status 'aberta' ou 'em_andamento'
            ->with(['ordens' => fn($q) => $q->whereIn('status', ['aberta', 'em_andamento'])])
            // Executa a query e retorna coleção de máquinas em alerta
            ->get();

        // Busca as ordens de serviço pendentes (abertas ou em andamento) para exibir na listagem do dashboard
        $ordensRecentes = OrdemServico::with(['maquina', 'tecnico'])
            // Filtra apenas ordens que ainda não foram concluídas
            ->whereIn('status', ['aberta', 'em_andamento'])
            // Ordena por prioridade decrescente: ordens críticas aparecem no topo
            ->orderBy('prioridade', 'desc')
            // Limita a 8 registros para não sobrecarregar a tela do dashboard
            ->limit(8)
            // Executa a consulta e retorna coleção de ordens
            ->get();

        // Busca as últimas manutenções realizadas para exibir no bloco de histórico recente
        $ultimasManutencoes = HistoricoManutencao::with(['maquina', 'tecnico'])
            // Ordena pelos registros mais recentes (usa coluna created_at decrescente)
            ->latest()
            // Limita a 5 registros para exibir apenas o histórico mais recente
            ->limit(5)
            // Executa a consulta e retorna coleção de históricos
            ->get();

        // Retorna a view do dashboard passando todas as variáveis coletadas via compact
        return view('dashboard', compact('stats', 'alertas', 'ordensRecentes', 'ultimasManutencoes'));
    } // fim do método index
} // fim da classe DashboardController
