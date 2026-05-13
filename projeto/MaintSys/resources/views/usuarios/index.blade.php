@extends('layouts.app')

@section('content')
<div style="background: var(--bg); min-height: 100vh; padding: 28px;">
    <!-- HEADER -->
    <div style="margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;">
            <div>
                <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
                    // administração
                </div>
                <h1 style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px;">
                    GERENCIAR USUÁRIOS
                </h1>
                <p style="font-family: var(--sans); font-size: 14px; color: var(--muted);">
                    Crie, edite ou remova usuários do sistema
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('acesso.index') }}" style="padding: 10px 16px; background: var(--border); color: var(--text); font-family: var(--cond); font-size: 13px; font-weight: 600; border: 1px solid var(--border); text-decoration: none; cursor: pointer; transition: all 0.15s; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
                    ← GERENCIAR ACESSO
                </a>
                <a href="{{ route('usuarios.create') }}" style="padding: 10px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; border: 1px solid var(--accent); text-decoration: none; cursor: pointer; transition: all 0.15s; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
                    ➕ NOVO USUÁRIO
                </a>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 28px;">
        <div style="background: var(--card); border: 1px solid var(--border); padding: 20px; position: relative; overflow: hidden; border-radius: 4px;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--accent);"></div>
            <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;">Total de Usuários</div>
            <div style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text);">{{ $users->count() }}</div>
        </div>
        <div style="background: var(--card); border: 1px solid var(--border); padding: 20px; position: relative; overflow: hidden; border-radius: 4px;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--accent2);"></div>
            <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;">Administradores</div>
            <div style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text);">{{ $users->where('role', 'admin')->count() }}</div>
        </div>
        <div style="background: var(--card); border: 1px solid var(--border); padding: 20px; position: relative; overflow: hidden; border-radius: 4px;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--blue);"></div>
            <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;">Usuários Comuns</div>
            <div style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text);">{{ $users->where('role', 'usuario')->count() }}</div>
        </div>
    </div>

    @if($users->count() > 0)
        <!-- TABELA -->
        <div style="background: var(--card); border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--surface); border-bottom: 1px solid var(--border);">
                    <tr>
                        <th style="padding: 14px; text-align: left; font-family: var(--mono); font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted);">Usuário</th>
                        <th style="padding: 14px; text-align: left; font-family: var(--mono); font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted);">E-mail</th>
                        <th style="padding: 14px; text-align: left; font-family: var(--mono); font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted);">Nível</th>
                        <th style="padding: 14px; text-align: left; font-family: var(--mono); font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted);">Criado em</th>
                        <th style="padding: 14px; text-align: right; font-family: var(--mono); font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted);">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr style="border-bottom: 1px solid var(--border); transition: background 0.1s;">
                            <td style="padding: 14px; font-family: var(--sans); font-size: 13px; color: var(--text); font-weight: 600;">
                                👤 {{ $user->name }}
                            </td>
                            <td style="padding: 14px; font-family: var(--mono); font-size: 12px; color: var(--muted);">
                                {{ $user->email }}
                            </td>
                            <td style="padding: 14px;">
                                <span style="display: inline-block; padding: 4px 12px; font-family: var(--mono); font-size: 9px; letter-spacing: 1px; text-transform: uppercase; border: 1px solid; border-radius: 2px;
                                    {{ $user->role === 'admin' ? 'color: var(--accent2); border-color: rgba(230,51,41,.4); background: rgba(230,51,41,.08);' : 'color: var(--blue); border-color: rgba(56,139,253,.4); background: rgba(56,139,253,.08);' }}
                                ">
                                    {{ $user->role === 'admin' ? 'Admin' : 'Usuário' }}
                                </span>
                            </td>
                            <td style="padding: 14px; font-family: var(--mono); font-size: 11px; color: var(--muted);">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td style="padding: 14px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <a href="{{ route('usuarios.edit', $user) }}" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: var(--accent); color: white; font-family: var(--cond); font-size: 11px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid var(--accent); border-radius: 3px; transition: all 0.15s;">
                                        ✏️ EDITAR
                                    </a>
                                    <a href="{{ route('usuarios.permissions', $user) }}" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: var(--green); color: white; font-family: var(--cond); font-size: 11px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid var(--green); border-radius: 3px; transition: all 0.15s;">
                                        🔑 PERMS
                                    </a>
                                    <form action="{{ route('usuarios.destroy', $user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Deletar usuário permanentemente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: transparent; color: var(--red); font-family: var(--cond); font-size: 11px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid rgba(207,34,46,.4); border-radius: 3px; transition: all 0.15s;">
                                            🗑️ DEL
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- EMPTY STATE -->
        <div style="background: var(--card); border: 1px solid var(--border); padding: 60px 28px; text-align: center; border-radius: 4px;">
            <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
            <div style="font-family: var(--cond); font-size: 18px; font-weight: 600; color: var(--text); margin-bottom: 8px;">
                Nenhum usuário cadastrado
            </div>
            <p style="font-family: var(--sans); font-size: 13px; color: var(--muted); margin-bottom: 24px;">
                Comece criando o primeiro usuário do sistema
            </p>
            <a href="{{ route('usuarios.create') }}" style="display: inline-block; padding: 10px 20px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid var(--accent); border-radius: 4px; transition: all 0.15s;">
                ➕ CRIAR PRIMEIRO USUÁRIO
            </a>
        </div>
    @endif

    <!-- FOOTER INFO -->
    <div style="margin-top: 32px; background: rgba(0,48,135,.06); border: 1px solid rgba(0,48,135,.2); border-radius: 4px; padding: 20px;">
        <div style="font-family: var(--mono); font-size: 10px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px;">
            ℹ️ INFORMAÇÕES
        </div>
        <ul style="font-family: var(--sans); font-size: 13px; color: var(--text); list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <li>• <strong>Editar</strong> = Altere nome, email, senha e nível</li>
            <li>• <strong>Permissões</strong> = Visualize as permissões do usuário</li>
            <li>• <strong>Deletar</strong> = Remova o usuário permanentemente</li>
            <li>• Admin Master não aparece aqui (protegido)</li>
        </ul>
    </div>
</div>
@endsection
