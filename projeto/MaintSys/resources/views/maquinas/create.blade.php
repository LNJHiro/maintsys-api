@extends('layouts.app')

@section('title', 'Nova Máquina')
@section('breadcrumb', '<a href="'.route('maquinas.index').'" style="color:var(--muted);text-decoration:none">máquinas</a> <span class="sep">/</span> <span>nova</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// cadastro</small>
        Nova Máquina
    </div>
    <a href="{{ route('maquinas.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('maquinas.store') }}">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label>Número de Série *</label>
                <input type="text" name="numero_serie" class="form-control"
                       value="{{ old('numero_serie') }}" placeholder="ex: SN-2024-001" required>
                @error('numero_serie')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Modelo *</label>
                <input type="text" name="modelo" class="form-control"
                       value="{{ old('modelo') }}" placeholder="ex: Torno CNC TL-500" required>
                @error('modelo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Fabricante</label>
                <input type="text" name="fabricante" class="form-control"
                       value="{{ old('fabricante') }}" placeholder="ex: Romi, Tornos...">
            </div>
            <div class="form-group">
                <label>Localização no Galpão *</label>
                <input type="text" name="localizacao" class="form-control"
                       value="{{ old('localizacao') }}" placeholder="ex: Galpão A — Setor 3" required>
                @error('localizacao')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Data de Instalação</label>
                <input type="date" name="data_instalacao" class="form-control"
                       value="{{ old('data_instalacao') }}">
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-control" required>
                    <option value="operacional"    {{ old('status','operacional')=='operacional'?'selected':'' }}>Operacional</option>
                    <option value="em_manutencao"  {{ old('status')=='em_manutencao'?'selected':'' }}>Em Manutenção</option>
                    <option value="parada_critica" {{ old('status')=='parada_critica'?'selected':'' }}>Parada Crítica</option>
                    <option value="inativa"        {{ old('status')=='inativa'?'selected':'' }}>Inativa</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Descrição / Observações</label>
            <textarea name="descricao" class="form-control" rows="3"
                      placeholder="Informações adicionais sobre o equipamento...">{{ old('descricao') }}</textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px">
            <button type="submit" class="btn btn-primary">Cadastrar Máquina</button>
            <a href="{{ route('maquinas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@endsection