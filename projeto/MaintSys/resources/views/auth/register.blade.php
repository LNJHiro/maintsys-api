@extends('layouts.guest')

@section('title', 'Criar Conta — MaintSys')

@section('content')
<div class="auth-form-wrap">

    <h1 class="auth-form-title">Criar conta</h1>
    <p class="auth-form-sub">Preencha os dados para acessar o sistema</p>

    @if($errors->any())
    <div class="alert alert-error">
        @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Nome Completo</label>
            <input type="text" id="name" name="name"
                   value="{{ old('name') }}"
                   placeholder="Seu nome completo"
                   required autofocus>
            @error('name')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="seu@email.com"
                   required>
            @error('email')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password"
                   placeholder="Mínimo 8 caracteres"
                   required minlength="8">
            @error('password')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar Senha</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="Repita a senha"
                   required minlength="8">
            @error('password_confirmation')
            <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn-submit">→ Criar conta</button>
    </form>

    <p class="auth-alt">
        Já tem conta? <a href="{{ route('login') }}">Entrar</a>
    </p>

</div>
@endsection
