@extends('layouts.app')

@section('title', 'Editar O.S.')
@section('breadcrumb', '<a href="'.route('ordens.index').'" style="color:var(--muted);text-decoration:none">ordens</a> <span class="sep">/</span> <span>'.e($ordem->numero).'</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// edição de ordem</small>
        {{ $ordem->numero }}
    </div>
    <a href="{{ route('ordens.show', $ordem) }}" class="btn btn-secondary">← Voltar</a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('ordens.update', $ordem) }}">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label>Máquina *</label>
                <select name="maquina_id" class="form-control" required>
                    @foreach($maquinas as $m)
                    <option value="{{ $m->id }}" {{ old('maquina_id',$ordem->maquina_id)==$m->id?'selected':'' }}>
                        {{ $m->modelo }} — {{ $m->numero_serie }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Técnico Responsável *</label>
                <select name="tecnico_id" class="form-control" required>
                    @foreach($tecnicos as $t)
                    <option value="{{ $t->id }}" {{ old('tecnico_id',$ordem->tecnico_id)==$t->id?'selected':'' }}>
                        {{ $t->nome }} — {{ $t->especialidade ?? 'Geral' }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tipo *</label>
                <select name="tipo" class="form-control" required>
                    <option value="preventiva" {{ old('tipo',$ordem->tipo)=='preventiva'?'selected':'' }}>Preventiva</option>
                    <option value="corretiva"  {{ old('tipo',$ordem->tipo)=='corretiva'?'selected':'' }}>Corretiva</option>
                </select>
            </div>
            <div class="form-group">
                <label>Prioridade *</label>
                <select name="prioridade" class="form-control" required>
                    <option value="baixa"   {{ old('prioridade',$ordem->prioridade)=='baixa'?'selected':'' }}>Baixa</option>
                    <option value="media"   {{ old('prioridade',$ordem->prioridade)=='media'?'selected':'' }}>Média</option>
                    <option value="alta"    {{ old('prioridade',$ordem->prioridade)=='alta'?'selected':'' }}>Alta</option>
                    <option value="critica" {{ old('prioridade',$ordem->prioridade)=='critica'?'selected':'' }}>🚨 Crítica</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" id="status-select" class="form-control" required>
                    <option value="aberta"       {{ old('status',$ordem->status)=='aberta'?'selected':'' }}>Aberta</option>
                    <option value="em_andamento" {{ old('status',$ordem->status)=='em_andamento'?'selected':'' }}>Em Andamento</option>
                    <option value="concluida"    {{ old('status',$ordem->status)=='concluida'?'selected':'' }}>Concluída</option>
                    <option value="cancelada"    {{ old('status',$ordem->status)=='cancelada'?'selected':'' }}>Cancelada</option>
                </select>
            </div>
            <div class="form-group">
                <label>Data Prevista</label>
                <input type="date" name="data_prevista" class="form-control"
                       value="{{ old('data_prevista', $ordem->data_prevista?->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="form-group">
            <label>Descrição *</label>
            <textarea name="descricao" class="form-control" rows="3" required>{{ old('descricao', $ordem->descricao) }}</textarea>
        </div>

        <div class="form-group">
            <label>Solução Aplicada</label>
            <textarea name="solucao" class="form-control" rows="3"
                      placeholder="Descreva a solução aplicada ao concluir a O.S...">{{ old('solucao', $ordem->solucao) }}</textarea>
        </div>

        {{-- CAMPOS EXTRAS AO CONCLUIR --}}
        <div id="campos-conclusao" style="display:none; border-top:1px solid var(--border); padding-top:18px; margin-top:4px;">
            <div style="font-family:var(--mono);font-size:10px;color:var(--green);letter-spacing:2px;margin-bottom:14px;">
                ✓ // DADOS DE CONCLUSÃO
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Tempo de Parada (horas)</label>
                    <input type="number" name="tempo_parada_horas" class="form-control"
                           step="0.5" min="0" value="{{ old('tempo_parada_horas', 0) }}"
                           placeholder="ex: 2.5">
                </div>
                <div class="form-group">
                    <label>Custo Total (R$)</label>
                    <input type="number" name="custo" class="form-control"
                           step="0.01" min="0" value="{{ old('custo', 0) }}"
                           placeholder="ex: 350.00">
                </div>
            </div>
            <div class="form-group">
                <label>Peças Utilizadas</label>
                <textarea name="pecas_utilizadas" class="form-control" rows="2"
                          placeholder="ex: Rolamento 6205, Correia B-52...">{{ old('pecas_utilizadas') }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:18px">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="{{ route('ordens.show', $ordem) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const statusSelect = document.getElementById('status-select');
    const camposConclusao = document.getElementById('campos-conclusao');

    function toggleConclusao() {
        camposConclusao.style.display = statusSelect.value === 'concluida' ? 'block' : 'none';
    }

    statusSelect.addEventListener('change', toggleConclusao);
    toggleConclusao();
</script>
@endpush

@endsection