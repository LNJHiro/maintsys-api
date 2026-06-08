{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador para esta página --}}
@section('title', 'Nova O.S.')
{{-- Define o breadcrumb de navegação com link para a listagem --}}
@section('breadcrumb')
    {{-- Link clicável que retorna à listagem de ordens --}}
    <a href="{{ route('ordens.index') }}" style="color:var(--muted);text-decoration:none">ordens</a>
    {{-- Separador visual entre os itens do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Item atual do breadcrumb indicando que está na criação --}}
    <span>nova</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com título e botão de voltar --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo decorativo --}}
    <div class="page-title">
        {{-- Subtítulo em estilo monospace indicando abertura de ordem --}}
        <small>// abertura de ordem</small>
        {{-- Título principal da página --}}
        Nova Ordem de Serviço
    </div>
    {{-- Botão de retorno para a listagem de ordens --}}
    <a href="{{ route('ordens.index') }}" class="btn btn-secondary">← Voltar</a>
</div>

{{-- Card branco que envolve o formulário de criação --}}
<div class="form-card">
    {{-- Formulário enviado via POST para a rota de armazenamento da OS --}}
    <form method="POST" action="{{ route('ordens.store') }}">
        {{-- Token CSRF obrigatório para proteção contra falsificação de requisição --}}
        @csrf

        {{-- Linha de formulário com dois campos lado a lado --}}
        <div class="form-row">
            {{-- Grupo do campo de seleção de máquina --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de máquina --}}
                <label>Máquina *</label>
                {{-- Select obrigatório para vincular a OS a uma máquina cadastrada --}}
                <select name="maquina_id" class="form-control" required>
                    {{-- Opção vazia padrão para forçar seleção --}}
                    <option value="">— Selecione a máquina —</option>
                    {{-- Itera sobre todas as máquinas cadastradas para gerar as opções --}}
                    @foreach($maquinas as $m)
                    {{-- Opção de máquina; mantém selecionada se houve erro de validação ou query string --}}
                    <option value="{{ $m->id }}"
                        {{ (old('maquina_id', request('maquina_id')) == $m->id) ? 'selected' : '' }}>
                        {{-- Exibe modelo, número de série e localização da máquina --}}
                        {{ $m->modelo }} — {{ $m->numero_serie }} [{{ $m->localizacao }}]
                    </option>
                    @endforeach {{-- fim da iteração sobre as máquinas --}}
                </select>
                {{-- Exibe mensagem de erro de validação para o campo maquina_id --}}
                @error('maquina_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo de seleção do técnico responsável --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de técnico --}}
                <label>Técnico Responsável *</label>
                {{-- Select obrigatório para vincular a OS a um técnico --}}
                <select name="tecnico_id" class="form-control" required>
                    {{-- Opção vazia padrão para forçar seleção --}}
                    <option value="">— Selecione o técnico —</option>
                    {{-- Itera sobre todos os técnicos cadastrados --}}
                    @foreach($tecnicos as $t)
                    {{-- Opção de técnico; mantém selecionada em caso de erro de validação --}}
                    <option value="{{ $t->id }}" {{ old('tecnico_id') == $t->id ? 'selected' : '' }}>
                        {{-- Exibe nome e especialidade do técnico; "Geral" se não houver especialidade --}}
                        {{ $t->nome }} — {{ $t->especialidade ?? 'Geral' }}
                    </option>
                    @endforeach {{-- fim da iteração sobre os técnicos --}}
                </select>
                {{-- Exibe mensagem de erro de validação para o campo tecnico_id --}}
                @error('tecnico_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Segunda linha de formulário com tipo e prioridade --}}
        <div class="form-row">
            {{-- Grupo do campo de seleção do tipo de manutenção --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de tipo --}}
                <label>Tipo de Manutenção *</label>
                {{-- Select obrigatório para definir se a OS é preventiva ou corretiva --}}
                <select name="tipo" class="form-control" required>
                    {{-- Opção vazia padrão --}}
                    <option value="">— Selecione —</option>
                    {{-- Opção preventiva; mantém selecionada em revalidação --}}
                    <option value="preventiva" {{ old('tipo')=='preventiva'?'selected':'' }}>Preventiva</option>
                    {{-- Opção corretiva; mantém selecionada em revalidação --}}
                    <option value="corretiva"  {{ old('tipo')=='corretiva'?'selected':'' }}>Corretiva</option>
                </select>
                {{-- Exibe mensagem de erro de validação para o campo tipo --}}
                @error('tipo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo de seleção da prioridade --}}
            <div class="form-group">
                {{-- Rótulo do campo obrigatório de prioridade --}}
                <label>Prioridade *</label>
                {{-- Select obrigatório para definir a urgência da OS --}}
                <select name="prioridade" class="form-control" required>
                    {{-- Opção baixa; mantém selecionada em revalidação --}}
                    <option value="baixa"  {{ old('prioridade')=='baixa'?'selected':'' }}>Baixa</option>
                    {{-- Opção média; pré-selecionada por padrão --}}
                    <option value="media"  {{ old('prioridade','media')=='media'?'selected':'' }}>Média</option>
                    {{-- Opção alta; mantém selecionada em revalidação --}}
                    <option value="alta"   {{ old('prioridade')=='alta'?'selected':'' }}>Alta</option>
                    {{-- Opção crítica com ícone de alerta --}}
                    <option value="critica"{{ old('prioridade')=='critica'?'selected':'' }}>🚨 Crítica</option>
                </select>
            </div>
        </div>

        {{-- Grupo do campo de descrição do problema ou serviço --}}
        <div class="form-group">
            {{-- Rótulo do campo obrigatório de descrição --}}
            <label>Descrição do Problema / Serviço *</label>
            {{-- Textarea para inserção do detalhamento do problema ou serviço --}}
            <textarea name="descricao" class="form-control" rows="4"
                      placeholder="Descreva detalhadamente o problema identificado ou o serviço a ser executado..."
                      required>{{ old('descricao') }}</textarea>
            {{-- Exibe mensagem de erro de validação para o campo descricao --}}
            @error('descricao')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Grupo do campo opcional de data prevista para conclusão --}}
        <div class="form-group">
            {{-- Rótulo do campo opcional de data prevista --}}
            <label>Data Prevista para Conclusão</label>
            {{-- Input de data limitado a largura de 200px; mantém valor em revalidação --}}
            <input type="date" name="data_prevista" class="form-control"
                   value="{{ old('data_prevista') }}" style="max-width:200px">
        </div>

        {{-- Container dos botões de submissão e cancelamento --}}
        <div style="display:flex;gap:10px;margin-top:8px">
            {{-- Botão de envio do formulário para criar a OS --}}
            <button type="submit" class="btn btn-primary">Abrir Ordem de Serviço</button>
            {{-- Link de cancelamento que retorna à listagem sem salvar --}}
            <a href="{{ route('ordens.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@endsection {{-- fim da seção de conteúdo principal --}}
