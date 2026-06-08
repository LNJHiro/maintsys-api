<?php

/**
 * CONTROLLER: TecnicoController
 *
 * Responsável pela gestão completa de técnicos no sistema:
 * - Listar técnicos
 * - Criar novo técnico (com usuário associado)
 * - Visualizar detalhes do técnico
 * - Editar dados do técnico
 * - Deletar técnico (com validações)
 *
 * Fluxo principal:
 * index → lista paginada de técnicos
 * create → exibe formulário
 * store → salva novo técnico + cria usuário em transação
 * show → exibe detalhes com ordens e histórico
 * edit → exibe formulário de edição
 * update → atualiza técnico e usuário em transação
 * destroy → deleta técnico após validações
 */

// Define o namespace do controller dentro da estrutura do Laravel
namespace App\Http\Controllers;

// Importa a classe Request para receber e validar dados do formulário HTTP
use Illuminate\Http\Request;
// Importa a facade DB para operações de banco de dados em transação
use Illuminate\Support\Facades\DB;
// Importa a facade Hash para criptografar senhas com Bcrypt
use Illuminate\Support\Facades\Hash;
// Importa a classe Rule para regras de validação avançadas (ex: unique com ignore)
use Illuminate\Validation\Rule;
// Importa o model Tecnico para manipular os dados de técnicos
use App\Models\Tecnico;
// Importa o model User para criar e manipular contas de usuário associadas
use App\Models\User;

// Declara o controller que herda funcionalidades base do Controller do Laravel
class TecnicoController extends Controller
{
    /**
     * FUNÇÃO: index()
     * ENTRADA: Nenhuma (Request automático)
     * PROCESSAMENTO:
     *   1. Busca todos os técnicos com contagem de ordens
     *   2. Ordena por mais recentes primeiro
     *   3. Pagina em 15 registros por página
     * SAÍDA: View com lista paginada de técnicos
     * USO: GET /tecnicos
     */
    public function index()
    {
        // Adiciona coluna virtual 'ordens_count' com total de O.S. de cada técnico
        // Ordena pelo mais recente (campo created_at decrescente)
        // Divide o resultado em páginas de 15 registros
        $tecnicos = Tecnico::withCount('ordens')->latest()->paginate(15);

        // Retorna a view de listagem passando a coleção paginada de técnicos
        return view('tecnicos.index', compact('tecnicos'));
    } // fim do método index

    /**
     * FUNÇÃO: create()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Retorna view com formulário em branco para novo técnico
     * SAÍDA: View 'tecnicos.create' (formulário vazio)
     * USO: GET /tecnicos/create
     */
    public function create()
    {
        // Retorna a view com o formulário de cadastro sem dados preenchidos
        return view('tecnicos.create');
    } // fim do método create

    /**
     * FUNÇÃO: store($request)
     * ENTRADA: Request com dados do formulário:
     *   - nome (obrigatório, string, até 255 caracteres)
     *   - matricula (obrigatório, string, até 50, único)
     *   - email (obrigatório, email válido, único em tecnicos e users)
     *   - password (obrigatório, min 8 caracteres, confirmação)
     *   - especialidade (opcional)
     *   - telefone (opcional)
     * PROCESSAMENTO:
     *   1. Valida todos os campos
     *   2. Hash a senha com Bcrypt
     *   3. Em transação DB:
     *      a. Cria usuário no banco com role 'usuario'
     *      b. Cria técnico vinculado ao usuário
     *   4. Se erro em qualquer passo, rollback automático
     * SAÍDA: Redirecionamento com mensagem de sucesso
     * USO: POST /tecnicos
     */
    public function store(Request $request)
    {
        // Executa a validação dos dados enviados pelo formulário
        // Retorna array somente com os campos declarados (mass assignment seguro)
        $data = $request->validate([
            // Nome é obrigatório, deve ser texto e ter no máximo 255 caracteres
            'nome'          => 'required|string|max:255',
            // Matrícula é obrigatória e deve ser única na tabela 'tecnicos'
            'matricula'     => 'required|string|max:50|unique:tecnicos,matricula',
            // Email requer múltiplas regras em array para melhor legibilidade
            'email'         => [
                // Campo obrigatório
                'required',
                // Deve ser um endereço de e-mail válido
                'email',
                // Máximo de 255 caracteres
                'max:255',
                // Não pode existir outro técnico com o mesmo e-mail
                'unique:tecnicos,email',
                // Não pode existir outro usuário do sistema com o mesmo e-mail
                'unique:users,email',
            ],
            // Senha obrigatória com mínimo de 8 caracteres; 'confirmed' exige campo password_confirmation igual
            'password'      => 'required|string|min:8|confirmed',
            // Especialidade é opcional (nullable)
            'especialidade' => 'nullable|string|max:255',
            // Telefone é opcional (nullable)
            'telefone'      => 'nullable|string|max:20',
        ]); // fim da validação

        // Abre uma transação de banco de dados: se qualquer instrução falhar, tudo é revertido (rollback)
        DB::transaction(function () use ($request, $data) {
            // Criptografa a senha com o algoritmo Bcrypt antes de armazenar
            $hashedPassword = Hash::make($data['password']);

            // Cria o registro de usuário no sistema para permitir login do técnico
            $user = User::create([
                // Usa o nome informado no formulário como nome de exibição do usuário
                'name'     => $data['nome'],
                // Usa o e-mail do técnico como login de acesso
                'email'    => $data['email'],
                // Armazena a senha já criptografada
                'password' => $hashedPassword,
                // Atribui role genérica de usuário (não administrador)
                'role'     => 'usuario',
            ]); // fim da criação do usuário

            // Vincula o ID do usuário recém-criado ao array de dados do técnico
            $data['user_id'] = $user->id;
            // Substitui a senha em texto puro pela versão criptografada no array
            $data['password'] = $hashedPassword;
            // Define o técnico como ativo por padrão (true), podendo ser sobrescrito pela request
            $data['ativo'] = $request->boolean('ativo', true);

            // Cria o registro do técnico no banco usando os dados validados e complementados
            Tecnico::create($data);
        }); // fim da transação DB

        // Redireciona para a listagem de técnicos com mensagem de sucesso na sessão
        return redirect()->route('tecnicos.index')->with('success', 'Tecnico cadastrado com perfil de usuario!');
    } // fim do método store

    /**
     * FUNÇÃO: show($id)
     * ENTRADA: $id (string) - ID do técnico a exibir
     * PROCESSAMENTO:
     *   1. Busca técnico pelo ID (ou lança erro 404 se não existe)
     *   2. Eager loads relacionamentos:
     *      - ordens.maquina (todas as O.S. e máquinas associadas)
     *      - historicos.maquina (históricos de manutenção com máquinas)
     *   3. Passa para view
     * SAÍDA: View com detalhes completos do técnico
     * USO: GET /tecnicos/{id}
     */
    public function show(string $id)
    {
        // Carrega o técnico com seus relacionamentos em uma única consulta (evita o problema N+1)
        // 'ordens.maquina' carrega todas as ordens de serviço e a máquina de cada ordem
        // 'historicos.maquina' carrega todos os históricos de manutenção e a máquina de cada histórico
        // findOrFail lança exceção 404 automaticamente se o ID não existir
        $tecnico = Tecnico::with(['ordens.maquina', 'historicos.maquina'])->findOrFail($id);

        // Retorna a view de detalhes passando o técnico com todos os relacionamentos carregados
        return view('tecnicos.show', compact('tecnico'));
    } // fim do método show

    /**
     * FUNÇÃO: edit($id)
     * ENTRADA: $id (string) - ID do técnico a editar
     * PROCESSAMENTO:
     *   1. Busca técnico pelo ID (ou lança erro 404)
     *   2. Passa para view de edição com dados preenchidos
     * SAÍDA: View com formulário pré-preenchido com dados do técnico
     * USO: GET /tecnicos/{id}/edit
     */
    public function edit(string $id)
    {
        // Busca o técnico pelo ID e lança 404 automaticamente se não encontrado
        $tecnico = Tecnico::findOrFail($id);

        // Retorna a view de edição passando o técnico para preencher os campos do formulário
        return view('tecnicos.edit', compact('tecnico'));
    } // fim do método edit

    /**
     * FUNÇÃO: update($request, $id)
     * ENTRADA:
     *   - $request com dados atualizados
     *   - $id (string) - ID do técnico a atualizar
     * PROCESSAMENTO:
     *   1. Busca técnico pelo ID
     *   2. Valida dados (permite update de email/matricula com regra unique ignorando próprio registro)
     *   3. Se password preenchido, valida e faz hash
     *   4. Em transação DB:
     *      a. Se técnico não tem usuário, cria um novo
     *      b. Se tem usuário, atualiza dados dele
     *      c. Atualiza dados do técnico
     * SAÍDA: Redirecionamento com mensagem de sucesso
     * USO: PUT /tecnicos/{id}
     */
    public function update(Request $request, string $id)
    {
        // Busca o técnico pelo ID e retorna 404 se não encontrado
        $tecnico = Tecnico::findOrFail($id);

        // Valida os dados do formulário com regras adaptadas para a operação de atualização
        $data = $request->validate([
            // Nome continua obrigatório
            'nome'          => 'required|string|max:255',
            // Matrícula deve ser única, mas ignora o próprio registro atual pelo ID
            'matricula'     => 'required|string|max:50|unique:tecnicos,matricula,' . $id,
            // E-mail requer múltiplas regras com ignore para o próprio técnico e usuário
            'email'         => [
                // Campo obrigatório
                'required',
                // Deve ser um e-mail válido
                'email',
                // Máximo de 255 caracteres
                'max:255',
                // Permite manter o mesmo e-mail ignorando o próprio técnico na checagem de unicidade
                Rule::unique('tecnicos', 'email')->ignore($tecnico->id),
                // Permite manter o mesmo e-mail ignorando o próprio usuário associado
                Rule::unique('users', 'email')->ignore($tecnico->user_id),
            ],
            // Especialidade continua opcional
            'especialidade' => 'nullable|string|max:255',
            // Telefone continua opcional
            'telefone'      => 'nullable|string|max:20',
        ]); // fim da validação

        // Inicializa variável de senha como nula (só será preenchida se o campo foi enviado)
        $password = null;

        // Verifica se o campo de senha foi preenchido no formulário (não está vazio)
        if ($request->filled('password')) {
            // Valida a nova senha: mínimo 8 caracteres e campo de confirmação igual
            $request->validate(['password' => 'string|min:8|confirmed']);
            // Criptografa a nova senha com Bcrypt
            $password = Hash::make($request->password);
            // Adiciona a senha criptografada ao array de dados a ser salvo
            $data['password'] = $password;
        } // fim da verificação de senha

        // Define o status ativo do técnico (checkbox do formulário ou true por padrão)
        $data['ativo'] = $request->boolean('ativo', true);

        // Abre transação para garantir consistência entre Tecnico e User
        DB::transaction(function () use ($tecnico, $data, $password) {
            // Recupera o usuário associado ao técnico (pode ser null se não tiver)
            $user = $tecnico->user;

            // Se o técnico ainda não possui usuário vinculado, cria um novo
            if (!$user) {
                // Cria novo usuário com os dados atualizados do técnico
                $user = User::create([
                    // Usa o nome atualizado como nome do usuário
                    'name'     => $data['nome'],
                    // Usa o e-mail atualizado como login do usuário
                    'email'    => $data['email'],
                    // Usa a nova senha se fornecida, ou mantém a senha atual criptografada do técnico
                    'password' => $password ?? $tecnico->password,
                    // Atribui role genérica de usuário
                    'role'     => 'usuario',
                ]); // fim da criação do usuário

                // Vincula o novo usuário ao técnico pelo ID
                $data['user_id'] = $user->id;
            } else {
                // Se já existe usuário associado, prepara os dados para atualização
                $userData = [
                    // Atualiza o nome de exibição do usuário
                    'name'  => $data['nome'],
                    // Atualiza o e-mail de login do usuário
                    'email' => $data['email'],
                ]; // fim do array de dados do usuário

                // Atualiza a senha do usuário somente se uma nova senha foi fornecida
                if ($password) {
                    // Adiciona a nova senha criptografada ao array de atualização
                    $userData['password'] = $password;
                } // fim da verificação de senha

                // Persiste as alterações no registro do usuário
                $user->update($userData);
            } // fim do bloco if/else de usuário

            // Persiste todas as alterações no registro do técnico
            $tecnico->update($data);
        }); // fim da transação DB

        // Redireciona para a listagem com mensagem de sucesso
        return redirect()->route('tecnicos.index')->with('success', 'Técnico atualizado com sucesso!');
    } // fim do método update

    /**
     * FUNÇÃO: destroy($id)
     * ENTRADA: $id (string) - ID do técnico a deletar
     * PROCESSAMENTO:
     *   1. Busca técnico pelo ID
     *   2. Valida se existem ordens de serviço (se sim, aborta)
     *   3. Valida se existem históricos (se sim, aborta)
     *   4. Em transação DB:
     *      a. Deleta técnico
     *      b. Se técnico tem usuário com role 'usuario', deleta usuário também
     * SAÍDA: Redirecionamento com mensagem de sucesso ou erro
     * USO: DELETE /tecnicos/{id}
     * NOTAS: Impede deleção se há dados vinculados (integridade referencial)
     */
    public function destroy(string $id)
    {
        // Busca o técnico pelo ID e retorna 404 se não encontrado
        $tecnico = Tecnico::findOrFail($id);

        // Verifica se existe pelo menos uma ordem de serviço vinculada ao técnico
        if ($tecnico->ordens()->exists()) {
            // Retorna para a listagem com mensagem de erro informando o bloqueio
            return redirect()->route('tecnicos.index')
                ->with('error', 'Não é possível excluir: existem O.S. vinculadas a este técnico.');
        } // fim da verificação de ordens

        // Verifica se existe pelo menos um histórico de manutenção vinculado ao técnico
        if ($tecnico->historicos()->exists()) {
            // Retorna para a listagem com mensagem de erro informando o bloqueio
            return redirect()->route('tecnicos.index')
                ->with('error', 'Nao e possivel excluir: existem historicos vinculados a este tecnico.');
        } // fim da verificação de históricos

        // Abre transação para deletar técnico e usuário de forma atômica
        DB::transaction(function () use ($tecnico) {
            // Recupera o usuário associado ao técnico antes de deletá-lo
            $user = $tecnico->user;

            // Remove o registro do técnico do banco de dados
            $tecnico->delete();

            // Remove o usuário associado somente se:
            // - o usuário existe (não é nulo)
            // - tem role 'usuario' (evita deletar administradores acidentalmente)
            if ($user && $user->role === 'usuario') {
                // Deleta o usuário do sistema
                $user->delete();
            } // fim da verificação e deleção do usuário
        }); // fim da transação DB

        // Redireciona para a listagem com mensagem de sucesso após a exclusão
        return redirect()->route('tecnicos.index')->with('success', 'Técnico excluído com sucesso!');
    } // fim do método destroy
} // fim da classe TecnicoController
