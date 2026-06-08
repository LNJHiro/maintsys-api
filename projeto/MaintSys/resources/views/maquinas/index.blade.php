{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página como "Máquinas" --}}
@section('title', 'Máquinas')

{{-- Preenche o breadcrumb com o texto "máquinas" --}}
@section('breadcrumb')
    <span>máquinas</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal --}}
@section('content')

{{-- Cabeçalho da página com título e botão de criação --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo descritivo --}}
    <div class="page-title">
        {{-- Subtítulo indicando o contexto de inventário --}}
        <small>// inventário de equipamentos</small>
        {{-- Título principal da página --}}
        Máquinas
    </div>
    {{-- Botão de nova máquina visível somente com permissão maquinas.criar --}}
    @if(auth()->user()->hasPermission('maquinas.criar'))
    {{-- Link para o formulário de cadastro de nova máquina --}}
    <a href="{{ route('maquinas.create') }}" class="btn btn-primary">+ Nova Máquina</a>
    @endif {{-- fim do bloco de permissão maquinas.criar --}}
</div> {{-- fim do page-header --}}

{{-- Cards de estatísticas rápidas de status das máquinas --}}
<div class="stats-grid" style="grid-template-columns: repeat(4,1fr)">
    {{-- Card: total geral de máquinas cadastradas no sistema --}}
    <div class="stat-card"><div class="stat-label">Total</div><div class="stat-value">{{ $stats['total'] }}</div></div>
    {{-- Card verde: máquinas com status "operacional" --}}
    <div class="stat-card green"><div class="stat-label">Operacionais</div><div class="stat-value" style="color:var(--green)">{{ $stats['operacional'] }}</div></div>
    {{-- Card amarelo: máquinas em processo de manutenção --}}
    <div class="stat-card yellow"><div class="stat-label">Em Manutenção</div><div class="stat-value" style="color:var(--yellow)">{{ $stats['em_manutencao'] }}</div></div>
    {{-- Card vermelho: máquinas em parada crítica (urgente) --}}
    <div class="stat-card red"><div class="stat-label">Parada Crítica</div><div class="stat-value" style="color:var(--red)">{{ $stats['parada_critica'] }}</div></div>
</div> {{-- fim da stats-grid --}}

{{-- Container da tabela de listagem de máquinas --}}
<div class="table-wrap">
    <table>
        {{-- Cabeçalho da tabela com nomes das colunas --}}
        <thead>
            <tr>
                <th>#</th><th>Nº Série</th><th>Modelo</th><th>Fabricante</th>
                <th>Localização</th><th>Cadastro</th><th>Status</th><th>O.S.</th><th>Ações</th>
            </tr>
        </thead>
        <tbody>
            {{-- Itera sobre as máquinas paginadas; exibe mensagem se a lista estiver vazia --}}
            @forelse($maquinas as $m)
            <tr>
                {{-- ID interno da máquina em fonte discreta --}}
                <td class="mono" style="color:var(--muted);font-size:18px">{{ $m->id }}</td>
                {{-- Número de série da máquina com destaque de cor de acento --}}
                <td class="mono" style="font-size:18px;color:var(--accent)">{{ $m->numero_serie }}</td>
                {{-- Modelo da máquina em negrito --}}
                <td style="font-weight:500">{{ $m->modelo }}</td>
                {{-- Fabricante da máquina; exibe "—" se não cadastrado --}}
                <td style="color:var(--muted)">{{ $m->fabricante ?? '—' }}</td>
                {{-- Localização física da máquina no galpão --}}
                <td>{{ $m->localizacao }}</td>
                {{-- Data de cadastro formatada como DD/MM/AAAA; exibe "—" se não preenchida --}}
                <td class="mono" style="font-size:18px;color:var(--muted)">
                    {{ $m->data_cadastro ? $m->data_cadastro->format('d/m/Y') : '—' }}
                </td>
                <td>
                    {{-- Define a cor do badge de status com base no valor do campo status --}}
                    @php $sc = match($m->status){
                        'operacional'    => 'green',  // verde = funcionando normalmente
                        'em_manutencao'  => 'yellow', // amarelo = em reparo
                        'parada_critica' => 'red',    // vermelho = parada urgente
                        default          => 'gray'    // cinza = inativa ou desconhecida
                    }; @endphp
                    {{-- Badge colorido com o rótulo legível do status --}}
                    <span class="badge badge-{{ $sc }}">{{ $m->status_label }}</span>
                </td>
                {{-- Contagem de ordens de serviço vinculadas à máquina (via withCount no controller) --}}
                <td class="mono" style="text-align:center;font-size:18px">{{ $m->ordens_count }}</td>
                <td>
                    {{-- Container de botões de ação da linha --}}
                    <div class="actions">
                        {{-- Botão sempre visível para ver os detalhes da máquina --}}
                        <a href="{{ route('maquinas.show', $m) }}" class="btn btn-secondary btn-sm">Ver</a>
                        {{-- Botão de edição visível somente com permissão maquinas.editar --}}
                        @if(auth()->user()->hasPermission('maquinas.editar'))
                        {{-- Link para o formulário de edição da máquina --}}
                        <a href="{{ route('maquinas.edit', $m) }}" class="btn btn-secondary btn-sm">Editar</a>
                        @endif {{-- fim do bloco de permissão maquinas.editar --}}
                        {{-- Formulário de exclusão visível somente com permissão maquinas.deletar --}}
                        @if(auth()->user()->hasPermission('maquinas.deletar'))
                        {{-- Formulário POST com spoofing de método DELETE para excluir a máquina --}}
                        <form method="POST" action="{{ route('maquinas.destroy', $m) }}"
                              onsubmit="confirmDelete(this, 'Excluir a máquina {{ $m->modelo }}?'); return false;">
                            {{-- Token CSRF para segurança + spoofing do método DELETE --}}
                            @csrf @method('DELETE')
                            {{-- Botão de exclusão com estilo de perigo --}}
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                        @endif {{-- fim do bloco de permissão maquinas.deletar --}}
                    </div>
                </td>
            </tr>
            {{-- Fallback: exibido quando não há máquinas cadastradas --}}
            @empty
            <tr>
                {{-- Célula única cobrindo todas as colunas com mensagem de lista vazia --}}
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);font-size:18px;padding:32px">
                    — nenhuma máquina cadastrada —
                </td>
            </tr>
            @endforelse {{-- fim do loop de máquinas --}}
        </tbody>
    </table>
    {{-- Renderiza os links de paginação gerados pelo Laravel --}}
    <div class="pagination">{{ $maquinas->links() }}</div>
</div> {{-- fim do table-wrap --}}

@endsection {{-- fim da seção content --}}
