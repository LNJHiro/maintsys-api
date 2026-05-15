<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MaintSys — @yield('title', 'Controle de Manutenção')</title>

    <link rel="icon" href="/senai.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@300;400;500;600;700;900&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── TOKENS ── */
        :root {
            --red:       #E3000F;
            --red-dark:  #b80009;
            --red-lite:  #ff2030;

            --bg:        #f0f2f5;
            --surface:   #ffffff;
            --card:      #ffffff;
            --border:    #e0e4ea;
            --border-hi: #c8cdd6;
            --text:      #111827;
            --muted:     #6b7280;

            --green:     #16a34a;
            --red-badge: #dc2626;
            --yellow:    #b45309;
            --blue:      #1d4ed8;
            --orange:    #c2410c;

            --mono:  'Share Tech Mono', monospace;
            --sans:  'Barlow', sans-serif;
            --cond:  'Barlow Condensed', sans-serif;

            /* alias de compatibilidade — views antigas usam --accent */
            --accent:  var(--red);
            --accent2: var(--orange);

            --sidebar-w: 248px;
        }

        [data-theme="dark"] {
            --bg:        #0d1117;
            --surface:   #161b22;
            --card:      #1f2430;
            --border:    #2a3040;
            --border-hi: #374151;
            --text:      #e6edf3;
            --muted:     #8b949e;

            --red:       #ff3b47;
            --red-dark:  #cc0010;
            --red-lite:  #ff5c66;
            --green:     #3fb950;
            --red-badge: #f85149;
            --yellow:    #d29922;
            --blue:      #58a6ff;
            --orange:    #f0883e;

            --accent:  var(--red);
            --accent2: var(--orange);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { height: 100%; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            font-size: 18px;
            min-height: 100vh;
            display: flex;
        }

        a { text-decoration: none; color: inherit; }

        /* ─────────────────────────────────────────────────────
           SIDEBAR
        ───────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            overflow-y: auto;
        }

        /* Faixa vermelha no topo da sidebar, com listras SENAI */
        .sidebar-header {
            background: var(--red);
            padding: 0;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* Listras decorativas estilo SENAI nas laterais */
        .sidebar-header::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 10px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.3) 0, rgba(255,255,255,.3) 7px,
                transparent 7px, transparent 13px
            );
        }
        .sidebar-header::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 10px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.3) 0, rgba(255,255,255,.3) 7px,
                transparent 7px, transparent 13px
            );
        }

        .sidebar-header-inner {
            padding: 18px 20px;
            position: relative;
            z-index: 1;
        }

        .sidebar-header img {
            height: 36px;
            width: auto;
            display: block;
            margin-bottom: 10px;
        }

        .sidebar-product {
            font-family: var(--cond);
            font-size: 19px;
            font-weight: 800;
            color: rgba(255,255,255,.9);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1;
        }

        .sidebar-sub {
            font-family: var(--mono);
            font-size: 14px;
            color: rgba(255,255,255,.55);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Seção label dentro da sidebar */
        .sidebar-section {
            padding: 18px 16px 6px;
            font-family: var(--mono);
            font-size: 14px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--muted);
            font-family: var(--cond);
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-left: 3px solid transparent;
            transition: all .15s;
        }

        .sidebar nav a:hover {
            color: var(--text);
            background: rgba(227,0,15,.05);
            border-left-color: rgba(227,0,15,.4);
        }

        [data-theme="dark"] .sidebar nav a:hover {
            background: rgba(255,59,71,.08);
        }

        .sidebar nav a.active {
            color: var(--red);
            background: rgba(227,0,15,.07);
            border-left-color: var(--red);
            font-weight: 700;
        }

        [data-theme="dark"] .sidebar nav a.active {
            background: rgba(255,59,71,.12);
        }

        .sidebar nav a .icon { font-size: 20px; width: 24px; text-align: center; }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            background: var(--surface);
            flex-shrink: 0;
        }

        .user-name {
            font-family: var(--cond);
            font-weight: 800;
            font-size: 18px;
            color: var(--text);
        }

        .user-email {
            color: var(--muted);
            font-size: 16px;
            margin-top: 2px;
        }

        .user-role {
            display: inline-block;
            margin-top: 6px;
            font-family: var(--mono);
            font-size: 9px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 2px 8px;
            border: 1px solid rgba(227,0,15,.35);
            color: var(--red);
            background: rgba(227,0,15,.06);
        }

        [data-theme="dark"] .user-role {
            border-color: rgba(255,59,71,.35);
            background: rgba(255,59,71,.1);
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 10px 16px;
            background: transparent;
            border: 1px solid var(--border-hi);
            color: var(--muted);
            font-family: var(--mono);
            font-size: 16px;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all .15s;
            width: 100%;
            justify-content: center;
        }

        .btn-logout:hover {
            border-color: var(--red);
            color: var(--red);
            background: rgba(227,0,15,.05);
        }

        /* ─────────────────────────────────────────────────────
           MAIN
        ───────────────────────────────────────────────────── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* ── TOPBAR ── */
        .topbar {
            height: 52px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        /* Linha vermelha fina no topo da topbar */
        .topbar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--red);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--mono);
            font-size: 16px;
            color: var(--muted);
        }

        .breadcrumb span { color: var(--red); }
        .breadcrumb .sep { color: var(--border-hi); }

        .theme-toggle {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 18px;
            font-family: var(--sans);
            cursor: pointer;
            border-radius: 4px;
            transition: all .18s;
        }

        .theme-toggle:hover {
            border-color: var(--red);
            color: var(--red);
            background: rgba(227,0,15,.05);
        }

        [data-theme="dark"] .theme-toggle:hover {
            background: rgba(255,59,71,.08);
        }

        /* ── CONTENT ── */
        .content {
            padding: 28px;
            flex: 1;
        }

        /* ── ALERTS ── */
        .alert {
            padding: 14px 18px;
            margin-bottom: 18px;
            border-left: 3px solid;
            font-family: var(--cond);
            font-size: 18px;
            font-weight: 700;
            border-radius: 0 4px 4px 0;
        }

        .alert-success {
            background: rgba(22,163,74,.07);
            border-color: var(--green);
            color: var(--green);
        }

        .alert-error {
            background: rgba(220,38,38,.07);
            border-color: var(--red-badge);
            color: var(--red-badge);
        }

        .alert-warning {
            background: rgba(227,0,15,.07);
            border-color: var(--red);
            color: var(--red);
        }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .page-title {
            font-family: var(--cond);
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--text);
        }

        .page-title small {
            display: block;
            font-family: var(--mono);
            font-size: 16px;
            color: var(--red);
            letter-spacing: 2px;
            margin-bottom: 4px;
            opacity: .75;
        }

        /* ── STATS GRID ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 18px;
            position: relative;
            overflow: hidden;
            transition: box-shadow .2s;
        }

        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--red);
        }

        .stat-card.green::before  { background: var(--green); }
        .stat-card.red::before    { background: var(--red-badge); }
        .stat-card.yellow::before { background: var(--yellow); }
        .stat-card.blue::before   { background: var(--blue); }

        .stat-label {
            font-family: var(--mono);
            font-size: 16px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-value {
            font-family: var(--cond);
            font-size: 40px;
            font-weight: 800;
            line-height: 1;
            color: var(--text);
        }

        /* ── TABLE ── */
        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            padding: 12px 16px;
            text-align: left;
            font-family: var(--mono);
            font-size: 16px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .1s;
        }

        tbody tr:last-child { border-bottom: none; }

        tbody tr:hover { background: rgba(227,0,15,.03); }

        [data-theme="dark"] tbody tr:hover { background: rgba(255,59,71,.05); }

        tbody td {
            padding: 14px 16px;
            font-size: 18px;
            color: var(--text);
        }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-family: var(--mono);
            font-size: 16px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid;
        }

        .badge-green  { color: var(--green);     border-color: rgba(22,163,74,.4);   background: rgba(22,163,74,.07); }
        .badge-red    { color: var(--red-badge);  border-color: rgba(220,38,38,.4);   background: rgba(220,38,38,.07); }
        .badge-yellow { color: var(--yellow);     border-color: rgba(180,83,9,.35);   background: rgba(180,83,9,.07); }
        .badge-blue   { color: var(--blue);       border-color: rgba(29,78,216,.35);  background: rgba(29,78,216,.07); }
        .badge-gray   { color: var(--muted);      border-color: var(--border-hi);     background: transparent; }
        .badge-orange { color: var(--orange);     border-color: rgba(194,65,12,.35);  background: rgba(194,65,12,.07); }

        [data-theme="dark"] .badge-green  { background: rgba(63,185,80,.1);  }
        [data-theme="dark"] .badge-red    { background: rgba(248,81,73,.1);  }
        [data-theme="dark"] .badge-yellow { background: rgba(210,153,34,.1); }
        [data-theme="dark"] .badge-blue   { background: rgba(88,166,255,.1); }
        [data-theme="dark"] .badge-orange { background: rgba(240,136,62,.1); }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            font-family: var(--cond);
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-decoration: none;
            border: 1px solid;
            cursor: pointer;
            transition: all .15s;
            border-radius: 3px;
        }

        .btn-primary {
            background: var(--red);
            border-color: var(--red);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--red-dark);
            border-color: var(--red-dark);
            box-shadow: 0 4px 12px rgba(227,0,15,.3);
        }

        .btn-secondary {
            background: transparent;
            border-color: var(--border-hi);
            color: var(--text);
        }

        .btn-secondary:hover {
            border-color: var(--red);
            color: var(--red);
            background: rgba(227,0,15,.04);
        }

        [data-theme="dark"] .btn-secondary:hover { background: rgba(255,59,71,.07); }

        .btn-danger {
            background: transparent;
            border-color: rgba(220,38,38,.4);
            color: var(--red-badge);
        }

        .btn-danger:hover {
            background: rgba(220,38,38,.08);
            border-color: var(--red-badge);
        }

        .btn-sm { padding: 6px 12px; font-size: 16px; }

        /* ── FORMS ── */
        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 28px;
            max-width: 740px;
        }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-family: var(--mono);
            font-size: 16px;
            color: var(--muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            background: var(--surface);
            border: 1px solid var(--border-hi);
            color: var(--text);
            font-family: var(--sans);
            font-size: 18px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            border-radius: 3px;
        }

        .form-control:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(227,0,15,.12);
        }

        [data-theme="dark"] .form-control:focus {
            box-shadow: 0 0 0 3px rgba(255,59,71,.15);
        }

        .form-control option { background: var(--surface); }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-error {
            margin-top: 4px;
            font-family: var(--mono);
            font-size: 10px;
            color: var(--red-badge);
        }

        /* ── PAGINATION ── */
        .pagination {
            display: flex;
            gap: 4px;
            padding: 12px;
            justify-content: center;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 6px 12px;
            font-family: var(--mono);
            font-size: 16px;
            border: 1px solid var(--border);
            color: var(--muted);
            transition: all .1s;
            border-radius: 2px;
        }

        .pagination a:hover      { border-color: var(--red); color: var(--red); }
        .pagination .active-page { border-color: var(--red); color: var(--red); background: rgba(227,0,15,.07); }

        /* ── UTILS ── */
        .mono { font-family: var(--mono); }
        .actions { display: flex; gap: 6px; }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border-hi); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--red); }
    </style>

    @stack('styles')
</head>

{{-- MODAL DE CONFIRMAÇÃO --}}
<div id="modal-confirm" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.75);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
    <div style="background:var(--card);border:1px solid var(--border);border-top:3px solid var(--red);max-width:420px;width:90%;padding:32px;position:relative;border-radius:0 0 4px 4px;">
        <div style="font-family:var(--mono);font-size:16px;color:var(--red);letter-spacing:3px;margin-bottom:16px;">⚠ // CONFIRMAR EXCLUSÃO</div>
        <div id="modal-msg" style="font-family:var(--cond);font-size:20px;font-weight:700;color:var(--text);margin-bottom:8px;"></div>
        <div style="font-family:var(--mono);font-size:16px;color:var(--muted);margin-bottom:28px;">Esta ação não pode ser desfeita.</div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="modalCancel()" class="btn btn-secondary">Cancelar</button>
            <button id="modal-confirm-btn" class="btn btn-danger" style="background:rgba(220,38,38,.1);">Confirmar Exclusão</button>
        </div>
    </div>
</div>

<script>
let _modalForm = null;
function confirmDelete(form, msg) {
    _modalForm = form;
    document.getElementById('modal-msg').textContent = msg;
    document.getElementById('modal-confirm').style.display = 'flex';
}
function modalCancel() {
    document.getElementById('modal-confirm').style.display = 'none';
    _modalForm = null;
}
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('modal-confirm-btn').addEventListener('click', function () {
        if (_modalForm) _modalForm.submit();
    });
    document.getElementById('modal-confirm').addEventListener('click', function (e) {
        if (e.target === this) modalCancel();
    });
});
</script>

<body>

{{-- ── SIDEBAR ────────────────────────────────────────── --}}
<aside class="sidebar">

    <div class="sidebar-header">
        <div class="sidebar-header-inner">
            <img src="/senai.webp" alt="SENAI">
            <div class="sidebar-product">MaintSys</div>
            <div class="sidebar-sub">Manutenção Industrial</div>
        </div>
    </div>

    <div class="sidebar-section">// sistema</div>
    <nav>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="icon">◆</span> Dashboard
        </a>
    </nav>

    <div class="sidebar-section">// equipamentos</div>
    <nav>
        @if(auth()->user()->hasPermission('maquinas.visualizar'))
        <a href="{{ route('maquinas.index') }}" class="{{ request()->routeIs('maquinas.*') ? 'active' : '' }}">
            <span class="icon">⚙</span> Máquinas
        </a>
        @endif
        @if(auth()->user()->hasPermission('historico.visualizar'))
        <a href="{{ route('historico.index') }}" class="{{ request()->routeIs('historico.*') ? 'active' : '' }}">
            <span class="icon">⏱</span> Histórico
        </a>
        @endif
    </nav>

    <div class="sidebar-section">// operações</div>
    <nav>
        @if(auth()->user()->hasPermission('ordens.visualizar'))
        <a href="{{ route('ordens.index') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
            <span class="icon">📋</span> Ordens de Serviço
        </a>
        @endif
        @if(auth()->user()->hasPermission('tecnicos.visualizar'))
        <a href="{{ route('tecnicos.index') }}" class="{{ request()->routeIs('tecnicos.*') ? 'active' : '' }}">
            <span class="icon">👤</span> Técnicos
        </a>
        @endif
    </nav>

    @if(auth()->user()->canManageUsers())
    <div class="sidebar-section">// admin</div>
    <nav>
        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <span class="icon">👥</span> Usuários
        </a>
        <a href="{{ route('acesso.index') }}" class="{{ request()->routeIs('acesso.*') ? 'active' : '' }}">
            <span class="icon">🔐</span> Gerenciar Acesso
        </a>
    </nav>
    @endif

    <div class="sidebar-footer">
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div class="user-email">{{ auth()->user()->email }}</div>
        <div class="user-role">{{ auth()->user()->role }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">↩ Sair do sistema</button>
        </form>
    </div>

</aside>

{{-- ── MAIN ─────────────────────────────────────────────── --}}
<div class="main">

    {{-- TOPBAR --}}
    <div class="topbar">
        <div class="breadcrumb">
            <span>SENAI</span>
            <span class="sep">/</span>
            {!! $__env->yieldContent('breadcrumb', '<span>dashboard</span>') !!}
        </div>

        @if(session('alerta'))
        <div style="font-family:var(--mono);font-size:16px;color:var(--red);padding:8px 14px;border:1px solid rgba(227,0,15,.3);background:rgba(227,0,15,.06);border-radius:3px;">
            {{ session('alerta') }}
        </div>
        @endif

        <button id="theme-toggle" class="theme-toggle" title="Alternar tema claro/escuro">
            <span id="theme-icon">🌙</span>
            <span id="theme-label">Escuro</span>
        </button>
    </div>

    {{-- CONTENT --}}
    <div class="content">

        @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">✕ {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

</div>

<script>
(function () {
    const toggle = document.getElementById('theme-toggle');
    const icon   = document.getElementById('theme-icon');
    const label  = document.getElementById('theme-label');
    const html   = document.documentElement;

    function apply(theme) {
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            icon.textContent  = '☀️';
            label.textContent = 'Claro';
        } else {
            html.removeAttribute('data-theme');
            icon.textContent  = '🌙';
            label.textContent = 'Escuro';
        }
        localStorage.setItem('theme', theme);
    }

    const saved = localStorage.getItem('theme');
    apply(saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));

    toggle.addEventListener('click', function () {
        apply(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
})();
</script>

@stack('scripts')
</body>
</html>
