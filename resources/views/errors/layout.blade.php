<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') - TCocina</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Roboto (Rocker) --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    {{-- Boxicons (Rocker icon set) --}}
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #00b4d8;
            --brand-accent: #ff6b35;
            --brand-blue-from: #0096c7;
            --brand-blue-to: #0c6568;
        }

        body {
            font-family: 'Roboto', system-ui, -apple-system, Segoe UI, sans-serif;
            background: #f7f7ff;
            letter-spacing: .3px;
            color: #4c5258;
            min-height: 100vh;
            margin: 0;
        }

        /* Top navbar (Rocker) */
        .err-navbar {
            background: #fff;
            box-shadow: 0 2px 6px rgba(218,218,253,.65), 0 2px 6px rgba(206,206,238,.54);
            padding: .75rem 1.25rem;
            position: sticky; top: 0; z-index: 1030;
        }
        .err-navbar .brand {
            display: inline-flex; align-items: center; gap: .6rem;
            color: var(--brand-blue-from); text-decoration: none; font-weight: 700;
        }
        .err-navbar .brand img { width: 36px; height: 36px; object-fit: contain; }
        .err-navbar .brand span { font-size: 1.1rem; }
        .err-navbar .nav-actions a {
            color: #4c5258; text-decoration: none; margin-left: 1rem; font-size: .9rem;
        }
        .err-navbar .nav-actions a:hover { color: var(--brand-blue-from); }

        /* Error page container */
        .error-page {
            min-height: calc(100vh - 64px - 56px);
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem;
        }

        .error-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,.06), 0 2px 8px rgba(0,0,0,.04);
            overflow: hidden;
            max-width: 1080px;
            width: 100%;
        }

        .error-card .row { align-items: center; }

        .error-body { padding: 3rem 2.5rem; }

        .error-code {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            font-size: clamp(5rem, 14vw, 9rem);
            line-height: 1;
            letter-spacing: -2px;
            margin: 0 0 .5rem;
        }
        .error-code .c1 { color: var(--brand-blue-from); }
        .error-code .c2 { color: var(--brand-accent); }
        .error-code .c3 { color: #28a745; }

        .error-title {
            font-weight: 700;
            font-size: clamp(1.5rem, 3vw, 2.2rem);
            color: #2c3340;
            margin-bottom: .75rem;
        }

        .error-message {
            font-size: 1rem;
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .error-tip {
            background: linear-gradient(135deg, rgba(0,150,199,.08), rgba(12,101,104,.08));
            border-left: 4px solid var(--brand-blue-from);
            padding: .9rem 1rem;
            border-radius: 8px;
            font-size: .9rem;
            color: #4c5258;
            margin-bottom: 1.5rem;
        }
        .error-tip i { color: var(--brand-blue-from); margin-right: .4rem; }

        .error-actions { display: flex; gap: .75rem; flex-wrap: wrap; }

        .btn-rocker-primary {
            background: linear-gradient(135deg, var(--brand-blue-from) 0%, var(--brand-blue-to) 100%);
            color: #fff; border: none; border-radius: 30px;
            padding: .7rem 1.6rem; font-weight: 500;
            display: inline-flex; align-items: center; gap: .4rem;
            transition: transform .2s ease, box-shadow .2s ease;
            text-decoration: none;
        }
        .btn-rocker-primary:hover {
            color: #fff; transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,150,199,.35);
        }
        .btn-rocker-outline {
            background: transparent; color: #2c3340;
            border: 2px solid #2c3340; border-radius: 30px;
            padding: .65rem 1.6rem; font-weight: 500;
            display: inline-flex; align-items: center; gap: .4rem;
            transition: all .2s ease; text-decoration: none;
        }
        .btn-rocker-outline:hover { background: #2c3340; color: #fff; }

        .error-illustration {
            display: flex; align-items: center; justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #f7f7ff 0%, #eef0fb 100%);
            min-height: 320px;
        }
        .error-illustration .ill-circle {
            width: clamp(180px, 30vw, 320px);
            height: clamp(180px, 30vw, 320px);
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-blue-from) 0%, var(--brand-blue-to) 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 20px 60px rgba(0,150,199,.35);
            position: relative;
        }
        .error-illustration .ill-circle::before {
            content: ''; position: absolute; inset: -12px;
            border-radius: 50%; border: 2px dashed rgba(0,150,199,.25);
            animation: spin 30s linear infinite;
        }
        .error-illustration i {
            font-size: clamp(80px, 14vw, 140px); color: #fff;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Footer Rocker */
        .err-footer {
            background: #fff; border-top: 1px solid #eef0fb;
            padding: 1rem 1.25rem; text-align: center; font-size: .85rem; color: #6b7280;
        }
        .err-footer a { color: var(--brand-blue-from); text-decoration: none; margin: 0 .35rem; }
        .err-footer a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .error-body { padding: 2rem 1.5rem; }
            .error-illustration { min-height: 220px; padding: 1.25rem; }
        }

        /* Variant tints (overridden per page) */
        .variant-warning .ill-circle { background: linear-gradient(135deg, #f9844a 0%, #f76b27 100%); box-shadow: 0 20px 60px rgba(247,107,39,.35); }
        .variant-warning .btn-rocker-primary { background: linear-gradient(135deg, #f9844a 0%, #f76b27 100%); }
        .variant-warning .error-tip { background: linear-gradient(135deg, rgba(249,132,74,.1), rgba(247,107,39,.1)); border-left-color: #f76b27; }
        .variant-warning .error-tip i { color: #f76b27; }

        .variant-danger .ill-circle { background: linear-gradient(135deg, #e63946 0%, #b00020 100%); box-shadow: 0 20px 60px rgba(230,57,70,.35); }
        .variant-danger .btn-rocker-primary { background: linear-gradient(135deg, #e63946 0%, #b00020 100%); }
        .variant-danger .error-tip { background: linear-gradient(135deg, rgba(230,57,70,.1), rgba(176,0,32,.1)); border-left-color: #e63946; }
        .variant-danger .error-tip i { color: #e63946; }

        .variant-info .ill-circle { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 20px 60px rgba(99,102,241,.35); }
        .variant-info .btn-rocker-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    </style>
    @stack('styles')
</head>
<body>

<nav class="err-navbar d-flex align-items-center justify-content-between">
    <a class="brand" href="{{ url('/') }}">
        @php
            $logoUrl = isset($businessSettings['brand_logo_url']) ? $businessSettings['brand_logo_url'] : null;
            $bizName = $businessSettings['business_name'] ?? 'TCocina';
        @endphp
        <img src="{{ $logoUrl ?: asset('favicon.png') }}" alt="{{ $bizName }}"
             onerror="this.onerror=null; this.src='{{ asset('favicon.png') }}';">
        <span>{{ $bizName }}</span>
    </a>
    <div class="nav-actions d-none d-sm-flex">
        <a href="{{ url('/') }}"><i class='bx bx-home-alt me-1'></i>Inicio</a>
        @auth
            <a href="{{ route('admin.dashboard') }}"><i class='bx bx-grid-alt me-1'></i>Panel</a>
        @else
            <a href="{{ route('login') }}"><i class='bx bx-log-in me-1'></i>Ingresar</a>
        @endauth
    </div>
</nav>

<main class="error-page @yield('variant', '')">
    <div class="container">
        <div class="error-card">
            <div class="row g-0">
                <div class="col-lg-7 order-2 order-lg-1">
                    <div class="error-body">
                        <h1 class="error-code">@yield('code')</h1>
                        <h2 class="error-title">@yield('heading')</h2>
                        <p class="error-message">@yield('message')</p>

                        @hasSection('tip')
                            <div class="error-tip">
                                <i class='bx bx-info-circle'></i>
                                @yield('tip')
                            </div>
                        @endif

                        <div class="error-actions">
                            @yield('actions')
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 order-1 order-lg-2">
                    <div class="error-illustration">
                        <div class="ill-circle">
                            <i class='@yield('icon', 'bx bx-error-alt')'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="err-footer">
    <span>© {{ date('Y') }} {{ $bizName ?? 'TCocina' }}</span>
    <span class="mx-2">·</span>
    <a href="{{ url('/') }}"><i class='bx bx-home-alt'></i> Inicio</a>
    <a href="javascript:history.back()"><i class='bx bx-arrow-back'></i> Volver</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
