{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página como "Nova Máquina" --}}
@section('title', 'Nova Máquina')
{{-- Preenche o breadcrumb com link para o índice e o nó atual "nova" --}}
@section('breadcrumb')
    {{-- Link clicável para voltar à listagem de máquinas --}}
    <a href="{{ route('maquinas.index') }}" style="color:var(--muted);text-decoration:none">máquinas</a>
    {{-- Separador visual entre os níveis do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Nó atual indicando criação de nova máquina --}}
    <span>nova</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal --}}
@section('content')

{{-- Cabeçalho da página com título e botão de retorno --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo de contexto --}}
    <div class="page-title">
        {{-- Subtítulo indicando que é uma tela de cadastro --}}
        <small>// cadastro</small>
        {{-- Título principal da página --}}
        Nova Máquina
    </div>
    {{-- Botão para voltar à listagem sem salvar --}}
    <a href="{{ route('maquinas.index') }}" class="btn btn-secondary">← Voltar</a>
</div> {{-- fim do page-header --}}

{{-- Container do formulário de cadastro --}}
<div class="form-card">
    {{-- Formulário enviado via POST para a rota de criação de máquinas --}}
    <form method="POST" action="{{ route('maquinas.store') }}">
        {{-- Token CSRF obrigatório para segurança contra ataques de formulário --}}
        @csrf

        {{-- Primeira linha: Número de Série e Modelo --}}
        <div class="form-row">
            {{-- Grupo do campo Número de Série --}}
            <div class="form-group">
                {{-- Rótulo do campo número de série, asterisco indica obrigatoriedade --}}
                <label>Número de Série *</label>
                {{-- Campo de texto para o número de série; mantém valor anterior em caso de erro --}}
                <input type="text" name="numero_serie" class="form-control"
                       value="{{ old('numero_serie') }}" placeholder="ex: SN-2024-001" required>
                {{-- Exibe mensagem de erro de validação para o campo número de série --}}
                @error('numero_serie')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo Modelo --}}
            <div class="form-group">
                {{-- Rótulo do campo modelo, asterisco indica obrigatoriedade --}}
                <label>Modelo *</label>
                {{-- Campo de texto para o modelo do equipamento --}}
                <input type="text" name="modelo" class="form-control"
                       value="{{ old('modelo') }}" placeholder="ex: Torno CNC TL-500" required>
                {{-- Exibe mensagem de erro de validação para o campo modelo --}}
                @error('modelo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div> {{-- fim da primeira linha --}}

        {{-- Segunda linha: Fabricante e Localização --}}
        <div class="form-row">
            {{-- Grupo do campo Fabricante (opcional) --}}
            <div class="form-group">
                {{-- Rótulo do campo fabricante, sem asterisco pois é opcional --}}
                <label>Fabricante</label>
                {{-- Campo de texto para o fabricante do equipamento --}}
                <input type="text" name="fabricante" class="form-control"
                       value="{{ old('fabricante') }}" placeholder="ex: Romi, Tornos...">
            </div>
            {{-- Grupo do campo Localização --}}
            <div class="form-group">
                {{-- Rótulo do campo localização, asterisco indica obrigatoriedade --}}
                <label>Localização no Galpão *</label>
                {{-- Campo de texto para a posição física da máquina na planta --}}
                <input type="text" name="localizacao" class="form-control"
                       value="{{ old('localizacao') }}" placeholder="ex: Galpão A — Setor 3" required>
                {{-- Exibe mensagem de erro de validação para o campo localização --}}
                @error('localizacao')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div> {{-- fim da segunda linha --}}

        {{-- Terceira linha: Data de Cadastro e Status --}}
        <div class="form-row">
            {{-- Grupo do campo Data de Cadastro (opcional) --}}
            <div class="form-group">
                {{-- Rótulo do campo data de cadastro, sem asterisco pois é opcional --}}
                <label>Data de Cadastro</label>
                {{-- Campo de data para registrar quando a máquina foi incluída no sistema --}}
                <input type="date" name="data_cadastro" class="form-control"
                       value="{{ old('data_cadastro') }}">
            </div>
            {{-- Grupo do campo Status --}}
            <div class="form-group">
                {{-- Rótulo do campo status, asterisco indica obrigatoriedade --}}
                <label>Status *</label>
                {{-- Select com as opções de status da máquina --}}
                <select name="status" class="form-control" required>
                    {{-- Opção padrão "Operacional", pré-selecionada em novos cadastros --}}
                    <option value="operacional"    {{ old('status','operacional')=='operacional'?'selected':'' }}>Operacional</option>
                    {{-- Opção para máquina em processo de manutenção --}}
                    <option value="em_manutencao"  {{ old('status')=='em_manutencao'?'selected':'' }}>Em Manutenção</option>
                    {{-- Opção para máquina em parada crítica (emergência) --}}
                    <option value="parada_critica" {{ old('status')=='parada_critica'?'selected':'' }}>Parada Crítica</option>
                    {{-- Opção para máquina desativada ou fora de uso --}}
                    <option value="inativa"        {{ old('status')=='inativa'?'selected':'' }}>Inativa</option>
                </select>
            </div>
        </div> {{-- fim da terceira linha --}}

        {{-- Campo de texto longo para descrição ou observações sobre o equipamento --}}
        <div class="form-group">
            {{-- Rótulo do campo descrição, sem asterisco pois é opcional --}}
            <label>Descrição / Observações</label>
            {{-- Área de texto com 3 linhas para informações adicionais --}}
            <textarea name="descricao" class="form-control" rows="3"
                      placeholder="Informações adicionais sobre o equipamento...">{{ old('descricao') }}</textarea>
        </div>

        {{-- Barra de botões de ação do formulário --}}
        <div style="display:flex;gap:10px;margin-top:8px">
            {{-- Botão principal para enviar o formulário e cadastrar a máquina --}}
            <button type="submit" class="btn btn-primary">Cadastrar Máquina</button>
            {{-- Link para cancelar e voltar à listagem sem salvar --}}
            <a href="{{ route('maquinas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div> {{-- fim do form-card --}}

@endsection {{-- fim da seção content --}}
