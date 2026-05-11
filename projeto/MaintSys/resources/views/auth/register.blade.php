@extends('layouts.guest')

@section('title', 'Criar Conta - MaintSys')

@section('content')
<div class="auth-card">
    <div class="auth-icon">☼</div>
    
    <h1 class="auth-title">Criar Conta</h1>
    <p class="auth-subtitle">
        Junte-se ao MaintSys e comece a gerenciar sua manutenção de forma inteligente
    </p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Erro ao registrar:</strong>
                <ul style="margin-top: 8px; margin-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-group @error('name') has-error @enderror">
            <label for="name">Nome Completo</label>
            <input 
                id="name"
                type="text" 
                name="name" 
                value="{{ old('name') }}"
                placeholder="Seu nome completo"
                required 
                autofocus
            />
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group @error('email') has-error @enderror">
            <label for="email">Endereço de Email</label>
            <input 
                id="email"
                type="email" 
                name="email" 
                value="{{ old('email') }}"
                placeholder="seu@email.com"
                required
            />
            @error('email')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group @error('password') has-error @enderror">
            <label for="password">Senha</label>
            <input 
                id="password"
                type="password" 
                name="password"
                placeholder="Mínimo 8 caracteres"
                required 
                minlength="8"
            />
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group @error('password_confirmation') has-error @enderror">
            <label for="password_confirmation">Confirmar Senha</label>
            <input 
                id="password_confirmation"
                type="password" 
                name="password_confirmation"
                placeholder="Repita sua senha"
                required
                minlength="8"
            />
            @error('password_confirmation')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn-submit">
            Criar Conta
        </button>
    </form>

    <div class="auth-divider">ou</div>

    <p class="auth-footer">
        Já tem conta? 
        <a href="{{ route('login') }}">Entre aqui</a>
    </p>
</div>
@endsection