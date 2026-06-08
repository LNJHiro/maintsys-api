{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página com o modelo da máquina --}}
@section('title', $maquina->modelo)
{{-- Preenche o breadcrumb com link para o índice e o modelo da máquina --}}
@section('breadcrumb')
    {{-- Link clicável para voltar à listagem de máquinas --}}
    <a href="{{ route('maquinas.index') }}" style="color:var(--muted);text-decoration:none">máquinas</a>
    {{-- Separador visual entre os níveis do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Nó atual mostrando o modelo da máquina visualizada --}}
    <span>{{ $maquina->modelo }}</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal --}}
@section('content')

{{-- Cabeçalho da página com número de série, modelo e botões de ação --}}
<div class="page-header">
    {{-- Bloco do título com número de série como subtítulo e modelo como título --}}
    <div class="page-title">
        {{-- Subtítulo com o número de série do equipamento --}}
        <small>// equipamento — {{ $maquina->numero_serie }}</small>
        {{-- Modelo da máquina como título principal --}}
        {{ $maquina->modelo }}
    </div>
    {{-- Área de botões de ação em linha --}}
    <div style="display:flex;gap:8px">
        {{-- Botão de histórico visível somente com permissão historico.visualizar --}}
        @if(auth()->user()->hasPermission('historico.visualizar'))
        {{-- Link para o histórico de manutenções desta máquina específica --}}
        <a href="{{ route('historico.por-maquina', $maquina) }}" class="btn btn-secondary">◎ Histórico</a>
        @endif {{-- fim do bloco de permissão historico.visualizar --}}
        {{-- Botão de nova OS visível somente com permissão ordens.criar --}}
        @if(auth()->user()->hasPermission('ordens.criar'))
        {{-- Link para criar nova OS pré-vinculada a esta máquina via query string --}}
        <a href="{{ route('ordens.create') }}?maquina_id={{ $maquina->id }}" class="btn btn-secondary">+ O.S.</a>
        @endif {{-- fim do bloco de permissão ordens.criar --}}
        {{-- Botão de edição visível somente com permissão maquinas.editar --}}
        @if(auth()->user()->hasPermission('maquinas.editar'))
        {{-- Link para o formulário de edição da máquina --}}
        <a href="{{ route('maquinas.edit', $maquina) }}" class="btn btn-primary">Editar</a>
        @endif {{-- fim do bloco de permissão maquinas.editar --}}
    </div>
</div> {{-- fim do page-header --}}

{{-- Layout em duas colunas: ficha da máquina (esquerda) e tabela de OS (direita) --}}
<div style="display:grid;grid-template-columns:320px 1fr;gap:20px">

    {{-- COLUNA ESQUERDA: ficha com dados detalhados da máquina --}}
    <div>
        {{-- Container do card de informações --}}
        <div class="table-wrap" style="padding:20px">
            {{-- Define a cor do badge de status da máquina --}}
            @php $sc = match($maquina->status){
                'operacional'=>'green',     // verde = funcionando normalmente
                'em_manutencao'=>'yellow',  // amarelo = em reparo
                'parada_critica'=>'red',    // vermelho = parada urgente
                default=>'gray'            // cinza = inativa ou desconhecida
            }; @endphp
            {{-- Exibe o badge de status da máquina em destaque no topo do card --}}
            <div style="margin-bottom:20px">
                {{-- Badge grande com o rótulo legível do status atual --}}
                <span class="badge badge-{{ $sc }}" style="font-size:16px;padding:6px 14px">
                    {{ $maquina->status_label }}
                </span>
            </div>

            {{-- Loop sobre array de pares [rótulo, valor] para renderizar cada dado da máquina --}}
            @foreach([
                ['Nº Série',     $maquina->numero_serie],
                ['Modelo',       $maquina->modelo],
                ['Fabricante',   $maquina->fabricante ?? '—'],
                ['Localização',  $maquina->localizacao],
                ['Cadastro',     $maquina->data_cadastro?->format('d/m/Y') ?? '—'],
                ['Total O.S.',   $maquina->ordens->count()],
            ] as [$label, $value])
            {{-- Linha de dado com rótulo e valor separados por espaço e borda inferior --}}
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
                {{-- Rótulo do dado em fonte monoespaçada e cor discreta --}}
                <span style="font-family:var(--mono);font-size:14px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
                {{-- Valor do dado em fonte condensada e negrito --}}
                <span style="font-family:var(--cond);font-size:18px;font-weight:500">{{ $value }}</span>
            </div>
            @endforeach {{-- fim do loop de dados da máquina --}}

            {{-- Bloco de descrição: exibido somente se a máquina tiver descrição cadastrada --}}
            @if($maquina->descricao)
            <div style="margin-top:16px">
                {{-- Título do bloco de descrição --}}
                <div style="font-family:var(--mono);font-size:14px;color:var(--muted);letter-spacing:1px;margin-bottom:6px">DESCRIÇÃO</div>
                {{-- Texto da descrição/observações sobre o equipamento --}}
                <p style="font-size:18px;color:var(--muted);line-height:1.5">{{ $maquina->descricao }}</p>
            </div>
            @endif {{-- fim do bloco de descrição --}}
        </div>
    </div> {{-- fim da coluna esquerda --}}

    {{-- COLUNA DIREITA: tabela de ordens de serviço da máquina --}}
    <div>
        {{-- Título da seção de ordens de serviço --}}
        <div style="font-family:var(--mono);font-size:16px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">
            // ORDENS DE SERVIÇO
        </div>
        {{-- Container da tabela de OS com scroll horizontal --}}
        <div class="table-wrap">
            <table>
                {{-- Cabeçalho da tabela de OS da máquina --}}
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
                    {{-- Itera sobre as OS da máquina, ordenadas da mais recente para a mais antiga --}}
                    @forelse($maquina->ordens->sortByDesc('created_at') as $os)
                    <tr>
                        {{-- Número da OS como link para o detalhe, com cor de acento --}}
                        <td class="mono" style="font-size:18px;color:var(--accent)">
                            <a href="{{ route('ordens.show', $os) }}" style="color:var(--accent)">{{ $os->numero }}</a>
                        </td>
                        {{-- Badge de tipo: laranja para corretiva, azul para preventiva --}}
                        <td><span class="badge {{ $os->tipo==='corretiva'?'badge-orange':'badge-blue' }}">{{ $os->tipo_label }}</span></td>
                        <td>
                            {{-- Define a cor do badge de prioridade da OS --}}
                            @php $pc = match($os->prioridade){'critica'=>'red','alta'=>'orange','media'=>'yellow',default=>'gray'}; @endphp
                            {{-- Badge colorido com o rótulo legível da prioridade --}}
                            <span class="badge badge-{{ $pc }}">{{ $os->prioridade_label }}</span>
                        </td>
                        {{-- Nome do técnico responsável pela OS; exibe "—" se não atribuído --}}
                        <td style="color:var(--muted)">{{ $os->tecnico->nome ?? '—' }}</td>
                        <td>
                            {{-- Define a cor do badge de status da OS --}}
                            @php $sc2 = match($os->status){'aberta'=>'blue','em_andamento'=>'yellow','concluida'=>'green',default=>'gray'}; @endphp
                            {{-- Badge colorido com o rótulo legível do status da OS --}}
                            <span class="badge badge-{{ $sc2 }}">{{ $os->status_label }}</span>
                        </td>
                        {{-- Data de abertura da OS formatada como DD/MM/AAAA --}}
                        <td class="mono" style="font-size:18px;color:var(--muted)">{{ $os->data_abertura->format('d/m/Y') }}</td>
                    </tr>
                    {{-- Fallback: exibido quando a máquina não possui ordens de serviço --}}
                    @empty
                    <tr><td colspan="6" style="color:var(--muted);font-family:var(--mono);font-size:18px;padding:20px">— sem ordens de serviço —</td></tr>
                    @endforelse {{-- fim do loop de OS da máquina --}}
                </tbody>
            </table>
        </div> {{-- fim do table-wrap das OS --}}
    </div> {{-- fim da coluna direita --}}

</div> {{-- fim do grid de duas colunas --}}

@endsection {{-- fim da seção content --}}
