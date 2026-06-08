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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Tecnico;
use App\Models\User;

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
        // withCount('ordens') adiciona coluna com contagem de O.S. de cada técnico
        // latest() ordena pela data de criação mais recente
        // paginate(15) divide em páginas de 15 registros
        $tecnicos = Tecnico::withCount('ordens')->latest()->paginate(15);
        return view('tecnicos.index', compact('tecnicos'));
    }

    /**
     * FUNÇÃO: create()
     * ENTRADA: Nenhuma
     * PROCESSAMENTO: Retorna view com formulário em branco para novo técnico
     * SAÍDA: View 'tecnicos.create' (formulário vazio)
     * USO: GET /tecnicos/create
     */
    public function create()
    {
        return view('tecnicos.create');
    }

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
        // Valida dados do formulário e retorna array com dados válidos
        $data = $request->validate([
            'nome'          => 'required|string|max:255',
            'matricula'     => 'required|string|max:50|unique:tecnicos,matricula',
            'email'         => [
                'required',
                'email',
                'max:255',
                'unique:tecnicos,email',        // Email único em tecnicos
                'unique:users,email',           // Email único em users
            ],
            'password'      => 'required|string|min:8|confirmed', // password_confirmation deve existir
            'especialidade' => 'nullable|string|max:255',
            'telefone'      => 'nullable|string|max:20',
        ]);

        // DB::transaction garante que ambas operações (User e Tecnico) são realizadas
        // ou nenhuma (atomicidade). Se erro no meio, tudo volta
        DB::transaction(function () use ($request, $data) {
            // Faz hash da senha com algoritmo Bcrypt
            $hashedPassword = Hash::make($data['password']);

            // Cria usuário no sistema para login
            $user = User::create([
                'name'     => $data['nome'],
                'email'    => $data['email'],
                'password' => $hashedPassword,
                'role'     => 'usuario',  // Role genérico de usuário
            ]);

            // Adiciona ID do usuário aos dados do técnico
            $data['user_id'] = $user->id;
            // Armazena senha com hash no técnico também
            $data['password'] = $hashedPassword;
            // ativo padrão é true (pode vir da request ou usa true)
            $data['ativo'] = $request->boolean('ativo', true);

            // Cria registro do técnico
            Tecnico::create($data);
        });

        // Redireciona para lista com mensagem de sucesso
        return redirect()->route('tecnicos.index')->with('success', 'Tecnico cadastrado com perfil de usuario!');
    }

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
        // with() faz eager loading de relacionamentos (reduz queries)
        // findOrFail() retorna 404 se não encontrar
        $tecnico = Tecnico::with(['ordens.maquina', 'historicos.maquina'])->findOrFail($id);
        return view('tecnicos.show', compact('tecnico'));
    }

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
        $tecnico = Tecnico::findOrFail($id);
        return view('tecnicos.edit', compact('tecnico'));
    }

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
        $tecnico = Tecnico::findOrFail($id);

        // Valida dados com regras específicas para update
        $data = $request->validate([
            'nome'          => 'required|string|max:255',
            // Ignora matrícula do próprio registro na validação de unique
            'matricula'     => 'required|string|max:50|unique:tecnicos,matricula,' . $id,
            'email'         => [
                'required',
                'email',
                'max:255',
                // Ignora email do próprio técnico
                Rule::unique('tecnicos', 'email')->ignore($tecnico->id),
                // Ignora email do usuário associado
                Rule::unique('users', 'email')->ignore($tecnico->user_id),
            ],
            'especialidade' => 'nullable|string|max:255',
            'telefone'      => 'nullable|string|max:20',
        ]);

        $password = null;

        // Se campo password foi preenchido, valida e faz hash
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $password = Hash::make($request->password);
            $data['password'] = $password;
        }

        $data['ativo'] = $request->boolean('ativo', true);

        // Transação para garantir consistência entre User e Tecnico
        DB::transaction(function () use ($tecnico, $data, $password) {
            $user = $tecnico->user;

            // Se técnico não tem usuário, cria um
            if (!$user) {
                $user = User::create([
                    'name'     => $data['nome'],
                    'email'    => $data['email'],
                    'password' => $password ?? $tecnico->password,
                    'role'     => 'usuario',
                ]);

                $data['user_id'] = $user->id;
            } else {
                // Se tem usuário, atualiza dados dele
                $userData = [
                    'name'  => $data['nome'],
                    'email' => $data['email'],
                ];

                // Só atualiza senha se foi fornecida
                if ($password) {
                    $userData['password'] = $password;
                }

                $user->update($userData);
            }

            // Atualiza dados do técnico
            $tecnico->update($data);
        });

        return redirect()->route('tecnicos.index')->with('success', 'Técnico atualizado com sucesso!');
    }

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
        $tecnico = Tecnico::findOrFail($id);

        // Validação: não permite deletar se há ordens de serviço
        if ($tecnico->ordens()->exists()) {
            return redirect()->route('tecnicos.index')
                ->with('error', 'Não é possível excluir: existem O.S. vinculadas a este técnico.');
        }

        // Validação: não permite deletar se há históricos
        if ($tecnico->historicos()->exists()) {
            return redirect()->route('tecnicos.index')
                ->with('error', 'Nao e possivel excluir: existem historicos vinculados a este tecnico.');
        }

        // Transação para deletar técnico e usuário associado
        DB::transaction(function () use ($tecnico) {
            $user = $tecnico->user;

            // Deleta técnico primeiro
            $tecnico->delete();

            // Deleta usuário apenas se:
            // 1. Existe usuário associado
            // 2. Usuário tem role 'usuario' (não delete admins acidentalmente)
            if ($user && $user->role === 'usuario') {
                $user->delete();
            }
        });

        return redirect()->route('tecnicos.index')->with('success', 'Técnico excluído com sucesso!');
    }
}
