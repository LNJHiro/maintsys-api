@extends('layouts.app')

@section('title', 'Registro #'.$historico->id)
@section('breadcrumb', '<a href="'.route('historico.index').'" style="color:var(--muted);text-decoration:none">histórico</a> <span class="sep">/</span> <span>#{{ $historico->id }}</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// registro de manutenção</small>
        {{ $historico->maquina->modelo ?? 'Máquina' }} — #{{ $historico->id }}
    </div>
    <a href="{{ route('historico.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px">

    <div class="table-wrap" style="padding:20px;height:fit-content">
        <span class="badge {{ $historico->tipo==='corretiva'?'badge-orange':'badge-blue' }}" style="margin-bottom:16px;display:inline-block">
            {{ ucfirst($historico->tipo) }}
        </span>

        @foreach([
            ['Máquina',      $historico->maquina->modelo ?? '—'],
            ['Nº Série',     $historico->maquina->numero_serie ?? '—'],
            ['Localização',  $historico->maquina->localizacao ?? '—'],
            ['Técnico',      $historico->tecnico->nome ?? '—'],
            ['O.S.',         $historico->ordem->numero ?? '—'],
            ['Início',       $historico->data_inicio->format('d/m/Y H:i')],
            ['Fim',          $historico->data_fim ? $historico->data_fim->format('d/m/Y H:i') : '—'],
            ['Parada (h)',   $historico->tempo_parada_horas > 0 ? number_format($historico->tempo_parada_horas,1).'h' : '—'],
            ['Custo',        $historico->custo > 0 ? 'R$ '.number_format($historico->custo,2,',','.') : '—'],
        ] as [$label, $value])
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
            <span style="font-family:var(--cond);font-size:13px;font-weight:500;max-width:160px;text-align:right">{{ $value }}</span>
        </div>
        @endforeach
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="table-wrap" style="padding:20px">
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">// DESCRIÇÃO</div>
            <p style="font-size:14px;line-height:1.7">{{ $historico->descricao }}</p>
        </div>

        @if($historico->solucao)
        <div class="table-wrap" style="padding:20px;border-color:rgba(63,185,80,.3)">
            <div style="font-family:var(--mono);font-size:10px;color:var(--green);letter-spacing:2px;margin-bottom:10px">✓ // SOLUÇÃO APLICADA</div>
            <p style="font-size:14px;line-height:1.7">{{ $historico->solucao }}</p>
        </div>
        @endif

        @if($historico->pecas_utilizadas)
        <div class="table-wrap" style="padding:20px">
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">// PEÇAS UTILIZADAS</div>
            <p style="font-size:14px;line-height:1.7;font-family:var(--mono);font-size:12px;color:var(--muted)">{{ $historico->pecas_utilizadas }}</p>
        </div>
        @endif

        @if($historico->observacoes)
        <div class="table-wrap" style="padding:20px">
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">// OBSERVAÇÕES</div>
            <p style="font-size:14px;line-height:1.7">{{ $historico->observacoes }}</p>
        </div>
        @endif
    </div>

</div>

@endsection