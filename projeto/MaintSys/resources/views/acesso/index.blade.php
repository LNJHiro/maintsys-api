{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')
{{-- Container principal centralizado com espaçamento interno --}}
<div class="container mx-auto px-4 py-8">
    {{-- Bloco do cabeçalho da página --}}
    <div class="mb-8">
        {{-- Título principal da tela de gerenciamento de permissões --}}
        <h1 class="text-3xl font-bold dark:text-white">Gerenciar Permissões</h1>
        {{-- Subtítulo explicativo da função da página --}}
        <p class="text-gray-600 dark:text-gray-400 mt-2">Configure as permissões para cada nível de acesso</p>
    </div>

    {{-- Grade de duas colunas que exibe um card por role (admin e usuário) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Itera sobre as roles disponíveis no sistema --}}
        @foreach($roles as $role)
            {{-- Card individual para cada role --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                {{-- Título do card com o nome da role traduzido --}}
                <h2 class="text-2xl font-bold mb-6 capitalize dark:text-white">{{ $role === 'admin' ? 'Administrador' : 'Usuário' }}</h2>

                {{-- Formulário sem action (o envio é feito via JavaScript com fetch) --}}
                <form id="form-{{ $role }}" class="space-y-6">
                    {{-- Token CSRF incluído no formulário para uso na requisição fetch --}}
                    @csrf
                    {{-- Método POST declarado no formulário (redundante pois o fetch define o método) --}}
                    @method('POST')

                    {{-- Itera sobre os módulos do sistema e suas permissões --}}
                    @foreach($permissions as $modulo => $perms)
                        {{-- Seção de cada módulo com separador inferior --}}
                        <div class="border-b pb-6 dark:border-gray-700">
                            {{-- Título do módulo com ícone; usa switch para exibição amigável --}}
                            <h3 class="text-lg font-semibold mb-4 capitalize dark:text-white">
                                {{-- Switch que mapeia o nome técnico do módulo para nome exibível --}}
                                @switch($modulo)
                                    {{-- Caso o módulo seja "maquinas" --}}
                                    @case('maquinas')
                                        📋 Máquinas
                                        @break
                                    {{-- Caso o módulo seja "tecnicos" --}}
                                    @case('tecnicos')
                                        👨‍🔧 Técnicos
                                        @break
                                    {{-- Caso o módulo seja "ordens" --}}
                                    @case('ordens')
                                        📝 Ordens de Serviço
                                        @break
                                    {{-- Caso o módulo seja "historico" --}}
                                    @case('historico')
                                        📊 Histórico
                                        @break
                                    {{-- Caso padrão: exibe o nome do módulo com primeira letra maiúscula --}}
                                    @default
                                        {{ ucfirst($modulo) }}
                                @endswitch {{-- fim do switch de módulos --}}
                            </h3>

                            {{-- Container com os checkboxes de permissão --}}
                            <div class="space-y-3">
                                {{-- Itera sobre cada permissão do módulo --}}
                                @foreach($perms as $permission)
                                    {{-- Label clicável que envolve o checkbox para facilitar a seleção --}}
                                    <label class="flex items-center cursor-pointer">
                                        {{-- Checkbox da permissão; marcado se a role já possui essa permissão --}}
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            {{-- Valor é o ID da permissão, enviado ao salvar --}}
                                            value="{{ $permission->id }}"
                                            {{-- Marca o checkbox se a permissão estiver na lista da role --}}
                                            {{ in_array($permission->id, $rolePermissions[$role] ?? []) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-blue-600"
                                        />
                                        {{-- Rótulo com a descrição legível da permissão --}}
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">
                                            {{ $permission->descricao }}
                                        </span>
                                    </label>
                                @endforeach {{-- fim da iteração sobre as permissões do módulo --}}
                            </div>
                        </div>
                    @endforeach {{-- fim da iteração sobre os módulos --}}

                    {{-- Botão que aciona a função JavaScript de salvar via AJAX --}}
                    <button
                        type="button"
                        {{-- Chama a função passando o nome da role como argumento --}}
                        onclick="salvarPermissoes('{{ $role }}')"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200"
                    >
                        Salvar Permissões
                    </button>
                </form>
            </div>
        @endforeach {{-- fim da iteração sobre as roles --}}
    </div>

    {{-- Card informativo explicando os níveis de acesso do sistema --}}
    <div class="mt-12 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        {{-- Título da seção informativa --}}
        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-2">ℹ️ Informações</h3>
        {{-- Lista com as descrições de cada nível de acesso --}}
        <ul class="text-blue-800 dark:text-blue-200 space-y-2">
            {{-- Descrição do nível Administrador --}}
            <li>• <strong>Administrador:</strong> Tem permissões sobre quase todos os recursos por padrão</li>
            {{-- Descrição do nível Usuário --}}
            <li>• <strong>Usuário:</strong> Tem permissões limitadas, apenas para visualizar e criar ordens de serviço</li>
            {{-- Descrição do Admin Master --}}
            <li>• <strong>Admin Master:</strong> Você (gerencia tudo e pode alterar estas permissões)</li>
        </ul>
    </div>

    {{-- Container com o link para a tela de gerenciamento de usuários --}}
    <div class="mt-8">
        {{-- Link que navega para a listagem de usuários do sistema --}}
        <a href="{{ route('acesso.usuarios') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
            Gerenciar Usuários →
        </a>
    </div>
</div>

{{-- Bloco de JavaScript para salvar permissões sem recarregar a página --}}
<script>
// Função chamada ao clicar em "Salvar Permissões" de uma role
function salvarPermissoes(role) {
    // Captura o formulário da role pelo ID dinâmico
    const form = document.getElementById('form-' + role);
    // Cria um objeto FormData a partir do formulário para capturar todos os checkboxes
    const formData = new FormData(form);
    // Extrai o array de IDs das permissões marcadas
    const permissions = formData.getAll('permissions[]');

    // Envia a requisição AJAX via fetch para a rota de salvar permissões da role
    fetch(`/acesso/role/${role}`, {
        // Método POST para atualizar as permissões
        method: 'POST',
        headers: {
            // Token CSRF lido do meta tag do HTML para autenticação da requisição
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            // Define o corpo como JSON
            'Content-Type': 'application/json',
        },
        // Serializa o array de permissões como JSON no corpo da requisição
        body: JSON.stringify({ permissions: permissions })
    })
    // Converte a resposta da API para JSON
    .then(response => response.json())
    // Trata o retorno da API
    .then(data => {
        // Exibe mensagem de sucesso caso o servidor retorne "message"
        if (data.message) {
            alert(data.message);
        // Exibe mensagem de erro caso o servidor retorne "error"
        } else if (data.error) {
            alert('Erro: ' + data.error);
        }
    })
    // Trata erros de rede ou falhas na requisição
    .catch(error => {
        // Loga o erro no console para depuração
        console.error('Erro:', error);
        // Alerta o usuário sobre a falha ao salvar
        alert('Erro ao salvar permissões');
    });
}
</script>
@endsection {{-- fim da seção de conteúdo principal --}}
