@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <span>dashboard</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// visão geral</small>
        Dashboard
    </div>
    <div style="font-family:var(--mono);font-size:18px;color:var(--muted)">
        {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

{{-- STATS --}}
<div class="stats-grid">
    @if(auth()->user()->hasPermission('dashboard.maquinas'))
    <div class="stat-card">
        <div class="stat-label">Total Máquinas</div>
        <div class="stat-value">{{ $stats['maquinas_total'] }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Operacionais</div>
        <div class="stat-value" style="color:var(--green)">{{ $stats['operacionais'] }}</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Em Manutenção</div>
        <div class="stat-value" style="color:var(--yellow)">{{ $stats['em_manutencao'] }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Parada Crítica</div>
        <div class="stat-value" style="color:var(--red)">{{ $stats['parada_critica'] }}</div>
    </div>
    @endif
    @if(auth()->user()->hasPermission('dashboard.tecnicos'))
    <div class="stat-card blue">
        <div class="stat-label">Técnicos Ativos</div>
        <div class="stat-value" style="color:var(--blue)">{{ $stats['tecnicos_ativos'] }}</div>
    </div>
    @endif
    @if(auth()->user()->hasPermission('dashboard.ordens'))
    <div class="stat-card">
        <div class="stat-label">O.S. Abertas</div>
        <div class="stat-value">{{ $stats['os_abertas'] }}</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Em Andamento</div>
        <div class="stat-value" style="color:var(--yellow)">{{ $stats['os_em_andamento'] }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Concluídas Hoje</div>
        <div class="stat-value" style="color:var(--green)">{{ $stats['os_concluidas_hoje'] }}</div>
    </div>
    @endif
</div>

{{-- ALERTAS DE PARADA CRÍTICA --}}
@if(auth()->user()->hasPermission('dashboard.alertas') && $alertas->count() > 0)
<div style="margin-bottom:24px;border:1px solid rgba(248,81,73,.3);background:rgba(248,81,73,.04);padding:16px;border-radius:4px;">
    <div style="font-family:var(--mono);font-size:18px;color:var(--red);letter-spacing:2px;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        ⚠ // ALERTAS — MÁQUINAS EM PARADA CRÍTICA
        <span style="background:rgba(248,81,73,.15);color:var(--red);padding:4px 10px;font-size:18px;border:1px solid rgba(248,81,73,.3);">
            {{ $alertas->count() }}
        </span>
    </div>

    @foreach($alertas as $m)

        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(248,81,73,.1);gap:12px;">
            <div style="font-family:var(--cond);font-size:18px;font-weight:600;flex:1;">{{ $m->modelo }}</div>
            <span style="font-family:var(--mono);font-size:18px;color:var(--muted);flex:1;">{{ $m->localizacao }}</span>
            <a href="{{ route('ordens.create') }}?maquina_id={{ $m->id }}" class="btn btn-danger btn-sm">
                + Abrir O.S.
            </a>
        </div>
    @endforeach
</div>
@endif

{{-- GRID PRINCIPAL --}}
<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:20px;">

    {{-- ORDENS ATIVAS --}}
    @if(auth()->user()->hasPermission('dashboard.ordens'))
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="font-family:var(--mono);font-size:18px;color:var(--muted);letter-spacing:2px;">
                // ORDENS DE SERVIÇO ATIVAS
            </div>
            <a href="{{ route('ordens.index') }}" style="font-family:var(--mono);font-size:18px;color:var(--accent);text-decoration:none;letter-spacing:1px;">
                ver todas →
            </a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Máquina</th>
                        <th>Técnico</th>
                        <th>Prior.</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($ordensRecentes as $ordem)
                        <tr style="cursor:pointer;" onclick="window.location.href=this.dataset.href" data-href="{{ route('ordens.show', $ordem->id) }}">
                            <td class="mono" style="font-size:18px;color:var(--accent)">
                                {{ $ordem->numero }}
                            </td>

                            <td>{{ $ordem->maquina->modelo ?? '-' }}</td>

                            <td style="color:var(--muted)">
                                {{ $ordem->tecnico->nome ?? '-' }}
                            </td>

                            <td>
                                @php
                                    $pc = match($ordem->prioridade) {
                                        'critica' => 'red',
                                        'alta'    => 'orange',
                                        'media'   => 'yellow',
                                        default   => 'gray',
                                    };
                                @endphp

                                <span class="badge badge-{{ $pc }}">
                                    {{ $ordem->prioridade_label }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $sc = match($ordem->status) {
                                        'aberta'       => 'blue',
                                        'em_andamento' => 'yellow',
                                        default        => 'gray',
                                    };
                                @endphp

                                <span class="badge badge-{{ $sc }}">
                                    {{ $ordem->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="color:var(--muted);font-family:var(--mono);font-size:18px;text-align:center;padding:20px;">
                                — sem ordens ativas —
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endif

    {{-- COLUNA DIREITA --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- ÚLTIMAS MANUTENÇÕES --}}
        @if(auth()->user()->hasPermission('dashboard.historico'))
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div style="font-family:var(--mono);font-size:18px;color:var(--muted);letter-spacing:2px;">
                    // ÚLTIMAS MANUTENÇÕES
                </div>
                <a href="{{ route('historico.index') }}" style="font-family:var(--mono);font-size:18px;color:var(--accent);text-decoration:none;letter-spacing:1px;">
                    ver todas →
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th>Tipo</th>
                            <th>Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($ultimasManutencoes as $h)
                            <tr>
                                <td>{{ $h->maquina->modelo ?? '-' }}</td>

                                <td>
                                    <span class="badge {{ $h->tipo === 'corretiva' ? 'badge-orange' : 'badge-blue' }}">
                                        {{ ucfirst($h->tipo) }}
                                    </span>
                                </td>

                                <td style="color:var(--muted);font-family:var(--mono);font-size:18px;">
                                    {{ optional($h->data_inicio)->format('d/m H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="color:var(--muted);font-family:var(--mono);font-size:18px;text-align:center;padding:20px;">
                                    — sem registros —
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @endif

        {{-- AÇÕES RÁPIDAS --}}
        <div>
            <div style="font-family:var(--mono);font-size:18px;color:var(--muted);letter-spacing:2px;margin-bottom:10px;">
                // AÇÕES RÁPIDAS
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;">
                @if(auth()->user()->hasPermission('ordens.criar'))
                <a href="{{ route('ordens.create') }}" class="btn btn-primary" style="justify-content:center;">
                    + Nova Ordem de Serviço
                </a>
                @endif
                @if(auth()->user()->hasPermission('maquinas.criar'))
                <a href="{{ route('maquinas.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    + Cadastrar Máquina
                </a>
                @endif
                @if(auth()->user()->hasPermission('tecnicos.criar'))
                <a href="{{ route('tecnicos.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    + Cadastrar Técnico
                </a>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection