{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador com o ID do registro --}}
@section('title', 'Registro #'.$historico->id)
{{-- Define o breadcrumb de navegação com link para a listagem do histórico --}}
@section('breadcrumb')
    {{-- Link clicável que retorna à listagem do histórico --}}
    <a href="{{ route('historico.index') }}" style="color:var(--muted);text-decoration:none">histórico</a>
    {{-- Separador visual entre os itens do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Exibe o ID do registro atual no breadcrumb --}}
    <span>#{{ $historico->id }}</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com modelo da máquina, ID e botão de voltar --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo e identificação do registro --}}
    <div class="page-title">
        {{-- Subtítulo em estilo monospace identificando o tipo de página --}}
        <small>// registro de manutenção</small>
        {{-- Exibe o modelo da máquina e o ID do registro; "Máquina" se não houver modelo --}}
        {{ $historico->maquina->modelo ?? 'Máquina' }} — #{{ $historico->id }}
    </div>
    {{-- Botão que retorna à listagem do histórico --}}
    <a href="{{ route('historico.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

{{-- Layout em grid de duas colunas: dados resumidos (300px) à esquerda e textos à direita --}}
<div style="display:grid;grid-template-columns:300px 1fr;gap:20px">

    {{-- Coluna da esquerda com dados resumidos do registro --}}
    <div class="table-wrap" style="padding:20px;height:fit-content">
        {{-- Badge colorido exibindo o tipo de manutenção do registro --}}
        <span class="badge {{ $historico->tipo==='corretiva'?'badge-orange':'badge-blue' }}" style="margin-bottom:16px;display:inline-block">
            {{-- Exibe o tipo com primeira letra maiúscula --}}
            {{ ucfirst($historico->tipo) }}
        </span>

        {{-- Itera sobre array de pares [rótulo, valor] para exibir os dados do histórico --}}
        @foreach([
            ['Máquina',      $historico->maquina->modelo ?? '—'],
            ['Nº Série',     $historico->maquina->numero_serie ?? '—'],
            ['Localização',  $historico->maquina->localizacao ?? '—'],
            ['Técnico',      $historico->tecnico->nome ?? '—'],
            ['O.S.',         $historico->ordem->numero ?? '—'],
            ['Início',       $historico->data_inicio->format('d/m/Y H:i')],
            ['Fim',          $historico->data_fim ? $historico->data_fim->format('d/m/Y H:i') : '—'],
            ['Parada (h)',   $historico->tempo_parada_horas > 0 ? number_format($historico->tempo_parada_horas,1).'h' : '—'],
            ['Custo',        $historico->custo > 0 ? 'R$ '.number_format($historico->custo,2,',','.') : '—'],
        ] as [$label, $value])
        {{-- Linha de informação com rótulo à esquerda e valor à direita --}}
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
            {{-- Rótulo em fonte mono e cor discreta --}}
            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
            {{-- Valor em fonte condensada e negrito --}}
            <span style="font-family:var(--cond);font-size:13px;font-weight:500;max-width:160px;text-align:right">{{ $value }}</span>
        </div>
        @endforeach {{-- fim da iteração sobre os dados do histórico --}}
    </div>

    {{-- Coluna da direita com os textos do registro --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Card com a descrição do problema ou serviço realizado --}}
        <div class="table-wrap" style="padding:20px">
            {{-- Rótulo decorativo da seção de descrição --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">// DESCRIÇÃO</div>
            {{-- Texto descritivo do problema ou serviço --}}
            <p style="font-size:14px;line-height:1.7">{{ $historico->descricao }}</p>
        </div>

        {{-- Exibe o card de solução somente se houver solução registrada --}}
        @if($historico->solucao)
        {{-- Card com borda esverdeada exibindo a solução aplicada --}}
        <div class="table-wrap" style="padding:20px;border-color:rgba(63,185,80,.3)">
            {{-- Rótulo decorativo da seção em cor verde --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--green);letter-spacing:2px;margin-bottom:10px">✓ // SOLUÇÃO APLICADA</div>
            {{-- Texto da solução aplicada na manutenção --}}
            <p style="font-size:14px;line-height:1.7">{{ $historico->solucao }}</p>
        </div>
        @endif {{-- fim da verificação de solução --}}

        {{-- Exibe o card de peças somente se houver peças registradas --}}
        @if($historico->pecas_utilizadas)
        {{-- Card com a lista de peças utilizadas na manutenção --}}
        <div class="table-wrap" style="padding:20px">
            {{-- Rótulo decorativo da seção de peças --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">// PEÇAS UTILIZADAS</div>
            {{-- Texto com as peças em estilo monospace para facilitar leitura de códigos --}}
            <p style="font-size:14px;line-height:1.7;font-family:var(--mono);font-size:12px;color:var(--muted)">{{ $historico->pecas_utilizadas }}</p>
        </div>
        @endif {{-- fim da verificação de peças utilizadas --}}

        {{-- Exibe o card de observações somente se houver observações registradas --}}
        @if($historico->observacoes)
        {{-- Card com observações adicionais sobre a manutenção --}}
        <div class="table-wrap" style="padding:20px">
            {{-- Rótulo decorativo da seção de observações --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">// OBSERVAÇÕES</div>
            {{-- Texto das observações adicionais --}}
            <p style="font-size:14px;line-height:1.7">{{ $historico->observacoes }}</p>
        </div>
        @endif {{-- fim da verificação de observações --}}
    </div>

</div>

@endsection {{-- fim da seção de conteúdo principal --}}
