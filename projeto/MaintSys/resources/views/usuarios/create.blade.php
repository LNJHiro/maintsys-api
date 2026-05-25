@extends('layouts.app')

@section('title', 'Novo Usuario')
@section('breadcrumb')
    <a href="{{ route('usuarios.index') }}" style="color:var(--muted);text-decoration:none">usuários</a>
    <span class="sep">/</span>
    <span>novo</span>
@endsection

@section('content')
<div style="background: var(--bg); min-height: 100vh; padding: 28px;">
    <!-- BREADCRUMB -->
    <a href="{{ route('usuarios.index') }}" style="font-family: var(--mono); font-size: 11px; color: var(--accent); text-decoration: none; letter-spacing: 1px; transition: all 0.15s;">
        ← VOLTAR
    </a>

    <!-- HEADER -->
    <div style="margin: 24px 0 32px 0;">
        <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
            // novo usuário
        </div>
        <h1 style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px;">
            CRIAR NOVO USUÁRIO
        </h1>
        <p style="font-family: var(--sans); font-size: 14px; color: var(--muted);">
            Preencha o formulário abaixo para adicionar um novo usuário ao sistema
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 28px;">
        <!-- FORMULÁRIO -->
        <div style="background: var(--card); border: 1px solid var(--border); padding: 28px; border-radius: 4px;">
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf

                <!-- Nome -->
                <div style="margin-bottom: 20px;">
                    <label for="name" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Nome Completo
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                        placeholder="Ex: João Silva"
                    />
                    @error('name')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Email -->
                <div style="margin-bottom: 20px;">
                    <label for="email" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        E-mail
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                        placeholder="Ex: joao@senai.br"
                    />
                    @error('email')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Senha -->
                <div style="margin-bottom: 20px;">
                    <label for="password" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Senha
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                        placeholder="Mínimo 6 caracteres"
                    />
                    @error('password')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Nível de Acesso -->
                <div style="margin-bottom: 28px;">
                    <label for="role" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Nível de Acesso
                    </label>
                    <select
                        id="role"
                        name="role"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                    >
                        <option value="">Selecione um nível...</option>
                        <option value="usuario" {{ old('role') === 'usuario' ? 'selected' : '' }}>👤 Usuário — Acesso limitado</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>👨‍💼 Administrador — Acesso completo</option>
                    </select>
                    @error('role')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Botões -->
                <div style="display: flex; gap: 10px;">
                    <button
                        type="submit"
                        style="flex: 1; padding: 12px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--accent); cursor: pointer; transition: all 0.15s; border-radius: 4px;"
                    >
                        ✓ CRIAR USUÁRIO
                    </button>
                    <a
                        href="{{ route('usuarios.index') }}"
                        style="flex: 1; padding: 12px 16px; background: transparent; color: var(--muted); font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--border-hi); text-decoration: none; cursor: pointer; transition: all 0.15s; text-align: center; border-radius: 4px;"
                    >
                        CANCELAR
                    </a>
                </div>
            </form>
        </div>

        <!-- SIDEBAR INFO -->
        <div>
            <div style="background: var(--card); border: 1px solid var(--border); padding: 16px; border-radius: 4px; margin-bottom: 16px;">
                <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px;">
                    NÍVEIS DE ACESSO
                </div>

                <div style="margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    <div style="font-family: var(--cond); font-size: 12px; font-weight: 600; color: var(--blue); margin-bottom: 4px;">👤 Usuário</div>
                    <div style="font-family: var(--sans); font-size: 11px; color: var(--muted); line-height: 1.5;">
                        Acesso limitado. Pode visualizar e criar ordens.
                    </div>
                </div>

                <div>
                    <div style="font-family: var(--cond); font-size: 12px; font-weight: 600; color: var(--accent2); margin-bottom: 4px;">👨‍💼 Admin</div>
                    <div style="font-family: var(--sans); font-size: 11px; color: var(--muted); line-height: 1.5;">
                        Acesso completo ao sistema.
                    </div>
                </div>
            </div>

            <div style="background: rgba(0,48,135,.06); border: 1px solid rgba(0,48,135,.2); padding: 12px; border-radius: 4px;">
                <div style="font-family: var(--mono); font-size: 9px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                    ℹ️ DICA
                </div>
                <div style="font-family: var(--sans); font-size: 11px; color: var(--text); line-height: 1.6;">
                    Guarde a senha com segurança. Você pode alterar dados depois.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
