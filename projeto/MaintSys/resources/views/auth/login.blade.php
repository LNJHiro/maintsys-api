@extends('layouts.guest')

@section('title', 'Entrar — MaintSys')

@section('content')
<div class="auth-form-wrap">

    <h1 class="auth-form-title">Bem-vindo de volta</h1>
    <p class="auth-form-sub">Entre com suas credenciais para acessar o sistema</p>

    @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="seu@email.com"
                   required autofocus autocomplete="username">
            @error('email')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••"
                   required autocomplete="current-password">
            @error('password')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-row-2">
            <label class="remember">
                <input type="checkbox" name="remember">
                Lembrar-me
            </label>
            @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="forgot">Esqueceu a senha?</a>
            @endif
        </div>

        <button type="submit" class="btn-submit">→ Entrar no sistema</button>
    </form>

    @if(Route::has('register'))
    <p class="auth-alt">
        Não tem conta? <a href="{{ route('register') }}">Criar conta</a>
    </p>
    @endif

</div>
@endsection
