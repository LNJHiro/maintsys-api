{{--
    VIEW: tecnicos/index.blade.php
    ROTA:    GET /tecnicos  →  TecnicoController::index()
    DADOS:   $tecnicos (paginado, com ordens_count)
    SEÇÕES:
      Linha 16 — Cabeçalho da página + botão "Novo Técnico"
      Linha 21 — Tabela de listagem de técnicos
      Linha 36 — Loop @forelse: cada linha da tabela
      Linha 44 — Coluna telefone com formatação automática
      Linha 59 — Coluna de status (badge verde/cinza)
      Linha 65 — Coluna de ações: Ver, Editar, Deletar
      Linha 88 — Paginação
--}}
{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página como "Técnicos" --}}
@section('title', 'Técnicos')

{{-- Preenche o breadcrumb com o texto "técnicos" --}}
@section('breadcrumb')
    <span>técnicos</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal --}}
@section('content')

{{-- CABEÇALHO: título da página + botão "Novo Técnico" (visível só com permissão tecnicos.criar) --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo decorativo --}}
    <div class="page-title">
        {{-- Subtítulo descritivo da seção --}}
        <small>// equipe de manutenção</small>
        {{-- Título principal da página --}}
        Técnicos
    </div>
    {{-- Exibe o botão somente se o usuário tiver permissão para criar técnicos --}}
    @if(auth()->user()->hasPermission('tecnicos.criar'))
    {{-- Link/botão para o formulário de criação de novo técnico --}}
    <a href="{{ route('tecnicos.create') }}" class="btn btn-primary">+ Novo Técnico</a>
    @endif {{-- fim do bloco de permissão tecnicos.criar --}}
</div> {{-- fim do page-header --}}

{{-- TABELA: lista todos os técnicos cadastrados --}}
<div class="table-wrap">
    <table>
        {{-- Cabeçalho da tabela com os nomes das colunas --}}
        <thead>
            <tr>
                <th>#</th>
                <th>Matrícula</th>
                <th>Nome</th>
                <th>Especialidade</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>O.S.</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            {{-- Itera sobre os técnicos paginados; exibe mensagem se a lista estiver vazia --}}
            @forelse($tecnicos as $t)
            <tr>
                {{-- ID interno do técnico, em fonte monoespaçada e cor discreta --}}
                <td class="mono" style="color:var(--muted);font-size:11px">{{ $t->id }}</td>
                {{-- Matrícula do técnico, destacada com cor de acento --}}
                <td class="mono" style="color:var(--accent);font-size:11px">{{ $t->matricula }}</td>
                {{-- Nome completo do técnico em negrito --}}
                <td style="font-weight:500">{{ $t->nome }}</td>
                {{-- Especialidade do técnico; exibe "—" se não cadastrada --}}
                <td style="color:var(--muted)">{{ $t->especialidade ?? '—' }}</td>
                {{-- E-mail do técnico em fonte menor e cor discreta --}}
                <td style="color:var(--muted);font-size:12px">{{ $t->email }}</td>

                {{-- Formata telefone: (99) 99999-9999 ou (99) 9999-9999 --}}
                <td class="mono" style="font-size:11px;color:var(--muted)">
                    {{-- Verifica se o telefone está preenchido antes de formatar --}}
                    @if($t->telefone)
                        @php
                            // Remove todos os caracteres não numéricos do telefone
                            $tel = preg_replace('/\D/', '', $t->telefone);
                            // Formata como celular (11 dígitos) ou fixo (10 dígitos)
                            $formatted = strlen($tel) === 11
                                ? '('.substr($tel,0,2).') '.substr($tel,2,5).'-'.substr($tel,7)
                                : (strlen($tel) === 10
                                    ? '('.substr($tel,0,2).') '.substr($tel,2,4).'-'.substr($tel,6)
                                    : $t->telefone); // mantém o valor original se não encaixar nos padrões
                        @endphp
                        {{-- Exibe o telefone já formatado --}}
                        {{ $formatted }}
                    @else
                        {{-- Exibe traço quando o técnico não tem telefone cadastrado --}}
                        —
                    @endif {{-- fim da verificação de telefone --}}
                </td>

                {{-- ordens_count vem de withCount('ordens') no controller --}}
                {{-- Exibe a quantidade de OS vinculadas ao técnico --}}
                <td class="mono" style="text-align:center">{{ $t->ordens_count }}</td>

                {{-- Badge verde = ativo, cinza = inativo --}}
                <td>
                    {{-- Aplica classe badge-green se ativo, badge-gray se inativo --}}
                    <span class="badge {{ $t->ativo ? 'badge-green' : 'badge-gray' }}">
                        {{-- Exibe o texto correspondente ao status do técnico --}}
                        {{ $t->ativo ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>

                {{-- Ações: Ver (sempre), Editar (permissão), Deletar (permissão) --}}
                <td>
                    <div class="actions">
                        {{-- Botão sempre visível para ver os detalhes do técnico --}}
                        <a href="{{ route('tecnicos.show', $t) }}" class="btn btn-secondary btn-sm">Ver</a>
                        {{-- Botão de edição visível somente com permissão tecnicos.editar --}}
                        @if(auth()->user()->hasPermission('tecnicos.editar'))
                        {{-- Link para o formulário de edição do técnico --}}
                        <a href="{{ route('tecnicos.edit', $t) }}" class="btn btn-secondary btn-sm">Editar</a>
                        @endif {{-- fim do bloco de permissão tecnicos.editar --}}
                        {{-- Formulário de exclusão visível somente com permissão tecnicos.deletar --}}
                        @if(auth()->user()->hasPermission('tecnicos.deletar'))
                        {{-- Formulário que envia DELETE via POST com spoofing de método --}}
                        <form method="POST" action="{{ route('tecnicos.destroy', $t) }}"
                              onsubmit="confirmDelete(this, 'Excluir o técnico {{ $t->nome }}?'); return false;">
                            {{-- Token CSRF para segurança + spoofing do método DELETE --}}
                            @csrf @method('DELETE')
                            {{-- Botão de exclusão com estilo de perigo --}}
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                        @endif {{-- fim do bloco de permissão tecnicos.deletar --}}
                    </div>
                </td>
            </tr>
            {{-- Fallback: exibido quando não há técnicos cadastrados --}}
            @empty
            <tr>
                {{-- Célula única cobrindo todas as colunas com mensagem de lista vazia --}}
                <td colspan="9" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    — nenhum técnico cadastrado —
                </td>
            </tr>
            @endforelse {{-- fim do loop de técnicos --}}
        </tbody>
    </table>
    {{-- Renderiza os links de paginação gerados pelo Laravel --}}
    <div class="pagination">{{ $tecnicos->links() }}</div>
</div> {{-- fim do table-wrap --}}

@endsection {{-- fim da seção content --}}
