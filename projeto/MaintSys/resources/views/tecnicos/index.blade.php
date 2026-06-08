{{--
    VIEW: tecnicos/index.blade.php
    ROTA:    GET /tecnicos  →  TecnicoController::index()
    DADOS:   $tecnicos (paginado, com ordens_count)
    SEÇÕES:
      Linha 16 — Cabeçalho da página + botão "Novo Técnico"
      Linha 21 — Tabela de listagem de técnicos
      Linha 36 — Loop @forelse: cada linha da tabela
      Linha 44 — Coluna telefone com formatação automática
      Linha 59 — Coluna de status (badge verde/cinza)
      Linha 65 — Coluna de ações: Ver, Editar, Deletar
      Linha 88 — Paginação
--}}
@extends('layouts.app')

@section('title', 'Técnicos')

@section('breadcrumb')
    <span>técnicos</span>
@endsection

@section('content')

{{-- CABEÇALHO: título da página + botão "Novo Técnico" (visível só com permissão tecnicos.criar) --}}
<div class="page-header">
    <div class="page-title">
        <small>// equipe de manutenção</small>
        Técnicos
    </div>
    @if(auth()->user()->hasPermission('tecnicos.criar'))
    <a href="{{ route('tecnicos.create') }}" class="btn btn-primary">+ Novo Técnico</a>
    @endif
</div>

{{-- TABELA: lista todos os técnicos cadastrados --}}
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

                {{-- Formata telefone: (99) 99999-9999 ou (99) 9999-9999 --}}
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

                {{-- ordens_count vem de withCount('ordens') no controller --}}
                <td class="mono" style="text-align:center">{{ $t->ordens_count }}</td>

                {{-- Badge verde = ativo, cinza = inativo --}}
                <td>
                    <span class="badge {{ $t->ativo ? 'badge-green' : 'badge-gray' }}">
                        {{ $t->ativo ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>

                {{-- Ações: Ver (sempre), Editar (permissão), Deletar (permissão) --}}
                <td>
                    <div class="actions">
                        <a href="{{ route('tecnicos.show', $t) }}" class="btn btn-secondary btn-sm">Ver</a>
                        @if(auth()->user()->hasPermission('tecnicos.editar'))
                        <a href="{{ route('tecnicos.edit', $t) }}" class="btn btn-secondary btn-sm">Editar</a>
                        @endif
                        @if(auth()->user()->hasPermission('tecnicos.deletar'))
                        <form method="POST" action="{{ route('tecnicos.destroy', $t) }}"
                              onsubmit="confirmDelete(this, 'Excluir o técnico {{ $t->nome }}?'); return false;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                        @endif
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