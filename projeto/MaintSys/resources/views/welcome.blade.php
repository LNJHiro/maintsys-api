<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MAINTSYS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --accent: #e8c547;
            --accent2: #f0d76a;
            --text: #f0ede6;
            --muted: #8a8f9e;
            --border: rgba(255,255,255,0.07);
        }

        html, body {
            background: #0d0f14;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .glow {
            position: absolute; top: -150px; left: 50%; transform: translateX(-50%);
            width: 700px; height: 400px;
            background: radial-gradient(ellipse at center, rgba(232,197,71,0.07) 0%, transparent 70%);
            pointer-events: none;
        }

        nav {
            position: relative; z-index: 10;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.5rem 3rem;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            display: flex; align-items: center; gap: 10px;
            font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.25rem;
            letter-spacing: 0.08em; color: var(--text);
            text-decoration: none;
        }

        .logo-icon {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
        }

        .nav-links { display: flex; align-items: center; gap: 2rem; }

        .nav-links a {
            color: var(--muted); text-decoration: none; font-size: 0.9rem;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--text); }

        .btn-nav {
            background: var(--accent) !important;
            color: #0d0f14 !important;
            border-radius: 8px !important;
            padding: 0.5rem 1.3rem !important;
            font-weight: 500 !important;
        }
        .btn-nav:hover { background: var(--accent2) !important; color: #0d0f14 !important; }

        .hero {
            position: relative; z-index: 5;
            display: flex; flex-direction: column; align-items: center;
            text-align: center;
            padding: 6rem 2rem 4rem;
        }

        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(232,197,71,0.1);
            border: 1px solid rgba(232,197,71,0.25);
            border-radius: 999px;
            padding: 0.35rem 1.1rem;
            font-size: 0.8rem; color: var(--accent);
            margin-bottom: 2rem;
        }

        .badge-dot {
            width: 6px; height: 6px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 2s infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.5rem, 6vw, 5rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
            max-width: 780px;
            color: var(--text);
        }

        h1 em { color: var(--accent); font-style: normal; }

        .subtitle {
            margin-top: 1.5rem;
            font-size: 1.05rem;
            color: var(--muted);
            max-width: 500px;
            line-height: 1.7;
        }

        .cta-group {
            display: flex; gap: 1rem; margin-top: 2.5rem;
            flex-wrap: wrap; justify-content: center;
        }

        .btn-primary {
            background: var(--accent);
            color: #0d0f14;
            border: none; border-radius: 10px;
            padding: 0.85rem 2rem;
            font-family: 'DM Sans', sans-serif;
            font-weight: 500; font-size: 1rem;
            cursor: pointer; text-decoration: none;
            transition: background 0.2s, transform 0.15s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { background: var(--accent2); transform: translateY(-2px); }

        .btn-secondary {
            background: transparent;
            color: var(--text);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 0.85rem 2rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            cursor: pointer; text-decoration: none;
            transition: border-color 0.2s, transform 0.15s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-secondary:hover { border-color: rgba(255,255,255,0.25); transform: translateY(-2px); }

        .stats {
            display: flex; gap: 3rem; margin-top: 4rem;
            flex-wrap: wrap; justify-content: center; align-items: center;
        }

        .stat { text-align: center; }
        .stat-num {
            font-family: 'Syne', sans-serif;
            font-size: 2rem; font-weight: 700;
            color: var(--text);
        }
        .stat-num em { color: var(--accent); font-style: normal; }
        .stat-label { font-size: 0.8rem; color: var(--muted); margin-top: 2px; }
        .divider { width: 1px; height: 40px; background: var(--border); }

        .features {
            position: relative; z-index: 5;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            margin: 0 3rem 3rem;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            background: var(--border);
        }

        .feature {
            background: #13161d;
            padding: 2rem;
            transition: background 0.2s;
        }
        .feature:hover { background: #1a1e28; }

        .feature-icon {
            width: 44px; height: 44px;
            background: rgba(232,197,71,0.1);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.2rem;
        }
        .feature-icon svg {
            width: 22px; height: 22px;
            stroke: var(--accent); fill: none;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }

        .feature h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1rem; font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        .feature p { font-size: 0.875rem; color: var(--muted); line-height: 1.6; }

        footer {
            position: relative; z-index: 5;
            text-align: center;
            padding: 2rem;
            border-top: 1px solid var(--border);
            font-size: 0.82rem; color: var(--muted);
        }

        @media (max-width: 900px) {
            .features { grid-template-columns: repeat(2, 1fr); margin: 0 1.5rem 2rem; }
        }
        @media (max-width: 600px) {
            nav { padding: 1rem 1.5rem; }
            .nav-links .hide-mobile { display: none; }
            .features { grid-template-columns: 1fr; margin: 0 1rem 2rem; }
            .stats { gap: 1.5rem; }
            .divider { display: none; }
        }
    </style>
</head>
<body>

<div class="glow"></div>

<nav>
    <a href="{{ url('/') }}" class="logo">
        <div class="logo-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0d0f14" stroke-width="2.5" stroke-linecap="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
            </svg>
        </div>
        {{ config('app.name', 'MAINTSYS') }}
    </a>

    <div class="nav-links">
        <a href="#features" class="hide-mobile">Recursos</a>
        <a href="#" class="hide-mobile">Suporte</a>
        @if (Route::has('login'))
            <a href="{{ route('login') }}" class="btn-nav">Entrar</a>
        @endif
    </div>
</nav>

<section class="hero">
    <div class="badge">
        <div class="badge-dot"></div>
        Sistema de Gestão de Manutenção
    </div>

    <h1>Manutenção inteligente,<br><em>sem complicação</em></h1>

    <p class="subtitle">
        Gerencie ordens de serviço, máquinas, técnicos e histórico em um único lugar. Eficiência total para sua operação.
    </p>

    <div class="cta-group">
        @if (Route::has('login'))
        <a href="{{ route('login') }}" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Acessar o sistema
        </a>
        @endif
        <a href="#features" class="btn-secondary">
            Ver recursos
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
        </a>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-num">100<em>%</em></div>
            <div class="stat-label">Rastreabilidade</div>
        </div>
        <div class="divider"></div>
        <div class="stat">
            <div class="stat-num"><em>↑</em>40%</div>
            <div class="stat-label">Produtividade</div>
        </div>
        <div class="divider"></div>
        <div class="stat">
            <div class="stat-num">24<em>/7</em></div>
            <div class="stat-label">Disponibilidade</div>
        </div>
    </div>
</section>

<div class="features" id="features">
    <div class="feature">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
        <h3>Dashboard em tempo real</h3>
        <p>Visualize indicadores, ordens abertas e status das máquinas em um painel centralizado.</p>
    </div>

    <div class="feature">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <h3>Gestão de máquinas</h3>
        <p>Cadastre equipamentos, vincule histórico e acompanhe o ciclo de vida de cada ativo.</p>
    </div>

    <div class="feature">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h3>Controle de técnicos</h3>
        <p>Atribua chamados, monitore disponibilidade e acompanhe a performance da equipe.</p>
    </div>

    <div class="feature">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <h3>Ordens de serviço</h3>
        <p>Abra, gerencie e encerre ordens com histórico completo e tempo de execução.</p>
    </div>

    <div class="feature">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <h3>Histórico e relatórios</h3>
        <p>Acesse registros completos e gere relatórios de desempenho para tomada de decisão.</p>
    </div>

    <div class="feature">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h3>Controle de acesso</h3>
        <p>Perfis para administradores, técnicos e gestores com permissões granulares.</p>
    </div>
</div>

<footer>
    &copy; {{ date('Y') }} {{ config('app.name', 'MAINTSYS') }} &mdash; Sistema de Gestão de Manutenção
</footer>

</body>
</html>