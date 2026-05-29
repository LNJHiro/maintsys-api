<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MaintSys — SENAI')</title>

    <script>
    (function(){var t=localStorage.getItem('theme');if(t==='dark'||(t===null&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.setAttribute('data-theme','dark');}})();
    </script>

    <link rel="icon" href="/senai.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --red:       #E3000F;
            --red-dark:  #b80009;
            --red-lite:  #ff2030;
            --white:     #ffffff;
            --bg:        #f5f6f8;
            --surface:   #ffffff;
            --card:      #ffffff;
            --border:    #e0e4ea;
            --border-hi: #c8cdd6;
            --text:      #111827;
            --muted:     #6b7280;
            --error:     #dc2626;
            --success:   #16a34a;
            --mono:      'Share Tech Mono', monospace;
            --sans:      'Barlow', sans-serif;
            --cond:      'Barlow Condensed', sans-serif;
        }

        [data-theme="dark"] {
            --white:     #ffffff;
            --bg:        #0d1117;
            --surface:   #161b22;
            --card:      #1f2430;
            --border:    #2a3040;
            --border-hi: #374151;
            --text:      #e6edf3;
            --muted:     #8b949e;
            --red:       #ff3b47;
            --red-dark:  #cc0010;
        }

        html, body {
            min-height: 100vh;
            font-family: var(--sans);
        }

        body {
            display: grid;
            grid-template-columns: 420px 1fr;
            background: var(--bg);
            color: var(--text);
        }

        a { text-decoration: none; color: inherit; }

        /* ── PAINEL ESQUERDO VERMELHO ── */
        .auth-left {
            background: var(--red);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 40px 44px;
            position: relative;
            overflow: hidden;
        }

        /* Listras SENAI na borda esquerda */
        .auth-left::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 12px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.3) 0, rgba(255,255,255,.3) 8px,
                transparent 8px, transparent 15px
            );
        }

        .auth-left::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 12px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.15) 0, rgba(255,255,255,.15) 8px,
                transparent 8px, transparent 15px
            );
        }

        .auth-left-inner {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .auth-left img {
            height: 40px;
            width: auto;
            filter: brightness(0) invert(1);
            margin-bottom: 48px;
        }

        .auth-left-headline {
            font-family: var(--cond);
            font-size: 38px;
            font-weight: 800;
            color: var(--white);
            line-height: 1.05;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        .auth-left-sub {
            font-size: 14px;
            color: rgba(255,255,255,.72);
            line-height: 1.65;
            max-width: 300px;
            margin-bottom: auto;
        }

        .auth-left-pills {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 40px;
        }

        .auth-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: rgba(255,255,255,.8);
            font-weight: 500;
        }

        .auth-pill-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: rgba(255,255,255,.15);
            display: grid;
            place-items: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        /* ── PAINEL DIREITO BRANCO ── */
        .auth-right {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bg);
        }

        /* topbar do painel direito */
        .auth-topbar {
            height: 56px;
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
        }

        [data-theme="dark"] .auth-topbar {
            background: var(--surface);
            border-color: var(--border);
        }

        .auth-topbar-link {
            font-size: 14px;
            color: var(--muted);
            transition: color .15s;
        }

        .auth-topbar-link:hover { color: var(--red); }

        .theme-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: transparent;
            color: var(--muted);
            font-size: 13px;
            font-family: var(--sans);
            cursor: pointer;
            transition: all .15s;
        }

        .theme-btn:hover { border-color: var(--red); color: var(--red); }

        .auth-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 400px;
        }

        .auth-form-title {
            font-family: var(--cond);
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .auth-form-sub {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 32px;
        }

        /* ── FORM ELEMENTS ── */
        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text);
            font-family: var(--sans);
            font-size: 14px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        [data-theme="dark"] .form-group input {
            background: var(--card);
            border-color: var(--border-hi);
        }

        .form-group input::placeholder { color: var(--muted); }

        .form-group input:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(227,0,15,.12);
        }

        [data-theme="dark"] .form-group input:focus {
            box-shadow: 0 0 0 3px rgba(255,59,71,.15);
        }

        .form-error {
            display: block;
            margin-top: 5px;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--error);
        }

        .btn-submit {
            width: 100%;
            padding: 12px 20px;
            background: var(--red);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-family: var(--cond);
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all .18s;
            margin-top: 8px;
        }

        .btn-submit:hover {
            background: var(--red-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(227,0,15,.3);
        }

        .btn-submit:active { transform: translateY(0); }

        .auth-alt {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: var(--muted);
        }

        .auth-alt a {
            color: var(--red);
            font-weight: 700;
            transition: opacity .15s;
        }

        .auth-alt a:hover { opacity: .75; }

        .form-row-2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 14px;
            color: var(--muted);
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            accent-color: var(--red);
            cursor: pointer;
        }

        .forgot {
            font-size: 14px;
            color: var(--muted);
            transition: color .15s;
        }
        .forgot:hover { color: var(--red); }

        /* alerts */
        .alert {
            padding: 10px 14px;
            border-left: 3px solid;
            border-radius: 0 4px 4px 0;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .alert-error   { background: rgba(220,38,38,.07);  border-color: var(--error);   color: var(--error); }
        .alert-success { background: rgba(22,163,74,.07);  border-color: var(--success); color: var(--success); }

        /* ── RESPONSIVE ── */
        @media (max-width: 860px) {
            body { grid-template-columns: 1fr; }
            .auth-left { display: none; }
            .auth-topbar { padding: 0 24px; }
            .auth-content { padding: 32px 24px; }
        }
    </style>

    @yield('styles')
</head>
<body>

<div class="auth-left">
    <div class="auth-left-inner">
        <img src="/senai.webp" alt="SENAI">

        <div class="auth-left-headline">
            Controle total<br>da manutenção<br>industrial
        </div>

        <p class="auth-left-sub">
            Gerencie ordens de serviço, máquinas, técnicos e histórico em uma única plataforma.
        </p>

        <div class="auth-left-pills">
            <div class="auth-pill">
                <div class="auth-pill-icon">⚙</div>
                Ordens de serviço integradas
            </div>
            <div class="auth-pill">
                <div class="auth-pill-icon">🏭</div>
                Gestão de equipamentos
            </div>
            <div class="auth-pill">
                <div class="auth-pill-icon">🔒</div>
                Controle de acesso granular
            </div>
            <div class="auth-pill">
                <div class="auth-pill-icon">📊</div>
                Dashboard em tempo real
            </div>
        </div>
    </div>
</div>

<div class="auth-right">
    <div class="auth-topbar">
        <a href="{{ url('/') }}" class="auth-topbar-link">← Voltar ao início</a>
        <button id="theme-btn" class="theme-btn">
            <span id="theme-icon">🌙</span>
            <span id="theme-label">Escuro</span>
        </button>
    </div>

    <div class="auth-content">
        @yield('content')
    </div>
</div>

<script>
(function () {
    const btn   = document.getElementById('theme-btn');
    const icon  = document.getElementById('theme-icon');
    const label = document.getElementById('theme-label');
    const html  = document.documentElement;

    function apply(t) {
        if (t === 'dark') {
            html.setAttribute('data-theme', 'dark');
            icon.textContent  = '☀️';
            label.textContent = 'Claro';
        } else {
            html.removeAttribute('data-theme');
            icon.textContent  = '🌙';
            label.textContent = 'Escuro';
        }
        localStorage.setItem('theme', t);
    }

    const saved = localStorage.getItem('theme');
    apply(saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
    btn.addEventListener('click', () => apply(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));
})();
</script>

@yield('scripts')
</body>
</html>
