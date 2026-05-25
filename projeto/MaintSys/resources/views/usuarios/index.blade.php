@extends('layouts.app')

@section('title', 'Usuarios')

@section('breadcrumb')
    <span>usuarios</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// administracao</small>
        Gerenciar Usuarios
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        @if(auth()->user()->hasPermission('acesso.gerenciar'))
        <a href="{{ route('acesso.index') }}" class="btn btn-secondary">Gerenciar Acesso</a>
        @endif
        @if(auth()->user()->hasPermission('usuarios.criar'))
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">+ Novo Usuario</a>
        @endif
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Usuarios</div>
        <div class="stat-value">{{ $users->count() }}</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">Administradores</div>
        <div class="stat-value">{{ $users->where('role', 'admin')->count() }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Usuarios Comuns</div>
        <div class="stat-value">{{ $users->where('role', 'usuario')->count() }}</div>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>E-mail</th>
                <th>Nivel</th>
                <th>Criado em</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td style="font-weight:600">{{ $user->name }}</td>
                <td class="mono" style="color:var(--muted);font-size:16px">{{ $user->email }}</td>
                <td>
                    <span class="badge {{ $user->role === 'admin' ? 'badge-orange' : 'badge-blue' }}">
                        {{ $user->role === 'admin' ? 'Admin' : 'Usuario' }}
                    </span>
                    @if($user->permissions_overridden)
                    <span class="badge badge-yellow" style="margin-left:6px">Individual</span>
                    @endif
                </td>
                <td class="mono" style="color:var(--muted);font-size:16px">{{ $user->created_at->format('d/m/Y') }}</td>
                <td>
                    <div class="actions">
                        @if(auth()->user()->hasPermission('usuarios.editar'))
                        <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-secondary btn-sm">Editar</a>
                        @endif
                        @if(auth()->user()->hasPermission('usuarios.permissoes'))
                        <a href="{{ route('usuarios.permissions', $user) }}" class="btn btn-secondary btn-sm">Perms</a>
                        @endif
                        @if(auth()->user()->hasPermission('usuarios.deletar'))
                        <form action="{{ route('usuarios.destroy', $user) }}" method="POST"
                              onsubmit="confirmDelete(this, 'Deletar usuario {{ $user->name }}?'); return false;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    nenhum usuario cadastrado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
