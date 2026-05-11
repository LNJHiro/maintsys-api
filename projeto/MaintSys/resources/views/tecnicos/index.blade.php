@extends('layouts.app')

@section('title', 'Técnicos')

@section('breadcrumb')
    <span>técnicos</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// equipe de manutenção</small>
        Técnicos
    </div>
    <a href="{{ route('tecnicos.create') }}" class="btn btn-primary">+ Novo Técnico</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Matrícula</th>
                <th>Nome</th>
                <th>Especialidade</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>O.S.</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tecnicos as $t)
            <tr>
                <td class="mono" style="color:var(--muted);font-size:11px">{{ $t->id }}</td>
                <td class="mono" style="color:var(--accent);font-size:11px">{{ $t->matricula }}</td>
                <td style="font-weight:500">{{ $t->nome }}</td>
                <td style="color:var(--muted)">{{ $t->especialidade ?? '—' }}</td>
                <td style="color:var(--muted);font-size:12px">{{ $t->email }}</td>
                <td class="mono" style="font-size:11px;color:var(--muted)">
                    @if($t->telefone)
                        @php
                            $tel = preg_replace('/\D/', '', $t->telefone);
                            $formatted = strlen($tel) === 11
                                ? '('.substr($tel,0,2).') '.substr($tel,2,5).'-'.substr($tel,7)
                                : (strlen($tel) === 10
                                    ? '('.substr($tel,0,2).') '.substr($tel,2,4).'-'.substr($tel,6)
                                    : $t->telefone);
                        @endphp
                        {{ $formatted }}
                    @else
                        —
                    @endif
                </td>
                <td class="mono" style="text-align:center">{{ $t->ordens_count }}</td>
                <td>
                    <span class="badge {{ $t->ativo ? 'badge-green' : 'badge-gray' }}">
                        {{ $t->ativo ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('tecnicos.show', $t) }}" class="btn btn-secondary btn-sm">Ver</a>
                        <a href="{{ route('tecnicos.edit', $t) }}" class="btn btn-secondary btn-sm">Editar</a>
                        <form method="POST" action="{{ route('tecnicos.destroy', $t) }}"
                              onsubmit="confirmDelete(this, 'Excluir o técnico {{ $t->nome }}?'); return false;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhum técnico cadastrado —
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $tecnicos->links() }}</div>
</div>

@endsection