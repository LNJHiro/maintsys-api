@extends('layouts.app')

@section('title', $tecnico->nome)
@section('breadcrumb', '<a href="'.route('tecnicos.index').'" style="color:var(--muted);text-decoration:none">técnicos</a> <span class="sep">/</span> <span>'.e($tecnico->nome).'</span>')

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// técnico — {{ $tecnico->matricula }}</small>
        {{ $tecnico->nome }}
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('tecnicos.edit', $tecnico) }}" class="btn btn-primary">Editar</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px">

    <div class="table-wrap" style="padding:20px;height:fit-content">
        <span class="badge {{ $tecnico->ativo ? 'badge-green' : 'badge-gray' }}" style="margin-bottom:16px;display:inline-block">
            {{ $tecnico->ativo ? 'Ativo' : 'Inativo' }}
        </span>
        @foreach([
            ['Matrícula',     $tecnico->matricula],
            ['E-mail',        $tecnico->email],
            ['Especialidade', $tecnico->especialidade ?? '—'],
            ['Telefone',      $tecnico->telefone ?? '—'],
            ['Total O.S.',    $tecnico->ordens->count()],
            ['Cadastro',      $tecnico->created_at->format('d/m/Y')],
        ] as [$label, $value])
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
            <span style="font-family:var(--cond);font-size:13px;font-weight:500;max-width:160px;text-align:right">{{ $value }}</span>
        </div>
        @endforeach
    </div>

    <div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">
            // ORDENS DE SERVIÇO ATRIBUÍDAS
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Máquina</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Abertura</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tecnico->ordens->sortByDesc('created_at') as $os)
                    <tr>
                        <td class="mono" style="font-size:11px">
                            <a href="{{ route('ordens.show', $os) }}" style="color:var(--accent)">{{ $os->numero }}</a>
                        </td>
                        <td>{{ $os->maquina->modelo ?? '—' }}</td>
                        <td><span class="badge {{ $os->tipo==='corretiva'?'badge-orange':'badge-blue' }}">{{ $os->tipo_label }}</span></td>
                        <td>
                            @php $sc = match($os->status){'aberta'=>'blue','em_andamento'=>'yellow','concluida'=>'green',default=>'gray'}; @endphp
                            <span class="badge badge-{{ $sc }}">{{ $os->status_label }}</span>
                        </td>
                        <td class="mono" style="font-size:11px;color:var(--muted)">{{ $os->data_abertura->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="color:var(--muted);font-family:var(--mono);font-size:11px;padding:20px">— sem ordens atribuídas —</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection