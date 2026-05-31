<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XN3SN7P3V6"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-XN3SN7P3V6');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TCocina - Delivery de Hamburguesas Smash en Olavarria')</title>
    <meta name="description" content="Hamburgueseria artesanal en Olavarria. Smash burgers con delivery a domicilio, envio y retiro. Pedidos por WhatsApp - Av. Pringles 3768, Olavarria.">
    <link rel="canonical" href="{{ url()->current() }}">
    @verbatim
    <script type="application/ld+json">{"@context":"https://schema.org","@type":"Restaurant","name":"TCocina Hamburguesas Smash","description":"Hamburgueseria artesanal en Olavarria. Delivery y retiro en local.","url":"https://tcocina.org","telephone":"+542284156473","priceRange":"$$","servesCuisine":["Hamburguesas","Smash Burger","Comida rapida artesanal"],"keywords":"hamburguesas olavarria, smash burger olavarria, hamburgueseria olavarria, delivery hamburguesas olavarria","address":{"@type":"PostalAddress","streetAddress":"Av. Pringles 3768","addressLocality":"Olavarria","addressRegion":"Buenos Aires","postalCode":"B7400","addressCountry":"AR"},"geo":{"@type":"GeoCoordinates","latitude":-36.8927,"longitude":-60.3228},"areaServed":{"@type":"City","name":"Olavarria"},"potentialAction":{"@type":"OrderAction","target":"https://tcocina.org"}}</script>
    @endverbatim

    <!-- Polo Fonts - Bebas Neue, DM Sans, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <style>
        :root{
            --brand-primary: {{ $businessSettings['brand_primary_color'] ?? '#00b4d8' }};
            --brand-accent:  {{ $businessSettings['brand_accent_color'] ?? '#ff6b35' }};
            --font-display: 'Bebas Neue', sans-serif;
            --font-body: 'DM Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }
        body {
            font-family: var(--font-body);
        }
    </style>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Beach Theme CSS -->
    <link href="{{ asset('css/custom-beach-theme.css') }}?v={{ filemtime(public_path('css/custom-beach-theme.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/main.css') }}?v={{ filemtime(public_path('css/main.css')) }}" rel="stylesheet">
    <!-- Polo Fonts CSS -->
    <link href="{{ asset('css/polo-fonts.css') }}" rel="stylesheet">
    <style>
        /* Mapear colores de marca a la paleta del sitio público */
        :root{
            --beach-primary: {{ $businessSettings['brand_primary_color'] ?? '#00b4d8' }};
            --beach-accent:  {{ $businessSettings['brand_accent_color'] ?? '#ff6b35' }};
            --beach-secondary: var(--beach-accent);
        }
        /* Alinear Bootstrap utilidades con la marca pública */
        .btn-primary,.bg-primary{ background-color: var(--beach-primary) !important; border-color: var(--beach-primary) !important; }
        .text-primary{ color: var(--beach-primary) !important; }
        .btn-outline-primary{ color: var(--beach-primary) !important; border-color: var(--beach-primary) !important; }
        /* Google button (brand style adapted to TCocina palette) */
        .google-brand-btn{
            --google-bg: #ffffff;
            --google-border: rgba(0, 180, 216, 0.28);
            --google-text: #0f172a;
            --google-shadow: 0 10px 22px rgba(2, 8, 23, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 700;
            border-radius: 12px;
            border: 1px solid var(--google-border);
            background: linear-gradient(180deg, var(--google-bg), #f8fbff);
            color: var(--google-text) !important;
            text-decoration: none;
            box-shadow: var(--google-shadow);
            transition: transform .18s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .google-brand-btn:hover{
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--beach-primary), #94a3b8 55%);
            box-shadow: 0 12px 26px rgba(2, 8, 23, 0.22);
        }
        .google-brand-btn:active{
            transform: translateY(0);
        }
        .google-brand-btn-sm{
            height: 34px;
            padding: 0 12px;
            font-size: .86rem;
            border-radius: 999px;
            gap: 8px;
        }
        .google-brand-btn-md{
            height: 44px;
            padding: 0 14px;
            font-size: .95rem;
        }
        .google-glyph{
            width: 18px;
            height: 18px;
            display: inline-block;
            flex-shrink: 0;
        }
        /* Botón usuario (mismo estilo que Google para consistencia) */
        .header-auth-btn{
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 34px;
            padding: 0 12px;
            font-size: .86rem;
            font-weight: 700;
            border-radius: 999px;
            border: 1px solid rgba(0, 180, 216, 0.28);
            background: linear-gradient(180deg, #fff, #f8fbff);
            color: #0f172a !important;
            box-shadow: 0 10px 22px rgba(2, 8, 23, 0.18);
            transition: transform .18s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .header-auth-btn:hover{
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--beach-primary), #94a3b8 55%);
            box-shadow: 0 12px 26px rgba(2, 8, 23, 0.22);
        }
        .header-auth-btn::after{ margin-left: 4px; }
        /* Preloader crítico - debe estar visible desde el inicio */
        #preloader {
            position: fixed !important;
            inset: 0 !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            z-index: 99999 !important;
            background: radial-gradient(circle at top, rgba(14, 165, 233, 0.35), rgba(7, 26, 47, 0.92)) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
        }
        /* Ocultar header inicialmente (solo visualmente, no funcionalmente) */
        #mainHeader.header-hidden {
            opacity: 0 !important;
        }
        #mainHeader {
            transition: opacity 0.3s ease !important;
            z-index: 1000 !important;
        }
        /* Estilos críticos para el logo del preloader - debe estar centrado desde el inicio */
        .preloader-logo-frame {
            position: relative !important;
            width: 150px !important;
            height: 150px !important;
            margin: 0 auto !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            z-index: 2 !important;
        }
        .preloader-logo {
            width: 80% !important;
            height: 80% !important;
            object-fit: contain !important;
            filter: drop-shadow(0 6px 12px rgba(14,165,233,0.35)) !important;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-white d-flex flex-column min-vh-100 @if (request()->routeIs('home') ||
        request()->routeIs('catalog') ||
        request()->routeIs('category') ||
        request()->routeIs('product') ||
        request()->routeIs('cart') ||
        request()->routeIs('checkout') ||
        request()->routeIs('order.confirmation')) order-flow-bg @endif">

    <!-- Preloader -->
    <div id="preloader" aria-hidden="true" style="position: fixed; inset: 0; display: flex; justify-content: center; align-items: center; z-index: 99999; background: radial-gradient(circle at top, rgba(14, 165, 233, 0.35), rgba(7, 26, 47, 0.92)); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
        <div class="preloader-content" style="position: relative; width: 230px; height: 230px; border-radius: 28px; padding: 28px 18px 32px; background: rgba(8, 24, 48, 0.65); border: 1px solid rgba(255, 255, 255, 0.12); box-shadow: 0 35px 65px rgba(0,0,0,0.45); overflow: hidden;">
            <div class="preloader-backdrop" aria-hidden="true"></div>
            <div class="preloader-orbit" aria-hidden="true"></div>
            <div class="preloader-logo-frame" style="position: relative; width: 150px; height: 150px; margin: 0 auto; display: flex; justify-content: center; align-items: center; z-index: 2;">
                <img src="{{ asset('images/Tsinfondo.png') }}" alt="TCocina" class="preloader-logo" style="width: 80%; height: 80%; object-fit: contain; filter: drop-shadow(0 6px 12px rgba(14,165,233,0.35));" />
                <div class="logo-shine" aria-hidden="true"></div>
            </div>
        </div>
    </div>

    <!-- Header Navigation -->
    <header class="border-bottom header-themed header-hidden" id="mainHeader">
        <div class="container pb-0">
            <div class="position-relative d-flex justify-content-between align-items-center"
                style="min-height: 88px; padding: 8px 0;" id="main-header">
                <!-- Left: Brand text -->
                <div class="d-flex align-items-center">
                    @php
                        $brandLogoLeft = $businessSettings['brand_logo_left_url'] ?? null;
                        if ($brandLogoLeft) {
                            if (str_starts_with($brandLogoLeft, '/storage/') || str_starts_with($brandLogoLeft, '/branding/') || str_starts_with($brandLogoLeft, '/images/')) {
                                $brandLogoLeft = asset($brandLogoLeft);
                            } else {
                                $brandLogoLeft = asset($brandLogoLeft);
                            }
                        } else {
                            $brandLogoLeft = asset('images/tcocina_navidad.png');
                        }
                    @endphp
                    <img src="{{ $brandLogoLeft }}"
                        alt="{{ $businessSettings['business_name'] ?? 'TCocina' }}"
                        style="height: 40px; width: auto;" class="me-2"
                        onerror="this.onerror=null; this.src='{{ asset('images/tcocina_navidad.png') }}';" />
                </div>

                <!-- Absolute centered Logo -->
                <div class="position-absolute top-50 start-50 translate-middle">
                    <a href="{{ route('catalog') }}" class="text-decoration-none d-flex align-items-center">
                        @php
                            $brandLogo = $businessSettings['brand_logo_url'] ?? null;
                            if ($brandLogo) {
                                if (str_starts_with($brandLogo, '/storage/') || str_starts_with($brandLogo, '/branding/') || str_starts_with($brandLogo, '/images/')) {
                                    $brandLogo = asset($brandLogo);
                                } else {
                                    $brandLogo = asset($brandLogo);
                                }
                            } else {
                                $brandLogo = asset('images/log.png');
                            }
                        @endphp
                        <img src="{{ $brandLogo }}"
                            alt="{{ $businessSettings['business_name'] ?? 'TCocina' }}" style="height: 52px;"
                            onerror="this.onerror=null; this.src='{{ asset('images/log.png') }}';" />
                    </a>
                </div>

                <!-- Right: Cart, Notifications & Menu -->
                <div class="d-flex align-items-center justify-content-end gap-3">
                    @auth
                    <!-- Notification Bell -->
                    <div style="position: relative; z-index: 1001;">
                        <button id="notificationToggle" class="btn position-relative d-flex align-items-center" aria-expanded="false" style="padding: 0; border: none; background: transparent; line-height: 1;">
                            <lord-icon
                                src="{{ asset('lordicons/notificaciones.json') }}"
                                colors="primary:#ffffff,secondary:#ffffff"
                                trigger="hover"
                                style="width:28px;height:28px">
                            </lord-icon>
                            <span id="notificationBadge"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 10px; padding: 4px 6px;">0</span>
                        </button>
                        <div id="notificationDropdown" style="width: 320px; max-height: 400px; overflow-y: auto; border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); z-index: 100000; position: fixed; right: 16px; top: 64px; display: none; background: #fff;">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: color-mix(in srgb, var(--beach-primary) 75%, #000 25%); color: #fff;">
                                <h6 class="mb-0 fw-bold text-white">Notificaciones</h6>
                                <img src="{{ $businessSettings['brand_logo_url'] ?? asset('images/log.png') }}"
                                    alt="{{ $businessSettings['business_name'] ?? 'TCocina' }}"
                                    style="height: 24px; width: auto; filter: brightness(0) invert(1);"
                                    onerror="this.onerror=null; this.src='{{ asset('images/log.png') }}';">
                            </div>
                            <div id="notificationList" class="p-0">
                                <div class="text-center p-4 text-muted">
                                    <i class="fas fa-bell-slash mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                                    <p class="mb-0 small">No hay notificaciones</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth

                    <!-- Seguí tu pedido -->
                    <a href="{{ route('orders.my-latest') }}" class="btn d-flex align-items-center" style="padding: 0; border: none; background: transparent; line-height: 1;" title="Seguí tu pedido">
                        <i class="fas fa-paper-plane text-white" style="font-size: 24px; line-height: 1;"></i>
                    </a>

                    <!-- Cart Button -->
                    <button id="cartToggle" class="btn position-relative d-flex align-items-center" style="padding: 0; border: none; background: transparent; line-height: 1;">
                        <i class="fas fa-shopping-cart text-white" style="color:#fff; font-size: 28px; line-height: 1;"></i>
                        <span id="cartBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-white text-beach-primary d-none" style="border: 2px solid var(--beach-primary);">0</span>
                    </button>




                    <!-- Google login en público se maneja solo con el FAB lateral -->
                </div>
            </div>


        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow-1">
        @if (!auth()->check() || (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->role !== 'kitchen'))
            @if (!empty($siteOffline) && $siteOffline && !request()->is('admin*') && !request()->is('login') && !request()->is('kitchen*') && !request()->is('pedido/*/seguimiento') && !((request()->is('/') || request()->is('catalog') || request()->is('catalog*')) && !empty($bannerActiveOrder)))
                <div id="siteOfflineBanner" style="position:fixed;top:0;left:0;right:0;z-index:990;background:linear-gradient(90deg,#1a1a2e 0%,#16213e 100%);border-bottom:2px solid rgba(255,200,0,.35);color:#f0e6c8;padding:10px 16px;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;text-align:center;font-size:.92rem;">
                    <span style="font-size:1.1rem;">🔒</span>
                    <strong style="letter-spacing:.08em;font-size:.95rem;">COCINA CERRADA</strong>
                    <span style="font-size:1.1rem;">🔒</span>
                    <span style="opacity:.25;">|</span>
                    <span>
                        <strong>{{ $businessSettings['site_offline_title'] ?? 'T cocina' }}:</strong>
                        {{ $businessSettings['site_offline_message'] ?? 'Por el momento no estamos tomando pedidos.' }}
                    </span>
                    @if(!empty($bannerActiveOrder))
                    <span style="opacity:.25;">|</span>
                    <a href="{{ route('orders.tracking', $bannerActiveOrder->order_number) }}"
                       style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,215,0,.15);color:#ffd700;font-weight:700;text-decoration:none;border:1px solid rgba(255,215,0,.5);padding:4px 12px;border-radius:20px;font-size:.85rem;white-space:nowrap;">
                        🔥 Tu pedido <strong>#{{ $bannerActiveOrder->order_number }}</strong> está en cocina &nbsp;→
                    </a>
                    @endif
                </div>
                <div id="siteOfflineSpacer" style="height:0;"></div>
                <script>
                    (function () {
                        var banner = document.getElementById('siteOfflineBanner');
                        var spacer = document.getElementById('siteOfflineSpacer');
                        var header = document.getElementById('mainHeader');
                        function update() {
                            var headerBottom = header ? Math.max(0, header.getBoundingClientRect().bottom) : 0;
                            banner.style.top = headerBottom + 'px';
                            spacer.style.height = banner.offsetHeight + 'px';
                        }
                        update();
                        window.addEventListener('scroll', update, { passive: true });
                        window.addEventListener('resize', update, { passive: true });
                        document.addEventListener('DOMContentLoaded', update);
                        window.addEventListener('load', update);
                        if (document.fonts && document.fonts.ready) { document.fonts.ready.then(update); }
                    })();
                </script>
            @endif
        @endif
        @yield('content')
    </main>

    <!-- Global Modals for Product Customization -->
    @if (isset($medallonesConfigs))
    <!-- Hamburger Customization Modal -->
    <div class="modal fade" id="hamburgerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="transform:scale(0.9);transform-origin:top center;margin-top:0;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hamburgerModalTitle">Personalizar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del producto -->
                    <div class="text-center mb-4">
                        <img id="hamburgerModalImage" src="" alt="" class="img-fluid rounded mb-3" style="max-height: 200px; object-fit: cover; width: 100%; cursor: zoom-in;">
                        <h5 class="fw-bold mb-2" id="hamburgerModalProductName"></h5>
                        <p class="text-muted small mb-3" id="hamburgerModalDescription"></p>
                        <!-- Precio dinámico -->
                        <div class="h2 text-beach-primary fw-bold" id="hamburgerModalPrice">$0</div>
                    </div>

                    <div class="product-options mb-3">
                        <div class="row g-2">
                            <!-- Select 1: Medallones -->
                            <div class="col-6 mb-2" id="hamburgerMedallonWrapper">
                                <label class="form-label fw-medium small">1. Medallones:</label>
                                <select class="form-select form-select-sm variant-select" data-variant-name="Medallones"
                                    data-config-type="medallones">
                                    @foreach ($medallonesConfigs as $index => $config)
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            {{ $config->value === 'Doble' ? 'selected' : ($index === 0 ? 'selected' : '') }}>
                                            {{ $config->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select 2: Tipo de Medallón -->
                            <div class="col-6 mb-2" id="hamburgerTipoMedallonWrapper">
                                <label class="form-label fw-medium small">2. Tipo de Medallón:</label>
                                <select class="form-select form-select-sm variant-select" data-variant-name="Tipo de Medallón"
                                    data-config-type="tipo_medallon">
                                    @if (isset($tipoConfigs))
                                        @foreach ($tipoConfigs as $index => $config)
                                            <option value="{{ $config->value }}"
                                                data-price-modifier="{{ $config->price_modifier }}"
                                                {{ $config->value === 'Carne' ? 'selected' : ($index === 0 ? 'selected' : '') }}>
                                                {{ $config->display_value }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Select 3: Dip -->
                            <div class="col-6 mb-2" id="hamburgerDipWrapper">
                                <label class="form-label fw-medium small">3. Dip:</label>
                                <select class="form-select form-select-sm option-select" data-option-name="Dip"
                                    data-config-type="dips">
                                    @if (isset($dipConfigs))
                                        @foreach ($dipConfigs as $index => $config)
                                            <option value="{{ $config->value }}"
                                                data-price-modifier="{{ $config->price_modifier }}"
                                                {{ $index === 0 ? 'selected' : '' }}>
                                                {{ $config->display_value }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Select 4: Aderezos -->
                            <div class="col-6 mb-2" id="hamburgerAderezosWrapper">
                                <label class="form-label fw-medium small">4. Aderezos:</label>
                                <select class="form-select form-select-sm option-select" data-option-name="Aderezos"
                                    data-config-type="aderezos" id="aderezosSelect">
                                    @if (isset($aderezosConfigs))
                                        @foreach ($aderezosConfigs as $index => $config)
                                            <option value="{{ $config->value }}"
                                                data-price-modifier="{{ $config->price_modifier }}"
                                                data-config-id="{{ $config->id }}"
                                                {{ $index === 0 ? 'selected' : '' }}>
                                                {{ $config->display_value }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Select 5: Extras -->
                            <div class="col-6 mb-2" id="hamburgerExtrasWrapper">
                                <label class="form-label fw-medium small">5. Extras:</label>
                                <div id="extras-container">
                                    <div class="extras-row d-flex align-items-center">
                                        <select class="form-select form-select-sm option-select extras-select"
                                                data-option-name="Extras" data-config-type="extras" style="flex: 1;">
                                            <option value="" data-price-modifier="0" selected>Sin extras</option>
                                            @if (isset($extrasConfigs))
                                                @foreach ($extrasConfigs as $config)
                                                    <option value="{{ $config->value }}"
                                                        data-price-modifier="{{ $config->price_modifier }}">
                                                        {{ $config->display_value }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <button type="button" class="btn btn-outline-success btn-sm ms-1 add-extra-btn"
                                                style="width: 31px; height: 31px; padding: 0; flex-shrink: 0;" title="Agregar otro extra">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Select 6: Dip Extra -->
                            <div class="col-6 mb-2" id="hamburgerDipExtraWrapper">
                                <label class="form-label fw-medium small">6. Dip Extra:</label>
                                <div id="dip-extra-container">
                                    <div class="dip-extra-row d-flex align-items-center">
                                        <select class="form-select form-select-sm option-select dip-extra-select"
                                                data-option-name="Dip Extra" data-config-type="dip_extra" style="flex: 1;">
                                            <option value="" data-price-modifier="0" selected>Sin dip extra</option>
                                            @if (isset($dipExtraConfigs))
                                                @foreach ($dipExtraConfigs as $config)
                                                    <option value="{{ $config->value }}"
                                                        data-price-modifier="{{ $config->price_modifier }}">
                                                        {{ $config->display_value }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <button type="button" class="btn btn-outline-success btn-sm ms-1 add-dip-extra-btn"
                                                style="width: 31px; height: 31px; padding: 0; flex-shrink: 0;" title="Agregar otro dip extra">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-beach-primary cart-button" id="hamburgerAddToCart">
                            <span class="cart-button-text">
                                <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                            </span>
                            <span class="cart-button-cart">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <span class="cart-button-tick">
                                <i class="fas fa-check"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (isset($dipConfigs))
    <!-- Dip Modal for Accompaniments -->
    <div class="modal fade" id="dipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dipModalTitle">Elegir Dip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="product-options mb-3">
                        <div class="mb-2">
                            <label class="form-label small text-black fw-medium">Dip:</label>
                            <select class="form-select form-select-sm" id="dipSelect" data-option-name="Dip">
                                @foreach ($dipConfigs as $index => $config)
                                    <option value="{{ $config->value }}"
                                        data-price-modifier="{{ $config->price_modifier }}"
                                        {{ $index === 0 ? 'selected' : '' }}>
                                        {{ $config->display_value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-beach-primary cart-button" id="dipAddToCart">
                            <span class="cart-button-text">
                                <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                            </span>
                            <span class="cart-button-cart">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <span class="cart-button-tick">
                                <i class="fas fa-check"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <footer class="text-white mt-auto footer-themed">
        <div class="py-5">
            <div class="container">
                <div class="row gy-4 justify-content-center text-center">
                    <!-- Brand / About -->
                    <div class="col-md-5">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            @php
                                $footerLogo = $businessSettings['brand_logo_left_url'] ?? null;
                                if ($footerLogo) {
                                    if (str_starts_with($footerLogo, '/storage/') || str_starts_with($footerLogo, '/branding/') || str_starts_with($footerLogo, '/images/')) {
                                        $footerLogo = asset($footerLogo);
                                    } else {
                                        $footerLogo = asset($footerLogo);
                                    }
                                } else {
                                    $footerLogo = asset('images/tcocina_navidad.png');
                                }
                            @endphp
                            <img src="{{ $footerLogo }}"
                                alt="{{ $businessSettings['business_name'] ?? 'TCocina' }}"
                                style="height: 60px; width: auto;"
                                onerror="this.onerror=null; this.src='{{ asset('images/tcocina_navidad.png') }}';" />
                        </div>
                        @if(!empty($businessSettings['footer_description']))
                        <div class="mb-3">
                            <p class="mb-0">{{ $businessSettings['footer_description'] }}</p>
                        </div>
                        @endif
                        <div class="small">
                            <div class="mb-1"><i
                                    class="fas fa-phone me-2"></i>{{ $businessSettings['business_phone'] ?? '' }}
                            </div>
                            <div class="mb-1"><i
                                    class="fas fa-envelope me-2"></i>{{ $businessSettings['business_email'] ?? '' }}
                            </div>
                            <div><i
                                    class="fas fa-map-marker-alt me-2"></i>{{ $businessSettings['business_address'] ?? '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Información legal (colapsable) -->
                    <div class="col-6 col-md-3">
                        <h6 id="footerInfoToggle" class="text-uppercase fw-bold small mb-3 d-flex align-items-center justify-content-center" style="cursor:pointer; user-select:none;">
                            Información
                            <i id="footerInfoCaret" class="fas fa-chevron-down ms-2" style="font-size: .8rem;"></i>
                        </h6>
                        <ul id="footerInfoLinks" class="list-unstyled small mb-0" style="display:none;">
                            <li class="mb-2"><a href="{{ route('legal.privacy') }}"
                                    class="text-white text-decoration-none">Privacidad</a>
                            </li>
                            <li class="mb-2"><a href="{{ route('legal.terms') }}"
                                    class="text-white text-decoration-none">Términos y
                                    Condiciones</a></li>
                            <li class="mb-2"><a href="{{ route('legal.shipping') }}"
                                    class="text-white text-decoration-none">Envíos y
                                    Devoluciones</a></li>
                            <li><a href="{{ route('legal.faq') }}" class="text-white text-decoration-none">Preguntas
                                    Frecuentes</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Redes Sociales -->
                    <div class="col-12 col-md-4">
                        <h6 class="text-uppercase fw-bold small mb-3">Redes Sociales</h6>
                        <p class="small mb-4">Seguinos en nuestras redes sociales</p>
                        <div class="d-flex gap-3 justify-content-center">
                            @php
                                $facebookUrl = $businessSettings['facebook_url'] ?? null;
                                $instagramUrl = $businessSettings['instagram_url'] ?? null;
                                $linkedinUrl = $businessSettings['linkedin_url'] ?? null;
                                $whatsappUrl = $businessSettings['whatsapp_url'] ?? null;
                                if (!$whatsappUrl && ($businessSettings['business_phone'] ?? null)) {
                                    $phone = preg_replace('/\D+/', '', $businessSettings['business_phone']);
                                    if ($phone) { $whatsappUrl = 'https://wa.me/' . $phone; }
                                }
                            @endphp

                            @if ($facebookUrl)
                                <a href="{{ $facebookUrl }}" target="_blank" class="text-white fs-4"
                                    title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            @endif

                            @if ($instagramUrl)
                                <a href="{{ $instagramUrl }}" target="_blank" class="text-white fs-4"
                                    title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            @endif

                            @if ($whatsappUrl)
                                <a href="{{ $whatsappUrl }}" target="_blank" class="text-white fs-4" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif

                            @if ($linkedinUrl)
                                <a href="{{ $linkedinUrl }}" target="_blank" class="text-white fs-4"
                                    title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom" id="footer-bl" style="--glow-x:-300px;--glow-y:50%;">
            <canvas id="bl-stars-canvas" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:0;"></canvas>
            <a href="https://stgrandesligas.com" target="_blank" rel="noopener"
               style="display:flex;align-items:center;opacity:0.75;transition:opacity 0.2s;position:relative;z-index:1;"
               onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.75">
                <img src="{{ asset('blstudiologofooter.jpeg') }}" alt="blstudio" style="height:26px;display:block;">
            </a>
            <p class="footer-bl-phrase" style="position:relative;z-index:1;">¿Ya tenés una web <strong>a medida para tu negocio</strong>?.</p>
            <a href="https://stgrandesligas.com" target="_blank" rel="noopener" class="footer-bl-btn" style="position:relative;z-index:1;text-decoration:none;">visitanos →</a>
        </div>
    </footer>

    <!-- Hoja inferior de carrito (solo móvil; por encima del header y la app bar del catálogo) -->
    <div id="mobileCartBottomSheet" class="mobile-cart-sheet" aria-hidden="true" role="dialog"
        aria-modal="true" aria-labelledby="mobileCartSheetTitleLabel">
        <div class="mobile-cart-sheet__backdrop" data-mobile-cart-sheet-dismiss="true"></div>
        <div class="mobile-cart-sheet__panel">
            <div class="mobile-cart-sheet__header-bar d-flex align-items-center justify-content-between gap-2 px-3 pt-1 pb-0 flex-shrink-0">
                <div id="mobileCartSheetTitle" class="mobile-cart-sheet__title-slot d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <span class="mobile-cart-sheet__header-cart-icon flex-shrink-0" style="color:#0a2540;" aria-hidden="true">
                        <lord-icon
                            src="{{ asset('lordicons/carritolordicon.json') }}"
                            trigger="loop"
                            colors="primary:#0a2540,secondary:#0a2540"
                            style="width:28px;height:28px">
                        </lord-icon>
                    </span>
                    <span class="mobile-cart-sheet__desktop-title fw-semibold" style="color:#0a2540;">Tu Pedido</span>
                </div>
                <span id="mobileCartSheetTitleLabel" class="visually-hidden">Tu pedido</span>
                <button type="button" class="mobile-cart-sheet__chevron flex-shrink-0" id="mobileCartSheetCollapseBtn"
                    aria-label="Plegar y volver al menú">
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
                <button type="button" class="mobile-cart-sheet__close-desktop flex-shrink-0" id="mobileCartSheetCloseDesktop"
                    aria-label="Cerrar carrito" style="display:none;">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="mobile-cart-sheet__body flex-fill overflow-hidden d-flex flex-column min-h-0 px-3 pt-1">
                <div id="mobileCartSheetItems" class="mobile-cart-sheet__items flex-grow-1 min-h-0 w-100"></div>
                <div id="mobileCartLoyaltyBanner" class="mobile-cart-loyalty-banner d-none mb-1 flex-shrink-0"></div>
                <div id="mobileCartSheetTotalsWrap" class="mobile-cart-sheet__totals card border-0 bg-light rounded-3 p-2 mt-1 mb-1 flex-shrink-0">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span id="mobileCartSheetSubtotal" class="text-beach-dark fw-medium">$0</span>
                    </div>
                    <div id="mobileCartSheetCouponDiscountRow" class="d-none d-flex justify-content-between small mb-1">
                        <span id="mobileCartSheetCouponDiscountLabel" class="text-success">Descuento cupón</span>
                        <span id="mobileCartSheetCouponDiscountAmount" class="text-success">-$0</span>
                    </div>
                    <div id="mobileCartCouponSection" class="mobile-cart-coupon mt-2 pt-2 border-top">
                        <div id="mobileCartCouponToggle" class="mobile-cart-coupon__header d-flex align-items-center justify-content-between" style="cursor:pointer;">
                            <p class="mobile-cart-coupon__title small text-muted mb-0"><strong>¿Tenés un cupón?</strong> <span class="mobile-cart-coupon__hint-inline">Podés chequearlo ahora.</span></p>
                            <span class="mobile-cart-coupon__chevron" aria-hidden="true">›</span>
                        </div>
                        <div id="mobileCartCouponBody" class="mobile-cart-coupon__body d-none mt-2">
                            <div class="mobile-cart-coupon__row d-flex align-items-center justify-content-between">
                                <div id="mobileCartCouponSlots" class="mobile-cart-coupon__slots d-flex" aria-label="Ingresá tu cupón"></div>
                                <button type="button" id="mobileCartCouponApplyBtn" class="mobile-cart-coupon__apply-btn btn btn-sm btn-outline-danger mobile-cart-sheet__remove-btn" aria-label="Validar cupón">
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div id="mobileCartCouponAppliedText" class="mobile-cart-coupon__applied d-none mt-1 text-success small text-center"></div>
                            <div id="mobileCartCouponError" class="mobile-cart-coupon__error d-none mt-1 text-danger small text-center"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                        <span class="fw-semibold text-beach-dark">Total</span>
                        <span id="mobileCartSheetTotal" class="h6 fw-bold text-beach-primary mb-0">$0</span>
                    </div>
                </div>
            </div>
            <div class="mobile-cart-sheet__footer border-top bg-white px-3 py-2 flex-shrink-0">
                <button type="button" id="mobileProceedToCart" class="btn btn-beach-primary w-100 py-2 fw-semibold text-uppercase mobile-proceed-cart-btn">
                    <span class="mobile-proceed-cart-btn__label">Finalizar pedido</span>
                    <span class="mobile-proceed-cart-btn__icon" aria-hidden="true">
                        <lord-icon
                            src="{{ asset('lordicons/carritolordicon.json') }}"
                            trigger="loop"
                            colors="primary:#0a2540,secondary:#0a2540"
                            style="width:28px;height:28px">
                        </lord-icon>
                    </span>
                </button>
                <p class="small text-muted text-center mt-1 mb-0" style="font-size: 0.72rem;">Elegí delivery ó retiro y confirmá tu pedido.</p>
            </div>
        </div>
    </div>

    <style>
        .mobile-cart-sheet {
            --mobile-cart-sheet-h: 88dvh;
            position: fixed;
            inset: 0;
            z-index: 1060;
            pointer-events: none;
        }

        .mobile-cart-sheet.is-open {
            pointer-events: auto;
        }

        .mobile-cart-sheet__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(8, 20, 40, 0.52);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            opacity: 0;
            transition: opacity 0.32s ease;
        }

        .mobile-cart-sheet.is-open .mobile-cart-sheet__backdrop {
            opacity: 1;
        }

        .mobile-cart-sheet__panel {
            position: absolute;
            left: 1mm;
            right: 1mm;
            bottom: 0;
            height: min(var(--mobile-cart-sheet-h), calc(100dvh - env(safe-area-inset-bottom, 0px) - 12px));
            max-height: calc(100dvh - env(safe-area-inset-top, 0px) - 24px);
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 2px solid var(--beach-primary, #0c6568);
            border-bottom: none;
            border-radius: 22px 22px 0 0;
            box-shadow: 0 -16px 48px rgba(0, 0, 0, 0.28);
            transform: translateY(105%);
            transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }

        .mobile-cart-sheet.is-open .mobile-cart-sheet__panel {
            transform: translateY(0);
        }

        .mobile-cart-sheet__header-bar {
            min-height: 44px;
        }

        .mobile-cart-sheet__chevron {
            border: none;
            background: rgba(12, 101, 104, 0.08);
            color: var(--beach-primary, #0c6568);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            align-self: center;
        }

        .mobile-cart-sheet__chevron:active {
            transform: scale(0.96);
        }

        .mobile-cart-sheet__header-cart-icon {
            font-size: 1.1rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
        }

        .mobile-cart-sheet__header-cart-icon lord-icon {
            display: block;
        }

        .mobile-proceed-cart-btn {
            position: relative;
            overflow: hidden;
        }
        .mobile-proceed-cart-btn .mobile-proceed-cart-btn__label {
            display: inline-block;
            transition: opacity 0.18s ease, transform 0.18s ease;
        }
        .mobile-proceed-cart-btn .mobile-proceed-cart-btn__icon {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0.85);
            transition: opacity 0.18s ease, transform 0.18s ease;
            pointer-events: none;
        }
        .mobile-proceed-cart-btn:hover .mobile-proceed-cart-btn__label,
        .mobile-proceed-cart-btn:focus-visible .mobile-proceed-cart-btn__label,
        .mobile-proceed-cart-btn:active .mobile-proceed-cart-btn__label {
            opacity: 0;
            transform: scale(0.9);
        }
        .mobile-proceed-cart-btn:hover .mobile-proceed-cart-btn__icon,
        .mobile-proceed-cart-btn:focus-visible .mobile-proceed-cart-btn__icon,
        .mobile-proceed-cart-btn:active .mobile-proceed-cart-btn__icon {
            opacity: 1;
            transform: scale(1);
        }

        .mobile-cart-sheet__title-slot .desktop-view-order-inner {
            display: none !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 0.45rem !important;
            flex: 1 1 auto;
            min-width: 0;
        }

        .mobile-cart-sheet__title-slot .desktop-view-order-lottie {
            display: none !important;
        }

        .mobile-cart-sheet__title-slot .desktop-view-order-label {
            order: 1;
            color: var(--beach-primary, #0c6568) !important;
            letter-spacing: 0.06em;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-cart-sheet__body {
            min-height: 0;
            flex: 1 1 auto;
        }

        .mobile-cart-sheet__items {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            min-height: 0;
            flex: 1 1 auto;
        }

        .mobile-cart-sheet__line {
            border-color: rgba(0, 0, 0, 0.06) !important;
        }

        .mobile-cart-sheet__line-img {
            width: 64px;
            min-width: 64px;
            object-fit: cover;
            align-self: stretch;
            height: 100%;
            min-height: 64px;
            border-radius: 8px !important;
            display: block;
            margin-top: 6px;
        }

        .mobile-cart-sheet__line-meta {
            line-height: 1.35;
            word-break: break-word;
        }

        .mobile-cart-sheet__qty-btn {
            min-width: 2.25rem;
            padding: 0.2rem 0.45rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .mobile-cart-sheet__remove-btn {
            padding: 0.2rem 0.5rem;
        }

        .mobile-cart-sheet__resumen-heading {
            letter-spacing: 0.02em;
        }

        .mobile-cart-sheet__coupon-hint {
            font-size: 0.72rem;
            line-height: 1.35;
            letter-spacing: 0.01em;
        }

        .mobile-cart-coupon__chevron {
            font-size: 1rem;
            color: #9ca3af;
            transition: transform 0.25s ease;
            display: inline-block;
            line-height: 1;
            flex-shrink: 0;
            margin-left: 6px;
        }
        .mobile-cart-coupon__chevron.open {
            transform: rotate(90deg);
        }
        .mobile-cart-coupon__title {
            font-size: 0.72rem;
            font-weight: 400;
            color: #6c757d;
            letter-spacing: 0.01em;
            line-height: 1.35;
            text-align: left;
        }

        /* Estado: cupón aplicado */
        .mobile-cart-coupon-applied-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6px 0 2px;
            gap: 4px;
            animation: couponAppliedIn 0.3s ease;
        }
        @keyframes couponAppliedIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }
        .mobile-cart-coupon-applied-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .mobile-cart-coupon-applied-code {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #111827;
            font-family: monospace;
        }
        .mobile-cart-coupon-applied-discount {
            font-size: 0.95rem;
            font-weight: 700;
            color: #16a34a;
        }
        .mobile-cart-coupon-applied-label {
            font-size: 0.72rem;
            color: #16a34a;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .mobile-cart-coupon-remove-btn {
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 0.68rem;
            cursor: pointer;
            padding: 2px 0;
            text-decoration: underline;
            line-height: 1;
        }
        .mobile-cart-coupon-remove-btn:hover { color: #ef4444; }

        .mobile-cart-coupon__hint-inline {
            font-weight: 400;
            color: #6c757d;
        }

        .mobile-cart-coupon__slots {
            flex-wrap: nowrap;
            flex: 1 1 auto;
            gap: 3px;
        }

        .mobile-cart-coupon-slot {
            flex: 1 1 0;
            min-width: 0;
            max-width: 2.4rem;
            width: 0;
            height: 1.85rem;
            border: 1.5px solid #111 !important;
            border-radius: 6px;
            text-align: center;
            font-size: 16px; /* ≥16px evita zoom automático en iOS Safari */
            font-weight: 700;
            text-transform: uppercase;
            color: #22313f;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            touch-action: manipulation;
        }

        .mobile-cart-coupon-slot:focus {
            outline: none;
            border-color: var(--beach-primary, #0c6568);
            box-shadow: 0 0 0 2px rgba(12, 101, 104, 0.16);
        }

        .mobile-cart-coupon-slot.filled {
            border-color: var(--beach-primary, #0c6568);
            background: rgba(12, 101, 104, 0.05);
        }

        .mobile-cart-coupon-slot.error {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.05);
        }

        .mobile-cart-coupon__apply-btn {
            width: 2rem;
            min-width: 2rem;
            height: 1.85rem;
            padding: 0.15rem 0.35rem !important;
            border-width: 1.5px !important;
            flex: 0 0 auto;
            transition: color 0.2s, background-color 0.2s, border-color 0.2s;
            color: #16a34a !important;
            background-color: transparent !important;
            border-color: #16a34a !important;
        }

        .mobile-cart-coupon__apply-btn:hover,
        .mobile-cart-coupon__apply-btn:active {
            color: #fff !important;
            background-color: #16a34a !important;
            border-color: #16a34a !important;
        }

        .mobile-cart-coupon__apply-btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .mobile-cart-coupon__apply-btn i {
            font-size: 0.9rem;
            line-height: 1;
            display: block;
        }

        .mobile-cart-coupon__apply-btn.state-loading {
            color: #fff !important;
            background-color: #f59e0b !important;
            border-color: #f59e0b !important;
            animation: coupon-btn-pulse 0.9s ease-in-out infinite alternate;
        }

        .mobile-cart-coupon__apply-btn.state-success {
            color: #fff !important;
            background-color: #16a34a !important;
            border-color: #16a34a !important;
        }

        .mobile-cart-coupon__apply-btn.state-success:hover,
        .mobile-cart-coupon__apply-btn.state-error:hover {
            opacity: 0.88;
        }

        .mobile-cart-coupon__apply-btn.state-error {
            color: #fff !important;
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        @keyframes coupon-btn-pulse {
            from { box-shadow: 0 0 0 0 rgba(245,158,11,0.5); }
            to   { box-shadow: 0 0 0 5px rgba(245,158,11,0); }
        }

        @keyframes coupon-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        .mobile-cart-coupon__apply-btn.state-loading i {
            display: inline-block;
            animation: coupon-spin 0.7s linear infinite;
        }

        .mobile-cart-coupon__applied {
            line-height: 1.25;
        }

        .mobile-cart-coupon__error {
            line-height: 1.25;
        }

        #mobileProceedToCart.btn-beach-primary {
            border: 2px solid var(--beach-primary, #0c6568) !important;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease,
                box-shadow 0.2s ease;
            -webkit-tap-highlight-color: transparent;
        }

        @media (hover: hover) {
            #mobileProceedToCart.btn-beach-primary:hover,
            #mobileProceedToCart.btn-beach-primary:focus-visible {
                background-color: #fff !important;
                background: #fff !important;
                color: var(--beach-primary, #0c6568) !important;
                border-color: var(--beach-primary, #0c6568) !important;
                box-shadow: none !important;
            }
        }

        @media (hover: none) {
            #mobileProceedToCart.btn-beach-primary:hover,
            #mobileProceedToCart.btn-beach-primary:focus,
            #mobileProceedToCart.btn-beach-primary:focus-visible {
                background-color: var(--beach-primary, #0c6568) !important;
                color: #fff !important;
                border-color: var(--beach-primary, #0c6568) !important;
                box-shadow: none !important;
            }
        }

        #mobileProceedToCart.btn-beach-primary:active {
            background-color: #fff !important;
            background: #fff !important;
            color: var(--beach-primary, #0c6568) !important;
            border-color: var(--beach-primary, #0c6568) !important;
            box-shadow: none !important;
        }

        body.mobile-cart-sheet-open {
            overflow: hidden;
            touch-action: none;
        }
        @media (min-width: 768px) {
            body.mobile-cart-sheet-open {
                overflow: hidden;
                touch-action: auto;
            }
        }

        /* Loyalty sticker preview banner */
        .mobile-cart-loyalty-banner {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 9px 12px 8px;
            animation: loyaltyBannerIn 0.3s ease;
        }
        @keyframes loyaltyBannerIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .mobile-cart-loyalty-banner__text {
            font-size: .78rem;
            font-weight: 600;
            color: #166534;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 6px;
        }
        .mobile-cart-loyalty-banner__text strong {
            color: #15803d;
        }
        .mobile-cart-loyalty-arrow {
            transition: transform 0.25s ease;
            display: inline-block;
        }
        .mobile-cart-loyalty-dots {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .mobile-cart-loyalty-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #dcfce7;
            border: 1.5px solid #4ade80;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .mobile-cart-loyalty-dot img {
            width: 14px;
            height: 14px;
            object-fit: contain;
            filter: brightness(0) saturate(100%) invert(20%) sepia(80%) saturate(500%) hue-rotate(108deg) brightness(85%) contrast(105%);
        }
        .mobile-cart-loyalty-progress-wrap {
            margin-top: 6px;
        }
        .mobile-cart-loyalty-progress-label {
            font-size: .72rem;
            color: #166534;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .mobile-cart-loyalty-progress-track {
            width: 100%;
            height: 8px;
            background: #bbf7d0;
            border-radius: 99px;
            overflow: hidden;
            position: relative;
        }
        .mobile-cart-loyalty-progress-bar {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #16a34a 0%, #4ade80 100%);
            transition: width 0.5s cubic-bezier(.4,0,.2,1);
            position: relative;
        }
        .mobile-cart-loyalty-progress-bar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 60%, rgba(255,255,255,.25) 100%);
            border-radius: 99px;
        }
        .mobile-cart-loyalty-progress-pct {
            font-size: .68rem;
            color: #15803d;
            font-weight: 700;
            text-align: right;
            margin-top: 2px;
        }

        .mobile-cart-sheet__close-desktop {
            border: none;
            background: rgba(12, 101, 104, 0.08);
            color: var(--beach-primary, #0c6568);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .mobile-cart-sheet__close-desktop:hover { background: rgba(12,101,104,.18); }

        @media (min-width: 768px) {
            /* En desktop: sidebar derecho en lugar de bottom sheet */
            .mobile-cart-sheet__panel {
                left: auto;
                right: 0;
                bottom: 0;
                top: 0;
                width: 420px;
                max-width: 96vw;
                height: 100dvh !important;
                max-height: 100dvh !important;
                border-radius: 22px 0 0 22px;
                border: 2px solid var(--beach-primary, #0c6568);
                border-right: none;
                transform: translateX(110%);
                transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .mobile-cart-sheet.is-open .mobile-cart-sheet__panel {
                transform: translateX(0);
            }
            /* Ocultar el chevron de plegar (solo tiene sentido en mobile) */
            .mobile-cart-sheet__chevron { display: none !important; }
            /* Mostrar el botón ✕ y el título en desktop */
            .mobile-cart-sheet__close-desktop { display: flex !important; }
            .mobile-cart-sheet__desktop-title { display: inline !important; font-size: 1rem; }
            /* Footer del sheet con más padding en desktop */
            .mobile-cart-sheet__footer { padding-bottom: 1.25rem; }
            /* Slots del cupón un poco más grandes en desktop */
            .mobile-cart-coupon-slot { height: 2.1rem; font-size: 15px; }
        }

        @keyframes slideUpFade {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-10vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(110vh) rotate(720deg);
                opacity: 0;
            }
        }

        .confetti-container {
            position: fixed;
            inset: 0;
            z-index: 9999;
            pointer-events: none;
            overflow: hidden;
        }

        .confetti-piece {
            position: absolute;
            width: 8px;
            height: 16px;
            opacity: 0;
            border-radius: 2px;
        }
    </style>

    <!-- Offcanvas Cart (Global) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
        <div class="offcanvas-header bg-white border-bottom">
            <h5 class="offcanvas-title text-beach-dark">Tu Carrito</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <!-- Cart Items -->
            <div id="cartItems" class="flex-fill overflow-auto p-3">
                <!-- Cart items will be dynamically inserted here -->
            </div>

            <!-- Cart Footer -->
            <div id="cartFooter" class="border-top p-3 bg-white">
                <div class="d-grid gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-medium text-beach-dark">Total:</span>
                        <span id="cartTotal" class="h5 fw-bold text-beach-primary mb-0">$0</span>
                    </div>
                    <button id="proceedToCart" class="btn btn-beach-primary w-100">
                        Ver Carrito Completo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>

    <!-- Floating WhatsApp Button -->
    @php
        $whatsappUrl = $businessSettings['whatsapp_url'] ?? null;
        if (!$whatsappUrl && ($businessSettings['business_phone'] ?? null)) {
            $phone = preg_replace('/\D+/', '', $businessSettings['business_phone']);
            if ($phone) { $whatsappUrl = 'https://wa.me/' . $phone; }
        }
    @endphp
    @if ($whatsappUrl)
        <div class="whatsapp-fab" id="whatsappFab">
            <div class="whatsapp-toggle" id="whatsappToggle">
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="whatsapp-content">
                <div class="tip-wrap">
                    <div class="tip" id="waTip"></div>
                </div>
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="fab-btn" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp fa-lg"></i>
                </a>
            </div>
        </div>
        <script>
            (function(){
                var fab = document.getElementById('whatsappFab');
                var toggle = document.getElementById('whatsappToggle');
                var tip = document.getElementById('waTip');
                var isExpanded = false;
                var messageText = '¿Tenés alguna duda? Hablános directamente';
                var typingTimeout = null;

                // Typewriter effect function
                function typeWriter(element, text, speed) {
                    if(!element) return;
                    element.textContent = '';
                    var i = 0;
                    function type() {
                        if (i < text.length) {
                            element.textContent += text.charAt(i);
                            i++;
                            setTimeout(type, speed);
                        }
                    }
                    type();
                }

                // Show message when expanded
                function showMessage() {
                    if(tip && isExpanded){
                        // Clear any existing timeout
                        if(typingTimeout) clearTimeout(typingTimeout);
                        
                        // Reset and show message
                        tip.classList.add('show');
                        typeWriter(tip, messageText, 50); // 50ms per letter
                        
                        // Hide message and collapse everything after 8 seconds
                        typingTimeout = setTimeout(function(){ 
                            tip.classList.remove('show');
                            // Collapse the entire fab when text disappears
                            isExpanded = false;
                            fab.classList.remove('expanded');
                        }, 8000);
                    } else if(tip && !isExpanded) {
                        // Hide message when collapsed
                        tip.classList.remove('show');
                        if(typingTimeout) clearTimeout(typingTimeout);
                    }
                }

                // Toggle expand/collapse
                if(toggle && fab){
                    toggle.addEventListener('click', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        isExpanded = !isExpanded;
                        fab.classList.toggle('expanded', isExpanded);
                        showMessage();
                    });
                }
            })();
        </script>
    @endif

    <!-- Floating Google Login Button (solo para invitados y cuando el álbum está encendido) -->
    @guest
    @if (!($loyaltyOffline ?? false))
        <div class="google-fab" id="googleFab">
            <div class="google-toggle" id="googleToggle">
                <svg class="google-fab-glyph" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                </svg>
            </div>
            <div class="google-content">
                <div class="tip-wrap">
                    <div class="tip" id="googleTip"></div>
                </div>
                <a href="{{ route('auth.google.redirect') }}" class="google-fab-circle" aria-label="Ingresar con Google">
                    <svg class="google-fab-glyph-lg" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    </svg>
                </a>
            </div>
        </div>
        <script>
            (function(){
                var fab = document.getElementById('googleFab');
                var toggle = document.getElementById('googleToggle');
                var tip = document.getElementById('googleTip');
                var isExpanded = false;
                var messageText = 'Logueate con google y gana premios con tus compras';
                var typingTimeout = null;
                var typeAnimationTimeout = null;

                function typeWriter(element, text, speed) {
                    if(!element) return;
                    // Clear any existing typing animation
                    if(typeAnimationTimeout) clearTimeout(typeAnimationTimeout);
                    element.textContent = '';
                    var i = 0;
                    function type() {
                        if (i < text.length) {
                            element.textContent += text.charAt(i);
                            i++;
                            typeAnimationTimeout = setTimeout(type, speed);
                        }
                    }
                    type();
                }

                function showMessage() {
                    if(tip && isExpanded){
                        if(typingTimeout) clearTimeout(typingTimeout);
                        tip.classList.add('show');
                        typeWriter(tip, messageText, 50);
                        typingTimeout = setTimeout(function(){
                            tip.classList.remove('show');
                            isExpanded = false;
                            fab.classList.remove('expanded');
                        }, 8000);
                    } else if(tip && !isExpanded) {
                        tip.classList.remove('show');
                        if(typingTimeout) clearTimeout(typingTimeout);
                        if(typeAnimationTimeout) clearTimeout(typeAnimationTimeout);
                    }
                }

                var googleLoginUrl = '{{ route('auth.google.redirect') }}';

                if(toggle && fab){
                    toggle.addEventListener('click', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        window.location.href = googleLoginUrl;
                    });
                }

                // Primera aparición a los 10s, luego cada 60s
                function autoExpand() {
                    if(!isExpanded && fab){
                        isExpanded = true;
                        fab.classList.add('expanded');
                        showMessage();
                    }
                }
                setTimeout(function(){
                    autoExpand();
                    setInterval(autoExpand, 60000);
                }, 10000);
            })();
        </script>
    @endif
    @endguest

    <!-- Floating User Button (solo para logueados) - mismo lugar que Google FAB -->
    @auth
        <div class="user-fab" id="userFab">
            <div class="user-fab-backdrop" id="userFabBackdrop" aria-hidden="true"></div>
            <div class="user-fab-toggle" id="userFabToggle">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-fab-content">
                <div class="user-fab-links">
                    <a href="{{ route('loyalty.dashboard') }}" class="user-fab-link"><i class="fas fa-sun me-2"></i>Mi Álbum</a>
                    <a href="{{ route('orders.my-latest') }}" class="user-fab-link"><i class="fas fa-map-marker-alt me-2"></i>Seguir mi pedido</a>
                    <a href="{{ route('profile.edit') }}" class="user-fab-link"><i class="fas fa-id-card me-2"></i>Mis Datos</a>
                    @if (Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="user-fab-link"><i class="fas fa-cog me-2"></i>Administración</a>
                    @endif
                    @if (in_array(Auth::user()->role, ['admin', 'kitchen']))
                        <a href="{{ route('kitchen.index') }}" class="user-fab-link"><i class="fas fa-fire me-2"></i>Cocina</a>
                    @endif
                    <a href="#" class="user-fab-link" onclick="event.preventDefault(); document.getElementById('user-fab-logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
                </div>
                <form id="user-fab-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
        <script>
            (function(){
                var fab = document.getElementById('userFab');
                var toggle = document.getElementById('userFabToggle');
                var backdrop = document.getElementById('userFabBackdrop');
                if (toggle && fab) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        fab.classList.toggle('expanded');
                        if (backdrop) backdrop.setAttribute('aria-hidden', fab.classList.contains('expanded') ? 'false' : 'true');
                    });
                    if (backdrop) {
                        backdrop.addEventListener('click', function() {
                            fab.classList.remove('expanded');
                            backdrop.setAttribute('aria-hidden', 'true');
                        });
                    }
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && fab.classList.contains('expanded')) {
                            fab.classList.remove('expanded');
                            if (backdrop) backdrop.setAttribute('aria-hidden', 'true');
                        }
                    });
                }
            })();
        </script>
    @endauth

    <!-- Preloader Script -->
    <script>
        // Mostrar header cuando el DOM esté listo (pero el preloader seguirá visible)
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('mainHeader');
            if (header) {
                header.classList.remove('header-hidden');
            }
        });

        function _hidePreloader() {
            const preloader = document.getElementById('preloader');
            if (preloader && preloader.style.display !== 'none') {
                preloader.classList.add('fade-out');
                setTimeout(function() {
                    preloader.style.display = 'none';
                }, 500);
            }
        }

        // Timeout de seguridad: si window.load no dispara o tarda demasiado, ocultamos igual
        setTimeout(_hidePreloader, 3000);

        window.addEventListener('load', function() {
            _hidePreloader();

            // Footer info toggle
            var infoToggle = document.getElementById('footerInfoToggle');
            var infoLinks = document.getElementById('footerInfoLinks');
            var caret = document.getElementById('footerInfoCaret');
            if (infoToggle && infoLinks) {
                infoToggle.addEventListener('click', function(){
                    var isHidden = infoLinks.style.display === 'none' || !infoLinks.style.display;
                    infoLinks.style.display = isHidden ? 'block' : 'none';
                    if(caret){ caret.classList.toggle('fa-rotate-180', isHidden === false); }
                });
            }
        });
    </script>

    @stack('scripts')

    {{-- Cuando el sitio está cerrado: bloquear flujo de pedidos (agregar al carrito → checkout) --}}
    <script>
        (function () {
            window.__siteOffline = @json((bool) ($siteOffline ?? false));
            if (!window.__siteOffline) return;

            var MSG_ORDER = @json($businessSettings['site_offline_message'] ?? 'El local está cerrado. Podés explorar el menú pero los pedidos no están disponibles por el momento.');

            // Selectores reales de botones de pedido en la app
            var ORDER_SELECTOR = [
                '.add-to-cart-btn',       // botones "agregar al carrito" en el catálogo
                '#hamburgerAddToCart',     // botón del modal de hamburguesas
                '#dipAddToCart',           // botón del modal de dips
                '#checkoutBtn',            // botón "ir al checkout" en /cart
                '#proceedToCart',          // botón "Ver Carrito Completo" en el offcanvas
                'a[href*="/checkout"]',
                'a[href*="/turnos"]',
            ].join(', ');

            // Interceptar clicks antes de que lleguen al handler original
            document.addEventListener('click', function (e) {
                var target = e.target.closest && e.target.closest(ORDER_SELECTOR);
                if (!target) return;
                e.preventDefault();
                e.stopImmediatePropagation();
                try { alert(MSG_ORDER); } catch (_) {}
            }, true);

            // Deshabilitar visualmente al cargar el DOM
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll(ORDER_SELECTOR).forEach(function (el) {
                    el.setAttribute('disabled', 'disabled');
                    el.setAttribute('title', MSG_ORDER);
                    el.style.opacity = '0.45';
                    el.style.cursor = 'not-allowed';
                    el.style.pointerEvents = 'none';
                });

                // Los botones .add-to-cart-btn se renderizan dinámicamente — observar el DOM
                var observer = new MutationObserver(function () {
                    document.querySelectorAll('.add-to-cart-btn:not([data-offline-disabled])').forEach(function (el) {
                        el.setAttribute('disabled', 'disabled');
                        el.setAttribute('title', MSG_ORDER);
                        el.setAttribute('data-offline-disabled', '1');
                        el.style.opacity = '0.45';
                        el.style.cursor = 'not-allowed';
                        el.style.pointerEvents = 'none';
                    });
                });
                observer.observe(document.body, { childList: true, subtree: true });
            });
        })();
    </script>

    <!-- Mobile Header Styles -->
    <style>
        /* Footer themed background and typography */
        .footer-themed{
            /* Fondo del footer tintado por el color de marca */
            background: linear-gradient(180deg, var(--beach-primary) 0%, var(--beach-primary) 100%), url('{{ asset('productos/fondo/fondo.png') }}');
            background-blend-mode: multiply;
            background-size: 380px auto; /* subtle pattern */
            background-repeat: repeat;
            background-position: center;
            box-shadow: inset 0 6px 24px rgba(0,0,0,0.25);
        }
        /* Header themed background: solid brand primary color (no transparency) */
        .header-themed{
            background: var(--beach-primary) !important;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }
        .footer-themed h3,
        .footer-themed h6{ letter-spacing:.4px; }
        .footer-themed h3{ font-size: 2rem; font-weight: 800; }
        .footer-themed h6{ font-size: 1rem; font-weight: 700; opacity:.95; }
        .footer-themed p, .footer-themed .small, .footer-themed a{ font-size: 1.05rem; }
        .footer-themed a:hover{ color:#b9f3ea !important; }
        
        /* Sin barras fijas abajo, el footer no debe dejar espacio extra */
        footer.footer-themed {
            margin-bottom: 0 !important;
            position: relative;
            z-index: 1;
        }
        
        /* Cuando hay barras fijas abajo, agregar separación para que no tapen el footer */
        body:has(.desktop-filter-appbar) footer.footer-themed,
        body:has(.desktop-progress-appbar) footer.footer-themed {
            margin-bottom: 120px !important;
        }
        
        @media (max-width: 767px) {
            body:has(.desktop-filter-appbar) footer.footer-themed,
            body:has(.desktop-progress-appbar) footer.footer-themed,
            body:has(.mobile-filter-appbar) footer.footer-themed {
                margin-bottom: 84px !important;
            }
            
            #main-header {
                min-height: 60px !important;
                padding: 4px 0 !important;
            }
        }

        /* FABs: misma altura para WhatsApp, Google y Usuario */
        :root{ --fab-top: 88px; --fab-top-mobile: 72px; }
        /* Floating WhatsApp Button - Left Side Collapsible */
        .whatsapp-fab{
            position:fixed;
            left:-2px;
            top:var(--fab-top);
            z-index:10004;
            display:flex;
            align-items:flex-start;
            transition:left .3s ease;
        }
        .whatsapp-fab .whatsapp-toggle{
            width:36px;
            height:36px;
            background:#25D366;
            border-radius:0 18px 18px 0;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            box-shadow:0 4px 12px rgba(0,0,0,.25);
            transition:all .3s ease;
            z-index:9986;
            opacity:0.85;
            animation: whatsappPulse 3s ease-in-out infinite;
        }
        .whatsapp-fab .whatsapp-toggle:hover{
            opacity:1;
            box-shadow:0 6px 16px rgba(0,0,0,.35);
            animation-play-state: paused;
        }
        @keyframes whatsappPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 0 rgba(37, 211, 102, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 8px rgba(37, 211, 102, 0);
            }
        }
        .whatsapp-fab .whatsapp-toggle i{
            color:#fff;
            font-size:14px;
            transition:transform .3s ease;
        }
        .whatsapp-fab.expanded .whatsapp-toggle i{
            transform:rotate(180deg);
        }
        .whatsapp-fab.expanded .whatsapp-toggle{
            animation: none;
        }
        .whatsapp-fab .whatsapp-content{
            display:flex;
            align-items:center;
            gap:8px;
            margin-left:-200px;
            opacity:0;
            pointer-events:none;
            transition:all .3s ease;
        }
        .whatsapp-fab.expanded .whatsapp-content{
            margin-left:0;
            opacity:1;
            pointer-events:auto;
        }
        .whatsapp-fab .fab-btn{
            width:56px;
            height:56px;
            border-radius:50%;
            background:#25D366;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 12px 28px rgba(0,0,0,.35), 0 8px 16px rgba(0,0,0,.2), 0 0 0 1px rgba(0,0,0,.06) inset;
            transition:transform .15s ease, box-shadow .2s ease;
            flex-shrink:0;
        }
        .whatsapp-fab .fab-btn i{
            font-size:30px;
            line-height:1;
        }
        .whatsapp-fab .fab-btn:hover{
            transform:translateY(-2px);
            box-shadow:0 14px 28px rgba(0,0,0,.3);
        }
        .whatsapp-fab .tip-wrap{
            display:flex;
            flex-direction:column;
            align-items:flex-start;
        }
        .whatsapp-fab .tip{
            background:rgba(0,0,0,.8);
            color:#fff;
            padding:10px 12px;
            border-radius:12px;
            max-width:220px;
            font-size:13px;
            line-height:1.25;
            opacity:0;
            transform:translateX(-8px);
            pointer-events:none;
            transition:opacity .25s ease, transform .25s ease;
            word-break: break-word;
        }
        .whatsapp-fab .tip.show{
            opacity:1;
            transform:translateX(0);
        }
        @media (max-width: 768px){
            .whatsapp-fab{
                top:var(--fab-top-mobile);
            }
            .whatsapp-fab .whatsapp-toggle{
                width:32px;
                height:32px;
            }
            .whatsapp-fab .whatsapp-toggle i{
                font-size:12px;
            }
            @keyframes whatsappPulse {
                0%, 100% {
                    transform: scale(1);
                    box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 0 rgba(37, 211, 102, 0.7);
                }
                50% {
                    transform: scale(1.05);
                    box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 6px rgba(37, 211, 102, 0);
                }
            }
            .whatsapp-fab .fab-btn{
                width:50px;
                height:50px;
            }
            .whatsapp-fab .fab-btn i{
                font-size:26px;
            }
            .whatsapp-fab .tip{
                max-width: min(180px, calc(100vw - 120px));
            }
        }

        /* Floating Google Login Button - Right Side (espejo del WhatsApp) */
        .google-fab{
            position:fixed;
            right:-2px;
            top:var(--fab-top);
            z-index:10004;
            display:flex;
            align-items:flex-start;
            flex-direction:row-reverse;
            transition:right .3s ease;
        }
        .google-fab .google-toggle{
            width:36px;
            height:36px;
            background:#ffffff;
            border-radius:18px 0 0 18px;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            box-shadow:0 4px 12px rgba(0,0,0,.25);
            transition:all .3s ease;
            z-index:9986;
            opacity:0.95;
            animation: googlePulse 3s ease-in-out infinite;
        }
        .google-fab .google-toggle:hover{
            opacity:1;
            box-shadow:0 6px 16px rgba(0,0,0,.35);
            animation-play-state: paused;
        }
        @keyframes googlePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 0 rgba(255, 255, 255, 0.6);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 8px rgba(255, 255, 255, 0);
            }
        }
        .google-fab .google-fab-glyph{
            width:18px;
            height:18px;
            flex-shrink:0;
        }
        .google-fab.expanded .google-toggle{
            animation: none;
            display: none;
        }
        .google-fab .google-content{
            display:flex;
            align-items:center;
            flex-direction:row-reverse;
            gap:8px;
            margin-right:-200px;
            opacity:0;
            pointer-events:none;
            transition:all .3s ease;
        }
        .google-fab.expanded .google-content{
            margin-right:0;
            opacity:1;
            pointer-events:auto;
        }
        .google-fab .google-fab-circle{
            width:56px;
            height:56px;
            border-radius:50%;
            background:linear-gradient(180deg, #fff, #f8fbff);
            color:#0f172a;
            text-decoration:none;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px solid rgba(0, 180, 216, 0.28);
            box-shadow:0 12px 28px rgba(0,0,0,.35), 0 8px 16px rgba(0,0,0,.2), 0 0 0 1px rgba(0,0,0,.06) inset;
            transition:transform .15s ease, box-shadow .2s ease;
            flex-shrink:0;
        }
        .google-fab .google-fab-circle:hover{
            transform:translateY(-2px);
            box-shadow:0 14px 28px rgba(0,0,0,.3);
        }
        .google-fab .google-fab-glyph-lg{
            width:28px;
            height:28px;
            flex-shrink:0;
        }
        .google-fab .tip-wrap{
            display:flex;
            flex-direction:column;
            align-items:flex-end;
        }
        .google-fab .tip{
            background:rgba(0,0,0,.85);
            color:#fff;
            padding:10px 12px;
            border-radius:12px;
            max-width:220px;
            font-size:13px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height:1.4;
            opacity:0;
            transform:translateX(8px);
            pointer-events:none;
            transition:opacity .25s ease, transform .25s ease;
            word-break: normal;
            overflow-wrap: break-word;
            text-align:left;
            direction:ltr;
        }
        .google-fab .tip.show{
            opacity:1;
            transform:translateX(0);
        }
        @media (max-width: 768px){
            .google-fab{
                top:var(--fab-top-mobile);
            }
            .google-fab .google-toggle{
                width:32px;
                height:32px;
                border-radius:16px 0 0 16px;
            }
            .google-fab .google-fab-glyph{
                width:16px;
                height:16px;
            }
            @keyframes googlePulse {
                0%, 100% {
                    transform: scale(1);
                    box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 0 rgba(255, 255, 255, 0.6);
                }
                50% {
                    transform: scale(1.05);
                    box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 6px rgba(255, 255, 255, 0);
                }
            }
            .google-fab .google-fab-circle{
                width:50px;
                height:50px;
            }
            .google-fab .google-fab-glyph-lg{
                width:24px;
                height:24px;
            }
            .google-fab .tip{
                max-width: min(180px, calc(100vw - 120px));
            }
        }

        /* Floating User Button - Right Side (misma altura que WhatsApp, solo ícono, toggle siempre visible) */
        .user-fab{
            position:fixed;
            right:-2px;
            top:var(--fab-top);
            z-index:9985;
            display:flex;
            align-items:flex-start;
            flex-direction:row-reverse;
            gap:8px;
        }
        .user-fab .user-fab-backdrop{
            display:none;
            position:fixed;
            inset:0;
            z-index:9984;
            background:rgba(0,0,0,.2);
            cursor:pointer;
        }
        .user-fab.expanded .user-fab-backdrop{
            display:block;
        }
        .user-fab .user-fab-toggle{
            width:36px;
            height:36px;
            background:var(--beach-primary, #00b4d8);
            border-radius:18px 0 0 18px;
            display:flex !important;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            box-shadow:0 4px 12px rgba(0,0,0,.25), 0 0 0 4px rgba(255, 215, 64, .18), 0 0 16px rgba(255, 215, 64, .45);
            transition:all .3s ease;
            position:relative;
            z-index:9987;
            flex-shrink:0;
            opacity:0.95;
            animation: userFabPulse 3s ease-in-out infinite;
        }
        .user-fab .user-fab-toggle i{
            color:#ffffff;
            font-size:16px;
        }
        .user-fab .user-fab-toggle:hover{
            opacity:1;
            box-shadow:0 6px 16px rgba(0,0,0,.35);
            animation-play-state: paused;
        }
        @keyframes userFabPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 0 rgba(255,255,255,.6); }
            50% { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,.25), 0 0 0 8px rgba(255,255,255,0); }
        }
        .user-fab.expanded .user-fab-toggle{ animation: none; }
        .user-fab .user-fab-content{
            display:flex;
            align-items:center;
            flex-direction:row-reverse;
            margin-right:-220px;
            opacity:0;
            pointer-events:none;
            transition:all .3s ease;
            position:relative;
            z-index:9986;
        }
        .user-fab.expanded .user-fab-content{
            margin-right:0;
            opacity:1;
            pointer-events:auto;
        }
        .user-fab .user-fab-links{
            display:flex;
            flex-direction:column;
            gap:6px;
            background:#fff;
            padding:12px 16px;
            border-radius:14px;
            box-shadow:0 12px 28px rgba(0,0,0,.25);
            border:1px solid rgba(0,0,0,.08);
            min-width:180px;
        }
        .user-fab .user-fab-link{
            display:flex;
            align-items:center;
            padding:8px 10px;
            color:#0f172a;
            text-decoration:none;
            font-weight:600;
            font-size:.9rem;
            border-radius:8px;
            transition:background .15s ease;
        }
        .user-fab .user-fab-link:hover{
            background:rgba(0,180,216,.12);
            color:var(--beach-primary, #00b4d8);
        }
        @media (max-width: 768px){
            .user-fab{ top:var(--fab-top-mobile); }
            .user-fab .user-fab-toggle{ width:32px; height:32px; border-radius:16px 0 0 16px; }
            .user-fab .user-fab-toggle i{ font-size:14px; }
        }

        /* FABs suben cuando el carrito mobile está abierto */
        body.mobile-cart-sheet-open .whatsapp-fab,
        body.mobile-cart-sheet-open .user-fab,
        body.mobile-cart-sheet-open .google-fab {
            top: 14px;
            transition: top 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            z-index: 1061;
        }

        /* Footer GL Logo 3D/shadow */
        .gl-logo{filter: drop-shadow(0 10px 18px rgba(0,0,0,.45)) drop-shadow(0 2px 4px rgba(0,0,0,.25)); transform: rotateX(0deg) rotateY(0deg); transition: transform .3s ease, filter .3s ease}
        .gl-logo-wrap:hover .gl-logo{transform: rotateX(6deg) rotateY(-6deg) translateZ(0); filter: drop-shadow(0 16px 28px rgba(0,0,0,.55)) drop-shadow(0 4px 8px rgba(0,0,0,.35))}
        /* Preloader Styles */
        #preloader {
            position: fixed;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9990;
            background: radial-gradient(circle at top, rgba(14, 165, 233, 0.35), rgba(7, 26, 47, 0.92));
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: opacity 0.6s ease-out;
        }

        #preloader.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .preloader-content{
            position: relative;
            width: 230px;
            height: 230px;
            border-radius: 28px;
            padding: 28px 18px 32px;
            background: rgba(8, 24, 48, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 35px 65px rgba(0,0,0,0.45);
            overflow: hidden;
            animation: preloaderFloat 3.2s ease-in-out infinite;
        }

        .preloader-backdrop{
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.25), transparent 45%),
                        radial-gradient(circle at 70% 10%, rgba(79,209,255,0.35), transparent 40%),
                        rgba(255,255,255,0.04);
            animation: backdropPulse 4s ease-in-out infinite alternate;
            z-index: 0;
        }

        .preloader-logo-frame{
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2;
        }

        .preloader-logo{
            width: 80%;
            height: 80%;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(14,165,233,0.35));
        }

        .logo-shine{
            position: absolute;
            inset: 0;
            border-radius: 34%;
            background: linear-gradient(120deg, transparent 0%, rgba(14,165,233,0.2) 40%, transparent 75%);
            transform: translateX(-100%);
            animation: logoShine 2.8s linear infinite;
            pointer-events: none;
        }

        .preloader-orbit{
            position: absolute;
            inset: 14px;
            border-radius: 50%;
            border: 1px dashed rgba(255,255,255,0.15);
            animation: orbitSpin 6s linear infinite;
            z-index: 1;
        }

        .orbit-dot{
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            box-shadow: 0 0 12px rgba(14,165,233,0.75);
        }
        .dot-1{ top: -5px; left: 50%; transform: translateX(-50%); }
        .dot-2{ bottom: 15%; right: -5px; }
        .dot-3{ top: 25%; left: -5px; }

        @keyframes orbitSpin{
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes preloaderFloat{
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes backdropPulse{
            0% { opacity: 0.65; }
            100% { opacity: 0.9; }
        }

        @keyframes logoShine{
            0% { transform: translateX(-120%); }
            60%,100% { transform: translateX(120%); }
        }

        /* Estilos para la campanita de notificaciones */
        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background-color 0.15s;
        }
        .notification-item:hover {
            background-color: #f9fafb;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item.unread {
            background-color: #ecfdf5;
            border-left: 3px solid #10b981;
        }
        .notification-item.unread:hover {
            background-color: #d1fae5;
        }
        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .notification-icon.info { background: #dbeafe; color: #1d4ed8; }
        .notification-icon.success { background: #d1fae5; color: #059669; }
        .notification-icon.warning { background: #fef3c7; color: #d97706; }
        .notification-icon.loyalty { background: #fef3c7; color: #f59e0b; }
        .notification-time {
            font-size: 11px;
            color: #9ca3af;
        }
        #notificationBadge {
            animation: notificationPulse 2s infinite;
        }
        @keyframes notificationPulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.1); }
        }
        #notificationBadge.d-none {
            animation: none;
        }
        @media (max-width: 767px) {
            #notificationDropdown {
                z-index: 11000 !important;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
                will-change: transform;
            }
        }
        /* ── BL STUDIO FOOTER BOTTOM ──────────────────────────────── */
        .footer-bottom {
            display: flex; align-items: center; justify-content: space-between;
            padding: 22px 60px; border-top: 1px solid rgba(214,255,60,0.18);
            background: #0d0d0d; flex-wrap: wrap; gap: 16px;
            position: relative; overflow: hidden;
        }
        .footer-bottom::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle 300px at var(--glow-x,-300px) var(--glow-y,50%), rgba(214,255,60,0.10), transparent 70%);
            pointer-events: none;
        }
        .footer-bl-phrase {
            font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 300;
            font-style: italic; color: rgba(255,255,255,0.7); letter-spacing: 0.01em;
            position: relative; margin: 0;
        }
        .footer-bl-phrase strong { font-style: normal; font-weight: 700; color: #d6ff3c; }
        .footer-bl-btn {
            font-family: 'JetBrains Mono', monospace; font-size: 7px;
            letter-spacing: 0.08em; text-transform: none;
            color: #0d0d0d; background: #d6ff3c; border: 1px solid transparent;
            padding: 5px 10px; cursor: pointer;
            transition: background 0.2s, transform 0.15s; position: relative;
        }
        .footer-bl-btn:hover { background: transparent; border: 1px solid #d6ff3c; color: #d6ff3c; transform: translateY(-1px); }
        @media (max-width: 900px) {
            .footer-bottom { padding: 20px 24px; flex-direction: column; text-align: center; gap: 12px; }
        }
        /* ── PROPUESTA MODAL VARIABLES ────────────────────────────── */
        #prop-overlay {
            --gold: #8a9155; --wood: #6e763e;
            --iron: #dad6cd; --iron2: #bfbcb4; --iron3: #8a887f;
            --off: #1e2112; --muted: #5a5e48; --black: #e6e2d9;
        }
        #feedback-overlay {
            --gold: #8a9155; --iron: #dad6cd; --iron2: #bfbcb4;
            --off: #1e2112; --muted: #5a5e48; --black: #e6e2d9;
        }
        /* ── PROP OVERLAY ─────────────────────────────────────────── */
        .prop-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.88);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            z-index: 9999; display: flex; align-items: center; justify-content: center;
            padding: 24px; opacity: 0; visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .prop-overlay.open { opacity: 1; visibility: visible; }
        .prop-box {
            background: var(--iron, #dad6cd); border: 1px solid var(--iron2, #bfbcb4);
            width: 100%; max-width: 1080px; max-height: 92vh;
            overflow-y: auto; padding: 52px 48px 48px;
            position: relative; box-sizing: border-box; display: flex; flex-direction: column;
        }
        .prop-close {
            position: absolute; top: 20px; right: 24px;
            background: none; border: none; color: var(--muted, #5a5e48);
            font-family: 'JetBrains Mono', monospace; font-size: 11px;
            letter-spacing: 0.16em; text-transform: uppercase; cursor: pointer; transition: color 0.2s;
        }
        .prop-close:hover { color: var(--gold, #8a9155); }
        .prop-step { display: none; width: 100%; }
        .prop-step.active { display: block; }
        .prop-plans { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-top: 36px; }
        @media (max-width: 1100px) { .prop-plans { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 600px)  { .prop-plans { grid-template-columns: 1fr; } }
        .prop-plan {
            border: 1px solid var(--iron2, #bfbcb4); padding: 28px 22px;
            cursor: pointer; transition: border-color 0.2s, background 0.2s; position: relative;
        }
        .prop-plan:hover { border-color: rgba(138,145,85,0.5); }
        .prop-plan.selected { border-color: var(--gold, #8a9155); background: rgba(138,145,85,0.05); }
        .prop-plan.selected::after {
            content: '✓'; position: absolute; top: 16px; right: 20px;
            color: var(--gold, #8a9155); font-family: 'JetBrains Mono', monospace; font-size: 13px;
        }
        .prop-badge {
            display: inline-block; font-family: 'JetBrains Mono', monospace;
            font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
            color: var(--black, #e6e2d9); background: var(--gold, #8a9155);
            padding: 3px 8px; margin-bottom: 16px;
        }
        .prop-price { font-family: 'Bebas Neue', sans-serif; font-size: 56px; color: var(--gold, #8a9155); line-height: 1; }
        .prop-price-old { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #666; text-decoration: line-through; margin-bottom: 4px; }
        .prop-period { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase; color: #aaa; margin-bottom: 20px; }
        .prop-plan-name { font-family: 'Bebas Neue', sans-serif; font-size: 22px; letter-spacing: 0.04em; color: #f0ece7; margin-bottom: 16px; }
        .prop-features { list-style: none; display: flex; flex-direction: column; gap: 7px; padding: 0; margin: 0; }
        .prop-features li { font-size: 13px; font-weight: 300; color: #c8c4be; line-height: 1.5; display: flex; gap: 8px; }
        .prop-features li::before { content: '✓'; color: var(--gold, #8a9155); font-family: 'JetBrains Mono', monospace; flex-shrink: 0; }
        .prop-nav { display: flex; gap: 12px; margin-top: 24px; }
        .prop-bank { background: var(--black, #e6e2d9); border: 1px solid var(--iron2, #bfbcb4); padding: 0; margin-top: 28px; width: 100%; box-sizing: border-box; }
        .prop-bank-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; border-bottom: 1px solid var(--iron2, #bfbcb4); }
        .prop-bank-row:last-child { border-bottom: none; }
        .prop-bank-label { font-family: 'JetBrains Mono', monospace; font-size: 8px; letter-spacing: 0.12em; text-transform: uppercase; color: #888; flex-shrink: 0; min-width: 120px; }
        .prop-bank-value { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--off, #1e2112); cursor: pointer; transition: color 0.2s; text-align: right; flex: 1; padding-left: 12px; }
        .prop-bank-value:hover { color: var(--gold, #8a9155); }
        #prop-overlay .prop-box {
            padding: 0; overflow: hidden; background: #111111;
            --off: #f0ece6; --muted: #8a8278; --iron: #1c1c1c; --iron2: #2a2a2a; --iron3: #4a4a4a; --black: #111111;
        }
        .prop-box-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 52px 48px 24px; position: relative; }
        .prop-float-bar {
            flex-shrink: 0; width: 100%; z-index: 10;
            background: #111111; border-top: 1px solid #2a2a2a;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            padding: 16px 48px; transform: translateY(100%); opacity: 0;
            transition: transform 0.3s cubic-bezier(0.25,0.8,0.25,1), opacity 0.3s ease;
            pointer-events: none;
        }
        .prop-float-bar.visible { transform: translateY(0); opacity: 1; pointer-events: all; }
        .prop-float-bar-label { display: flex; flex-direction: column; gap: 2px; }
        .prop-float-bar-plan { font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase; color: #888; }
        .prop-float-bar-price { font-family: 'Bebas Neue', sans-serif; font-size: 28px; color: var(--gold, #8a9155); line-height: 1; }
        .prop-float-bar-btn {
            display: flex; align-items: center; gap: 10px;
            background: var(--gold, #8a9155); color: #ffffff;
            font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase;
            border: none; padding: 16px 32px; cursor: pointer; transition: background 0.2s, transform 0.15s; white-space: nowrap;
        }
        .prop-float-bar-btn:hover { background: #6e763e; transform: translateY(-2px); }
        #prop-overlay .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 600px) { #prop-overlay .form-row { grid-template-columns: 1fr; } }
        #prop-overlay .form-field { display: flex; flex-direction: column; gap: 8px; }
        #prop-overlay .form-field label { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: #8a8278; }
        #prop-overlay .form-field input, #prop-overlay .form-field textarea {
            background: #1c1c1c; border: 1px solid #2a2a2a; color: #f0ece6;
            font-family: 'DM Sans', sans-serif; font-size: 14px; padding: 14px 16px;
            outline: none; transition: border-color 0.2s; border-radius: 0;
        }
        #prop-overlay .form-field textarea { resize: vertical; min-height: 80px; }
        #prop-overlay .form-field input:focus, #prop-overlay .form-field textarea:focus { border-color: #8a9155; }
        #prop-overlay .form-field input::placeholder, #prop-overlay .form-field textarea::placeholder { color: #4a4a4a; }
        #prop-overlay .form-submit, #feedback-overlay .form-submit {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: #8a9155; color: #ffffff;
            font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase;
            border: none; padding: 18px 32px; cursor: pointer;
            transition: background 0.2s, transform 0.15s; width: 100%;
        }
        #prop-overlay .form-submit:hover, #feedback-overlay .form-submit:hover { background: #6e763e; transform: translateY(-2px); }
        #prop-overlay .btn-outline {
            display: flex; align-items: center; justify-content: center;
            background: transparent; color: #f0ece6;
            font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.14em; text-transform: uppercase;
            padding: 15px 28px; border: 1px solid rgba(240,236,230,0.3);
            transition: border-color 0.2s, color 0.2s, transform 0.15s; cursor: pointer;
        }
        #prop-overlay .btn-outline:hover { border-color: #8a9155; color: #8a9155; transform: translateY(-2px); }
        #prop-overlay .section-tag, #feedback-overlay .section-tag {
            font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.24em; text-transform: uppercase;
            color: #8a9155; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;
        }
        #prop-overlay .section-tag::before, #feedback-overlay .section-tag::before {
            content: ''; display: block; width: 24px; height: 1px; background: #8a9155;
        }
        #prop-overlay .section-title, #feedback-overlay .section-title {
            font-family: 'Bebas Neue', sans-serif; font-size: clamp(32px,4vw,56px);
            line-height: 0.95; letter-spacing: 0.02em; color: #f0ece6; margin-bottom: 12px;
        }
        /* loader */
        .loader-overlay {
            position: fixed; inset: 0; background: rgba(13,13,13,0.92);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            z-index: 10000; display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden; transition: opacity 0.25s, visibility 0.25s;
        }
        .loader-overlay.open { opacity: 1; visibility: visible; }
        .loader-box {
            background: #dad6cd; border: 1px solid #bfbcb4;
            padding: 44px 48px; min-width: 320px; max-width: 92vw;
            text-align: center; position: relative; overflow: hidden;
        }
        .loader-box::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, #8a9155, transparent);
            animation: blLoaderBar 1.6s ease-in-out infinite;
        }
        @keyframes blLoaderBar { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        .loader-tag {
            font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em;
            color: #8a9155; text-transform: uppercase; margin-bottom: 22px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .loader-tag::before, .loader-tag::after { content: ''; width: 22px; height: 1px; background: #8a9155; opacity: 0.6; }
        .loader-icon { width: 48px; height: 48px; margin: 0 auto 20px; color: #8a9155; animation: blLoaderPulse 1.6s ease-in-out infinite; }
        .loader-icon.success { color: #6dcc6d; animation: blLoaderSuccess 0.5s ease-out; }
        @keyframes blLoaderPulse { 0%,100%{opacity:.5;transform:scale(1);}50%{opacity:1;transform:scale(1.08);} }
        @keyframes blLoaderSuccess { 0%{transform:scale(.5);opacity:0;}60%{transform:scale(1.15);opacity:1;}100%{transform:scale(1);opacity:1;} }
        .loader-msg { font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 400; color: #1e2112; line-height: 1.5; }
        .loader-sub { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.18em; color: #888; text-transform: uppercase; margin-top: 10px; }
        .loader-dots::after { display: inline-block; animation: blLoaderDots 1.4s steps(4,end) infinite; content: ''; width: 1em; text-align: left; }
        @keyframes blLoaderDots { 0%{content:'';}25%{content:'.';}50%{content:'..';}75%{content:'...';}100%{content:'';} }
        /* objeciones */
        .prop-objeciones { margin-top: 28px; padding-top: 24px; border-top: 1px solid #2a2a2a; }
        .prop-obj-title {
            font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase;
            color: #999; margin-bottom: 0; background: none; border: none; cursor: pointer;
            display: flex; align-items: center; gap: 8px; padding: 0; transition: color 0.2s; width: 100%;
        }
        .prop-obj-title:hover { color: #8a9155; }
        .prop-obj-arrow { font-size: 14px; line-height: 1; transition: transform 0.25s ease; display: inline-block; }
        .prop-obj-title[aria-expanded="true"] .prop-obj-arrow { transform: rotate(90deg); }
        .prop-obj-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 0; }
        .prop-obj-chip {
            font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase;
            color: #8a8278; background: none; border: 1px solid #2a2a2a;
            padding: 7px 14px; cursor: pointer; transition: border-color 0.2s, color 0.2s;
        }
        .prop-obj-chip:hover, .prop-obj-chip.active { border-color: #8a9155; color: #8a9155; }
        .prop-obj-resp { display: none; margin-top: 16px; padding: 16px 20px; background: rgba(138,145,85,0.06); border-left: 2px solid #8a9155; font-size: 13px; font-weight: 300; color: #f0ece6; line-height: 1.75; }
        .prop-obj-resp.show { display: block; }
        /* feedback */
        .feedback-bl-header { background: #0d0d0d; margin: -52px -48px 32px; padding: 18px 48px; display: flex; align-items: center; }
        .feedback-bl-logo { font-family: 'DM Sans', sans-serif; font-size: 26px; font-weight: 300; letter-spacing: -0.01em; color: rgba(255,255,255,0.82); }
        .feedback-bl-logo-dot { color: #d6ff3c; }
        .modal-stars-canvas { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
        #feedback-overlay .prop-box { background: #111111; --off: #f0ece6; --muted: #8a8278; --iron: #1c1c1c; --iron2: #2a2a2a; --iron3: #4a4a4a; --black: #111111; }
        #feedback-overlay .form-field { display: flex; flex-direction: column; gap: 8px; }
        #feedback-overlay .form-field label { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: #8a8278; }
        #feedback-overlay .form-field textarea {
            background: #1c1c1c; border: 1px solid #2a2a2a; color: #f0ece6;
            font-family: 'DM Sans', sans-serif; font-size: 14px; padding: 14px 16px;
            outline: none; transition: border-color 0.2s; border-radius: 0; resize: vertical; min-height: 100px;
        }
        #feedback-overlay .form-field textarea:focus { border-color: #8a9155; }
        @media (max-width: 900px) {
            .feedback-bl-header { margin: -32px -20px 20px !important; padding: 14px 20px; }
            .prop-box-scroll { padding: 32px 20px 16px; }
            .prop-float-bar { padding: 14px 20px; }
            #prop-overlay .prop-box, #feedback-overlay .prop-box { max-height: 85svh; max-height: 85vh; border-radius: 2px; }
        }
    </style>

    @auth
    <script>
        // Sistema de notificaciones
        (function() {
            const notificationBadge = document.getElementById('notificationBadge');
            const notificationList = document.getElementById('notificationList');
            const markAllReadBtn = document.getElementById('markAllRead');

            // Cargar notificaciones
            async function loadNotifications() {
                try {
                    const response = await fetch('/api/notifications');
                    const data = await response.json();

                    if (data.success) {
                        updateBadge(data.unread_count);
                        renderNotifications(data.notifications);
                    }
                } catch (e) {
                    console.error('Error cargando notificaciones:', e);
                }
            }

            // Actualizar badge
            function updateBadge(count) {
                if (count > 0) {
                    notificationBadge.textContent = count > 99 ? '99+' : count;
                    notificationBadge.classList.remove('d-none');
                } else {
                    notificationBadge.classList.add('d-none');
                }
            }

            // Renderizar notificaciones
            function renderNotifications(notifications) {
                if (!notifications || notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-bell-slash mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                            <p class="mb-0 small">No hay notificaciones</p>
                        </div>
                    `;
                    return;
                }

                notificationList.innerHTML = notifications.map(n => `
                    <div class="notification-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" data-url="${n.action_url || ''}">
                        <div class="d-flex gap-3">
                            <div class="notification-icon ${n.type}">
                                ${getIconForType(n.type)}
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                                <p class="mb-1 fw-semibold small" style="color: #111827;">${escapeHtml(n.title)}</p>
                                ${n.message ? `<p class="mb-1 small text-muted" style="line-height: 1.4;">${escapeHtml(n.message)}</p>` : ''}
                                <p class="notification-time mb-0">${formatTime(n.created_at)}</p>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Agregar event listeners
                notificationList.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const url = this.dataset.url;

                        // Marcar como leída
                        markAsRead(id);

                        // Redirigir si hay URL
                        if (url) {
                            window.location.href = url;
                        }
                    });
                });
            }

            // Obtener icono según tipo
            function getIconForType(type) {
                const icons = {
                    info: '<i class="fas fa-info"></i>',
                    success: '<i class="fas fa-check"></i>',
                    warning: '<i class="fas fa-exclamation"></i>',
                    loyalty: '<i class="fas fa-sun"></i>'
                };
                return icons[type] || icons.info;
            }

            // Escapar HTML
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Formatear tiempo
            function formatTime(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000);

                if (diff < 60) return 'Hace un momento';
                if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
                if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
                if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} d`;
                return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
            }

            // Marcar como leída
            async function markAsRead(id) {
                try {
                    await fetch(`/api/notifications/${id}/read`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });
                    loadNotifications();
                } catch (e) {
                    console.error('Error marcando notificación:', e);
                }
            }

            // Marcar todas como leídas
            async function markAllAsRead() {
                try {
                    await fetch('/api/notifications/read-all', {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });
                    loadNotifications();
                } catch (e) {
                    console.error('Error marcando notificaciones:', e);
                }
            }

            // Event listeners
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    markAllAsRead();
                });
            }

            // Manual dropdown toggle for iOS compatibility
            const notificationToggle = document.getElementById('notificationToggle');
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationToggle && notificationDropdown) {
                notificationToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isOpen = notificationDropdown.style.display === 'block';
                    notificationDropdown.style.display = isOpen ? 'none' : 'block';
                    notificationToggle.setAttribute('aria-expanded', !isOpen);
                });

                // Close when clicking outside
                document.addEventListener('click', function(e) {
                    if (!notificationToggle.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.style.display = 'none';
                        notificationToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Cargar al iniciar
            loadNotifications();

            // Actualizar cada 30 segundos
            setInterval(loadNotifications, 30000);
        })();
    </script>
    @endauth


    <script>
    // -- BL STUDIO FOOTER CANVAS + GLOW
    (function() {
        var canvas = document.getElementById('bl-stars-canvas');
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        var W, H, stars = [], mouse = { x:-999, y:-999 };
        var COUNT = 55, COLOR = '#d6ff3c';
        function resize() { var r = canvas.parentElement.getBoundingClientRect(); W = canvas.width = r.width; H = canvas.height = r.height; }
        function mkStar() { return { x:Math.random()*(W||400), y:Math.random()*(H||80)-(H||80), r:Math.random()*1.2+0.3, vy:Math.random()*0.6+0.25, vx:(Math.random()-0.5)*0.2, o:Math.random()*0.5+0.3, pulse:Math.random()*Math.PI*2 }; }
        function init() { resize(); stars = Array.from({length:COUNT}, mkStar); }
        function draw() {
            ctx.clearRect(0,0,W,H);
            stars.forEach(function(s) {
                s.pulse+=0.04;
                var dist = Math.hypot(s.x-mouse.x, s.y-mouse.y);
                var pull = Math.max(0,1-dist/80);
                if(dist<80&&dist>0){s.x+=(s.x-mouse.x)/dist*pull*1.5;s.y+=(s.y-mouse.y)/dist*pull*1.5;}
                s.x+=s.vx; s.y+=s.vy;
                if(s.y>H+4){Object.assign(s,mkStar(),{y:-4,x:Math.random()*W});}
                ctx.beginPath(); ctx.arc(s.x,s.y,s.r+pull*1.5,0,Math.PI*2);
                ctx.fillStyle=COLOR; ctx.globalAlpha=s.o*(0.7+0.3*Math.sin(s.pulse))+pull*0.4; ctx.fill();
            });
            ctx.globalAlpha=1; requestAnimationFrame(draw);
        }
        canvas.parentElement.addEventListener('mousemove', function(e) { var r=canvas.getBoundingClientRect(); mouse.x=e.clientX-r.left; mouse.y=e.clientY-r.top; });
        canvas.parentElement.addEventListener('mouseleave', function(){mouse.x=-999;mouse.y=-999;});
        window.addEventListener('resize', resize);
        init(); draw();

        var footerBl = document.getElementById('footer-bl');
        if (footerBl) {
            footerBl.addEventListener('mousemove', function(e) { var r=footerBl.getBoundingClientRect(); footerBl.style.setProperty('--glow-x',(e.clientX-r.left)+'px'); footerBl.style.setProperty('--glow-y',(e.clientY-r.top)+'px'); });
            footerBl.addEventListener('mouseleave', function(){footerBl.style.setProperty('--glow-x','-300px');footerBl.style.setProperty('--glow-y','50%');});
        }
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

