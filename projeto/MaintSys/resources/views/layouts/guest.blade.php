<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#003087">
    
    <title>@yield('title', 'MaintSys')</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%23003087'/><text x='50' y='68' font-size='60' font-weight='bold' text-anchor='middle' fill='%23FFFFFF'>S</text></svg>">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />

    <style>
        :root {
            --bg: #F5F7FA;
            --bg-2: #FFFFFF;
            --text: #1A1A2E;
            --muted: #6B7280;
            --yellow: #003087;
            --yellow-2: #0052A5;
            --border: #D0D7DE;
            --error: #CF222E;
            --success: #2DA44E;
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
            display: flex;
            flex-direction: column;
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
        }

        .nav-link:hover {
            color: var(--yellow);
        }

        .btn-top {
            background: var(--yellow);
            color: #FFFFFF;
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 800;
            transition: 0.2s ease;
            border: 1px solid var(--yellow);
            cursor: pointer;
        }

        .btn-top:hover {
            background: var(--yellow-2);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 48, 135, 0.2);
        }

        /* ==================== CONTAINER ==================== */
        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 480px;
            background: var(--bg-2);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px 32px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: var(--yellow);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 32px;
            margin: 0 auto 32px;
        }

        .auth-title {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 12px;
            text-align: center;
        }

        .auth-subtitle {
            color: var(--muted);
            font-size: 15px;
            text-align: center;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #F5F7FA;
            color: var(--text);
            font-size: 15px;
            font-family: 'Instrument Sans', Arial, sans-serif;
            transition: 0.2s ease;
        }

        .form-group input::placeholder {
            color: #9CA3AF;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--yellow);
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(0, 48, 135, 0.1);
        }

        .form-error {
            font-size: 13px;
            color: var(--error);
            margin-top: 6px;
            display: none;
        }

        .form-group.has-error input {
            border-color: var(--error);
        }

        .form-group.has-error .form-error {
            display: block;
        }

        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            background: var(--yellow);
            color: #FFFFFF;
            border: 1px solid var(--yellow);
            border-radius: 10px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s ease;
            margin-top: 28px;
        }

        .btn-submit:hover {
            background: var(--yellow-2);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 48, 135, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 28px 0;
            color: var(--muted);
            font-size: 13px;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--muted);
        }

        .auth-footer a {
            color: var(--yellow);
            font-weight: 600;
            transition: 0.2s ease;
        }

        .auth-footer a:hover {
            color: var(--yellow-2);
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: var(--error);
            display: block;
        }

        .alert-success {
            background: rgba(81, 207, 102, 0.1);
            border: 1px solid rgba(81, 207, 102, 0.3);
            color: var(--success);
            display: block;
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

            .auth-container {
                padding: 40px 16px;
            }

            .auth-card {
                padding: 32px 24px;
                border-radius: 16px;
            }

            .auth-title {
                font-size: 24px;
            }

            .auth-subtitle {
                font-size: 14px;
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

            .auth-card {
                padding: 24px 16px;
            }

            .auth-title {
                font-size: 22px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            .btn-submit {
                margin-top: 20px;
                padding: 12px 20px;
            }
        }
    </style>

    @yield('styles')
</head>
<body>

    <header class="navbar">
        <a href="{{ url('/') }}" class="brand" aria-label="SENAI MaintSys - Voltar para home">
            <span class="brand-icon">S</span>
            <span>SENAI MaintSys</span>
        </a>

        <nav class="nav-right" aria-label="Navegação">
            <a href="{{ url('/') }}" class="nav-link">Home</a>
            
            @auth
                <a href="{{ route('dashboard') }}" class="btn-top">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="nav-link">
                    Entrar
                </a>

                <a href="{{ route('register') }}" class="btn-top">
                    Criar conta
                </a>
            @endauth
        </nav>
    </header>

    <main class="auth-container">
        @yield('content')
    </main>

</body>
</html>