{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página como "Editar Técnico" --}}
@section('title', 'Editar Técnico')
{{-- Preenche o breadcrumb com link para o índice e o nó atual "editar" --}}
@section('breadcrumb')
    {{-- Link clicável para voltar à listagem de técnicos --}}
    <a href="{{ route('tecnicos.index') }}" style="color:var(--muted);text-decoration:none">técnicos</a>
    {{-- Separador visual entre os níveis do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Nó atual indicando edição --}}
    <span>editar</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal --}}
@section('content')

{{-- Cabeçalho da página com nome do técnico e botão de retorno --}}
<div class="page-header">
    {{-- Bloco do título mostrando contexto e nome do técnico sendo editado --}}
    <div class="page-title">
        {{-- Subtítulo indicando que é uma tela de edição --}}
        <small>// edição</small>
        {{-- Nome do técnico sendo editado como título principal --}}
        {{ $tecnico->nome }}
    </div>
    {{-- Botão para voltar à listagem sem salvar alterações --}}
    <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary">← Voltar</a>
</div> {{-- fim do page-header --}}

{{-- Container do formulário de edição --}}
<div class="form-card">
    {{-- Formulário enviado via POST com spoofing para PUT (atualização) --}}
    <form method="POST" action="{{ route('tecnicos.update', $tecnico) }}">
        {{-- Token CSRF obrigatório para segurança --}}
        @csrf
        {{-- Spoofing do método HTTP para PUT, exigido pelo Laravel para updates --}}
        @method('PUT')

        {{-- Primeira linha: Nome Completo e Matrícula --}}
        <div class="form-row">
            {{-- Grupo do campo Nome Completo --}}
            <div class="form-group">
                {{-- Rótulo do campo nome, asterisco indica obrigatoriedade --}}
                <label>Nome Completo *</label>
                {{-- Campo pré-preenchido com o valor atual; mantém digitação anterior em erro --}}
                <input type="text" name="nome" class="form-control"
                       value="{{ old('nome', $tecnico->nome) }}" required>
                {{-- Exibe mensagem de erro de validação para o campo nome --}}
                @error('nome')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo Matrícula --}}
            <div class="form-group">
                {{-- Rótulo do campo matrícula, asterisco indica obrigatoriedade --}}
                <label>Matrícula *</label>
                {{-- Campo pré-preenchido com a matrícula atual do técnico --}}
                <input type="text" name="matricula" class="form-control"
                       value="{{ old('matricula', $tecnico->matricula) }}" required>
                {{-- Exibe mensagem de erro de validação para o campo matrícula --}}
                @error('matricula')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div> {{-- fim da primeira linha --}}

        {{-- Segunda linha: E-mail e Especialidade --}}
        <div class="form-row">
            {{-- Grupo do campo E-mail --}}
            <div class="form-group">
                {{-- Rótulo do campo e-mail, asterisco indica obrigatoriedade --}}
                <label>E-mail *</label>
                {{-- Campo pré-preenchido com o e-mail atual do técnico --}}
                <input type="email" name="email" class="form-control"
                       value="{{ old('email', $tecnico->email) }}" required>
                {{-- Exibe mensagem de erro de validação para o campo e-mail --}}
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo Especialidade (opcional) --}}
            <div class="form-group">
                {{-- Rótulo do campo especialidade, sem asterisco pois é opcional --}}
                <label>Especialidade</label>
                {{-- Campo pré-preenchido com a especialidade atual; vazio se não cadastrada --}}
                <input type="text" name="especialidade" class="form-control"
                       value="{{ old('especialidade', $tecnico->especialidade) }}">
            </div>
        </div> {{-- fim da segunda linha --}}

        {{-- Bloco destacado de redefinição de senha (opcional na edição) --}}
        <div style="background:rgba(240,165,0,.05);border:1px solid rgba(240,165,0,.2);padding:12px 16px;margin-bottom:18px">
            {{-- Aviso informativo: campos de senha são opcionais na edição --}}
            <div style="font-family:var(--mono);font-size:10px;color:var(--accent);margin-bottom:10px;letter-spacing:1px">
                // REDEFINIR SENHA — deixe em branco para manter a atual
            </div>
            {{-- Linha com campos de nova senha e confirmação --}}
            <div class="form-row">
                {{-- Grupo do campo Nova Senha --}}
                <div class="form-group" style="margin-bottom:0">
                    {{-- Rótulo do campo nova senha (sem asterisco, é opcional) --}}
                    <label>Nova Senha</label>
                    {{-- Campo de senha; se deixado em branco, a senha atual é mantida --}}
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres">
                    {{-- Exibe mensagem de erro de validação para o campo senha --}}
                    @error('password')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                {{-- Grupo do campo Confirmar Nova Senha --}}
                <div class="form-group" style="margin-bottom:0">
                    {{-- Rótulo do campo de confirmação de nova senha --}}
                    <label>Confirmar Nova Senha</label>
                    {{-- Campo para repetir a nova senha; Laravel valida a correspondência --}}
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div> {{-- fim da linha de campos de senha --}}
        </div> {{-- fim do bloco de redefinição de senha --}}

        {{-- Terceira linha: Telefone e Checkbox Ativo --}}
        <div class="form-row">
            {{-- Grupo do campo Telefone --}}
            <div class="form-group">
                {{-- Rótulo do campo telefone, sem asterisco pois é opcional --}}
                <label>Telefone</label>
                {{-- Campo pré-preenchido com telefone atual; id="telefone" usado pelo script JS --}}
                <input type="text" name="telefone" id="telefone" class="form-control"
                       value="{{ old('telefone', $tecnico->telefone) }}">
            </div>
            {{-- Grupo do checkbox de status ativo --}}
            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px">
                {{-- Campo oculto garante envio de "0" quando o checkbox não está marcado --}}
                <input type="hidden" name="ativo" value="0">
                {{-- Checkbox pré-marcado conforme o status atual do técnico --}}
                <input type="checkbox" name="ativo" value="1" id="ativo"
                       {{ old('ativo', $tecnico->ativo) ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--accent)">
                {{-- Rótulo clicável para o checkbox --}}
                <label for="ativo" style="font-family:var(--cond);font-size:14px;color:var(--text);letter-spacing:0">
                    Técnico Ativo
                </label>
            </div>
        </div> {{-- fim da terceira linha --}}

        {{-- Barra de botões de ação do formulário --}}
        <div style="display:flex;gap:10px;margin-top:8px">
            {{-- Botão para confirmar e salvar as alterações --}}
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            {{-- Link para cancelar e voltar sem salvar --}}
            <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div> {{-- fim do form-card --}}

{{-- Injeta o script JS na seção "scripts" do layout --}}
@push('scripts')
<script>
{{-- Aguarda o carregamento completo do DOM antes de inicializar a máscara --}}
document.addEventListener('DOMContentLoaded', function() {
    {{-- Obtém a referência ao campo de telefone pelo id --}}
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        {{-- Escuta o evento de digitação para aplicar a máscara em tempo real --}}
        telefoneInput.addEventListener('input', function(e) {
            {{-- Remove não-dígitos e limita a 11 caracteres numéricos --}}
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 10) {
                {{-- Formato celular: (DDD) 9XXXX-XXXX --}}
                v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length > 6) {
                {{-- Formato fixo: (DDD) XXXX-XXXX --}}
                v = v.replace(/(\d{2})(\d{4})(\d*)/, '($1) $2-$3');
            } else if (v.length > 2) {
                {{-- Formato parcial: (DDD) ... --}}
                v = v.replace(/(\d{2})(\d*)/, '($1) $2');
            }
            {{-- Atualiza o campo com a máscara aplicada --}}
            e.target.value = v;
        });
    }
});
</script>
@endpush {{-- fim do bloco de scripts --}}

@endsection {{-- fim da seção content --}}
