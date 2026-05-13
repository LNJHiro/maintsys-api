<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MaintSys - Sistema de gestão de manutenção inteligente. Gerencie ordens de serviço, máquinas, técnicos e histórico em um único lugar.">
    <meta name="theme-color" content="#003087">
    
    <title>{{ config('app.name', 'MaintSys') }} - Sistema de Gestão de Manutenção</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23003087'/><text x='50' y='68' font-size='60' font-weight='bold' text-anchor='middle' fill='%23FFFFFF'>S</text></svg>">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />

    <style>
        :root {
            --bg: #F5F7FA;
            --bg-2: #FFFFFF;
            --bg-3: #EEF1F6;
            --text: #1A1A2E;
            --muted: #6B7280;
            --yellow: #003087;
            --yellow-2: #0052A5;
            --yellow-dark: #001F5A;
            --border: #D0D7DE;
        }

        [data-theme="dark"] {
            --bg: #0F1419;
            --bg-2: #1A1F2E;
            --bg-3: #24293F;
            --text: #E8E8F0;
            --muted: #9CA3AF;
            --yellow: #4A9EFF;
            --yellow-2: #6BB4FF;
            --yellow-dark: #2A6FD9;
            --border: #2D3748;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            font-family: 'Instrument Sans', Arial, sans-serif;
            background:
                radial-gradient(circle at top center, rgba(0, 48, 135, 0.06), transparent 35%),
                linear-gradient(180deg, var(--bg), #E8ECF5);
            color: var(--text);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* ==================== NAVBAR ==================== */
        .navbar {
            width: 100%;
            height: 86px;
            padding: 0 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            font-weight: 900;
            font-size: 24px;
            letter-spacing: 0.5px;
            transition: 0.2s ease;
        }

        .brand:hover {
            color: var(--yellow);
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--yellow);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 20px;
            transition: 0.2s ease;
        }

        .brand:hover .brand-icon {
            transform: scale(1.05) rotate(10deg);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-link {
            color: #555555;
            font-size: 15px;
            font-weight: 500;
            transition: 0.2s ease;
            position: relative;
        }

        [data-theme="dark"] .nav-link {
            color: #B0B5C5;
        }

        .theme-toggle-header {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
            padding: 6px 12px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
        }

        .theme-toggle-header:hover {
            background: rgba(0, 48, 135, 0.1);
            border-color: var(--yellow);
            color: var(--yellow);
        }

        .nav-link:hover {
            color: var(--yellow);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--yellow);
            transition: width 0.2s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-top {
            background: var(--yellow);
            color: #FFFFFF;
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 800;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--yellow);
        }

        .btn-top:hover {
            background: var(--yellow-2);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 48, 135, 0.2);
        }

        .btn-top.logout {
            background: transparent;
            color: #555555;
            border: 1px solid rgba(0, 0, 0, 0.15);
            padding: 10px 18px;
        }

        .btn-top.logout:hover {
            background: rgba(207, 34, 46, 0.08);
            color: #ff6b6b;
            border-color: #ff6b6b;
        }

        /* ==================== HERO ==================== */
        .hero {
            min-height: calc(100vh - 86px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 24px;
            text-align: center;
        }

        .hero-inner {
            max-width: 980px;
            width: 100%;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            border: 1px solid rgba(0, 48, 135, 0.35);
            background: rgba(0, 48, 135, 0.08);
            color: var(--yellow);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 42px;
            animation: slideUp 0.6s ease-out;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--yellow);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .hero h1 {
            font-size: clamp(54px, 8vw, 116px);
            line-height: 0.93;
            font-weight: 900;
            letter-spacing: -5px;
            margin-bottom: 34px;
            animation: slideUp 0.6s ease-out 0.1s backwards;
        }

        .hero h1 span {
            color: var(--yellow);
            display: block;
        }

        .hero p {
            color: var(--muted);
            max-width: 620px;
            margin: 0 auto;
            font-size: 18px;
            line-height: 1.7;
            animation: slideUp 0.6s ease-out 0.2s backwards;
        }

        .hero-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 38px;
            animation: slideUp 0.6s ease-out 0.3s backwards;
        }

        .btn-primary,
        .btn-secondary,
        .btn-outline {
            min-width: 190px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 15px 26px;
            border-radius: 11px;
            font-size: 16px;
            font-weight: 800;
            transition: 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--yellow);
            color: #FFFFFF;
            border: 1px solid var(--yellow);
        }

        .btn-primary:hover {
            background: var(--yellow-2);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 48, 135, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: var(--yellow);
            border: 1px solid rgba(0, 48, 135, 0.45);
        }

        .btn-secondary:hover {
            background: rgba(0, 48, 135, 0.09);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--text);
            border: 1px solid rgba(0, 0, 0, 0.18);
        }

        .btn-outline:hover {
            background: rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
        }

        /* ==================== METRICS ==================== */
        .metrics {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            margin-top: 70px;
            animation: slideUp 0.6s ease-out 0.4s backwards;
        }

        .metric {
            padding: 0 38px;
            border-right: 1px solid var(--border);
        }

        .metric:last-child {
            border-right: none;
        }

        .metric-value {
            font-size: 30px;
            font-weight: 900;
            color: var(--text);
            letter-spacing: -1px;
        }

        .metric-value span {
            color: var(--yellow);
        }

        .metric-label {
            color: var(--muted);
            font-size: 13px;
            margin-top: 6px;
        }

        /* ==================== RECURSOS SECTION ==================== */
        .recursos-section {
            padding: 80px 24px;
            background: var(--bg-3);
            border-top: 1px solid var(--border);
        }

        .recursos-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .recursos-title {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 18px;
            color: var(--text);
            text-align: center;
        }

        .recursos-subtitle {
            color: var(--muted);
            font-size: 17px;
            max-width: 680px;
            margin: 0 auto 60px;
            text-align: center;
        }

        .recursos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .recurso-card {
            background: var(--bg-2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            transition: 0.3s ease;
        }

        .recurso-card:hover {
            border-color: rgba(0, 48, 135, 0.3);
            background: rgba(0, 48, 135, 0.02);
            transform: translateY(-4px);
        }

        .recurso-card h3 {
            color: var(--yellow);
            margin-bottom: 10px;
            font-size: 18px;
        }

        .recurso-card p {
            color: var(--muted);
            line-height: 1.6;
            font-size: 14px;
        }

        /* ==================== FOOTER ==================== */
        .footer {
            padding: 50px 24px;
            background: var(--bg);
            border-top: 1px solid var(--border);
        }

        .footer-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-col h4 {
            color: var(--text);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 10px;
        }

        .footer-col a {
            color: var(--muted);
            font-size: 14px;
            transition: 0.2s ease;
        }

        .footer-col a:hover {
            color: var(--yellow);
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .footer-brand-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: var(--yellow);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 16px;
        }

        .footer-brand span {
            font-weight: 900;
            color: var(--text);
        }

        .footer-description {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
            max-width: 300px;
        }

        .footer-divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 24px;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-copyright {
            color: var(--muted);
            font-size: 13px;
        }

        .footer-social {
            display: flex;
            gap: 16px;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .navbar {
                padding: 0 20px;
                height: 70px;
            }

            .brand {
                font-size: 20px;
            }

            .brand-icon {
                width: 30px;
                height: 30px;
                font-size: 16px;
            }

            .nav-right {
                gap: 12px;
            }

            .nav-link {
                display: none;
            }

            .btn-top {
                padding: 10px 16px;
                font-size: 14px;
            }

            .hero {
                min-height: calc(100vh - 70px);
                padding: 60px 16px;
            }

            .hero h1 {
                letter-spacing: -2px;
            }

            .hero p {
                font-size: 16px;
            }

            .hero-actions {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary,
            .btn-outline {
                width: 100%;
                min-width: unset;
            }

            .metrics {
                flex-direction: column;
                gap: 24px;
                margin-top: 50px;
            }

            .metric {
                border-right: none;
                padding: 0;
            }

            .recursos-section {
                padding: 60px 16px;
            }

            .recursos-title {
                font-size: 32px;
            }

            .recursos-subtitle {
                margin-bottom: 40px;
            }

            .recursos-grid {
                gap: 16px;
            }

            .recurso-card {
                padding: 20px;
            }

            .footer-content {
                gap: 24px;
                margin-bottom: 24px;
            }

            .footer-bottom {
                justify-content: center;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 0 16px;
            }

            .brand {
                font-size: 18px;
                gap: 8px;
            }

            .brand-icon {
                width: 28px;
                height: 28px;
            }

            .hero h1 {
                font-size: clamp(36px, 10vw, 60px);
                line-height: 1;
            }

            .hero p {
                font-size: 15px;
            }

            .hero-actions {
                gap: 12px;
            }

            .badge {
                font-size: 12px;
                padding: 6px 14px;
                margin-bottom: 24px;
            }

            .recursos-title {
                font-size: 28px;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="{{ url('/') }}" class="brand" aria-label="SENAI MaintSys - Voltar para home">
            <img src="/senai.webp" alt="SENAI" style="height:30px;width:auto">
            <span>MaintSys</span>
        </a>

        <nav class="nav-right" aria-label="Navegação principal">
            <a href="#recursos" class="nav-link">Recursos</a>
            <a href="#suporte" class="nav-link">Suporte</a>

            <button id="theme-toggle-welcome" class="theme-toggle-header" title="Alternar tema">
                <span id="theme-icon-welcome">🌙</span>
                <span>Escuro</span>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="btn-top" title="Ir para o painel de controle">
                    Dashboard
                </a>
                
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-top logout" title="Sair da conta" aria-label="Fazer logout">
                        Sair
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-link" title="Entrar na sua conta">
                    Entrar
                </a>

                <a href="{{ route('register') }}" class="btn-top" title="Criar uma nova conta">
                    Criar conta
                </a>
            @endauth
        </nav>
    </header>

    <main>
        <section class="hero">
            <section class="hero-inner">
                <div class="badge">
                    <span class="badge-dot"></span>
                    Sistema de Gestão de Manutenção
                </div>

                <h1>
                    Manutenção inteligente,
                    <span>sem complicação</span>
                </h1>

                <p>
                    Gerencie ordens de serviço, máquinas, técnicos e histórico em
                    um único lugar. Eficiência total para sua operação.
                </p>

                <div class="hero-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary">
                            → Acessar dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary">
                            → Acessar o sistema
                        </a>

                        <a href="{{ route('register') }}" class="btn-secondary">
                            + Criar conta
                        </a>
                    @endauth

                    <a href="#recursos" class="btn-outline">
                        Ver recursos ▼
                    </a>
                </div>

                <div class="metrics">
                    <div class="metric">
                        <div class="metric-value">100<span>%</span></div>
                        <div class="metric-label">Rastreabilidade</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><span>↑</span>40%</div>
                        <div class="metric-label">Produtividade</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value">24/7</div>
                        <div class="metric-label">Disponibilidade</div>
                    </div>
                </div>
            </section>
        </section>

        <section id="recursos" class="recursos-section" aria-labelledby="recursos-title">
            <div class="recursos-container">
                <h2 id="recursos-title" class="recursos-title">Recursos do sistema</h2>

                <p class="recursos-subtitle">
                    Controle completo para manutenção preventiva, corretiva, técnicos, máquinas e histórico de intervenções.
                </p>

                <div class="recursos-grid">
                    <div class="recurso-card">
                        <h3>Ordens de Serviço</h3>
                        <p>Abra, acompanhe e conclua ordens de manutenção com prioridade e status.</p>
                    </div>

                    <div class="recurso-card">
                        <h3>Máquinas</h3>
                        <p>Cadastre máquinas, acompanhe status e identifique paradas críticas.</p>
                    </div>

                    <div class="recurso-card">
                        <h3>Técnicos</h3>
                        <p>Gerencie técnicos ativos, especialidades, contatos e atendimentos.</p>
                    </div>

                    <div class="recurso-card">
                        <h3>Histórico</h3>
                        <p>Consulte manutenções realizadas, soluções aplicadas, custos e tempo de parada.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer" id="suporte">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-col">
                    <div class="footer-brand">
                        <span class="footer-brand-icon">S</span>
                        <span>SENAI MaintSys</span>
                    </div>
                    <p class="footer-description">
                        Sistema inteligente de gestão de manutenção para maximizar a eficiência operacional.
                    </p>
                </div>

                <div class="footer-col">
                    <h4>Navegação</h4>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="#recursos">Recursos</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        @endauth
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Suporte</h4>
                    <ul>
                        <li><a href="mailto:suporte@maintsys.com" title="Enviar email de suporte">Email de Suporte</a></li>
                        <li><a href="#" title="Ver documentação">Documentação</a></li>
                        <li><a href="#" title="Ver FAQ">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#" title="Ver termos de serviço">Termos de Serviço</a></li>
                        <li><a href="#" title="Ver política de privacidade">Privacidade</a></li>
                        <li><a href="#" title="Ver cookies">Cookies</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-divider"></div>

            <div class="footer-bottom">
                <p class="footer-copyright">
                    © {{ date('Y') }} SENAI MaintSys. Todos os direitos reservados.
                </p>
                <div class="footer-social">
                    <a href="#" aria-label="GitHub" title="Siga no GitHub">GitHub</a>
                    <a href="#" aria-label="LinkedIn" title="Siga no LinkedIn">LinkedIn</a>
                </div>
            </div>
        </div>
    </footer>

<script>
(function() {
    const themeToggle = document.getElementById('theme-toggle-welcome');
    const themeIcon = document.getElementById('theme-icon-welcome');
    const html = document.documentElement;

    function initTheme() {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved || (prefersDark ? 'dark' : 'light');
        applyTheme(theme);
    }

    function applyTheme(theme) {
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            themeIcon.textContent = '☀️';
            themeToggle.querySelector('span:last-child').textContent = 'Claro';
        } else {
            html.removeAttribute('data-theme');
            themeIcon.textContent = '🌙';
            themeToggle.querySelector('span:last-child').textContent = 'Escuro';
        }
        localStorage.setItem('theme', theme);
    }

    function toggleTheme() {
        const current = html.getAttribute('data-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
    }

    themeToggle.addEventListener('click', toggleTheme);
    initTheme();
})();
</script>

</body>
</html>