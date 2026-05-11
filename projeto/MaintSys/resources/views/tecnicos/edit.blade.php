@extends('layouts.app')

@section('title', 'Editar Técnico')
@section('breadcrumb', '<a href="'.route('tecnicos.index').'" style="color:var(--muted);text-decoration:none">técnicos</a> <span class="sep">/</span> <span>editar</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// edição</small>
        {{ $tecnico->nome }}
    </div>
    <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('tecnicos.update', $tecnico) }}">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" name="nome" class="form-control"
                       value="{{ old('nome', $tecnico->nome) }}" required>
                @error('nome')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Matrícula *</label>
                <input type="text" name="matricula" class="form-control"
                       value="{{ old('matricula', $tecnico->matricula) }}" required>
                @error('matricula')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>E-mail *</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email', $tecnico->email) }}" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Especialidade</label>
                <input type="text" name="especialidade" class="form-control"
                       value="{{ old('especialidade', $tecnico->especialidade) }}">
            </div>
        </div>

        <div style="background:rgba(240,165,0,.05);border:1px solid rgba(240,165,0,.2);padding:12px 16px;margin-bottom:18px">
            <div style="font-family:var(--mono);font-size:10px;color:var(--accent);margin-bottom:10px;letter-spacing:1px">
                // REDEFINIR SENHA — deixe em branco para manter a atual
            </div>
            <div class="form-row">
                <div class="form-group" style="margin-bottom:0">
                    <label>Nova Senha</label>
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres">
                    @error('password')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="telefone" class="form-control"
                       value="{{ old('telefone', $tecnico->telefone) }}">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px">
                <input type="hidden" name="ativo" value="0">
                <input type="checkbox" name="ativo" value="1" id="ativo"
                       {{ old('ativo', $tecnico->ativo) ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--accent)">
                <label for="ativo" style="font-family:var(--cond);font-size:14px;color:var(--text);letter-spacing:0">
                    Técnico Ativo
                </label>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@endsection