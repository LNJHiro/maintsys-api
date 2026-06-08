{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador --}}
@section('title', 'Editar O.S.')
{{-- Define o breadcrumb de navegação com link para a listagem --}}
@section('breadcrumb')
    {{-- Link clicável que retorna à listagem de ordens --}}
    <a href="{{ route('ordens.index') }}" style="color:var(--muted);text-decoration:none">ordens</a>
    {{-- Separador visual entre os itens do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Exibe o número da OS atual no breadcrumb --}}
    <span>{{ $ordem->numero }}</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com número da OS e botão de voltar --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo e número da OS --}}
    <div class="page-title">
        {{-- Subtítulo em estilo monospace indicando edição de ordem --}}
        <small>// edição de ordem</small>
        {{-- Exibe o número identificador da OS sendo editada --}}
        {{ $ordem->numero }}
    </div>
    {{-- Botão que retorna à página de detalhes da OS --}}
    <a href="{{ route('ordens.show', $ordem) }}" class="btn btn-secondary">← Voltar</a>
</div>

{{-- Card que envolve o formulário de edição --}}
<div class="form-card">
    {{-- Formulário enviado via POST com método PUT simulado para atualizar a OS --}}
    <form method="POST" action="{{ route('ordens.update', $ordem) }}">
        {{-- Token CSRF obrigatório para proteção contra falsificação de requisição --}}
        @csrf
        {{-- Diretiva que simula o método HTTP PUT, já que HTML só suporta GET e POST --}}
        @method('PUT')

        {{-- Linha de formulário com máquina e técnico lado a lado --}}
        <div class="form-row">
            {{-- Grupo do campo de seleção de máquina --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de máquina --}}
                <label>Máquina *</label>
                {{-- Select obrigatório; carrega o valor atual da OS ou o enviado anteriormente --}}
                <select name="maquina_id" class="form-control" required>
                    {{-- Itera sobre todas as máquinas cadastradas --}}
                    @foreach($maquinas as $m)
                    {{-- Opção de máquina; marca como selecionada a máquina atual da OS --}}
                    <option value="{{ $m->id }}" {{ old('maquina_id',$ordem->maquina_id)==$m->id?'selected':'' }}>
                        {{-- Exibe modelo e número de série da máquina --}}
                        {{ $m->modelo }} — {{ $m->numero_serie }}
                    </option>
                    @endforeach {{-- fim da iteração sobre as máquinas --}}
                </select>
            </div>
            {{-- Grupo do campo de seleção do técnico responsável --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de técnico --}}
                <label>Técnico Responsável *</label>
                {{-- Select obrigatório; carrega o técnico atual da OS --}}
                <select name="tecnico_id" class="form-control" required>
                    {{-- Itera sobre todos os técnicos cadastrados --}}
                    @foreach($tecnicos as $t)
                    {{-- Opção de técnico; marca como selecionado o técnico atual da OS --}}
                    <option value="{{ $t->id }}" {{ old('tecnico_id',$ordem->tecnico_id)==$t->id?'selected':'' }}>
                        {{-- Exibe nome e especialidade; "Geral" se não houver --}}
                        {{ $t->nome }} — {{ $t->especialidade ?? 'Geral' }}
                    </option>
                    @endforeach {{-- fim da iteração sobre os técnicos --}}
                </select>
            </div>
        </div>

        {{-- Linha de formulário com tipo e prioridade lado a lado --}}
        <div class="form-row">
            {{-- Grupo do campo de tipo de manutenção --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de tipo --}}
                <label>Tipo *</label>
                {{-- Select obrigatório; exibe o tipo atual da OS --}}
                <select name="tipo" class="form-control" required>
                    {{-- Opção preventiva; mantém selecionada se for o tipo atual --}}
                    <option value="preventiva" {{ old('tipo',$ordem->tipo)=='preventiva'?'selected':'' }}>Preventiva</option>
                    {{-- Opção corretiva; mantém selecionada se for o tipo atual --}}
                    <option value="corretiva"  {{ old('tipo',$ordem->tipo)=='corretiva'?'selected':'' }}>Corretiva</option>
                </select>
            </div>
            {{-- Grupo do campo de prioridade --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de prioridade --}}
                <label>Prioridade *</label>
                {{-- Select obrigatório; exibe a prioridade atual da OS --}}
                <select name="prioridade" class="form-control" required>
                    {{-- Opção baixa; selecionada se for a prioridade atual --}}
                    <option value="baixa"   {{ old('prioridade',$ordem->prioridade)=='baixa'?'selected':'' }}>Baixa</option>
                    {{-- Opção média; selecionada se for a prioridade atual --}}
                    <option value="media"   {{ old('prioridade',$ordem->prioridade)=='media'?'selected':'' }}>Média</option>
                    {{-- Opção alta; selecionada se for a prioridade atual --}}
                    <option value="alta"    {{ old('prioridade',$ordem->prioridade)=='alta'?'selected':'' }}>Alta</option>
                    {{-- Opção crítica com ícone; selecionada se for a prioridade atual --}}
                    <option value="critica" {{ old('prioridade',$ordem->prioridade)=='critica'?'selected':'' }}>🚨 Crítica</option>
                </select>
            </div>
        </div>

        {{-- Linha de formulário com status e data prevista --}}
        <div class="form-row">
            {{-- Grupo do campo de status da OS --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de status --}}
                <label>Status *</label>
                {{-- Select com ID para ser controlado pelo JavaScript ao mudar para "concluida" --}}
                <select name="status" id="status-select" class="form-control" required>
                    {{-- Opção aberta; selecionada se for o status atual --}}
                    <option value="aberta"       {{ old('status',$ordem->status)=='aberta'?'selected':'' }}>Aberta</option>
                    {{-- Opção em andamento; selecionada se for o status atual --}}
                    <option value="em_andamento" {{ old('status',$ordem->status)=='em_andamento'?'selected':'' }}>Em Andamento</option>
                    {{-- Opção concluída; ao selecionar, o JS exibe campos extras de conclusão --}}
                    <option value="concluida"    {{ old('status',$ordem->status)=='concluida'?'selected':'' }}>Concluída</option>
                    {{-- Opção cancelada; selecionada se for o status atual --}}
                    <option value="cancelada"    {{ old('status',$ordem->status)=='cancelada'?'selected':'' }}>Cancelada</option>
                </select>
            </div>
            {{-- Grupo do campo de data prevista --}}
            <div class="form-group">
                {{-- Rótulo do campo opcional de data prevista --}}
                <label>Data Prevista</label>
                {{-- Input de data com valor atual da OS; usa null-safe operator para evitar erro --}}
                <input type="date" name="data_prevista" class="form-control"
                       value="{{ old('data_prevista', $ordem->data_prevista?->format('Y-m-d')) }}">
            </div>
        </div>

        {{-- Grupo do campo de descrição do problema --}}
        <div class="form-group">
            {{-- Rótulo do campo obrigatório de descrição --}}
            <label>Descrição *</label>
            {{-- Textarea com o conteúdo atual da OS; preenchido com valor antigo em caso de erro --}}
            <textarea name="descricao" class="form-control" rows="3" required>{{ old('descricao', $ordem->descricao) }}</textarea>
        </div>

        {{-- Grupo do campo de solução aplicada --}}
        <div class="form-group">
            {{-- Rótulo do campo opcional de solução --}}
            <label>Solução Aplicada</label>
            {{-- Textarea para descrever a solução; preenchido com valor atual da OS --}}
            <textarea name="solucao" class="form-control" rows="3"
                      placeholder="Descreva a solução aplicada ao concluir a O.S...">{{ old('solucao', $ordem->solucao) }}</textarea>
        </div>

        {{-- Seção oculta com campos extras exibidos somente quando o status for "concluida" --}}
        {{-- CAMPOS EXTRAS AO CONCLUIR --}}
        {{-- Container dos campos de conclusão; display:none por padrão, controlado via JS --}}
        <div id="campos-conclusao" style="display:none; border-top:1px solid var(--border); padding-top:18px; margin-top:4px;">
            {{-- Rótulo decorativo que indica a seção de dados de conclusão --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--green);letter-spacing:2px;margin-bottom:14px;">
                ✓ // DADOS DE CONCLUSÃO
            </div>
            {{-- Linha com campos de tempo de parada e custo lado a lado --}}
            <div class="form-row">
                {{-- Grupo do campo de tempo de parada --}}
                <div class="form-group">
                    {{-- Rótulo do campo de tempo de parada em horas --}}
                    <label>Tempo de Parada (horas)</label>
                    {{-- Input numérico com incremento de 0,5 horas e valor mínimo 0 --}}
                    <input type="number" name="tempo_parada_horas" class="form-control"
                           step="0.5" min="0" value="{{ old('tempo_parada_horas', 0) }}"
                           placeholder="ex: 2.5">
                </div>
                {{-- Grupo do campo de custo total --}}
                <div class="form-group">
                    {{-- Rótulo do campo de custo total em reais --}}
                    <label>Custo Total (R$)</label>
                    {{-- Input numérico monetário com precisão de 2 casas decimais --}}
                    <input type="number" name="custo" class="form-control"
                           step="0.01" min="0" value="{{ old('custo', 0) }}"
                           placeholder="ex: 350.00">
                </div>
            </div>
            {{-- Grupo do campo de peças utilizadas --}}
            <div class="form-group">
                {{-- Rótulo do campo de peças --}}
                <label>Peças Utilizadas</label>
                {{-- Textarea para listar as peças trocadas ou usadas na manutenção --}}
                <textarea name="pecas_utilizadas" class="form-control" rows="2"
                          placeholder="ex: Rolamento 6205, Correia B-52...">{{ old('pecas_utilizadas') }}</textarea>
            </div>
        </div>

        {{-- Seção oculta para agendamento automático da próxima preventiva --}}
        {{-- CAMPOS DE PREVENTIVA AUTOMÁTICA --}}
        {{-- Container dos campos de preventiva; exibido somente quando status=concluida e tipo=preventiva --}}
        <div id="campos-preventiva" style="display:none; border-top:1px solid var(--border); padding-top:18px; margin-top:4px;">
            {{-- Rótulo decorativo que indica a seção de agendamento preventivo --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--blue);letter-spacing:2px;margin-bottom:14px;">
                📅 // AGENDAR PRÓXIMA PREVENTIVA
            </div>
            {{-- Grupo do campo de data da próxima preventiva --}}
            <div class="form-group">
                {{-- Rótulo do campo de próxima preventiva --}}
                <label>Data da Próxima Manutenção Preventiva</label>
                {{-- Input de data para geração automática da próxima OS preventiva --}}
                <input type="date" name="proxima_preventiva" class="form-control"
                       value="{{ old('proxima_preventiva') }}">
                {{-- Instrução informando que o campo é opcional --}}
                <small style="color:var(--muted);display:block;margin-top:4px">Deixe em branco se não quiser agendar automaticamente</small>
            </div>
        </div>

        {{-- Container dos botões de salvar e cancelar --}}
        <div style="display:flex;gap:10px;margin-top:18px">
            {{-- Botão de submissão que salva as alterações da OS --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            {{-- Link de cancelamento que retorna aos detalhes sem salvar --}}
            <a href="{{ route('ordens.show', $ordem) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

{{-- Empilha o bloco de script no stack "scripts" para ser renderizado no rodapé --}}
@push('scripts')
<script>
    // Captura o elemento select de status pelo ID
    const statusSelect = document.getElementById('status-select');
    // Captura o elemento select de tipo pelo atributo name
    const tipoSelect = document.querySelector('select[name="tipo"]');
    // Captura o container dos campos de conclusão
    const camposConclusao = document.getElementById('campos-conclusao');
    // Captura o container dos campos de preventiva
    const camposPreventiva = document.getElementById('campos-preventiva');

    // Função que mostra/oculta campos extras conforme o status e tipo selecionados
    function toggleCampos() {
        // Verifica se o status atual é "concluida"
        const isConcluida = statusSelect.value === 'concluida';
        // Verifica se o tipo atual é "preventiva"
        const isPreventiva = tipoSelect.value === 'preventiva';

        // Exibe campos de conclusão somente quando status for "concluida"
        camposConclusao.style.display = isConcluida ? 'block' : 'none';
        // Exibe campos de preventiva somente quando concluída E tipo preventiva
        camposPreventiva.style.display = (isConcluida && isPreventiva) ? 'block' : 'none';
    }

    // Adiciona listener para executar toggleCampos ao mudar o status
    statusSelect.addEventListener('change', toggleCampos);
    // Adiciona listener para executar toggleCampos ao mudar o tipo
    tipoSelect.addEventListener('change', toggleCampos);
    // Executa imediatamente ao carregar para refletir estado inicial do formulário
    toggleCampos();
</script>
@endpush {{-- fim do bloco de scripts empilhados --}}

@endsection {{-- fim da seção de conteúdo principal --}}
