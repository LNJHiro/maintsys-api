@extends('layouts.app')

@section('title', 'Nova O.S.')
@section('breadcrumb', '<a href="'.route('ordens.index').'" style="color:var(--muted);text-decoration:none">ordens</a> <span class="sep">/</span> <span>nova</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// abertura de ordem</small>
        Nova Ordem de Serviço
    </div>
    <a href="{{ route('ordens.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('ordens.store') }}">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label>Máquina *</label>
                <select name="maquina_id" class="form-control" required>
                    <option value="">— Selecione a máquina —</option>
                    @foreach($maquinas as $m)
                    <option value="{{ $m->id }}"
                        {{ (old('maquina_id', request('maquina_id')) == $m->id) ? 'selected' : '' }}>
                        {{ $m->modelo }} — {{ $m->numero_serie }} [{{ $m->localizacao }}]
                    </option>
                    @endforeach
                </select>
                @error('maquina_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Técnico Responsável *</label>
                <select name="tecnico_id" class="form-control" required>
                    <option value="">— Selecione o técnico —</option>
                    @foreach($tecnicos as $t)
                    <option value="{{ $t->id }}" {{ old('tecnico_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->nome }} — {{ $t->especialidade ?? 'Geral' }}
                    </option>
                    @endforeach
                </select>
                @error('tecnico_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tipo de Manutenção *</label>
                <select name="tipo" class="form-control" required>
                    <option value="">— Selecione —</option>
                    <option value="preventiva" {{ old('tipo')=='preventiva'?'selected':'' }}>Preventiva</option>
                    <option value="corretiva"  {{ old('tipo')=='corretiva'?'selected':'' }}>Corretiva</option>
                </select>
                @error('tipo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Prioridade *</label>
                <select name="prioridade" class="form-control" required>
                    <option value="baixa"  {{ old('prioridade')=='baixa'?'selected':'' }}>Baixa</option>
                    <option value="media"  {{ old('prioridade','media')=='media'?'selected':'' }}>Média</option>
                    <option value="alta"   {{ old('prioridade')=='alta'?'selected':'' }}>Alta</option>
                    <option value="critica"{{ old('prioridade')=='critica'?'selected':'' }}>🚨 Crítica</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Descrição do Problema / Serviço *</label>
            <textarea name="descricao" class="form-control" rows="4"
                      placeholder="Descreva detalhadamente o problema identificado ou o serviço a ser executado..."
                      required>{{ old('descricao') }}</textarea>
            @error('descricao')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label>Data Prevista para Conclusão</label>
            <input type="date" name="data_prevista" class="form-control"
                   value="{{ old('data_prevista') }}" style="max-width:200px">
        </div>

        <div style="display:flex;gap:10px;margin-top:8px">
            <button type="submit" class="btn btn-primary">Abrir Ordem de Serviço</button>
            <a href="{{ route('ordens.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@endsection