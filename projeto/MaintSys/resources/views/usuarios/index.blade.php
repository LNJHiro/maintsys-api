{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba do navegador --}}
@section('title', 'Usuarios')

{{-- Define o breadcrumb de navegação --}}
@section('breadcrumb')
    {{-- Texto fixo indicando a seção de usuários --}}
    <span>usuarios</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção principal de conteúdo da página --}}
@section('content')

{{-- Cabeçalho da página com título e botões condicionais --}}
<div class="page-header">
    {{-- Bloco do título com subtítulo decorativo --}}
    <div class="page-title">
        {{-- Subtítulo em estilo monospace indicando seção de administração --}}
        <small>// administracao</small>
        {{-- Título principal da página --}}
        Gerenciar Usuarios
    </div>
    {{-- Container dos botões de ação condicionados por permissão --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        {{-- Verifica se o usuário tem permissão para gerenciar acesso --}}
        @if(auth()->user()->hasPermission('acesso.gerenciar'))
        {{-- Link para a tela de gerenciamento de permissões por role --}}
        <a href="{{ route('acesso.index') }}" class="btn btn-secondary">Gerenciar Acesso</a>
        @endif {{-- fim da verificação de permissão de acesso --}}
        {{-- Verifica se o usuário tem permissão para criar novos usuários --}}
        @if(auth()->user()->hasPermission('usuarios.criar'))
        {{-- Botão para navegar ao formulário de criação de usuário --}}
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">+ Novo Usuario</a>
        @endif {{-- fim da verificação de permissão para criar --}}
    </div>
</div>

{{-- Grade de estatísticas com totais de usuários por role --}}
<div class="stats-grid">
    {{-- Card com total geral de usuários cadastrados --}}
    <div class="stat-card">
        <div class="stat-label">Total Usuarios</div>
        {{-- Conta todos os usuários da coleção carregada --}}
        <div class="stat-value">{{ $users->count() }}</div>
    </div>
    {{-- Card laranja com total de usuários com role "admin" --}}
    <div class="stat-card orange">
        <div class="stat-label">Administradores</div>
        {{-- Filtra a coleção contando apenas administradores --}}
        <div class="stat-value">{{ $users->where('role', 'admin')->count() }}</div>
    </div>
    {{-- Card azul com total de usuários com role "usuario" --}}
    <div class="stat-card blue">
        <div class="stat-label">Usuarios Comuns</div>
        {{-- Filtra a coleção contando apenas usuários comuns --}}
        <div class="stat-value">{{ $users->where('role', 'usuario')->count() }}</div>
    </div>
</div>

{{-- Container da tabela de usuários --}}
<div class="table-wrap">
    {{-- Tabela principal listando todos os usuários --}}
    <table>
        {{-- Cabeçalho da tabela com os nomes das colunas --}}
        <thead>
            <tr>
                <th>Usuario</th>
                <th>E-mail</th>
                <th>Nivel</th>
                <th>Criado em</th>
                <th>Acoes</th>
            </tr>
        </thead>
        {{-- Corpo da tabela com os registros de usuários --}}
        <tbody>
            {{-- Itera sobre os usuários; exibe mensagem se a lista estiver vazia --}}
            @forelse($users as $user)
            {{-- Linha da tabela para cada usuário --}}
            <tr>
                {{-- Exibe o nome do usuário em negrito --}}
                <td style="font-weight:600">{{ $user->name }}</td>
                {{-- Exibe o e-mail do usuário em fonte mono --}}
                <td class="mono" style="color:var(--muted);font-size:16px">{{ $user->email }}</td>
                {{-- Coluna de nível com badge colorido e badge "Individual" condicional --}}
                <td>
                    {{-- Badge laranja para admin, azul para usuário comum --}}
                    <span class="badge {{ $user->role === 'admin' ? 'badge-orange' : 'badge-blue' }}">
                        {{-- Exibe "Admin" ou "Usuario" conforme o role --}}
                        {{ $user->role === 'admin' ? 'Admin' : 'Usuario' }}
                    </span>
                    {{-- Verifica se este usuário tem permissões individuais diferentes do padrão da role --}}
                    @if($user->permissions_overridden)
                    {{-- Badge amarelo indicando que as permissões foram personalizadas individualmente --}}
                    <span class="badge badge-yellow" style="margin-left:6px">Individual</span>
                    @endif {{-- fim da verificação de permissões individuais --}}
                </td>
                {{-- Exibe a data de criação do usuário formatada --}}
                <td class="mono" style="color:var(--muted);font-size:16px">{{ $user->created_at->format('d/m/Y') }}</td>
                {{-- Coluna de ações com botões de editar, permissões e deletar --}}
                <td>
                    {{-- Container dos botões de ação --}}
                    <div class="actions">
                        {{-- Verifica se o usuário logado tem permissão para editar usuários --}}
                        @if(auth()->user()->hasPermission('usuarios.editar'))
                        {{-- Botão que navega ao formulário de edição do usuário --}}
                        <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-secondary btn-sm">Editar</a>
                        @endif {{-- fim da verificação de permissão para editar --}}
                        {{-- Verifica se o usuário logado tem permissão para ver permissões --}}
                        @if(auth()->user()->hasPermission('usuarios.permissoes'))
                        {{-- Botão que navega à tela de permissões do usuário --}}
                        <a href="{{ route('usuarios.permissions', $user) }}" class="btn btn-secondary btn-sm">Perms</a>
                        @endif {{-- fim da verificação de permissão para ver permissões --}}
                        {{-- Verifica se o usuário logado tem permissão para deletar usuários --}}
                        @if(auth()->user()->hasPermission('usuarios.deletar'))
                        {{-- Formulário de exclusão com confirmação antes de enviar --}}
                        <form action="{{ route('usuarios.destroy', $user) }}" method="POST"
                              onsubmit="confirmDelete(this, 'Deletar usuario {{ $user->name }}?'); return false;">
                            {{-- Token CSRF para proteção --}}
                            @csrf
                            {{-- Método DELETE simulado para exclusão --}}
                            @method('DELETE')
                            {{-- Botão vermelho de exclusão do usuário --}}
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                        @endif {{-- fim da verificação de permissão para deletar --}}
                    </div>
                </td>
            </tr>
            {{-- Bloco exibido quando não há nenhum usuário cadastrado --}}
            @empty
            <tr>
                {{-- Célula que ocupa todas as 5 colunas com mensagem de lista vazia --}}
                <td colspan="5" style="text-align:center;color:var(--muted);font-family:var(--mono);padding:32px">
                    nenhum usuario cadastrado
                </td>
            </tr>
            @endforelse {{-- fim da iteração sobre os usuários --}}
        </tbody>
    </table>
</div>

@endsection {{-- fim da seção de conteúdo principal --}}
