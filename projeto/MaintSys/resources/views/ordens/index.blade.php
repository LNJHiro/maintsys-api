@extends('layouts.app')

@section('title', 'Ordens de Serviço')

@section('breadcrumb')
    <span>ordens de serviço</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// gestão de O.S.</small>
        Ordens de Serviço
    </div>
    <a href="{{ route('ordens.create') }}" class="btn btn-primary">+ Nova O.S.</a>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card blue">
        <div class="stat-label">Abertas</div>
        <div class="stat-value" style="color:var(--blue)">{{ $stats['abertas'] }}</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Em Andamento</div>
        <div class="stat-value" style="color:var(--yellow)">{{ $stats['em_andamento'] }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Concluídas</div>
        <div class="stat-value" style="color:var(--green)">{{ $stats['concluidas'] }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Críticas Ativas</div>
        <div class="stat-value" style="color:var(--red)">{{ $stats['criticas'] }}</div>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Tipo</th>
                <th>Máquina</th>
                <th>Técnico</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Abertura</th>
                <th>Prevista</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordens as $os)
            <tr>
                <td class="mono" style="font-size:11px;color:var(--accent)">{{ $os->numero }}</td>
                <td>
                    <span class="badge {{ $os->tipo === 'corretiva' ? 'badge-orange' : 'badge-blue' }}">
                        {{ $os->tipo_label }}
                    </span>
                </td>
                <td style="font-weight:500">{{ $os->maquina->modelo ?? '—' }}</td>
                <td style="color:var(--muted)">{{ $os->tecnico->nome ?? '—' }}</td>
                <td>
                    @php $pc = match($os->prioridade){
                        'critica' => 'red',
                        'alta'    => 'orange',
                        'media'   => 'yellow',
                        default   => 'gray'
                    }; @endphp
                    <span class="badge badge-{{ $pc }}">{{ $os->prioridade_label }}</span>
                </td>
                <td>
                    @php $sc = match($os->status){
                        'aberta'       => 'blue',
                        'em_andamento' => 'yellow',
                        'concluida'    => 'green',
                        default        => 'gray'
                    }; @endphp
                    <span class="badge badge-{{ $sc }}">{{ $os->status_label }}</span>
                </td>
                <td class="mono" style="font-size:11px;color:var(--muted)">{{ $os->data_abertura->format('d/m/Y') }}</td>
                <td class="mono" style="font-size:11px;color:var(--muted)">
                    {{ $os->data_prevista ? $os->data_prevista->format('d/m/Y') : '—' }}
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('ordens.show', $os) }}" class="btn btn-secondary btn-sm">Ver</a>
                        <a href="{{ route('ordens.edit', $os) }}" class="btn btn-secondary btn-sm">Editar</a>
                        <form method="POST" action="{{ route('ordens.destroy', $os) }}"
                              onsubmit="confirmDelete(this, 'Excluir O.S. {{ $os->numero }}?'); return false;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhuma ordem de serviço —
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $ordens->links() }}</div>
</div>

@endsection