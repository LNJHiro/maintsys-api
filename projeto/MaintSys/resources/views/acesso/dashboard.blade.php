@extends('layouts.app')

@section('content')
<div style="background: var(--bg); min-height: 100vh; padding: 28px;">
    <!-- HEADER -->
    <div style="margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; margin-bottom: 16px;">
            <div>
                <div style="font-family: var(--mono); font-size: 13px; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
                    // acesso & permissões
                </div>
                <h1 style="font-family: var(--cond); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px;">
                    GERENCIAR ACESSO
                </h1>
                <p style="font-family: var(--sans); font-size: 14px; color: var(--muted);">
                    Configure permissões por usuário ou por nível de acesso
                </p>
            </div>
        </div>

        <!-- TABS/SECTION SELECTOR -->
        <div style="display: flex; gap: 8px; margin-top: 24px;">
            <button id="tab-users" onclick="switchTab('users')" style="padding: 10px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; border: 1px solid var(--accent); cursor: pointer; transition: all 0.15s; border-radius: 4px;">
                👥 POR USUÁRIO
            </button>
            <button id="tab-roles" onclick="switchTab('roles')" style="padding: 10px 16px; background: transparent; color: var(--muted); font-family: var(--cond); font-size: 13px; font-weight: 600; border: 1px solid var(--border-hi); cursor: pointer; transition: all 0.15s; border-radius: 4px;">
                📋 POR NÍVEL
            </button>
        </div>
    </div>

    <!-- TAB: GERENCIAR POR USUÁRIO -->
    <div id="section-users" style="display: block;">
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 20px;">
            <!-- LISTA DE USUÁRIOS -->
            <div>
                <div style="background: var(--card); border: 1px solid var(--border); border-radius: 4px; overflow: hidden;">
                    <div style="background: var(--surface); border-bottom: 1px solid var(--border); padding: 14px;">
                        <div style="font-family: var(--mono); font-size: 12px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase;">
                            Usuários
                        </div>
                    </div>
                    <div style="max-height: 600px; overflow-y: auto;">
                        @foreach($users as $user)
                            <button
                                onclick="selectUser({{ $user->id }}, '{{ $user->name }}')"
                                id="user-btn-{{ $user->id }}"
                                style="width: 100%; padding: 12px 14px; background: transparent; border: none; border-bottom: 1px solid var(--border); color: var(--muted); font-family: var(--sans); font-size: 15px; text-align: left; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; gap: 8px;"
                            >
                                <span style="flex: 1;">{{ $user->name }}</span>
                                @if($userHasIndividual[$user->id])
                                    <span title="Permissões individuais configuradas" style="display: inline-block; width: 8px; height: 8px; background: var(--accent2); border-radius: 50%;"></span>
                                @else
                                    <span title="Usando permissões do nível" style="display: inline-block; width: 8px; height: 8px; background: var(--border-hi); border-radius: 50%;"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- PERMISSÕES DO USUÁRIO SELECIONADO -->
            <div id="user-permissions-container" style="display: none;">
                <div style="background: var(--card); border: 1px solid var(--border); border-radius: 4px; padding: 20px;">
                    <div style="margin-bottom: 24px;">
                        <div style="font-family: var(--mono); font-size: 12px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                            Permissões de
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <h2 id="selected-user-name" style="font-family: var(--cond); font-size: 24px; font-weight: 700; color: var(--text); margin: 0;"></h2>
                            <span id="perm-source-badge" style="display: none; padding: 3px 10px; font-family: var(--mono); font-size: 12px; letter-spacing: 1px; text-transform: uppercase; border: 1px solid; border-radius: 2px;"></span>
                        </div>
                    </div>

                    <div id="user-permissions-form" style="margin-bottom: 24px;"></div>

                    <div id="save-status" style="display: none; padding: 12px; margin-bottom: 16px; border-radius: 4px; font-family: var(--sans); font-size: 13px;"></div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button
                            type="button"
                            id="user-save-btn"
                            onclick="saveUserPermissions()"
                            style="flex: 2; min-width: 160px; padding: 12px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 15px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--accent); cursor: pointer; transition: all 0.15s; border-radius: 4px;"
                        >
                            ✓ SALVAR PERMISSÕES
                        </button>
                        <button
                            type="button"
                            id="user-reset-btn"
                            onclick="resetToRole()"
                            title="Remove a configuração individual e volta a usar as permissões do nível"
                            style="flex: 1; min-width: 100px; padding: 12px 16px; background: transparent; color: var(--yellow); font-family: var(--cond); font-size: 14px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(154,103,0,.4); cursor: pointer; transition: all 0.15s; border-radius: 4px;"
                        >
                            ↺ RESETAR
                        </button>
                        <button
                            type="button"
                            onclick="closeUserPermissions()"
                            style="flex: 1; min-width: 80px; padding: 12px 16px; background: transparent; color: var(--muted); font-family: var(--cond); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border: 1px solid var(--border-hi); cursor: pointer; transition: all 0.15s; border-radius: 4px;"
                        >
                            FECHAR
                        </button>
                    </div>
                </div>
            </div>

            <!-- INSTRUÇÃO SE NENHUM USUÁRIO SELECIONADO -->
            <div id="user-empty-state" style="display: flex; align-items: center; justify-content: center; background: var(--card); border: 1px solid var(--border); border-radius: 4px; padding: 60px 20px; text-align: center;">
                <div>
                    <div style="font-size: 48px; margin-bottom: 16px;">👤</div>
                    <div style="font-family: var(--cond); font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 8px;">
                        Selecione um usuário
                    </div>
                    <p style="font-family: var(--sans); font-size: 13px; color: var(--muted);">
                        Clique em um usuário à esquerda para ver e editar suas permissões
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB: GERENCIAR POR NÍVEL -->
    <div id="section-roles" style="display: none;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            @foreach(['admin' => 'Administrador', 'usuario' => 'Usuário'] as $roleKey => $roleLabel)
                <div style="background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 4px;">
                    <h2 style="font-family: var(--cond); font-size: 18px; font-weight: 700; color: var(--text); margin: 0 0 20px 0; text-transform: uppercase;">
                        {{ $roleLabel }}
                    </h2>

                    <form id="form-{{ $roleKey }}">
                        @csrf
                        @foreach($permissions as $modulo => $perms)
                            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
                                <h3 style="font-family: var(--cond); font-size: 15px; font-weight: 700; color: var(--text); text-transform: uppercase; margin: 0 0 12px 0;">
                                    @switch($modulo)
                                        @case('maquinas')
                                            ⚙ Máquinas
                                            @break
                                        @case('tecnicos')
                                            👤 Técnicos
                                            @break
                                        @case('ordens')
                                            📋 Ordens
                                            @break
                                        @case('historico')
                                            ⏱ Histórico
                                            @break
                                        @case('dashboard')
                                            ◆ Dashboard
                                            @break
                                        @case('usuarios')
                                            Usuarios
                                            @break
                                        @case('acesso')
                                            Acesso
                                            @break
                                        @default
                                            {{ ucfirst($modulo) }}
                                    @endswitch
                                </h3>

                                <div style="space-y: 8px;">
                                    @foreach($perms as $permission)
                                        <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 8px; padding: 6px 0;">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->id }}"
                                                {{ in_array($permission->id, $rolePermissions[$roleKey] ?? []) ? 'checked' : '' }}
                                                style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--accent);"
                                            />
                                            <span style="margin-left: 8px; font-family: var(--sans); font-size: 13px; color: var(--text);">
                                                {{ $permission->descricao }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <button
                            type="button"
                            onclick="saveRolePermissions('{{ $roleKey }}')"
                            style="width: 100%; padding: 10px 16px; background: var(--accent); color: white; font-family: var(--cond); font-size: 13px; font-weight: 600; border: 1px solid var(--accent); cursor: pointer; transition: all 0.15s; border-radius: 4px;"
                        >
                            ✓ SALVAR NÍVEL
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 24px; background: rgba(227,0,15,.06); border: 1px solid rgba(227,0,15,.2); border-radius: 4px; padding: 16px;">
            <div style="font-family: var(--mono); font-size: 12px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;">
                ℹ️ NÍVEL DE ACESSO
            </div>
            <div style="font-family: var(--sans); font-size: 13px; color: var(--text); line-height: 1.6;">
                Permissões configuradas aqui aplicam-se a <strong>todos os usuários</strong> daquele nível, a menos que você customize permissões individuais.
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;

function switchTab(tab) {
    document.getElementById('section-users').style.display = tab === 'users' ? 'block' : 'none';
    document.getElementById('section-roles').style.display = tab === 'roles' ? 'block' : 'none';

    document.getElementById('tab-users').style.background = tab === 'users' ? 'var(--accent)' : 'transparent';
    document.getElementById('tab-users').style.color = tab === 'users' ? 'white' : 'var(--muted)';
    document.getElementById('tab-users').style.borderColor = tab === 'users' ? 'var(--accent)' : 'var(--border-hi)';

    document.getElementById('tab-roles').style.background = tab === 'roles' ? 'var(--accent)' : 'transparent';
    document.getElementById('tab-roles').style.color = tab === 'roles' ? 'white' : 'var(--muted)';
    document.getElementById('tab-roles').style.borderColor = tab === 'roles' ? 'var(--accent)' : 'var(--border-hi)';
}

function selectUser(userId, userName) {
    currentUserId = userId;

    // Atualizar seleção visual
    document.querySelectorAll('[id^="user-btn-"]').forEach(btn => {
        btn.style.background = 'transparent';
        btn.style.color = 'var(--muted)';
        btn.querySelector('span:last-child').style.background = 'transparent';
    });

    const selectedBtn = document.getElementById(`user-btn-${userId}`);
    selectedBtn.style.background = 'rgba(227,0,15,0.08)';
    selectedBtn.style.color = 'var(--text)';
    selectedBtn.querySelector('span:last-child').style.background = 'var(--accent)';

    // Atualizar nome
    document.getElementById('selected-user-name').textContent = userName;

    // Mostrar formulário
    document.getElementById('user-empty-state').style.display = 'none';
    document.getElementById('user-permissions-container').style.display = 'block';
    document.getElementById('save-status').style.display = 'none';

    // Construir formulário de permissões
    const container = document.getElementById('user-permissions-form');
    container.innerHTML = '';

    const permissions = @json($permissions);
    const userPerms = @json($userPermissions);
    const hasIndividual = @json($userHasIndividual);
    const currentUserPerms = userPerms[userId] || [];

    // Atualizar badge de origem das permissões
    const badge = document.getElementById('perm-source-badge');
    badge.style.display = 'inline-block';
    if (hasIndividual[userId]) {
        badge.textContent = '⚙ Individual';
        badge.style.color = 'var(--accent2)';
        badge.style.borderColor = 'rgba(230,51,41,.4)';
        badge.style.background = 'rgba(230,51,41,.08)';
    } else {
        badge.textContent = '↑ Herdado do nível';
        badge.style.color = 'var(--muted)';
        badge.style.borderColor = 'var(--border-hi)';
        badge.style.background = 'transparent';
    }

    Object.entries(permissions).forEach(([modulo, perms]) => {
        const section = document.createElement('div');
        section.style.marginBottom = '20px';
        section.style.paddingBottom = '20px';
        section.style.borderBottom = '1px solid var(--border)';

        const title = document.createElement('h3');
        title.style.fontFamily = 'var(--cond)';
        title.style.fontSize = '12px';
        title.style.fontWeight = '700';
        title.style.color = 'var(--text)';
        title.style.textTransform = 'uppercase';
        title.style.margin = '0 0 12px 0';

        const icons = { maquinas: '⚙', tecnicos: '👤', ordens: '📋', historico: '⏱', dashboard: '◆', usuarios: 'U', acesso: 'A' };
        title.textContent = (icons[modulo] || '•') + ' ' + modulo.charAt(0).toUpperCase() + modulo.slice(1);

        section.appendChild(title);

        perms.forEach(perm => {
            const label = document.createElement('label');
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.cursor = 'pointer';
            label.style.marginBottom = '8px';
            label.style.padding = '6px 0';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'user-perm-checkbox';
            checkbox.dataset.permId = perm.id;
            checkbox.checked = currentUserPerms.includes(perm.id);
            checkbox.style.width = '16px';
            checkbox.style.height = '16px';
            checkbox.style.cursor = 'pointer';
            checkbox.style.accentColor = 'var(--accent)';

            const span = document.createElement('span');
            span.textContent = perm.descricao;
            span.style.marginLeft = '8px';
            span.style.fontFamily = 'var(--sans)';
            span.style.fontSize = '13px';
            span.style.color = 'var(--text)';

            label.appendChild(checkbox);
            label.appendChild(span);
            section.appendChild(label);
        });

        container.appendChild(section);
    });
}

function closeUserPermissions() {
    document.getElementById('user-permissions-container').style.display = 'none';
    document.getElementById('save-status').style.display = 'none';
}

function showStatus(message, isError = false) {
    const statusDiv = document.getElementById('save-status');
    statusDiv.textContent = message;
    statusDiv.style.display = 'block';
    statusDiv.style.backgroundColor = isError ? 'rgba(207,34,46,.15)' : 'rgba(45,164,78,.15)';
    statusDiv.style.color = isError ? 'var(--red)' : 'var(--green)';
    statusDiv.style.borderLeft = isError ? '3px solid var(--red)' : '3px solid var(--green)';
}

function saveUserPermissions() {
    if (!currentUserId) {
        showStatus('❌ Nenhum usuário selecionado', true);
        return;
    }

    const btn = document.getElementById('user-save-btn');
    const originalText = btn.textContent;
    btn.textContent = '⏳ Salvando...';
    btn.disabled = true;

    // Coletar checkboxes marcadas
    const checkboxes = document.querySelectorAll('.user-perm-checkbox:checked');
    const permissions = Array.from(checkboxes).map(cb => cb.dataset.permId);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch(`/acesso/usuario/${currentUserId}/permissoes`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions: permissions })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        btn.textContent = originalText;
        btn.disabled = false;

        if (data.message) {
            showStatus('✓ ' + data.message);
            // Atualizar badge para "Individual"
            const badge = document.getElementById('perm-source-badge');
            badge.textContent = '⚙ Individual';
            badge.style.color = 'var(--accent2)';
            badge.style.borderColor = 'rgba(230,51,41,.4)';
            badge.style.background = 'rgba(230,51,41,.08)';
            // Atualizar bolinha na lista
            const dot = document.querySelector(`#user-btn-${currentUserId} span:last-child`);
            if (dot) dot.style.background = 'var(--accent2)';
        } else if (data.error) {
            showStatus('❌ ' + data.error, true);
        }
    })
    .catch(error => {
        btn.textContent = originalText;
        btn.disabled = false;
        console.error('Erro completo:', error);
        showStatus('❌ Erro ao salvar: ' + error.message, true);
    });
}

function resetToRole() {
    if (!currentUserId) return;

    if (!confirm('Remover configuração individual e voltar a usar as permissões do nível?')) return;

    const btn = document.getElementById('user-reset-btn');
    btn.textContent = '⏳...';
    btn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch(`/acesso/usuario/${currentUserId}/permissoes`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ inherit: true })
    })
    .then(response => response.json())
    .then(data => {
        btn.textContent = '↺ RESETAR';
        btn.disabled = false;

        if (data.message) {
            showStatus('✓ Configuração individual removida. Usando permissões do nível.');
            // Atualizar badge para "Herdado"
            const badge = document.getElementById('perm-source-badge');
            badge.textContent = '↑ Herdado do nível';
            badge.style.color = 'var(--muted)';
            badge.style.borderColor = 'var(--border-hi)';
            badge.style.background = 'transparent';
            // Atualizar bolinha na lista
            const dot = document.querySelector(`#user-btn-${currentUserId} span:last-child`);
            if (dot) dot.style.background = 'var(--border-hi)';
        }
    })
    .catch(error => {
        btn.textContent = '↺ RESETAR';
        btn.disabled = false;
        showStatus('❌ Erro: ' + error.message, true);
    });
}

function saveRolePermissions(role) {
    const form = document.getElementById(`form-${role}`);
    const formData = new FormData(form);
    const permissions = formData.getAll('permissions[]');

    const btn = event.target;
    const originalText = btn.textContent;
    btn.textContent = '⏳ Salvando...';
    btn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch(`/acesso/role/${role}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions: permissions })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        btn.textContent = originalText;
        btn.disabled = false;

        if (data.message) {
            alert('✓ ' + data.message);
        } else if (data.error) {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        btn.textContent = originalText;
        btn.disabled = false;
        console.error('Erro completo:', error);
        alert('❌ Erro ao salvar: ' + error.message);
    });
}
</script>
@endsection
