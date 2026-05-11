@extends('layouts.app')

@section('title', 'Editar Máquina')
@section('breadcrumb', '<a href="'.route('maquinas.index').'" style="color:var(--muted);text-decoration:none">máquinas</a> <span class="sep">/</span> <span>editar</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// edição</small>
        {{ $maquina->modelo }}
    </div>
    <a href="{{ route('maquinas.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('maquinas.update', $maquina) }}">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label>Número de Série *</label>
                <input type="text" name="numero_serie" class="form-control"
                       value="{{ old('numero_serie', $maquina->numero_serie) }}" required>
                @error('numero_serie')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Modelo *</label>
                <input type="text" name="modelo" class="form-control"
                       value="{{ old('modelo', $maquina->modelo) }}" required>
                @error('modelo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Fabricante</label>
                <input type="text" name="fabricante" class="form-control"
                       value="{{ old('fabricante', $maquina->fabricante) }}">
            </div>
            <div class="form-group">
                <label>Localização no Galpão *</label>
                <input type="text" name="localizacao" class="form-control"
                       value="{{ old('localizacao', $maquina->localizacao) }}" required>
                @error('localizacao')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Data de Instalação</label>
                <input type="date" name="data_instalacao" class="form-control"
                       value="{{ old('data_instalacao', $maquina->data_instalacao?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-control" required>
                    <option value="operacional"    {{ old('status',$maquina->status)=='operacional'?'selected':'' }}>Operacional</option>
                    <option value="em_manutencao"  {{ old('status',$maquina->status)=='em_manutencao'?'selected':'' }}>Em Manutenção</option>
                    <option value="parada_critica" {{ old('status',$maquina->status)=='parada_critica'?'selected':'' }}>Parada Crítica</option>
                    <option value="inativa"        {{ old('status',$maquina->status)=='inativa'?'selected':'' }}>Inativa</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Descrição / Observações</label>
            <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $maquina->descricao) }}</textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('maquinas.show', $maquina) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@endsection