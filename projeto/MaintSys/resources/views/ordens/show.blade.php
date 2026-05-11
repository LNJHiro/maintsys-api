@extends('layouts.app')

@section('title', $ordem->numero)
@section('breadcrumb', '<a href="'.route('ordens.index').'" style="color:var(--muted);text-decoration:none">ordens</a> <span class="sep">/</span> <span>'.e($ordem->numero).'</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// ordem de serviço</small>
        {{ $ordem->numero }}
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('ordens.edit', $ordem) }}" class="btn btn-primary">Editar O.S.</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px">

    {{-- DETALHES --}}
    <div class="table-wrap" style="padding:20px;height:fit-content">
        @php
            $pc = match($ordem->prioridade){'critica'=>'red','alta'=>'orange','media'=>'yellow',default=>'gray'};
            $sc = match($ordem->status){'aberta'=>'blue','em_andamento'=>'yellow','concluida'=>'green',default=>'gray'};
        @endphp
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
            <span class="badge badge-{{ $sc }}" style="font-size:12px">{{ $ordem->status_label }}</span>
            <span class="badge badge-{{ $pc }}" style="font-size:12px">{{ $ordem->prioridade_label }}</span>
            <span class="badge {{ $ordem->tipo==='corretiva'?'badge-orange':'badge-blue' }}" style="font-size:12px">{{ $ordem->tipo_label }}</span>
        </div>

        @foreach([
            ['Número',    $ordem->numero],
            ['Máquina',   $ordem->maquina->modelo ?? '—'],
            ['Nº Série',  $ordem->maquina->numero_serie ?? '—'],
            ['Local',     $ordem->maquina->localizacao ?? '—'],
            ['Técnico',   $ordem->tecnico->nome ?? '—'],
            ['Abertura',  $ordem->data_abertura->format('d/m/Y H:i')],
            ['Prevista',  $ordem->data_prevista ? $ordem->data_prevista->format('d/m/Y') : '—'],
            ['Conclusão', $ordem->data_conclusao ? $ordem->data_conclusao->format('d/m/Y H:i') : '—'],
        ] as [$label, $value])
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
            <span style="font-family:var(--cond);font-size:13px;font-weight:500;max-width:160px;text-align:right">{{ $value }}</span>
        </div>
        @endforeach
    </div>

    {{-- DESCRIÇÃO E SOLUÇÃO --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="table-wrap" style="padding:20px">
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">
                // DESCRIÇÃO DO PROBLEMA / SERVIÇO
            </div>
            <p style="font-size:14px;line-height:1.7;color:var(--text)">{{ $ordem->descricao }}</p>
        </div>

        @if($ordem->solucao)
        <div class="table-wrap" style="padding:20px;border-color:rgba(63,185,80,.3)">
            <div style="font-family:var(--mono);font-size:10px;color:var(--green);letter-spacing:2px;margin-bottom:10px">
                ✓ // SOLUÇÃO APLICADA
            </div>
            <p style="font-size:14px;line-height:1.7;color:var(--text)">{{ $ordem->solucao }}</p>
        </div>
        @endif

        @if($ordem->historico)
        <div class="table-wrap" style="padding:20px">
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:12px">
                // REGISTRO NO HISTÓRICO
            </div>
            <div style="display:flex;gap:24px;flex-wrap:wrap">
                <div>
                    <div style="font-family:var(--mono);font-size:10px;color:var(--muted)">INÍCIO</div>
                    <div style="font-family:var(--cond);font-size:15px;font-weight:600">
                        {{ $ordem->historico->data_inicio?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
                <div>
                    <div style="font-family:var(--mono);font-size:10px;color:var(--muted)">FIM</div>
                    <div style="font-family:var(--cond);font-size:15px;font-weight:600">
                        {{ $ordem->historico->data_fim?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
                @if($ordem->historico->custo)
                <div>
                    <div style="font-family:var(--mono);font-size:10px;color:var(--muted)">CUSTO</div>
                    <div style="font-family:var(--cond);font-size:15px;font-weight:600;color:var(--accent)">
                        R$ {{ number_format($ordem->historico->custo, 2, ',', '.') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection