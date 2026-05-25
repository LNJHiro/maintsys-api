<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MaintSys SENAI — Sistema de gestão de manutenção industrial.">
    <title>MaintSys — SENAI</title>

    <link rel="icon" type="image/webp" href="/senai.webp">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --red:       #E3000F;
            --red-dark:  #b80009;
            --red-deep:  #8a0007;
            --red-lite:  #ff1a26;
            --white:     #ffffff;
            --off-white: #fafafa;
            --gray-50:   #f5f5f5;
            --gray-100:  #ebebeb;
            --gray-200:  #d4d4d4;
            --gray-400:  #9a9a9a;
            --gray-600:  #555555;
            --gray-800:  #222222;
            --black:     #111111;
            --text:      #1a1a1a;
            --muted:     #666666;
            --border:    #e0e0e0;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Instrument Sans', system-ui, sans-serif;
            background: var(--white);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        /* ── PADRÃO DE LISTRAS SENAI ────────────────────────── */
        .senai-stripes {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .senai-stripes span {
            display: block;
            width: 22px;
            height: 3px;
            background: var(--white);
            border-radius: 2px;
            opacity: .85;
        }
        .senai-stripes span:nth-child(2) { width: 16px; }
        .senai-stripes span:nth-child(4) { width: 16px; }

        /* ── NAV ────────────────────────────────────────────── */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 48px;
            height: 68px;
            background: var(--white);
            border-bottom: 3px solid var(--red);
            box-shadow: 0 2px 16px rgba(0,0,0,.08);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo img {
            height: 34px;
            width: auto;
        }

        .logo-divider {
            width: 1px;
            height: 28px;
            background: var(--gray-200);
        }

        .logo-product {
            font-size: 20px;
            font-weight: 900;
            color: var(--text);
            letter-spacing: -0.3px;
        }

        .logo-product span {
            color: var(--red);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav-link {
            font-size: 15px;
            font-weight: 700;
            color: var(--muted);
            transition: color .18s;
            position: relative;
            padding-bottom: 2px;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--red);
            transition: width .2s;
        }

        .nav-link:hover { color: var(--red); }
        .nav-link:hover::after { width: 100%; }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline-nav {
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 800;
            color: var(--red);
            border: 1.5px solid var(--red);
            background: transparent;
            transition: all .18s;
            font-family: inherit;
            cursor: pointer;
        }

        .btn-outline-nav:hover {
            background: var(--red);
            color: var(--white);
        }

        .btn-red-nav {
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 800;
            background: var(--red);
            color: var(--white);
            border: none;
            cursor: pointer;
            transition: all .18s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-red-nav:hover {
            background: var(--red-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(227,0,15,.35);
        }

        /* ── HERO ────────────────────────────────────────────── */
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: calc(100vh - 68px);
            overflow: hidden;
        }

        /* Lado esquerdo — vermelho */
        .hero-left {
            background: var(--red);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 60px 80px 72px;
            overflow: hidden;
        }

        /* Listras decorativas na extremidade esquerda, inspiradas no logo */
        .hero-left::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 18px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.25) 0px,
                rgba(255,255,255,.25) 10px,
                transparent 10px,
                transparent 18px
            );
        }

        /* Listras decorativas na extremidade direita */
        .hero-left::after {
            content: '';
            position: absolute;
            right: -1px;
            top: 0;
            bottom: 0;
            width: 18px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.15) 0px,
                rgba(255,255,255,.15) 10px,
                transparent 10px,
                transparent 18px
            );
            z-index: 1;
        }

        .hero-left-inner {
            position: relative;
            z-index: 2;
        }

        .hero-senai-logo {
            margin-bottom: 36px;
        }

        .hero-senai-logo img {
            height: 48px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .hero-eyebrow {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(255,255,255,.7);
            margin-bottom: 16px;
        }

        .hero-title {
            font-size: clamp(40px, 4.2vw, 64px);
            font-weight: 900;
            color: var(--white);
            line-height: .97;
            letter-spacing: -2px;
            margin-bottom: 24px;
        }

        .hero-title em {
            font-style: normal;
            display: block;
            color: rgba(255,255,255,.75);
            font-size: .68em;
            letter-spacing: -1px;
            margin-top: 4px;
        }

        .hero-desc {
            font-size: 17px;
            color: rgba(255,255,255,.82);
            line-height: 1.65;
            max-width: 400px;
            margin-bottom: 40px;
        }

        .hero-cta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-white {
            padding: 14px 28px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 800;
            background: var(--white);
            color: var(--red);
            border: none;
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-white:hover {
            background: var(--off-white);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0,0,0,.2);
        }

        .btn-outline-white {
            padding: 13px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 800;
            background: transparent;
            color: var(--white);
            border: 1.5px solid rgba(255,255,255,.5);
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline-white:hover {
            border-color: var(--white);
            background: rgba(255,255,255,.1);
        }

        /* Lado direito — branco com stats */
        .hero-right {
            background: var(--gray-50);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 60px 80px 72px;
            border-left: 1px solid var(--gray-100);
        }

        .hero-tagline {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 20px;
        }

        .hero-right h2 {
            font-size: clamp(24px, 3vw, 38px);
            font-weight: 900;
            letter-spacing: -1px;
            line-height: 1.15;
            color: var(--text);
            margin-bottom: 20px;
        }

        .hero-right p {
            font-size: 16px;
            color: var(--muted);
            line-height: 1.65;
            max-width: 380px;
            margin-bottom: 48px;
        }

        /* Stats grid no lado direito */
        .stats-2x2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .stat-box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            transition: all .2s;
        }

        .stat-box:hover {
            border-color: var(--red);
            box-shadow: 0 4px 16px rgba(227,0,15,.1);
        }

        .stat-box-val {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: -2px;
            color: var(--red);
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-box-lbl {
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── BARRA DIVISÓRIA SENAI ───────────────────────────── */
        .senai-band {
            background: var(--red);
            padding: 20px 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            overflow: hidden;
            position: relative;
        }

        .senai-band::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 14px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.3) 0px,
                rgba(255,255,255,.3) 8px,
                transparent 8px,
                transparent 14px
            );
        }

        .senai-band::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 14px;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.3) 0px,
                rgba(255,255,255,.3) 8px,
                transparent 8px,
                transparent 14px
            );
        }

        .band-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
        }

        .band-icon {
            font-size: 18px;
        }

        /* ── FEATURES ────────────────────────────────────────── */
        .features {
            padding: 100px 72px;
            background: var(--white);
        }

        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 52px;
            gap: 20px;
        }

        .section-label {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 10px;
        }

        .section-title {
            font-size: clamp(28px, 3.8vw, 44px);
            font-weight: 900;
            letter-spacing: -1.5px;
            line-height: 1.1;
            color: var(--text);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feat-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 28px;
            transition: all .22s;
            position: relative;
            overflow: hidden;
            background: var(--white);
        }

        .feat-card-top {
            width: 100%;
            height: 3px;
            background: var(--gray-100);
            border-radius: 0 0 2px 2px;
            position: absolute;
            top: 0;
            left: 0;
            transition: background .22s;
        }

        .feat-card:hover {
            border-color: rgba(227,0,15,.3);
            box-shadow: 0 12px 32px rgba(227,0,15,.08);
            transform: translateY(-4px);
        }

        .feat-card:hover .feat-card-top {
            background: var(--red);
        }

        .feat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background: rgba(227,0,15,.08);
            display: grid;
            place-items: center;
            font-size: 22px;
            margin-bottom: 18px;
            margin-top: 8px;
        }

        .feat-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
        }

        .feat-desc {
            font-size: 14.5px;
            color: var(--muted);
            line-height: 1.6;
        }

        /* ── CTA FINAL ───────────────────────────────────────── */
        .cta-final {
            background: var(--black);
            padding: 88px 72px;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 48px;
            position: relative;
            overflow: hidden;
        }

        /* Listras verticais decorativas */
        .cta-final::before {
            content: '';
            position: absolute;
            top: 0; bottom: 0; left: 0;
            width: 12px;
            background: repeating-linear-gradient(
                to bottom,
                var(--red) 0px,
                var(--red) 10px,
                transparent 10px,
                transparent 18px
            );
        }

        .cta-final::after {
            content: '';
            position: absolute;
            top: 0; bottom: 0; right: 0;
            width: 12px;
            background: repeating-linear-gradient(
                to bottom,
                var(--red) 0px,
                var(--red) 10px,
                transparent 10px,
                transparent 18px
            );
        }

        .cta-text { padding-left: 32px; }

        .cta-eyebrow {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 12px;
        }

        .cta-final h2 {
            font-size: clamp(30px, 3.8vw, 48px);
            font-weight: 900;
            letter-spacing: -1.5px;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 12px;
        }

        .cta-final p {
            font-size: 16px;
            color: rgba(255,255,255,.55);
            max-width: 480px;
        }

        .cta-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 200px;
            padding-right: 32px;
        }

        .btn-red-lg {
            padding: 14px 28px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 800;
            background: var(--red);
            color: var(--white);
            border: none;
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
            text-align: center;
            display: block;
        }

        .btn-red-lg:hover {
            background: var(--red-lite);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(227,0,15,.4);
        }

        .btn-ghost-lg {
            padding: 13px 28px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 800;
            background: transparent;
            color: rgba(255,255,255,.65);
            border: 1.5px solid rgba(255,255,255,.2);
            cursor: pointer;
            transition: all .2s;
            font-family: inherit;
            text-align: center;
            display: block;
        }

        .btn-ghost-lg:hover {
            border-color: rgba(255,255,255,.5);
            color: var(--white);
        }

        /* ── FOOTER ──────────────────────────────────────────── */
        footer {
            background: #0a0a0a;
            padding: 56px 72px 32px;
            border-top: 3px solid var(--red);
        }

        .footer-top {
            display: grid;
            grid-template-columns: 1.8fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 48px;
        }

        .footer-brand img {
            height: 36px;
            width: auto;
            margin-bottom: 16px;
            filter: brightness(0) invert(1);
            opacity: .85;
        }

        .footer-brand-sub {
            font-size: 14px;
            font-weight: 700;
            color: rgba(255,255,255,.4);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .footer-desc {
            font-size: 14px;
            color: rgba(255,255,255,.4);
            line-height: 1.65;
            max-width: 280px;
        }

        .footer-col h5 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,.3);
            margin-bottom: 16px;
        }

        .footer-col ul { list-style: none; }
        .footer-col li { margin-bottom: 10px; }

        .footer-col a {
            font-size: 14px;
            color: rgba(255,255,255,.5);
            transition: color .18s;
        }

        .footer-col a:hover { color: var(--red); }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,.08);
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-copy {
            font-size: 13px;
            color: rgba(255,255,255,.3);
        }

        .footer-copy strong {
            color: var(--red);
        }

        .footer-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: rgba(255,255,255,.3);
        }

        .footer-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--red);
        }

        /* ── RESPONSIVE ──────────────────────────────────────── */
        @media (max-width: 1024px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 860px) {
            nav { padding: 0 24px; }
            .nav-links { display: none; }
            .hero { grid-template-columns: 1fr; }
            .hero-right { display: none; }
            .hero-left { padding: 60px 32px; min-height: calc(100vh - 68px); }
            .senai-band { padding: 18px 32px; flex-wrap: wrap; }
            .features { padding: 72px 24px; }
            .features-grid { grid-template-columns: 1fr; }
            .cta-final { padding: 64px 24px; grid-template-columns: 1fr; }
            .cta-text { padding-left: 24px; }
            .cta-actions { padding-right: 0; flex-direction: row; flex-wrap: wrap; min-width: unset; }
            .btn-red-lg, .btn-ghost-lg { flex: 1; }
            footer { padding: 48px 24px 28px; }
            .footer-top { grid-template-columns: 1fr 1fr; gap: 28px; }
        }

        @media (max-width: 540px) {
            nav { height: 60px; }
            .logo img { height: 28px; }
            .logo-product { font-size: 16px; }
            .footer-top { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

{{-- ── NAV ────────────────────────────────────────────────── --}}
<nav>
    <a href="{{ url('/') }}" class="logo">
        <img src="/senai.webp" alt="SENAI">
        <div class="logo-divider"></div>
        <span class="logo-product">Maint<span>Sys</span></span>
    </a>

    <div class="nav-links">
        <a href="#recursos" class="nav-link">Recursos</a>
        <a href="#suporte" class="nav-link">Suporte</a>
    </div>

    <div class="nav-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-red-nav">Dashboard →</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn-outline-nav">Sair</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-outline-nav">Entrar</a>
            @if(Route::has('register'))
            <a href="{{ route('register') }}" class="btn-red-nav">Criar conta →</a>
            @endif
        @endauth
    </div>
</nav>

{{-- ── HERO ────────────────────────────────────────────────── --}}
<section class="hero">

    {{-- Lado vermelho --}}
    <div class="hero-left">
        <div class="hero-left-inner">
            <div class="hero-senai-logo">
                <img src="/senai.webp" alt="SENAI">
            </div>

            <div class="hero-eyebrow">Sistema de Gestão · Manutenção Industrial</div>

            <h1 class="hero-title">
                MaintSys
                <em>Controle total da manutenção</em>
            </h1>

            <p class="hero-desc">
                Gerencie ordens de serviço, máquinas, técnicos e histórico de manutenção
                em uma plataforma centralizada, segura e eficiente.
            </p>

            <div class="hero-cta">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-white">→ Acessar dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-white">→ Entrar no sistema</a>
                    @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-outline-white">Criar conta</a>
                    @endif
                @endauth
                <a href="#recursos" class="btn-outline-white">Ver recursos ↓</a>
            </div>
        </div>
    </div>

    {{-- Lado branco --}}
    <div class="hero-right">
        <div class="hero-tagline">// indicadores do sistema</div>

        <h2>Eficiência e rastreabilidade<br>em tempo real</h2>

        <p>
            Identifique paradas críticas, acompanhe ordens abertas e mantenha
            o histórico completo de cada equipamento — tudo em um só lugar.
        </p>

        <div class="stats-2x2">
            <div class="stat-box">
                <div class="stat-box-val">100%</div>
                <div class="stat-box-lbl">Rastreabilidade</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-val">↑40%</div>
                <div class="stat-box-lbl">Produtividade</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-val">24/7</div>
                <div class="stat-box-lbl">Disponibilidade</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-val">0s</div>
                <div class="stat-box-lbl">Burocracia</div>
            </div>
        </div>
    </div>

</section>

{{-- ── BANDA SENAI ─────────────────────────────────────────── --}}
<div class="senai-band">
    <div class="band-item"><span class="band-icon">⚙</span> Manutenção Preventiva e Corretiva</div>
    <div class="band-item"><span class="band-icon">📋</span> Ordens de Serviço Integradas</div>
    <div class="band-item"><span class="band-icon">🔒</span> Controle de Acesso Granular</div>
    <div class="band-item"><span class="band-icon">📊</span> Dashboard em Tempo Real</div>
</div>

{{-- ── FEATURES ────────────────────────────────────────────── --}}
<section id="recursos" class="features">
    <div class="section-header">
        <div>
            <div class="section-label">Funcionalidades</div>
            <h2 class="section-title">Tudo para a gestão<br>da manutenção industrial</h2>
        </div>
    </div>

    <div class="features-grid">
        <div class="feat-card">
            <div class="feat-card-top"></div>
            <div class="feat-icon">⚙</div>
            <div class="feat-title">Ordens de Serviço</div>
            <p class="feat-desc">Abra, acompanhe e conclua ordens com prioridade, tipo (preventiva/corretiva) e status em tempo real.</p>
        </div>

        <div class="feat-card">
            <div class="feat-card-top"></div>
            <div class="feat-icon">🏭</div>
            <div class="feat-title">Gestão de Máquinas</div>
            <p class="feat-desc">Cadastre equipamentos, monitore o status operacional e identifique paradas críticas imediatamente.</p>
        </div>

        <div class="feat-card">
            <div class="feat-card-top"></div>
            <div class="feat-icon">👷</div>
            <div class="feat-title">Técnicos</div>
            <p class="feat-desc">Gerencie a equipe técnica ativa, contatos e vinculação às ordens de serviço abertas.</p>
        </div>

        <div class="feat-card">
            <div class="feat-card-top"></div>
            <div class="feat-icon">📊</div>
            <div class="feat-title">Histórico Completo</div>
            <p class="feat-desc">Consulte todas as manutenções realizadas com custo, tempo de parada e solução aplicada.</p>
        </div>

        <div class="feat-card">
            <div class="feat-card-top"></div>
            <div class="feat-icon">🔒</div>
            <div class="feat-title">Controle de Acesso</div>
            <p class="feat-desc">Permissões granulares por módulo e por usuário. Cada perfil acessa exatamente o necessário.</p>
        </div>

        <div class="feat-card">
            <div class="feat-card-top"></div>
            <div class="feat-icon">⚡</div>
            <div class="feat-title">Dashboard Inteligente</div>
            <p class="feat-desc">Visão geral com alertas de parada crítica, estatísticas e ações rápidas configuráveis por usuário.</p>
        </div>
    </div>
</section>

{{-- ── CTA FINAL ───────────────────────────────────────────── --}}
@guest
<section class="cta-final">
    <div class="cta-text">
        <div class="cta-eyebrow">// SENAI MaintSys</div>
        <h2>Pronto para otimizar<br>a manutenção?</h2>
        <p>Acesse o sistema e tenha controle total dos equipamentos, técnicos e ordens de serviço da sua unidade.</p>
    </div>
    <div class="cta-actions">
        <a href="{{ route('login') }}" class="btn-red-lg">→ Entrar no sistema</a>
        <a href="#recursos" class="btn-ghost-lg">Ver recursos</a>
    </div>
</section>
@endguest

{{-- ── FOOTER ──────────────────────────────────────────────── --}}
<footer id="suporte">
    <div class="footer-top">
        <div class="footer-brand">
            <img src="/senai.webp" alt="SENAI">
            <div class="footer-brand-sub">MaintSys</div>
            <p class="footer-desc">
                Sistema de gestão de manutenção industrial desenvolvido para maximizar a eficiência operacional.
            </p>
        </div>

        <div class="footer-col">
            <h5>Navegação</h5>
            <ul>
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a href="#recursos">Recursos</a></li>
                @auth
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                @endauth
            </ul>
        </div>

        <div class="footer-col">
            <h5>Suporte</h5>
            <ul>
                <li><a href="mailto:suporte@maintsys.com">Email de Suporte</a></li>
                <li><a href="#">Documentação</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h5>Legal</h5>
            <ul>
                <li><a href="#">Termos de Serviço</a></li>
                <li><a href="#">Privacidade</a></li>
                <li><a href="#">Cookies</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p class="footer-copy">© {{ date('Y') }} <strong>SENAI</strong> — MaintSys. Todos os direitos reservados.</p>
        <div class="footer-badge">
            <span class="footer-badge-dot"></span>
            Sistema operacional
        </div>
    </div>
</footer>

</body>
</html>
