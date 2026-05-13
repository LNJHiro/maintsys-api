@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold dark:text-white">Gerenciar Usuários</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Altere o nível de acesso de cada usuário</p>
        </div>
        <a href="{{ route('acesso.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            ← Voltar
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-4 text-left font-bold dark:text-white">Nome</th>
                    <th class="px-6 py-4 text-left font-bold dark:text-white">E-mail</th>
                    <th class="px-6 py-4 text-left font-bold dark:text-white">Nível Atual</th>
                    <th class="px-6 py-4 text-left font-bold dark:text-white">Novo Nível</th>
                    <th class="px-6 py-4 text-left font-bold dark:text-white">Ação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 dark:text-white">{{ $user->name }}</td>
                        <td class="px-6 py-4 dark:text-white">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                                {{ $user->role === 'admin' ? 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}">
                                {{ $user->role === 'admin' ? 'Administrador' : 'Usuário' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <select
                                id="role-{{ $user->id }}"
                                class="px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                                <option value="usuario" {{ $user->role === 'usuario' ? 'selected' : '' }}>Usuário</option>
                            </select>
                        </td>
                        <td class="px-6 py-4">
                            <button
                                onclick="alterarUsuario({{ $user->id }}, '{{ $user->name }}')"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-4 rounded transition duration-200"
                            >
                                Alterar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            Nenhum usuário encontrado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
        <h3 class="text-lg font-bold text-yellow-900 dark:text-yellow-100 mb-2">⚠️ Aviso</h3>
        <p class="text-yellow-800 dark:text-yellow-200">
            Usuários com nível <strong>Administrador</strong> terão acesso às mesmas permissões configuradas na aba "Permissões".
            Usuários com nível <strong>Usuário</strong> terão acesso apenas às permissões do nível "Usuário".
        </p>
    </div>
</div>

<script>
function alterarUsuario(userId, userName) {
    const selectElement = document.getElementById('role-' + userId);
    const newRole = selectElement.value;

    if (!confirm(`Alterar nível de "${userName}" para "${newRole === 'admin' ? 'Administrador' : 'Usuário'}"?`)) {
        return;
    }

    fetch(`/acesso/usuario/${userId}`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ role: newRole })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            location.reload();
        } else if (data.error) {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao alterar usuário');
    });
}
</script>
@endsection
