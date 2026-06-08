{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador --}}
@section('title', 'Ordens de Serviço')

{{-- Define o texto do breadcrumb exibido no topo da página --}}
@section('breadcrumb')
    {{-- Exibe o texto fixo "ordens de serviço" no breadcrumb --}}
    <span>ordens de serviço</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com título e botões de ação --}}
<div class="page-header">
    {{-- Bloco com o título e subtítulo da página --}}
    <div class="page-title">
        {{-- Subtítulo decorativo em estilo monospace --}}
        <small>// gestão de O.S.</small>
        {{-- Título principal da página --}}
        Ordens de Serviço
    </div>
    {{-- GRUPO DE BOTÕES: IMPRIMIR / EXPORTAR / CRIAR
         - Botão Imprimir: abre caixa de diálogo de impressão (window.print)
         - Botão Exportar CSV: faz download do arquivo
         - Botão Nova O.S.: cria nova ordem (visível só com permissão)
    --}}
    {{-- Container flexível que agrupa os botões de ação no cabeçalho --}}
    <div class="btn-export-group" style="display:flex;gap:8px;align-items:center">
        {{-- Botão que aciona a impressão nativa do navegador via JavaScript --}}
        <button onclick="window.print()" class="btn btn-secondary" title="Imprimir lista">
            &#128438; Imprimir
        </button>
        {{-- Link para exportar a listagem atual de ordens no formato CSV --}}
        <a href="{{ route('ordens.exportar') }}" class="btn btn-secondary" title="Exportar para CSV">
            &#8659; Exportar CSV
        </a>
        {{-- Verifica se o usuário logado tem permissão para criar ordens --}}
        @if(auth()->user()->hasPermission('ordens.criar'))
        {{-- Botão exibido apenas para quem tem permissão de criar ordens --}}
        <a href="{{ route('ordens.create') }}" class="btn btn-primary">+ Nova O.S.</a>
        @endif {{-- fim da verificação de permissão para criar --}}
    </div>
</div>

{{-- Grade de estatísticas com 4 colunas mostrando totais por status/prioridade --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    {{-- Card azul exibindo total de ordens com status "aberta" --}}
    <div class="stat-card blue">
        <div class="stat-label">Abertas</div>
        {{-- Exibe o valor numérico do total de ordens abertas --}}
        <div class="stat-value" style="color:var(--blue)">{{ $stats['abertas'] }}</div>
    </div>
    {{-- Card amarelo exibindo total de ordens em andamento --}}
    <div class="stat-card yellow">
        <div class="stat-label">Em Andamento</div>
        {{-- Exibe o valor numérico do total de ordens em andamento --}}
        <div class="stat-value" style="color:var(--yellow)">{{ $stats['em_andamento'] }}</div>
    </div>
    {{-- Card verde exibindo total de ordens concluídas --}}
    <div class="stat-card green">
        <div class="stat-label">Concluídas</div>
        {{-- Exibe o valor numérico do total de ordens concluídas --}}
        <div class="stat-value" style="color:var(--green)">{{ $stats['concluidas'] }}</div>
    </div>
    {{-- Card vermelho exibindo total de ordens com prioridade crítica ativas --}}
    <div class="stat-card red">
        <div class="stat-label">Críticas Ativas</div>
        {{-- Exibe o valor numérico do total de ordens críticas --}}
        <div class="stat-value" style="color:var(--red)">{{ $stats['criticas'] }}</div>
    </div>
</div>

{{-- Container que envolve a tabela de ordens de serviço --}}
<div class="table-wrap">
    {{-- Tabela principal listando todas as ordens de serviço --}}
    <table>
        {{-- Cabeçalho da tabela com os nomes das colunas --}}
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
        {{-- Corpo da tabela com as linhas de cada ordem de serviço --}}
        <tbody>
            {{-- Itera sobre a coleção de ordens; exibe mensagem alternativa se vazia --}}
            @forelse($ordens as $os)
            {{-- Linha da tabela para cada ordem de serviço --}}
            <tr>
                {{-- Exibe o número identificador da OS em fonte monoespaçada e cor de destaque --}}
                <td class="mono" style="font-size:11px;color:var(--accent)">{{ $os->numero }}</td>
                {{-- Coluna de tipo com badge colorido diferenciando corretiva de preventiva --}}
                <td>
                    {{-- Badge laranja para corretiva, azul para preventiva --}}
                    <span class="badge {{ $os->tipo === 'corretiva' ? 'badge-orange' : 'badge-blue' }}">
                        {{-- Exibe o rótulo legível do tipo (ex.: "Corretiva" / "Preventiva") --}}
                        {{ $os->tipo_label }}
                    </span>
                </td>
                {{-- Exibe o modelo da máquina vinculada; exibe "—" se não houver --}}
                <td style="font-weight:500">{{ $os->maquina->modelo ?? '—' }}</td>
                {{-- Exibe o nome do técnico responsável; exibe "—" se não houver --}}
                <td style="color:var(--muted)">{{ $os->tecnico->nome ?? '—' }}</td>
                {{-- Coluna de prioridade com cor dinâmica do badge --}}
                <td>
                    {{-- Define a variável $pc com a cor correspondente à prioridade da OS --}}
                    @php $pc = match($os->prioridade){
                        'critica' => 'red',
                        'alta'    => 'orange',
                        'media'   => 'yellow',
                        default   => 'gray'
                    }; @endphp
                    {{-- Exibe badge com a cor e o rótulo da prioridade --}}
                    <span class="badge badge-{{ $pc }}">{{ $os->prioridade_label }}</span>
                </td>
                {{-- Coluna de status com cor dinâmica do badge --}}
                <td>
                    {{-- Define a variável $sc com a cor correspondente ao status da OS --}}
                    @php $sc = match($os->status){
                        'aberta'       => 'blue',
                        'em_andamento' => 'yellow',
                        'concluida'    => 'green',
                        default        => 'gray'
                    }; @endphp
                    {{-- Exibe badge com a cor e o rótulo do status --}}
                    <span class="badge badge-{{ $sc }}">{{ $os->status_label }}</span>
                </td>
                {{-- Exibe a data de abertura da OS no formato dia/mês/ano --}}
                <td class="mono" style="font-size:11px;color:var(--muted)">{{ $os->data_abertura->format('d/m/Y') }}</td>
                {{-- Exibe a data prevista de conclusão ou "—" se não informada --}}
                <td class="mono" style="font-size:11px;color:var(--muted)">
                    {{ $os->data_prevista ? $os->data_prevista->format('d/m/Y') : '—' }}
                </td>
                {{-- Coluna de ações com botões de visualizar, editar e excluir --}}
                <td>
                    {{-- Container flexível para alinhar os botões de ação --}}
                    <div class="actions">
                        {{-- Botão que navega para a página de detalhes da OS --}}
                        <a href="{{ route('ordens.show', $os) }}" class="btn btn-secondary btn-sm">Ver</a>
                        {{-- Verifica se o usuário tem permissão para editar ordens --}}
                        @if(auth()->user()->hasPermission('ordens.editar'))
                        {{-- Botão de edição visível apenas para quem tem permissão --}}
                        <a href="{{ route('ordens.edit', $os) }}" class="btn btn-secondary btn-sm">Editar</a>
                        @endif {{-- fim da verificação de permissão para editar --}}
                        {{-- Verifica se o usuário tem permissão para deletar ordens --}}
                        @if(auth()->user()->hasPermission('ordens.deletar'))
                        {{-- Formulário de exclusão que exige confirmação antes de enviar --}}
                        <form method="POST" action="{{ route('ordens.destroy', $os) }}"
                              onsubmit="confirmDelete(this, 'Excluir O.S. {{ $os->numero }}?'); return false;">
                            {{-- Token CSRF para segurança e método DELETE para deleção --}}
                            @csrf @method('DELETE')
                            {{-- Botão vermelho de exclusão --}}
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                        @endif {{-- fim da verificação de permissão para deletar --}}
                    </div>
                </td>
            </tr>
            {{-- Bloco exibido quando não há nenhuma ordem de serviço --}}
            @empty
            <tr>
                {{-- Célula que ocupa todas as 9 colunas exibindo mensagem vazia --}}
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhuma ordem de serviço —
                </td>
            </tr>
            @endforelse {{-- fim da iteração sobre as ordens --}}
        </tbody>
    </table>
    {{-- Renderiza os links de paginação da coleção de ordens --}}
    <div class="pagination">{{ $ordens->links() }}</div>
</div>

@endsection {{-- fim da seção de conteúdo principal --}}
