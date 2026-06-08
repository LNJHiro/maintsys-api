{{-- Herda o layout principal da aplicação (layouts/app.blade.php) --}}
@extends('layouts.app')

{{-- Define o título da aba/página como "Dashboard" --}}
@section('title', 'Dashboard')

{{-- Preenche o slot de breadcrumb com o texto "dashboard" --}}
@section('breadcrumb')
    <span>dashboard</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal da página --}}
@section('content')

{{-- Cabeçalho da página: título e data/hora atual --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo decorativo --}}
    <div class="page-title">
        {{-- Subtítulo em estilo comentário de código --}}
        <small>// visão geral</small>
        {{-- Título principal da seção --}}
        Dashboard
    </div>
    {{-- Exibe a data e hora atual formatadas como DD/MM/AAAA HH:MM --}}
    <div style="font-family:var(--mono);font-size:18px;color:var(--muted)">
        {{ now()->format('d/m/Y H:i') }}
    </div>
</div> {{-- fim do page-header --}}

{{-- STATS: cards de estatísticas do sistema --}}
<div class="stats-grid">
    {{-- Exibe cards de máquinas somente se o usuário tiver permissão dashboard.maquinas --}}
    @if(auth()->user()->hasPermission('dashboard.maquinas'))
    {{-- Card: total de máquinas cadastradas no sistema --}}
    <div class="stat-card">
        <div class="stat-label">Total Máquinas</div>
        {{-- Exibe o valor da estatística de total de máquinas --}}
        <div class="stat-value">{{ $stats['maquinas_total'] }}</div>
    </div>
    {{-- Card verde: máquinas com status "operacional" --}}
    <div class="stat-card green">
        <div class="stat-label">Operacionais</div>
        {{-- Valor em verde, indica máquinas funcionando normalmente --}}
        <div class="stat-value" style="color:var(--green)">{{ $stats['operacionais'] }}</div>
    </div>
    {{-- Card amarelo: máquinas em processo de manutenção --}}
    <div class="stat-card yellow">
        <div class="stat-label">Em Manutenção</div>
        {{-- Valor em amarelo, indica atenção --}}
        <div class="stat-value" style="color:var(--yellow)">{{ $stats['em_manutencao'] }}</div>
    </div>
    {{-- Card vermelho: máquinas em parada crítica (urgente) --}}
    <div class="stat-card red">
        <div class="stat-label">Parada Crítica</div>
        {{-- Valor em vermelho, indica situação crítica --}}
        <div class="stat-value" style="color:var(--red)">{{ $stats['parada_critica'] }}</div>
    </div>
    @endif {{-- fim do bloco de permissão dashboard.maquinas --}}
    {{-- Exibe card de técnicos somente se o usuário tiver permissão dashboard.tecnicos --}}
    @if(auth()->user()->hasPermission('dashboard.tecnicos'))
    {{-- Card azul: quantidade de técnicos com status ativo --}}
    <div class="stat-card blue">
        <div class="stat-label">Técnicos Ativos</div>
        {{-- Valor em azul, exibe técnicos disponíveis --}}
        <div class="stat-value" style="color:var(--blue)">{{ $stats['tecnicos_ativos'] }}</div>
    </div>
    @endif {{-- fim do bloco de permissão dashboard.tecnicos --}}
    {{-- Exibe cards de ordens de serviço somente se o usuário tiver permissão dashboard.ordens --}}
    @if(auth()->user()->hasPermission('dashboard.ordens'))
    {{-- Card: ordens de serviço com status "aberta" --}}
    <div class="stat-card">
        <div class="stat-label">O.S. Abertas</div>
        {{-- Exibe o total de OS abertas aguardando atendimento --}}
        <div class="stat-value">{{ $stats['os_abertas'] }}</div>
    </div>
    {{-- Card amarelo: ordens que já foram iniciadas e estão em andamento --}}
    <div class="stat-card yellow">
        <div class="stat-label">Em Andamento</div>
        {{-- Valor em amarelo, OS com técnico atuando --}}
        <div class="stat-value" style="color:var(--yellow)">{{ $stats['os_em_andamento'] }}</div>
    </div>
    {{-- Card verde: ordens finalizadas no dia atual --}}
    <div class="stat-card green">
        <div class="stat-label">Concluídas Hoje</div>
        {{-- Valor em verde, produtividade do dia --}}
        <div class="stat-value" style="color:var(--green)">{{ $stats['os_concluidas_hoje'] }}</div>
    </div>
    @endif {{-- fim do bloco de permissão dashboard.ordens --}}
</div> {{-- fim da stats-grid --}}

{{-- ALERTAS DE PARADA CRÍTICA: bloco visível apenas com permissão e se houver alertas --}}
@if(auth()->user()->hasPermission('dashboard.alertas') && $alertas->count() > 0)
{{-- Container do painel de alertas, com borda e fundo vermelho suave --}}
<div style="margin-bottom:24px;border:1px solid rgba(248,81,73,.3);background:rgba(248,81,73,.04);padding:16px;border-radius:4px;">
    {{-- Título do painel de alertas com ícone de aviso e contador --}}
    <div style="font-family:var(--mono);font-size:18px;color:var(--red);letter-spacing:2px;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        ⚠ // ALERTAS — MÁQUINAS EM PARADA CRÍTICA
        {{-- Badge com a quantidade de máquinas em parada crítica --}}
        <span style="background:rgba(248,81,73,.15);color:var(--red);padding:4px 10px;font-size:18px;border:1px solid rgba(248,81,73,.3);">
            {{ $alertas->count() }}
        </span>
    </div>

    {{-- Itera sobre cada máquina em parada crítica para exibir no painel --}}
    @foreach($alertas as $m)

        {{-- Linha de alerta: modelo, localização e botão para abrir OS --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(248,81,73,.1);gap:12px;">
            {{-- Nome/modelo da máquina em parada crítica --}}
            <div style="font-family:var(--cond);font-size:18px;font-weight:600;flex:1;">{{ $m->modelo }}</div>
            {{-- Localização física da máquina no galpão --}}
            <span style="font-family:var(--mono);font-size:18px;color:var(--muted);flex:1;">{{ $m->localizacao }}</span>
            {{-- Link para criar nova OS pré-vinculada à máquina em alerta --}}
            <a href="{{ route('ordens.create') }}?maquina_id={{ $m->id }}" class="btn btn-danger btn-sm">
                + Abrir O.S.
            </a>
        </div>
    @endforeach {{-- fim do loop de alertas --}}
</div>
@endif {{-- fim do bloco de alertas de parada crítica --}}

{{-- GRID PRINCIPAL: layout em duas colunas (ordens ativas | coluna direita) --}}
<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:20px;">

    {{-- COLUNA ESQUERDA: tabela de ordens de serviço ativas --}}
    {{-- Bloco de ordens ativas, visível somente com permissão dashboard.ordens --}}
    @if(auth()->user()->hasPermission('dashboard.ordens'))
    <div>
        {{-- Cabeçalho da seção de OS com título e link "ver todas" --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            {{-- Título da seção no estilo de comentário de código --}}
            <div style="font-family:var(--mono);font-size:18px;color:var(--muted);letter-spacing:2px;">
                // ORDENS DE SERVIÇO ATIVAS
            </div>
            {{-- Link de atalho para a listagem completa de OS --}}
            <a href="{{ route('ordens.index') }}" style="font-family:var(--mono);font-size:18px;color:var(--accent);text-decoration:none;letter-spacing:1px;">
                ver todas →
            </a>
        </div>

        {{-- Container da tabela de OS com scroll horizontal --}}
        <div class="table-wrap">
            <table>
                {{-- Cabeçalho da tabela de ordens de serviço --}}
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
                    {{-- Itera sobre as OS recentes; exibe mensagem vazia se não houver nenhuma --}}
                    @forelse($ordensRecentes as $ordem)
                        {{-- Linha clicável: ao clicar navega para o detalhe da OS --}}
                        <tr style="cursor:pointer;" onclick="window.location.href=this.dataset.href" data-href="{{ route('ordens.show', $ordem->id) }}">
                            {{-- Número da OS com destaque em cor de acento --}}
                            <td class="mono" style="font-size:18px;color:var(--accent)">
                                {{ $ordem->numero }}
                            </td>

                            {{-- Modelo da máquina vinculada à OS; exibe traço se não houver --}}
                            <td>{{ $ordem->maquina->modelo ?? '-' }}</td>

                            {{-- Nome do técnico responsável pela OS; exibe traço se não atribuído --}}
                            <td style="color:var(--muted)">
                                {{ $ordem->tecnico->nome ?? '-' }}
                            </td>

                            <td>
                                {{-- Define a cor do badge de prioridade com base no valor do campo --}}
                                @php
                                    $pc = match($ordem->prioridade) {
                                        'critica' => 'red',    // prioridade crítica = vermelho
                                        'alta'    => 'orange', // prioridade alta = laranja
                                        'media'   => 'yellow', // prioridade média = amarelo
                                        default   => 'gray',   // baixa ou indefinida = cinza
                                    };
                                @endphp

                                {{-- Badge colorido exibindo o rótulo legível da prioridade --}}
                                <span class="badge badge-{{ $pc }}">
                                    {{ $ordem->prioridade_label }}
                                </span>
                            </td>

                            <td>
                                {{-- Define a cor do badge de status com base no valor do campo --}}
                                @php
                                    $sc = match($ordem->status) {
                                        'aberta'       => 'blue',   // OS aberta = azul
                                        'em_andamento' => 'yellow', // OS em andamento = amarelo
                                        default        => 'gray',   // outros = cinza
                                    };
                                @endphp

                                {{-- Badge colorido exibindo o rótulo legível do status --}}
                                <span class="badge badge-{{ $sc }}">
                                    {{ $ordem->status_label }}
                                </span>
                            </td>
                        </tr>
                    {{-- Fallback: exibido quando não há ordens ativas --}}
                    @empty
                        <tr>
                            {{-- Célula ocupando todas as colunas com mensagem de lista vazia --}}
                            <td colspan="5" style="color:var(--muted);font-family:var(--mono);font-size:18px;text-align:center;padding:20px;">
                                — sem ordens ativas —
                            </td>
                        </tr>
                    @endforelse {{-- fim do forelse de ordens recentes --}}
                </tbody>
            </table>
        </div>
    </div>

    @endif {{-- fim do bloco de permissão dashboard.ordens --}}

    {{-- COLUNA DIREITA: últimas manutenções e ações rápidas --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- ÚLTIMAS MANUTENÇÕES: tabela com histórico recente, visível com permissão dashboard.historico --}}
        @if(auth()->user()->hasPermission('dashboard.historico'))
        <div>
            {{-- Cabeçalho da seção de histórico com link para ver todos --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                {{-- Título da seção de últimas manutenções --}}
                <div style="font-family:var(--mono);font-size:18px;color:var(--muted);letter-spacing:2px;">
                    // ÚLTIMAS MANUTENÇÕES
                </div>
                {{-- Link de atalho para o histórico completo de manutenções --}}
                <a href="{{ route('historico.index') }}" style="font-family:var(--mono);font-size:18px;color:var(--accent);text-decoration:none;letter-spacing:1px;">
                    ver todas →
                </a>
            </div>

            {{-- Container da tabela de últimas manutenções --}}
            <div class="table-wrap">
                <table>
                    {{-- Cabeçalho da tabela: Máquina, Tipo e Data --}}
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th>Tipo</th>
                            <th>Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        {{-- Itera sobre as últimas manutenções do histórico --}}
                        @forelse($ultimasManutencoes as $h)
                            <tr>
                                {{-- Modelo da máquina que recebeu a manutenção --}}
                                <td>{{ $h->maquina->modelo ?? '-' }}</td>

                                <td>
                                    {{-- Badge laranja para corretiva, azul para preventiva --}}
                                    <span class="badge {{ $h->tipo === 'corretiva' ? 'badge-orange' : 'badge-blue' }}">
                                        {{-- Capitaliza a primeira letra do tipo de manutenção --}}
                                        {{ ucfirst($h->tipo) }}
                                    </span>
                                </td>

                                {{-- Data e hora de início da manutenção formatada --}}
                                <td style="color:var(--muted);font-family:var(--mono);font-size:18px;">
                                    {{ optional($h->data_inicio)->format('d/m H:i') }}
                                </td>
                            </tr>
                        {{-- Fallback: exibido quando não há registros de manutenção --}}
                        @empty
                            <tr>
                                {{-- Célula com mensagem de histórico vazio --}}
                                <td colspan="3" style="color:var(--muted);font-family:var(--mono);font-size:18px;text-align:center;padding:20px;">
                                    — sem registros —
                                </td>
                            </tr>
                        @endforelse {{-- fim do forelse de últimas manutenções --}}
                    </tbody>
                </table>
            </div>
        </div>

        @endif {{-- fim do bloco de permissão dashboard.historico --}}

        {{-- AÇÕES RÁPIDAS: botões de atalho para criação de registros --}}
        <div>
            {{-- Título da seção de ações rápidas --}}
            <div style="font-family:var(--mono);font-size:18px;color:var(--muted);letter-spacing:2px;margin-bottom:10px;">
                // AÇÕES RÁPIDAS
            </div>

            {{-- Lista vertical de botões de ação --}}
            <div style="display:flex;flex-direction:column;gap:8px;">
                {{-- Botão de nova OS, visível somente com permissão ordens.criar --}}
                @if(auth()->user()->hasPermission('ordens.criar'))
                {{-- Link para o formulário de criação de nova Ordem de Serviço --}}
                <a href="{{ route('ordens.create') }}" class="btn btn-primary" style="justify-content:center;">
                    + Nova Ordem de Serviço
                </a>
                @endif {{-- fim do bloco de permissão ordens.criar --}}
                {{-- Botão de nova máquina, visível somente com permissão maquinas.criar --}}
                @if(auth()->user()->hasPermission('maquinas.criar'))
                {{-- Link para o formulário de cadastro de nova máquina --}}
                <a href="{{ route('maquinas.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    + Cadastrar Máquina
                </a>
                @endif {{-- fim do bloco de permissão maquinas.criar --}}
                {{-- Botão de novo técnico, visível somente com permissão tecnicos.criar --}}
                @if(auth()->user()->hasPermission('tecnicos.criar'))
                {{-- Link para o formulário de cadastro de novo técnico --}}
                <a href="{{ route('tecnicos.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    + Cadastrar Técnico
                </a>
                @endif {{-- fim do bloco de permissão tecnicos.criar --}}
            </div>
        </div>

    </div> {{-- fim da coluna direita --}}
</div> {{-- fim do grid principal --}}

@endsection {{-- fim da seção content --}}
