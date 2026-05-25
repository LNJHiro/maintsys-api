@extends('layouts.app')

@section('title', 'Meu Perfil')
@section('breadcrumb')
    <span>perfil</span>
@endsection

@push('styles')
<style>
    @media (max-width: 920px) {
        .profile-grid { grid-template-columns: 1fr !important; }
    }

    @media (max-width: 640px) {
        .profile-grid .form-row { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <small>// conta e notificacoes</small>
        Meu Perfil
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Voltar</a>
</div>

<div class="profile-grid" style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:20px;align-items:start;">
    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="form-card" style="max-width:none;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
                <div>
                    <div style="font-family:var(--mono);font-size:16px;color:var(--red);letter-spacing:2px;text-transform:uppercase;">// notificacoes</div>
                    <div style="font-family:var(--cond);font-size:24px;font-weight:800;color:var(--text);">Ordens atribuidas</div>
                </div>

                @if($unreadNotificationsCount > 0)
                    <form method="POST" action="{{ route('profile.notifications.read') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Marcar como lidas</button>
                    </form>
                @endif
            </div>

            @if (session('status') === 'notifications-read')
                <div class="alert alert-success" style="margin-bottom:14px;">Notificacoes marcadas como lidas.</div>
            @endif

            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = is_null($notification->read_at);
                    @endphp

                    <div style="border:1px solid {{ $isUnread ? 'rgba(227,0,15,.35)' : 'var(--border)' }};border-left:3px solid {{ $isUnread ? 'var(--red)' : 'var(--border-hi)' }};padding:14px;background:{{ $isUnread ? 'rgba(227,0,15,.04)' : 'var(--surface)' }};">
                        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                            <div>
                                <div style="font-family:var(--cond);font-size:20px;font-weight:800;color:var(--text);">
                                    {{ $data['titulo'] ?? 'Notificacao' }}
                                </div>
                                <div style="color:var(--muted);font-size:17px;margin-top:2px;">
                                    {{ $data['mensagem'] ?? '' }}
                                </div>
                            </div>

                            @if($isUnread)
                                <span class="badge badge-red" style="font-size:12px;">Nova</span>
                            @else
                                <span class="badge badge-gray" style="font-size:12px;">Lida</span>
                            @endif
                        </div>

                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                            @if(!empty($data['numero']))
                                <span class="badge badge-blue" style="font-size:12px;">{{ $data['numero'] }}</span>
                            @endif
                            @if(!empty($data['maquina']))
                                <span class="badge badge-gray" style="font-size:12px;">{{ $data['maquina'] }}</span>
                            @endif
                            @if(!empty($data['prioridade']))
                                <span class="badge badge-orange" style="font-size:12px;">{{ ucfirst($data['prioridade']) }}</span>
                            @endif
                            @if(!empty($data['data_prevista']))
                                <span class="badge badge-gray" style="font-size:12px;">Prevista {{ \Illuminate\Support\Carbon::parse($data['data_prevista'])->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:12px;">
                            <span style="font-family:var(--mono);font-size:14px;color:var(--muted);">
                                {{ $notification->created_at?->format('d/m/Y H:i') }}
                            </span>

                            @if(!empty($data['url']) && auth()->user()->hasPermission('ordens.visualizar'))
                                <a href="{{ $data['url'] }}" class="btn btn-primary btn-sm">Abrir O.S.</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="border:1px dashed var(--border-hi);padding:18px;color:var(--muted);font-family:var(--mono);font-size:16px;text-align:center;">
                        Nenhuma notificacao recebida.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="form-card" style="max-width:none;">
            <div style="font-family:var(--mono);font-size:16px;color:var(--red);letter-spacing:2px;text-transform:uppercase;margin-bottom:16px;">// dados do perfil</div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;">
                    <button type="submit" class="btn btn-primary">Salvar perfil</button>
                    @if (session('status') === 'profile-updated')
                        <span style="font-family:var(--mono);font-size:14px;color:var(--green);">Salvo.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="form-card" style="max-width:none;">
            <div style="font-family:var(--mono);font-size:16px;color:var(--red);letter-spacing:2px;text-transform:uppercase;margin-bottom:16px;">// seguranca</div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="update_password_current_password">Senha atual</label>
                    <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
                    @foreach($errors->updatePassword->get('current_password') as $message)
                        <div class="form-error">{{ $message }}</div>
                    @endforeach
                </div>

                <div class="form-group">
                    <label for="update_password_password">Nova senha</label>
                    <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password">
                    @foreach($errors->updatePassword->get('password') as $message)
                        <div class="form-error">{{ $message }}</div>
                    @endforeach
                </div>

                <div class="form-group">
                    <label for="update_password_password_confirmation">Confirmar senha</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                </div>

                <div style="display:flex;align-items:center;gap:10px;">
                    <button type="submit" class="btn btn-primary">Atualizar senha</button>
                    @if (session('status') === 'password-updated')
                        <span style="font-family:var(--mono);font-size:14px;color:var(--green);">Senha atualizada.</span>
                    @endif
                </div>
            </form>
        </div>

        <div class="form-card" style="max-width:none;border-color:rgba(220,38,38,.25);">
            <div style="font-family:var(--mono);font-size:16px;color:var(--red-badge);letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;">// excluir conta</div>
            <p style="color:var(--muted);font-size:16px;line-height:1.5;margin-bottom:16px;">
                Esta acao remove sua conta de acesso. Use apenas se tiver certeza.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="form-group">
                    <label for="delete_password">Senha</label>
                    <input id="delete_password" name="password" type="password" class="form-control" autocomplete="current-password">
                    @foreach($errors->userDeletion->get('password') as $message)
                        <div class="form-error">{{ $message }}</div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-danger">Excluir minha conta</button>
            </form>
        </div>
    </div>
</div>

@endsection
