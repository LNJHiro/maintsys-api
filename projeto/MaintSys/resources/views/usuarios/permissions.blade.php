@extends('layouts.app')

@section('content')
<div style="background: var(--bg); min-height: 100vh; padding: 28px;">
    <!-- BREADCRUMB -->
    <a href="{{ route('usuarios.index') }}" style="font-family: var(--mono); font-size: 11px; color: var(--accent); text-decoration: none; letter-spacing: 1px; transition: all 0.15s;">
        ← VOLTAR
    </a>

    <!-- HEADER -->
    <div style="margin: 24px 0 32px 0; display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;">
        <div>
            <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
                // permissões
            </div>
            <h1 style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px;">
                {{ strtoupper($user->name) }}
            </h1>
            <p style="font-family: var(--sans); font-size: 14px; color: var(--muted);">
                Nível:
                <span style="display: inline-block; padding: 2px 8px; margin-left: 6px; font-family: var(--mono); font-size: 9px; letter-spacing: 1px; text-transform: uppercase; border: 1px solid;
                    {{ $user->role === 'admin' ? 'color: var(--accent2); border-color: rgba(230,51,41,.4); background: rgba(230,51,41,.08);' : 'color: var(--blue); border-color: rgba(56,139,253,.4); background: rgba(56,139,253,.08);' }}
                ">
                    {{ $user->role === 'admin' ? 'ADMIN' : 'USUÁRIO' }}
                </span>
            </p>
        </div>
        @if(auth()->user()->hasPermission('usuarios.editar'))
        <a href="{{ route('usuarios.edit', $user) }}" style="padding: 10px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; border: 1px solid var(--accent); text-decoration: none; cursor: pointer; transition: all 0.15s; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
            ✏️ EDITAR USUÁRIO
        </a>
        @endif
    </div>

    <!-- PERMISSÕES GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 28px;">
        @foreach($permissions as $modulo => $perms)
            <div style="background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 4px; overflow: hidden;">
                <!-- Header do Módulo -->
                <div style="padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--border);">
                    <h2 style="font-family: var(--cond); font-size: 14px; font-weight: 700; color: var(--text); text-transform: uppercase; margin: 0;">
                        @switch($modulo)
                            @case('maquinas')
                                📋 Máquinas
                                @break
                            @case('tecnicos')
                                👨‍🔧 Técnicos
                                @break
                            @case('ordens')
                                📝 Ordens
                                @break
                            @case('historico')
                                📊 Histórico
                                @break
                            @default
                                {{ ucfirst($modulo) }}
                        @endswitch
                    </h2>
                </div>

                <!-- Permissões -->
                <div style="space-y: 8px;">
                    @foreach($perms as $permission)
                        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                            <div style="flex-shrink: 0; padding-top: 2px;">
                                @if(in_array($permission->id, $userPermissions ?? []))
                                    <span style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; background: var(--green); color: white; border-radius: 2px; font-weight: bold;">✓</span>
                                @else
                                    <span style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; background: rgba(207,34,46,.2); color: var(--red); border-radius: 2px; font-weight: bold;">✗</span>
                                @endif
                            </div>
                            <div style="flex: 1;">
                                <div style="font-family: var(--sans); font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 2px;">
                                    {{ $permission->descricao }}
                                </div>
                                <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 0.5px;">
                                    {{ $permission->name }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- INFO BOX -->
    <div style="background: rgba(0,48,135,.06); border: 1px solid rgba(0,48,135,.2); border-radius: 4px; padding: 20px; margin-bottom: 28px;">
        <div style="font-family: var(--mono); font-size: 10px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px;">
            ℹ️ COMO FUNCIONA
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="color: var(--green); display: block; margin-bottom: 4px;">✓ Acesso Ativo</strong>
                O usuário pode acessar e usar esta funcionalidade.
            </div>
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="color: var(--red); display: block; margin-bottom: 4px;">✗ Acesso Bloqueado</strong>
                O usuário não pode acessar esta funcionalidade.
            </div>
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="display: block; margin-bottom: 4px;">📊 Nível Determina</strong>
                Permissões são definidas pelo nível (Admin/Usuário).
            </div>
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="display: block; margin-bottom: 4px;">🔧 Para Alterar</strong>
                Mude no "Gerenciar Acesso" por nível.
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
        <a href="{{ route('usuarios.index') }}" style="padding: 12px 16px; background: transparent; color: var(--muted); font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--border-hi); text-decoration: none; cursor: pointer; transition: all 0.15s; text-align: center; border-radius: 4px;">
            ← VOLTAR À LISTA
        </a>
        @if(auth()->user()->hasPermission('acesso.gerenciar'))
        <a href="{{ route('acesso.index') }}" style="padding: 12px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--accent); text-decoration: none; cursor: pointer; transition: all 0.15s; text-align: center; border-radius: 4px;">
            🔐 GERENCIAR PERMISSÕES
        </a>
        @endif
    </div>
</div>
@endsection
