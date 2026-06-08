{{-- Herda o layout principal da aplicação --}}
@extends('layouts.app')

{{-- Define o título da aba/página com o nome do técnico --}}
@section('title', $tecnico->nome)
{{-- Preenche o breadcrumb com link para o índice e o nome do técnico --}}
@section('breadcrumb')
    {{-- Link clicável para voltar à listagem de técnicos --}}
    <a href="{{ route('tecnicos.index') }}" style="color:var(--muted);text-decoration:none">técnicos</a>
    {{-- Separador visual entre os níveis do breadcrumb --}}
    <span class="sep">/</span>
    {{-- Nó atual mostrando o nome do técnico visualizado --}}
    <span>{{ $tecnico->nome }}</span>
@endsection {{-- fim da seção breadcrumb --}}

{{-- Inicia a seção de conteúdo principal --}}
@section('content')

{{-- Cabeçalho da página com matrícula, nome e botão de edição --}}
<div class="page-header">
    {{-- Bloco do título com matrícula como subtítulo e nome como título principal --}}
    <div class="page-title">
        {{-- Subtítulo com a matrícula do técnico --}}
        <small>// técnico — {{ $tecnico->matricula }}</small>
        {{-- Nome completo do técnico como título principal --}}
        {{ $tecnico->nome }}
    </div>
    {{-- Área de botões de ação --}}
    <div style="display:flex;gap:8px">
        {{-- Botão de edição visível somente se o usuário tiver permissão tecnicos.editar --}}
        @if(auth()->user()->hasPermission('tecnicos.editar'))
        {{-- Link para o formulário de edição do técnico --}}
        <a href="{{ route('tecnicos.edit', $tecnico) }}" class="btn btn-primary">Editar</a>
        @endif {{-- fim do bloco de permissão tecnicos.editar --}}
    </div>
</div> {{-- fim do page-header --}}

{{-- Layout em duas colunas: ficha do técnico (esquerda) e tabela de OS (direita) --}}
<div style="display:grid;grid-template-columns:280px 1fr;gap:20px">

    {{-- COLUNA ESQUERDA: ficha com dados do técnico --}}
    <div class="table-wrap" style="padding:20px;height:fit-content">
        {{-- Badge de status do técnico: verde se ativo, cinza se inativo --}}
        <span class="badge {{ $tecnico->ativo ? 'badge-green' : 'badge-gray' }}" style="margin-bottom:16px;display:inline-block">
            {{-- Texto do status: "Ativo" ou "Inativo" --}}
            {{ $tecnico->ativo ? 'Ativo' : 'Inativo' }}
        </span>
        {{-- Loop sobre array de pares [rótulo, valor] para renderizar cada dado do técnico --}}
        @foreach([
            ['Matrícula',     $tecnico->matricula],
            ['E-mail',        $tecnico->email],
            ['Especialidade', $tecnico->especialidade ?? '—'],
            ['Telefone',      $tecnico->telefone ?? '—'],
            ['Total O.S.',    $tecnico->ordens->count()],
            ['Cadastro',      $tecnico->created_at->format('d/m/Y')],
        ] as [$label, $value])
        {{-- Linha de dado com rótulo e valor separados por espaço e borda inferior --}}
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
            {{-- Rótulo do dado em fonte monoespaçada e cor discreta --}}
            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:1px">{{ $label }}</span>
            {{-- Valor do dado em fonte condensada e negrito --}}
            <span style="font-family:var(--cond);font-size:13px;font-weight:500;max-width:160px;text-align:right">{{ $value }}</span>
        </div>
        @endforeach {{-- fim do loop de dados do técnico --}}
    </div> {{-- fim da ficha do técnico --}}

    {{-- COLUNA DIREITA: tabela de ordens de serviço atribuídas ao técnico --}}
    <div>
        {{-- Título da seção de ordens do técnico --}}
        <div style="font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:10px">
            // ORDENS DE SERVIÇO ATRIBUÍDAS
        </div>
        {{-- Container da tabela de ordens com scroll --}}
        <div class="table-wrap">
            <table>
                {{-- Cabeçalho da tabela de OS --}}
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Máquina</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Abertura</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Itera sobre as OS do técnico, ordenadas da mais recente para a mais antiga --}}
                    @forelse($tecnico->ordens->sortByDesc('created_at') as $os)
                    <tr>
                        {{-- Número da OS como link para o detalhe, destacado com cor de acento --}}
                        <td class="mono" style="font-size:11px">
                            <a href="{{ route('ordens.show', $os) }}" style="color:var(--accent)">{{ $os->numero }}</a>
                        </td>
                        {{-- Modelo da máquina vinculada à OS; exibe "—" se não encontrada --}}
                        <td>{{ $os->maquina->modelo ?? '—' }}</td>
                        {{-- Badge de tipo: laranja para corretiva, azul para preventiva --}}
                        <td><span class="badge {{ $os->tipo==='corretiva'?'badge-orange':'badge-blue' }}">{{ $os->tipo_label }}</span></td>
                        <td>
                            {{-- Define a cor do badge de status com base no valor do campo --}}
                            @php $sc = match($os->status){'aberta'=>'blue','em_andamento'=>'yellow','concluida'=>'green',default=>'gray'}; @endphp
                            {{-- Badge colorido exibindo o rótulo legível do status da OS --}}
                            <span class="badge badge-{{ $sc }}">{{ $os->status_label }}</span>
                        </td>
                        {{-- Data de abertura da OS formatada como DD/MM/AAAA --}}
                        <td class="mono" style="font-size:11px;color:var(--muted)">{{ $os->data_abertura->format('d/m/Y') }}</td>
                    </tr>
                    {{-- Fallback: exibido quando o técnico não possui ordens atribuídas --}}
                    @empty
                    <tr><td colspan="5" style="color:var(--muted);font-family:var(--mono);font-size:11px;padding:20px">— sem ordens atribuídas —</td></tr>
                    @endforelse {{-- fim do loop de OS do técnico --}}
                </tbody>
            </table>
        </div> {{-- fim do table-wrap das OS --}}
    </div> {{-- fim da coluna direita --}}

</div> {{-- fim do grid de duas colunas --}}

@endsection {{-- fim da seção content --}}
