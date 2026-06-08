{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador com o modelo da máquina --}}
@section('title', 'Histórico — '.$maquina->modelo)
{{-- Define o breadcrumb de navegação com link para a listagem geral do histórico --}}
@section('breadcrumb')
    {{-- Link clicável que retorna à listagem geral do histórico --}}
    <a href="{{ route('historico.index') }}" style="color:var(--muted);text-decoration:none">histórico</a>
    {{-- Separador visual entre os itens do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Exibe o modelo da máquina como item atual do breadcrumb --}}
    <span>{{ $maquina->modelo }}</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com modelo, número de série e botões de ação --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo e identificação da máquina --}}
    <div class="page-title">
        {{-- Subtítulo em estilo monospace identificando a página --}}
        <small>// histórico por equipamento</small>
        {{-- Exibe o modelo da máquina como título principal --}}
        {{ $maquina->modelo }}
        {{-- Exibe o número de série da máquina em fonte menor e cor discreta --}}
        <span style="font-family:var(--mono);font-size:12px;color:var(--muted);font-weight:400;margin-left:12px">
            {{ $maquina->numero_serie }}
        </span>
    </div>
    {{-- Container dos botões de ação do cabeçalho --}}
    <div style="display:flex;gap:8px">
        {{-- Link que navega para a página de detalhes da máquina --}}
        <a href="{{ route('maquinas.show', $maquina) }}" class="btn btn-secondary">Ver Máquina</a>
        {{-- Link que abre o formulário de nova OS pré-preenchido com esta máquina --}}
        <a href="{{ route('ordens.create') }}?maquina_id={{ $maquina->id }}" class="btn btn-primary">+ Nova O.S.</a>
    </div>
</div>

{{-- REINCIDÊNCIA --}}
{{-- Exibe a seção de análise de reincidência somente se houver dados de reincidência --}}
@if($reincidencias->count() > 0)
{{-- Card com a análise de manutenções corretivas por mês --}}
<div class="table-wrap" style="padding:16px;margin-bottom:20px">
    {{-- Rótulo decorativo em amarelo indicando análise de reincidência --}}
    <div style="font-family:var(--mono);font-size:16px;color:var(--yellow);letter-spacing:2px;margin-bottom:12px">
        ⚠ // ANÁLISE DE REINCIDÊNCIA — MANUTENÇÕES CORRETIVAS POR MÊS
    </div>
    {{-- Container flexível para os cards mensais de reincidência --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        {{-- Itera sobre cada mês/ano com registros de reincidência --}}
        @foreach($reincidencias as $r)
        {{-- Card individual de cada período com total de corretivas --}}
        <div style="background:var(--surface);border:1px solid var(--border);padding:14px 18px;text-align:center">
            {{-- Exibe o mês formatado com zero à esquerda e o ano --}}
            <div style="font-family:var(--mono);font-size:14px;color:var(--muted);letter-spacing:1px">
                {{ str_pad($r->mes,2,'0',STR_PAD_LEFT) }}/{{ $r->ano }}
            </div>
            {{-- Exibe o total de corretivas; vermelho se >= 3, amarelo se >= 2, normal caso contrário --}}
            <div style="font-family:var(--cond);font-size:32px;font-weight:700;color:{{ $r->total >= 3 ? 'var(--red)' : ($r->total >= 2 ? 'var(--yellow)' : 'var(--text)') }}">
                {{-- Número de ocorrências corretivas no período --}}
                {{ $r->total }}
            </div>
            {{-- Rótulo descritivo abaixo do número --}}
            <div style="font-family:var(--mono);font-size:14px;color:var(--muted)">corretivas</div>
        </div>
        @endforeach {{-- fim da iteração sobre os períodos de reincidência --}}
    </div>
</div>
@endif {{-- fim da verificação de reincidências --}}

{{-- HISTÓRICO --}}
{{-- Container da tabela com todos os registros de histórico desta máquina --}}
<div class="table-wrap">
    {{-- Tabela de histórico de manutenções da máquina --}}
    <table>
        {{-- Cabeçalho da tabela com os nomes das colunas --}}
        <thead>
            <tr>
                <th>#</th>
                <th>Tipo</th>
                <th>Técnico</th>
                <th>O.S.</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Parada (h)</th>
                <th>Custo</th>
                <th>Ver</th>
            </tr>
        </thead>
        {{-- Corpo da tabela com os registros de histórico --}}
        <tbody>
            {{-- Itera sobre os históricos; exibe mensagem se a lista estiver vazia --}}
            @forelse($historicos as $h)
            {{-- Linha da tabela para cada registro de histórico --}}
            <tr>
                {{-- Exibe o ID do registro em fonte mono --}}
                <td class="mono" style="color:var(--muted);font-size:18px">{{ $h->id }}</td>
                {{-- Badge colorido: laranja para corretiva, azul para preventiva --}}
                <td><span class="badge {{ $h->tipo==='corretiva'?'badge-orange':'badge-blue' }}">{{ ucfirst($h->tipo) }}</span></td>
                {{-- Exibe o nome do técnico responsável --}}
                <td style="color:var(--muted)">{{ $h->tecnico->nome ?? '—' }}</td>
                {{-- Coluna com link para a OS vinculada ao registro --}}
                <td class="mono" style="font-size:18px">
                    {{-- Verifica se há uma OS vinculada a este histórico --}}
                    @if($h->ordem)
                        {{-- Link clicável para os detalhes da OS vinculada --}}
                        <a href="{{ route('ordens.show', $h->ordem) }}" style="color:var(--accent)">{{ $h->ordem->numero }}</a>
                    @else —
                    @endif {{-- fim da verificação de OS vinculada --}}
                </td>
                {{-- Exibe a data/hora de início da manutenção --}}
                <td class="mono" style="font-size:18px;color:var(--muted)">{{ $h->data_inicio->format('d/m/Y H:i') }}</td>
                {{-- Exibe a data/hora de fim; "—" se ainda não finalizada --}}
                <td class="mono" style="font-size:18px;color:var(--muted)">{{ $h->data_fim ? $h->data_fim->format('d/m/Y H:i') : '—' }}</td>
                {{-- Exibe o tempo de parada com 1 decimal; "—" se zero --}}
                <td class="mono" style="font-size:18px;text-align:center">{{ $h->tempo_parada_horas > 0 ? number_format($h->tempo_parada_horas,1) : '—' }}</td>
                {{-- Exibe o custo em reais com cor de destaque; "—" se zero --}}
                <td class="mono" style="font-size:18px;color:{{ $h->custo > 0 ? 'var(--accent)' : 'var(--muted)' }}">
                    {{ $h->custo > 0 ? 'R$ '.number_format($h->custo,2,',','.') : '—' }}
                </td>
                {{-- Botão que navega para os detalhes do registro de histórico --}}
                <td><a href="{{ route('historico.show', $h) }}" class="btn btn-secondary btn-sm">Ver</a></td>
            </tr>
            {{-- Bloco exibido quando não há nenhum histórico para esta máquina --}}
            @empty
            <tr>
                {{-- Célula que ocupa todas as 9 colunas com mensagem de lista vazia --}}
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhum histórico para esta máquina —
                </td>
            </tr>
            @endforelse {{-- fim da iteração sobre os históricos --}}
        </tbody>
    </table>
    {{-- Renderiza os links de paginação da coleção de históricos --}}
    <div class="pagination">{{ $historicos->links() }}</div>
</div>

@endsection {{-- fim da seção de conteúdo principal --}}
