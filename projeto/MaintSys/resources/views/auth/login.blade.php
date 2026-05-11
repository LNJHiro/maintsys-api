<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — {{ config('app.name', 'MAINTSYS') }}</title>
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
            --border-input: rgba(255,255,255,0.12);
            --card: #13161d;
        }

        html, body {
            background: #0d0f14;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .glow {
            position: fixed; top: -150px; left: 50%; transform: translateX(-50%);
            width: 600px; height: 400px;
            background: radial-gradient(ellipse at center, rgba(232,197,71,0.07) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }

        /* NAV */
        nav {
            position: relative; z-index: 10;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 3rem;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            display: flex; align-items: center; gap: 10px;
            font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.1rem;
            letter-spacing: 0.08em; color: var(--text);
            text-decoration: none;
        }

        .logo-icon {
            width: 30px; height: 30px;
            background: var(--accent);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
        }

        /* MAIN */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            position: relative; z-index: 5;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }

        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .card-icon {
            width: 52px; height: 52px;
            background: rgba(232,197,71,0.1);
            border: 1px solid rgba(232,197,71,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.2rem;
        }

        .card-icon svg {
            width: 26px; height: 26px;
            stroke: var(--accent); fill: none;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }

        .card-header h1 {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem; font-weight: 700;
            color: var(--text);
            margin-bottom: 0.35rem;
        }

        .card-header p {
            font-size: 0.875rem;
            color: var(--muted);
        }

        /* FORM */
        .form-group {
            margin-bottom: 1.1rem;
        }

        label {
            display: block;
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 0.45rem;
            letter-spacing: 0.02em;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-input);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: var(--accent);
            background: rgba(232,197,71,0.04);
        }

        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: rgba(138,143,158,0.5);
        }

        .error-msg {
            font-size: 0.78rem;
            color: #f08080;
            margin-top: 0.4rem;
        }

        /* REMEMBER + FORGOT */
        .form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 1.2rem 0 1.5rem;
        }

        .remember {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.85rem; color: var(--muted);
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .forgot {
            font-size: 0.85rem;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot:hover { color: var(--accent); }

        /* BUTTON */
        .btn-submit {
            width: 100%;
            background: var(--accent);
            color: #0d0f14;
            border: none;
            border-radius: 10px;
            padding: 0.85rem;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600; font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            letter-spacing: 0.02em;
        }
        .btn-submit:hover { background: var(--accent2); transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        /* DIVIDER */
        .divider {
            display: flex; align-items: center; gap: 1rem;
            margin: 1.5rem 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: var(--border);
        }
        .divider span { font-size: 0.75rem; color: var(--muted); }

        /* BACK LINK */
        .back-link {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 0.85rem; color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--text); }
        .back-link svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; }

        /* FOOTER */
        footer {
            position: relative; z-index: 5;
            text-align: center;
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.78rem; color: var(--muted);
        }
    </style>
</head>
<body>

<div class="glow"></div>

<nav>
    <a href="{{ url('/') }}" class="logo">
        <div class="logo-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0d0f14" stroke-width="2.5" stroke-linecap="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
            </svg>
        </div>
        {{ config('app.name', 'MAINTSYS') }}
    </a>
</nav>

<main>
    <div class="card">
        <div class="card-header">
            <div class="card-icon">
                <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            </div>
            <h1>Bem-vindo de volta</h1>
            <p>Entre com suas credenciais para acessar o sistema</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div style="background: rgba(232,197,71,0.1); border: 1px solid rgba(232,197,71,0.25); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.85rem; color: var(--accent); margin-bottom: 1.5rem;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email"
                    value="{{ old('email') }}"
                    placeholder="seu@email.com"
                    required autofocus autocomplete="username">
                @error('email')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password"
                    placeholder="••••••••"
                    required autocomplete="current-password">
                @error('password')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember + Forgot -->
            <div class="form-footer">
                <label class="remember">
                    <input type="checkbox" name="remember">
                    Lembrar-me
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot">Esqueceu a senha?</a>
                @endif
            </div>

            <button type="submit" class="btn-submit">Entrar no sistema</button>
        </form>

        <div class="divider"><span>ou</span></div>

        <a href="{{ url('/') }}" class="back-link">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Voltar para a página inicial
        </a>
    </div>
</main>

<footer>
    &copy; {{ date('Y') }} {{ config('app.name', 'MAINTSYS') }} &mdash; Sistema de Gestão de Manutenção
</footer>

</body>
</html>