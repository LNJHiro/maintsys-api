@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold dark:text-white">Gerenciar Permissões</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Configure as permissões para cada nível de acesso</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @foreach($roles as $role)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6 capitalize dark:text-white">{{ $role === 'admin' ? 'Administrador' : 'Usuário' }}</h2>

                <form id="form-{{ $role }}" class="space-y-6">
                    @csrf
                    @method('POST')

                    @foreach($permissions as $modulo => $perms)
                        <div class="border-b pb-6 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-4 capitalize dark:text-white">
                                @switch($modulo)
                                    @case('maquinas')
                                        📋 Máquinas
                                        @break
                                    @case('tecnicos')
                                        👨‍🔧 Técnicos
                                        @break
                                    @case('ordens')
                                        📝 Ordens de Serviço
                                        @break
                                    @case('historico')
                                        📊 Histórico
                                        @break
                                    @default
                                        {{ ucfirst($modulo) }}
                                @endswitch
                            </h3>

                            <div class="space-y-3">
                                @foreach($perms as $permission)
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            {{ in_array($permission->id, $rolePermissions[$role] ?? []) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-blue-600"
                                        />
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">
                                            {{ $permission->descricao }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <button
                        type="button"
                        onclick="salvarPermissoes('{{ $role }}')"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200"
                    >
                        Salvar Permissões
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-12 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-2">ℹ️ Informações</h3>
        <ul class="text-blue-800 dark:text-blue-200 space-y-2">
            <li>• <strong>Administrador:</strong> Tem permissões sobre quase todos os recursos por padrão</li>
            <li>• <strong>Usuário:</strong> Tem permissões limitadas, apenas para visualizar e criar ordens de serviço</li>
            <li>• <strong>Admin Master:</strong> Você (gerencia tudo e pode alterar estas permissões)</li>
        </ul>
    </div>

    <div class="mt-8">
        <a href="{{ route('acesso.usuarios') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
            Gerenciar Usuários →
        </a>
    </div>
</div>

<script>
function salvarPermissoes(role) {
    const form = document.getElementById('form-' + role);
    const formData = new FormData(form);
    const permissions = formData.getAll('permissions[]');

    fetch(`/acesso/role/${role}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ permissions: permissions })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
        } else if (data.error) {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar permissões');
    });
}
</script>
@endsection
