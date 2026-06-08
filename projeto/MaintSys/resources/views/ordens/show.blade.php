{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador com o número da OS --}}
@section('title', $ordem->numero)
{{-- Define o breadcrumb de navegação com link para a listagem --}}
@section('breadcrumb')
    {{-- Link clicável que retorna à listagem de ordens --}}
    <a href="{{ route('ordens.index') }}" style="color:var(--muted);text-decoration:none">ordens</a>
    {{-- Separador visual entre os itens do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Exibe o número da OS como item atual do breadcrumb --}}
    <span>{{ $ordem->numero }}</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com número da OS e botões de ação --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo e número da OS --}}
    <div class="page-title">
        {{-- Subtítulo em estilo monospace identificando a seção --}}
        <small>// ordem de serviço</small>
        {{-- Exibe o número identificador da OS --}}
        {{ $ordem->numero }}
    </div>
    {{-- GRUPO DE BOTÕES: IMPRIMIR / EXPORTAR / EDITAR
         - Botão Imprimir: abre caixa de diálogo de impressão (window.print)
         - Botão Exportar CSV: faz download do arquivo
         - Botão Editar: link para edição (visível só com permissão)
    --}}
    {{-- Container flexível com os botões de ação do cabeçalho --}}
    <div class="btn-export-group" style="display:flex;gap:8px">
        {{-- Botão que aciona a impressão nativa do navegador via JavaScript --}}
        <button onclick="window.print()" class="btn btn-secondary" title="Imprimir O.S.">
            &#128438; Imprimir
        </button>
        {{-- Link para exportar os dados desta OS específica no formato CSV --}}
        <a href="{{ route('ordens.exportar-single', $ordem) }}" class="btn btn-secondary" title="Exportar para CSV">
            &#8659; Exportar CSV
        </a>
        {{-- Verifica se o usuário tem permissão para editar ordens --}}
        @if(auth()->user()->hasPermission('ordens.editar'))
        {{-- Botão de edição visível apenas para quem tem permissão --}}
        <a href="{{ route('ordens.edit', $ordem) }}" class="btn btn-primary">Editar O.S.</a>
        @endif {{-- fim da verificação de permissão para editar --}}
    </div>
</div>

{{-- Layout em grid de duas colunas: detalhes (300px) à esquerda e conteúdo à direita --}}
<div style="display:grid;grid-template-columns:300px 1fr;gap:20px">

    {{-- DETALHES --}}
    {{-- Coluna da esquerda com os dados resumidos da OS --}}
    <div class="table-wrap" style="padding:20px;height:fit-content">
        {{-- Define as variáveis de cor para prioridade ($pc) e status ($sc) --}}
        @php
            $pc = match($ordem->prioridade){'critica'=>'red','alta'=>'orange','media'=>'yellow',default=>'gray'};
            $sc = match($ordem->status){'aberta'=>'blue','em_andamento'=>'yellow','concluida'=>'green',default=>'gray'};
        @endphp
        {{-- Container com os badges de status, prioridade e tipo da OS --}}
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
            {{-- Badge colorido exibindo o status atual da OS --}}
            <span class="badge badge-{{ $sc }}" style="font-size:12px">{{ $ordem->status_label }}</span>
            {{-- Badge colorido exibindo a prioridade da OS --}}
            <span class="badge badge-{{ $pc }}" style="font-size:12px">{{ $ordem->prioridade_label }}</span>
            {{-- Badge colorido diferenciando corretiva (laranja) de preventiva (azul) --}}
            <span class="badge {{ $ordem->tipo==='corretiva'?'badge-orange':'badge-blue' }}" style="font-size:12px">{{ $ordem->tipo_label }}</span>
        </div>

        {{-- Itera sobre array de pares [rótulo, valor] para exibir as informações da OS --}}
        @foreach([
            ['Número',    $ordem->numero],
            ['Máquina',   $ordem->maquina->modelo ?? '—'],
            ['Nº Série',  $ordem->maquina->numero_serie ?? '—'],
            ['Local',     $ordem->maquina->localizacao ?? '—'],
            ['Técnico',   $ordem->tecnico->nome ?? '—'],
            ['Abertura',  $ordem->data_abertura->format('d/m/Y H:i')],
            ['Prevista',  $ordem->data_prevista ? $ordem->data_prevista->format('d/m/Y') : '—'],
            ['Conclusão', $ordem->data_conclusao ? $ordem->data_conclusao->format('d/m/Y H:i') : '—'],
        ] as [$label, $value])
        {{-- Linha de informação com rótulo à esquerda e valor à direita --}}
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
            {{-- Rótulo em fonte mono e cor discreta --}}
            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
            {{-- Valor em fonte condensada e negrito --}}
            <span style="font-family:var(--cond);font-size:13px;font-weight:500;max-width:160px;text-align:right">{{ $value }}</span>
        </div>
        @endforeach {{-- fim da iteração sobre os dados da OS --}}
    </div>

    {{-- DESCRIÇÃO E SOLUÇÃO --}}
    {{-- Coluna da direita com os textos descritivos e dados do histórico --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Card com a descrição detalhada do problema ou serviço --}}
        <div class="table-wrap" style="padding:20px">
            {{-- Rótulo decorativo da seção --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">
                // DESCRIÇÃO DO PROBLEMA / SERVIÇO
            </div>
            {{-- Texto da descrição do problema ou serviço a executar --}}
            <p style="font-size:14px;line-height:1.7;color:var(--text)">{{ $ordem->descricao }}</p>
        </div>

        {{-- Exibe o card de solução somente se a OS tiver solução registrada --}}
        @if($ordem->solucao)
        {{-- Card com borda esverdeada destacando a solução aplicada --}}
        <div class="table-wrap" style="padding:20px;border-color:rgba(63,185,80,.3)">
            {{-- Rótulo decorativo da seção em cor verde --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--green);letter-spacing:2px;margin-bottom:10px">
                ✓ // SOLUÇÃO APLICADA
            </div>
            {{-- Texto da solução aplicada na manutenção --}}
            <p style="font-size:14px;line-height:1.7;color:var(--text)">{{ $ordem->solucao }}</p>
        </div>
        @endif {{-- fim da verificação de solução --}}

        {{-- Exibe o card de histórico somente se houver registro de histórico vinculado --}}
        @if($ordem->historico)
        {{-- Card com os dados do registro de histórico vinculado a esta OS --}}
        <div class="table-wrap" style="padding:20px">
            {{-- Rótulo decorativo da seção de histórico --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:12px">
                // REGISTRO NO HISTÓRICO
            </div>
            {{-- Container flexível com as informações do histórico --}}
            <div style="display:flex;gap:24px;flex-wrap:wrap">
                {{-- Bloco com a data/hora de início da manutenção --}}
                <div>
                    {{-- Rótulo do campo de início --}}
                    <div style="font-family:var(--mono);font-size:10px;color:var(--muted)">INÍCIO</div>
                    {{-- Exibe a data/hora de início formatada; "—" se não registrada --}}
                    <div style="font-family:var(--cond);font-size:15px;font-weight:600">
                        {{ $ordem->historico->data_inicio?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
                {{-- Bloco com a data/hora de fim da manutenção --}}
                <div>
                    {{-- Rótulo do campo de fim --}}
                    <div style="font-family:var(--mono);font-size:10px;color:var(--muted)">FIM</div>
                    {{-- Exibe a data/hora de fim formatada; "—" se não registrada --}}
                    <div style="font-family:var(--cond);font-size:15px;font-weight:600">
                        {{ $ordem->historico->data_fim?->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
                {{-- Exibe o bloco de custo somente se houver custo registrado --}}
                @if($ordem->historico->custo)
                {{-- Bloco com o custo total da manutenção em destaque --}}
                <div>
                    {{-- Rótulo do campo de custo --}}
                    <div style="font-family:var(--mono);font-size:10px;color:var(--muted)">CUSTO</div>
                    {{-- Exibe o custo formatado em reais com separadores brasileiros --}}
                    <div style="font-family:var(--cond);font-size:15px;font-weight:600;color:var(--accent)">
                        R$ {{ number_format($ordem->historico->custo, 2, ',', '.') }}
                    </div>
                </div>
                @endif {{-- fim da verificação de custo --}}
            </div>
        </div>
        @endif {{-- fim da verificação de histórico --}}

    </div>
</div>

@endsection {{-- fim da seção de conteúdo principal --}}
