@extends('layouts.app')

@section('title', 'Novo Técnico')
@section('breadcrumb', '<a href="'.route('tecnicos.index').'" style="color:var(--muted);text-decoration:none">técnicos</a> <span class="sep">/</span> <span>novo</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// cadastro</small>
        Novo Técnico
    </div>
    <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('tecnicos.store') }}">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" name="nome" class="form-control"
                       value="{{ old('nome') }}" placeholder="ex: João da Silva" required>
                @error('nome')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Matrícula *</label>
                <input type="text" name="matricula" class="form-control"
                       value="{{ old('matricula') }}" placeholder="ex: TEC-001" required>
                @error('matricula')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>E-mail *</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" placeholder="tecnico@empresa.com" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Especialidade</label>
                <input type="text" name="especialidade" class="form-control"
                       value="{{ old('especialidade') }}" placeholder="ex: Elétrica, Hidráulica, Mecânica">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Senha *</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Mínimo 8 caracteres" required>
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Confirmar Senha *</label>
                <input type="password" name="password_confirmation" class="form-control"
                       placeholder="Repita a senha" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="telefone" class="form-control"
                       value="{{ old('telefone') }}" placeholder="(19) 99999-9999">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px">
                <input type="hidden" name="ativo" value="0">
                <input type="checkbox" name="ativo" value="1" id="ativo"
                       {{ old('ativo', '1') ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--accent)">
                <label for="ativo" style="font-family:var(--cond);font-size:14px;color:var(--text);letter-spacing:0">
                    Técnico Ativo
                </label>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px">
            <button type="submit" class="btn btn-primary">Cadastrar Técnico</button>
            <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@endsection