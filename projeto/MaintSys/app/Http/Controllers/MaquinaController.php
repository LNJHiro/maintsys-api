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

// Define o namespace do controller dentro da estrutura do Laravel
namespace App\Http\Controllers;

// Importa a classe Request para receber e validar dados do formulário HTTP
use Illuminate\Http\Request;
// Importa o model Maquina para realizar operações no banco de dados
use App\Models\Maquina;

// Declara o controller que herda funcionalidades base do Controller do Laravel
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
        // Carrega a lista de máquinas paginada:
        // withCount('ordens') adiciona coluna virtual 'ordens_count' com o total de O.S. por máquina
        // latest() ordena do mais recente ao mais antigo (campo created_at decrescente)
        // paginate(15) divide o resultado em páginas de 15 registros cada
        $maquinas = Maquina::withCount('ordens')->latest()->paginate(15);

        // Monta array associativo com contagens por status para exibir nos cards de resumo
        $stats = [
            // Conta o total de máquinas cadastradas no sistema
            'total'          => Maquina::count(),
            // Conta máquinas no status 'operacional' usando o scope do model
            'operacional'    => Maquina::operacional()->count(),
            // Conta máquinas no status 'em_manutencao' usando o scope do model
            'em_manutencao'  => Maquina::emManutencao()->count(),
            // Conta máquinas no status 'parada_critica' usando o scope do model
            'parada_critica' => Maquina::paradaCritica()->count(),
        ]; // fim do array $stats

        // Retorna a view de listagem passando as máquinas paginadas e as estatísticas
        return view('maquinas.index', compact('maquinas', 'stats'));
    } // fim do método index

    /**
     * FUNÇÃO: create()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Retorna view com formulário vazio
     * SAÍDA: View 'maquinas.create'
     * USO: GET /maquinas/create
     */
    public function create()
    {
        // Retorna a view com o formulário de cadastro sem dados preenchidos
        return view('maquinas.create');
    } // fim do método create

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
        // Executa a validação dos dados enviados pelo formulário
        // Retorna array somente com os campos declarados nas regras
        $data = $request->validate([
            // Número de série obrigatório e único na tabela 'maquinas'
            'numero_serie'    => 'required|string|max:100|unique:maquinas,numero_serie',
            // Modelo é obrigatório e deve ser texto
            'modelo'          => 'required|string|max:255',
            // Fabricante é opcional (pode ser nulo)
            'fabricante'      => 'nullable|string|max:255',
            // Localização é obrigatória para identificar onde a máquina está
            'localizacao'     => 'required|string|max:255',
            // Data de cadastro é opcional e deve ser uma data válida
            'data_cadastro'   => 'nullable|date',
            // Status deve ser um dos valores permitidos pelo sistema
            'status'          => 'required|in:operacional,em_manutencao,parada_critica,inativa',
            // Descrição é opcional e pode ter texto longo
            'descricao'       => 'nullable|string',
        ]); // fim da validação

        // Persiste o novo registro de máquina no banco de dados com os dados validados
        $maquina = Maquina::create($data);

        // Verifica se a máquina foi cadastrada diretamente em parada crítica
        if ($maquina->status === 'parada_critica') {
            // Armazena mensagem de alerta na sessão para exibição imediata na próxima requisição
            session()->flash('alerta', "⚠️ Máquina {$maquina->modelo} cadastrada em Parada Crítica!");
        } // fim da verificação de parada crítica

        // Redireciona para a listagem de máquinas com mensagem de sucesso
        return redirect()->route('maquinas.index')->with('success', 'Máquina cadastrada com sucesso!');
    } // fim do método store

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
        // Carrega a máquina com seus relacionamentos em uma única consulta (evita o problema N+1):
        // 'ordens.tecnico' traz todas as ordens de serviço e o técnico responsável por cada uma
        // 'historicos.tecnico' traz todos os registros do histórico e o técnico de cada intervenção
        // findOrFail retorna automaticamente erro 404 se o ID não existir no banco
        $maquina = Maquina::with(['ordens.tecnico', 'historicos.tecnico'])->findOrFail($id);

        // Retorna a view de detalhes passando a máquina com relacionamentos carregados
        return view('maquinas.show', compact('maquina'));
    } // fim do método show

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
        // Busca a máquina pelo ID e lança 404 automaticamente se não encontrada
        $maquina = Maquina::findOrFail($id);

        // Retorna a view de edição passando a máquina para preencher os campos do formulário
        return view('maquinas.edit', compact('maquina'));
    } // fim do método edit

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
        // Busca a máquina pelo ID e retorna 404 se não encontrada
        $maquina = Maquina::findOrFail($id);

        // Armazena o status atual da máquina antes da atualização para comparar depois
        $statusAnterior = $maquina->status;

        // Valida os dados enviados pelo formulário com regras adaptadas para atualização
        $data = $request->validate([
            // Número de série deve ser único, mas ignora o próprio registro pelo ID
            'numero_serie'    => 'required|string|max:100|unique:maquinas,numero_serie,' . $id,
            // Modelo continua obrigatório
            'modelo'          => 'required|string|max:255',
            // Fabricante continua opcional
            'fabricante'      => 'nullable|string|max:255',
            // Localização continua obrigatória
            'localizacao'     => 'required|string|max:255',
            // Data de cadastro continua opcional
            'data_cadastro'   => 'nullable|date',
            // Status deve continuar sendo um dos valores permitidos
            'status'          => 'required|in:operacional,em_manutencao,parada_critica,inativa',
            // Descrição continua opcional
            'descricao'       => 'nullable|string',
        ]); // fim da validação

        // Persiste as alterações no registro da máquina com os dados validados
        $maquina->update($data);

        // Verifica se o status da máquina foi alterado em relação ao anterior
        if ($statusAnterior !== $maquina->status) {
            // Seleciona a mensagem de alerta de acordo com o novo status usando match (PHP 8+)
            $msg = match($maquina->status) {
                // Alerta crítico quando a máquina entra em parada
                'parada_critica' => "🚨 {$maquina->modelo} entrou em Parada Crítica!",
                // Mensagem positiva quando a máquina volta a operar
                'operacional'    => "✅ {$maquina->modelo} voltou a ser Operacional!",
                // Mensagem informativa quando a máquina entra em manutenção
                'em_manutencao'  => "🔧 {$maquina->modelo} está em Manutenção.",
                // Mensagem genérica para outros status (ex: inativa)
                default          => "ℹ️ Status da {$maquina->modelo} atualizado.",
            }; // fim do match

            // Armazena a mensagem de alerta na sessão para exibição na próxima requisição
            session()->flash('alerta', $msg);
        } // fim da verificação de mudança de status

        // Redireciona para a listagem de máquinas com mensagem de sucesso
        return redirect()->route('maquinas.index')->with('success', 'Máquina atualizada com sucesso!');
    } // fim do método update

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
        // Busca a máquina pelo ID e retorna 404 se não encontrada
        $maquina = Maquina::findOrFail($id);

        // Verifica se existe pelo menos uma ordem de serviço vinculada a esta máquina
        if ($maquina->ordens()->exists()) {
            // Retorna para a listagem com mensagem de erro bloqueando a exclusão
            return redirect()->route('maquinas.index')
                ->with('error', 'Não é possível excluir: existem Ordens de Serviço vinculadas.');
        } // fim da verificação de ordens

        // Verifica se existe pelo menos um histórico de manutenção vinculado a esta máquina
        if ($maquina->historicos()->exists()) {
            // Retorna para a listagem com mensagem de erro bloqueando a exclusão
            return redirect()->route('maquinas.index')
                ->with('error', 'Nao e possivel excluir: existem historicos vinculados a esta maquina.');
        } // fim da verificação de históricos

        // Remove o registro da máquina do banco de dados
        $maquina->delete();

        // Redireciona para a listagem com mensagem de sucesso após a exclusão
        return redirect()->route('maquinas.index')->with('success', 'Máquina excluída com sucesso!');
    } // fim do método destroy
} // fim da classe MaquinaController
