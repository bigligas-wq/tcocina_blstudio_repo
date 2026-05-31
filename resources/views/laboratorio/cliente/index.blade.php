@extends('layouts.admin')

@section('title', 'Laboratorio · BLStudio')

@include('laboratorio._head')

@php
    $userId          = auth()->id();
    $userName        = explode(' ', auth()->user()->name)[0];
    $isDeveloper     = method_exists(auth()->user(), 'isDeveloper') ? auth()->user()->isDeveloper() : false;
    $activeIds       = $activeImprovementIds ?? [];
    $inProgressIds   = $inProgressImprovementIds ?? [];

    // Featured + catálogo: el prototipo trata featured aparte y el resto en grid.
    $allImprovements = collect();
    if (!empty($featured))     $allImprovements->push($featured);
    if (!empty($catalog))      $allImprovements = $allImprovements->merge($catalog);

    $catCounts = [
        'todas'       => $allImprovements->where('id', '!=', optional($featured)->id)->count(),
        'visual'      => $allImprovements->where('id', '!=', optional($featured)->id)->where('categoria', 'visual')->count(),
        'ux'          => $allImprovements->where('id', '!=', optional($featured)->id)->where('categoria', 'ux')->count(),
        'performance' => $allImprovements->where('id', '!=', optional($featured)->id)->where('categoria', 'performance')->count(),
        'admin'       => $allImprovements->where('id', '!=', optional($featured)->id)->where('categoria', 'admin')->count(),
    ];

    // Helper para serializar mejora al frontend.
    $serializeImp = function ($imp) use ($activeIds, $inProgressIds) {
        return [
            'id'         => $imp->id,
            'nombre'     => $imp->nombre,
            'short'      => $imp->descripcion_corta,
            'long'       => $imp->descripcion_larga,
            'cat'        => $imp->categoria,
            'icon'       => $imp->icono ?: '✨',
            'price'      => (float) $imp->precio_efectivo,
            'before_url' => $imp->imagen_antes_url,
            'after_url'  => $imp->imagen_despues_url,
            'diffs'      => is_array($imp->diferencias) ? $imp->diferencias : (json_decode($imp->diferencias ?? '[]', true) ?: []),
            'nuevo'      => (bool) $imp->es_nueva,
            'featured'   => (bool) $imp->es_destacada,
            'activa'     => in_array($imp->id, $activeIds, true),
            'proceso'    => in_array($imp->id, $inProgressIds, true),
        ];
    };
    $featuredData = $featured ? $serializeImp($featured) : null;
    $catalogData  = ($catalog ?? collect())->map($serializeImp)->values()->all();

    // Stats (los del controller ya vienen calculados).
    $statActivas = $stats['activas']     ?? 0;
    $statNuevas  = $stats['disponibles'] ?? count($catalogData) + ($featuredData ? 1 : 0);
@endphp

@section('content')
<div class="lab-app">

    {{-- Marca de agua BLS gigante (decorativa, sutil) --}}
    <div class="lab-watermark" aria-hidden="true">BLS</div>

    <main class="lab lab-scroll" id="lab-main">

        {{-- ============ HEADER BRANDEADO BLSTUDIO ============ --}}
        <header class="lab-header" id="lab-header">
            <div class="lab-header-bg" aria-hidden="true"></div>
            <div class="lab-header-inner">
                <div class="lab-header-brand" role="img" aria-label="Laboratorio BLStudio">
                    <span class="lab-header-atom" data-lab-lottie="atom" data-recolor="mut" aria-hidden="true"></span>
                    <span class="lab-header-mark">
                        <img src="{{ asset('blstudiosinfondo.png') }}" alt="BLStudio" class="lab-header-logo">
                        <span class="lab-header-sep">·</span>
                        <span class="lab-header-name">LABORATORIO</span>
                    </span>
                </div>

                <div class="lab-header-meta">
                    <span class="lab-header-crumb">
                        <span>Panel</span><span class="sl">/</span><b>Laboratorio</b>
                    </span>
                    <span class="lab-header-build" title="Versión del laboratorio">
                        <span class="bd"></span>
                        <span>v0.1 · estable</span>
                    </span>
                </div>
            </div>
            <div class="lab-header-scanline" aria-hidden="true"></div>
        </header>

        <div class="lab-inner">

            @if (session('success'))
                <div class="lab-alert success">{{ session('success') }}</div>
            @endif

            @if (empty($labSettings['whatsapp']) && $isDeveloper)
                <div class="lab-alert warning">
                    Falta configurar el WhatsApp del developer en
                    <a href="{{ route('laboratorio.admin.config') }}" style="color: var(--lab-amber); text-decoration: underline;">Configuración</a>.
                </div>
            @endif

            {{-- ============ HERO ============ --}}
            <header class="lab-hero" id="lab-hero">
                <div class="fade-up" style="animation-delay:.05s">
                    <div class="lab-eyebrow lab-hero-eyebrow">
                        <span class="ln"></span>
                        <span>SESIÓN <span data-tw-greet>{{ $userName }}</span> · <b>conexión activa</b></span>
                    </div>
                    <h1>
                        Bienvenido al<br>
                        <em>Laboratorio <span class="lab-hero-bls">BLStudio</span></em><span class="dot">.</span>
                    </h1>
                    <p class="lead">
                        Donde las ideas se convierten en mejoras para tu negocio.
                        <span class="soft">Elegís la que te cierra, yo la activo.</span>
                    </p>
                    <div class="lab-hero-actions">
                        <button type="button" class="btn btn-primary lab-hero-idea-btn" id="lab-hero-idea-btn">
                            <span class="lab-hero-idea-ic" aria-hidden="true">⚗</span>
                            Tirar una idea nueva
                        </button>
                        <a href="{{ route('laboratorio.historial') }}" class="btn btn-ghost">Ver historial</a>
                    </div>
                </div>

                <div class="lab-hero-vis fade-up" style="animation-delay:.2s" aria-hidden="true">
                    <div class="lab-factory-stage">
                        <div class="lab-factory-glow"></div>
                        <div class="lab-factory-ring"></div>
                        <div class="lab-factory-ring r2"></div>
                        <div class="lab-factory-host" data-lab-lottie="factory" data-recolor="ink"></div>
                    </div>
                </div>
            </header>

            {{-- ============ FEATURED ============ --}}
            @if ($featuredData)
                <section id="lab-featured">
                    <div class="lab-section-head">
                        <h2>Muestra destacada<span class="lab-section-glyph">⚛</span></h2>
                        <span class="sub">recién salida del reactor</span>
                    </div>
                    <div class="lab-featured" data-improvement-id="{{ $featuredData['id'] }}">
                        <div class="fglow"></div>
                        <span class="lab-code lab-code-featured">LAB-{{ str_pad($featuredData['id'], 3, '0', STR_PAD_LEFT) }} · MUESTRA DESTACADA</span>
                        <div class="lab-featured-grid">
                            <div class="lab-featured-body">
                                <div class="tagrow">
                                    <span class="lab-badge featured"><span class="bd" style="background:var(--lab-lime)"></span>Destacada</span>
                                    @if ($featuredData['nuevo'])
                                        <span class="lab-badge nuevo">🧪 Muestra fresca</span>
                                    @endif
                                    <span class="lab-badge cat">{{ ucfirst($featuredData['cat']) }}</span>
                                </div>
                                <h3>{{ $featuredData['nombre'] }}</h3>
                                <p class="desc">{{ $featuredData['long'] ?: $featuredData['short'] }}</p>
                                @if (count($featuredData['diffs']))
                                    <div class="lab-diffs">
                                        @foreach ($featuredData['diffs'] as $d)
                                            <div class="lab-diff">
                                                <span class="d" style="background: {{ $d['color'] ?? '#3ecf8e' }}"></span>
                                                {{ $d['texto'] ?? $d['t'] ?? '' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="lab-featured-foot">
                                    <div class="lab-price big">
                                        <div class="amt"><span>USD </span>{{ number_format($featuredData['price'], 0) }}</div>
                                        <div class="meta">pago único · online en 24 h</div>
                                    </div>
                                    @if ($featuredData['activa'])
                                        <span class="lab-state-line activa"><span class="lab-state-dot"></span>REACTOR ACTIVO · funcionando en tu web</span>
                                    @elseif ($featuredData['proceso'])
                                        <span class="lab-state-line proceso"><span class="lab-state-dot"></span>EN SÍNTESIS · la estoy cocinando</span>
                                    @else
                                        <button class="btn btn-ghost" data-lab-action="preview" data-id="{{ $featuredData['id'] }}">Ver cómo queda</button>
                                        <button class="btn btn-primary" data-lab-action="add" data-id="{{ $featuredData['id'] }}" data-lab-add="{{ $featuredData['id'] }}">+ <span data-tw-copy="featuredAdd">Sumar a mi web</span></button>
                                    @endif
                                </div>
                                <div class="reaction-bar" style="max-width: 420px;">
                                    <div data-lab-stars="{{ $featuredData['id'] }}"></div>
                                </div>
                            </div>
                            @if ($featuredData['after_url'])
                                <div class="lab-featured-vis">
                                    <div class="lab-fv-frame">
                                        <div class="lab-fv-after" style="background-image: url('{{ $featuredData['after_url'] }}')"></div>
                                        <span class="lab-fv-tag">después</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            {{-- ============ FREE BANNER ============ --}}
            <div class="lab-free-banner fade-up" data-tw-free>
                <span class="fb-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/></svg>
                </span>
                <div class="fb-txt">
                    <b>La primera corre por mi cuenta</b> — elegí cualquier mejora y la estrenas gratis.
                </div>
                <span class="fb-pill"><span class="bd"></span>primera gratis</span>
            </div>

            {{-- ============ TABS + GRID ============ --}}
            <div class="lab-section-head" style="margin-bottom: 6px;" id="lab-tabs">
                <h2>Banco de muestras<span class="lab-section-glyph">🧪</span></h2>
                <span class="sub">{{ $catCounts['todas'] }} disponibles en el laboratorio</span>
            </div>

            <div class="lab-tabs-row">
                <div class="lab-tabs">
                    <button class="lab-tab active" data-lab-tab="todas">Todas <span class="cnt">{{ $catCounts['todas'] }}</span></button>
                    <button class="lab-tab" data-lab-tab="visual">Visual <span class="cnt">{{ $catCounts['visual'] }}</span></button>
                    <button class="lab-tab" data-lab-tab="ux">UX <span class="cnt">{{ $catCounts['ux'] }}</span></button>
                    <button class="lab-tab" data-lab-tab="performance">Performance <span class="cnt">{{ $catCounts['performance'] }}</span></button>
                    <button class="lab-tab" data-lab-tab="admin">Admin <span class="cnt">{{ $catCounts['admin'] }}</span></button>
                </div>
            </div>

            @if (count($catalogData) === 0 && !$featuredData)
                <div class="lab-card" style="text-align:center; padding: 40px;">
                    <p style="color: var(--lab-mut);">Todavía no hay mejoras publicadas. Pronto vamos a tener novedades.</p>
                </div>
            @else
                <div class="lab-grid" id="lab-grid">
                    @foreach ($catalogData as $i => $imp)
                        <div class="fade-up" style="animation-delay: {{ 0.04 * $i }}s" data-lab-cat="{{ $imp['cat'] }}">
                            <article class="lab-card cat-{{ $imp['cat'] }}" data-improvement-id="{{ $imp['id'] }}">
                                <div class="meniscus"></div>
                                <div class="cbubbles">
                                    @for ($b = 0; $b < 8; $b++)
                                        <i style="left: {{ 12 + $b * 11 }}%; width: {{ 6 + ($b % 4) * 3 }}px; height: {{ 6 + ($b % 4) * 3 }}px; animation-delay: {{ ($b * 0.18) }}s; animation-duration: {{ 2.2 + ($b * 0.12) }}s;"></i>
                                    @endfor
                                </div>
                                <span class="lab-code">LAB-{{ str_pad($imp['id'], 3, '0', STR_PAD_LEFT) }}</span>
                                <div class="lab-card-top">
                                    <span class="cat-ic">{{ $imp['icon'] }}</span>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
                                        @if ($imp['nuevo']) <span class="lab-badge nuevo">🧪 Fresca</span> @endif
                                        @if ($imp['activa'])
                                            <span class="lab-badge activa"><span class="bd"></span>Reactor activo</span>
                                        @elseif ($imp['proceso'])
                                            <span class="lab-badge proceso"><span class="bd"></span>En síntesis</span>
                                        @else
                                            <span class="lab-badge cat">{{ ucfirst($imp['cat']) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <h4>{{ $imp['nombre'] }}</h4>
                                <p class="cdesc">{{ $imp['short'] }}</p>

                                <div class="lab-card-foot">
                                    @if ($imp['activa'])
                                        <div class="lab-state-line activa"><span class="lab-state-dot"></span>REACTOR ACTIVO</div>
                                    @elseif ($imp['proceso'])
                                        <div class="lab-state-line proceso"><span class="lab-state-dot"></span>EN SÍNTESIS</div>
                                    @else
                                        <div class="lab-price"><div class="amt"><span>USD </span>{{ number_format($imp['price'], 0) }}</div></div>
                                        <div class="lab-card-actions">
                                            <button class="lab-icon-btn" title="Ver cómo queda" data-lab-action="preview" data-id="{{ $imp['id'] }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </button>
                                            <button class="lab-add-btn" data-lab-action="add" data-id="{{ $imp['id'] }}" data-lab-add="{{ $imp['id'] }}">+ <span data-tw-copy="add">Sumar</span></button>
                                        </div>
                                    @endif
                                </div>

                                <div class="reaction-bar">
                                    <div data-lab-stars="{{ $imp['id'] }}"></div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>{{-- /.lab-inner --}}

    </main>{{-- /.lab --}}

    {{-- Cart dock (montado por JS cuando hay items) --}}
    <div id="lab-cart-dock"></div>

        {{-- ============ MODALES (escondidos hasta .open) ============ --}}

        {{-- Preview --}}
        <div id="lab-modal-preview" class="lab-scrim">
            <div class="lab-modal lab-scroll">
                <div class="lab-modal-head">
                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                        <span class="cat-ic" data-pv-icon style="width:42px; height:42px;"></span>
                        <div>
                            <h3 style="font-family: var(--lab-font-display); font-weight: 800; font-size: 22px; margin:0; letter-spacing:-.01em;" data-pv-name></h3>
                            <span class="lab-badge cat" style="margin-top:6px;" data-pv-cat></span>
                        </div>
                    </div>
                    <button class="lab-modal-x" data-lab-action="close-modal" data-modal="lab-modal-preview">✕</button>
                </div>
                <div class="lab-modal-body">
                    <div class="lab-ba-toggle">
                        <button data-pv-view="antes">Antes</button>
                        <button class="on" data-pv-view="despues">Después</button>
                    </div>
                    <div class="lab-ba">
                        <div class="pane" data-pv-before style="opacity: 0;">
                            <span class="ptag">como está hoy</span>
                        </div>
                        <div class="pane" data-pv-after style="opacity: 1;">
                            <span class="ptag" style="background: rgba(255,76,12,.85); border-color: transparent;">con la mejora</span>
                        </div>
                    </div>
                    <p style="font-size: 15px; line-height: 1.6; color: var(--lab-ink-2); margin: 4px 0 18px;" data-pv-desc></p>
                    <div class="lab-diffs" data-pv-diffs style="margin-bottom: 22px;"></div>
                    <div style="text-align:center; margin: 4px 0 20px; padding: 18px 0; border-top: 1px solid var(--lab-line); border-bottom: 1px solid var(--lab-line);">
                        <div style="font-family: var(--lab-font-mono); font-size: 12px; color: var(--lab-mut); margin-bottom: 12px;">¿qué tan a tu medida la ves?</div>
                        <div data-pv-stars></div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <div class="lab-price big">
                            <div class="amt"><span>USD </span><span data-pv-price>0</span></div>
                            <div class="meta">pago único · online en 24 h</div>
                        </div>
                        <button class="btn btn-primary" data-pv-add><span data-tw-copy="previewAdd">Esta la sumo</span> →</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order (carrito + checkout) --}}
        <div id="lab-modal-order" class="lab-scrim">
            <div class="lab-modal lab-scroll" style="width: min(620px, 100%);">
                <div class="lab-modal-head">
                    <div>
                        <h3 style="font-family: var(--lab-font-display); font-weight: 800; font-size: 22px; margin: 0;" data-tw-copy="pedido">Lo que vas a sumar</h3>
                        <p style="color: var(--lab-mut); font-size: 13.5px; margin: 6px 0 0;">Sin compromiso. Lo mirás, y si querés avanzás vos cuando quieras.</p>
                    </div>
                    <button class="lab-modal-x" data-lab-action="close-modal" data-modal="lab-modal-order">✕</button>
                </div>
                <div class="lab-modal-body">
                    <div id="lab-order-items" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px;"></div>

                    <div style="padding: 4px 4px 0;">
                        <div style="display:flex; justify-content:space-between; font-family: var(--lab-font-mono); font-size: 13px; color: var(--lab-mut); margin-bottom: 8px;">
                            <span>Subtotal</span><span>USD <span id="lab-order-subtotal">0</span></span>
                        </div>
                        <div id="lab-order-free-row" style="display:none; justify-content:space-between; font-family: var(--lab-font-mono); font-size: 13px; color: var(--lab-lime-soft); margin-bottom: 8px;">
                            <span>Primera mejora gratis</span><span>− USD <span id="lab-order-free">0</span></span>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:space-between; padding: 16px 4px; border-top: 1px solid var(--lab-line);">
                        <span style="font-family: var(--lab-font-mono); font-size: 13px; color: var(--lab-mut);">Total</span>
                        <div class="lab-price big"><div class="amt"><span>USD </span><span id="lab-order-total">0</span></div></div>
                    </div>

                    <div style="display:flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
                        <button class="btn btn-primary" id="lab-order-submit" style="flex:1; justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 0 1 12 4zm-2.7 4c-.2 0-.5 0-.7.4-.2.4-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.8 4.3 3.8 2.1.8 2.6.7 3 .6.5 0 1.4-.5 1.6-1.1.2-.6.2-1 .1-1.1l-.6-.3c-.3-.1-1.4-.7-1.6-.8-.2 0-.4-.1-.5.2l-.7.8c-.1.2-.3.2-.5.1-.6-.3-1.4-.5-2.3-1.4-.6-.6-1-1.3-1.2-1.5-.1-.3 0-.4.1-.5l.4-.5.3-.4v-.4c0-.1-.5-1.3-.7-1.8-.2-.4-.4-.4-.5-.4z"/></svg>
                            <span data-tw-copy="whats">Pasármelo por WhatsApp</span>
                        </button>
                        <button class="btn btn-ghost" data-lab-action="close-modal" data-modal="lab-modal-order">Sigo mirando</button>
                    </div>
                    <p style="font-family: var(--lab-font-mono); font-size: 11.5px; color: var(--lab-mut-2); text-align: center; margin: 14px 0 0;">
                        te abre tu WhatsApp con el detalle listo — no se envía nada solo
                    </p>
                </div>
            </div>
        </div>

        {{-- Signals (solo developer) --}}
        @if ($isDeveloper)
            <div id="lab-modal-signals" class="lab-scrim">
                <div class="lab-modal lab-scroll" style="width: min(640px, 100%);">
                    <div class="lab-modal-head">
                        <div>
                            <span class="dev-tag"><span class="bd"></span>solo vos · no lo ve el cliente</span>
                            <h3 style="font-family: var(--lab-font-display); font-weight: 800; font-size: 22px; margin: 8px 0 0;">Señales de interés</h3>
                            <p style="color: var(--lab-mut); font-size: 13.5px; margin: 6px 0 0;">Qué le llama más la atención al cliente, para afinar las próximas propuestas.</p>
                        </div>
                        <button class="lab-modal-x" data-lab-action="close-modal" data-modal="lab-modal-signals">✕</button>
                    </div>
                    <div class="lab-modal-body">
                        <div class="sig-stats" id="lab-sig-stats"></div>
                        <div class="sig-list" id="lab-sig-list"></div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--lab-line);">
                            <span style="font-family: var(--lab-font-mono); font-size: 11px; color: var(--lab-mut-2);">se guarda en este navegador</span>
                            <button class="btn btn-soft" id="lab-sig-clear" style="font-size: 12.5px; padding: 8px 10px;">Borrar señales</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    {{-- Loader inicial · brandeado BLStudio --}}
    <div class="lab-loader" id="lab-loader">
        {{-- BLS gigante de fondo (marca de agua del preloader) --}}
        <div class="lab-loader-watermark" aria-hidden="true">BLS</div>

        {{-- Esquinas estilo bracket terminal --}}
        <span class="lab-loader-bracket tl" aria-hidden="true"></span>
        <span class="lab-loader-bracket tr" aria-hidden="true"></span>
        <span class="lab-loader-bracket bl" aria-hidden="true"></span>
        <span class="lab-loader-bracket br" aria-hidden="true"></span>

        <div class="lab-loader-inner">
            <div class="lglow"></div>

            {{-- Átomo + logo superpuestos (átomo orbita alrededor del logo BLS) --}}
            <div class="lab-loader-mark">
                <div class="lab-loader-atom" data-lab-lottie="atom" data-recolor="mut" data-loop="1"></div>
                <img class="lab-loader-logo" src="{{ asset('blstudiosinfondo.png') }}" alt="BLStudio">
            </div>

            {{-- Wordmark grande --}}
            <div class="lab-loader-wordmark">
                <span class="wm-pre">BLSTUDIO</span>
                <span class="wm-sep">·</span>
                <span class="wm-main">LABORATORIO</span>
            </div>

            {{-- Subtítulo con typewriter --}}
            <div class="lab-loader-sub">
                <span class="ld-prompt">&gt;</span>
                <span class="ld-typewriter" id="lab-loader-tw" data-tw-text="conectando con el laboratorio | destilando ideas para tu web&#8230;"></span>
                <span class="ld-cursor">▌</span>
            </div>

            {{-- Barra de progreso --}}
            <div class="lbar"><i></i></div>

            {{-- Meta del build (esquina abajo) --}}
            <div class="lab-loader-meta">
                <span>v0.1 · build estable</span>
                <span class="sep">·</span>
                <span>lab.blstudio.io</span>
                <span class="dot pulse" aria-hidden="true"></span>
            </div>
        </div>
    </div>

    {{-- Botón flotante de ideas --}}
    <div class="lab-float-idea" id="lab-float-idea">
        {{-- Popup (viñeta de diálogo) --}}
        <div class="lab-float-popup" id="lab-float-popup" hidden>
            <div class="lab-float-popup-body">
                <div class="lab-float-popup-hd">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>¿Tenés una idea de mejora?</span>
                    <button class="lab-float-popup-x" id="lab-float-popup-x" aria-label="Cerrar">✕</button>
                </div>
                {{-- Mismo widget que el chat bar del laboratorio --}}
                <div id="lab-float-popup-form">
                    <div class="lab-chat-inner lab-float-chat-inner">
                        <div class="lab-chat-preview" id="lab-float-img-wrap" style="display:none;">
                            <img id="lab-float-img-prev" src="" alt="preview">
                            <button type="button" class="lab-chat-preview-x" id="lab-float-img-x" title="Quitar">✕</button>
                        </div>
                        <textarea id="lab-float-text" placeholder="¿Se te ocurre algo? Tirámela… (podés adjuntar una imagen)" maxlength="1000"></textarea>
                        <div class="lab-chat-actions">
                            <label class="lab-chat-attach" title="Adjuntar imagen">
                                <input type="file" id="lab-float-file" accept="image/*" hidden>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            </label>
                            <span class="lab-chat-count"><span id="lab-float-count">0</span>/1000</span>
                            <button class="lab-chat-send" id="lab-float-send" disabled aria-label="Enviar idea">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="lab-float-sent" id="lab-float-popup-sent" style="display:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Anotada. Si tiene sentido, la vas a ver acá con su precio.</span>
                </div>
            </div>
            {{-- Flecha apuntando al botón --}}
            <div class="lab-float-popup-arrow"></div>
        </div>

        {{-- El botón: átomo animado + hint de burbuja --}}
        <div class="lab-float-row">
            <div class="lab-float-hint" id="lab-float-hint">¿Tenés alguna idea de mejora?</div>
            <button class="lab-float-trigger" id="lab-float-trigger" aria-label="Compartir idea de mejora">
                <div class="lab-float-atom-wrap">
                    <svg class="lab-float-atom-svg" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                        <circle cx="24" cy="24" r="3.5" fill="var(--lab-lime)" opacity=".9"/>
                        <ellipse cx="24" cy="24" rx="20" ry="7" stroke="var(--lab-lime)" stroke-width="1.3" opacity=".75"/>
                        <ellipse cx="24" cy="24" rx="20" ry="7" stroke="var(--lab-lime)" stroke-width="1.3" opacity=".55"/>
                        <ellipse cx="24" cy="24" rx="20" ry="7" stroke="var(--lab-lime)" stroke-width="1.3" opacity=".55"/>
                    </svg>
                </div>
            </button>
        </div>
    </div>

        {{-- TweaksPanel (solo developer) — fixed al viewport, FUERA de la notebook --}}
        @if ($isDeveloper)
            <button class="lab-tweaks-fab" id="lab-tweaks-fab" title="Ajustes dev">⚙</button>
            <aside class="lab-tweaks-panel" id="lab-tweaks-panel">
                <div class="twk-hd">
                    <span>Ajustes dev · solo vos</span>
                    <button class="x" data-lab-action="close-tweaks">✕</button>
                </div>
                <div class="twk-body">
                    <div class="twk-section">Identidad</div>
                    <div class="twk-row">
                        <label>Saludar a</label>
                        <input type="text" data-twk="greet" value="{{ $userName }}">
                    </div>
                    <div class="twk-row">
                        <label>Color de acción</label>
                        <div class="swatches" data-twk-swatches="accion">
                            <button data-val="#ff4c0c" style="background:#ff4c0c"></button>
                            <button data-val="#8bc34a" style="background:#8bc34a"></button>
                            <button data-val="#3ecf8e" style="background:#3ecf8e"></button>
                            <button data-val="#38b6ff" style="background:#38b6ff"></button>
                            <button data-val="#f5a623" style="background:#f5a623"></button>
                        </div>
                    </div>
                    <div class="twk-section">Atmósfera</div>
                    <div class="twk-row">
                        <label>Burbujeo</label>
                        <div class="seg" data-twk-seg="burbujeo">
                            <button data-val="off">off</button>
                            <button data-val="mix">mix</button>
                            <button data-val="vivo">vivo</button>
                        </div>
                    </div>
                    <div class="twk-row">
                        <label>Tono del copy</label>
                        <div class="seg" data-twk-seg="tono">
                            <button data-val="tranquilo">tranquilo</button>
                            <button data-val="entusiasta">entusiasta</button>
                        </div>
                    </div>
                    <div class="twk-row toggle-row">
                        <label>Primera mejora gratis</label>
                        <button class="toggle" data-twk-toggle="regalo"><i></i></button>
                    </div>
                    <div class="twk-section">Para vos (dev)</div>
                    <button class="twk-btn" data-lab-action="open-signals">Ver señales de interés</button>
                </div>
            </aside>
        @endif
</div>{{-- /.lab-app --}}

@push('scripts')
<script>
    window.LAB_BOOT = {
        improvements: @json(array_filter(array_merge([$featuredData], $catalogData))),
        featuredId:   {{ $featuredData['id'] ?? 'null' }},
        urls: {
            crearOrden:        @json(route('laboratorio.orden.store')),
            markWhatsappSent:  @json(route('laboratorio.orden.whatsapp', ['labOrder' => ':number'])),
            idea:              @json(route('laboratorio.idea')),
        },
        whatsapp: @json($labSettings['whatsapp'] ?? ''),
        isDeveloper: @json($isDeveloper),
        userName:    @json($userName),
        csrf:        @json(csrf_token()),
    };
</script>
<script src="{{ asset('js/laboratorio.js') }}?v={{ filemtime(public_path('js/laboratorio.js')) }}"></script>
@endpush
@endsection
