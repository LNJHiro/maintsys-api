<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaintSys — @yield('title', 'Controle de Manutenção Industrial')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@300;400;500;600;700;900&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0a0d10;
            --surface:   #111418;
            --card:      #161b22;
            --border:    #21262d;
            --border-hi: #30363d;
            --text:      #e6edf3;
            --muted:     #7d8590;
            --accent:    #f0a500;
            --accent2:   #e36209;
            --green:     #3fb950;
            --red:       #f85149;
            --yellow:    #d29922;
            --blue:      #388bfd;
            --mono:      'Share Tech Mono', monospace;
            --sans:      'Barlow', sans-serif;
            --cond:      'Barlow Condensed', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            font-size: 14px;
            min-height: 100vh;
            display: flex;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo .sys-name {
            font-family: var(--mono);
            font-size: 20px;
            color: var(--accent);
            letter-spacing: 2px;
        }

        .sidebar-logo .sys-sub {
            font-family: var(--cond);
            font-size: 10px;
            color: var(--muted);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .sidebar-section {
            padding: 16px 12px 8px;
            font-family: var(--mono);
            font-size: 9px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: var(--muted);
            text-decoration: none;
            font-family: var(--cond);
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
            border-left: 2px solid transparent;
            transition: all 0.15s;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            color: var(--text);
            background: rgba(240,165,0,0.06);
            border-left-color: var(--accent);
        }

        .sidebar nav a .icon { font-size: 16px; width: 20px; text-align: center; }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid var(--border);
            font-size: 12px;
        }

        .sidebar-footer .user-name {
            font-family: var(--cond);
            font-weight: 600;
            color: var(--text);
        }

        .sidebar-footer .user-email {
            color: var(--muted);
            font-size: 11px;
        }

        .btn-logout {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 12px;
            background: transparent;
            border: 1px solid var(--border-hi);
            color: var(--muted);
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }

        .btn-logout:hover { border-color: var(--red); color: var(--red); }

        /* ── MAIN ── */
        .main {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ── TOPBAR ── */
        .topbar {
            height: 56px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 8px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--mono);
            font-size: 11px;
            color: var(--muted);
        }

        .topbar .breadcrumb span { color: var(--accent); }
        .topbar .breadcrumb .sep { color: var(--border-hi); }

        /* ── CONTENT ── */
        .content {
            padding: 28px;
            flex: 1;
        }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-left: 3px solid;
            font-family: var(--cond);
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success { background: rgba(63,185,80,.08); border-color: var(--green); color: var(--green); }
        .alert-error   { background: rgba(248,81,73,.08);  border-color: var(--red);   color: var(--red); }
        .alert-warning { background: rgba(240,165,0,.08);  border-color: var(--accent);color: var(--accent); }

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
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text);
        }

        .page-title small {
            display: block;
            font-family: var(--mono);
            font-size: 10px;
            color: var(--muted);
            letter-spacing: 2px;
            margin-bottom: 4px;
        }

        /* ── CARDS / STATS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 16px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--accent);
        }

        .stat-card.green::before  { background: var(--green); }
        .stat-card.red::before    { background: var(--red); }
        .stat-card.yellow::before { background: var(--yellow); }
        .stat-card.blue::before   { background: var(--blue); }

        .stat-label {
            font-family: var(--mono);
            font-size: 9px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-value {
            font-family: var(--cond);
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            color: var(--text);
        }

        /* ── TABLE ── */
        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 10px 14px;
            text-align: left;
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }

        tbody td {
            padding: 11px 14px;
            font-family: var(--sans);
            font-size: 13px;
            color: var(--text);
        }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid;
        }

        .badge-green  { color: var(--green);  border-color: rgba(63,185,80,.4);  background: rgba(63,185,80,.08); }
        .badge-red    { color: var(--red);    border-color: rgba(248,81,73,.4);  background: rgba(248,81,73,.08); }
        .badge-yellow { color: var(--yellow); border-color: rgba(210,153,34,.4); background: rgba(210,153,34,.08); }
        .badge-blue   { color: var(--blue);   border-color: rgba(56,139,253,.4); background: rgba(56,139,253,.08); }
        .badge-gray   { color: var(--muted);  border-color: var(--border-hi);    background: transparent; }
        .badge-orange { color: var(--accent2);border-color: rgba(227,98,9,.4);   background: rgba(227,98,9,.08); }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            font-family: var(--cond);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-decoration: none;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #000;
        }

        .btn-primary:hover { background: var(--accent2); border-color: var(--accent2); }

        .btn-secondary {
            background: transparent;
            border-color: var(--border-hi);
            color: var(--text);
        }

        .btn-secondary:hover { border-color: var(--muted); }

        .btn-danger {
            background: transparent;
            border-color: rgba(248,81,73,.4);
            color: var(--red);
        }

        .btn-danger:hover { background: rgba(248,81,73,.1); }

        .btn-sm { padding: 4px 10px; font-size: 11px; }

        /* ── FORM ── */
        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 28px;
            max-width: 720px;
        }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-family: var(--mono);
            font-size: 10px;
            color: var(--muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            background: var(--surface);
            border: 1px solid var(--border-hi);
            color: var(--text);
            font-family: var(--sans);
            font-size: 13px;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-control:focus { border-color: var(--accent); }

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
            color: var(--red);
        }

        /* ── PAGINATION ── */
        .pagination {
            display: flex;
            gap: 4px;
            padding: 12px;
            justify-content: center;
            border-top: 1px solid var(--border);
        }

        .pagination a, .pagination span {
            padding: 4px 10px;
            font-family: var(--mono);
            font-size: 11px;
            border: 1px solid var(--border);
            color: var(--muted);
            text-decoration: none;
            transition: all 0.1s;
        }

        .pagination a:hover { border-color: var(--accent); color: var(--accent); }
        .pagination .active-page { border-color: var(--accent); color: var(--accent); }

        /* ── MONO TEXT ── */
        .mono { font-family: var(--mono); }

        /* ── ACTIONS COL ── */
        .actions { display: flex; gap: 6px; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border-hi); }
        ::-webkit-scrollbar-thumb:hover { background: var(--muted); }
    </style>
    @stack('styles')
</head>

<!-- MODAL DE CONFIRMAÇÃO -->
<div id="modal-confirm" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
    <div style="background:var(--card);border:1px solid var(--red);max-width:420px;width:90%;padding:32px;position:relative;">
        <div style="font-family:var(--mono);font-size:10px;color:var(--red);letter-spacing:3px;margin-bottom:16px;">⚠ // CONFIRMAR EXCLUSÃO</div>
        <div id="modal-msg" style="font-family:var(--cond);font-size:18px;font-weight:600;color:var(--text);margin-bottom:8px;"></div>
        <div style="font-family:var(--mono);font-size:11px;color:var(--muted);margin-bottom:28px;">Esta ação não pode ser desfeita.</div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="modalCancel()" class="btn btn-secondary">Cancelar</button>
            <button id="modal-confirm-btn" class="btn btn-danger" style="background:rgba(248,81,73,.15);">Confirmar Exclusão</button>
        </div>
    </div>
</div>

<script>
    let _modalForm = null;

    function confirmDelete(form, msg) {
        _modalForm = form;
        document.getElementById('modal-msg').textContent = msg;
        const modal = document.getElementById('modal-confirm');
        modal.style.display = 'flex';
    }

    function modalCancel() {
        document.getElementById('modal-confirm').style.display = 'none';
        _modalForm = null;
    }

    document.getElementById('modal-confirm-btn').addEventListener('click', function() {
        if (_modalForm) _modalForm.submit();
    });

    document.getElementById('modal-confirm').addEventListener('click', function(e) {
        if (e.target === this) modalCancel();
    });
</script>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sys-name">MAINT<span style="color:var(--muted)">SYS</span></div>
        <div class="sys-sub">Manutenção Industrial 4.0</div>
    </div>

    <div class="sidebar-section">// sistema</div>
    <nav>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="icon">⬡</span> Dashboard
        </a>
    </nav>

    <div class="sidebar-section">// equipamentos</div>
    <nav>
        <a href="{{ route('maquinas.index') }}" class="{{ request()->routeIs('maquinas.*') ? 'active' : '' }}">
            <span class="icon">⚙</span> Máquinas
        </a>
        <a href="{{ route('historico.index') }}" class="{{ request()->routeIs('historico.*') ? 'active' : '' }}">
            <span class="icon">◎</span> Histórico
        </a>
    </nav>

    <div class="sidebar-section">// operações</div>
    <nav>
        <a href="{{ route('ordens.index') }}" class="{{ request()->routeIs('ordens.*') ? 'active' : '' }}">
            <span class="icon">▣</span> Ordens de Serviço
        </a>
        <a href="{{ route('tecnicos.index') }}" class="{{ request()->routeIs('tecnicos.*') ? 'active' : '' }}">
            <span class="icon">◈</span> Técnicos
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div class="user-email">{{ auth()->user()->email }}</div>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn-logout">[ SAIR ]</button>
        </form>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
        <div class="breadcrumb">
            <span>MAINTSYS</span>
            <span class="sep">/</span>
            {!! $__env->yieldContent('breadcrumb', '<span>dashboard</span>') !!}
        </div>

        @if(session('alerta'))
            <div style="margin-left:auto; font-family:var(--mono); font-size:11px; color:var(--accent); padding: 4px 12px; border: 1px solid rgba(240,165,0,.3); background: rgba(240,165,0,.06);">
                {{ session('alerta') }}
            </div>
        @endif
    </div>

    <!-- CONTENT -->
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

@stack('scripts')
</body>
</html>