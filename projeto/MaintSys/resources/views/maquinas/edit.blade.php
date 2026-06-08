{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página como "Editar Máquina" --}}
@section('title', 'Editar Máquina')
{{-- Preenche o breadcrumb com link para o índice e o nó atual "editar" --}}
@section('breadcrumb')
    {{-- Link clicável para voltar à listagem de máquinas --}}
    <a href="{{ route('maquinas.index') }}" style="color:var(--muted);text-decoration:none">máquinas</a>
    {{-- Separador visual entre os níveis do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Nó atual indicando edição --}}
    <span>editar</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal --}}
@section('content')

{{-- Cabeçalho da página com modelo da máquina e botão de retorno --}}
<div class="page-header">
    {{-- Bloco do título mostrando o contexto e nome da máquina sendo editada --}}
    <div class="page-title">
        {{-- Subtítulo indicando que é uma tela de edição --}}
        <small>// edição</small>
        {{-- Modelo da máquina sendo editada como título principal --}}
        {{ $maquina->modelo }}
    </div>
    {{-- Botão para voltar à listagem sem salvar alterações --}}
    <a href="{{ route('maquinas.index') }}" class="btn btn-secondary">← Voltar</a>
</div> {{-- fim do page-header --}}

{{-- Container do formulário de edição --}}
<div class="form-card">
    {{-- Formulário enviado via POST com spoofing para PUT (atualização) --}}
    <form method="POST" action="{{ route('maquinas.update', $maquina) }}">
        {{-- Token CSRF obrigatório para segurança --}}
        @csrf
        {{-- Spoofing do método HTTP para PUT, exigido pelo Laravel para updates --}}
        @method('PUT')

        {{-- Primeira linha: Número de Série e Modelo --}}
        <div class="form-row">
            {{-- Grupo do campo Número de Série --}}
            <div class="form-group">
                {{-- Rótulo do campo número de série, asterisco indica obrigatoriedade --}}
                <label>Número de Série *</label>
                {{-- Campo pré-preenchido com o número de série atual; mantém valor em caso de erro --}}
                <input type="text" name="numero_serie" class="form-control"
                       value="{{ old('numero_serie', $maquina->numero_serie) }}" required>
                {{-- Exibe mensagem de erro de validação para o campo número de série --}}
                @error('numero_serie')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo Modelo --}}
            <div class="form-group">
                {{-- Rótulo do campo modelo, asterisco indica obrigatoriedade --}}
                <label>Modelo *</label>
                {{-- Campo pré-preenchido com o modelo atual da máquina --}}
                <input type="text" name="modelo" class="form-control"
                       value="{{ old('modelo', $maquina->modelo) }}" required>
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
                {{-- Campo pré-preenchido com o fabricante atual --}}
                <input type="text" name="fabricante" class="form-control"
                       value="{{ old('fabricante', $maquina->fabricante) }}">
            </div>
            {{-- Grupo do campo Localização --}}
            <div class="form-group">
                {{-- Rótulo do campo localização, asterisco indica obrigatoriedade --}}
                <label>Localização no Galpão *</label>
                {{-- Campo pré-preenchido com a localização atual da máquina --}}
                <input type="text" name="localizacao" class="form-control"
                       value="{{ old('localizacao', $maquina->localizacao) }}" required>
                {{-- Exibe mensagem de erro de validação para o campo localização --}}
                @error('localizacao')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div> {{-- fim da segunda linha --}}

        {{-- Terceira linha: Data de Cadastro e Status --}}
        <div class="form-row">
            {{-- Grupo do campo Data de Cadastro --}}
            <div class="form-group">
                {{-- Rótulo do campo data de cadastro, sem asterisco pois é opcional --}}
                <label>Data de Cadastro</label>
                {{-- Campo pré-preenchido com a data atual; operador nullsafe evita erro se null --}}
                <input type="date" name="data_cadastro" class="form-control"
                       value="{{ old('data_cadastro', $maquina->data_cadastro?->format('Y-m-d')) }}">
            </div>
            {{-- Grupo do campo Status --}}
            <div class="form-group">
                {{-- Rótulo do campo status, asterisco indica obrigatoriedade --}}
                <label>Status *</label>
                {{-- Select pré-selecionado com o status atual da máquina --}}
                <select name="status" class="form-control" required>
                    {{-- Opção "Operacional": selecionada se status atual ou anterior for "operacional" --}}
                    <option value="operacional"    {{ old('status',$maquina->status)=='operacional'?'selected':'' }}>Operacional</option>
                    {{-- Opção "Em Manutenção": selecionada se status for "em_manutencao" --}}
                    <option value="em_manutencao"  {{ old('status',$maquina->status)=='em_manutencao'?'selected':'' }}>Em Manutenção</option>
                    {{-- Opção "Parada Crítica": selecionada se status for "parada_critica" --}}
                    <option value="parada_critica" {{ old('status',$maquina->status)=='parada_critica'?'selected':'' }}>Parada Crítica</option>
                    {{-- Opção "Inativa": selecionada se status for "inativa" --}}
                    <option value="inativa"        {{ old('status',$maquina->status)=='inativa'?'selected':'' }}>Inativa</option>
                </select>
            </div>
        </div> {{-- fim da terceira linha --}}

        {{-- Campo de texto longo para descrição ou observações sobre o equipamento --}}
        <div class="form-group">
            {{-- Rótulo do campo descrição, sem asterisco pois é opcional --}}
            <label>Descrição / Observações</label>
            {{-- Área de texto pré-preenchida com a descrição atual da máquina --}}
            <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $maquina->descricao) }}</textarea>
        </div>

        {{-- Barra de botões de ação do formulário --}}
        <div style="display:flex;gap:10px;margin-top:8px">
            {{-- Botão para confirmar e salvar as alterações --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            {{-- Link para cancelar e voltar à tela de detalhes da máquina --}}
            <a href="{{ route('maquinas.show', $maquina) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div> {{-- fim do form-card --}}

@endsection {{-- fim da seção content --}}
