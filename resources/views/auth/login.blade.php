<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $businessSettings['business_name'] ?? 'TCocina' }} — Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=JetBrains+Mono:wght@400;500&family=Playfair+Display:ital@1&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-950:  #0a0e1a;
            --blue-900:  #0f1628;
            --blue-800:  #16203a;
            --blue-700:  #1e2f55;
            --blue-600:  #243f72;
            --blue-500:  #284497;
            --blue-400:  #3456be;
            --blue-300:  #5578d4;
            --blue-200:  #8aa3e8;
            --blue-100:  #c4d1f4;
            --white:     #ffffff;
            --cream:     #f0f4ff;
            --cream-dim: #8a96b8;
            --amber:     #E8A020;
            --amber-lt:  #F2B133;
            --border:    rgba(255,255,255,0.06);
            --border-md: rgba(255,255,255,0.11);
            --input-bg:  rgba(255,255,255,0.04);
            --font-display: 'Bebas Neue', sans-serif;
            --font-body:    'DM Sans', sans-serif;
            --font-mono:    'JetBrains Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { overflow-x: hidden; }

        body {
            font-family: var(--font-body);
            background: var(--blue-950);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ── PANEL IZQUIERDO ── */
        .left {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 3.5rem;
            overflow: hidden;
        }

        .left::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 65% 55% at 30% 50%, rgba(40,68,151,0.45) 0%, transparent 65%),
                radial-gradient(ellipse 40% 35% at 75% 20%, rgba(232,160,32,0.12) 0%, transparent 55%),
                linear-gradient(160deg, #0f1628 0%, #0a0e1a 100%);
        }

        .left::after {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
            background-size: 52px 52px;
            mask-image: radial-gradient(ellipse 85% 85% at 40% 50%, black 15%, transparent 75%);
        }

        .left-header {
            position: relative; z-index: 2;
            display: flex; align-items: center; gap: 12px;
        }

        .logo-badge {
            width: 42px; height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--blue-700), var(--blue-500));
            border: 1px solid rgba(255,255,255,0.12);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(40,68,151,0.45);
            overflow: hidden;
        }

        .logo-badge img {
            width: 100%; height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .logo-wordmark {
            font-family: var(--font-display);
            font-size: 1.9rem;
            letter-spacing: 0.06em;
            color: var(--white);
            line-height: 1;
        }

        /* Imagen hero central */
        .burger-stage {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -54%);
            width: 380px; height: 380px;
            animation: floatIn 0.9s cubic-bezier(0.22,1,0.36,1) 0.2s both;
            pointer-events: none;
        }

        @keyframes floatIn {
            from { opacity: 0; transform: translate(-50%, -48%); }
            to   { opacity: 1; transform: translate(-50%, -54%); }
        }

        .burger-img {
            width: 100%; height: 100%;
            object-fit: contain;
            animation: floatBob 5s ease-in-out 1.1s infinite;
            filter: drop-shadow(0 24px 40px rgba(40,68,151,0.35));
        }

        @keyframes floatBob {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-13px); }
        }

        .burger-glow {
            position: absolute;
            bottom: -5px; left: 50%;
            transform: translateX(-50%);
            width: 210px; height: 55px;
            background: radial-gradient(ellipse, rgba(40,68,151,0.5) 0%, transparent 70%);
            filter: blur(10px);
            animation: glowBob 5s ease-in-out 1.1s infinite;
        }

        @keyframes glowBob {
            0%, 100% { opacity: 0.7; transform: translateX(-50%) scaleX(1); }
            50%       { opacity: 1;   transform: translateX(-50%) scaleX(0.85); }
        }

        .left-bottom { position: relative; z-index: 2; }

        .live-pill {
            display: inline-flex;
            align-items: center; gap: 7px;
            font-family: var(--font-mono);
            font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase;
            color: var(--blue-100);
            background: rgba(40,68,151,0.2);
            border: 1px solid rgba(40,68,151,0.4);
            border-radius: 100px;
            padding: 5px 13px;
            margin-bottom: 1.1rem;
        }

        .live-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #00e676;
            animation: blink 2s ease-in-out infinite;
            box-shadow: 0 0 6px #00e676;
        }

        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.2; } }

        .left-title {
            font-family: var(--font-display);
            font-size: clamp(3rem, 4.5vw, 4.4rem);
            letter-spacing: 0.04em;
            color: var(--white);
            line-height: 0.95;
            margin-bottom: 1rem;
        }

        .left-title .accent {
            color: var(--amber);
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-weight: 400;
            font-size: 1em;
            letter-spacing: 0.01em;
            display: inline-block;
        }

        .left-desc {
            font-size: 13.5px; font-weight: 300;
            color: var(--cream-dim);
            line-height: 1.7; max-width: 320px;
            margin-bottom: 2rem;
        }

        .stats { display: flex; align-items: center; }

        .stat { padding: 0 1.6rem; display: flex; flex-direction: column; gap: 3px; }
        .stat:first-child { padding-left: 0; }

        .stat-n {
            font-family: var(--font-display);
            font-size: 2rem; letter-spacing: 0.04em;
            color: var(--white); line-height: 1;
        }

        .stat-l {
            font-family: var(--font-mono);
            font-size: 9px; letter-spacing: 0.14em;
            text-transform: uppercase; color: var(--cream-dim);
        }

        .stat-sep { width: 1px; height: 36px; background: var(--border-md); flex-shrink: 0; }

        /* ── PANEL DERECHO ── */
        /* Studio credit */
        .studio-credit {
            position: absolute;
            bottom: 1.2rem; left: 0; right: 0;
            display: flex; flex-direction: column;
            align-items: center; gap: 5px;
            pointer-events: none;
        }
        .studio-credit-label {
            font-family: var(--font-mono);
            font-size: 8px; letter-spacing: 0.22em; text-transform: uppercase;
            color: rgba(255,255,255,0.18);
        }
        .studio-credit-logo {
            width: 144px; opacity: 0.35;
            transition: opacity 0.3s ease;
            pointer-events: all;
        }
        .studio-credit:hover .studio-credit-logo { opacity: 0.7; }
        .studio-credit:hover .studio-credit-label { color: rgba(255,255,255,0.45); }

        .right {
            width: 460px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            padding: 2.5rem;
            position: relative;
            background: rgba(9,13,24,0.6);
            border-left: 1px solid var(--border);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
        }

        .right::before {
            content: '';
            position: absolute; top: -80px; right: -60px;
            width: 300px; height: 300px; border-radius: 50%;
            background: radial-gradient(circle, rgba(40,68,151,0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .right::after {
            content: '';
            position: absolute; bottom: -60px; left: -40px;
            width: 220px; height: 220px; border-radius: 50%;
            background: radial-gradient(circle, rgba(232,160,32,0.07) 0%, transparent 70%);
            pointer-events: none;
        }

        .form-wrap {
            width: 100%; max-width: 340px;
            opacity: 0; transform: translateY(22px);
            animation: slideUp 0.65s cubic-bezier(0.22,1,0.36,1) 0.45s forwards;
        }

        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }

        .form-eyebrow {
            font-family: var(--font-mono);
            font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase;
            color: var(--blue-200);
            margin-bottom: 0.5rem;
        }

        .form-title {
            font-family: var(--font-display);
            font-size: 2.9rem; letter-spacing: 0.04em;
            color: var(--white); line-height: 0.95;
            margin-bottom: 0.5rem;
        }

        .form-sub {
            font-size: 13px; font-weight: 300;
            color: var(--cream-dim);
            margin-bottom: 2rem;
        }

        /* Campos */
        .field { margin-bottom: 1rem; }

        .field-label {
            display: block;
            font-family: var(--font-mono);
            font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase;
            color: var(--cream-dim);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .field-input {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--border-md);
            border-radius: 8px;
            padding: 12px 15px;
            font-family: var(--font-body);
            font-size: 14px; font-weight: 400;
            color: var(--white);
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        .field-input::placeholder { color: rgba(255,255,255,0.18); }

        .field-input:focus {
            border-color: var(--blue-400);
            background: rgba(40,68,151,0.1);
            box-shadow: 0 0 0 3px rgba(40,68,151,0.18);
        }

        .field-input.is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.15) !important;
        }

        .field-error {
            font-family: var(--font-mono);
            font-size: 10px; letter-spacing: 0.08em;
            color: #f87171;
            margin-top: 6px;
            display: block;
        }

        /* Error de credenciales */
        .alert-login-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #fca5a5;
            margin-bottom: 1.2rem;
        }

        .pw-toggle {
            position: absolute; right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--cream-dim);
            display: flex; align-items: center; padding: 4px;
            transition: color 0.18s;
        }
        .pw-toggle:hover { color: var(--blue-200); }

        /* Opciones */
        .row-opts {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.6rem;
        }

        .check-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 400;
            color: var(--cream-dim); cursor: pointer; user-select: none;
        }
        .check-label input { display: none; }

        .custom-check {
            width: 15px; height: 15px;
            border-radius: 4px;
            border: 1px solid var(--border-md);
            background: var(--input-bg);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: all 0.18s;
        }
        .check-label input:checked + .custom-check {
            background: var(--blue-500);
            border-color: var(--blue-500);
        }
        .check-label input:checked + .custom-check::after {
            content: '';
            width: 7px; height: 4px;
            border-left: 2px solid #fff;
            border-bottom: 2px solid #fff;
            transform: rotate(-45deg) translateY(-1px);
            display: block;
        }

        .forgot {
            font-family: var(--font-mono);
            font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--blue-200);
            text-decoration: none; transition: color 0.18s;
        }
        .forgot:hover { color: var(--white); }

        /* Botón submit */
        .btn-submit {
            width: 100%; padding: 13px;
            border-radius: 8px; border: none;
            background: linear-gradient(135deg, var(--blue-500) 0%, var(--blue-400) 100%);
            color: var(--white);
            font-family: var(--font-display);
            font-size: 1.25rem; letter-spacing: 0.12em;
            cursor: pointer; position: relative; overflow: hidden;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 24px rgba(40,68,151,0.45);
        }
        .btn-submit::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 55%);
            opacity: 0; transition: opacity 0.18s;
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 32px rgba(40,68,151,0.55); }
        .btn-submit:hover::before { opacity: 1; }
        .btn-submit:active { transform: translateY(0); }

        /* Mobile header (logo + wordmark) */
        .mobile-top-header {
            display: none;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 2rem 2rem 0;
            flex-shrink: 0;
        }

        /* Mobile hero */
        .mobile-hero {
            display: none;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .mobile-hero-img {
            width: 240px; height: 240px;
            object-fit: contain;
            animation: floatBob 5s ease-in-out infinite;
            filter: drop-shadow(0 20px 32px rgba(40,68,151,0.45));
        }

        @media (max-width: 860px) {
            .left { display: none; }
            .right {
                width: 100vw;
                max-width: 100vw;
                flex-shrink: 1;
                border: none;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding: 0 0 2.5rem;
                overflow-y: auto;
                min-height: 100vh;
            }
            .right .form-wrap {
                padding: 0 2rem;
                width: 100%;
            }
            .mobile-top-header { display: flex; }
            .mobile-hero {
                display: block;
                width: 100%;
                margin-top: clamp(2.5rem, 10vh, 5rem);
                margin-bottom: clamp(1rem, 3vh, 2rem);
            }
            .mobile-hero-img {
                display: block;
                margin-left: auto;
                margin-right: auto;
                transform: translateX(15px);
            }
            /* En mobile el studio credit fluye con el contenido, no está absoluto */
            .studio-credit {
                position: static !important;
                margin-top: 2rem;
                padding-bottom: 1rem;
            }
        }
    </style>
</head>
<body>

<!-- ── PANEL IZQUIERDO ── -->
<div class="left">
    <canvas id="stars-left" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:1;"></canvas>

    <div class="left-header">
        <a href="https://tcocina.org" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:12px;text-decoration:none;">
            <div class="logo-badge">
                <img src="{{ $businessSettings['brand_logo_url'] ?? asset('images/log.png') }}"
                     alt="{{ $businessSettings['business_name'] ?? 'TCocina' }}"
                     onerror="this.onerror=null;this.src='{{ asset('images/log.png') }}';">
            </div>
            <span class="logo-wordmark">{{ $businessSettings['business_name'] ?? 'TCocina' }}</span>
        </a>
    </div>

    <div class="burger-stage">
        <div class="burger-glow"></div>
        <img class="burger-img"
             src="{{ asset('images/tcocinalogin.png') }}"
             alt="Hamburguesa"
             onerror="this.style.display='none'">
    </div>

    <div class="left-bottom">
        <div class="live-pill">
            <span class="live-dot"></span>
            Sistema en línea
        </div>
        <h1 class="left-title">
            GESTIONÁ<br>TU <span class="accent">cocina</span><br>EN TIEMPO REAL
        </h1>
        <p class="left-desc">Control integral de pedidos, menú y operaciones.</p>
        <div class="stats">
            <div class="stat">
                <span class="stat-n">24/7</span>
                <span class="stat-l">Disponible</span>
            </div>
            <div class="stat-sep"></div>
            <div class="stat">
                <span class="stat-n">1</span>
                <span class="stat-l">Panel central</span>
            </div>
            <div class="stat-sep"></div>
            <div class="stat">
                <span class="stat-n">LIVE</span>
                <span class="stat-l">Tiempo real</span>
            </div>
        </div>
    </div>
</div>

<!-- ── PANEL DERECHO ── -->
<div class="right">

    <canvas id="stars-right" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:0;"></canvas>

    <!-- Header mobile: logo + wordmark, esquina superior izquierda -->
    <div class="mobile-top-header">
        <a href="https://tcocina.org" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:12px;text-decoration:none;">
            <div class="logo-badge">
                <img src="{{ $businessSettings['brand_logo_url'] ?? asset('images/log.png') }}"
                     alt="{{ $businessSettings['business_name'] ?? 'TCocina' }}"
                     onerror="this.onerror=null;this.src='{{ asset('images/log.png') }}';">
            </div>
            <span class="logo-wordmark">{{ $businessSettings['business_name'] ?? 'TCocina' }}</span>
        </a>
    </div>

    <div class="mobile-hero">
        <img class="mobile-hero-img"
             src="{{ asset('images/tcocinalogin.png') }}"
             alt="Hero"
             onerror="this.style.display='none'">
    </div>

    <div class="form-wrap">

        <p class="form-eyebrow">Panel de administración blstudio</p>
        <h2 class="form-title">BIENVENIDO</h2>
        <p class="form-sub">Ingresá tus credenciales para continuar</p>

        {{-- Error de credenciales --}}
        @if ($errors->has('email') && !$errors->has('email'))
        @endif
        @if (session('status'))
            <div class="alert-login-error">{{ session('status') }}</div>
        @endif
        @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
            <div class="alert-login-error">
                @foreach ($errors->all() as $error){{ $error }} @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="field">
                <label class="field-label" for="email">Correo Electrónico</label>
                <div class="input-wrap">
                    <input class="field-input @error('email') is-invalid @enderror"
                           type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="admin@tcocina.com"
                           autocomplete="email" autofocus>
                </div>
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label class="field-label" for="password">Contraseña</label>
                <div class="input-wrap">
                    <input class="field-input @error('password') is-invalid @enderror @error('email') is-invalid @enderror"
                           type="password" id="password" name="password"
                           placeholder="••••••••"
                           style="padding-right: 42px"
                           autocomplete="current-password">
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Mostrar contraseña">
                        <svg id="eye-svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="row-opts">
                <label class="check-label">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span class="custom-check"></span>
                    Recordarme
                </label>
                @if (Route::has('password.request'))
                    <a class="forgot" href="{{ route('password.request') }}">¿Olvidaste tu clave?</a>
                @endif
            </div>

            <button type="submit" class="btn-submit">Iniciar Sesión</button>

        </form>
    </div>
    <!-- Studio credit -->
    <div class="studio-credit">
        <span class="studio-credit-label">desarrollado por</span>
        <a href="http://stgrandesligas.com" target="_blank" rel="noopener" style="display:block;line-height:0;">
        <svg class="studio-credit-logo" viewBox="0 0 480 160" xmlns="http://www.w3.org/2000/svg" id="bl-logo-svg">
            <defs>
                <linearGradient id="bl-m" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#f0f0f0"/>
                    <stop offset="30%"  stop-color="#c0c0c0"/>
                    <stop offset="55%"  stop-color="#e8e8e8"/>
                    <stop offset="80%"  stop-color="#909090"/>
                    <stop offset="100%" stop-color="#c8c8c8"/>
                </linearGradient>
                <linearGradient id="bl-g" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#a8d400"/>
                    <stop offset="100%" stop-color="#6a8a00"/>
                </linearGradient>
                <clipPath id="bl-tc">
                    <rect x="0" y="0" width="660" height="130"/>
                </clipPath>
            </defs>
            <g clip-path="url(#bl-tc)" opacity="0.45">
                <line x1="75"  y1="38" x2="148" y2="60" stroke="#8db600" stroke-width="0.7" opacity="0.5"/>
                <line x1="148" y1="60" x2="218" y2="36" stroke="#8db600" stroke-width="0.7" opacity="0.4"/>
                <line x1="218" y1="36" x2="295" y2="52" stroke="#8db600" stroke-width="0.7" opacity="0.5"/>
                <circle cx="75"  cy="38" r="2.6" fill="#8db600"/>
                <circle cx="218" cy="36" r="2.6" fill="#8db600"/>
                <circle cx="148" cy="60" r="1.8" fill="#e0e0e0"/>
                <circle cx="295" cy="52" r="1.8" fill="#e0e0e0"/>
            </g>
            <text id="bl-logo-text" x="32" y="128"
                font-family="'DM Sans','Helvetica Neue',Arial,sans-serif"
                font-size="122" font-weight="300" letter-spacing="-4"
                fill="url(#bl-m)">blstudio</text>
            <circle id="bl-dot"    cx="0" cy="0" r="7" fill="url(#bl-g)"/>
            <circle id="bl-pulse1" cx="0" cy="0" r="7" fill="none" stroke="#8db600" stroke-width="1.2">
                <animate attributeName="r"       values="7;22;7"    dur="2.2s" repeatCount="indefinite"/>
                <animate attributeName="opacity" values="0.7;0;0.7" dur="2.2s" repeatCount="indefinite"/>
            </circle>
            <circle id="bl-pulse2" cx="0" cy="0" r="7" fill="none" stroke="#8db600" stroke-width="0.6">
                <animate attributeName="r"       values="7;36;7"      dur="2.2s" begin="0.5s" repeatCount="indefinite"/>
                <animate attributeName="opacity" values="0.35;0;0.35" dur="2.2s" begin="0.5s" repeatCount="indefinite"/>
            </circle>
        </svg>
        </a>
    </div>

</div>

<script>
    // blstudio logo — posicionar punto verde tras el texto
    (function() {
        const svg  = document.getElementById('bl-logo-svg');
        const text = document.getElementById('bl-logo-text');
        const els  = ['bl-dot','bl-pulse1','bl-pulse2'].map(id => document.getElementById(id));
        function place() {
            try {
                const bb = text.getBBox();
                const x  = bb.x + bb.width + 10;
                const y  = bb.y + bb.height * 0.72;
                svg.setAttribute('viewBox', `0 0 ${x + 30} 160`);
                els.forEach(el => { el.setAttribute('cx', x); el.setAttribute('cy', y); });
            } catch(e) {}
        }
        document.fonts && document.fonts.ready ? document.fonts.ready.then(place) : setTimeout(place, 400);
    })();

    const pwToggle = document.getElementById('pwToggle');
    const pwInput  = document.getElementById('password');
    const eyeSvg   = document.getElementById('eye-svg');
    if (pwToggle && pwInput) {
        pwToggle.addEventListener('click', function() {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            pwToggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            eyeSvg.innerHTML = show
                ? `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>`
                : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
        });
    }
</script>

<script>
    // blstudio falling stars — fondo ambiental login
    (function() {
        var COLOR = '#d6ff3c';

        function initStars(canvasId, count, opacityMax) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            var W, H, stars = [];

            function resize() {
                var r = canvas.parentElement.getBoundingClientRect();
                W = canvas.width  = r.width  || canvas.parentElement.offsetWidth;
                H = canvas.height = r.height || canvas.parentElement.offsetHeight;
            }

            function mkStar() {
                return {
                    x:     Math.random() * (W || 400),
                    y:     Math.random() * (H || 600) - (H || 600),
                    r:     Math.random() * 1.0 + 0.2,
                    vy:    Math.random() * 0.55 + 0.2,
                    vx:    (Math.random() - 0.5) * 0.15,
                    o:     Math.random() * opacityMax + 0.05,
                    pulse: Math.random() * Math.PI * 2
                };
            }

            function draw() {
                ctx.clearRect(0, 0, W, H);
                stars.forEach(function(s) {
                    s.pulse += 0.035;
                    s.x += s.vx; s.y += s.vy;
                    if (s.y > H + 4) { Object.assign(s, mkStar(), { y: -4, x: Math.random() * W }); }
                    ctx.beginPath();
                    ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
                    ctx.fillStyle = COLOR;
                    ctx.globalAlpha = s.o * (0.7 + 0.3 * Math.sin(s.pulse));
                    ctx.fill();
                });
                ctx.globalAlpha = 1;
                requestAnimationFrame(draw);
            }

            resize();
            stars = Array.from({ length: count }, mkStar);
            draw();
            window.addEventListener('resize', resize);
        }

        initStars('stars-left',  55, 0.22);
        initStars('stars-right', 40, 0.18);
    })();
</script>

<script>
(function () {
    var ua = window.navigator.userAgent || '';
    var apps = [
        ['WhatsApp',  /WhatsApp/i],
        ['Instagram', /Instagram/i],
        ['Facebook',  /FBAN|FBAV|FB_IAB|FB4A|FBDV/i],
        ['TikTok',    /TikTok|BytedanceWebview/i],
        ['Snapchat',  /Snapchat/i],
        ['Line',      /Line\//i],
    ];
    var appName = null;
    for (var i = 0; i < apps.length; i++) {
        if (apps[i][1].test(ua)) { appName = apps[i][0]; break; }
    }
    if (!appName) return;

    var isAndroid = /Android/i.test(ua);

    var style = document.createElement('style');
    style.textContent = '@keyframes iabArrow{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}';
    document.head.appendChild(style);

    var arrow = '<div style="position:absolute;top:0;right:20px;pointer-events:none;animation:iabArrow 1.5s ease-in-out infinite">' +
        '<svg width="28" height="80" viewBox="0 0 28 80" fill="none">' +
        '<line x1="14" y1="76" x2="14" y2="18" stroke="white" stroke-width="3.5" stroke-linecap="round"/>' +
        '<polygon points="14,4 5,22 23,22" fill="white"/>' +
        '</svg></div>';

    var androidBtn = isAndroid
        ? '<button onclick="location.href=\'intent://\'+location.hostname+location.pathname+location.search+\'#Intent;scheme=https;package=com.android.chrome;end\'" style="margin-top:20px;width:100%;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:14px;padding:14px;font-size:14px;font-weight:700;cursor:pointer;letter-spacing:.02em">Abrir en Chrome →</button>'
        : '';

    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;background:rgba(8,12,22,0.96);display:flex;align-items:center;justify-content:center;padding:32px;font-family:system-ui,-apple-system,sans-serif';
    overlay.innerHTML = arrow +
        '<div style="max-width:300px;width:100%;text-align:center">' +
            '<img src="/images/tcocinalogin-sm.png?v=2" style="width:116px;height:116px;border-radius:50%;object-fit:cover;object-position:center;margin-bottom:22px;display:block;margin-left:auto;margin-right:auto" alt="TCocina"/>' +
            '<p style="margin:0 0 12px;font-size:22px;font-weight:900;color:#fff;line-height:1.2;letter-spacing:-0.02em">Abrí nuestra web desde<br>un navegador externo</p>' +
            '<p style="margin:0 0 30px;font-size:13px;color:#64748b;line-height:1.7">Desde el navegador de <span style="color:#94a3b8;font-weight:600">' + appName + '</span>, algunas funciones de nuestra app no las vas a poder ver. Por eso necesitamos que abras uno externo.</p>' +
            '<div style="display:flex;align-items:flex-start;gap:14px;text-align:left;border-top:1px solid rgba(255,255,255,0.08);padding-top:24px">' +
                '<div style="font-size:18px;font-weight:900;color:rgba(255,255,255,0.25);flex-shrink:0;padding-top:1px">01</div>' +
                '<p style="margin:0;font-size:14px;color:#cbd5e1;line-height:1.55">Tocá los <strong style="color:#fff;font-weight:700">···</strong> arriba a la derecha y elegí <strong style="color:#fff;font-weight:700">"Abrir en navegador externo"</strong></p>' +
            '</div>' +
            androidBtn +
        '</div>';
    document.body.appendChild(overlay);
})();
</script>
</body>
</html>
