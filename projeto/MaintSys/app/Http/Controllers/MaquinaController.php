<?php

/**
 * CONTROLLER: MaquinaController
 *
 * Responsável pela gestão de máquinas no sistema:
 * - Listar máquinas com estatísticas de status
 * - Criar nova máquina
 * - Visualizar detalhes e histórico
 * - Editar informações
 * - Deletar máquina (com validações)
 *
 * Funcionalidades especiais:
 * - Conta de ordens de serviço por máquina
 * - Estatísticas de status (operacional, em manutenção, parada crítica)
 * - Alertas visuais quando status crítico é ativado
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maquina;

class MaquinaController extends Controller
{
    /**
     * FUNÇÃO: index()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO:
     *   1. Busca todas as máquinas com contagem de ordens
     *   2. Ordena por mais recentes
     *   3. Pagina em 15 registros
     *   4. Calcula estatísticas de status (total, operacional, em manutenção, parada crítica)
     * SAÍDA: View com lista de máquinas e estatísticas
     * USO: GET /maquinas
     */
    public function index()
    {
        // Carrega máquinas paginadas com contagem de O.S.
        $maquinas = Maquina::withCount('ordens')->latest()->paginate(15);

        // Calcula estatísticas por status para exibir no dashboard
        $stats = [
            'total'          => Maquina::count(),
            'operacional'    => Maquina::operacional()->count(),  // Usa scope operacional()
            'em_manutencao'  => Maquina::emManutencao()->count(),  // Usa scope emManutencao()
            'parada_critica' => Maquina::paradaCritica()->count(), // Usa scope paradaCritica()
        ];

        return view('maquinas.index', compact('maquinas', 'stats'));
    }

    /**
     * FUNÇÃO: create()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Retorna view com formulário vazio
     * SAÍDA: View 'maquinas.create'
     * USO: GET /maquinas/create
     */
    public function create()
    {
        return view('maquinas.create');
    }

    /**
     * FUNÇÃO: store($request)
     * ENTRADA: Request com dados do formulário:
     *   - numero_serie (obrigatório, único)
     *   - modelo (obrigatório)
     *   - fabricante (opcional)
     *   - localizacao (obrigatório)
     *   - data_cadastro (opcional, date)
     *   - status (obrigatório, um de: operacional, em_manutencao, parada_critica, inativa)
     *   - descricao (opcional)
     * PROCESSAMENTO:
     *   1. Valida todos os campos
     *   2. Cria novo registro de máquina
     *   3. Se status é 'parada_critica', exibe alerta visual
     * SAÍDA: Redirecionamento para lista com mensagem de sucesso
     * USO: POST /maquinas
     */
    public function store(Request $request)
    {
        // Valida dados do formulário
        $data = $request->validate([
            'numero_serie'    => 'required|string|max:100|unique:maquinas,numero_serie',
            'modelo'          => 'required|string|max:255',
            'fabricante'      => 'nullable|string|max:255',
            'localizacao'     => 'required|string|max:255',
            'data_cadastro'   => 'nullable|date',
            // Status deve ser um desses valores
            'status'          => 'required|in:operacional,em_manutencao,parada_critica,inativa',
            'descricao'       => 'nullable|string',
        ]);

        // Cria máquina no banco
        $maquina = Maquina::create($data);

        // Se máquina foi cadastrada em parada crítica, mostra alerta
        if ($maquina->status === 'parada_critica') {
            session()->flash('alerta', "⚠️ Máquina {$maquina->modelo} cadastrada em Parada Crítica!");
        }

        return redirect()->route('maquinas.index')->with('success', 'Máquina cadastrada com sucesso!');
    }

    /**
     * FUNÇÃO: show($id)
     * ENTRADA: $id (string) - ID da máquina
     * PROCESSAMENTO:
     *   1. Busca máquina pelo ID com eager load de:
     *      - ordens.tecnico (todas as ordens e técnicos responsáveis)
     *      - historicos.tecnico (histórico de manutenção com técnicos)
     *   2. Passa para view de detalhes
     * SAÍDA: View com detalhes completos da máquina
     * USO: GET /maquinas/{id}
     */
    public function show(string $id)
    {
        // Eager load para não gerar N queries (evita problema N+1)
        $maquina = Maquina::with(['ordens.tecnico', 'historicos.tecnico'])->findOrFail($id);
        return view('maquinas.show', compact('maquina'));
    }

    /**
     * FUNÇÃO: edit($id)
     * ENTRADA: $id (string) - ID da máquina
     * PROCESSAMENTO:
     *   1. Busca máquina pelo ID
     *   2. Passa para view com formulário pré-preenchido
     * SAÍDA: View com formulário de edição
     * USO: GET /maquinas/{id}/edit
     */
    public function edit(string $id)
    {
        $maquina = Maquina::findOrFail($id);
        return view('maquinas.edit', compact('maquina'));
    }

    /**
     * FUNÇÃO: update($request, $id)
     * ENTRADA:
     *   - $request com dados atualizados
     *   - $id (string) - ID da máquina
     * PROCESSAMENTO:
     *   1. Busca máquina e armazena status anterior
     *   2. Valida dados (unique ignora próprio registro)
     *   3. Atualiza máquina
     *   4. Se status mudou, exibe alerta específico do novo status
     * SAÍDA: Redirecionamento com mensagem de sucesso e possível alerta
     * USO: PUT /maquinas/{id}
     */
    public function update(Request $request, string $id)
    {
        $maquina = Maquina::findOrFail($id);
        // Armazena status antes de atualizar para comparar depois
        $statusAnterior = $maquina->status;

        // Valida dados
        $data = $request->validate([
            'numero_serie'    => 'required|string|max:100|unique:maquinas,numero_serie,' . $id,
            'modelo'          => 'required|string|max:255',
            'fabricante'      => 'nullable|string|max:255',
            'localizacao'     => 'required|string|max:255',
            'data_cadastro'   => 'nullable|date',
            'status'          => 'required|in:operacional,em_manutencao,parada_critica,inativa',
            'descricao'       => 'nullable|string',
        ]);

        // Atualiza máquina
        $maquina->update($data);

        // Se status mudou, exibe alerta apropriado
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

    /**
     * FUNÇÃO: destroy($id)
     * ENTRADA: $id (string) - ID da máquina a deletar
     * PROCESSAMENTO:
     *   1. Busca máquina pelo ID
     *   2. Valida se tem ordens de serviço (se sim, aborta)
     *   3. Valida se tem históricos (se sim, aborta)
     *   4. Deleta máquina
     * SAÍDA: Redirecionamento com mensagem de sucesso ou erro
     * USO: DELETE /maquinas/{id}
     * NOTAS: Impede deleção se há dados relacionados (integridade)
     */
    public function destroy(string $id)
    {
        $maquina = Maquina::findOrFail($id);

        // Não permite deletar se tem ordens de serviço vinculadas
        if ($maquina->ordens()->exists()) {
            return redirect()->route('maquinas.index')
                ->with('error', 'Não é possível excluir: existem Ordens de Serviço vinculadas.');
        }

        // Não permite deletar se tem históricos vinculados
        if ($maquina->historicos()->exists()) {
            return redirect()->route('maquinas.index')
                ->with('error', 'Nao e possivel excluir: existem historicos vinculados a esta maquina.');
        }

        // Deleta máquina
        $maquina->delete();
        return redirect()->route('maquinas.index')->with('success', 'Máquina excluída com sucesso!');
    }
}
