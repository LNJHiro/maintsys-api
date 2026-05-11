@extends('layouts.app')

@section('title', 'Máquinas')

@section('breadcrumb')
    <span>máquinas</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// inventário de equipamentos</small>
        Máquinas
    </div>
    <a href="{{ route('maquinas.create') }}" class="btn btn-primary">+ Nova Máquina</a>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(4,1fr)">
    <div class="stat-card"><div class="stat-label">Total</div><div class="stat-value">{{ $stats['total'] }}</div></div>
    <div class="stat-card green"><div class="stat-label">Operacionais</div><div class="stat-value" style="color:var(--green)">{{ $stats['operacional'] }}</div></div>
    <div class="stat-card yellow"><div class="stat-label">Em Manutenção</div><div class="stat-value" style="color:var(--yellow)">{{ $stats['em_manutencao'] }}</div></div>
    <div class="stat-card red"><div class="stat-label">Parada Crítica</div><div class="stat-value" style="color:var(--red)">{{ $stats['parada_critica'] }}</div></div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Nº Série</th><th>Modelo</th><th>Fabricante</th>
                <th>Localização</th><th>Instalação</th><th>Status</th><th>O.S.</th><th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($maquinas as $m)
            <tr>
                <td class="mono" style="color:var(--muted);font-size:11px">{{ $m->id }}</td>
                <td class="mono" style="font-size:11px;color:var(--accent)">{{ $m->numero_serie }}</td>
                <td style="font-weight:500">{{ $m->modelo }}</td>
                <td style="color:var(--muted)">{{ $m->fabricante ?? '—' }}</td>
                <td>{{ $m->localizacao }}</td>
                <td class="mono" style="font-size:11px;color:var(--muted)">
                    {{ $m->data_instalacao ? $m->data_instalacao->format('d/m/Y') : '—' }}
                </td>
                <td>
                    @php $sc = match($m->status){
                        'operacional'    => 'green',
                        'em_manutencao'  => 'yellow',
                        'parada_critica' => 'red',
                        default          => 'gray'
                    }; @endphp
                    <span class="badge badge-{{ $sc }}">{{ $m->status_label }}</span>
                </td>
                <td class="mono" style="text-align:center">{{ $m->ordens_count }}</td>
                <td>
                    <div class="actions">
                        <a href="{{ route('maquinas.show', $m) }}" class="btn btn-secondary btn-sm">Ver</a>
                        <a href="{{ route('maquinas.edit', $m) }}" class="btn btn-secondary btn-sm">Editar</a>
                        <form method="POST" action="{{ route('maquinas.destroy', $m) }}"
                              onsubmit="confirmDelete(this, 'Excluir a máquina {{ $m->modelo }}?'); return false;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhuma máquina cadastrada —
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $maquinas->links() }}</div>
</div>

@endsection