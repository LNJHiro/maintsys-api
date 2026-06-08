{{--
    VIEW: tecnicos/create.blade.php
    ROTA:    GET  /tecnicos/create  →  TecnicoController::create()
    SUBMETE: POST /tecnicos         →  TecnicoController::store()
    SEÇÕES:
      Linha 20 — Formulário principal
      Linha 24 — Campo: Nome Completo
      Linha 31 — Campo: Matrícula
      Linha 39 — Campo: E-mail
      Linha 46 — Campo: Especialidade
      Linha 53 — Campo: Senha
      Linha 60 — Campo: Confirmar Senha
      Linha 67 — Campo: Telefone (com máscara JS no final do arquivo)
      Linha 73 — Checkbox: Técnico Ativo
      Linha 84 — Botões: Cadastrar / Cancelar
      Linha 91 — Script JS de máscara de telefone
--}}
{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página como "Novo Técnico" --}}
@section('title', 'Novo Técnico')
{{-- Preenche o breadcrumb com link para o índice e o nó atual "novo" --}}
@section('breadcrumb')
    {{-- Link clicável para voltar à listagem de técnicos --}}
    <a href="{{ route('tecnicos.index') }}" style="color:var(--muted);text-decoration:none">técnicos</a>
    {{-- Separador visual entre os níveis do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Nó atual do breadcrumb indicando criação --}}
    <span>novo</span>
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
        Novo Técnico
    </div>
    {{-- Botão para voltar à listagem de técnicos sem salvar --}}
    <a href="{{ route('tecnicos.index') }}" class="btn btn-secondary">← Voltar</a>
</div> {{-- fim do page-header --}}

{{-- Container do formulário de cadastro --}}
<div class="form-card">
    {{-- Formulário enviado via POST para a rota de criação de técnicos --}}
    <form method="POST" action="{{ route('tecnicos.store') }}">
        {{-- Token CSRF obrigatório para segurança contra ataques de formulário --}}
        @csrf

        {{-- Primeira linha do formulário: Nome e Matrícula --}}
        <div class="form-row">
            {{-- Grupo do campo Nome Completo --}}
            <div class="form-group">
                {{-- Rótulo do campo nome, asterisco indica obrigatoriedade --}}
                <label>Nome Completo *</label>
                {{-- Campo de texto para o nome do técnico; mantém valor anterior em caso de erro --}}
                <input type="text" name="nome" class="form-control"
                       value="{{ old('nome') }}" placeholder="ex: João da Silva" required>
                {{-- Exibe mensagem de erro de validação para o campo nome --}}
                @error('nome')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo Matrícula --}}
            <div class="form-group">
                {{-- Rótulo do campo matrícula, asterisco indica obrigatoriedade --}}
                <label>Matrícula *</label>
                {{-- Campo de texto para a matrícula; mantém valor anterior em caso de erro --}}
                <input type="text" name="matricula" class="form-control"
                       value="{{ old('matricula') }}" placeholder="ex: TEC-001" required>
                {{-- Exibe mensagem de erro de validação para o campo matrícula --}}
                @error('matricula')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div> {{-- fim da primeira linha do formulário --}}

        {{-- Segunda linha do formulário: E-mail e Especialidade --}}
        <div class="form-row">
            {{-- Grupo do campo E-mail --}}
            <div class="form-group">
                {{-- Rótulo do campo e-mail, asterisco indica obrigatoriedade --}}
                <label>E-mail *</label>
                {{-- Campo de e-mail com validação de formato pelo browser --}}
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" placeholder="tecnico@empresa.com" required>
                {{-- Exibe mensagem de erro de validação para o campo e-mail --}}
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo Especialidade (opcional) --}}
            <div class="form-group">
                {{-- Rótulo do campo especialidade, sem asterisco pois é opcional --}}
                <label>Especialidade</label>
                {{-- Campo de texto para área de atuação do técnico --}}
                <input type="text" name="especialidade" class="form-control"
                       value="{{ old('especialidade') }}" placeholder="ex: Elétrica, Hidráulica, Mecânica">
            </div>
        </div> {{-- fim da segunda linha do formulário --}}

        {{-- Terceira linha do formulário: Senha e Confirmação de Senha --}}
        <div class="form-row">
            {{-- Grupo do campo Senha --}}
            <div class="form-group">
                {{-- Rótulo do campo senha, asterisco indica obrigatoriedade --}}
                <label>Senha *</label>
                {{-- Campo de senha com mínimo de 8 caracteres --}}
                <input type="password" name="password" class="form-control"
                       placeholder="Mínimo 8 caracteres" required>
                {{-- Exibe mensagem de erro de validação para o campo senha --}}
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            {{-- Grupo do campo Confirmação de Senha --}}
            <div class="form-group">
                {{-- Rótulo do campo de confirmação de senha --}}
                <label>Confirmar Senha *</label>
                {{-- Campo para repetir a senha; Laravel valida se bate com o campo password --}}
                <input type="password" name="password_confirmation" class="form-control"
                       placeholder="Repita a senha" required>
            </div>
        </div> {{-- fim da terceira linha do formulário --}}

        {{-- Quarta linha do formulário: Telefone e Checkbox Ativo --}}
        <div class="form-row">
            {{-- Grupo do campo Telefone com máscara JS --}}
            <div class="form-group">
                {{-- Rótulo do campo telefone, sem asterisco pois é opcional --}}
                <label>Telefone</label>
                {{-- Campo de telefone; id="telefone" é usado pelo script JS de máscara --}}
                <input type="text" name="telefone" id="telefone" class="form-control"
                       value="{{ old('telefone') }}" placeholder="(19) 99999-9999">
            </div>
            {{-- Grupo do checkbox de status ativo, alinhado verticalmente ao centro --}}
            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px">
                {{-- Campo oculto garante envio do valor "0" quando o checkbox não está marcado --}}
                <input type="hidden" name="ativo" value="0">
                {{-- Checkbox: se marcado envia "1" (ativo); vem marcado por padrão em novos cadastros --}}
                <input type="checkbox" name="ativo" value="1" id="ativo"
                       {{ old('ativo', '1') ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--accent)">
                {{-- Rótulo clicável do checkbox "Técnico Ativo" --}}
                <label for="ativo" style="font-family:var(--cond);font-size:14px;color:var(--text);letter-spacing:0">
                    Técnico Ativo
                </label>
            </div>
        </div> {{-- fim da quarta linha do formulário --}}

        {{-- Barra de botões de ação do formulário --}}
        <div style="display:flex;gap:10px;margin-top:8px">
            {{-- Botão principal para enviar o formulário e cadastrar o técnico --}}
            <button type="submit" class="btn btn-primary">Cadastrar Técnico</button>
            {{-- Link para cancelar e voltar à listagem sem salvar --}}
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
            {{-- Atualiza o valor do campo com a máscara aplicada --}}
            e.target.value = v;
        });
    }
});
</script>
@endpush {{-- fim do bloco de scripts --}}

@endsection {{-- fim da seção content --}}
