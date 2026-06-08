{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador --}}
@section('title', 'Histórico de Manutenções')

{{-- Define o breadcrumb de navegação --}}
@section('breadcrumb')
    {{-- Texto fixo indicando a seção atual --}}
    <span>histórico</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com título e botões de ação --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo decorativo --}}
    <div class="page-title">
        {{-- Subtítulo em estilo monospace indicando log de intervenções --}}
        <small>// log de intervenções</small>
        {{-- Título principal da página --}}
        Histórico de Manutenções
    </div>
    {{-- GRUPO DE BOTÕES: IMPRIMIR / EXPORTAR
         - Botão Imprimir: abre caixa de diálogo de impressão (window.print)
         - Botão Exportar CSV: faz download do arquivo com filtros aplicados
    --}}
    {{-- Container flexível que agrupa os botões de ação --}}
    <div class="btn-export-group" style="display:flex;gap:8px;align-items:center">
        {{-- Botão que aciona a impressão nativa do navegador via JavaScript --}}
        <button onclick="window.print()" class="btn btn-secondary" title="Imprimir lista">
            &#128438; Imprimir
        </button>
        {{-- Link para exportar o histórico em CSV respeitando os filtros ativos da URL --}}
        <a href="{{ route('historico.exportar', request()->only(['maquina_id','tipo','tecnico_id','data_inicio','data_fim'])) }}"
           class="btn btn-secondary" title="Exportar para CSV">
            &#8659; Exportar CSV
        </a>
    </div>
</div>

{{-- FILTROS --}}
{{-- Card com formulário de filtros para refinar a listagem do histórico --}}
<div class="table-wrap" style="padding:16px;margin-bottom:16px">
    {{-- Formulário de filtragem enviado via GET para manter os filtros na URL --}}
    <form method="GET" action="{{ route('historico.index') }}">
        {{-- Container flexível que organiza os campos de filtro em linha --}}
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            {{-- Grupo do filtro de máquina --}}
            <div>
                {{-- Rótulo do filtro de máquina em estilo monospace --}}
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">MÁQUINA</div>
                {{-- Select para filtrar histórico por máquina; mantém seleção da URL atual --}}
                <select name="maquina_id" class="form-control" style="width:200px">
                    {{-- Opção padrão para não filtrar por máquina --}}
                    <option value="">Todas</option>
                    {{-- Itera sobre as máquinas cadastradas para gerar as opções --}}
                    @foreach($maquinas as $m)
                    {{-- Opção de máquina; mantém selecionada conforme query string da URL --}}
                    <option value="{{ $m->id }}" {{ request('maquina_id') == $m->id ? 'selected' : '' }}>{{ $m->modelo }}</option>
                    @endforeach {{-- fim da iteração sobre as máquinas --}}
                </select>
            </div>
            {{-- Grupo do filtro de tipo de manutenção --}}
            <div>
                {{-- Rótulo do filtro de tipo --}}
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">TIPO</div>
                {{-- Select para filtrar por tipo de manutenção --}}
                <select name="tipo" class="form-control" style="width:150px">
                    {{-- Opção padrão sem filtrar por tipo --}}
                    <option value="">Todos</option>
                    {{-- Opção preventiva; mantém selecionada conforme query string --}}
                    <option value="preventiva" {{ request('tipo') == 'preventiva' ? 'selected' : '' }}>Preventiva</option>
                    {{-- Opção corretiva; mantém selecionada conforme query string --}}
                    <option value="corretiva"  {{ request('tipo') == 'corretiva'  ? 'selected' : '' }}>Corretiva</option>
                </select>
            </div>
            {{-- Grupo do filtro de técnico --}}
            <div>
                {{-- Rótulo do filtro de técnico --}}
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">TÉCNICO</div>
                {{-- Select para filtrar por técnico responsável --}}
                <select name="tecnico_id" class="form-control" style="width:180px">
                    {{-- Opção padrão sem filtrar por técnico --}}
                    <option value="">Todos</option>
                    {{-- Itera sobre os técnicos cadastrados --}}
                    @foreach($tecnicos as $t)
                    {{-- Opção de técnico; mantém selecionada conforme query string --}}
                    <option value="{{ $t->id }}" {{ request('tecnico_id') == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
                    @endforeach {{-- fim da iteração sobre os técnicos --}}
                </select>
            </div>
            {{-- Grupo do filtro de data inicial do período --}}
            <div>
                {{-- Rótulo do campo de data inicial --}}
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">DE</div>
                {{-- Input de data para o início do período filtrado; mantém valor da URL --}}
                <input type="date" name="data_inicio" class="form-control" style="width:150px" value="{{ request('data_inicio') }}">
            </div>
            {{-- Grupo do filtro de data final do período --}}
            <div>
                {{-- Rótulo do campo de data final --}}
                <div style="font-family:var(--mono);font-size:9px;color:var(--muted);letter-spacing:1.5px;margin-bottom:5px">ATÉ</div>
                {{-- Input de data para o fim do período filtrado; mantém valor da URL --}}
                <input type="date" name="data_fim" class="form-control" style="width:150px" value="{{ request('data_fim') }}">
            </div>
            {{-- Botão que aplica os filtros ao submeter o formulário via GET --}}
            <button type="submit" class="btn btn-primary">Filtrar</button>
            {{-- Link que limpa todos os filtros retornando à URL base do histórico --}}
            <a href="{{ route('historico.index') }}" class="btn btn-secondary">Limpar</a>
        </div>
    </form>
</div>

{{-- Container da tabela de registros do histórico --}}
<div class="table-wrap">
    {{-- Tabela principal com os registros de histórico --}}
    <table>
        {{-- Cabeçalho da tabela com os nomes das colunas --}}
        <thead>
            <tr>
                <th>#</th>
                <th>Máquina</th>
                <th>Tipo</th>
                <th>Técnico</th>
                <th>O.S. Vinculada</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Parada (h)</th>
                <th>Custo</th>
                <th>Ações</th>
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
                {{-- Exibe o modelo da máquina vinculada ao histórico --}}
                <td style="font-weight:500">{{ $h->maquina->modelo ?? '—' }}</td>
                {{-- Coluna de tipo com badge colorido --}}
                <td>
                    {{-- Badge laranja para corretiva, azul para preventiva --}}
                    <span class="badge {{ $h->tipo === 'corretiva' ? 'badge-orange' : 'badge-blue' }}">
                        {{-- Exibe o tipo com primeira letra maiúscula --}}
                        {{ ucfirst($h->tipo) }}
                    </span>
                </td>
                {{-- Exibe o nome do técnico responsável pela manutenção --}}
                <td style="color:var(--muted)">{{ $h->tecnico->nome ?? '—' }}</td>
                {{-- Coluna com link para a OS vinculada ao histórico --}}
                <td class="mono" style="font-size:18px">
                    {{-- Verifica se há uma OS vinculada a este registro --}}
                    @if($h->ordem)
                        {{-- Link clicável para os detalhes da OS vinculada --}}
                        <a href="{{ route('ordens.show', $h->ordem) }}" style="color:var(--accent)">{{ $h->ordem->numero }}</a>
                    @else
                        {{-- Exibe traço quando não há OS vinculada --}}
                        <span style="color:var(--muted)">—</span>
                    @endif {{-- fim da verificação de OS vinculada --}}
                </td>
                {{-- Exibe a data/hora de início da manutenção --}}
                <td class="mono" style="font-size:18px;color:var(--muted)">{{ $h->data_inicio->format('d/m/Y H:i') }}</td>
                {{-- Exibe a data/hora de fim; "—" se ainda não finalizada --}}
                <td class="mono" style="font-size:18px;color:var(--muted)">
                    {{ $h->data_fim ? $h->data_fim->format('d/m/Y H:i') : '—' }}
                </td>
                {{-- Exibe o tempo de parada formatado com 1 decimal; "—" se zero --}}
                <td class="mono" style="font-size:18px;text-align:center">
                    {{ $h->tempo_parada_horas > 0 ? number_format($h->tempo_parada_horas, 1) : '—' }}
                </td>
                {{-- Exibe o custo em reais com cor de destaque; "—" se zero --}}
                <td class="mono" style="font-size:18px;color:{{ $h->custo > 0 ? 'var(--accent)' : 'var(--muted)' }}">
                    {{ $h->custo > 0 ? 'R$ '.number_format($h->custo, 2, ',', '.') : '—' }}
                </td>
                {{-- Coluna de ações com botões de ver e deletar --}}
                <td>
                    {{-- Container dos botões de ação --}}
                    <div class="actions">
                        {{-- Botão que navega para os detalhes do registro de histórico --}}
                        <a href="{{ route('historico.show', $h) }}" class="btn btn-secondary btn-sm">Ver</a>
                        {{-- Verifica se o usuário tem permissão para deletar registros de histórico --}}
                        @if(auth()->user()->hasPermission('historico.deletar'))
                        {{-- Formulário de exclusão com confirmação antes de enviar --}}
                        <form method="POST" action="{{ route('historico.destroy', $h) }}"
                              onsubmit="confirmDelete(this, 'Excluir este registro de histórico?'); return false;">
                            {{-- Token CSRF e método DELETE para excluir o registro --}}
                            @csrf @method('DELETE')
                            {{-- Botão vermelho de exclusão do registro --}}
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                        @endif {{-- fim da verificação de permissão para deletar --}}
                    </div>
                </td>
            </tr>
            {{-- Bloco exibido quando não há nenhum registro de histórico --}}
            @empty
            <tr>
                {{-- Célula que ocupa todas as 10 colunas com mensagem vazia --}}
                <td colspan="10" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhum registro no histórico —
                </td>
            </tr>
            @endforelse {{-- fim da iteração sobre os históricos --}}
        </tbody>
    </table>
    {{-- Renderiza os links de paginação da coleção de históricos --}}
    <div class="pagination">{{ $historicos->links() }}</div>
</div>

@endsection {{-- fim da seção de conteúdo principal --}}
