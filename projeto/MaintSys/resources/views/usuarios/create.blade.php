{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador --}}
@section('title', 'Novo Usuario')
{{-- Define o breadcrumb de navegação com link para a listagem de usuários --}}
@section('breadcrumb')
    {{-- Link clicável que retorna à listagem de usuários --}}
    <a href="{{ route('usuarios.index') }}" style="color:var(--muted);text-decoration:none">usuários</a>
    {{-- Separador visual entre os itens do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Item atual do breadcrumb indicando criação --}}
    <span>novo</span>
@endsection {{-- fim da seção breadcrumb --}}

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
    {{-- Bloco do cabeçalho com subtítulo, título e descrição --}}
    <div style="margin: 24px 0 32px 0;">
        {{-- Subtítulo decorativo em estilo monospace --}}
        <div style="font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
            // novo usuário
        </div>
        {{-- Título principal da página em caixa alta --}}
        <h1 style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px;">
            CRIAR NOVO USUÁRIO
        </h1>
        {{-- Instrução descritiva para o usuário --}}
        <p style="font-family: var(--sans); font-size: 14px; color: var(--muted);">
            Preencha o formulário abaixo para adicionar um novo usuário ao sistema
        </p>
    </div>

    {{-- Layout em grid de duas colunas: formulário à esquerda e sidebar à direita --}}
    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 28px;">
        <!-- FORMULÁRIO -->
        {{-- Card que contém o formulário de criação de usuário --}}
        <div style="background: var(--card); border: 1px solid var(--border); padding: 28px; border-radius: 4px;">
            {{-- Formulário enviado via POST para a rota de armazenamento --}}
            <form action="{{ route('usuarios.store') }}" method="POST">
                {{-- Token CSRF obrigatório para proteção da requisição --}}
                @csrf

                <!-- Nome -->
                {{-- Grupo do campo de nome completo --}}
                <div style="margin-bottom: 20px;">
                    {{-- Rótulo do campo de nome --}}
                    <label for="name" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Nome Completo
                    </label>
                    {{-- Input de texto obrigatório para o nome do usuário --}}
                    <input
                        type="text"
                        id="name"
                        name="name"
                        {{-- Mantém o valor preenchido em caso de erro de validação --}}
                        value="{{ old('name') }}"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                        placeholder="Ex: João Silva"
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
                    {{-- Input de e-mail obrigatório com validação de formato --}}
                    <input
                        type="email"
                        id="email"
                        name="email"
                        {{-- Mantém o valor preenchido em caso de erro de validação --}}
                        value="{{ old('email') }}"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                        placeholder="Ex: joao@senai.br"
                    />
                    {{-- Exibe a mensagem de erro de validação para o campo email --}}
                    @error('email')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror {{-- fim da exibição de erro do campo email --}}
                </div>

                <!-- Senha -->
                {{-- Grupo do campo de senha --}}
                <div style="margin-bottom: 20px;">
                    {{-- Rótulo do campo de senha --}}
                    <label for="password" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Senha
                    </label>
                    {{-- Input de senha obrigatório; o valor nunca é repreenchido por segurança --}}
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                        placeholder="Mínimo 6 caracteres"
                    />
                    {{-- Exibe a mensagem de erro de validação para o campo password --}}
                    @error('password')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror {{-- fim da exibição de erro do campo password --}}
                </div>

                <!-- Nível de Acesso -->
                {{-- Grupo do campo de seleção do nível de acesso --}}
                <div style="margin-bottom: 28px;">
                    {{-- Rótulo do campo de nível de acesso --}}
                    <label for="role" style="display: block; font-family: var(--mono); font-size: 10px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                        Nível de Acesso
                    </label>
                    {{-- Select obrigatório para definir as permissões do usuário --}}
                    <select
                        id="role"
                        name="role"
                        required
                        style="width: 100%; padding: 10px 12px; background: var(--surface); border: 1px solid var(--border-hi); color: var(--text); font-family: var(--sans); font-size: 13px; outline: none; transition: border-color 0.15s; border-radius: 3px;"
                    >
                        {{-- Opção padrão vazia para forçar seleção --}}
                        <option value="">Selecione um nível...</option>
                        {{-- Opção de usuário comum com acesso limitado --}}
                        <option value="usuario" {{ old('role') === 'usuario' ? 'selected' : '' }}>👤 Usuário — Acesso limitado</option>
                        {{-- Opção de administrador com acesso completo --}}
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>👨‍💼 Administrador — Acesso completo</option>
                    </select>
                    {{-- Exibe a mensagem de erro de validação para o campo role --}}
                    @error('role')
                        <div style="margin-top: 4px; font-family: var(--mono); font-size: 10px; color: var(--red);">
                            {{ $message }}
                        </div>
                    @enderror {{-- fim da exibição de erro do campo role --}}
                </div>

                <!-- Botões -->
                {{-- Container dos botões de confirmar e cancelar --}}
                <div style="display: flex; gap: 10px;">
                    {{-- Botão de submissão que cria o usuário --}}
                    <button
                        type="submit"
                        style="flex: 1; padding: 12px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--accent); cursor: pointer; transition: all 0.15s; border-radius: 4px;"
                    >
                        ✓ CRIAR USUÁRIO
                    </button>
                    {{-- Link de cancelamento que retorna à listagem sem criar --}}
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
        {{-- Coluna da direita com informações explicativas sobre os níveis de acesso --}}
        <div>
            {{-- Card informativo descrevendo os níveis de acesso disponíveis --}}
            <div style="background: var(--card); border: 1px solid var(--border); padding: 16px; border-radius: 4px; margin-bottom: 16px;">
                {{-- Rótulo da seção de níveis de acesso --}}
                <div style="font-family: var(--mono); font-size: 9px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px;">
                    NÍVEIS DE ACESSO
                </div>

                {{-- Bloco explicativo do nível "Usuário" --}}
                <div style="margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    {{-- Nome do nível com cor azul --}}
                    <div style="font-family: var(--cond); font-size: 12px; font-weight: 600; color: var(--blue); margin-bottom: 4px;">👤 Usuário</div>
                    {{-- Descrição do nível de usuário comum --}}
                    <div style="font-family: var(--sans); font-size: 11px; color: var(--muted); line-height: 1.5;">
                        Acesso limitado. Pode visualizar e criar ordens.
                    </div>
                </div>

                {{-- Bloco explicativo do nível "Admin" --}}
                <div>
                    {{-- Nome do nível com cor de destaque --}}
                    <div style="font-family: var(--cond); font-size: 12px; font-weight: 600; color: var(--accent2); margin-bottom: 4px;">👨‍💼 Admin</div>
                    {{-- Descrição do nível de administrador --}}
                    <div style="font-family: var(--sans); font-size: 11px; color: var(--muted); line-height: 1.5;">
                        Acesso completo ao sistema.
                    </div>
                </div>
            </div>

            {{-- Card de dica com fundo azulado --}}
            <div style="background: rgba(0,48,135,.06); border: 1px solid rgba(0,48,135,.2); padding: 12px; border-radius: 4px;">
                {{-- Rótulo da dica em cor de destaque --}}
                <div style="font-family: var(--mono); font-size: 9px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                    ℹ️ DICA
                </div>
                {{-- Texto da dica sobre segurança de senha --}}
                <div style="font-family: var(--sans); font-size: 11px; color: var(--text); line-height: 1.6;">
                    Guarde a senha com segurança. Você pode alterar dados depois.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection {{-- fim da seção de conteúdo principal --}}
