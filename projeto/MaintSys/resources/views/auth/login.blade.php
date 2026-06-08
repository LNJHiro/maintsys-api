{{-- Herda o layout de visitante (sem menu lateral, sem autenticação) --}}
@extends('layouts.guest')

{{-- Define o título da aba do navegador --}}
@section('title', 'Entrar — MaintSys')

{{-- Inicia a seção principal de conteúdo da página de login --}}
@section('content')
{{-- Container centralizado que envolve o formulário de autenticação --}}
<div class="auth-form-wrap">

    {{-- Título de boas-vindas exibido acima do formulário --}}
    <h1 class="auth-form-title">Bem-vindo de volta</h1>
    {{-- Subtítulo com instrução para o usuário --}}
    <p class="auth-form-sub">Entre com suas credenciais para acessar o sistema</p>

    {{-- Exibe mensagem de status de sessão (ex.: "Link de redefinição enviado") --}}
    @if(session('status'))
    {{-- Bloco de alerta de sucesso para mensagens da sessão --}}
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif {{-- fim da verificação de mensagem de sessão --}}

    {{-- Formulário de login enviado via POST para a rota de autenticação --}}
    <form method="POST" action="{{ route('login') }}">
        {{-- Token CSRF obrigatório para proteção da requisição --}}
        @csrf

        {{-- Grupo do campo de e-mail --}}
        <div class="form-group">
            {{-- Rótulo do campo de e-mail --}}
            <label for="email">E-mail</label>
            {{-- Input de e-mail com foco automático e autocompletar de login --}}
            <input type="email" id="email" name="email"
                   {{-- Mantém o e-mail preenchido em caso de erro de validação --}}
                   value="{{ old('email') }}"
                   placeholder="seu@email.com"
                   required autofocus autocomplete="username">
            {{-- Exibe a mensagem de erro de validação para o campo email --}}
            @error('email')
            <span class="form-error">{{ $message }}</span>
            @enderror {{-- fim da exibição de erro do campo email --}}
        </div>

        {{-- Grupo do campo de senha --}}
        <div class="form-group">
            {{-- Rótulo do campo de senha --}}
            <label for="password">Senha</label>
            {{-- Input de senha com autocompletar para a senha atual --}}
            <input type="password" id="password" name="password"
                   placeholder="••••••••"
                   required autocomplete="current-password">
            {{-- Exibe a mensagem de erro de validação para o campo password --}}
            @error('password')
            <span class="form-error">{{ $message }}</span>
            @enderror {{-- fim da exibição de erro do campo password --}}
        </div>

        {{-- Linha com checkbox "Lembrar-me" e link "Esqueceu a senha?" --}}
        <div class="form-row-2">
            {{-- Label clicável com o checkbox de "Lembrar-me" --}}
            <label class="remember">
                {{-- Checkbox que mantém a sessão ativa por período prolongado --}}
                <input type="checkbox" name="remember">
                Lembrar-me
            </label>
            {{-- Verifica se a rota de recuperação de senha existe no sistema --}}
            @if(Route::has('password.request'))
            {{-- Link para a tela de recuperação de senha --}}
            <a href="{{ route('password.request') }}" class="forgot">Esqueceu a senha?</a>
            @endif {{-- fim da verificação da rota de recuperação de senha --}}
        </div>

        {{-- Botão de submissão do formulário de login --}}
        <button type="submit" class="btn-submit">→ Entrar no sistema</button>
    </form>

    {{-- Verifica se a rota de registro está disponível no sistema --}}
    @if(Route::has('register'))
    {{-- Parágrafo com link alternativo para criar uma conta --}}
    <p class="auth-alt">
        {{-- Texto com link para a tela de registro --}}
        Não tem conta? <a href="{{ route('register') }}">Criar conta</a>
    </p>
    @endif {{-- fim da verificação da rota de registro --}}

</div>
@endsection {{-- fim da seção de conteúdo principal --}}
