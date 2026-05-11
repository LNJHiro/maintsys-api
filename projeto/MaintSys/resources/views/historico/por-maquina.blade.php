@extends('layouts.app')

@section('title', 'Histórico — '.$maquina->modelo)
@section('breadcrumb', '<a href="'.route('historico.index').'" style="color:var(--muted);text-decoration:none">histórico</a> <span class="sep">/</span> <span>'.e($maquina->modelo).'</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// histórico por equipamento</small>
        {{ $maquina->modelo }}
        <span style="font-family:var(--mono);font-size:12px;color:var(--muted);font-weight:400;margin-left:12px">
            {{ $maquina->numero_serie }}
        </span>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('maquinas.show', $maquina) }}" class="btn btn-secondary">Ver Máquina</a>
        <a href="{{ route('ordens.create') }}?maquina_id={{ $maquina->id }}" class="btn btn-primary">+ Nova O.S.</a>
    </div>
</div>

{{-- REINCIDÊNCIA --}}
@if($reincidencias->count() > 0)
<div class="table-wrap" style="padding:16px;margin-bottom:20px">
    <div style="font-family:var(--mono);font-size:10px;color:var(--yellow);letter-spacing:2px;margin-bottom:12px">
        ⚠ // ANÁLISE DE REINCIDÊNCIA — MANUTENÇÕES CORRETIVAS POR MÊS
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        @foreach($reincidencias as $r)
        <div style="background:var(--surface);border:1px solid var(--border);padding:10px 16px;text-align:center">
            <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1px">
                {{ str_pad($r->mes,2,'0',STR_PAD_LEFT) }}/{{ $r->ano }}
            </div>
            <div style="font-family:var(--cond);font-size:28px;font-weight:700;color:{{ $r->total >= 3 ? 'var(--red)' : ($r->total >= 2 ? 'var(--yellow)' : 'var(--text)') }}">
                {{ $r->total }}
            </div>
            <div style="font-family:var(--mono);font-size:9px;color:var(--muted)">corretivas</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- HISTÓRICO --}}
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tipo</th>
                <th>Técnico</th>
                <th>O.S.</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Parada (h)</th>
                <th>Custo</th>
                <th>Ver</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historicos as $h)
            <tr>
                <td class="mono" style="color:var(--muted);font-size:11px">{{ $h->id }}</td>
                <td><span class="badge {{ $h->tipo==='corretiva'?'badge-orange':'badge-blue' }}">{{ ucfirst($h->tipo) }}</span></td>
                <td style="color:var(--muted)">{{ $h->tecnico->nome ?? '—' }}</td>
                <td class="mono" style="font-size:11px">
                    @if($h->ordem)
                        <a href="{{ route('ordens.show', $h->ordem) }}" style="color:var(--accent)">{{ $h->ordem->numero }}</a>
                    @else —
                    @endif
                </td>
                <td class="mono" style="font-size:11px;color:var(--muted)">{{ $h->data_inicio->format('d/m/Y H:i') }}</td>
                <td class="mono" style="font-size:11px;color:var(--muted)">{{ $h->data_fim ? $h->data_fim->format('d/m/Y H:i') : '—' }}</td>
                <td class="mono" style="font-size:11px;text-align:center">{{ $h->tempo_parada_horas > 0 ? number_format($h->tempo_parada_horas,1) : '—' }}</td>
                <td class="mono" style="font-size:11px;color:{{ $h->custo > 0 ? 'var(--accent)' : 'var(--muted)' }}">
                    {{ $h->custo > 0 ? 'R$ '.number_format($h->custo,2,',','.') : '—' }}
                </td>
                <td><a href="{{ route('historico.show', $h) }}" class="btn btn-secondary btn-sm">Ver</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhum histórico para esta máquina —
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $historicos->links() }}</div>
</div>

@endsection