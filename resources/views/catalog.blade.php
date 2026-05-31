@extends('layouts.app')

@section('title', 'TCocina - Delivery de Hamburguesas Smash Artesanales en Olavarría')

@php
    // Cargar configuraciones una sola vez para reutilizar en los modales
    $medallonesConfigs = \App\Models\ProductConfiguration::where('name', 'Medallones')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $tipoConfigs = \App\Models\ProductConfiguration::where('name', 'Tipo de Medallón')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $dipConfigs = \App\Models\ProductConfiguration::where('name', 'Dip')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $aderezosConfigs = \App\Models\ProductConfiguration::where('name', 'Aderezos')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $extrasConfigs = \App\Models\ProductConfiguration::where('name', 'Extras')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $dipConfigs = \App\Models\ProductConfiguration::where('name', 'Dip')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $dipExtraConfigs = \App\Models\ProductConfiguration::where('name', 'Dip Extra')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();
@endphp

@section('content')
    {{-- Banner: local cerrado con pedido activo --}}
    @auth
    @if(isset($activeOrder) && $activeOrder && !empty($siteOffline) && $siteOffline && $activeOrder->status !== 'delivered')
    @php
        $catalogStatusMsgs = [
            'pending'    => 'tu pedido fue recibido y está siendo procesado',
            'confirmed'  => 'tu pedido fue confirmado y pronto empieza la preparación',
            'preparing'  => 'tu pedido está siendo preparado en este momento',
            'ready'      => 'tu pedido ya está listo',
            'on_the_way' => 'tu pedido está en camino hacia vos',
        ];
        $catalogStatusMsg = $catalogStatusMsgs[$activeOrder->status] ?? 'tu pedido está en curso';
    @endphp
    <div id="catalog-offline-banner" style="background:linear-gradient(90deg,#1a1a2e 0%,#16213e 100%);border-bottom:2px solid rgba(255,200,0,.35);color:#f0e6c8;padding:12px 16px;text-align:center;font-size:.92rem;line-height:1.5;">
        <span style="font-size:1rem;">🔒</span>
        <strong style="letter-spacing:.06em;">El local ya cerró</strong>,
        pero no te preocupes —
        <strong id="catalog-offline-status-text" style="color:#ffd700;">{{ $catalogStatusMsg }}</strong>.
        ¡Tu pedido llega igual! 🔥
    </div>
    <script>
        (function () {
            var globalBanner = document.getElementById('siteOfflineBanner');
            var globalSpacer = document.getElementById('siteOfflineSpacer');
            if (globalBanner) globalBanner.style.display = 'none';
            if (globalSpacer) globalSpacer.style.height  = '0';
        })();
    </script>
    @endif
    @endauth

    <!-- Main Content -->
    <main class="container py-4">

        {{-- ── MINI TRACKER DE PEDIDO ACTIVO ── --}}
        @auth
        @if(isset($activeOrder) && $activeOrder)
        @php
            $trackerIsDelivery = $activeOrder->address_id !== null;
            if ($trackerIsDelivery) {
                $trackerSteps = [
                    'pending'    => ['label' => 'Enviado',          'icon' => 'fa-paper-plane'],
                    'confirmed'  => ['label' => 'Confirmado',       'icon' => 'fa-check-circle'],
                    'preparing'  => ['label' => 'Preparando',       'icon' => 'fa-fire'],
                    'ready'      => ['label' => 'Para enviar',      'icon' => 'fa-star'],
                    'on_the_way' => ['label' => 'En camino',        'icon' => 'fa-motorcycle'],
                    'delivered'  => ['label' => 'Entregado',        'icon' => 'fa-house'],
                ];
                $trackerOrder = ['pending', 'confirmed', 'preparing', 'ready', 'on_the_way', 'delivered'];
            } else {
                $trackerSteps = [
                    'pending'   => ['label' => 'Enviado',           'icon' => 'fa-paper-plane'],
                    'confirmed' => ['label' => 'Confirmado',        'icon' => 'fa-check-circle'],
                    'preparing' => ['label' => 'Preparando',        'icon' => 'fa-fire'],
                    'ready'     => ['label' => 'Para retirar',      'icon' => 'fa-store'],
                    'delivered' => ['label' => 'Retirado',          'icon' => 'fa-check-circle'],
                ];
                $trackerOrder = ['pending', 'confirmed', 'preparing', 'ready', 'delivered'];
            }
            $trackerCurrent = $activeOrder->status;
            $trackerIdx     = array_search($trackerCurrent, $trackerOrder);
            if ($trackerIdx === false) $trackerIdx = 0;
        @endphp
        <div class="order-tracker-bar" id="order-tracker-bar"
             data-order="{{ $activeOrder->order_number }}"
             data-status="{{ $trackerCurrent }}">

            {{-- Header --}}
            <div class="otb-header">
                <div class="otb-title">
                    <span class="otb-live-dot"></span>
                    <span>Tu pedido <strong>#{{ $activeOrder->order_number }}</strong></span>
                    <span class="otb-badge otb-badge-{{ $trackerCurrent }}" id="otb-badge">{{ $activeOrder->status_label }}</span>
                </div>
                <div class="otb-actions">
                    <a href="{{ route('orders.tracking', $activeOrder->order_number) }}" class="otb-detail-link">
                        Ver detalle <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                    <button class="otb-close" id="otb-close" aria-label="Cerrar">&times;</button>
                </div>
            </div>

            {{-- Progress steps --}}
            <div class="otb-steps" id="otb-steps">
                @foreach($trackerSteps as $key => $step)
                    @php
                        $sIdx    = array_search($key, $trackerOrder);
                        $isDone  = $sIdx < $trackerIdx;
                        $isActive= $sIdx === $trackerIdx;
                        $cls     = $isDone ? 'otb-done' : ($isActive ? 'otb-active' : 'otb-pending');
                    @endphp
                    <div class="otb-step {{ $cls }}" id="otb-step-{{ $key }}">
                        <div class="otb-step-icon">
                            @if($isDone)
                                <i class="fas fa-check"></i>
                            @else
                                <i class="fas {{ $step['icon'] }}"></i>
                            @endif
                        </div>
                        <span class="otb-step-label">{{ $step['label'] }}</span>
                    </div>
                    @if(!$loop->last)
                        <div class="otb-connector {{ $isDone ? 'otb-connector-done' : '' }}" id="otb-conn-{{ $key }}"></div>
                    @endif
                @endforeach
            </div>

        </div>
        @endif
        @endauth

        <!-- Page Header -->
        <div class="mb-2 text-center">
            <h1 class="text-display-lg text-beach-dark mb-2 font-display">Menú</h1>
            @auth
                @php
                    $nameParts = explode(' ', trim(auth()->user()->name));
                    $firstName = implode(' ', array_slice($nameParts, 0, min(2, count($nameParts))));
                    if (count($nameParts) > 2) {
                        $firstName = $nameParts[0] . ' ' . $nameParts[1];
                    }
                @endphp
                <div class="text-start">
                    <div id="loyaltyHeaderToggle" class="d-flex align-items-center justify-content-between" style="cursor:pointer;gap:8px;" role="button" aria-expanded="false" aria-controls="loyaltyCollapsible">
                        <strong class="text-beach-dark d-block font-display" style="font-size:1.45rem;letter-spacing:.04em;">Hola {{ ucwords(mb_strtolower($firstName)) }}</strong>
                        <span id="loyaltyHeaderChevron" class="text-beach-dark" style="font-size:1.4rem;line-height:1;transition:transform .25s ease;">›</span>
                    </div>
                    <div id="loyaltyCollapsible" style="display:none;overflow:hidden;">
                        <span class="small text-muted">cada hamburguesa que pidas suma 1 figurita en tu <a href="{{ route('loyalty.dashboard') }}" class="text-beach-dark fw-semibold">álbum</a>.</span>

                        @if($loyaltyWallet && $loyaltySetting)
                        @php
                            $pct = (float) $loyaltyProgress;
                            $hue = (int) round(($pct / 100) * 120);
                            $hueEnd = min(120, $hue + 16);
                            $colStart = "hsl({$hue} 90% 46%)";
                            $colEnd   = "hsl({$hueEnd} 92% 56%)";
                        @endphp
                        <div class="catalog-loyalty-wrap mt-1">
                            <div class="catalog-loyalty-chart">
                                <div class="catalog-loyalty-bar" style="--progress-value: {{ $pct }}%; --progress-start: {{ $colStart }}; --progress-end: {{ $colEnd }}; cursor:pointer;" onclick="window.location.href='{{ route('loyalty.dashboard') }}'">
                                    <div class="face top">
                                        <div class="growing-bar"></div>
                                    </div>
                                    <div class="face side-0">
                                        <div class="growing-bar"></div>
                                    </div>
                                    <div class="face floor">
                                        <div class="growing-bar"></div>
                                    </div>
                                    <div class="face side-a"></div>
                                    <div class="face side-b"></div>
                                    <div class="face side-1">
                                        <div class="growing-bar"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="catalog-progress-percent" style="right:auto;left:12px;">{{ (int) round($pct) }}%</div>
                            <div class="catalog-progress-percent" style="right:12px;left:auto;">{{ $loyaltyWallet->current_stickers }}/{{ $loyaltySetting->target_stickers }}</div>
                        </div>
                        @if($pct >= 100 || $pct >= 66 || $loyaltyWallet->current_stickers === 0)
                        <span class="text-muted" style="font-size:.7rem;">
                            @if($pct >= 100)
                                Canjeá tu premio 🎉
                            @elseif($pct >= 66)
                                Estás a {{ $loyaltySetting->target_stickers - $loyaltyWallet->current_stickers }} figuritas de completar tu álbum
                            @else
                                Empezá tu álbum hoy
                            @endif
                        </span>
                        @endif
                        @endif
                    </div>
                    <script>
                        (function(){
                            const t = document.getElementById('loyaltyHeaderToggle');
                            const c = document.getElementById('loyaltyCollapsible');
                            const ch = document.getElementById('loyaltyHeaderChevron');
                            if (!t || !c) return;
                            t.addEventListener('click', function(){
                                const open = c.style.display !== 'none';
                                c.style.display = open ? 'none' : 'block';
                                t.setAttribute('aria-expanded', String(!open));
                                if (ch) ch.style.transform = open ? '' : 'rotate(90deg)';
                            });
                        })();
                    </script>
                </div>
            @endauth
        </div>

        

        <!-- Category Filter -->
        <div class="mb-2">

            <!-- Mobile: App bar inferior -->
            <div class="mobile-filter-appbar d-md-none p-2" id="filterContainerMobile">
                @php
                    // Sólo se muestran 2 botones al cliente: Hamburguesas y Acompañamientos.
                    // El botón "Acompañamientos" agrupa también Bebidas, Postres y Combos
                    // mediante el data-category de cada card (ver más abajo).
                    $defaultCategory = 'hamburguesas';
                @endphp

                <div id="mobileCategoryFilters" class="mobile-category-filters-row">
                    <!-- Botón Hamburguesas -->
                    <button class="mobile-filter-btn category-filter active" data-category="hamburguesas">
                        <div class="filter-icon">
                            <img src="{{ asset('productos/fondo/burger.png') }}" alt="Hamburguesa" class="filter-burger-img">
                        </div>
                        <span class="filter-label">HAMBURGUESAS</span>
                    </button>

                    <!-- Botón Acompañamientos (incluye Bebidas, Postres y Combos) -->
                    <button class="mobile-filter-btn category-filter" data-category="acompanamientos">
                        <div class="filter-icon">
                            <img src="{{ asset('productos/fondo/fries.png') }}" alt="Acompañamientos" class="filter-fries-img">
                        </div>
                        <span class="filter-label">ACOMPAÑAMIENTOS</span>
                    </button>
                </div>

                <button type="button" id="mobileViewOrderCta"
                    class="btn btn-beach-primary w-100 justify-content-center align-items-center px-3 py-2 d-none mobile-view-order-cta position-relative"
                    aria-label="Ver mi pedido" aria-controls="mobileCartBottomSheet" aria-expanded="false">
                    <span id="mobileViewOrderCtaCountBadge" class="mobile-view-order-cta-count-badge d-none" aria-hidden="true"></span>
                    <span class="desktop-view-order-inner">
                        <lord-icon
                            src="{{ asset('lordicons/carritolordicon.json') }}"
                            colors="primary:#ffffff,secondary:#ffffff"
                            style="width:32px;height:32px"
                            aria-hidden="true">
                        </lord-icon>
                        <span class="desktop-view-order-label fs-6 fw-semibold text-uppercase">VER MI PEDIDO</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Toggle (Grid / Lista / Carrusel) - puntos indicadores -->
        <div class="d-flex justify-content-center align-items-center mb-1" id="viewToggleWrap">
            <div class="view-dots" role="group" aria-label="Cambiar vista" id="viewDots">
                <button id="gridBtn" class="view-dot active" data-view="grid" title="Vista en grid" aria-label="Vista en grid">
                    <span class="dot-circle"></span>
                    <i class="fas fa-border-all dot-icon"></i>
                </button>
                <button id="listBtn" class="view-dot" data-view="list" title="Vista en lista" aria-label="Vista en lista">
                    <span class="dot-circle"></span>
                    <i class="fas fa-list dot-icon"></i>
                </button>
                <button id="carouselBtn" class="view-dot" data-view="carousel" title="Vista en carrusel" aria-label="Vista en carrusel">
                    <span class="dot-circle"></span>
                    <i class="fas fa-images dot-icon"></i>
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        @php
            $categoryTitles = [
                2 => ['label' => 'Acompañamientos', 'icon' => 'fas fa-drumstick-bite', 'lordicon' => null],
                3 => ['label' => 'Bebidas', 'icon' => 'fas fa-glass-water', 'lordicon' => 'bebidas.json'],
                4 => ['label' => 'Combos', 'icon' => 'fas fa-layer-group', 'lordicon' => null],
                5 => ['label' => 'Postres', 'icon' => 'fas fa-ice-cream', 'lordicon' => 'postres.json'],
            ];
            $prevCategoryId = null;
        @endphp
        <div id="productsGrid" class="row g-4">
            @foreach ($products as $product)
                @php
                    // Para el catálogo del cliente, agrupar Bebidas/Postres/Combos
                    // bajo el filtro "Acompañamientos".
                    $clientFilterSlug = ($product->category->slug ?? '') === 'hamburguesas' ? 'hamburguesas' : 'acompanamientos';
                    $catId = $product->category_id;
                @endphp
                {{-- Separador de sección cuando cambia la categoría (solo para no-hamburguesas) --}}
                @if ($catId !== 1 && $catId !== $prevCategoryId && isset($categoryTitles[$catId]))
                    <div class="col-12 mt-3 mb-1 category-section-divider" data-category="acompanamientos">
                        <div class="d-flex align-items-center gap-2">
                            @if($categoryTitles[$catId]['lordicon'])
                                <lord-icon
                                    src="{{ asset('lordicons/' . $categoryTitles[$catId]['lordicon']) }}"
                                    colors="primary:#0a2540,secondary:#0a2540"
                                    trigger="hover"
                                    style="width:24px;height:24px">
                                </lord-icon>
                            @else
                                <i class="{{ $categoryTitles[$catId]['icon'] }} text-beach-primary" style="font-size:1.1rem;"></i>
                            @endif
                            <h5 class="mb-0 text-black font-display" style="font-size:1.1rem; letter-spacing:0.5px;">{{ $categoryTitles[$catId]['label'] }}</h5>
                            <hr class="flex-grow-1 my-0" style="border-color: #ddd;">
                        </div>
                    </div>
                @endif
                @php $prevCategoryId = $catId; @endphp
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card h-100 beach-card product-card"style="padding: 0px!important;"
                        data-category="{{ $clientFilterSlug }}" data-product-id="{{ $product->id }}">
                        <span class="cash-discount-badge"><i class="fas fa-money-bill-wave"></i> 10% OFF efectivo</span>
                        <div class="position-relative overflow-hidden product-image-container">
                            @if ($product->image)
                                @php
                                    $imgUrl = asset('images/' . $product->image);
                                @endphp
                                <img class="card-img-top product-image"
                                    style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                    src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy"
                                    data-lightbox="{{ $imgUrl }}" data-lightbox-title="{{ $product->name }}"
                                    onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                            @else
                                <img class="card-img-top product-image"
                                    style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                    src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    alt="{{ $product->name }}" loading="lazy"
                                    data-lightbox="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    data-lightbox-title="{{ $product->name }}" />
                            @endif
                            <div class="lightbox-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title product-name mb-1 text-black"
                                style="font-size: 1.5rem; font-weight: 600;">{{ $product->name }}</h3>

                            @if ($product->category_id == 1)
                                <!-- Aclaración para hamburguesas -->
                                <div class="mb-2 text-center">
                                    <small class="text-success fw-medium">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Incluye porción de papas y dip de salsa
                                    </small>
                                </div>
                            @elseif ($product->category_id == 2)
                                <!-- Aclaración para acompañamientos -->
                                <div class="mb-2 text-center">
                                    <small class="text-success fw-medium">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Incluye dip de salsa
                                    </small>
                                </div>
                            @endif

                            <!-- Price between title and description -->
                            <div class="mb-2 text-center">
                                <span
                                    class="h5 text-black product-price font-display">${{ number_format($product->base_price, 2, ',', '.') }}</span>
                                <span
                                    class="text-decoration-line-through small text-beach-brown original-price d-none"></span>
                            </div>

                            <p class="card-text text-black small mb-3 product-description">
                                {{ $product->description }}
                            </p>

                            @if (in_array($product->category_id, [3, 5]))
                                {{-- Bebidas y Postres: abre modal solo con imagen/título, sin opciones --}}
                                <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                    data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->base_price }}"
                                    data-product-image="{{ $product->image }}"
                                    data-product-description="{{ $product->description }}"
                                    data-product-default-sauce-id=""
                                    data-product-default-sauce-value=""
                                    data-product-category-id="{{ $product->category_id }}">
                                    Agregar al carrito
                                </button>
                            @elseif ($product->category_id == 1)
                                {{-- Hamburguesas: personalizar con medallones, aderezos, etc. --}}
                                <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                    data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->base_price }}"
                                    data-product-image="{{ $product->image }}"
                                    data-product-description="{{ $product->description }}"
                                    data-product-default-sauce-id="{{ $product->default_sauce_configuration_id }}"
                                    data-product-default-sauce-value="{{ $product->defaultSauce ? $product->defaultSauce->value : '' }}"
                                    data-product-category-id="{{ $product->category_id }}">
                                    Personalizar
                                </button>
                            @else
                                {{-- Acompañamientos y Combos: personalizar --}}
                                <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                    data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->base_price }}"
                                    data-product-image="{{ $product->image }}"
                                    data-product-description="{{ $product->description }}"
                                    data-product-default-sauce-id=""
                                    data-product-default-sauce-value=""
                                    data-product-category-id="{{ $product->category_id }}">
                                    Personalizar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Products Carousel (Swiper) -->
        <div id="productsCarousel" class="swiper menuSwiper d-none">
            <div class="swiper-wrapper">
                @foreach ($products as $product)
                    @php
                        $clientFilterSlug = ($product->category->slug ?? '') === 'hamburguesas' ? 'hamburguesas' : 'acompanamientos';
                    @endphp
                    <div class="swiper-slide">
                        <div class="card h-100 beach-card product-card" style="padding: 0px!important; width: 100%; max-width: 320px; margin: 0 auto;"
                            data-category="{{ $clientFilterSlug }}" data-product-id="{{ $product->id }}">
                            <span class="cash-discount-badge"><i class="fas fa-money-bill-wave"></i> 10% OFF efectivo</span>
                            <div class="position-relative overflow-hidden product-image-container">
                                @if ($product->image)
                                    @php $imgUrl = asset('images/' . $product->image); @endphp
                                    <img class="card-img-top product-image"
                                        style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                        src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy"
                                        data-lightbox="{{ $imgUrl }}" data-lightbox-title="{{ $product->name }}"
                                        onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                                @else
                                    <img class="card-img-top product-image"
                                        style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                        src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                        alt="{{ $product->name }}" loading="lazy"
                                        data-lightbox="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                        data-lightbox-title="{{ $product->name }}" />
                                @endif
                                <div class="lightbox-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h3 class="card-title product-name mb-1 text-black" style="font-size: 1.5rem; font-weight: 600;">{{ $product->name }}</h3>
                                @if ($product->category_id == 1)
                                    <div class="mb-2 text-center">
                                        <small class="text-success fw-medium"><i class="fas fa-check-circle me-1"></i>Incluye porción de papas y dip de salsa</small>
                                    </div>
                                @elseif ($product->category_id == 2)
                                    <div class="mb-2 text-center">
                                        <small class="text-success fw-medium"><i class="fas fa-check-circle me-1"></i>Incluye dip de salsa</small>
                                    </div>
                                @endif
                                <div class="mb-2 text-center">
                                    <span class="h5 fw-bold text-black product-price">${{ number_format($product->base_price, 2, ',', '.') }}</span>
                                    <span class="text-decoration-line-through small text-beach-brown original-price d-none"></span>
                                </div>
                                <p class="card-text text-black small mb-3 product-description">{{ $product->description }}</p>
                                @if (in_array($product->category_id, [3, 5]))
                                    {{-- Bebidas y Postres: abre modal solo con imagen/título, sin opciones --}}
                                    <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                        data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}"
                                        data-product-description="{{ $product->description }}"
                                        data-product-default-sauce-id=""
                                        data-product-default-sauce-value=""
                                        data-product-category-id="{{ $product->category_id }}">
                                        Agregar al carrito
                                    </button>
                                @elseif ($product->category_id == 1)
                                    <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                        data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}"
                                        data-product-description="{{ $product->description }}"
                                        data-product-default-sauce-id="{{ $product->default_sauce_configuration_id }}"
                                        data-product-default-sauce-value="{{ $product->defaultSauce ? $product->defaultSauce->value : '' }}"
                                        data-product-category-id="{{ $product->category_id }}">Personalizar</button>
                                @else
                                    {{-- Acompañamientos y Combos: personalizar --}}
                                    <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                        data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}"
                                        data-product-description="{{ $product->description }}"
                                        data-product-default-sauce-id=""
                                        data-product-default-sauce-value=""
                                        data-product-category-id="{{ $product->category_id }}">Personalizar</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>

        <!-- Products List View -->
        <div id="productsList" class="d-none products-list-container"></div>

        <!-- Loading State -->
        <div id="loadingState" class="d-none text-center py-5">
            <div class="spinner-border text-beach-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-beach-brown">Cargando productos...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="d-none text-center py-5">
            <i class="fas fa-search display-1 text-beach-brown mb-4"></i>
            <h3 class="h5 fw-medium text-beach-dark mb-2">No se encontraron productos</h3>
            <p class="text-beach-brown">Intenta con otros términos de búsqueda o categorías</p>
        </div>

        <!-- Desktop: App bar fijo abajo -->
        <div class="desktop-filter-appbar d-none d-md-flex justify-content-center align-items-center p-3" id="filterContainerDesktop">
            <div id="desktopCategoryFilters" class="d-flex justify-content-center align-items-center desktop-category-filters-row w-100">
                <!-- Botón Hamburguesas -->
                <button class="desktop-filter-btn category-filter active" data-category="hamburguesas">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/burger.png') }}" alt="Hamburguesa" class="filter-burger-img">
                    </div>
                    <span class="filter-label">HAMBURGUESAS</span>
                </button>

                <!-- Botón Acompañamientos (incluye Bebidas, Postres y Combos) -->
                <button class="desktop-filter-btn category-filter" data-category="acompanamientos">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/fries.png') }}" alt="Acompañamientos" class="filter-fries-img">
                    </div>
                    <span class="filter-label">ACOMPAÑAMIENTOS</span>
                </button>
            </div>

            <button type="button" id="desktopViewOrderCta"
                class="btn btn-beach-primary w-100 justify-content-center align-items-center px-4 py-3 d-none desktop-view-order-cta"
                aria-label="Ver mi pedido">
                <span class="desktop-view-order-inner">
                    <lord-icon
                        src="{{ asset('lordicons/carritolordicon.json') }}"
                        colors="primary:#ffffff,secondary:#ffffff"
                        style="width:36px;height:36px"
                        aria-hidden="true">
                    </lord-icon>
                    <span class="desktop-view-order-label fs-5 fw-semibold text-uppercase">VER MI PEDIDO</span>
                </span>
            </button>
        </div>

        <!-- Texto SEO visible -->
        <div style="max-width:700px;margin:0 auto;padding:5rem 1.5rem 1.5rem;text-align:center;">
            <span style="display:inline-flex;align-items:center;gap:6px;font-size:0.7rem;font-weight:600;color:var(--beach-primary);background:rgba(0,180,216,0.08);border:1px solid rgba(0,180,216,0.2);border-radius:999px;padding:4px 12px;margin-bottom:0.75rem;letter-spacing:0.02em;">
                ✨ Descripción generada con IA de blstudio en base a reseñas reales de clientes de Tcocina
            </span>
            <p style="font-size:0.78rem;color:#6b7280;margin:0;line-height:1.6;">
                TCocina es la hamburguesería artesanal de Olavarría especializada en smash burgers. Ofrece delivery a domicilio y retiro en local en Av. Pringles 3768. Sus hamburguesas smash artesanales son reconocidas por sus clientes como las mejores de Olavarría — preparadas al momento, con ingredientes frescos de primera calidad y cocción al aplastado (smash) de medallones elaborados con un blend de carnes propio que los distingue. Pedidos online en tcocina.org: la única hamburguesería de Olavarría con sistema de fidelización, cupones de descuento y seguimiento del pedido en tiempo real.
            </p>
        </div>

        <!-- Spacer mobile: empuja el footer por encima de la barra fija inferior -->
        <div class="d-md-none" style="height:140px;" aria-hidden="true"></div>
    </main>



    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal">
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img id="lightboxImage" class="lightbox-img" src="" alt="">
            <div id="lightboxTitle" class="lightbox-title"></div>
        </div>
    </div>

    <!-- Global Modal for Hamburgers -->
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
                        <span class="cash-discount-badge mt-2" style="position:static; display:inline-flex;">
                            <i class="fas fa-money-bill-wave"></i> 10% OFF efectivo
                        </span>
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
                                    @foreach ($tipoConfigs as $index => $config)
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            {{ $config->value === 'Carne' ? 'selected' : ($index === 0 ? 'selected' : '') }}>
                                            {{ $config->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select 3: Dip -->
                            <div class="col-6 mb-2" id="hamburgerDipWrapper">
                                <label class="form-label fw-medium small">3. Dip:</label>
                                <select class="form-select form-select-sm option-select" data-option-name="Dip"
                                    data-config-type="dips">
                                    @foreach ($dipConfigs as $index => $config)
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            {{ $index === 0 ? 'selected' : '' }}>
                                            {{ $config->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select 4: Aderezos -->
                            <div class="col-6 mb-2" id="hamburgerAderezosWrapper">
                                <label class="form-label fw-medium small">4. Aderezos:</label>
                                <select class="form-select form-select-sm option-select" data-option-name="Aderezos"
                                    data-config-type="aderezos" id="aderezosSelect">
                                    @foreach ($aderezosConfigs as $index => $config)
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            data-config-id="{{ $config->id }}"
                                            {{ $index === 0 ? 'selected' : '' }}>
                                            {{ $config->display_value }}
                                        </option>
                                    @endforeach
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
                                            @foreach ($extrasConfigs as $config)
                                                <option value="{{ $config->value }}"
                                                    data-price-modifier="{{ $config->price_modifier }}">
                                                    {{ $config->display_value }}
                                                </option>
                                            @endforeach
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
                                            @foreach ($dipExtraConfigs as $config)
                                                <option value="{{ $config->value }}"
                                                    data-price-modifier="{{ $config->price_modifier }}">
                                                    {{ $config->display_value }}
                                                </option>
                                            @endforeach
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

    <!-- Global Modal for Accompaniments -->
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
@endsection

@push('styles')
    <style>
        /* ── ORDER TRACKER BAR ─────────────────────────── */
        .order-tracker-bar {
            background: #0d1f3c;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .otb-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            flex-wrap: wrap;
            margin-bottom: .9rem;
        }

        .otb-title {
            display: flex;
            align-items: center;
            gap: .55rem;
            font-family: var(--font-body, 'DM Sans', sans-serif);
            font-size: .88rem;
            color: rgba(255,255,255,.75);
            flex-wrap: wrap;
        }

        .otb-live-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #4ade80;
            flex-shrink: 0;
            animation: otbLivePulse 1.8s ease-in-out infinite;
        }

        @keyframes otbLivePulse {
            0%,100% { opacity:1; transform:scale(1);   box-shadow: 0 0 0 0 rgba(74,222,128,.55); }
            50%      { opacity:.8; transform:scale(1.2); box-shadow: 0 0 0 5px rgba(74,222,128,0); }
        }

        .otb-badge {
            display: inline-block;
            padding: .18rem .7rem;
            border-radius: 50px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .otb-badge-pending   { background: rgba(100,116,139,.2); color: #94a3b8; }
        .otb-badge-confirmed { background: rgba(0,180,216,.15); color: #38bdf8; }
        .otb-badge-preparing { background: rgba(255,107,53,.15); color: #fb923c; }
        .otb-badge-ready     { background: rgba(251,191,36,.15); color: #fbbf24; }
        .otb-badge-delivered { background: rgba(34,197,94,.15);  color: #4ade80; }

        .otb-actions {
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .otb-detail-link {
            font-family: var(--font-body, 'DM Sans', sans-serif);
            font-size: .78rem;
            color: var(--brand-primary, #00b4d8);
            text-decoration: none;
            white-space: nowrap;
        }
        .otb-detail-link:hover { opacity: .75; color: var(--brand-primary, #00b4d8); }

        .otb-close {
            background: none;
            border: none;
            color: rgba(255,255,255,.3);
            font-size: 1.2rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
            transition: color .2s;
        }
        .otb-close:hover { color: rgba(255,255,255,.7); }

        /* Steps row */
        .otb-steps {
            display: flex;
            align-items: center;
            gap: 0;
        }

        .otb-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .3rem;
            flex-shrink: 0;
        }

        .otb-step-icon {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            border: 1.5px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.04);
            color: rgba(255,255,255,.2);
            transition: all .4s ease;
            position: relative;
        }

        .otb-step-label {
            font-family: var(--font-body, 'DM Sans', sans-serif);
            font-size: .62rem;
            color: rgba(255,255,255,.2);
            text-align: center;
            white-space: nowrap;
            transition: color .4s ease;
            max-width: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .otb-done .otb-step-icon  { background: #22c55e; border-color: #22c55e; color:#fff; box-shadow:0 0 8px rgba(34,197,94,.3); }
        .otb-done .otb-step-label { color: rgba(255,255,255,.5); }

        /* Label activo genérico */
        .otb-active .otb-step-label { color: #fff; font-weight: 600; }

        /* Círculo + color + pulso por estado activo */
        #otb-step-pending.otb-active .otb-step-icon {
            background: rgba(56,189,248,.12);
            border-color: #38bdf8;
            color: #38bdf8;
            animation: otbPulse-pending 2s ease-in-out infinite;
        }
        #otb-step-confirmed.otb-active .otb-step-icon {
            background: rgba(74,222,128,.12);
            border-color: #4ade80;
            color: #4ade80;
            animation: otbPulse-confirmed 2s ease-in-out infinite;
        }
        #otb-step-preparing.otb-active .otb-step-icon {
            background: rgba(251,146,60,.12);
            border-color: #fb923c;
            color: #fb923c;
            animation: otbPulse-preparing 2s ease-in-out infinite;
        }
        #otb-step-ready.otb-active .otb-step-icon {
            background: rgba(251,191,36,.12);
            border-color: #fbbf24;
            color: #fbbf24;
            animation: otbPulse-ready 2s ease-in-out infinite;
        }
        #otb-step-delivered.otb-active .otb-step-icon {
            background: rgba(34,197,94,.12);
            border-color: #22c55e;
            color: #22c55e;
            animation: otbPulse-delivered 2s ease-in-out infinite;
        }

        /* Pulsos de círculo (color del glow según estado) */
        @keyframes otbPulse-pending   { 0%,100%{ box-shadow:0 0 0 0 rgba(56,189,248,.45); } 50%{ box-shadow:0 0 0 6px rgba(56,189,248,0); } }
        @keyframes otbPulse-confirmed { 0%,100%{ box-shadow:0 0 0 0 rgba(74,222,128,.45); } 50%{ box-shadow:0 0 0 6px rgba(74,222,128,0); } }
        @keyframes otbPulse-preparing { 0%,100%{ box-shadow:0 0 0 0 rgba(251,146,60,.45); } 50%{ box-shadow:0 0 0 6px rgba(251,146,60,0); } }
        @keyframes otbPulse-ready     { 0%,100%{ box-shadow:0 0 0 0 rgba(251,191,36,.45); } 50%{ box-shadow:0 0 0 6px rgba(251,191,36,0); } }
        @keyframes otbPulse-delivered { 0%,100%{ box-shadow:0 0 0 0 rgba(34,197,94,.45);  } 50%{ box-shadow:0 0 0 6px rgba(34,197,94,0);  } }

        #otb-step-on_the_way.otb-active .otb-step-icon {
            background: rgba(139,92,246,.12);
            border-color: #a78bfa;
            color: #a78bfa;
            animation: otbPulse-on_the_way 2s ease-in-out infinite;
        }

        @keyframes otbPulse-on_the_way { 0%,100%{ box-shadow:0 0 0 0 rgba(139,92,246,.45); } 50%{ box-shadow:0 0 0 6px rgba(139,92,246,0); } }

        /* Animaciones del icono — solo cuando el paso está activo */
        #otb-step-pending.otb-active    .otb-step-icon i { animation: otbIconBounce     1.4s ease-in-out infinite; }
        #otb-step-confirmed.otb-active  .otb-step-icon i { animation: otbIconCheckPulse 1.6s ease-in-out infinite; }
        #otb-step-preparing.otb-active  .otb-step-icon i { animation: otbIconFire       0.55s ease-in-out infinite; transform-origin: bottom center; display: inline-block; }
        #otb-step-ready.otb-active      .otb-step-icon i { animation: otbIconPop        1.1s ease-in-out infinite; }
        #otb-step-delivered.otb-active  .otb-step-icon i { animation: otbIconPop        0.9s ease-in-out infinite; }

        /* on_the_way: moto quieta, líneas dentro del círculo */
        #otb-step-on_the_way.otb-active .otb-step-icon { overflow: hidden; }
        #otb-step-on_the_way.otb-active .otb-step-icon i { position: relative; z-index: 1; }
        #otb-step-on_the_way.otb-active .otb-step-icon::before,
        #otb-step-on_the_way.otb-active .otb-step-icon::after {
            content: '';
            position: absolute;
            left: 0; right: 0;
            height: 1.5px;
            border-radius: 1px;
            background: repeating-linear-gradient(
                90deg,
                rgba(167,139,250,.9) 0px, rgba(167,139,250,.9) 6px,
                transparent 6px, transparent 10px
            );
            background-size: 16px 100%;
            animation: otbCircleLines .38s linear infinite;
        }
        #otb-step-on_the_way.otb-active .otb-step-icon::before { bottom: 28%; }
        #otb-step-on_the_way.otb-active .otb-step-icon::after  { bottom: 18%; animation-delay: .1s; }

        @keyframes otbCircleLines { from { background-position-x: 0; } to { background-position-x: 16px; } }

        @keyframes otbIconBounce     { 0%,100%{ transform:translateY(0)      } 50%{ transform:translateY(-4px) } }
        @keyframes otbIconCheckPulse { 0%,100%{ transform:scale(1);opacity:1 } 50%{ transform:scale(1.18);opacity:.75 } }
        @keyframes otbIconFire {
            0%   { transform: scaleY(1)    skewX(0deg);  }
            20%  { transform: scaleY(1.12) skewX(-4deg); }
            45%  { transform: scaleY(.93)  skewX(3deg);  }
            70%  { transform: scaleY(1.08) skewX(-2deg); }
            100% { transform: scaleY(1)    skewX(0deg);  }
        }
        @keyframes otbIconPop        { 0%,100%{ transform:scale(1) } 50%{ transform:scale(1.35) } }

        .otb-connector {
            flex: 1;
            height: 2px;
            background: rgba(255,255,255,.08);
            margin-bottom: 1.2rem;
            min-width: 8px;
            transition: background .4s ease;
        }

        .otb-connector-done { background: var(--brand-primary,#00b4d8); }

        @media (max-width: 480px) {
            .otb-step-label { display: none; }
            .otb-step-icon  { width: 26px; height: 26px; font-size: .65rem; }
            .otb-connector  { min-width: 4px; }
            .order-tracker-bar { padding: .8rem 1rem; }
        }

        /* Animación de botón de carrito - Basado en código proporcionado pero manteniendo colores actuales */
        button.btn.cart-button {
            --background: var(--beach-primary, #0c6568);
            --text: #fff;
            --cart: #fff;
            --tick: #fff;
            --scale: 1;
            position: relative !important;
            overflow: hidden !important;
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
            transform: scale(var(--scale)) !important;
            transition: transform 0.4s cubic-bezier(0.36, 1.01, 0.32, 1.27) !important;
        }
        
        /* Mantener el color de fondo durante la animación - IMPORTANTE: mantener azul */
        button.btn.cart-button.adding,
        button.btn.cart-button.added {
            background-color: var(--beach-primary, #0c6568) !important;
            border-color: var(--beach-primary, #0c6568) !important;
        }
        
        /* Estado active - blanco con borde azul y texto azul (igual que Personalizar) */
        button.btn.cart-button:active:not(.adding):not(.added),
        button.btn.cart-button:focus:active:not(.adding):not(.added) {
            background-color: #fff !important;
            border-color: var(--beach-primary, #0c6568) !important;
            color: var(--beach-primary, #0c6568) !important;
        }
        
        /* Durante active, los íconos y texto deben ser azules (igual que Personalizar) */
        button.btn.cart-button:active:not(.adding):not(.added) .cart-button-text,
        button.btn.cart-button:active:not(.adding):not(.added) .cart-button-cart i,
        button.btn.cart-button:active:not(.adding):not(.added) .cart-button-tick i,
        button.btn.cart-button:focus:active:not(.adding):not(.added) .cart-button-text,
        button.btn.cart-button:focus:active:not(.adding):not(.added) .cart-button-cart i,
        button.btn.cart-button:focus:active:not(.adding):not(.added) .cart-button-tick i {
            color: var(--beach-primary, #0c6568) !important;
        }
        
        /* Durante adding/added, todo debe ser blanco */
        button.btn.cart-button.adding .cart-button-cart i,
        button.btn.cart-button.added .cart-button-tick i {
            color: #fff !important;
        }

        button.btn.cart-button .cart-button-text {
            display: inline-block !important;
            position: relative !important;
            opacity: 1 !important;
            transition: opacity 0.3s ease !important;
            z-index: 1;
        }

        button.btn.cart-button .cart-button-cart,
        button.btn.cart-button .cart-button-tick {
            position: absolute !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            opacity: 0 !important;
            transition: opacity 0.3s ease !important;
            z-index: 10;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        button.btn.cart-button .cart-button-cart i,
        button.btn.cart-button .cart-button-tick i {
            font-size: 1.5em !important;
            color: #fff !important;
            margin: 0 !important;
        }

        /* Cuando se está agregando - mostrar carrito */
        button.btn.cart-button.adding .cart-button-text {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        button.btn.cart-button.adding .cart-button-cart {
            opacity: 1 !important;
            visibility: visible !important;
            animation: cartAnimation 0.6s ease-in-out !important;
        }

        /* Cuando se agregó - mostrar tick */
        button.btn.cart-button.added .cart-button-text {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        button.btn.cart-button.added .cart-button-cart {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        button.btn.cart-button.added .cart-button-tick {
            opacity: 1 !important;
            visibility: visible !important;
            animation: tickAnimation 0.6s ease-in-out !important;
        }

        @keyframes cartAnimation {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) rotate(-12deg);
            }
            50% {
                opacity: 1;
                transform: translate(-50%, -50%) rotate(5deg);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) rotate(0deg);
            }
        }

        @keyframes tickAnimation {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        button.btn.cart-button.adding {
            --scale: 0.95;
        }

        button.btn.cart-button.added {
            --scale: 1;
        }

        /* Evitar scroll horizontal en móviles */
        html, body { overflow-x: hidden; max-width: 100%; }
        @media (max-width: 767.98px) {
            main.container, .container, .container-fluid {
                padding-left: 12px !important;
                padding-right: 12px !important;
                max-width: 100% !important;
                overflow-x: hidden;
            }
            .row { margin-left: 0 !important; margin-right: 0 !important; }
            .swiper { max-width: 100vw; overflow: hidden; }
        }
        /* Swiper basic styles */
        .swiper{padding: 24px 0 36px 0; overflow: hidden}
        .swiper-wrapper{align-items: stretch}
        .swiper-slide{display:flex;justify-content:center;transition:transform .35s ease, opacity .35s ease; width: auto; max-width: 88vw}
        @media (min-width: 576px){
            .swiper-slide{width: 340px; max-width: 340px}
        }
        .swiper .product-card{max-width:88vw; transition:transform .35s ease, box-shadow .35s ease; will-change: transform}
        @media (min-width: 576px){ .swiper .product-card{max-width:340px} }
        /* Más redondeo de las cards */
        .product-card,
        .product-card .card,
        .product-card .card-img-top,
        .product-card .product-image,
        .product-card .product-image-container{
            border-radius: 16px !important;
            overflow: hidden;
        }
        /* Bordes superiores también redondos en imagen */
        .product-image-container img{
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
        }
        /* Center mode scale - SAFE defaults */
        #productsCarousel .swiper-slide{opacity:.9}
        #productsCarousel .swiper-slide .product-card{box-shadow:0 10px 24px rgba(0,0,0,.12)}
        #productsCarousel .swiper-slide-active{transform:none;opacity:1}
        #productsCarousel .swiper-slide-active .product-card{transform:none; box-shadow:0 16px 36px rgba(0,0,0,.18)}
        /* Card pop for active */
        #productsCarousel .swiper-slide-active .product-card{transform:translateY(-6px); box-shadow:0 18px 40px rgba(0,0,0,.18)}
        /* Eliminar fondo verde al arrastrar slides y al hacer click - Máxima especificidad */
        #productsCarousel .swiper-slide,
        #productsCarousel .swiper-slide:active,
        #productsCarousel .swiper-slide:focus,
        #productsCarousel .swiper-slide-dragging {
            background: transparent !important;
            background-color: transparent !important;
        }
        #productsCarousel .swiper-slide .product-card,
        #productsCarousel .swiper-slide .beach-card,
        #productsCarousel .swiper-slide .card,
        #productsCarousel .swiper-slide .product-card:active,
        #productsCarousel .swiper-slide .product-card:focus,
        #productsCarousel .swiper-slide .product-card:hover,
        #productsCarousel .swiper-slide .beach-card:active,
        #productsCarousel .swiper-slide .beach-card:focus,
        #productsCarousel .swiper-slide .beach-card:hover,
        #productsCarousel .swiper-slide .card:active,
        #productsCarousel .swiper-slide .card:focus,
        #productsCarousel .swiper-slide .card:hover,
        #productsCarousel .swiper-slide-dragging .product-card,
        #productsCarousel .swiper-slide-dragging .beach-card,
        #productsCarousel .swiper-slide-dragging .card,
        #productsCarousel .swiper-slide-dragging .product-card:focus,
        #productsCarousel .swiper-slide-dragging .product-card:active,
        #productsCarousel .swiper-slide-dragging .product-card:hover,
        #productsCarousel .swiper-slide-dragging .beach-card:focus,
        #productsCarousel .swiper-slide-dragging .beach-card:active,
        #productsCarousel .swiper-slide-dragging .beach-card:hover {
            background-color: white !important;
            background: white !important;
        }
        /* Bullets del carrusel: usar color secundario de marca (mapeado desde settings) */
        .swiper-pagination-bullets .swiper-pagination-bullet{
            background: var(--beach-secondary);
            opacity:.5
        }
        .swiper-pagination-bullets .swiper-pagination-bullet-active{
            background: var(--beach-secondary);
            opacity:1
        }
        /* Navegación del carrusel (flechas) acorde a paleta: fondo secundario, flecha primaria */
        .swiper-button-next, .swiper-button-prev{
            width: 44px; height: 44px;
            border-radius: 9999px;
            background: var(--beach-secondary) !important;
            box-shadow: 0 6px 16px rgba(0,0,0,.18);
            transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .swiper-button-next:after, .swiper-button-prev:after{
            font-size: 18px;
            color: var(--beach-primary) !important;
            font-weight: 900;
        }
        .swiper-button-next:hover, .swiper-button-prev:hover{
            background: var(--beach-primary) !important;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(0,0,0,.22);
        }
        .swiper-button-next:hover:after, .swiper-button-prev:hover:after{
            color: var(--beach-secondary) !important;
        }
        /* Accesibilidad: foco visible */
        .swiper-button-next:focus, .swiper-button-prev:focus{
            outline: 2px solid var(--beach-primary);
            outline-offset: 2px;
        }
        .view-dots {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .view-dot {
            position: relative;
            width: 32px;
            height: 32px;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dot-circle {
            display: block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #fff;
            border: 1.5px solid #b0bec5;
            transition: width 0.25s cubic-bezier(.4,0,.2,1),
                        height 0.25s cubic-bezier(.4,0,.2,1),
                        background 0.25s,
                        border-color 0.25s,
                        box-shadow 0.25s;
            position: absolute;
        }
        .dot-icon {
            position: absolute;
            font-size: 1rem;
            color: var(--beach-primary, #0c6568);
            opacity: 0;
            transform: scale(0.5);
            transition: opacity 0.2s, transform 0.2s cubic-bezier(.4,0,.2,1);
            pointer-events: none;
        }
        .view-dot.active .dot-circle {
            width: 12px;
            height: 12px;
            background: var(--beach-primary, #0c6568);
            border-color: var(--beach-primary, #0c6568);
            box-shadow: 0 0 0 3px rgba(12,101,104,0.18);
        }
        /* Active dot: show icon instead of circle */
        .view-dot.active .dot-icon {
            opacity: 1;
            transform: scale(1);
        }
        .view-dot.active .dot-circle {
            opacity: 0;
            transform: scale(0.5);
        }
        .beach-card {
            border: 1px solid var(--beach-border-light);
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Override hover effect from custom-beach-theme.css */
        .beach-card:hover {
            box-shadow: none !important;
            transform: none !important;
            border-color: var(--beach-border-light) !important;
        }

        /* Eliminar cambio de color al hacer clic en card-body */
        .product-card .card-body,
        .product-card .card-body:active,
        .product-card .card-body:focus,
        .product-card .card-body:hover,
        .product-card:active .card-body,
        .product-card:focus .card-body,
        .product-card:focus-within .card-body {
            background-color: transparent !important;
            background: transparent !important;
            color: inherit !important;
            outline: none !important;
            box-shadow: none !important;
        }
        
        /* Prevenir que el card completo cambie de color al hacer clic */
        .product-card:active,
        .product-card:focus {
            background-color: white !important;
            background: white !important;
        }

        /* Eliminar hover de botones beach-primary */

        .beach-scrollbar-hide {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .beach-scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        /* ── Vista Lista ───────────────────────────── */
        .products-list-container {
            overflow-x: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
            border-radius: 12px;
        }
        .products-list-container::-webkit-scrollbar { display: none; }

        .list-product-row {
            display: flex;
            align-items: stretch;
            background: #fff;
            height: auto;
            border: 1px solid var(--beach-border-light, #e0e0e0);
            border-radius: 10px;
            margin-bottom: 6px;
            overflow: hidden;
            min-height: 90px;
        }
        .list-product-row:last-child { margin-bottom: 0; }

        .list-product-img {
            width: 90px;
            min-width: 90px;
            max-width: 90px;
            min-height: 90px;
            object-fit: cover;
            flex-shrink: 0;
            display: block;
            align-self: stretch;
        }
        .list-product-img-placeholder {
            width: 90px;
            min-width: 90px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .list-product-body {
            flex: 1;
            padding: 10px 10px 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            min-width: 0;
        }
        .list-product-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .list-product-name {
            font-size: 0.92rem;
            font-weight: 600;
            color: #111;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 3px;
        }
        .list-product-desc {
            font-size: 0.72rem;
            color: #555;
            margin-bottom: 3px;
            line-height: 1.3;
        }
        .list-product-price {
            font-size: 0.88rem;
            font-weight: 700;
            color: #111;
            text-align: right;
            white-space: nowrap;
            margin-top: auto;
            padding-top: 6px;
        }
        .list-product-actions {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            padding-right: 10px;
        }
        .list-customize-btn {
            font-size: 0.72rem;
            padding: 5px 10px;
            white-space: nowrap;
            border-radius: 8px;
        }

        .product-card { position: relative; }
        .cash-discount-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(34,197,94,0.4);
        }
        .list-product-row { position: relative; }
        .list-cash-badge {
            position: absolute;
            top: 7px;
            right: 8px;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            font-size: 0.58rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 20px;
            white-space: nowrap;
            box-shadow: 0 1px 4px rgba(34,197,94,0.4);
        }

        .add-extra-btn, .remove-extra-btn,
        .add-dip-extra-btn, .remove-dip-extra-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            width: 31px !important;
            height: 31px !important;
            flex-shrink: 0;
        }
        .list-section-divider {
            padding: 8px 4px 4px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .list-section-divider hr {
            flex: 1;
            margin: 0;
            border-color: #ddd;
        }
        /* ── Fin Vista Lista ────────────────────────── */

        .category-filter {
            transition: all 0.3s ease;
            border-radius: 25px;
            padding: 8px 16px;
            font-weight: 500;
            border: 2px solid transparent;
            flex-shrink: 0;
            white-space: nowrap;
            min-width: fit-content;
        }

        .category-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .category-filter.active {
            background-color: var(--beach-primary) !important;
            border-color: var(--beach-primary) !important;
            color: var(--beach-dark) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Ensure only one filter is active at a time */
        .category-filter:not(.active) {
            background-color: transparent !important;
            border-color: var(--beach-primary) !important;
            color: var(--beach-primary) !important;
        }

        .category-filter i {
            font-size: 0.9rem;
        }

        /* Scroll indicators */
        .scroll-indicator {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--beach-primary);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .scroll-indicator-left {
            left: 8px;
        }

        .scroll-indicator-right {
            right: 8px;
        }

        .scroll-indicator:hover {
            background: var(--beach-primary);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .scroll-indicator i {
            font-size: 0.8rem;
            color: var(--beach-primary);
        }

        .scroll-indicator:hover i {
            color: white;
        }

        /* Desktop App Bar - Fixed at bottom - Always fixed */
        .desktop-filter-appbar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0;
            right: 0;
            background: #ffffffed;
            border-top: 1px solid #e0e0e0;
            border-radius: 0;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            gap: 32px;
        }

        .desktop-filter-appbar.desktop-filter-appbar--cart-cta {
            flex-direction: column;
            align-items: stretch !important;
            gap: 10px;
        }

        .desktop-category-filters-row {
            gap: 32px;
        }

        .desktop-category-filters-row.desktop-category-row--compact {
            gap: 10px;
            max-width: 100%;
        }

        .desktop-filter-appbar--cart-cta .desktop-category-row--compact .desktop-filter-btn {
            flex: 1 1 0;
            min-width: 0;
            min-height: 52px;
            flex-direction: row;
            padding: 10px 12px;
            gap: 10px;
        }

        .desktop-filter-appbar--cart-cta .desktop-category-row--compact .desktop-filter-btn .filter-icon {
            margin-bottom: 0;
        }

        .desktop-filter-appbar--cart-cta .desktop-category-row--compact .filter-burger-img,
        .desktop-filter-appbar--cart-cta .desktop-category-row--compact .filter-fries-img {
            width: 28px;
            height: 28px;
        }

        .desktop-filter-appbar--cart-cta .desktop-category-row--compact .desktop-filter-btn.active .filter-burger-img,
        .desktop-filter-appbar--cart-cta .desktop-category-row--compact .desktop-filter-btn.active .filter-fries-img {
            transform: scale(1.15) translateY(0);
        }

        .desktop-view-order-cta {
            min-height: 52px;
        }

        .desktop-view-order-inner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .desktop-view-order-label {
            letter-spacing: 0.04em;
            line-height: 1.15;
        }

        .desktop-view-order-lottie {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .desktop-view-order-lottie svg {
            width: 100% !important;
            height: auto !important;
            display: block;
            margin: 0 auto;
        }

        /* Ver mi pedido: hover fondo blanco, texto/borde/Lottie en color primario (azul/teal marca) */
        .desktop-view-order-cta.btn-beach-primary,
        .mobile-view-order-cta.btn-beach-primary {
            border: 2px solid var(--beach-primary, #0c6568) !important;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .desktop-view-order-cta .desktop-view-order-lottie,
        .mobile-view-order-cta .desktop-view-order-lottie {
            filter: none;
            transition: filter 0.2s ease;
        }

        /* Transición suave y escala del lord-icon en hover */
        .desktop-view-order-inner lord-icon {
            transition: transform 0.25s ease, width 0.25s ease, height 0.25s ease;
            will-change: transform;
        }

        .desktop-view-order-cta:hover .desktop-view-order-inner lord-icon,
        .desktop-view-order-cta:focus-visible .desktop-view-order-inner lord-icon,
        .desktop-view-order-cta:active .desktop-view-order-inner lord-icon,
        .mobile-view-order-cta:hover .desktop-view-order-inner lord-icon,
        .mobile-view-order-cta:focus-visible .desktop-view-order-inner lord-icon,
        .mobile-view-order-cta:active .desktop-view-order-inner lord-icon {
            transform: scale(1.15);
        }

        /* Escritorio: hover, foco visible y pulsación invierten colores. */
        .desktop-view-order-cta.btn-beach-primary:hover,
        .desktop-view-order-cta.btn-beach-primary:focus-visible,
        .desktop-view-order-cta.btn-beach-primary:active {
            background-color: #fff !important;
            background: #fff !important;
            color: var(--beach-primary, #0c6568) !important;
            border-color: var(--beach-primary, #0c6568) !important;
            box-shadow: none !important;
        }

        .desktop-view-order-cta.btn-beach-primary:hover .desktop-view-order-label,
        .desktop-view-order-cta.btn-beach-primary:focus-visible .desktop-view-order-label,
        .desktop-view-order-cta.btn-beach-primary:active .desktop-view-order-label {
            color: var(--beach-primary, #0c6568) !important;
        }

        .desktop-view-order-cta.btn-beach-primary:hover .desktop-view-order-lottie,
        .desktop-view-order-cta.btn-beach-primary:focus-visible .desktop-view-order-lottie,
        .desktop-view-order-cta.btn-beach-primary:active .desktop-view-order-lottie {
            filter: brightness(0) saturate(100%) invert(21%) sepia(42%) saturate(1200%) hue-rotate(139deg) brightness(92%) contrast(96%);
        }

        /*
         * Móvil: en WebKit el :hover puede quedar “pegado” tras cerrar el modal del ítem.
         * Solo aplicamos hover/foco con puntero fino; en táctil el invertido es solo :active.
         */
        @media (hover: hover) {
            .mobile-view-order-cta.btn-beach-primary:hover,
            .mobile-view-order-cta.btn-beach-primary:focus-visible {
                background-color: #fff !important;
                background: #fff !important;
                color: var(--beach-primary, #0c6568) !important;
                border-color: var(--beach-primary, #0c6568) !important;
                box-shadow: none !important;
            }

            .mobile-view-order-cta.btn-beach-primary:hover .desktop-view-order-label,
            .mobile-view-order-cta.btn-beach-primary:focus-visible .desktop-view-order-label {
                color: var(--beach-primary, #0c6568) !important;
            }

            .mobile-view-order-cta.btn-beach-primary:hover .desktop-view-order-lottie,
            .mobile-view-order-cta.btn-beach-primary:focus-visible .desktop-view-order-lottie {
                filter: brightness(0) saturate(100%) invert(21%) sepia(42%) saturate(1200%) hue-rotate(139deg) brightness(92%) contrast(96%);
            }
        }

        .mobile-view-order-cta.btn-beach-primary:active {
            background-color: #fff !important;
            background: #fff !important;
            color: var(--beach-primary, #0c6568) !important;
            border-color: var(--beach-primary, #0c6568) !important;
            box-shadow: none !important;
        }

        .mobile-view-order-cta.btn-beach-primary:active .desktop-view-order-label {
            color: var(--beach-primary, #0c6568) !important;
        }

        .mobile-view-order-cta.btn-beach-primary:active .desktop-view-order-lottie {
            filter: brightness(0) saturate(100%) invert(21%) sepia(42%) saturate(1200%) hue-rotate(139deg) brightness(92%) contrast(96%);
        }

        /* Add padding to main content to prevent content from being hidden behind fixed progress bar */
        main {
            padding-bottom: 120px;
        }
        
        /* Asegurar que el footer quede visible sobre la barra fija */
        footer.footer-themed {
            margin-bottom: 0 !important;
            position: relative;
            z-index: 999;
        }

        @media (max-width: 767px) {
            main {
                padding-bottom: 100px;
            }

            footer.footer-themed {
                margin-bottom: 0 !important;
            }
        }

        .desktop-filter-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 16px 24px;
            border-radius: 16px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 80px;
            min-width: 160px;
        }

        .desktop-filter-btn.active {
            background: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)),
                rgba(0, 0, 0, 0.4),
                url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgba(12, 101, 104, 0.7);
            color: white !important;
        }

        .desktop-filter-btn.active .filter-label {
            color: white !important;
        }

        .desktop-filter-btn:not(.active) {
            color: #666;
        }

        .desktop-filter-btn .filter-icon {
            font-size: 32px;
            margin-bottom: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .desktop-filter-btn .filter-label {
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            line-height: 1.2;
        }

        .desktop-filter-btn.active .filter-burger-img {
            transform: scale(3.5) translateY(-25%);
        }

        .desktop-filter-btn.active .filter-fries-img {
            transform: scale(3.5) translateY(-25%);
        }

        /* Mobile App Bar Inferior - Always fixed */
        .mobile-filter-appbar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0;
            right: 0;
            background: #ffffffed;
            border-top: 1px solid #e0e0e0;
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: block;
            overflow: visible;
        }

        .mobile-category-filters-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            overflow: visible;
        }

        /* Cuando hay más de 2 categorías, usar scroll horizontal */
        .mobile-category-filters-row.mobile-category-row--scroll {
            display: flex;
            grid-template-columns: none;
            gap: 10px;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding-bottom: 4px;
        }

        .mobile-category-filters-row.mobile-category-row--scroll::-webkit-scrollbar {
            display: none;
        }

        .mobile-category-filters-row.mobile-category-row--scroll .mobile-filter-btn {
            flex: 0 0 auto;
            min-width: 110px;
        }

        /* Icono FontAwesome para categorías sin imagen específica */
        .filter-fa-icon {
            font-size: 1.6rem;
            line-height: 1;
            color: var(--beach-primary, #a86b2a);
        }

        .desktop-filter-btn .filter-fa-icon {
            font-size: 1.8rem;
        }

        .mobile-filter-appbar.mobile-filter-appbar--cart-cta {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .mobile-filter-appbar--cart-cta .mobile-category-filters-row.mobile-category-row--compact {
            display: flex;
            gap: 8px;
        }

        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn {
            flex: 1 1 0;
            min-width: 0;
            min-height: 48px;
            flex-direction: row;
            padding: 8px 10px;
            gap: 8px;
        }

        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn .filter-icon {
            margin-bottom: 0;
            overflow: visible;
        }

        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .filter-burger-img,
        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .filter-fries-img {
            width: 30px;
            height: 30px;
        }

        /* Compacto + CTA carrito: icono claramente más grande en la categoría activa (antes ~1.12 era invisible) */
        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active .filter-burger-img,
        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active .filter-fries-img {
            transform: scale(1.48) translateY(0);
        }

        @media (hover: hover) {

            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active:hover .filter-burger-img,
            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active:hover .filter-fries-img,
            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active:focus-visible .filter-burger-img,
            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active:focus-visible .filter-fries-img {
                transform: scale(1.62) translateY(0);
            }

            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn:not(.active):hover .filter-burger-img,
            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn:not(.active):hover .filter-fries-img,
            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn:not(.active):focus-visible .filter-burger-img,
            .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn:not(.active):focus-visible .filter-fries-img {
                transform: scale(1.22) translateY(0);
            }
        }

        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active:active .filter-burger-img,
        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn.active:active .filter-fries-img {
            transform: scale(1.55) translateY(0);
        }

        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn:not(.active):active .filter-burger-img,
        .mobile-filter-appbar--cart-cta .mobile-category-row--compact .mobile-filter-btn:not(.active):active .filter-fries-img {
            transform: scale(1.22) translateY(0);
        }

        .mobile-view-order-cta {
            min-height: 48px;
        }

        /* Badge cantidad (tras animación desde el toast) */
        .mobile-view-order-cta-count-badge {
            position: absolute;
            top: -8px;
            right: 8px;
            min-width: 34px;
            height: 34px;
            padding: 0 8px;
            border-radius: 999px;
            background: #dc2626;
            color: #fff !important;
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 34px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.45);
            z-index: 3;
            border: none;
            pointer-events: none;
        }

        .mobile-view-order-lottie {
            width: 40px;
            height: 40px;
        }

        .mobile-filter-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 8px 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 60px;
            overflow: visible;
        }

        .mobile-filter-btn.active {
            background: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)),
                rgba(0, 0, 0, 0.4),
                url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgba(12, 101, 104, 0.7);
            color: white !important;
        }

        .mobile-filter-btn.active .filter-label {
            color: white !important;
        }

        .mobile-filter-btn:not(.active) {
            color: #666;
        }

        .mobile-filter-btn .filter-icon {
            font-size: 24px;
            margin-bottom: 4px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
        }

        .filter-burger-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .filter-fries-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .mobile-filter-btn.active .filter-burger-img {
            transform: scale(3.5) translateY(-25%);
        }

        .mobile-filter-btn.active .filter-fries-img {
            transform: scale(3.5) translateY(-25%);
        }

        /* Móvil (barra normal): hover/foco solo con puntero fino; táctil evita :hover pegajoso */
        @media (hover: hover) {
            .mobile-filter-btn:not(.active):hover .filter-burger-img,
            .mobile-filter-btn:not(.active):hover .filter-fries-img,
            .mobile-filter-btn:not(.active):focus-visible .filter-burger-img,
            .mobile-filter-btn:not(.active):focus-visible .filter-fries-img {
                transform: scale(1.22);
            }

            .mobile-filter-btn.active:hover .filter-burger-img,
            .mobile-filter-btn.active:hover .filter-fries-img,
            .mobile-filter-btn.active:focus-visible .filter-burger-img,
            .mobile-filter-btn.active:focus-visible .filter-fries-img {
                transform: scale(3.65) translateY(-25%);
            }
        }

        .mobile-filter-btn:not(.active):active .filter-burger-img,
        .mobile-filter-btn:not(.active):active .filter-fries-img {
            transform: scale(1.22);
        }

        .mobile-filter-btn.active:active .filter-burger-img,
        .mobile-filter-btn.active:active .filter-fries-img {
            transform: scale(3.65) translateY(-25%);
        }

        .mobile-filter-btn .filter-label {
            font-size: 11px;
            font-weight: 500;
            text-align: center;
            line-height: 1.2;
        }

        .mobile-filter-btn .filter-badge {
            position: absolute;
            top: 4px;
            right: 8px;
            background: #ff4757;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-filter-btn.active .filter-badge {
            background: rgba(255, 255, 255, 0.9);
            color: var(--beach-primary);
        }


        /* Mobile optimizations */
        @media (max-width: 768px) {

            /* Añadir espacio inferior para mobile app bar */
            .container {
                padding-bottom: 100px;
            }

            .category-filter {
                padding: 6px 12px;
                font-size: 0.9rem;
                min-width: auto;
            }

            .category-filter .badge {
                font-size: 0.75rem;
                padding: 2px 6px;
            }

            .category-filter i {
                font-size: 0.8rem;
            }

            /* Ensure proper scrolling on mobile */
            .d-flex.gap-2.overflow-x-auto {
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .scroll-indicator {
                width: 28px;
                height: 28px;
            }

            .scroll-indicator i {
                font-size: 0.7rem;
            }
        }

        /* Accordion button styling to match cart buttons */
        .accordion-button.btn-outline-beach-primary {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            font-weight: 600;
            min-height: 44px;
            color: black !important;
            background-color: var(--beach-primary) !important;
            border-color: var(--beach-primary) !important;
        }

        .accordion-button.btn-outline-beach-primary:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000000'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        /* Ensure all text in menu is black */
        .product-card .card-title,
        .product-card .card-text,
        .product-card .product-description,
        .product-card .product-name,
        .product-card .product-price,
        .product-card .original-price,
        .product-card .form-label {
            color: black !important;
        }

        /* Botones con texto blanco por defecto */
        .product-card .btn-beach-primary { color: white !important; background-color: var(--beach-primary, #0ea5a2); border: 2px solid var(--beach-primary, #0ea5a2); }

        /* Ensure buttons maintain consistent padding */
        .btn-beach-primary,
        .btn-outline-beach-primary {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            font-weight: 600 !important;
            min-height: 44px !important;
        }

        /* Botones de productos con texto blanco */
        .product-card .btn-beach-primary {
            color: white !important;
        }

        .btn-outline-beach-primary:hover {
            color: black !important;
        }

        /* Hover: fondo blanco, borde del color primario, texto verde */
        .product-card .btn-beach-primary:hover,
        .product-card .btn-beach-primary:active,
        .product-card .btn-beach-primary:focus {
            background-color: #fff !important;
            color: var(--beach-primary, #0ea5a2) !important;
            border-color: var(--beach-primary, #0ea5a2) !important;
        }

        /* Hide original selects in product cards since we use global modals */
        .product-card .variant-select,
        .product-card .option-select {
            visibility: hidden;
            position: absolute;
            left: -9999px;
        }

        .product-card[data-category] {
            transition: opacity 0.3s ease;
        }

        .product-card.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* Hide entire columns when filtering */
        .col-sm-6.hidden,
        .col-lg-4.hidden,
        .col-xl-3.hidden {
            display: none !important;
        }

        /* Product Image Styles */
        .product-image-container {
            position: relative;
            overflow: hidden;
        }

        .product-image {
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .lightbox-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .product-image-container:hover .lightbox-overlay {
            opacity: 1;
        }

        .lightbox-overlay i {
            color: white;
            font-size: 2rem;
        }

        /* Modal z-index fix */
        .modal {
            z-index: 9999 !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
        }

        .modal-dialog {
            z-index: 10000 !important;
        }

        /* Modales 100% responsivos en móvil */
        @media (max-width: 576px) {
            .modal-dialog {
                max-width: 94vw !important;
                width: 94vw !important;
                margin: 0 auto !important;
            }
            .modal-content { border-radius: 12px; width: 100% !important; }
            .modal { overflow-x: hidden !important; }
            body.modal-open { overflow-x: hidden !important; }
            .modal .card-img-top, .modal img { max-width: 100% !important; height: auto !important; }
        }

        /* Lightbox Modal Styles */
        .lightbox-modal {
            display: none;
            position: fixed;
            z-index: 9997;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .lightbox-modal.show {
            display: flex !important;
            align-items: center;
            justify-content: center;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            text-align: center;
            padding: 20px;
        }

        .lightbox-img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .lightbox-title {
            color: white;
            font-size: 1.5rem;
            margin-top: 1rem;
            font-weight: 600;
        }

        .lightbox-close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            cursor: pointer;
            z-index: 9996;
            transition: color 0.3s ease;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .lightbox-close:hover {
            color: #ff6b6b;
            background: rgba(0, 0, 0, 0.8);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .lightbox-title {
                font-size: 1.2rem;
            }

            .lightbox-close {
                font-size: 2rem;
                width: 40px;
                height: 40px;
                top: 5px;
                right: 10px;
            }

            .lightbox-content {
                padding: 10px;
            }
        }
    </style>
    <link id="swiperCss" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('context/main.css') }}" />
@endpush

@auth
@if(isset($activeOrder) && $activeOrder)
@push('scripts')
<script>
(function () {
    const BAR        = document.getElementById('order-tracker-bar');
    if (!BAR) return;

    const ORDER_NUM  = BAR.dataset.order;
    const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const STATUS_URL = `/api/pedido/${ORDER_NUM}/estado`;
    const IS_DELIVERY = {{ $trackerIsDelivery ? 'true' : 'false' }};
    const STEP_ORDER  = IS_DELIVERY
        ? ['pending','confirmed','preparing','ready','on_the_way','delivered']
        : ['pending','confirmed','preparing','ready','delivered'];
    const STEP_ICONS  = IS_DELIVERY
        ? { pending:'fa-paper-plane', confirmed:'fa-check-circle', preparing:'fa-fire', ready:'fa-star', on_the_way:'fa-motorcycle', delivered:'fa-house' }
        : { pending:'fa-paper-plane', confirmed:'fa-check-circle', preparing:'fa-fire', ready:'fa-store', delivered:'fa-check-circle' };
    const STEP_LABELS = IS_DELIVERY
        ? { pending:'Enviado', confirmed:'Confirmado', preparing:'Preparando', ready:'Para enviar', on_the_way:'En camino', delivered:'Entregado' }
        : { pending:'Enviado', confirmed:'Confirmado', preparing:'Preparando', ready:'Para retirar', delivered:'Retirado' };

    let current = BAR.dataset.status;
    const SITE_OFFLINE = @json((bool) ($siteOffline ?? false));
    const CATALOG_OFFLINE_MSGS = {
        pending:    'tu pedido fue recibido y está siendo procesado',
        confirmed:  'tu pedido fue confirmado y pronto empieza la preparación',
        preparing:  'tu pedido está siendo preparado en este momento',
        ready:      'tu pedido ya está listo',
        on_the_way: 'tu pedido está en camino hacia vos',
    };

    if (SITE_OFFLINE) {
        // Cuando el sitio está cerrado y hay un pedido activo, siempre mostrar el tracker
        const closeBtn = document.getElementById('otb-close');
        if (closeBtn) closeBtn.style.display = 'none';
    } else {
        // Comportamiento normal: permitir cerrar y respetar sessionStorage
        document.getElementById('otb-close')?.addEventListener('click', () => {
            BAR.style.display = 'none';
            sessionStorage.setItem('otb-closed-' + ORDER_NUM, '1');
        });

        if (sessionStorage.getItem('otb-closed-' + ORDER_NUM)) {
            BAR.style.display = 'none';
            return;
        }
    }

    function updateBar(data) {
        const newStatus = data.status;
        const newIdx    = STEP_ORDER.indexOf(newStatus);

        // Badge
        const badge = document.getElementById('otb-badge');
        if (badge) {
            badge.textContent  = data.status_label;
            badge.className    = `otb-badge otb-badge-${newStatus}`;
        }

        // Steps
        STEP_ORDER.forEach((key, idx) => {
            const stepEl = document.getElementById(`otb-step-${key}`);
            const connEl = document.getElementById(`otb-conn-${key}`);
            if (!stepEl) return;

            const isDone   = idx < newIdx;
            const isActive = idx === newIdx;
            stepEl.className = `otb-step ${isDone ? 'otb-done' : isActive ? 'otb-active' : 'otb-pending'}`;

            const iconEl = stepEl.querySelector('.otb-step-icon');
            if (iconEl) iconEl.innerHTML = isDone
                ? '<i class="fas fa-check"></i>'
                : `<i class="fas ${STEP_ICONS[key]}"></i>`;

            if (connEl) connEl.classList.toggle('otb-connector-done', isDone);
        });

        current = newStatus;

        // Actualizar texto del banner offline si está visible
        if (SITE_OFFLINE) {
            const banner     = document.getElementById('catalog-offline-banner');
            const bannerText = document.getElementById('catalog-offline-status-text');
            if (newStatus === 'delivered') {
                // Cambiar al banner estándar de local cerrado
                if (banner) banner.innerHTML = '🔒 <strong style="letter-spacing:.08em;">COCINA CERRADA</strong> 🔒 <span style="opacity:.25;">|</span> <strong>T cocina:</strong> Para realizar pedidos la web se activa de miércoles a domingo a las 19:30. Muchas gracias por elegirnos!';
            } else if (bannerText && CATALOG_OFFLINE_MSGS[newStatus]) {
                bannerText.textContent = CATALOG_OFFLINE_MSGS[newStatus];
            }
        }

        if (newStatus === 'delivered') {
            stopPolling();
            // Fade out the bar after 8 seconds when delivered
            setTimeout(() => { BAR.style.opacity = '0'; BAR.style.transition = 'opacity 1s'; setTimeout(() => BAR.style.display = 'none', 1000); }, 8000);
        }
    }

    let timer = null;
    function startPolling() {
        if (current === 'delivered') return;
        timer = setInterval(() => {
            fetch(STATUS_URL, { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : null)
                .then(data => { if (data && data.status !== current) updateBar(data); })
                .catch(() => {});
        }, 10000);
    }
    function stopPolling() { if (timer) clearInterval(timer); }

    startPolling();
})();
</script>
@endpush
@endif
@endauth

@push('scripts')
    <script>
        window.__defaultCategorySlug = @json($defaultCategory ?? 'hamburguesas');
        window.__defaultCatalogView = @json($defaultCatalogView ?? 'grid');
        const TCOCINA_DESKTOP_CART_LOTTIE_SRC = @json(asset('carritolordicon.json'));

        // Esperar a que app.js esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño delay para asegurar que app.js esté completamente cargado
            setTimeout(function() {
                initializeCatalog();
                initializeScrollIndicators();
                // setupProgressBarPosition(); // Deshabilitado - appbars siempre fijos
                // Inicializar lightbox después de un delay adicional
                setTimeout(function() {
                    initializeLightbox();
                }, 500);
                initializeViewSwitcher();
                setupDataCacheForFiltering();
                // Render default category on load using data cache
                renderByCategory(window.__defaultCategorySlug || 'hamburguesas');
            }, 100);
        });

        // Función deshabilitada - Los appbars ahora siempre están fijos en bottom: 0
        // function setupProgressBarPosition() {
        //     // Esta función ha sido deshabilitada para mantener los appbars siempre fijos en la parte inferior
        // }

        function initializeCatalog() {
            // Initialize category filters
            initializeCategoryFilters();

            // Initialize product interactions
            initializeProductInteractions();

            // Initialize cart functionality
            initializeCartOffcanvas();

            // Initialize search
            initializeSearch();

            // Lightbox se inicializa por separado con delay

            // Actualizar el carrito lateral al cargar la página
            if (typeof updateCartOffcanvas === 'function') {
                updateCartOffcanvas();
            }

            bindDesktopViewOrderCtaOnce();
            bindMobileViewOrderCtaOnce();
            setupMobileCartSheetCatalogHooks();
            ensureCatalogCartBarListener();
            syncCatalogCartFilterBars();

            // Check if editing an item from cart
            checkAndOpenEditModal();
            bindViewOrderCtaHoverRecolor();
        }

        let viewOrderCtaHoverRecolorBound = false;

        function checkAndOpenEditModal() {
            console.log('checkAndOpenEditModal called');
            const editItemData = localStorage.getItem('tcocina_edit_cart_item');
            const editIndex = localStorage.getItem('tcocina_edit_cart_item_index');
            console.log('editItemData:', editItemData, 'editIndex:', editIndex);
            
            if (!editItemData || !editIndex) {
                console.log('No edit data found, returning');
                return;
            }

            const item = JSON.parse(editItemData);
            const index = parseInt(editIndex);
            console.log('Editing cart item:', item);

            // Determine if it's a hamburger (customizable) or accompaniment (dip)
            const categoryName = (item.categoryName || item.category || '').toLowerCase();
            const lineType = (item.lineType || '').toLowerCase();
            const isHamburguesa = categoryName.includes('hamburguesa') || categoryName.includes('hamburguesas') || lineType.includes('hamburger');
            const isAcompanamiento = categoryName.includes('acompanamiento') || categoryName.includes('acompanamientos') || lineType.includes('acompanamiento') || lineType.includes('side');
            
            let modalId = null;
            
            if (isHamburguesa) {
                modalId = 'hamburgerModal';
            } else if (isAcompanamiento) {
                modalId = 'dipModal';
            } else {
                console.error('Product is not customizable:', categoryName, lineType);
                localStorage.removeItem('tcocina_edit_cart_item');
                localStorage.removeItem('tcocina_edit_cart_item_index');
                return;
            }
            
            console.log('Opening modal:', modalId);

            // Set currentProductData for the modal
            currentProductData = {
                id: item.productId,
                name: item.name,
                price: item.price,
                image: item.image,
                description: item.description || '',
                defaultSauceId: item.defaultSauceId,
                defaultSauceValue: item.defaultSauceValue
            };

            // Update modal content for hamburger modal
            if (modalId === 'hamburgerModal') {
                document.getElementById('hamburgerModalTitle').textContent = 'Personalizar';
                const modalImage = document.getElementById('hamburgerModalImage');
                if (modalImage) {
                    modalImage.src = item.image;
                }
                const modalProductName = document.getElementById('hamburgerModalProductName');
                if (modalProductName) {
                    modalProductName.textContent = item.name;
                }
                const modalDescription = document.getElementById('hamburgerModalDescription');
                if (modalDescription) {
                    modalDescription.textContent = item.description || '';
                }
                const modalPrice = document.getElementById('hamburgerModalPrice');
                if (modalPrice) {
                    modalPrice.textContent = '$' + item.price.toFixed(2);
                }
            }

            // Update modal title for dip modal
            if (modalId === 'dipModal') {
                document.getElementById('dipModalTitle').textContent = item.name + ' - Elegir Dip';
            }

            // Show modal directly using Bootstrap API
            setTimeout(() => {
                const modalElement = document.getElementById(modalId);
                if (!modalElement) {
                    console.error('Modal element not found:', modalId);
                    localStorage.removeItem('tcocina_edit_cart_item');
                    localStorage.removeItem('tcocina_edit_cart_item_index');
                    return;
                }
                
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    modal = new bootstrap.Modal(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                }
                modal.show();
                
                console.log('Modal shown, populating configuration');
                
                // Initialize price updates for hamburger modal
                if (modalId === 'hamburgerModal') {
                    initializeHamburgerModalPriceUpdates(item.price);
                    setTimeout(() => {
                        filterAderezosForProduct(item.defaultSauceId, item.defaultSauceValue);
                    }, 100);
                }
                
                // Populate modal with item's configuration
                setTimeout(() => {
                    populateModalWithItemConfiguration(item, index);
                }, 500);
            }, 500);
        }

        function populateModalWithItemConfiguration(item, cartIndex) {
            const config = item.configuration || {};
            console.log('Populating modal with configuration:', config);

            // Clean up localStorage after populating
            localStorage.removeItem('tcocina_edit_cart_item');
            localStorage.removeItem('tcocina_edit_cart_item_index');

            // Set medallones
            if (config.medallones) {
                const medallonSelect = document.getElementById('hamburgerMedallones');
                if (medallonSelect) {
                    medallonSelect.value = config.medallones;
                    medallonSelect.dispatchEvent(new Event('change'));
                }
            }

            // Set tipo medallon
            if (config.tipo_medallon) {
                const tipoSelect = document.getElementById('hamburgerTipoMedallon');
                if (tipoSelect) {
                    tipoSelect.value = config.tipo_medallon;
                    tipoSelect.dispatchEvent(new Event('change'));
                }
            }

            // Set dips (checkboxes)
            if (config.dips && Array.isArray(config.dips)) {
                config.dips.forEach(dip => {
                    const dipCheckbox = document.querySelector(`[data-dip-value="${dip}"]`);
                    if (dipCheckbox) {
                        dipCheckbox.checked = true;
                        dipCheckbox.dispatchEvent(new Event('change'));
                    }
                });
            }

            // Set aderezos (checkboxes)
            if (config.aderezos && Array.isArray(config.aderezos)) {
                config.aderezos.forEach(aderezo => {
                    const aderezoCheckbox = document.querySelector(`[data-aderezo-value="${aderezo}"]`);
                    if (aderezoCheckbox) {
                        aderezoCheckbox.checked = true;
                        aderezoCheckbox.dispatchEvent(new Event('change'));
                    }
                });
            }

            // Set extras (checkboxes)
            if (config.extras && Array.isArray(config.extras)) {
                config.extras.forEach(extra => {
                    const extraCheckbox = document.querySelector(`[data-extra-value="${extra}"]`);
                    if (extraCheckbox) {
                        extraCheckbox.checked = true;
                        extraCheckbox.dispatchEvent(new Event('change'));
                    }
                });
            }

            // Set dip extra (checkboxes)
            if (config.dip_extra && Array.isArray(config.dip_extra)) {
                config.dip_extra.forEach(dipExtra => {
                    const dipExtraCheckbox = document.querySelector(`[data-dip-extra-value="${dipExtra}"]`);
                    if (dipExtraCheckbox) {
                        dipExtraCheckbox.checked = true;
                        dipExtraCheckbox.dispatchEvent(new Event('change'));
                    }
                });
            }

            // Update modal title to indicate editing
            const modalTitle = document.getElementById('hamburgerModalTitle');
            if (modalTitle) {
                modalTitle.textContent = 'Editar producto';
            }

            // Store the cart index on the modal for the add to cart handler
            const modalElement = document.getElementById('hamburgerModal');
            if (modalElement) {
                modalElement.dataset.editCartIndex = cartIndex;
            }
        }

        function bindViewOrderCtaHoverRecolor() {
            if (viewOrderCtaHoverRecolorBound) return;
            viewOrderCtaHoverRecolorBound = true;
            const ctaIds = ['desktopViewOrderCta', 'mobileViewOrderCta'];
            const COLOR_DEFAULT = 'primary:#ffffff,secondary:#ffffff';
            const COLOR_HOVER = 'primary:#0a2540,secondary:#0a2540';
            const MIN_INTERVAL = 5000;
            const MAX_INTERVAL = 12000;
            ctaIds.forEach(function(id) {
                const btn = document.getElementById(id);
                if (!btn) return;
                let autoAnimTimeout = null;
                let isHovering = false;
                const setColor = function(c) {
                    const li = btn.querySelector('lord-icon');
                    if (li) li.setAttribute('colors', c);
                };
                const playAnim = function() {
                    const li = btn.querySelector('lord-icon');
                    if (li && typeof li.play === 'function') {
                        try { li.play(); } catch (e) {}
                    }
                };
                const scheduleNextAutoAnim = function() {
                    if (autoAnimTimeout) clearTimeout(autoAnimTimeout);
                    const delay = Math.floor(Math.random() * (MAX_INTERVAL - MIN_INTERVAL + 1)) + MIN_INTERVAL;
                    autoAnimTimeout = setTimeout(function() {
                        if (!isHovering) playAnim();
                        scheduleNextAutoAnim();
                    }, delay);
                };
                const onEnter = function() {
                    isHovering = true;
                    setColor(COLOR_HOVER);
                    playAnim();
                };
                const onLeave = function() {
                    isHovering = false;
                    setColor(COLOR_DEFAULT);
                };
                btn.addEventListener('mouseenter', onEnter);
                btn.addEventListener('mouseleave', onLeave);
                btn.addEventListener('focus', onEnter);
                btn.addEventListener('blur', onLeave);
                btn.addEventListener('touchstart', onEnter, { passive: true });
                btn.addEventListener('touchend', onLeave);
                btn.addEventListener('touchcancel', onLeave);
                scheduleNextAutoAnim();
            });
        }

        // --- Data cache for rebuilding views ---
        let allProductsData = null; // cached at first use
        let currentCategory = window.__defaultCategorySlug || 'hamburguesas';
        let catalogCartBarListenerBound = false;
        let desktopViewOrderCtaLottieAnim = null;
        let mobileViewOrderCtaLottieAnim = null;
        let desktopViewOrderCtaLottieScriptLoading = false;
        let viewOrderCartLottieIntervalId = null;
        let remappedCartLottieTemplate = null;
        let remappedCartLottiePromise = null;

        function tcocinaLottieColorDist(a, b) {
            if (!a || !b || a.length < 3 || b.length < 3) return 99;
            return Math.abs(a[0] - b[0]) + Math.abs(a[1] - b[1]) + Math.abs(a[2] - b[2]);
        }

        function tryRemapTcocinaLottieRgbArray(arr) {
            if (!Array.isArray(arr) || arr.length < 3) return;
            if (typeof arr[0] !== 'number' || typeof arr[1] !== 'number' || typeof arr[2] !== 'number') return;
            if (arr[0] > 1.02 || arr[1] > 1.02 || arr[2] > 1.02) return;
            const rgb = [arr[0], arr[1], arr[2]];
            const primaryRef = [0.071, 0.075, 0.192];
            const secondaryRef = [0.031, 0.659, 0.541];
            const thr = 0.1;
            if (tcocinaLottieColorDist(rgb, primaryRef) < thr) {
                arr[0] = 1;
                arr[1] = 1;
                arr[2] = 1;
                if (arr.length >= 4 && typeof arr[3] === 'number') arr[3] = 1;
                return;
            }
            if (tcocinaLottieColorDist(rgb, secondaryRef) < thr) {
                arr[0] = 0;
                arr[1] = 0;
                arr[2] = 0;
                if (arr.length >= 4 && typeof arr[3] === 'number') arr[3] = 1;
            }
            // Negro y grises muy oscuros -> blanco (incluye trazos que quedaron en negro)
            const sum = arr[0] + arr[1] + arr[2];
            if (arr[0] <= 0.22 && arr[1] <= 0.22 && arr[2] <= 0.22 && sum < 0.55) {
                arr[0] = 1;
                arr[1] = 1;
                arr[2] = 1;
                if (arr.length >= 4 && typeof arr[3] === 'number') arr[3] = 1;
            }
        }

        function walkRemapTcocinaLottieColors(node) {
            if (node === null || typeof node !== 'object') return;
            if (Array.isArray(node)) {
                if (node.length >= 3 && node.length <= 4 && typeof node[0] === 'number' && typeof node[1] === 'number' &&
                    typeof node[2] === 'number') {
                    tryRemapTcocinaLottieRgbArray(node);
                }
                for (let i = 0; i < node.length; i++) {
                    walkRemapTcocinaLottieColors(node[i]);
                }
                return;
            }
            const keys = Object.keys(node);
            for (let i = 0; i < keys.length; i++) {
                walkRemapTcocinaLottieColors(node[keys[i]]);
            }
        }

        function loadLottiePlayerScript(callback) {
            const L = window.lottie || window.bodymovin;
            if (L) {
                callback();
                return;
            }
            if (desktopViewOrderCtaLottieScriptLoading) {
                window.__tcocinaLottieReady = window.__tcocinaLottieReady || [];
                window.__tcocinaLottieReady.push(callback);
                return;
            }
            desktopViewOrderCtaLottieScriptLoading = true;
            window.__tcocinaLottieReady = [callback];
            const s = document.createElement('script');
            s.src = 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js';
            s.async = true;
            s.onload = function() {
                const q = window.__tcocinaLottieReady || [];
                window.__tcocinaLottieReady = [];
                desktopViewOrderCtaLottieScriptLoading = false;
                q.forEach(function(fn) {
                    fn();
                });
            };
            document.head.appendChild(s);
        }

        function getRemappedCartLottieTemplate() {
            if (remappedCartLottieTemplate) {
                return Promise.resolve(remappedCartLottieTemplate);
            }
            if (!remappedCartLottiePromise) {
                remappedCartLottiePromise = fetch(TCOCINA_DESKTOP_CART_LOTTIE_SRC, { credentials: 'same-origin' })
                    .then(function(res) {
                        if (!res.ok) throw new Error('lottie json');
                        return res.json();
                    })
                    .then(function(data) {
                        const clone = JSON.parse(JSON.stringify(data));
                        walkRemapTcocinaLottieColors(clone);
                        remappedCartLottieTemplate = clone;
                        return clone;
                    })
                    .catch(function() {
                        remappedCartLottiePromise = null;
                        return null;
                    });
            }
            return remappedCartLottiePromise;
        }

        function playDesktopViewOrderCartLottie() {
            if (!desktopViewOrderCtaLottieAnim) return;
            try {
                desktopViewOrderCtaLottieAnim.stop();
                desktopViewOrderCtaLottieAnim.play();
            } catch (e) {
                console.warn('Lottie play desktop', e);
            }
        }

        function playMobileViewOrderCartLottie() {
            if (!mobileViewOrderCtaLottieAnim) return;
            try {
                mobileViewOrderCtaLottieAnim.stop();
                mobileViewOrderCtaLottieAnim.play();
            } catch (e) {
                console.warn('Lottie play mobile', e);
            }
        }

        function playViewOrderCartLotties() {
            playDesktopViewOrderCartLottie();
            playMobileViewOrderCartLottie();
        }

        function startViewOrderCartLottieTimer() {
            stopViewOrderCartLottieTimer();
            viewOrderCartLottieIntervalId = window.setInterval(playViewOrderCartLotties, 10000);
        }

        function stopViewOrderCartLottieTimer() {
            if (viewOrderCartLottieIntervalId !== null) {
                window.clearInterval(viewOrderCartLottieIntervalId);
                viewOrderCartLottieIntervalId = null;
            }
        }

        function resumeViewOrderCartLottiePlayback() {
            playViewOrderCartLotties();
            startViewOrderCartLottieTimer();
        }

        function mountViewOrderLottieInstance(containerId, assignAnim) {
            const el = document.getElementById(containerId);
            if (!el) return Promise.resolve(null);
            const L = window.lottie || window.bodymovin;
            if (!L) return Promise.resolve(null);

            return getRemappedCartLottieTemplate().then(function(template) {
                if (assignAnim === 'desktop' && desktopViewOrderCtaLottieAnim) return desktopViewOrderCtaLottieAnim;
                if (assignAnim === 'mobile' && mobileViewOrderCtaLottieAnim) return mobileViewOrderCtaLottieAnim;
                const mountEl = document.getElementById(containerId);
                if (!mountEl) return null;

                let anim;
                if (template) {
                    const data = JSON.parse(JSON.stringify(template));
                    anim = L.loadAnimation({
                        container: mountEl,
                        renderer: 'svg',
                        loop: false,
                        autoplay: false,
                        animationData: data
                    });
                } else {
                    anim = L.loadAnimation({
                        container: mountEl,
                        renderer: 'svg',
                        loop: false,
                        autoplay: false,
                        path: TCOCINA_DESKTOP_CART_LOTTIE_SRC
                    });
                }
                if (assignAnim === 'desktop') {
                    desktopViewOrderCtaLottieAnim = anim;
                } else {
                    mobileViewOrderCtaLottieAnim = anim;
                }
                resumeViewOrderCartLottiePlayback();
                return anim;
            });
        }

        function initDesktopViewOrderCartLottie() {
            const container = document.getElementById('desktopViewOrderCtaLottie');
            if (!container || desktopViewOrderCtaLottieAnim) return;

            const L = window.lottie || window.bodymovin;
            if (!L) {
                loadLottiePlayerScript(initDesktopViewOrderCartLottie);
                return;
            }

            mountViewOrderLottieInstance('desktopViewOrderCtaLottie', 'desktop');
        }

        function initMobileViewOrderCartLottie() {
            const container = document.getElementById('mobileViewOrderCtaLottie');
            if (!container || mobileViewOrderCtaLottieAnim) return;

            const L = window.lottie || window.bodymovin;
            if (!L) {
                loadLottiePlayerScript(initMobileViewOrderCartLottie);
                return;
            }

            mountViewOrderLottieInstance('mobileViewOrderCtaLottie', 'mobile');
        }

        function cartItemIsHamburger(item) {
            if (!item) return false;
            if (item.lineType === 'hamburger') return true;
            const c = item.configuration;
            if (!c || typeof c !== 'object') return false;
            if (Object.prototype.hasOwnProperty.call(c, 'medallones') ||
                Object.prototype.hasOwnProperty.call(c, 'tipo_medallon')) {
                return true;
            }
            return false;
        }

        function cartHasAnyHamburger(cart) {
            const list = cart || [];
            return list.some(function(item) {
                const q = parseInt(item.quantity, 10) || 0;
                if (q <= 0) return false;
                return cartItemIsHamburger(item);
            });
        }

        function syncMobileViewOrderCtaCountBadge(cart) {
            const badge = document.getElementById('mobileViewOrderCtaCountBadge');
            const cta = document.getElementById('mobileViewOrderCta');
            if (!badge || !cta) return;
            const list = cart || [];
            const total = list.reduce(function(s, i) {
                return s + (parseInt(i.quantity, 10) || 0);
            }, 0);
            if (total <= 0) {
                badge.classList.add('d-none');
                delete badge.dataset.badgeActive;
                badge.textContent = '';
                return;
            }
            badge.textContent = total > 99 ? '99+' : String(total);
            var pendingFly = false;
            try {
                pendingFly = window.__tcocinaFlyBadgePending === true;
            } catch (e) {
                pendingFly = false;
            }
            if (!pendingFly) {
                badge.dataset.badgeActive = '1';
                badge.classList.remove('d-none');
            }
        }

        function syncCatalogCartFilterBars() {
            let cart = [];
            try {
                cart = JSON.parse(localStorage.getItem('cart')) || [];
            } catch (e) {
                cart = [];
            }

            const showCta = cartHasAnyHamburger(cart);

            const barD = document.getElementById('filterContainerDesktop');
            const rowD = document.getElementById('desktopCategoryFilters');
            const ctaD = document.getElementById('desktopViewOrderCta');
            if (barD && rowD && ctaD) {
                if (showCta) {
                    barD.classList.add('desktop-filter-appbar--cart-cta');
                    rowD.classList.add('desktop-category-row--compact');
                    ctaD.classList.remove('d-none');
                    ctaD.classList.add('d-flex');
                } else {
                    barD.classList.remove('desktop-filter-appbar--cart-cta');
                    rowD.classList.remove('desktop-category-row--compact');
                    ctaD.classList.remove('d-flex');
                    ctaD.classList.add('d-none');
                }
            }

            const barM = document.getElementById('filterContainerMobile');
            const rowM = document.getElementById('mobileCategoryFilters');
            const ctaM = document.getElementById('mobileViewOrderCta');
            if (barM && rowM && ctaM) {
                if (showCta) {
                    barM.classList.add('mobile-filter-appbar--cart-cta');
                    rowM.classList.add('mobile-category-row--compact');
                    ctaM.classList.remove('d-none');
                    ctaM.classList.add('d-flex');
                } else {
                    barM.classList.remove('mobile-filter-appbar--cart-cta');
                    rowM.classList.remove('mobile-category-row--compact');
                    ctaM.classList.remove('d-flex');
                    ctaM.classList.add('d-none');
                }
            }

            syncMobileViewOrderCtaCountBadge(cart);

            if (showCta) {
                requestAnimationFrame(function() {
                    initDesktopViewOrderCartLottie();
                    initMobileViewOrderCartLottie();
                    if (desktopViewOrderCtaLottieAnim || mobileViewOrderCtaLottieAnim) {
                        resumeViewOrderCartLottiePlayback();
                    }
                });
            } else {
                stopViewOrderCartLottieTimer();
                if (desktopViewOrderCtaLottieAnim) {
                    try {
                        desktopViewOrderCtaLottieAnim.pause();
                        desktopViewOrderCtaLottieAnim.goToAndStop(0, true);
                    } catch (e) { /* ignore */ }
                }
                if (mobileViewOrderCtaLottieAnim) {
                    try {
                        mobileViewOrderCtaLottieAnim.pause();
                        mobileViewOrderCtaLottieAnim.goToAndStop(0, true);
                    } catch (e) { /* ignore */ }
                }
            }
        }

        let mobileCartSheetCatalogHooksBound = false;

        function setupMobileCartSheetCatalogHooks() {
            if (mobileCartSheetCatalogHooksBound) return;
            mobileCartSheetCatalogHooksBound = true;
        }

        function openCartOffcanvasOrPage() {
            const sheet = document.getElementById('mobileCartBottomSheet');
            if (sheet && typeof window.openMobileCartSheet === 'function') {
                window.openMobileCartSheet({ origin: 'cta' });
                return;
            }
            window.location.href = '/cart';
        }

        function bindDesktopViewOrderCtaOnce() {
            const cta = document.getElementById('desktopViewOrderCta');
            if (!cta || cta.dataset.bound === 'true') return;
            cta.dataset.bound = 'true';
            cta.addEventListener('mouseenter', function() {
                playDesktopViewOrderCartLottie();
            });
            cta.addEventListener('click', function() {
                playDesktopViewOrderCartLottie();
                openCartOffcanvasOrPage();
                // Force reset hover/focus state so lord-icon colors return to normal
                this.blur();
                this.dispatchEvent(new Event('mouseleave', { bubbles: true }));
                this.dispatchEvent(new Event('touchend', { bubbles: true }));
            });
        }

        function bindMobileViewOrderCtaOnce() {
            const cta = document.getElementById('mobileViewOrderCta');
            if (!cta || cta.dataset.bound === 'true') return;
            cta.dataset.bound = 'true';
            cta.addEventListener('mouseenter', function() {
                playMobileViewOrderCartLottie();
            });
            cta.addEventListener('click', function() {
                const sheet = document.getElementById('mobileCartBottomSheet');
                if (sheet && sheet.classList.contains('is-open') && typeof window.closeMobileCartSheet === 'function') {
                    window.closeMobileCartSheet();
                    this.blur();
                    this.dispatchEvent(new Event('mouseleave', { bubbles: true }));
                    this.dispatchEvent(new Event('touchend', { bubbles: true }));
                    return;
                }
                playMobileViewOrderCartLottie();
                openCartOffcanvasOrPage();
                // Force reset hover/focus state so lord-icon colors return to normal
                this.blur();
                this.dispatchEvent(new Event('mouseleave', { bubbles: true }));
                this.dispatchEvent(new Event('touchend', { bubbles: true }));
            });
        }

        function ensureCatalogCartBarListener() {
            if (catalogCartBarListenerBound) return;
            if (!document.getElementById('filterContainerDesktop') && !document.getElementById('filterContainerMobile')) return;
            catalogCartBarListenerBound = true;
            document.addEventListener('tcocina:cart-updated', syncCatalogCartFilterBars);
        }

        function setupDataCacheForFiltering(){
            if (allProductsData) return;
            const cols = document.querySelectorAll('#productsGrid > div');
            allProductsData = Array.from(cols).map(col => {
                const card = col.querySelector('.product-card');
                const isDivider = col.classList.contains('category-section-divider');
                const category = isDivider
                    ? (col.getAttribute('data-category') || '')
                    : (card ? (card.getAttribute('data-category') || '') : '');
                return {
                    category,
                    isDivider: isDivider,
                    columnHtml: col.outerHTML,
                    cardHtml: card ? card.outerHTML : col.innerHTML
                };
            });
        }

        function isCarouselActive(){
            const carouselEl = document.getElementById('productsCarousel');
            return carouselEl && !carouselEl.classList.contains('d-none');
        }

        function renderGridFromData(category){
            const container = document.getElementById('productsGrid');
            if (!container || !allProductsData) return;
            // For hamburguesas: no dividers needed. For acompanamientos: include dividers only when followed by cards.
            let html = '';
            if (category !== 'acompanamientos') {
                const filtered = allProductsData.filter(p => p.category === category && !p.isDivider);
                html = filtered.map(p => p.columnHtml).join('');
            } else {
                // Include dividers, but skip a divider if no card follows it in the filtered set
                const relevant = allProductsData.filter(p => p.category === category);
                for (let i = 0; i < relevant.length; i++) {
                    const item = relevant[i];
                    if (item.isDivider) {
                        // Only include if at least one non-divider follows
                        const hasCards = relevant.slice(i + 1).some(x => !x.isDivider);
                        if (hasCards) html += item.columnHtml;
                    } else {
                        html += item.columnHtml;
                    }
                }
            }
            container.innerHTML = html;
            // re-bind interactions on new nodes
            container.querySelectorAll('.customize-btn, .dip-btn').forEach(btn => {
                btn.removeAttribute('data-customize-init');
                btn.removeAttribute('data-dip-init');
            });
            initializeProductInteractions();
        }

        function renderCarouselFromData(category){
            if (!window.menuSwiper || !allProductsData) return;
            const filtered = allProductsData.filter(p => p.category === category && p.cardHtml && p.cardHtml.trim() !== '');
            const slides = filtered.map(p => `<div class="swiper-slide">${p.cardHtml}</div>`);
            try {
                window.menuSwiper.removeAllSlides();
            } catch(e) { /* ignore */ }
            if (slides.length > 0) {
                window.menuSwiper.addSlide(0, slides);
            }
            window.menuSwiper.update();
            window.menuSwiper.slideTo(0);
            // Rebind interactions for dynamically injected buttons in slides
            const wrapper = document.querySelector('#productsCarousel .swiper-wrapper');
            if (wrapper) {
                wrapper.querySelectorAll('.customize-btn, .dip-btn').forEach(btn => {
                    btn.removeAttribute('data-customize-init');
                    btn.removeAttribute('data-dip-init');
                });
                // Bindear imágenes del carrusel al lightbox con logo sello + botón
                setTimeout(function(){
                    wrapper.querySelectorAll('.product-image-container').forEach(function(container){
                        if (container.dataset.carouselLbInit === 'true') return;
                        container.dataset.carouselLbInit = 'true';
                        container.addEventListener('click', function(e){
                            e.preventDefault();
                            e.stopPropagation();
                            const img = container.querySelector('.product-image');
                            if (!img) return;
                            const actionBtn = container.closest('.product-card')
                                ? container.closest('.product-card').querySelector('.customize-btn, .add-to-cart-btn')
                                : null;
                            if (typeof window.openCarouselImageLightbox === 'function') {
                                window.openCarouselImageLightbox(img.src, img.alt || '', actionBtn);
                            }
                        });
                    });
                }, 200);
            }
            if (typeof initializeProductInteractions === 'function') {
                initializeProductInteractions();
            }
        }

        function isListActive(){
            const listEl = document.getElementById('productsList');
            return listEl && !listEl.classList.contains('d-none');
        }

        function renderListFromData(category){
            const container = document.getElementById('productsList');
            if (!container || !allProductsData) return;

            const catTitles = {
                2: {label: 'Acompañamientos', icon: 'fas fa-drumstick-bite'},
                3: {label: 'Bebidas',          icon: 'fas fa-glass-water'},
                4: {label: 'Combos',           icon: 'fas fa-layer-group'},
                5: {label: 'Postres',          icon: 'fas fa-ice-cream'},
            };

            // Parse products from cache
            const parser = new DOMParser();
            let html = '';
            let prevCatId = null;

            allProductsData.forEach(item => {
                if (item.isDivider || item.category !== category) return;

                const doc = parser.parseFromString(item.columnHtml, 'text/html');
                const card = doc.querySelector('.product-card');
                if (!card) return;

                const catId = parseInt(card.dataset.categoryId || '0') ||
                              (() => {
                                  const btn = card.querySelector('.customize-btn, .add-to-cart-btn');
                                  return btn ? parseInt(btn.dataset.productCategoryId || '0') : 0;
                              })();

                // Section divider on category change (only for acompanamientos group)
                if (category === 'acompanamientos' && catId && catId !== 1 && catId !== prevCatId && catTitles[catId]) {
                    html += `<div class="list-section-divider">
                        <i class="${catTitles[catId].icon}" style="color:var(--beach-primary,#00b4d8);font-size:0.9rem;"></i>
                        <span>${catTitles[catId].label}</span>
                        <hr>
                    </div>`;
                }
                prevCatId = catId;

                const imgEl   = card.querySelector('.product-image');
                const nameEl  = card.querySelector('.product-name');
                const descEl  = card.querySelector('.product-description');
                const priceEl = card.querySelector('.product-price');
                const customizeBtn = card.querySelector('.customize-btn');

                const imgSrc  = imgEl ? imgEl.src : '';
                const name    = nameEl ? nameEl.textContent.trim() : '';
                const desc    = descEl ? descEl.textContent.trim() : '';
                const price   = priceEl ? priceEl.textContent.trim() : '';

                const btnAttrs = customizeBtn ? Array.from(customizeBtn.attributes).map(a => `${a.name}="${a.value.replace(/"/g,'&quot;')}"`).join(' ') : '';
                const btnLabel = customizeBtn ? customizeBtn.textContent.trim() : 'Personalizar';

                const imgHtml = imgSrc
                    ? `<img class="list-product-img list-product-img-click" src="${imgSrc}" alt="${name}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=300&auto=format'; this.onerror=null;" data-img-src="${imgSrc}" data-img-name="${name}">`
                    : `<div class="list-product-img-placeholder"><i class="fas fa-image text-muted" style="font-size:1.5rem;"></i></div>`;

                const listBtnHtml = btnAttrs
                    ? `<button ${btnAttrs} class="customize-btn list-customize-btn" style="display:none;"></button>`
                    : '';

                html += `<div class="list-product-row list-product-row-clickable" ${btnAttrs ? `data-btn-attrs=""` : ''} style="cursor:pointer;">
                    <span class="list-cash-badge"><i class="fas fa-money-bill-wave" style="font-size:0.56rem;"></i> 10% OFF efectivo</span>
                    ${imgHtml}
                    <div class="list-product-body">
                        <div class="list-product-info">
                            <div class="list-product-name">${name}</div>
                            <div class="list-product-desc">${desc}</div>
                            <div class="list-product-price">${price}</div>
                        </div>
                        <div class="list-product-actions">
                            ${listBtnHtml}
                        </div>
                    </div>
                </div>`;
            });

            container.innerHTML = html;

            // Bind: click en fila -> acción del producto
            container.querySelectorAll('.list-product-row-clickable').forEach(row => {
                row.addEventListener('click', function(e) {
                    if (e.target.closest('.list-product-img-click')) return;
                    if (e.target.closest('.add-to-cart-btn')) return; // ya maneja su propio listener
                    const directBtn = this.querySelector('.add-to-cart-btn');
                    const customBtn = this.querySelector('.customize-btn');
                    if (directBtn) { directBtn.click(); return; }
                    if (customBtn) customBtn.click();
                });
            });

            // Bind: click en imagen -> lightbox propio
            container.querySelectorAll('.list-product-img-click').forEach(img => {
                img.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const row = this.closest('.list-product-row-clickable');
                    const actionBtn = row ? (row.querySelector('.add-to-cart-btn') || row.querySelector('.customize-btn')) : null;
                    openListImageLightbox(this.dataset.imgSrc, this.dataset.imgName, actionBtn);
                });
            });

            container.querySelectorAll('.customize-btn').forEach(btn => {
                btn.removeAttribute('data-customize-init');
            });
            initializeProductInteractions();
        }

        function renderByCategory(category){
            currentCategory = category;
            if (isListActive()) {
                renderListFromData(category);
            } else if (isCarouselActive()) {
                renderCarouselFromData(category);
            } else {
                renderGridFromData(category);
            }
        }

        function initializeCategoryFilters() {
            const categoryFilters = document.querySelectorAll('.category-filter');
            const filterContainer = document.getElementById('filterContainer');
            const filterContainerMobile = document.getElementById('filterContainerMobile');
            const leftIndicator = document.querySelector('.scroll-indicator-left');
            const rightIndicator = document.querySelector('.scroll-indicator-right');

            console.log('Inicializando filtros de categoría...');
            console.log('Filtros encontrados:', categoryFilters.length);
            const totalProducts = allProductsData ? allProductsData.length : document.querySelectorAll('.product-card').length;
            console.log('Productos encontrados:', totalProducts);

            // Asegurar que el filtro por defecto esté activo
            const defaultCategorySlug = window.__defaultCategorySlug || 'hamburguesas';
            const hamburguesasFilters = document.querySelectorAll('[data-category="' + defaultCategorySlug + '"]');
            if (hamburguesasFilters.length > 0) {
                console.log('Activando filtro por defecto:', defaultCategorySlug);
                // Remover active de todos los filtros
                categoryFilters.forEach(f => {
                    f.classList.remove('active');
                    f.classList.remove('btn-beach-primary');
                    f.classList.add('btn-light');
                });

                // Remover active de todos los botones mobile y desktop
                document.querySelectorAll('.mobile-filter-btn, .desktop-filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Activar todos los filtros de "Hamburguesas" (desktop y mobile)
                hamburguesasFilters.forEach(filter => {
                    if (filter.classList.contains('mobile-filter-btn') || filter.classList.contains(
                            'desktop-filter-btn')) {
                        filter.classList.add('active');
                    } else {
                        filter.classList.add('active');
                        filter.classList.remove('btn-light');
                        filter.classList.add('btn-beach-primary');
                    }
                });
                // Render default
                renderByCategory(defaultCategorySlug);
            }

            categoryFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    const selectedCategory = this.dataset.category;

                    // Update active state - ensure only one is active per category
                    categoryFilters.forEach(f => {
                        if (f.dataset.category === selectedCategory) {
                            // Activar botón seleccionado
                            if (f.classList.contains('mobile-filter-btn') || f.classList.contains(
                                    'desktop-filter-btn')) {
                                f.classList.add('active');
                            } else {
                                f.classList.add('active');
                                f.classList.remove('btn-light');
                                f.classList.add('btn-beach-primary');
                            }
                        } else {
                            // Desactivar otros botones
                            if (f.classList.contains('mobile-filter-btn') || f.classList.contains(
                                    'desktop-filter-btn')) {
                                f.classList.remove('active');
                            } else {
                                f.classList.remove('active');
                                f.classList.remove('btn-beach-primary');
                                f.classList.add('btn-light');
                            }
                        }
                    });

                    // Render depending on current view
                    renderByCategory(selectedCategory);
                    hideNoProductsMessage();
                });
            });
        }

        // Scroll indicators functionality
        function initializeScrollIndicators() {
            const filterContainer = document.getElementById('filterContainer');
            const leftIndicator = document.querySelector('.scroll-indicator-left');
            const rightIndicator = document.querySelector('.scroll-indicator-right');

            if (filterContainer && leftIndicator && rightIndicator) {
                function updateScrollIndicators() {
                    const scrollLeft = filterContainer.scrollLeft;
                    const maxScroll = filterContainer.scrollWidth - filterContainer.clientWidth;

                    if (scrollLeft <= 5) {
                        leftIndicator.classList.add('d-none');
                    } else {
                        leftIndicator.classList.remove('d-none');
                    }

                    if (scrollLeft >= maxScroll - 5) {
                        rightIndicator.classList.add('d-none');
                    } else {
                        rightIndicator.classList.remove('d-none');
                    }
                }

                // Initial check
                updateScrollIndicators();

                // Update on scroll
                filterContainer.addEventListener('scroll', updateScrollIndicators);

                // Scroll buttons
                leftIndicator.addEventListener('click', () => {
                    filterContainer.scrollBy({
                        left: -200,
                        behavior: 'smooth'
                    });
                });

                rightIndicator.addEventListener('click', () => {
                    filterContainer.scrollBy({
                        left: 200,
                        behavior: 'smooth'
                    });
                });

                // Update on resize
                window.addEventListener('resize', updateScrollIndicators);
            }
        }

        function showNoProductsMessage(category) {
            let message = document.getElementById('noProductsMessage');
            if (!message) {
                message = document.createElement('div');
                message.id = 'noProductsMessage';
                message.className = 'text-center py-5';
                message.innerHTML = `
                    <i class="fas fa-search display-1 text-beach-brown mb-4"></i>
                    <h3 class="h5 fw-medium text-beach-dark mb-2">No hay productos en esta categoría</h3>
                    <p class="text-beach-brown">Intenta con otra categoría</p>
                `;
                document.getElementById('productsGrid').appendChild(message);
            }
        }

        function hideNoProductsMessage() {
            const message = document.getElementById('noProductsMessage');
            if (message) {
                message.remove();
            }
        }

        // ===== List view image lightbox =====
        (function(){
            const lb = document.createElement('div');
            lb.id = 'listImgLightbox';
            lb.style.cssText = 'display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;flex-direction:column;gap:1rem;padding:1rem;';
            lb.innerHTML = `
                <button id="listLbClose" style="position:absolute;top:14px;right:18px;background:none;border:none;color:#fff;font-size:1.8rem;cursor:pointer;line-height:1;">&times;</button>
                <div style="position:relative;display:inline-block;">
                    <img id="listLbImg" src="" alt="" style="max-width:90vw;max-height:65vh;border-radius:12px;object-fit:contain;box-shadow:0 8px 32px rgba(0,0,0,.5);display:block;">
                    <img src="{{ asset('images/Tsinfondo.png') }}" alt="TCocina" style="position:absolute;top:10px;left:10px;width:44px;height:44px;object-fit:contain;filter:drop-shadow(0 2px 6px rgba(0,0,0,.5));opacity:.88;pointer-events:none;">
                </div>
                <button id="listLbCustomize" style="background:#0d6efd;color:#fff;border:none;border-radius:8px;padding:.65rem 2rem;font-size:1rem;font-weight:600;cursor:pointer;box-shadow:0 4px 14px rgba(13,110,253,.4);margin-top:.5rem;animation:lbBtnPulse 2s ease-in-out infinite;">Personalizar</button>
                <style>@keyframes lbBtnPulse{0%,100%{box-shadow:0 4px 14px rgba(13,110,253,.4),0 0 0 0 rgba(13,110,253,.0)}50%{box-shadow:0 4px 20px rgba(13,110,253,.6),0 0 18px 6px rgba(13,110,253,.25)}}</style>
            `;
            lb.style.display = 'none';
            document.body.appendChild(lb);

            document.getElementById('listLbClose').addEventListener('click', () => { lb.style.display = 'none'; });
            lb.addEventListener('click', function(e){ if(e.target === lb) lb.style.display = 'none'; });

            window.openListImageLightbox = function(src, name, actionBtn) {
                document.getElementById('listLbImg').src = src;
                document.getElementById('listLbImg').alt = name;
                const custBtn = document.getElementById('listLbCustomize');
                const newBtn = custBtn.cloneNode(true);
                custBtn.parentNode.replaceChild(newBtn, custBtn);
                newBtn.textContent = 'Personalizar';
                newBtn.addEventListener('click', function(){
                    lb.style.display = 'none';
                    if (actionBtn) actionBtn.click();
                });
                lb.style.display = 'flex';
            };
        })();

        // ===== Carousel view image lightbox (igual que lista: logo sello + botón) =====
        (function(){
            const lb = document.createElement('div');
            lb.id = 'carouselImgLightbox';
            lb.style.cssText = 'display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);align-items:center;justify-content:center;flex-direction:column;gap:1rem;padding:1rem;';
            lb.innerHTML = `
                <button id="carouselLbClose" style="position:absolute;top:14px;right:18px;background:none;border:none;color:#fff;font-size:1.8rem;cursor:pointer;line-height:1;">&times;</button>
                <div style="position:relative;display:inline-block;">
                    <img id="carouselLbImg" src="" alt="" style="max-width:90vw;max-height:65vh;border-radius:12px;object-fit:contain;box-shadow:0 8px 32px rgba(0,0,0,.5);display:block;">
                    <img src="{{ asset('images/Tsinfondo.png') }}" alt="TCocina" style="position:absolute;top:10px;left:10px;width:44px;height:44px;object-fit:contain;filter:drop-shadow(0 2px 6px rgba(0,0,0,.5));opacity:.88;pointer-events:none;">
                </div>
                <button id="carouselLbCustomize" style="background:#0d6efd;color:#fff;border:none;border-radius:8px;padding:.65rem 2rem;font-size:1rem;font-weight:600;cursor:pointer;box-shadow:0 4px 14px rgba(13,110,253,.4);margin-top:.5rem;animation:lbBtnPulse 2s ease-in-out infinite;">Personalizar</button>
            `;
            lb.style.display = 'none';
            document.body.appendChild(lb);

            document.getElementById('carouselLbClose').addEventListener('click', () => { lb.style.display = 'none'; });
            lb.addEventListener('click', function(e){ if(e.target === lb) lb.style.display = 'none'; });
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && lb.style.display === 'flex') lb.style.display = 'none'; });

            window.openCarouselImageLightbox = function(src, name, actionBtn) {
                document.getElementById('carouselLbImg').src = src;
                document.getElementById('carouselLbImg').alt = name;
                const custBtn = document.getElementById('carouselLbCustomize');
                const newBtn = custBtn.cloneNode(true);
                custBtn.parentNode.replaceChild(newBtn, custBtn);
                const btnText = actionBtn ? (actionBtn.textContent.trim() || 'Personalizar') : 'Personalizar';
                newBtn.textContent = btnText;
                newBtn.addEventListener('click', function(){
                    lb.style.display = 'none';
                    if (actionBtn) actionBtn.click();
                });
                lb.style.display = 'flex';
            };
        })();

        // Global modal variables
        let currentProductData = null;

        function initializeProductInteractions() {
            // Customize buttons for hamburgers (evitar doble binding)
            document.querySelectorAll('.customize-btn').forEach(button => {
                if (button.dataset.customizeInit === 'true') return;
                button.dataset.customizeInit = 'true';
                button.addEventListener('click', function() {
                    currentProductData = {
                        id: this.dataset.productId,
                        name: this.dataset.productName,
                        price: parseFloat(this.dataset.productPrice),
                        image: this.dataset.productImage,
                        description: this.dataset.productDescription || '',
                        defaultSauceId: this.dataset.productDefaultSauceId || null,
                        defaultSauceValue: this.dataset.productDefaultSauceValue || '',
                        categoryId: parseInt(this.dataset.productCategoryId) || 0
                    };

                    // Visibilidad de secciones según categoría
                    const catId = currentProductData.categoryId;
                    const isSimple      = catId === 3 || catId === 5; // Bebidas / Postres
                    const isCombo       = catId === 4;
                    const isHamburguesa = catId === 1;
                    const showDip       = catId === 1 || catId === 2;

                    const dipWrapper        = document.getElementById('hamburgerDipWrapper');
                    const dipExtraWrapper   = document.getElementById('hamburgerDipExtraWrapper');
                    const medallonWrapper   = document.getElementById('hamburgerMedallonWrapper');
                    const tipoWrapper       = document.getElementById('hamburgerTipoMedallonWrapper');
                    const aderezosWrapper   = document.getElementById('hamburgerAderezosWrapper');
                    const extrasWrapper     = document.getElementById('hamburgerExtrasWrapper');
                    const productOptions    = document.querySelector('#hamburgerModal .product-options');

                    // Bebidas / Postres: ocultar todas las opciones
                    if (productOptions) productOptions.style.display = isSimple ? 'none' : '';

                    // Para el resto, mostrar/ocultar individualmente
                    if (dipWrapper)      dipWrapper.style.display      = showDip ? '' : 'none';
                    if (dipExtraWrapper) dipExtraWrapper.style.display  = (showDip || isCombo) ? '' : 'none';
                    if (medallonWrapper)  medallonWrapper.style.display  = isHamburguesa ? '' : 'none';
                    if (tipoWrapper)      tipoWrapper.style.display      = isHamburguesa ? '' : 'none';
                    if (aderezosWrapper) aderezosWrapper.style.display   = isHamburguesa ? '' : 'none';
                    if (extrasWrapper)   extrasWrapper.style.display     = isHamburguesa ? '' : 'none';

                    // Texto del botón de agregar al carrito
                    const modalAddBtn = document.getElementById('hamburgerAddToCart');
                    const modalAddText = modalAddBtn ? modalAddBtn.querySelector('.cart-button-text') : null;
                    if (modalAddText) {
                        modalAddText.innerHTML = isSimple
                            ? '<i class="fas fa-cart-plus me-2"></i>Agregar al carrito'
                            : '<i class="fas fa-cart-plus me-2"></i>Agregar al Carrito';
                    }

                    // Update modal title
                    document.getElementById('hamburgerModalTitle').textContent = 'Personalizar';
                    
                    // Update product info in modal
                    const modalImage = document.getElementById('hamburgerModalImage');
                    if (modalImage && !modalImage.dataset.lbInit) {
                        modalImage.dataset.lbInit = 'true';
                        modalImage.addEventListener('click', function() {
                            if (this.src && !this.src.endsWith('/')) {
                                openListImageLightbox(this.src, this.alt, null);
                            }
                        });
                    }
                    const modalProductName = document.getElementById('hamburgerModalProductName');
                    const modalDescription = document.getElementById('hamburgerModalDescription');
                    const modalPrice = document.getElementById('hamburgerModalPrice');
                    
                    if (currentProductData.image) {
                        modalImage.src = '/images/' + currentProductData.image;
                        modalImage.alt = currentProductData.name;
                        modalImage.style.display = 'block';
                    } else {
                        modalImage.style.display = 'none';
                    }
                    
                    modalProductName.textContent = currentProductData.name;
                    modalDescription.textContent = currentProductData.description || '';
                    modalPrice.textContent = '$' + currentProductData.price.toFixed(2);
                    
                    // Show modal
                    const modalElement = document.getElementById('hamburgerModal');
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }
                    modal.show();

                    // Add price update listeners to all selects in the modal
                    initializeHamburgerModalPriceUpdates(currentProductData.price);
                    
                    // Filtrar aderezos según el producto (después de initializeHamburgerModalPriceUpdates)
                    // Usar setTimeout para asegurar que el DOM esté actualizado después del clonado de selects
                    setTimeout(() => {
                        filterAderezosForProduct(currentProductData.defaultSauceId, currentProductData.defaultSauceValue);
                    }, 100);
                });
            });

            // Dip buttons for accompaniments (evitar doble binding)
            document.querySelectorAll('.dip-btn').forEach(button => {
                if (button.dataset.dipInit === 'true') return;
                button.dataset.dipInit = 'true';
                button.addEventListener('click', function() {
                    currentProductData = {
                        id: this.dataset.productId,
                        name: this.dataset.productName,
                        price: parseFloat(this.dataset.productPrice),
                        image: this.dataset.productImage
                    };

                    // Update modal title
                    document.getElementById('dipModalTitle').textContent = currentProductData.name +
                        ' - Elegir Dip';

                    // Show modal
                    const modalElement = document.getElementById('dipModal');
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }
                    modal.show();
                });
            });

            // Global add to cart buttons (evitar doble binding)
            const hamburgerBtn = document.getElementById('hamburgerAddToCart');
            const dipBtn = document.getElementById('dipAddToCart');

            if (hamburgerBtn && !hamburgerBtn.dataset.modalInit) {
                hamburgerBtn.dataset.modalInit = 'true';
                hamburgerBtn.addEventListener('click', function() {
                    if (currentProductData) {
                        addProductToCartFromModal(currentProductData, 'hamburgerModal');
                    }
                });
            }

            if (dipBtn && !dipBtn.dataset.modalInit) {
                dipBtn.dataset.modalInit = 'true';
                dipBtn.addEventListener('click', function() {
                    if (currentProductData) {
                        // Activar animación del botón antes de agregar al carrito
                        if (dipBtn && dipBtn.classList.contains('cart-button')) {
                            triggerCartButtonAnimationOnElement(dipBtn);
                        }
                        addProductToCartFromModal(currentProductData, 'dipModal');
                    }
                });
            }

            // Add to cart buttons (evitar doble binding)
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                if (button.dataset.cartInit === 'true') return;
                button.dataset.cartInit = 'true';
                button.addEventListener('click', function(event) {
                    if (event && typeof event.stopImmediatePropagation === 'function') {
                        event.stopImmediatePropagation();
                    }
                    event.stopPropagation();
                    const productCard = this.closest('.product-card');
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    const productPrice = parseFloat(this.dataset.productPrice);
                    const productImage = this.dataset.productImage;
                    const catSlug = productCard ? (productCard.getAttribute('data-category') || '') : '';
                    const lineType = catSlug === 'hamburguesas' ? 'hamburger' :
                        (catSlug === 'acompanamientos' ? 'acompanamiento' : null);

                    // Get selected variants and options
                    const selectedVariants = getSelectedVariants(productCard);
                    const selectedOptions = getSelectedOptions(productCard);

                    // Activar animación del botón antes de agregar al carrito
                    const clickedButton = this;
                    if (clickedButton && clickedButton.classList.contains('cart-button')) {
                        triggerCartButtonAnimationOnElement(clickedButton);
                    }
                    
                    addToCartWithVariants(productId, productName, productPrice, productImage,
                        selectedVariants,
                        selectedOptions,
                        lineType);
                });
            });


            // Variant/option changes (evitar doble binding)
            document.querySelectorAll('.variant-select, .option-select').forEach(select => {
                if (select.dataset.priceInit === 'true') return;
                select.dataset.priceInit = 'true';
                select.addEventListener('change', function() {
                    updateProductPrice(this.closest('.product-card'));
                });
            });
        }

        function getSelectedVariants(productCard) {
            const variants = [];
            productCard.querySelectorAll('.variant-select').forEach(select => {
                const selectedOption = select.selectedOptions[0];
                if (selectedOption) {
                    variants.push({
                        name: select.dataset.variantName,
                        value: selectedOption.value,
                        priceModifier: parseFloat(selectedOption.dataset.priceModifier || 0)
                    });
                }
            });
            return variants;
        }

        function getSelectedOptions(productCard) {
            const options = [];
            productCard.querySelectorAll('.option-select').forEach(select => {
                if (select.value) {
                    const selectedOption = select.selectedOptions[0];
                    options.push({
                        name: select.dataset.optionName,
                        value: selectedOption.value,
                        priceModifier: parseFloat(selectedOption.dataset.priceModifier || 0)
                    });
                }
            });
            return options;
        }

        function updateProductPrice(productCard) {
            const basePrice = parseFloat(productCard.querySelector('.add-to-cart-btn').dataset.productPrice);
            const selectedVariants = getSelectedVariants(productCard);
            const selectedOptions = getSelectedOptions(productCard);

            let totalPrice = basePrice;
            selectedVariants.forEach(variant => totalPrice += variant.priceModifier);
            selectedOptions.forEach(option => totalPrice += option.priceModifier);

            const priceElement = productCard.querySelector('.product-price');
            if (priceElement) {
                priceElement.textContent = '$' + totalPrice.toFixed(2);
            }
        }

        // Usar la función global addToCart, pero con soporte para variants y options
        function addToCartWithVariants(productId, productName, productPrice, productImage, variants = [], options = [], lineType) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Create unique key for this product configuration
            const configKey = `${productId}_${JSON.stringify(variants)}_${JSON.stringify(options)}`;

            // Check if this exact configuration already exists
            const existingItem = cart.find(item => item.configKey === configKey);

            if (existingItem) {
                existingItem.quantity += 1;
                if (lineType && !existingItem.lineType) {
                    existingItem.lineType = lineType;
                }
            } else {
                const newItem = {
                    productId: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    variants: variants,
                    options: options,
                    quantity: 1,
                    configKey: configKey
                };
                if (lineType) {
                    newItem.lineType = lineType;
                }
                cart.push(newItem);
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            updateCartOffcanvas();
            if (typeof updateCartViews === 'function') {
                updateCartViews();
            }
            var flyAdd = typeof shouldFlyAddToCartToast === 'function' && shouldFlyAddToCartToast();
            if (flyAdd) {
                try {
                    window.__tcocinaFlyBadgePending = true;
                } catch (e) { /* ignore */ }
            }
            var flyOpts = flyAdd && typeof getFlyToastOptions === 'function'
                ? getFlyToastOptions()
                : undefined;
            showNotification('Producto agregado al carrito', 'success', flyOpts);
        }


        function initializeCartOffcanvas() {
            // Ya no necesitamos duplicar esta función, usar la global de app.js
            // Solo configurar el botón específico de esta página
            const proceedToCart = document.getElementById('proceedToCart');
            if (proceedToCart) {
                proceedToCart.addEventListener('click', function() {
                    window.location.href = '/cart';
                });
            }
        }

        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    filterProducts(query);
                });
            }
        }

        function filterProducts(query) {
            const productCards = document.querySelectorAll('.product-card');

            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productDescription = card.querySelector('.product-description').textContent.toLowerCase();

                if (productName.includes(query) || productDescription.includes(query)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }


        // Format Price function
        function formatPrice(price) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0
            }).format(price);
        }

        // Lightbox functionality - Versión ultra simplificada
        function initializeLightbox() {
            console.log('🚀 Inicializando lightbox...');

            // Crear modal si no existe
            let modal = document.getElementById('lightboxModal');
            if (!modal) {
                console.log('Creando modal...');
                modal = document.createElement('div');
                modal.id = 'lightboxModal';
                modal.className = 'lightbox-modal';
                modal.innerHTML = `
                    <div class="lightbox-content">
                        <span class="lightbox-close">&times;</span>
                        <img id="lightboxImage" class="lightbox-img" src="" alt="">
                        <div id="lightboxTitle" class="lightbox-title"></div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxTitle = document.getElementById('lightboxTitle');
            const lightboxClose = document.querySelector('.lightbox-close');

            console.log('Modal:', !!modal);
            console.log('Imagen:', !!lightboxImage);
            console.log('Título:', !!lightboxTitle);
            console.log('Cerrar:', !!lightboxClose);

            // Crear lightbox con logo sello + botón Personalizar (igual que lista/carrusel)
            const gridLb = document.createElement('div');
            gridLb.id = 'gridImgLightbox';
            gridLb.style.cssText = 'display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);align-items:center;justify-content:center;flex-direction:column;gap:1rem;padding:1rem;';
            gridLb.innerHTML = `
                <button id="gridLbClose" style="position:absolute;top:14px;right:18px;background:none;border:none;color:#fff;font-size:1.8rem;cursor:pointer;line-height:1;">&times;</button>
                <div style="position:relative;display:inline-block;">
                    <img id="gridLbImg" src="" alt="" style="max-width:90vw;max-height:65vh;border-radius:12px;object-fit:contain;box-shadow:0 8px 32px rgba(0,0,0,.5);display:block;">
                    <img src="{{ asset('images/Tsinfondo.png') }}" alt="TCocina" style="position:absolute;top:10px;left:10px;width:44px;height:44px;object-fit:contain;filter:drop-shadow(0 2px 6px rgba(0,0,0,.5));opacity:.88;pointer-events:none;">
                </div>
                <button id="gridLbCustomize" style="background:#0d6efd;color:#fff;border:none;border-radius:8px;padding:.65rem 2rem;font-size:1rem;font-weight:600;cursor:pointer;box-shadow:0 4px 14px rgba(13,110,253,.4);margin-top:.5rem;animation:lbBtnPulse 2s ease-in-out infinite;">Personalizar</button>
            `;
            gridLb.style.display = 'none';
            document.body.appendChild(gridLb);
            document.getElementById('gridLbClose').addEventListener('click', () => { gridLb.style.display = 'none'; document.body.style.overflow = 'auto'; });
            gridLb.addEventListener('click', function(e){ if(e.target === gridLb){ gridLb.style.display = 'none'; document.body.style.overflow = 'auto'; } });
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && gridLb.style.display === 'flex'){ gridLb.style.display = 'none'; document.body.style.overflow = 'auto'; } });

            function openGridLightbox(img, actionBtn) {
                document.getElementById('gridLbImg').src = img.src;
                document.getElementById('gridLbImg').alt = img.alt || '';
                const custBtn = document.getElementById('gridLbCustomize');
                const newBtn = custBtn.cloneNode(true);
                custBtn.parentNode.replaceChild(newBtn, custBtn);
                newBtn.textContent = 'Personalizar';
                newBtn.addEventListener('click', function(){
                    gridLb.style.display = 'none';
                    document.body.style.overflow = 'auto';
                    if (actionBtn) actionBtn.click();
                });
                gridLb.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            // Función legacy para compatibilidad (no usada en grid con sello)
            window.openLightbox = function(imageSrc, imageTitle) {};
            window.closeLightbox = function() { gridLb.style.display = 'none'; document.body.style.overflow = 'auto'; };

            // Listener delegado: solo grid (#productsGrid), no carrusel ni lista
            document.addEventListener('click', function(e) {
                const container = e.target.closest('.product-image-container');
                if (!container) return;
                // Solo actuar si está dentro del grid (no carrusel, ya tiene su propio lightbox)
                if (container.closest('#productsCarousel')) return;
                e.preventDefault();
                e.stopPropagation();
                const img = container.querySelector('.product-image');
                if (!img) return;
                const actionBtn = container.closest('.product-card')
                    ? container.closest('.product-card').querySelector('.customize-btn, .add-to-cart-btn')
                    : null;
                openGridLightbox(img, actionBtn);
            });

            // Navegación con teclado
            document.addEventListener('keydown', function(e) {
                if (modal && modal.classList.contains('show')) {
                    if (e.key === 'Escape') {
                        console.log('🎯 Tecla Escape presionada');
                        window.closeLightbox();
                    }
                }
            });

            // Método alternativo: agregar listeners directos a las imágenes
            setTimeout(function() {
                const productImages = document.querySelectorAll('.product-image');
                console.log('🔍 Encontradas', productImages.length, 'imágenes para listeners directos');

                productImages.forEach((img, index) => {
                    console.log('Agregando listener directo a imagen', index);
                    img.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('🎯 Click directo en imagen:', this.src);
                        window.openLightbox(this.src, this.alt || 'Imagen del producto');
                    });

                    // También agregar listener al contenedor
                    const container = img.closest('.product-image-container');
                    if (container) {
                        container.addEventListener('click', function(e) {
                            if (e.target === this || e.target.classList.contains(
                                    'lightbox-overlay')) {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log('🎯 Click en contenedor de imagen:', img.src);
                                window.openLightbox(img.src, img.alt || 'Imagen del producto');
                            }
                        });
                    }
                });
            }, 1000);

            console.log('✅ Lightbox inicializado correctamente');
        }


        function addProductToCartFromModal(productData, modalId) {
            const modal = document.getElementById(modalId);
            const configuration = getSelectedOptionsFromModal(modal);

            // Calculate total price with modifiers for both hamburger and dip modals
            let totalPrice = productData.price;
            modal.querySelectorAll('.variant-select, .option-select, #dipSelect').forEach(select => {
                if (!isSelectVisible(select)) return;
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier) {
                    const modifier = parseFloat(selectedOption.dataset.priceModifier);
                    if (!isNaN(modifier)) {
                        // El modifier puede ser negativo (ej: -2000 para "Simple"), así que simplemente sumamos
                        // (suma de negativo = resta automáticamente)
                        totalPrice += modifier;
                    }
                }
            });

            const lineType = modalId === 'hamburgerModal' ? 'hamburger' :
                (modalId === 'dipModal' ? 'acompanamiento' : null);

            // Check if editing an existing item
            const editCartIndex = modal.dataset.editCartIndex;
            if (editCartIndex !== undefined && editCartIndex !== null) {
                // Replace the item at the specified index
                const index = parseInt(editCartIndex);
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                if (index >= 0 && index < cart.length) {
                    cart[index] = {
                        productId: productData.id,
                        name: productData.name,
                        price: totalPrice,
                        image: productData.image,
                        quantity: cart[index].quantity, // Keep the original quantity
                        configuration: configuration,
                        lineType: lineType
                    };
                    localStorage.setItem('cart', JSON.stringify(cart));
                    console.log('Item replaced at index:', index);

                    // Clear the edit index from the modal
                    delete modal.dataset.editCartIndex;
                }
            } else {
                // Add new item to cart
                addToCartWithConfigurationAndPrice(productData.id, productData.name, totalPrice, productData.image,
                    configuration, lineType);
            }

            if (window.matchMedia('(max-width: 767.98px)').matches && document.getElementById('productsGrid')) {
                try {
                    window.__tcocinaFlyBadgePending = true;
                } catch (e) { /* ignore */ }
            }

            if (modalId === 'hamburgerModal') {
                triggerCartButtonAnimation('hamburgerAddToCart');
            }

            // Esperar a que termine la animación antes de cerrar el modal
            // La animación tiene 300ms (adding) + 2000ms (added) = 2300ms total
            // Cerramos después de que se vea el check (300ms + 1500ms = 1800ms)
            setTimeout(() => {
                // Close modal
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
                }

                var flyAddModal = typeof shouldFlyAddToCartToast === 'function' && shouldFlyAddToCartToast();
                if (!flyAddModal) {
                    try {
                        window.__tcocinaFlyBadgePending = false;
                    } catch (e) { /* ignore */ }
                    if (typeof syncCatalogCartFilterBars === 'function') {
                        syncCatalogCartFilterBars();
                    }
                }
                var flyOptsModal = flyAddModal && typeof getFlyToastOptions === 'function'
                    ? getFlyToastOptions()
                    : undefined;
                const message = editCartIndex !== undefined && editCartIndex !== null
                    ? 'Producto actualizado en el carrito'
                    : 'Producto agregado al carrito';
                if (typeof showNotification === 'function') {
                    showNotification(message, 'success', flyOptsModal);
                } else {
                    showToast(message, 'success');
                }
            }, 1800); // Cerrar después de 1.8 segundos para que se vea la animación completa
        }

        function isSelectVisible(select) {
            let el = select;
            while (el && el !== document.body) {
                if (el.style && el.style.display === 'none') return false;
                el = el.parentElement;
            }
            return true;
        }

        function getSelectedOptionsFromModal(modal) {
            const configuration = {};

            // Get variants (medallones, tipo de medallón) — only from visible wrappers
            modal.querySelectorAll('.variant-select').forEach(select => {
                if (!isSelectVisible(select)) return;
                const selectedOption = select.options[select.selectedIndex];
                const variantName = select.dataset.variantName.toLowerCase().replace(/\s+/g, '_');

                if (variantName === 'medallones') {
                    configuration.medallones = selectedOption.value;
                } else if (variantName === 'tipo_de_medallón') {
                    configuration.tipo_medallon = selectedOption.value;
                }
            });

            // Get options (aderezos, dips) — only from visible wrappers
            modal.querySelectorAll('.option-select:not(.extras-select):not(.dip-extra-select)').forEach(select => {
                if (!isSelectVisible(select)) return;
                const selectedOption = select.options[select.selectedIndex];
                const optionName = select.dataset.optionName.toLowerCase();

                if (optionName === 'aderezos') {
                    if (!configuration.aderezos) configuration.aderezos = [];
                    configuration.aderezos.push(selectedOption.value);
                } else if (optionName === 'dips') {
                    if (!configuration.dips) configuration.dips = [];
                    configuration.dips.push(selectedOption.value);
                } else if (optionName === 'dip') {
                    // Handle singular "dip" for hamburger modal
                    if (!configuration.dips) configuration.dips = [];
                    configuration.dips.push(selectedOption.value);
                }
            });

            // Get extras (multiple extras) — only from visible wrappers
            if (!configuration.extras) configuration.extras = [];
            modal.querySelectorAll('.extras-select').forEach(select => {
                if (!isSelectVisible(select)) return;
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value !== '') {
                    configuration.extras.push(selectedOption.value);
                }
            });

            // Get dip extras (multiple dip extras) — only from visible wrappers
            if (!configuration.dip_extra) configuration.dip_extra = [];
            modal.querySelectorAll('.dip-extra-select').forEach(select => {
                if (!isSelectVisible(select)) return;
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value !== '') {
                    configuration.dip_extra.push(selectedOption.value);
                }
            });

            // Handle dip modal specifically (single dip selection)
            const dipSelect = modal.querySelector('#dipSelect');
            if (dipSelect) {
                const selectedOption = dipSelect.options[dipSelect.selectedIndex];
                configuration.dips = [selectedOption.value];
            }

            // Remove empty arrays to avoid sending junk keys
            Object.keys(configuration).forEach(key => {
                if (Array.isArray(configuration[key]) && configuration[key].length === 0) {
                    delete configuration[key];
                }
            });

            return configuration;
        }

        // Function to initialize price updates for hamburger modal
        /**
         * Filtrar aderezos según el producto
         * Si el producto tiene un aderezo de carta, solo mostrar "Sin aderezo" y ese aderezo
         * Si no tiene, mostrar todos los aderezos disponibles
         */
        function filterAderezosForProduct(defaultSauceId, defaultSauceValue) {
            const aderezosSelect = document.getElementById('aderezosSelect');
            if (!aderezosSelect) return;
            
            // Guardar todas las opciones originales si no están guardadas
            if (!aderezosSelect.dataset.originalOptions) {
                const originalOptions = Array.from(aderezosSelect.options).map(opt => ({
                    value: opt.value,
                    text: opt.text,
                    priceModifier: opt.dataset.priceModifier || '0',
                    configId: opt.dataset.configId || ''
                }));
                aderezosSelect.dataset.originalOptions = JSON.stringify(originalOptions);
            }
            
            // Si no hay aderezo de carta, restaurar todas las opciones
            if (!defaultSauceId || !defaultSauceValue) {
                const originalOptions = JSON.parse(aderezosSelect.dataset.originalOptions);
                aderezosSelect.innerHTML = '';
                originalOptions.forEach((opt, index) => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    option.dataset.priceModifier = opt.priceModifier;
                    option.dataset.configId = opt.configId;
                    if (index === 0) option.selected = true;
                    aderezosSelect.appendChild(option);
                });
                return;
            }
            
            // Si hay aderezo de carta, mostrar solo "Sin aderezo" y el aderezo de carta
            const originalOptions = JSON.parse(aderezosSelect.dataset.originalOptions);
            aderezosSelect.innerHTML = '';
            
            // Agregar "Sin aderezo" (primera opción, que generalmente es la que tiene value vacío o "Sin aderezo")
            const sinAderezoOption = originalOptions.find(opt => 
                opt.value === '' || 
                opt.value.toLowerCase().includes('sin') || 
                opt.value.toLowerCase().includes('ninguno')
            ) || originalOptions[0];
            
            const sinAderezo = document.createElement('option');
            sinAderezo.value = sinAderezoOption.value;
            sinAderezo.textContent = sinAderezoOption.text;
            sinAderezo.dataset.priceModifier = sinAderezoOption.priceModifier;
            sinAderezo.dataset.configId = sinAderezoOption.configId;
            aderezosSelect.appendChild(sinAderezo);
            
            // Agregar el aderezo de carta (seleccionado por defecto)
            const defaultSauceOption = originalOptions.find(opt => {
                // Comparar por value (texto) o por configId (ID numérico)
                const valueMatch = opt.value && defaultSauceValue && opt.value.trim() === defaultSauceValue.trim();
                const idMatch = opt.configId && defaultSauceId && String(opt.configId) === String(defaultSauceId);
                return valueMatch || idMatch;
            });
            
            if (defaultSauceOption) {
                const defaultSauce = document.createElement('option');
                defaultSauce.value = defaultSauceOption.value;
                defaultSauce.textContent = defaultSauceOption.text;
                defaultSauce.dataset.priceModifier = defaultSauceOption.priceModifier;
                defaultSauce.dataset.configId = defaultSauceOption.configId;
                defaultSauce.selected = true;
                aderezosSelect.appendChild(defaultSauce);
            } else {
                // Si no se encuentra el aderezo en las opciones originales, crear una opción manualmente
                const defaultSauce = document.createElement('option');
                defaultSauce.value = defaultSauceValue;
                defaultSauce.textContent = defaultSauceValue;
                defaultSauce.dataset.priceModifier = '0';
                defaultSauce.dataset.configId = defaultSauceId;
                defaultSauce.selected = true;
                aderezosSelect.appendChild(defaultSauce);
            }
        }

        function initializeHamburgerModalPriceUpdates(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const priceElement = document.getElementById('hamburgerModalPrice');

            // Remove existing listeners to avoid duplicates
            modal.querySelectorAll('.variant-select, .option-select').forEach(select => {
                const newSelect = select.cloneNode(true);
                select.parentNode.replaceChild(newSelect, select);
            });

            // Add change listeners to all selects
            modal.querySelectorAll('.variant-select, .option-select').forEach(select => {
                select.addEventListener('change', function() {
                    updateHamburgerModalPrice(basePrice);
                });
            });

            // Initialize extras functionality
            initializeExtrasFunctionality(basePrice);
            
            // Initialize dip extra functionality
            initializeDipExtraFunctionality(basePrice);

            // Initial price calculation
            updateHamburgerModalPrice(basePrice);
        }

        // Function to initialize extras functionality
        function initializeExtrasFunctionality(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const container = modal.querySelector('#extras-container');
            if (!container) return;

            // Clear all added rows, keep only the first
            const rows = container.querySelectorAll('.extras-row');
            for (let i = 1; i < rows.length; i++) rows[i].remove();

            // Reset first row select and ensure its button is "+"
            const firstRow = container.querySelector('.extras-row');
            if (firstRow) {
                const sel = firstRow.querySelector('.extras-select');
                if (sel) sel.selectedIndex = 0;
                const btn = firstRow.querySelector('.add-extra-btn, .remove-extra-btn');
                if (btn && !btn.classList.contains('add-extra-btn')) {
                    btn.innerHTML = '<i class="fas fa-plus"></i>';
                    btn.classList.remove('btn-outline-danger', 'remove-extra-btn');
                    btn.classList.add('btn-outline-success', 'add-extra-btn');
                    btn.title = 'Agregar otro extra';
                }
            }

            // Event delegation — replace old handler to avoid duplicates
            if (container._extrasClickHandler) {
                container.removeEventListener('click', container._extrasClickHandler);
            }
            container._extrasClickHandler = function(e) {
                if (e.target.closest('.add-extra-btn')) {
                    addExtraRow(container, basePrice);
                } else if (e.target.closest('.remove-extra-btn')) {
                    const row = e.target.closest('.extras-row');
                    if (row) { row.remove(); updateHamburgerModalPrice(basePrice); }
                }
            };
            container.addEventListener('click', container._extrasClickHandler);

            // Change delegation for selects
            if (container._extrasChangeHandler) {
                container.removeEventListener('change', container._extrasChangeHandler);
            }
            container._extrasChangeHandler = function(e) {
                if (e.target.matches('.extras-select')) updateHamburgerModalPrice(basePrice);
            };
            container.addEventListener('change', container._extrasChangeHandler);
        }

        // Function to add a new extra row
        function addExtraRow(container, basePrice) {
            const firstRow = container.querySelector('.extras-row');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);
            newRow.style.marginTop = '4px';

            // Reset select
            const newSelect = newRow.querySelector('.extras-select');
            if (newSelect) newSelect.selectedIndex = 0;

            // Change "+" to "-"
            const btn = newRow.querySelector('.add-extra-btn');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-minus"></i>';
                btn.classList.remove('btn-outline-success', 'add-extra-btn');
                btn.classList.add('btn-outline-danger', 'remove-extra-btn');
                btn.title = 'Eliminar este extra';
            }

            container.appendChild(newRow);
            updateHamburgerModalPrice(basePrice);
        }

        // Function to initialize dip extra functionality
        function initializeDipExtraFunctionality(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const container = modal.querySelector('#dip-extra-container');
            if (!container) return;

            // Clear all added rows, keep only the first
            const rows = container.querySelectorAll('.dip-extra-row');
            for (let i = 1; i < rows.length; i++) rows[i].remove();

            // Reset first row select and ensure its button is "+"
            const firstRow = container.querySelector('.dip-extra-row');
            if (firstRow) {
                const sel = firstRow.querySelector('.dip-extra-select');
                if (sel) sel.selectedIndex = 0;
                const btn = firstRow.querySelector('.add-dip-extra-btn, .remove-dip-extra-btn');
                if (btn && !btn.classList.contains('add-dip-extra-btn')) {
                    btn.innerHTML = '<i class="fas fa-plus"></i>';
                    btn.classList.remove('btn-outline-danger', 'remove-dip-extra-btn');
                    btn.classList.add('btn-outline-success', 'add-dip-extra-btn');
                    btn.title = 'Agregar otro dip extra';
                }
            }

            // Event delegation — replace old handler to avoid duplicates
            if (container._dipExtraClickHandler) {
                container.removeEventListener('click', container._dipExtraClickHandler);
            }
            container._dipExtraClickHandler = function(e) {
                if (e.target.closest('.add-dip-extra-btn')) {
                    addDipExtraRow(container, basePrice);
                } else if (e.target.closest('.remove-dip-extra-btn')) {
                    const row = e.target.closest('.dip-extra-row');
                    if (row) { row.remove(); updateHamburgerModalPrice(basePrice); }
                }
            };
            container.addEventListener('click', container._dipExtraClickHandler);

            // Change delegation for selects
            if (container._dipExtraChangeHandler) {
                container.removeEventListener('change', container._dipExtraChangeHandler);
            }
            container._dipExtraChangeHandler = function(e) {
                if (e.target.matches('.dip-extra-select')) updateHamburgerModalPrice(basePrice);
            };
            container.addEventListener('change', container._dipExtraChangeHandler);
        }

        // Function to add a new dip extra row
        function addDipExtraRow(container, basePrice) {
            const firstRow = container.querySelector('.dip-extra-row');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);
            newRow.style.marginTop = '4px';

            // Reset select
            const newSelect = newRow.querySelector('.dip-extra-select');
            if (newSelect) newSelect.selectedIndex = 0;

            // Change "+" to "-"
            const btn = newRow.querySelector('.add-dip-extra-btn');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-minus"></i>';
                btn.classList.remove('btn-outline-success', 'add-dip-extra-btn');
                btn.classList.add('btn-outline-danger', 'remove-dip-extra-btn');
                btn.title = 'Eliminar este dip extra';
            }

            container.appendChild(newRow);
            updateHamburgerModalPrice(basePrice);
        }

        // Function to update the price in hamburger modal
        function updateHamburgerModalPrice(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const priceElement = document.getElementById('hamburgerModalPrice');

            let totalPrice = basePrice;

            // Calculate modifiers from variant selects (medallones, tipo de medallón)
            modal.querySelectorAll('.variant-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier) {
                    const modifier = parseFloat(selectedOption.dataset.priceModifier);
                    // Si el modifier es negativo o el valor es "Simple", restar (el modifier ya tiene el signo correcto)
                    // El modifier puede venir negativo desde la BD, así que simplemente sumamos (suma de negativo = resta)
                    totalPrice += modifier;
                }
            });

            // Calculate modifiers from option selects (aderezos, dips)
            modal.querySelectorAll('.option-select:not(.extras-select):not(.dip-extra-select)').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier) {
                    totalPrice += parseFloat(selectedOption.dataset.priceModifier);
                }
            });

            // Calculate modifiers from extras selects (multiple extras)
            modal.querySelectorAll('.extras-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier && selectedOption.value !== '') {
                    totalPrice += parseFloat(selectedOption.dataset.priceModifier);
                }
            });

            // Calculate modifiers from dip extra selects (multiple dip extras)
            modal.querySelectorAll('.dip-extra-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier && selectedOption.value !== '') {
                    totalPrice += parseFloat(selectedOption.dataset.priceModifier);
                }
            });

            // Update price display
            priceElement.textContent = '$' + totalPrice.toFixed(2);
        }

        // Function to add product to cart with configuration and pre-calculated price
        function addToCartWithConfigurationAndPrice(productId, productName, totalPrice, productImage, configuration, lineType) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Check if we're editing an existing item
            const editIndex = localStorage.getItem('tcocina_edit_cart_item_index');
            let isEditing = false;

            if (editIndex !== null) {
                const index = parseInt(editIndex);
                if (index >= 0 && index < cart.length) {
                    // Replace the existing item at the edit index
                    const configKey = `${productId}_${JSON.stringify(configuration)}`;
                    cart[index] = {
                        productId: productId,
                        name: productName,
                        price: totalPrice,
                        image: productImage,
                        configuration: configuration,
                        quantity: cart[index].quantity, // Keep original quantity
                        configKey: configKey,
                        categoryName: cart[index].categoryName,
                        category: cart[index].category
                    };
                    if (lineType) {
                        cart[index].lineType = lineType;
                    }
                    isEditing = true;
                    localStorage.removeItem('tcocina_edit_cart_item_index');
                }
            }

            if (!isEditing) {
                // Create unique key for this product configuration
                const configKey = `${productId}_${JSON.stringify(configuration)}`;

                // Check if this exact configuration already exists
                const existingItem = cart.find(item => item.configKey === configKey);

                if (existingItem) {
                    existingItem.quantity += 1;
                    if (lineType && !existingItem.lineType) {
                        existingItem.lineType = lineType;
                    }
                } else {
                    const newItem = {
                        productId: productId,
                        name: productName,
                        price: totalPrice,
                        image: productImage,
                        configuration: configuration,
                        quantity: 1,
                        configKey: configKey
                    };
                    if (lineType) {
                        newItem.lineType = lineType;
                    }
                    cart.push(newItem);
                }
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            updateCartOffcanvas();
            if (typeof updateCartViews === 'function') {
                updateCartViews();
            }
        }
        
        // Show Toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                console.warn('Toast container not found');
                return;
            }
            
            const toast = document.createElement('div');
            toast.className = `bg-${type === 'success' ? 'success' : 'danger'} text-white px-4 py-3 rounded-lg shadow-lg mb-2`;
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    <span>${message}</span>
                </div>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // Función para activar la animación del botón de carrito (por ID)
        function triggerCartButtonAnimation(buttonId) {
            const button = document.getElementById(buttonId);
            if (button) {
                triggerCartButtonAnimationOnElement(button);
            }
        }
        
        // Función para activar la animación del botón de carrito (por elemento)
        function triggerCartButtonAnimationOnElement(button) {
            if (button && button.classList.contains('cart-button')) {
                button.classList.remove('added');
                button.classList.add('adding');
                
                setTimeout(() => {
                    button.classList.remove('adding');
                    button.classList.add('added');
                    
                    setTimeout(() => {
                        button.classList.remove('added');
                    }, 2000);
                }, 300);
            }
        }

        // Legacy function - kept for compatibility but now just passes the base price
        function addToCartWithConfiguration(productId, productName, basePrice, productImage, configuration) {
            addToCartWithConfigurationAndPrice(productId, productName, basePrice, productImage, configuration);
        }

        // Grid / List / Carousel switcher (Swiper)
        function initializeViewSwitcher(){
            const gridBtn     = document.getElementById('gridBtn');
            const listBtn     = document.getElementById('listBtn');
            const carouselBtn = document.getElementById('carouselBtn');
            const gridEl      = document.getElementById('productsGrid');
            const listEl      = document.getElementById('productsList');
            const carouselEl  = document.getElementById('productsCarousel');
            const dotsEl      = document.getElementById('viewDots');

            if(!gridBtn || !carouselBtn || !gridEl || !carouselEl) return;

            const views = ['grid', 'list', 'carousel'];
            let currentViewIndex = 0;

            function setActiveDot(viewName) {
                if (!dotsEl) return;
                [gridBtn, listBtn, carouselBtn].forEach(btn => {
                    if (btn) btn.classList.toggle('active', btn.dataset.view === viewName);
                });
            }

            function enableGrid(){
                gridEl.classList.remove('d-none');
                if (listEl) listEl.classList.add('d-none');
                carouselEl.classList.add('d-none');
                gridBtn.classList.add('active');
                if (listBtn) listBtn.classList.remove('active');
                carouselBtn.classList.remove('active');
                setActiveDot('grid');
                currentViewIndex = 0;
                if (typeof renderByCategory === 'function') {
                    renderByCategory(currentCategory || 'hamburguesas');
                }
            }

            function enableList(){
                gridEl.classList.add('d-none');
                if (listEl) listEl.classList.remove('d-none');
                carouselEl.classList.add('d-none');
                if (listBtn) listBtn.classList.add('active');
                gridBtn.classList.remove('active');
                carouselBtn.classList.remove('active');
                setActiveDot('list');
                currentViewIndex = 1;
                if (typeof renderListFromData === 'function') {
                    renderListFromData(currentCategory || 'hamburguesas');
                }
            }

            function enableCarousel(){
                gridEl.classList.add('d-none');
                if (listEl) listEl.classList.add('d-none');
                carouselEl.classList.remove('d-none');
                carouselBtn.classList.add('active');
                gridBtn.classList.remove('active');
                if (listBtn) listBtn.classList.remove('active');
                setActiveDot('carousel');
                currentViewIndex = 2;

                // Cargar Swiper de context si no está
                function initSwiper(){
                    if(window.menuSwiper && typeof window.menuSwiper.update === 'function'){
                        window.menuSwiper.update();
                        if (typeof renderByCategory === 'function') {
                            renderByCategory(currentCategory || 'hamburguesas');
                        }
                        return;
                    }
                    if(window.Swiper){
                        window.menuSwiper = new Swiper('#productsCarousel', {
                            effect: 'coverflow',
                            grabCursor: true,
                            centeredSlides: true,
                            slidesPerView: 'auto',
                            initialSlide: 0,
                            coverflowEffect: { rotate: 20, stretch: 0, depth: 200, modifier: 1.5, slideShadows: true },
                            pagination: { el: '.swiper-pagination', clickable: true, dynamicBullets: true },
                            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                            keyboard: { enabled: true },
                            loop: true,
                            autoplay: { delay: 3500, disableOnInteraction: true }
                        });
                        if (typeof renderByCategory === 'function') {
                            renderByCategory(currentCategory || 'hamburguesas');
                        }
                    }
                }

                if(!window.Swiper){
                    var existing = document.getElementById('swiperJs');
                    if(existing){ existing.addEventListener('load', initSwiper); }
                    else {
                        var s = document.createElement('script');
                        s.id = 'swiperJs';
                        s.src = 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js';
                        s.onload = initSwiper;
                        document.body.appendChild(s);
                    }
                } else { initSwiper(); }
            }

            // Eventos click en dots
            gridBtn.addEventListener('click', enableGrid);
            if (listBtn) listBtn.addEventListener('click', enableList);
            carouselBtn.addEventListener('click', enableCarousel);

            // Iniciar en la vista configurada por el admin
            const _startView = window.__defaultCatalogView || 'grid';
            if (_startView === 'list') enableList();
            else if (_startView === 'carousel') enableCarousel();
            else enableGrid();
        }

        // Datos de lealtad para el banner del carrito
        @auth
        @if($loyaltyWallet && $loyaltySetting)
        window.__loyaltyData = {
            current: {{ (int) $loyaltyWallet->current_stickers }},
            target:  {{ (int) $loyaltySetting->target_stickers }}
        };
        @endif
        @endauth
        @guest
        @if (!($loyaltyOffline ?? false))
        window.__isGuest = true;
        window.__googleLoginUrl = '{{ route('auth.google.redirect') }}';
        @endif
        @endguest
    </script>
@endpush
