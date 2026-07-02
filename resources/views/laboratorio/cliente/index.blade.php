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

    {{-- Fondo shader WebGL (líneas plasma) --}}
    <canvas id="lab-shader-bg" class="lab-shader-bg" aria-hidden="true"></canvas>

    {{-- Marca de agua BLS gigante (decorativa, sutil) --}}
    <div class="lab-watermark" aria-hidden="true">BLS</div>

    {{-- ============ CONTENT WRAPPER ============ --}}
    <main class="lab" id="lab-main">
    <div class="lab-content-wrapper">

        {{-- ============ SIDEBAR NAV (pegado a la card) ============ --}}
        <aside class="lab-sidenav" id="lab-sidenav" aria-label="Navegación del Laboratorio">
            <div class="lab-sidenav-logo">
                <img src="{{ asset('blstudiosinfondo.png') }}" alt="BLStudio" class="lab-sidenav-logo-img">
            </div>
            <nav class="lab-sidenav-nav">
                {{-- Actualizaciones --}}
                <button class="lab-sidenav-btn active" data-nav="actualizaciones" title="Ver actualizaciones" aria-label="Ver actualizaciones">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </button>
                {{-- Guardadas --}}
                <button class="lab-sidenav-btn" data-nav="guardadas" title="Guardadas para después" aria-label="Guardadas para después">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    <span class="lab-sidenav-badge" id="snav-guardadas-cnt" style="display:none">0</span>
                </button>
                {{-- Me gusta --}}
                <button class="lab-sidenav-btn" data-nav="me-gusta" title="Me gusta" aria-label="Me gusta">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span class="lab-sidenav-badge" id="snav-megusta-cnt" style="display:none">0</span>
                </button>
                {{-- Activas --}}
                <button class="lab-sidenav-btn" data-nav="activas" title="Mis mejoras activas" aria-label="Mis mejoras activas">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    @if ($statActivas > 0)
                        <span class="lab-sidenav-badge activas-badge" id="snav-activas-cnt">{{ $statActivas }}</span>
                    @else
                        <span class="lab-sidenav-badge activas-badge" id="snav-activas-cnt" style="display:none">0</span>
                    @endif
                </button>
                {{-- Carrito --}}
                <button class="lab-sidenav-btn" data-nav="carrito" title="Carrito" aria-label="Carrito" id="snav-carrito-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span class="lab-sidenav-badge carrito-badge" id="snav-carrito-cnt" style="display:none">0</span>
                </button>
            </nav>
            {{-- Sugerir idea (footer) --}}
            <div class="lab-sidenav-footer">
                <button class="lab-sidenav-btn idea" data-nav="idea" title="Sugerir una idea" aria-label="Sugerir una idea">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="18" x2="15" y2="18"/><line x1="10" y1="22" x2="14" y2="22"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/></svg>
                </button>
            </div>
        </aside>

        <div class="lab-main-card">

            {{-- Topbar --}}
            <div class="lab-card-topbar">
                <span class="lab-card-topbar-title" id="lab-panel-title">Actualizaciones</span>
                <div class="lab-card-topbar-right">
                    <span class="lab-card-topbar-user">{{ $userName }}</span>
                    <span class="lab-card-topbar-ver">v0.1</span>
                </div>
            </div>

            {{-- ============ PANEL: ACTUALIZACIONES ============ --}}
            <div class="lab-panel active" id="panel-actualizaciones" data-panel="actualizaciones">
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

                    {{-- HERO --}}
                    <header class="lab-hero lab-hero--slim" id="lab-hero">
                        <div class="fade-up" style="animation-delay:.05s">
                            <h1>
                                Actualizaciones <span class="lab-hero-bls">blstudio</span><span class="dot">.</span>
                            </h1>
                            <p class="lead">
                                Elegí solamente lo que querés actualizar, y tenelo listo en 24 hs.
                            </p>
                        </div>
                    </header>

            {{-- ============ FEATURED ============ --}}
            @if ($featuredData)
                <section id="lab-featured">
                    <div class="lab-featured" data-improvement-id="{{ $featuredData['id'] }}">
                        <div class="fglow"></div>
                        <div class="lab-featured-grid">
                            <div class="lab-featured-body">
                                <div class="tagrow">
                                    <span class="lab-badge featured"><span class="bd" style="background:var(--lab-lime)"></span>Destacada</span>
                                    @if ($featuredData['nuevo'])
                                        <span class="lab-badge nuevo">Muestra fresca</span>
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
                                <div class="lab-card-top">
                                    <span class="cat-ic">{{ $imp['icon'] }}</span>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
                                        @if ($imp['nuevo']) <span class="lab-badge nuevo">Nuevo</span> @endif
                                        @if ($imp['activa'])
                                            <span class="lab-badge activa"><span class="bd"></span>Ya activo en tu web</span>
                                        @elseif ($imp['proceso'])
                                            <span class="lab-badge proceso"><span class="bd"></span>En camino</span>
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
                                            <button class="lab-add-btn" data-lab-action="add" data-id="{{ $imp['id'] }}" data-lab-add="{{ $imp['id'] }}"><span data-tw-copy="add">Me interesa →</span></button>
                                        </div>
                                    @endif
                                </div>

                                <div class="lab-card-signals">
                                    <button class="lab-sig-like" data-sig-id="{{ $imp['id'] }}" title="Me gusta">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                        <span>Me gusta</span>
                                    </button>
                                    <button class="lab-sig-skip" data-sig-id="{{ $imp['id'] }}" title="No me interesa ahora">
                                        No me interesa
                                    </button>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif


                </div>{{-- /.lab-inner --}}
            </div>{{-- /#panel-actualizaciones --}}

            {{-- ============ PANEL: GUARDADAS ============ --}}
            <div class="lab-panel" id="panel-guardadas" data-panel="guardadas">
                <div class="lab-panel-hero">
                    <h2>Guardadas para después</h2>
                    <p>Las mejoras que marcaste para revisar más adelante.</p>
                </div>
                <div class="lab-panel-grid-wrap">
                    <div class="lab-grid" id="guardadas-grid"></div>
                    <div class="lab-panel-empty" id="guardadas-empty">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                        <span>Todavía no guardaste ninguna mejora para después.</span>
                    </div>
                </div>
            </div>

            {{-- ============ PANEL: ME GUSTA ============ --}}
            <div class="lab-panel" id="panel-me-gusta" data-panel="me-gusta">
                <div class="lab-panel-hero">
                    <h2>Me gusta</h2>
                    <p>Las mejoras que marcaste como favoritas.</p>
                </div>
                <div class="lab-panel-grid-wrap">
                    <div class="lab-grid" id="megusta-grid"></div>
                    <div class="lab-panel-empty" id="megusta-empty">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        <span>Todavía no marcaste ninguna mejora como "Me gusta".</span>
                    </div>
                </div>
            </div>

            {{-- ============ PANEL: ACTIVAS ============ --}}
            <div class="lab-panel" id="panel-activas" data-panel="activas">
                <div class="lab-panel-hero">
                    <h2>Mis mejoras activas</h2>
                    <p>Todo lo que ya está funcionando en tu web.</p>
                </div>
                <div class="lab-panel-grid-wrap">
                    <div class="lab-grid" id="activas-grid"></div>
                    <div class="lab-panel-empty" id="activas-empty">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>Todavía no tenés mejoras activas en tu web.</span>
                    </div>
                </div>
            </div>

            {{-- ============ PANEL: CARRITO ============ --}}
            <div class="lab-panel" id="panel-carrito" data-panel="carrito">
                <div class="lab-panel-carrito-wrap">
                    <div class="lab-panel-carrito-header">
                        <h2>Carrito</h2>
                        <p>Las mejoras que elegiste. Sin compromiso — mandámelas por WhatsApp cuando quieras avanzar.</p>
                    </div>
                    <div id="panel-carrito-items"></div>
                    <div class="lab-pc-empty" id="panel-carrito-empty">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <span>El carrito está vacío. Elegí una mejora del catálogo.</span>
                    </div>
                    <div id="panel-carrito-totals" style="display:none;">
                        <div class="lab-pc-totals">
                            <div class="lab-pc-total-row"><span>Subtotal</span><span>USD <span id="panel-cart-subtotal">0</span></span></div>
                            <div class="lab-pc-total-row free" id="panel-cart-free-row" style="display:none;"><span>Primera mejora gratis</span><span>− USD <span id="panel-cart-free">0</span></span></div>
                            <div class="lab-pc-total-row total"><span>Total</span><span>USD <span id="panel-cart-total">0</span></span></div>
                        </div>
                        <div class="lab-pc-cta">
                            <button class="btn btn-primary" id="panel-cart-submit">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 0 1 12 4zm-2.7 4c-.2 0-.5 0-.7.4-.2.4-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.8 4.3 3.8 2.1.8 2.6.7 3 .6.5 0 1.4-.5 1.6-1.1.2-.6.2-1 .1-1.1l-.6-.3c-.3-.1-1.4-.7-1.6-.8-.2 0-.4-.1-.5.2l-.7.8c-.1.2-.3.2-.5.1-.6-.3-1.4-.5-2.3-1.4-.6-.6-1-1.3-1.2-1.5-.1-.3 0-.4.1-.5l.4-.5.3-.4v-.4c0-.1-.5-1.3-.7-1.8-.2-.4-.4-.4-.5-.4z"/></svg>
                                Mandármelo por WhatsApp
                            </button>
                        </div>
                        <p style="font-family:var(--lab-font-mono);font-size:11px;color:#94a3b8;text-align:center;margin:12px 0 0;">te abre tu WhatsApp con el detalle listo · no se envía nada solo</p>
                    </div>
                </div>
            </div>

            {{-- ============ PANEL: SUGERIR IDEA ============ --}}
            <div class="lab-panel" id="panel-idea" data-panel="idea">
                <div class="lab-idea-wrap">
                    <div class="lab-idea-header">
                        <h2>Sugerir una idea</h2>
                        <p>¿Se te ocurre algo que podría mejorar tu web o tu sistema de gestión? Escribímela. Si tiene sentido la desarrollo y la agrego al catálogo.</p>
                    </div>
                    <div id="lab-idea-form-panel">
                        <div class="lab-idea-box">
                            <div class="lab-idea-img-preview" id="lab-idea-img-wrap" style="position:relative;display:none;">
                                <img id="lab-idea-img-prev" src="" alt="preview" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
                                <button type="button" class="lab-idea-img-remove" id="lab-idea-img-x" title="Quitar imagen">✕</button>
                            </div>
                            <textarea id="lab-idea-text" placeholder="Describí tu idea... (podés pegar una captura de pantalla con Ctrl+V)" maxlength="1000"></textarea>
                            <div class="lab-idea-actions">
                                <label class="lab-idea-attach" title="Adjuntar imagen">
                                    <input type="file" id="lab-idea-file" accept="image/*" hidden>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                </label>
                                <span class="lab-idea-counter"><span id="lab-idea-count">0</span>/1000</span>
                                <button class="lab-idea-send" id="lab-idea-send" disabled>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    Enviar idea
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="lab-idea-sent-state" id="lab-idea-sent">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <p><strong>Idea enviada.</strong> La reviso y si tiene sentido la sumo al catálogo — te aviso.</p>
                    </div>
                </div>
            </div>

        </div>{{-- /.lab-main-card --}}
    </div>{{-- /.lab-content-wrapper --}}
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
                            <span class="pane-atom-badge">
                                <img src="{{ asset('blstudiosinfondo.png') }}" alt="BLStudio" style="width:18px;height:18px;object-fit:contain;opacity:.7;">
                                <span style="font-family:var(--lab-font-mono);font-size:9px;letter-spacing:.1em;color:rgba(255,255,255,.5);text-transform:uppercase;">BLStudio</span>
                            </span>
                        </div>
                    </div>
                    <p style="font-size: 15px; line-height: 1.6; color: var(--lab-ink-2); margin: 4px 0 18px;" data-pv-desc></p>
                    <div class="lab-diffs" data-pv-diffs style="margin-bottom: 22px;"></div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding-top: 16px; border-top: 1px solid var(--lab-line);">
                        <div>
                            <div class="lab-price big">
                                <div class="amt"><span>USD </span><span data-pv-price>0</span></div>
                            </div>
                            <div style="font-family: var(--lab-font-mono); font-size: 11px; color: var(--lab-mut); margin-top: 4px;">pago único · activo en 24 h</div>
                        </div>
                        <button class="btn btn-primary" data-pv-add><span data-tw-copy="previewAdd">Me interesa →</span></button>
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

    {{-- ============ INTRO MODAL (aparece post-preloader) ============ --}}
    <div class="lab-intro" id="lab-intro" hidden>
        <div class="lab-intro-card">

            {{-- Logo + marca --}}
            <div class="lab-intro-logo-row">
                <img src="{{ asset('blstudiosinfondo.png') }}" alt="BLStudio" class="lab-intro-logo-img">
                <span class="lab-intro-logo-sep">·</span>
                <span class="lab-intro-logo-label">Laboratorio</span>
            </div>

            <h2 class="lab-intro-title">Centro de actualizaciones</h2>

            <p class="lab-intro-desc">
                En esta sección se van a cargar todas las mejoras y actualizaciones disponibles para la web de TCocina y para el sistema de gestión. Vas a poder elegir las que te gusten y llevar tu propio ritmo de mejoras, a tu tiempo y de manera personalizada.
            </p>

            <div class="lab-intro-summary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                <span>Elegís las mejoras que quieras individualmente y las podés tener subidas a la web en <b>24 horas</b>.</span>
            </div>

            <div class="lab-intro-stats-row">
                <div class="lab-intro-stat-item">
                    <span class="n">{{ $catCounts['todas'] ?? 0 }}</span>
                    <span class="l">mejoras disponibles</span>
                </div>
                <div class="lab-intro-stat-div"></div>
                <div class="lab-intro-stat-item">
                    <span class="n">24h</span>
                    <span class="l">tiempo de activación</span>
                </div>
                <div class="lab-intro-stat-div"></div>
                <div class="lab-intro-stat-item">
                    <span class="n">$0</span>
                    <span class="l">para mirar y elegir</span>
                </div>
            </div>

            <button class="lab-intro-cta" id="lab-intro-enter">
                Entendido, avanzar →
            </button>

            @php $walletBal = isset($wallet) ? $wallet->balance_usd : 0; @endphp
            @if ($walletBal > 0)
            <div class="lab-intro-credits-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" width="14" height="14"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Tenés <b>USD {{ number_format($walletBal, 2) }}</b> en créditos disponibles
            </div>
            @endif

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
