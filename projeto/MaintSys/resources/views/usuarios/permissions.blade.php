{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')
{{-- Container principal da página com fundo e espaçamento --}}
<div style="background: var(--bg); min-height: 100vh; padding: 28px;">
    <!-- BREADCRUMB -->
    {{-- Link de retorno à listagem de usuários --}}
    <a href="{{ route('usuarios.index') }}" style="font-family: var(--mono); font-size: 11px; color: var(--accent); text-decoration: none; letter-spacing: 1px; transition: all 0.15s;">
        ← VOLTAR
    </a>

    <!-- HEADER -->
    {{-- Bloco do cabeçalho com nome do usuário, nível e botão de editar --}}
    <div style="margin: 24px 0 32px 0; display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;">
        {{-- Parte esquerda do cabeçalho com subtítulo, nome e nível --}}
        <div>
            {{-- Subtítulo decorativo em estilo monospace --}}
            <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
                // permissões
            </div>
            {{-- Exibe o nome do usuário em caixa alta como título da página --}}
            <h1 style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px;">
                {{ strtoupper($user->name) }}
            </h1>
            {{-- Parágrafo com o nível de acesso atual do usuário --}}
            <p style="font-family: var(--sans); font-size: 14px; color: var(--muted);">
                Nível:
                {{-- Badge inline com cor e estilo determinados pelo role do usuário --}}
                <span style="display: inline-block; padding: 2px 8px; margin-left: 6px; font-family: var(--mono); font-size: 9px; letter-spacing: 1px; text-transform: uppercase; border: 1px solid;
                    {{-- Estilo vermelho para admin, azul para usuário comum --}}
                    {{ $user->role === 'admin' ? 'color: var(--accent2); border-color: rgba(230,51,41,.4); background: rgba(230,51,41,.08);' : 'color: var(--blue); border-color: rgba(56,139,253,.4); background: rgba(56,139,253,.08);' }}
                ">
                    {{-- Exibe "ADMIN" ou "USUÁRIO" conforme o role --}}
                    {{ $user->role === 'admin' ? 'ADMIN' : 'USUÁRIO' }}
                </span>
            </p>
        </div>
        {{-- Verifica se o usuário logado tem permissão para editar usuários --}}
        @if(auth()->user()->hasPermission('usuarios.editar'))
        {{-- Botão de edição do usuário visível apenas para quem tem permissão --}}
        <a href="{{ route('usuarios.edit', $user) }}" style="padding: 10px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; border: 1px solid var(--accent); text-decoration: none; cursor: pointer; transition: all 0.15s; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
            ✏️ EDITAR USUÁRIO
        </a>
        @endif {{-- fim da verificação de permissão para editar --}}
    </div>

    <!-- PERMISSÕES GRID -->
    {{-- Grade responsiva que exibe os cards de permissões por módulo --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 28px;">
        {{-- Itera sobre cada módulo do sistema e suas permissões --}}
        @foreach($permissions as $modulo => $perms)
            {{-- Card individual para cada módulo do sistema --}}
            <div style="background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 4px; overflow: hidden;">
                <!-- Header do Módulo -->
                {{-- Cabeçalho do card com o nome do módulo --}}
                <div style="padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--border);">
                    {{-- Título do módulo com ícone; usa switch para exibição amigável --}}
                    <h2 style="font-family: var(--cond); font-size: 14px; font-weight: 700; color: var(--text); text-transform: uppercase; margin: 0;">
                        {{-- Switch que mapeia o nome técnico do módulo para o nome exibível --}}
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
                                📝 Ordens
                                @break
                            {{-- Caso o módulo seja "historico" --}}
                            @case('historico')
                                📊 Histórico
                                @break
                            {{-- Caso padrão: exibe o nome do módulo com primeira letra maiúscula --}}
                            @default
                                {{ ucfirst($modulo) }}
                        @endswitch {{-- fim do switch de módulos --}}
                    </h2>
                </div>

                <!-- Permissões -->
                {{-- Container com a lista de permissões do módulo --}}
                <div style="space-y: 8px;">
                    {{-- Itera sobre cada permissão do módulo --}}
                    @foreach($perms as $permission)
                        {{-- Item de permissão com ícone de status e descrição --}}
                        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                            {{-- Container do ícone de status (check ou X) --}}
                            <div style="flex-shrink: 0; padding-top: 2px;">
                                {{-- Verifica se esta permissão está na lista de permissões do usuário --}}
                                @if(in_array($permission->id, $userPermissions ?? []))
                                    {{-- Ícone verde de check quando o usuário tem a permissão --}}
                                    <span style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; background: var(--green); color: white; border-radius: 2px; font-weight: bold;">✓</span>
                                @else
                                    {{-- Ícone vermelho de X quando o usuário não tem a permissão --}}
                                    <span style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; background: rgba(207,34,46,.2); color: var(--red); border-radius: 2px; font-weight: bold;">✗</span>
                                @endif {{-- fim da verificação de permissão do usuário --}}
                            </div>
                            {{-- Container com a descrição e o nome técnico da permissão --}}
                            <div style="flex: 1;">
                                {{-- Descrição legível da permissão --}}
                                <div style="font-family: var(--sans); font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 2px;">
                                    {{ $permission->descricao }}
                                </div>
                                {{-- Nome técnico da permissão no formato "modulo.acao" --}}
                                <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 0.5px;">
                                    {{ $permission->name }}
                                </div>
                            </div>
                        </div>
                    @endforeach {{-- fim da iteração sobre as permissões do módulo --}}
                </div>
            </div>
        @endforeach {{-- fim da iteração sobre os módulos --}}
    </div>

    <!-- INFO BOX -->
    {{-- Card informativo explicando como o sistema de permissões funciona --}}
    <div style="background: rgba(0,48,135,.06); border: 1px solid rgba(0,48,135,.2); border-radius: 4px; padding: 20px; margin-bottom: 28px;">
        {{-- Título da seção informativa --}}
        <div style="font-family: var(--mono); font-size: 10px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px;">
            ℹ️ COMO FUNCIONA
        </div>
        {{-- Grade responsiva com os quatro blocos explicativos --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            {{-- Bloco explicando o ícone de acesso ativo --}}
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="color: var(--green); display: block; margin-bottom: 4px;">✓ Acesso Ativo</strong>
                O usuário pode acessar e usar esta funcionalidade.
            </div>
            {{-- Bloco explicando o ícone de acesso bloqueado --}}
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="color: var(--red); display: block; margin-bottom: 4px;">✗ Acesso Bloqueado</strong>
                O usuário não pode acessar esta funcionalidade.
            </div>
            {{-- Bloco explicando como o nível determina as permissões --}}
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="display: block; margin-bottom: 4px;">📊 Nível Determina</strong>
                Permissões são definidas pelo nível (Admin/Usuário).
            </div>
            {{-- Bloco indicando como alterar as permissões --}}
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                <strong style="display: block; margin-bottom: 4px;">🔧 Para Alterar</strong>
                Mude no "Gerenciar Acesso" por nível.
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    {{-- Grade com botões de ação no rodapé da página --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
        {{-- Link de retorno à listagem de usuários --}}
        <a href="{{ route('usuarios.index') }}" style="padding: 12px 16px; background: transparent; color: var(--muted); font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--border-hi); text-decoration: none; cursor: pointer; transition: all 0.15s; text-align: center; border-radius: 4px;">
            ← VOLTAR À LISTA
        </a>
        {{-- Verifica se o usuário logado tem permissão para gerenciar acesso --}}
        @if(auth()->user()->hasPermission('acesso.gerenciar'))
        {{-- Link para a tela de gerenciamento de permissões por role --}}
        <a href="{{ route('acesso.index') }}" style="padding: 12px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--accent); text-decoration: none; cursor: pointer; transition: all 0.15s; text-align: center; border-radius: 4px;">
            🔐 GERENCIAR PERMISSÕES
        </a>
        @endif {{-- fim da verificação de permissão para gerenciar acesso --}}
    </div>
</div>
@endsection {{-- fim da seção de conteúdo principal --}}
