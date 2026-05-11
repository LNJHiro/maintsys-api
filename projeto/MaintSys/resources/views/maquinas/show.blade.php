@extends('layouts.app')

@section('title', $maquina->modelo)
@section('breadcrumb', '<a href="'.route('maquinas.index').'" style="color:var(--muted);text-decoration:none">máquinas</a> <span class="sep">/</span> <span>'.e($maquina->modelo).'</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// equipamento — {{ $maquina->numero_serie }}</small>
        {{ $maquina->modelo }}
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('historico.por-maquina', $maquina) }}" class="btn btn-secondary">◎ Histórico</a>
        <a href="{{ route('ordens.create') }}?maquina_id={{ $maquina->id }}" class="btn btn-secondary">+ O.S.</a>
        <a href="{{ route('maquinas.edit', $maquina) }}" class="btn btn-primary">Editar</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:20px">

    {{-- INFO CARD --}}
    <div>
        <div class="table-wrap" style="padding:20px">
            @php $sc = match($maquina->status){
                'operacional'=>'green','em_manutencao'=>'yellow',
                'parada_critica'=>'red',default=>'gray'
            }; @endphp
            <div style="margin-bottom:20px">
                <span class="badge badge-{{ $sc }}" style="font-size:12px;padding:4px 12px">
                    {{ $maquina->status_label }}
                </span>
            </div>

            @foreach([
                ['Nº Série',     $maquina->numero_serie],
                ['Modelo',       $maquina->modelo],
                ['Fabricante',   $maquina->fabricante ?? '—'],
                ['Localização',  $maquina->localizacao],
                ['Instalação',   $maquina->data_instalacao?->format('d/m/Y') ?? '—'],
                ['Total O.S.',   $maquina->ordens->count()],
            ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
                <span style="font-family:var(--cond);font-size:14px;font-weight:500">{{ $value }}</span>
            </div>
            @endforeach

            @if($maquina->descricao)
            <div style="margin-top:16px">
                <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px;margin-bottom:6px">DESCRIÇÃO</div>
                <p style="font-size:13px;color:var(--muted);line-height:1.5">{{ $maquina->descricao }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ORDENS DE SERVIÇO --}}
    <div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">
            // ORDENS DE SERVIÇO
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Prioridade</th>
                        <th>Técnico</th>
                        <th>Status</th>
                        <th>Abertura</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maquina->ordens->sortByDesc('created_at') as $os)
                    <tr>
                        <td class="mono" style="font-size:11px;color:var(--accent)">
                            <a href="{{ route('ordens.show', $os) }}" style="color:var(--accent)">{{ $os->numero }}</a>
                        </td>
                        <td><span class="badge {{ $os->tipo==='corretiva'?'badge-orange':'badge-blue' }}">{{ $os->tipo_label }}</span></td>
                        <td>
                            @php $pc = match($os->prioridade){'critica'=>'red','alta'=>'orange','media'=>'yellow',default=>'gray'}; @endphp
                            <span class="badge badge-{{ $pc }}">{{ $os->prioridade_label }}</span>
                        </td>
                        <td style="color:var(--muted)">{{ $os->tecnico->nome ?? '—' }}</td>
                        <td>
                            @php $sc2 = match($os->status){'aberta'=>'blue','em_andamento'=>'yellow','concluida'=>'green',default=>'gray'}; @endphp
                            <span class="badge badge-{{ $sc2 }}">{{ $os->status_label }}</span>
                        </td>
                        <td class="mono" style="font-size:11px;color:var(--muted)">{{ $os->data_abertura->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="color:var(--muted);font-family:var(--mono);font-size:11px;padding:20px">— sem ordens de serviço —</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection