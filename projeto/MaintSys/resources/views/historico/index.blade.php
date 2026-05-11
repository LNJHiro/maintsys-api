@extends('layouts.app')

@section('title', 'Histórico de Manutenções')

@section('breadcrumb')
    <span>histórico</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// log de intervenções</small>
        Histórico de Manutenções
    </div>
</div>

{{-- FILTROS --}}
<div class="table-wrap" style="padding:16px;margin-bottom:16px">
    <form method="GET" action="{{ route('historico.index') }}">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">MÁQUINA</div>
                <select name="maquina_id" class="form-control" style="width:200px">
                    <option value="">Todas</option>
                    @foreach($maquinas as $m)
                    <option value="{{ $m->id }}" {{ request('maquina_id') == $m->id ? 'selected' : '' }}>{{ $m->modelo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">TIPO</div>
                <select name="tipo" class="form-control" style="width:150px">
                    <option value="">Todos</option>
                    <option value="preventiva" {{ request('tipo') == 'preventiva' ? 'selected' : '' }}>Preventiva</option>
                    <option value="corretiva"  {{ request('tipo') == 'corretiva'  ? 'selected' : '' }}>Corretiva</option>
                </select>
            </div>
            <div>
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">TÉCNICO</div>
                <select name="tecnico_id" class="form-control" style="width:180px">
                    <option value="">Todos</option>
                    @foreach($tecnicos as $t)
                    <option value="{{ $t->id }}" {{ request('tecnico_id') == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">DE</div>
                <input type="date" name="data_inicio" class="form-control" style="width:150px" value="{{ request('data_inicio') }}">
            </div>
            <div>
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">ATÉ</div>
                <input type="date" name="data_fim" class="form-control" style="width:150px" value="{{ request('data_fim') }}">
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('historico.index') }}" class="btn btn-secondary">Limpar</a>
        </div>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Máquina</th>
                <th>Tipo</th>
                <th>Técnico</th>
                <th>O.S. Vinculada</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Parada (h)</th>
                <th>Custo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historicos as $h)
            <tr>
                <td class="mono" style="color:var(--muted);font-size:11px">{{ $h->id }}</td>
                <td style="font-weight:500">{{ $h->maquina->modelo ?? '—' }}</td>
                <td>
                    <span class="badge {{ $h->tipo === 'corretiva' ? 'badge-orange' : 'badge-blue' }}">
                        {{ ucfirst($h->tipo) }}
                    </span>
                </td>
                <td style="color:var(--muted)">{{ $h->tecnico->nome ?? '—' }}</td>
                <td class="mono" style="font-size:11px">
                    @if($h->ordem)
                        <a href="{{ route('ordens.show', $h->ordem) }}" style="color:var(--accent)">{{ $h->ordem->numero }}</a>
                    @else
                        <span style="color:var(--muted)">—</span>
                    @endif
                </td>
                <td class="mono" style="font-size:11px;color:var(--muted)">{{ $h->data_inicio->format('d/m/Y H:i') }}</td>
                <td class="mono" style="font-size:11px;color:var(--muted)">
                    {{ $h->data_fim ? $h->data_fim->format('d/m/Y H:i') : '—' }}
                </td>
                <td class="mono" style="font-size:11px;text-align:center">
                    {{ $h->tempo_parada_horas > 0 ? number_format($h->tempo_parada_horas, 1) : '—' }}
                </td>
                <td class="mono" style="font-size:11px;color:{{ $h->custo > 0 ? 'var(--accent)' : 'var(--muted)' }}">
                    {{ $h->custo > 0 ? 'R$ '.number_format($h->custo, 2, ',', '.') : '—' }}
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('historico.show', $h) }}" class="btn btn-secondary btn-sm">Ver</a>
                        <form method="POST" action="{{ route('historico.destroy', $h) }}"
                              onsubmit="confirmDelete(this, 'Excluir este registro de histórico?'); return false;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhum registro no histórico —
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $historicos->links() }}</div>
</div>

@endsection