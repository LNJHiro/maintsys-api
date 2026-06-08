{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador --}}
@section('title', 'Editar Usuario')
{{-- Define o breadcrumb de navegação com link para a listagem de usuários --}}
@section('breadcrumb')
    {{-- Link clicável que retorna à listagem de usuários --}}
    <a href="{{ route('usuarios.index') }}" style="color:var(--muted);text-decoration:none">usuários</a>
    {{-- Separador visual entre os itens do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Item atual do breadcrumb indicando edição --}}
    <span>editar</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')
{{-- Container principal da página com fundo e espaçamento --}}
<div style="background: var(--bg); min-height: 100vh; padding: 28px;">

    <!-- HEADER -->
    {{-- Bloco do cabeçalho com subtítulo, nome do usuário e descrição --}}
    <div style="margin: 24px 0 32px 0;">
        {{-- Subtítulo decorativo em estilo monospace --}}
        <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
            // editar usuário
        </div>
        {{-- Exibe o nome do usuário sendo editado em caixa alta --}}
        <h1 style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px;">
            {{ strtoupper($user->name) }}
        </h1>
        {{-- Instrução descritiva para o operador --}}
        <p style="font-family: var(--sans); font-size: 14px; color: var(--muted);">
            Atualize as informações do usuário
        </p>
    </div>

    {{-- Layout em grid de duas colunas: formulário à esquerda e sidebar à direita --}}
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 28px;">
        <!-- FORMULÁRIO -->
        {{-- Card que contém o formulário de edição do usuário --}}
        <div style="background: var(--card); border: 1px solid var(--border); padding: 28px; border-radius: 4px;">
            {{-- Formulário enviado via POST com método PUT simulado para atualizar o usuário --}}
            <form action="{{ route('usuarios.update', $user) }}" method="POST">
                {{-- Token CSRF obrigatório para proteção da requisição --}}
                @csrf
                {{-- Diretiva que simula o método HTTP PUT --}}
                @method('PUT')

                <!-- Nome -->
                {{-- Grupo do campo de nome completo --}}
                <div style="margin-bottom: 20px;">
                    {{-- Rótulo do campo de nome --}}
                    <label for="name" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Nome Completo
                    </label>
                    {{-- Input de texto com o nome atual do usuário; mantém valor em caso de erro --}}
                    <input
                        type="text"
                        id="name"
                        name="name"
                        {{-- Prioriza o valor enviado anteriormente; cai para o valor atual do usuário --}}
                        value="{{ old('name', $user->name) }}"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                    />
                    {{-- Exibe a mensagem de erro de validação para o campo name --}}
                    @error('name')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror {{-- fim da exibição de erro do campo name --}}
                </div>

                <!-- Email -->
                {{-- Grupo do campo de e-mail --}}
                <div style="margin-bottom: 20px;">
                    {{-- Rótulo do campo de e-mail --}}
                    <label for="email" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        E-mail
                    </label>
                    {{-- Input de e-mail com o endereço atual do usuário --}}
                    <input
                        type="email"
                        id="email"
                        name="email"
                        {{-- Prioriza o valor enviado anteriormente; cai para o e-mail atual --}}
                        value="{{ old('email', $user->email) }}"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                    />
                    {{-- Exibe a mensagem de erro de validação para o campo email --}}
                    @error('email')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror {{-- fim da exibição de erro do campo email --}}
                </div>

                <!-- Senha -->
                {{-- Grupo do campo de nova senha --}}
                <div style="margin-bottom: 20px;">
                    {{-- Rótulo indicando que o campo é opcional para manter a senha atual --}}
                    <label for="password" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Senha (deixe em branco para manter)
                    </label>
                    {{-- Input de senha sem valor pré-preenchido; vazio mantém a senha existente --}}
                    <input
                        type="password"
                        id="password"
                        name="password"
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                        placeholder="Nova senha (deixe em branco para manter)"
                    />
                    {{-- Exibe a mensagem de erro de validação para o campo password --}}
                    @error('password')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror {{-- fim da exibição de erro do campo password --}}
                </div>

                <!-- Nível de Acesso -->
                {{-- Grupo do campo de nível de acesso --}}
                <div style="margin-bottom: 28px;">
                    {{-- Rótulo do campo de nível --}}
                    <label for="role" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Nível de Acesso
                    </label>
                    {{-- Select obrigatório com o nível atual do usuário pré-selecionado --}}
                    <select
                        id="role"
                        name="role"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                    >
                        {{-- Opção de usuário; selecionada se for o nível atual --}}
                        <option value="usuario" {{ old('role', $user->role) === 'usuario' ? 'selected' : '' }}>👤 Usuário — Acesso limitado</option>
                        {{-- Opção de administrador; selecionada se for o nível atual --}}
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>👨‍💼 Administrador — Acesso completo</option>
                    </select>
                    {{-- Exibe a mensagem de erro de validação para o campo role --}}
                    @error('role')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror {{-- fim da exibição de erro do campo role --}}
                </div>

                <!-- Botões -->
                {{-- Container dos botões de salvar e cancelar --}}
                <div style="display: flex; gap: 10px;">
                    {{-- Botão de submissão que salva as alterações do usuário --}}
                    <button
                        type="submit"
                        style="flex: 1; padding: 12px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--accent); cursor: pointer; transition: all 0.15s; border-radius: 4px;"
                    >
                        ✓ SALVAR ALTERAÇÕES
                    </button>
                    {{-- Link de cancelamento que retorna à listagem sem salvar --}}
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
        {{-- Coluna da direita com informações do usuário sendo editado --}}
        <div>
            <!-- Card Info -->
            {{-- Card com dados resumidos do usuário atual --}}
            <div style="background: var(--card); border: 1px solid var(--border); padding: 16px; border-radius: 4px; margin-bottom: 16px;">
                {{-- Bloco com o nível de acesso atual do usuário --}}
                <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    {{-- Rótulo informando o nível atual --}}
                    <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px;">Nível Atual</div>
                    {{-- Verifica se o usuário é administrador para exibir badge correspondente --}}
                    @if($user->role === 'admin')
                    {{-- Badge de administrador com cor vermelha --}}
                    <span style="display: inline-block; padding: 4px 12px; font-family: var(--mono); font-size: 9px; letter-spacing: 1px; text-transform: uppercase; border: 1px solid; color: var(--accent2); border-color: rgba(230,51,41,.4); background: rgba(230,51,41,.08);">
                        ADMIN
                    </span>
                    @else
                    {{-- Badge de usuário comum com cor azul --}}
                    <span style="display: inline-block; padding: 4px 12px; font-family: var(--mono); font-size: 9px; letter-spacing: 1px; text-transform: uppercase; border: 1px solid; color: var(--blue); border-color: rgba(56,139,253,.4); background: rgba(56,139,253,.08);">
                        USUÁRIO
                    </span>
                    @endif {{-- fim da verificação de role --}}
                </div>

                {{-- Bloco com o e-mail atual do usuário --}}
                <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    {{-- Rótulo do e-mail --}}
                    <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px;">E-mail</div>
                    {{-- Exibe o e-mail atual do usuário --}}
                    <div style="font-family: var(--mono); font-size: 11px; color: var(--text);">{{ $user->email }}</div>
                </div>

                {{-- Bloco com a data de criação do usuário --}}
                <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    {{-- Rótulo da data de criação --}}
                    <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px;">Membro desde</div>
                    {{-- Exibe a data de criação formatada em dia/mês/ano --}}
                    <div style="font-family: var(--sans); font-size: 13px; color: var(--text);">{{ $user->created_at->format('d/m/Y') }}</div>
                </div>

                {{-- Link que navega para a tela de permissões do usuário --}}
                <a
                    href="{{ route('usuarios.permissions', $user) }}"
                    style="display: block; text-align: center; padding: 8px 12px; background: var(--green); color: white; font-family: var(--cond); font-size: 12px; font-weight: 600; text-decoration: none; border-radius: 3px; transition: all 0.15s;"
                >
                    🔑 VER PERMISSÕES
                </a>
            </div>

            <!-- Info Box -->
            {{-- Card informativo com fundo azulado --}}
            <div style="background: rgba(0,48,135,.06); border: 1px solid rgba(0,48,135,.2); padding: 12px; border-radius: 4px;">
                {{-- Rótulo informativo --}}
                <div style="font-family: var(--mono); font-size: 9px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                    ℹ️ INFORMAÇÃO
                </div>
                {{-- Texto explicando como as permissões são determinadas --}}
                <div style="font-family: var(--sans); font-size: 11px; color: var(--text); line-height: 1.6;">
                    As permissões são determinadas pelo nível de acesso.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection {{-- fim da seção de conteúdo principal --}}
