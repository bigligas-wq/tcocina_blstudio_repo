@extends('layouts.app')

@section('title', 'Seguimiento — Pedido #' . $order->order_number . ' — TCocina')

@section('content')

@php
    $isDelivery = $order->address_id !== null;

    if ($isDelivery) {
        $steps = [
            'pending'    => ['label' => 'Pedido enviado',    'sub' => 'Tu pedido llegó al local',         'icon' => 'fa-paper-plane'],
            'confirmed'  => ['label' => 'Confirmado',        'sub' => 'El local aceptó tu pedido',        'icon' => 'fa-check-circle'],
            'preparing'  => ['label' => 'En la plancha',     'sub' => '¡Tu burger está en preparación!',  'icon' => 'fa-fire'],
            'ready'      => ['label' => 'Listo para enviar', 'sub' => 'Tu pedido está listo',             'icon' => 'fa-star'],
            'on_the_way' => ['label' => 'En camino',         'sub' => '¡Tu pedido viene hacia vos!',      'icon' => 'fa-motorcycle'],
            'delivered'  => ['label' => 'Entregado',         'sub' => '¡Llegó tu pedido!',               'icon' => 'fa-house'],
        ];
        $statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'on_the_way', 'delivered'];
    } else {
        $steps = [
            'pending'   => ['label' => 'Pedido enviado',       'sub' => 'Tu pedido llegó al local',         'icon' => 'fa-paper-plane'],
            'confirmed' => ['label' => 'Confirmado',           'sub' => 'El local aceptó tu pedido',        'icon' => 'fa-check-circle'],
            'preparing' => ['label' => 'En la plancha',        'sub' => '¡Tu burger está en preparación!',  'icon' => 'fa-fire'],
            'ready'     => ['label' => 'Listo para retirar',   'sub' => 'Pasá a buscarlo al local',         'icon' => 'fa-store'],
            'delivered' => ['label' => 'Retirado',             'sub' => '¡Que lo disfrutes!',              'icon' => 'fa-check-circle'],
        ];
        $statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'delivered'];
    }

    $currentIdx = array_search($order->status, $statusOrder);
    if ($currentIdx === false) $currentIdx = 0;

    $timestamps = [
        'pending'    => $order->created_at,
        'confirmed'  => $order->confirmed_at,
        'preparing'  => $order->preparing_at,
        'ready'      => $order->ready_at,
        'on_the_way' => $order->out_for_delivery_at,
        'delivered'  => $order->delivered_at,
    ];

    $microturno = $order->microturno;
    $eta        = $microturno ? $microturno->getFormattedTimeAttribute() : null;

    $wallet          = $wallet ?? null;
    $currentStickers = $wallet?->current_stickers ?? 0;
    $targetStickers  = $setting?->target_stickers ?? 8;

    // Mostrar prompt de reseña solo si está entregado y el usuario NO confirmó que ya dejó reseña
    $showReviewPrompt = ($order->status === 'delivered') && !($order->user && $order->user->google_review_completed_at);
@endphp

<div class="tracker-page" id="tracker-page">

    {{-- ── Banner offline: siempre en el DOM, visible solo cuando siteOffline=true ── --}}
    @php
        $isDeliveredOnLoad = $order->status === 'delivered';
        $isSiteOffline     = !empty($siteOffline) && $siteOffline;
        $offlineStatusMsgs = [
            'pending'    => 'tu pedido fue recibido y está siendo procesado',
            'confirmed'  => 'tu pedido fue confirmado y pronto empieza la preparación',
            'preparing'  => 'tu pedido está siendo preparado en este momento',
            'ready'      => 'tu pedido ya está listo',
            'on_the_way' => 'tu pedido está en camino hacia vos',
        ];
        $offlineStatusMsg = $offlineStatusMsgs[$order->status] ?? 'tu pedido está en curso';
    @endphp
    <div id="offline-tracking-banner" style="{{ $isSiteOffline ? '' : 'display:none;' }}background:linear-gradient(90deg,#1a1a2e 0%,#16213e 100%);border-bottom:2px solid rgba(255,200,0,.35);color:#f0e6c8;padding:12px 16px;text-align:center;font-size:.92rem;line-height:1.5;">
        <span id="offline-banner-active" style="{{ ($isSiteOffline && !$isDeliveredOnLoad) ? '' : 'display:none' }}">
            🔒 <strong style="letter-spacing:.06em;">El local ya cerró</strong>,
            pero no te preocupes —
            <strong id="offline-status-text" style="color:#ffd700;">{{ $offlineStatusMsg }}</strong>.
            ¡Tu pedido llega igual! 🔥
        </span>
        <span id="offline-banner-delivered" style="{{ ($isSiteOffline && $isDeliveredOnLoad) ? '' : 'display:none' }}">
            🔒 <strong style="letter-spacing:.08em;font-size:.95rem;">COCINA CERRADA</strong> 🔒
            <span style="opacity:.25;">|</span>
            <strong>T cocina:</strong>
            Para realizar pedidos la web se activa de miércoles a domingo a las 19:30. Muchas gracias por elegirnos!
        </span>
    </div>

    {{-- ────────────── HEADER ────────────── --}}
    <div class="tracker-hero">
        <div class="container">
            <p class="tracker-order-meta">Pedido <span class="tracker-order-num">#{{ $order->order_number }}</span></p>
            <div class="tracker-badge-wrap">
                <span class="tracker-status-badge" id="status-badge" data-status="{{ $order->status }}">
                    {{ $order->status_label }}
                </span>
            </div>
            <div class="tracker-live" id="polling-indicator">
                <span class="live-dot"></span>
                <span class="live-label">Actualizando en tiempo real</span>
            </div>
        </div>
    </div>

    <div class="container tracker-body">

        {{-- ────────────── STEPS ────────────── --}}
        <div class="tracker-card" id="steps-card">
            <h2 class="tracker-section-title">Estado del pedido</h2>

            <div class="tracker-steps" id="tracker-steps">
                @foreach($steps as $key => $step)
                    @php
                        $idx       = array_search($key, $statusOrder);
                        $isDone    = $idx < $currentIdx;
                        $isActive  = $idx === $currentIdx;
                        $isPending = $idx > $currentIdx;
                        $ts        = $timestamps[$key] ?? null;
                        $stateClass = $isDone ? 'step-done' : ($isActive ? 'step-active' : 'step-pending');
                    @endphp

                    <div class="tracker-step {{ $stateClass }}" data-step="{{ $key }}" id="step-{{ $key }}">
                        {{-- Connector line (except for last) --}}
                        @if(!$loop->last)
                            <div class="step-connector">
                                <div class="connector-fill {{ $isDone ? 'filled' : '' }}" id="connector-{{ $key }}"></div>
                            </div>
                        @endif

                        {{-- Icon circle --}}
                        <div class="step-icon-wrap">
                            <div class="step-circle">
                                @if($isDone)
                                    <i class="fas fa-check"></i>
                                @else
                                    <i class="fas {{ $step['icon'] }}"></i>
                                @endif
                            </div>
                        </div>

                        {{-- Text --}}
                        <div class="step-content">
                            <div class="step-label">{{ $step['label'] }}</div>
                            <div class="step-sub">{{ $step['sub'] }}</div>
                            @if($ts && ($isDone || $isActive))
                                <div class="step-time" id="time-{{ $key }}">{{ $ts->format('H:i') }}</div>
                            @else
                                <div class="step-time" id="time-{{ $key }}"></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ────────────── ETA ────────────── --}}
        @if($eta && $order->status !== 'delivered')
            <div class="tracker-card tracker-eta" id="eta-card">
                <i class="fas fa-clock tracker-eta-icon"></i>
                <div>
                    <div class="tracker-eta-label">Horario de entrega</div>
                    <div class="tracker-eta-time" id="eta-value">{{ $eta }}</div>
                </div>
            </div>
        @endif

        {{-- ────────────── FIGURITAS ────────────── --}}
        @if($setting && $setting->enabled ?? false)
            <div class="tracker-card tracker-loyalty" id="loyalty-card">
                <h2 class="tracker-section-title">
                    <i class="fas fa-sun me-2" style="color: var(--brand-accent)"></i>
                    Tu álbum de figuritas
                </h2>
                <div class="loyalty-grid" id="loyalty-grid">
                    @for($i = 1; $i <= $targetStickers; $i++)
                        <div class="loyalty-sticker {{ $i <= $currentStickers ? 'sticker-filled' : 'sticker-empty' }}"
                             id="sticker-{{ $i }}">
                            🍔
                        </div>
                    @endfor
                </div>
                <div class="loyalty-progress-text" id="loyalty-text">
                    <span id="sticker-count">{{ $currentStickers }}</span>/{{ $targetStickers }} figuritas
                </div>
                <a href="{{ route('loyalty.dashboard') }}" class="loyalty-link">
                    Ver mi álbum completo <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        @endif

    </div>{{-- /container --}}
</div>{{-- /tracker-page --}}

{{-- ────────────── CELEBRATION OVERLAY ────────────── --}}
<div class="celebration-overlay" id="celebration" aria-hidden="true" style="display:none">
    <div class="confetti-container" id="confetti-container"></div>
    <div class="celebration-content">

        {{-- Fase 1: Moto en movimiento --}}
        <div id="phase-moto" class="celebration-phase">
            <div class="celebration-icon-wrap">
                <div class="celebration-icon">🛵</div>
                <div class="celebration-speed-track"></div>
            </div>
            <p class="celebration-phase-label">Tu pedido está llegando…</p>
        </div>

        {{-- Fase 2: Check animado (oculto hasta la transición) --}}
        <div id="phase-check" class="celebration-phase" style="display:none; opacity:0;">
            <svg class="check-svg" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle class="check-circle" cx="40" cy="40" r="34" stroke="#4ade80" stroke-width="3"/>
                <polyline class="check-mark" points="22,40 34,53 58,27" stroke="#4ade80" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="celebration-title" id="celebration-title" style="opacity:0; transform:translateY(12px)">¡Llegó tu pedido!</h1>
        <p class="celebration-sub" id="celebration-sub" style="opacity:0">Esperamos que lo disfrutes</p>
        <div class="celebration-actions" id="celebration-actions" style="opacity:0">
            <a href="{{ route('catalog') }}" class="btn-celebration-primary">
                <i class="fas fa-utensils me-2"></i>Volver al menú
            </a>
            <a href="{{ route('loyalty.dashboard') }}" class="btn-celebration-secondary">
                <i class="fas fa-sun me-2"></i>Ver mis figuritas
            </a>
        </div>

        {{-- Review Prompt — solo la primera vez (anti-spam) --}}
        <div class="celebration-review" id="celebration-review" style="display:none;opacity:0">
            <div class="review-g-wrap">
                <svg class="review-g" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
            </div>
            <p class="review-question">¿Cómo estuvo tu experiencia?</p>
            <div class="review-stars" id="review-stars">
                @for($i = 1; $i <= 5; $i++)
                <svg class="rv-star" data-val="{{ $i }}" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                @endfor
            </div>
        </div>

        <!-- Modal review -->
        <div class="review-modal" id="review-modal">
            <div class="review-modal-backdrop"></div>
            <div class="review-modal-box">
                <button type="button" class="review-modal-close" id="review-modal-close" aria-label="Cerrar">&times;</button>
                <div class="review-modal-fuse">
                    <a href="https://g.page/r/CepJ7XpQQOkyEBM/review" target="_blank" rel="noopener" class="review-modal-btn" id="review-modal-cta">
                        Escribir reseña en Google
                    </a>
                </div>
                <button type="button" class="review-modal-completed" id="review-modal-completed">Ya dejé mi reseña en Google</button>
            </div>
            <!-- BLStudio credit debajo del modal -->
            <div class="review-modal-studio" id="review-modal-studio">
                <span class="review-modal-studio-label">desarrollado por</span>
                <a href="http://stgrandesligas.com" target="_blank" rel="noopener" class="review-modal-studio-link">
                    <svg class="review-modal-studio-svg" viewBox="0 0 260 70" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="bl-m2" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%"   stop-color="#d0d0d0"/>
                                <stop offset="30%"  stop-color="#a0a0a0"/>
                                <stop offset="55%"  stop-color="#c8c8c8"/>
                                <stop offset="80%"  stop-color="#808080"/>
                                <stop offset="100%" stop-color="#b0b0b0"/>
                            </linearGradient>
                            <linearGradient id="bl-g2" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%"   stop-color="#a8d400"/>
                                <stop offset="100%" stop-color="#6a8a00"/>
                            </linearGradient>
                        </defs>
                        <text x="15" y="56" font-family="'DM Sans','Helvetica Neue',Arial,sans-serif" font-size="50" font-weight="300" letter-spacing="-1.5" fill="url(#bl-m2)">blstudio</text>
                        <circle cx="216" cy="44" r="3.5" fill="url(#bl-g2)"/>
                        <circle cx="216" cy="44" r="3.5" fill="none" stroke="#8db600" stroke-width="0.8">
                            <animate attributeName="r" values="3.5;14;3.5" dur="2.2s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.7;0;0.7" dur="2.2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="216" cy="44" r="3.5" fill="none" stroke="#8db600" stroke-width="0.5">
                            <animate attributeName="r" values="3.5;22;3.5" dur="2.2s" begin="0.5s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.35;0;0.35" dur="2.2s" begin="0.5s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ─── TRACKER PAGE ─────────────────────────────────── */
.tracker-page {
    min-height: 100vh;
    background: #0a1628;
    padding-bottom: 3rem;
}

/* ─── HERO ─────────────────────────────────────────── */
.tracker-hero {
    background: linear-gradient(135deg, #0d1f3c 0%, #0a1628 60%, rgba(255,107,53,.08) 100%);
    border-bottom: 1px solid rgba(255,255,255,.07);
    padding: 2.5rem 0 2rem;
    text-align: center;
}

.tracker-order-meta {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    color: rgba(255,255,255,.5);
    font-size: .85rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: .3rem;
}

.tracker-order-num {
    font-family: var(--font-mono), 'JetBrains Mono', monospace;
    color: rgba(255,255,255,.85);
    font-size: 1rem;
    font-weight: 500;
}

.tracker-badge-wrap {
    margin-bottom: .8rem;
}

.tracker-status-badge {
    display: inline-block;
    padding: .4rem 1.4rem;
    border-radius: 50px;
    font-family: var(--font-display), 'Bebas Neue', sans-serif;
    font-size: 1.5rem;
    letter-spacing: .06em;
    background: rgba(255,107,53,.15);
    color: var(--brand-accent, #ff6b35);
    border: 1.5px solid rgba(255,107,53,.35);
    transition: all .4s ease;
}

.tracker-status-badge[data-status="pending"]   { background: rgba(100,116,139,.15); color: #94a3b8; border-color: rgba(100,116,139,.3); }
.tracker-status-badge[data-status="confirmed"] { background: rgba(0,180,216,.12);  color: var(--brand-primary, #00b4d8); border-color: rgba(0,180,216,.3); }
.tracker-status-badge[data-status="preparing"] { background: rgba(255,107,53,.15); color: var(--brand-accent, #ff6b35); border-color: rgba(255,107,53,.35); }
.tracker-status-badge[data-status="ready"]     { background: rgba(34,197,94,.12);  color: #4ade80; border-color: rgba(34,197,94,.3); }
.tracker-status-badge[data-status="delivered"] { background: rgba(34,197,94,.15);  color: #22c55e; border-color: rgba(34,197,94,.4); }

/* ─── LIVE INDICATOR ────────────────────────────────── */
.tracker-live {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .78rem;
    color: rgba(255,255,255,.55);
    margin-top: .4rem;
}

.tracker-live.paused { opacity: .3; }

.live-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #4ade80;
    animation: livePulse 1.8s ease-in-out infinite;
    flex-shrink: 0;
}

@keyframes livePulse {
    0%,100% { opacity:1; transform:scale(1);   box-shadow: 0 0 0 0 rgba(74,222,128,.5); }
    50%      { opacity:.7; transform:scale(1.2); box-shadow: 0 0 0 4px rgba(74,222,128,0); }
}

/* ─── BODY / CONTAINER ──────────────────────────────── */
.tracker-body {
    max-width: 560px;
    margin: 0 auto;
    padding-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ─── CARD ──────────────────────────────────────────── */
.tracker-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: 1.5rem;
    backdrop-filter: blur(6px);
}

.tracker-section-title {
    font-family: var(--font-display), 'Bebas Neue', sans-serif;
    font-size: 1.25rem;
    letter-spacing: .05em;
    color: rgba(255,255,255,.7);
    margin-bottom: 1.4rem;
    text-transform: uppercase;
}

/* ─── STEPS ─────────────────────────────────────────── */
.tracker-steps {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
}

.tracker-step {
    display: grid;
    grid-template-columns: 44px 1fr;
    gap: 0 1rem;
    align-items: start;
    position: relative;
    padding-bottom: 1.6rem;
}

.tracker-step:last-child {
    padding-bottom: 0;
}

/* Connector line */
.step-connector {
    position: absolute;
    left: 21px;
    top: 44px;
    bottom: 0;
    width: 2px;
    background: rgba(255,255,255,.08);
    z-index: 0;
    overflow: hidden;
}

.connector-fill {
    width: 100%;
    height: 0%;
    background: linear-gradient(180deg, var(--brand-primary, #00b4d8), rgba(0,180,216,.3));
    transition: height .8s ease;
}

.connector-fill.filled {
    height: 100%;
}

/* Icon circle */
.step-icon-wrap {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: center;
    padding-top: 2px;
}

.step-circle {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    border: 2px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.25);
    transition: all .4s ease;
    position: relative;
    flex-shrink: 0;
}

/* Done state — círculo verde */
.step-done .step-circle {
    background: #22c55e;
    border-color: #22c55e;
    color: #fff;
    box-shadow: 0 0 12px rgba(34,197,94,.3);
}

.step-done .step-label { color: rgba(255,255,255,.7); }
.step-done .step-sub   { color: rgba(255,255,255,.35); }
.step-done .step-time  { color: rgba(34,197,94,.8); }

/* Active state — color + pulso por paso */
.step-active .step-label { color: #fff; font-weight: 600; }
.step-active .step-sub   { color: rgba(255,255,255,.55); }

.tracker-step[data-step="pending"].step-active .step-circle {
    background: rgba(56,189,248,.12); border-color: #38bdf8; color: #38bdf8;
    animation: trkPulse-pending 2s ease-in-out infinite;
}
.tracker-step[data-step="pending"].step-active .step-time { color: #38bdf8; }

.tracker-step[data-step="confirmed"].step-active .step-circle {
    background: rgba(74,222,128,.12); border-color: #4ade80; color: #4ade80;
    animation: trkPulse-confirmed 2s ease-in-out infinite;
}
.tracker-step[data-step="confirmed"].step-active .step-time { color: #4ade80; }

.tracker-step[data-step="preparing"].step-active .step-circle {
    background: rgba(251,146,60,.12); border-color: #fb923c; color: #fb923c;
    animation: trkPulse-preparing 2s ease-in-out infinite;
}
.tracker-step[data-step="preparing"].step-active .step-time { color: #fb923c; }

.tracker-step[data-step="ready"].step-active .step-circle {
    background: rgba(251,191,36,.12); border-color: #fbbf24; color: #fbbf24;
    animation: trkPulse-ready 2s ease-in-out infinite;
}
.tracker-step[data-step="ready"].step-active .step-time { color: #fbbf24; }

.tracker-step[data-step="on_the_way"].step-active .step-circle {
    background: rgba(139,92,246,.12); border-color: #a78bfa; color: #a78bfa;
    animation: trkPulse-on_the_way 2s ease-in-out infinite;
}
.tracker-step[data-step="on_the_way"].step-active .step-time { color: #a78bfa; }

.tracker-step[data-step="delivered"].step-active .step-circle {
    background: rgba(34,197,94,.12); border-color: #22c55e; color: #22c55e;
    animation: trkPulse-delivered 2s ease-in-out infinite;
}
.tracker-step[data-step="delivered"].step-active .step-time { color: #22c55e; }

@keyframes trkPulse-pending    { 0%,100%{ box-shadow:0 0 0 0 rgba(56,189,248,.45);  } 50%{ box-shadow:0 0 0 8px rgba(56,189,248,0);  } }
@keyframes trkPulse-confirmed  { 0%,100%{ box-shadow:0 0 0 0 rgba(74,222,128,.45);  } 50%{ box-shadow:0 0 0 8px rgba(74,222,128,0);  } }
@keyframes trkPulse-preparing  { 0%,100%{ box-shadow:0 0 0 0 rgba(251,146,60,.45);  } 50%{ box-shadow:0 0 0 8px rgba(251,146,60,0);  } }
@keyframes trkPulse-ready      { 0%,100%{ box-shadow:0 0 0 0 rgba(251,191,36,.45);  } 50%{ box-shadow:0 0 0 8px rgba(251,191,36,0);  } }
@keyframes trkPulse-on_the_way { 0%,100%{ box-shadow:0 0 0 0 rgba(139,92,246,.45);  } 50%{ box-shadow:0 0 0 8px rgba(139,92,246,0);  } }
@keyframes trkPulse-delivered  { 0%,100%{ box-shadow:0 0 0 0 rgba(34,197,94,.45);   } 50%{ box-shadow:0 0 0 8px rgba(34,197,94,0);   } }

/* Animaciones del icono — solo cuando ese paso está activo */

/* pending: rebota */
.tracker-step[data-step="pending"].step-active    .step-circle i { animation: iconBounce  1.4s ease-in-out infinite; }

/* confirmed: pulso suave, sin girar */
.tracker-step[data-step="confirmed"].step-active  .step-circle i { animation: iconCheckPulse 1.6s ease-in-out infinite; }

/* preparing: llama que se mueve */
.tracker-step[data-step="preparing"].step-active  .step-circle i {
    animation: iconFire 0.55s ease-in-out infinite;
    transform-origin: bottom center;
    display: inline-block;
}

/* ready: pop */
.tracker-step[data-step="ready"].step-active      .step-circle i { animation: iconPop 1.1s ease-in-out infinite; }

/* on_the_way: moto quieta, líneas de velocidad con ::before/::after */
.tracker-step[data-step="on_the_way"].step-active .step-circle {
    overflow: hidden;
}
.tracker-step[data-step="on_the_way"].step-active .step-circle i {
    position: relative;
    z-index: 1;
}
.tracker-step[data-step="on_the_way"].step-active .step-circle::before,
.tracker-step[data-step="on_the_way"].step-active .step-circle::after {
    content: '';
    position: absolute;
    left: 0; right: 0;
    height: 1.5px;
    border-radius: 1px;
    background: repeating-linear-gradient(
        90deg,
        rgba(167,139,250,.9) 0px, rgba(167,139,250,.9) 7px,
        transparent 7px, transparent 12px
    );
    background-size: 19px 100%;
    animation: circleLines .38s linear infinite;
}
.tracker-step[data-step="on_the_way"].step-active .step-circle::before { bottom: 28%; }
.tracker-step[data-step="on_the_way"].step-active .step-circle::after  { bottom: 18%; animation-delay: .1s; }

@keyframes circleLines { from { background-position-x: 0; } to { background-position-x: -19px; } }

/* delivered: pop */
.tracker-step[data-step="delivered"].step-active  .step-circle i { animation: iconPop 0.9s ease-in-out infinite; }

@keyframes iconBounce     { 0%,100%{ transform:translateY(0)     } 50%{ transform:translateY(-4px) } }
@keyframes iconCheckPulse { 0%,100%{ transform:scale(1);opacity:1 } 50%{ transform:scale(1.18);opacity:.75 } }
@keyframes iconFire       {
    0%   { transform: scaleY(1)    skewX(0deg);   }
    20%  { transform: scaleY(1.12) skewX(-4deg);  }
    45%  { transform: scaleY(.93)  skewX(3deg);   }
    70%  { transform: scaleY(1.08) skewX(-2deg);  }
    100% { transform: scaleY(1)    skewX(0deg);   }
}
@keyframes iconPop        { 0%,100%{ transform:scale(1) } 50%{ transform:scale(1.35) } }

/* Pending state */
.step-pending .step-label { color: rgba(255,255,255,.25); }
.step-pending .step-sub   { color: rgba(255,255,255,.15); }

/* Step text */
.step-content { padding-top: 10px; }

.step-label {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .95rem;
    font-weight: 600;
    color: rgba(255,255,255,.4);
    line-height: 1.2;
    transition: color .4s ease;
}

.step-sub {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .78rem;
    color: rgba(255,255,255,.2);
    margin-top: .15rem;
    transition: color .4s ease;
}

.step-time {
    font-family: var(--font-mono), 'JetBrains Mono', monospace;
    font-size: .72rem;
    margin-top: .3rem;
    min-height: 1em;
    transition: color .4s ease;
}

/* ─── ETA ───────────────────────────────────────────── */
.tracker-eta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.tracker-eta-icon {
    font-size: 1.5rem;
    color: var(--brand-accent, #ff6b35);
    flex-shrink: 0;
}

.tracker-eta-label {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .75rem;
    color: rgba(255,255,255,.35);
    text-transform: uppercase;
    letter-spacing: .06em;
}

.tracker-eta-time {
    font-family: var(--font-mono), 'JetBrains Mono', monospace;
    font-size: 1.4rem;
    color: rgba(255,255,255,.85);
    font-weight: 500;
}

/* ─── LOYALTY ───────────────────────────────────────── */
.tracker-loyalty {}

.loyalty-grid {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-bottom: .8rem;
    justify-content: flex-start;
}

.loyalty-sticker {
    width: 38px; height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all .3s ease;
    border: 1.5px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.04);
}

.loyalty-sticker.sticker-filled {
    background: rgba(255,246,191,.1);
    border-color: rgba(251,191,36,.3);
    box-shadow: 0 0 8px rgba(251,191,36,.15);
}

.loyalty-sticker.sticker-empty {
    filter: grayscale(1) opacity(.3);
}

.loyalty-progress-text {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .85rem;
    color: rgba(255,255,255,.4);
    margin-bottom: .6rem;
}

.loyalty-link {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .82rem;
    color: var(--brand-primary, #00b4d8);
    text-decoration: none;
    transition: opacity .2s;
}

.loyalty-link:hover { opacity: .7; }

/* ─── CELEBRATION ───────────────────────────────────── */
.celebration-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(5,10,20,.97);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
    animation: celebFadeIn .5s ease;
}

@keyframes celebFadeIn { from{opacity:0} to{opacity:1} }

.celebration-content {
    position: relative;
    z-index: 2;
    padding: 1.2rem 1.5rem;
    max-width: 420px;
}

.celebration-icon-wrap {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1rem;
}

.celebration-icon {
    font-size: 4rem;
    display: block;
    line-height: 1;
    margin-bottom: 4px;
    animation: celebIcon 1s ease-in-out infinite alternate;
}

@keyframes celebIcon { from{transform:translateX(-8px)} to{transform:translateX(8px)} }

/* Líneas de velocidad: misma fila, pasan de izq a der por debajo de la moto */
.celebration-speed-track {
    width: 110px;
    height: 3px;
    border-radius: 2px;
    overflow: hidden;
    background: repeating-linear-gradient(
        90deg,
        rgba(255,255,255,.85) 0px,
        rgba(255,255,255,.85) 28px,
        transparent 28px,
        transparent 46px
    );
    background-size: 74px 100%;
    animation: mlineFlow .45s linear infinite;
}

@keyframes mlineFlow {
    from { background-position-x: 0; }
    to   { background-position-x: 74px; }
}

.celebration-title {
    font-family: var(--font-display), 'Bebas Neue', sans-serif;
    font-size: clamp(2.5rem, 8vw, 4rem);
    letter-spacing: .04em;
    color: #fff;
    margin-bottom: .5rem;
}

.celebration-sub {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    color: rgba(255,255,255,.5);
    font-size: 1rem;
    margin-bottom: 1.4rem;
}

.celebration-actions {
    display: flex;
    flex-direction: column;
    gap: .75rem;
    align-items: center;
}

/* Fases de la celebración */
.celebration-phase { display: flex; flex-direction: column; align-items: center; }

.celebration-phase-label {
    font-family: 'DM Sans', sans-serif;
    color: rgba(255,255,255,.6);
    font-size: .9rem;
    margin-top: .6rem;
    margin-bottom: 0;
}

/* Check SVG animado */
.check-svg {
    width: 110px;
    height: 110px;
    margin-bottom: 1rem;
}

.check-circle {
    stroke-dasharray: 214;
    stroke-dashoffset: 214;
    animation: drawCircle .7s cubic-bezier(.4,0,.2,1) forwards;
}

.check-mark {
    stroke-dasharray: 70;
    stroke-dashoffset: 70;
    animation: drawCheck .45s ease .65s forwards;
}

@keyframes drawCircle { to { stroke-dashoffset: 0; } }
@keyframes drawCheck  { to { stroke-dashoffset: 0; } }

.btn-celebration-primary {
    display: inline-flex !important;
    align-items: center !important;
    background: #00b4d8 !important;
    color: #fff !important;
    padding: .85rem 2.2rem !important;
    border-radius: 50px !important;
    font-family: 'DM Sans', sans-serif !important;
    font-weight: 700 !important;
    font-size: 1rem !important;
    text-decoration: none !important;
    border: none !important;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 4px 24px rgba(0,180,216,.4) !important;
}

.btn-celebration-primary:hover,
.btn-celebration-primary:focus {
    transform: translateY(-2px);
    color: #fff !important;
    background: #0099bb !important;
    text-decoration: none !important;
    box-shadow: 0 8px 28px rgba(0,180,216,.55) !important;
}

.btn-celebration-secondary {
    display: inline-flex;
    align-items: center;
    color: rgba(255,255,255,.5);
    padding: .5rem 1.2rem;
    border-radius: 50px;
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .9rem;
    text-decoration: none;
    border: 1px solid rgba(255,255,255,.12);
    transition: all .2s;
}

.btn-celebration-secondary:hover { color: rgba(255,255,255,.8); border-color: rgba(255,255,255,.3); }

/* ─── REVIEW PROMPT (dentro de celebration) ─────────── */
.celebration-review {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .35rem;
    max-width: 360px;
    width: 100%;
    margin-top: .3rem;
}

.review-g-wrap { margin-bottom: 6px; }
.review-g {
    width: 32px; height: 32px;
    display: block;
}

.review-question {
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: 1rem;
    font-weight: 500;
    color: #fff;
    margin: 0;
    line-height: 1.3;
}

.review-stars {
    display: inline-flex;
    gap: 6px;
    margin: .15rem 0 0;
}
.rv-star {
    width: 28px; height: 28px;
    cursor: pointer;
    transition: transform .15s, fill .2s;
    fill: rgba(255,255,255,.22);
}
.rv-star:hover { transform: scale(1.18); }
.rv-star.active { fill: #fbbc05; }
.rv-star.pulse { animation: rvStarPulse .35s ease; }
@keyframes rvStarPulse {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.25); }
    100% { transform: scale(1); }
}

/* ─── REVIEW MODAL (imagen profesional) ────────────── */
.review-modal {
    position: fixed;
    inset: 0;
    z-index: 200;
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .3s;
}
.review-modal.open { display: flex; opacity: 1; }

.review-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(4px);
}

.review-modal-box {
    position: relative;
    z-index: 1;
    width: 90%;
    max-width: 340px;
    aspect-ratio: 765 / 1114;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    background: url('/images/modalreseñas.png') center center / cover no-repeat;
    transform: translateY(12px) scale(.96);
    transition: transform .3s cubic-bezier(.34,1.56,.64,1);
}
.review-modal.open .review-modal-box {
    transform: translateY(0) scale(1);
}

/* Botón + dismiss posicionados sobre la imagen */
.review-modal-fuse {
    position: absolute;
    left: 50%;
    bottom: 26%;
    transform: translateX(-50%);
    width: 82%;
    border-radius: 28px;
    padding: 3px;
    overflow: hidden;
    animation: fuseGlow 2.5s ease-in-out infinite;
}
.review-modal-fuse::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: conic-gradient(from 0deg, #ff6b35, #ffd700, #ff4500, #ffd700, #ff6b35);
    animation: fuseSpin 4s linear infinite;
    z-index: 0;
}
.review-modal-btn {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 12px 20px;
    border-radius: 25px;
    border: none;
    background: #fff;
    color: #1a73e8;
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .9rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}
@keyframes fuseSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
@keyframes fuseGlow {
    0%, 100% { box-shadow: 0 0 6px rgba(255,107,53,.25), 0 0 12px rgba(255,107,53,.1); }
    50% { box-shadow: 0 0 12px rgba(255,107,53,.4), 0 0 24px rgba(255,107,53,.2); }
}

.review-modal-completed {
    position: absolute;
    left: 50%;
    bottom: 16%;
    transform: translateX(-50%);
    font-family: var(--font-body), 'DM Sans', sans-serif;
    font-size: .72rem;
    color: rgba(255,255,255,.55);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 10px;
    transition: color .2s;
    white-space: nowrap;
}
.review-modal-completed:hover { color: rgba(255,255,255,.85); }

/* X de cerrar */
.review-modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 10;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: rgba(0,0,0,.25);
    color: #fff;
    font-size: 1.4rem;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .2s, transform .15s;
}
.review-modal-close:hover {
    background: rgba(0,0,0,.45);
    transform: scale(1.08);
}

/* Botón hover */
.review-modal-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 16px rgba(0,0,0,.2);
}

/* BLStudio credit debajo del modal */
.review-modal-studio {
    position: absolute;
    left: 50%;
    bottom: 5%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    opacity: 1;
    transition: opacity .3s ease;
    pointer-events: none;
}
.review-modal-studio-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: .25em;
    text-transform: uppercase;
    color: rgba(255,255,255,.75);
    transition: color .3s ease;
}
.review-modal-studio-link {
    display: block;
    line-height: 0;
    pointer-events: all;
}
.review-modal-studio-svg {
    width: 160px;
    filter: drop-shadow(0 0 5px rgba(255,255,255,.35));
    transition: filter .3s ease;
}
.review-modal-studio:hover {
    opacity: 1;
}
.review-modal-studio:hover .review-modal-studio-label {
    color: rgba(255,255,255,.85);
}
.review-modal-studio:hover .review-modal-studio-svg {
    filter: drop-shadow(0 0 12px rgba(255,255,255,.8));
}

/* ─── CONFETTI ──────────────────────────────────────── */
.confetti-container {
    position: fixed;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 1;
}

.confetti-piece {
    position: absolute;
    width: 8px;
    height: 8px;
    top: -10px;
    border-radius: 2px;
    animation: confettiFall linear forwards;
}

@keyframes confettiFall {
    0%   { transform: translateY(0)   rotate(0deg);   opacity: 1; }
    100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
}

/* ─── RESPONSIVE ────────────────────────────────────── */
@media (max-width: 480px) {
    .tracker-hero { padding: 2rem 0 1.5rem; }
    .tracker-card { padding: 1.2rem; border-radius: 12px; }
    .step-circle  { width: 38px; height: 38px; font-size: .85rem; }
    .tracker-steps { }
    .step-connector { left: 18px; top: 40px; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const ORDER_NUMBER  = @json($order->order_number);
    const STATUS_URL    = `/api/pedido/${ORDER_NUMBER}/estado`;
    const CSRF_TOKEN    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const POLL_INTERVAL = 10000;
    const IS_DELIVERY   = @json($isDelivery);
    const SHOW_REVIEW_PROMPT = @json($showReviewPrompt);
    const DISMISS_URL    = `/gracias/${ORDER_NUMBER}/dismiss`;
    const COMPLETED_URL  = `/gracias/${ORDER_NUMBER}/completada`;

    const STATUS_ORDER_DELIVERY = ['pending', 'confirmed', 'preparing', 'ready', 'on_the_way', 'delivered'];
    const STATUS_ORDER_PICKUP   = ['pending', 'confirmed', 'preparing', 'ready', 'delivered'];
    const STATUS_ORDER = IS_DELIVERY ? STATUS_ORDER_DELIVERY : STATUS_ORDER_PICKUP;

    const STEPS_META = IS_DELIVERY ? {
        pending:    { label: 'Pedido enviado',    sub: 'Tu pedido llegó al local',        icon: 'fa-paper-plane' },
        confirmed:  { label: 'Confirmado',        sub: 'El local aceptó tu pedido',       icon: 'fa-check-circle' },
        preparing:  { label: 'En la plancha',     sub: '¡Tu burger está en preparación!', icon: 'fa-fire' },
        ready:      { label: 'Listo para enviar', sub: 'Tu pedido está listo',            icon: 'fa-star' },
        on_the_way: { label: 'En camino',         sub: '¡Tu pedido viene hacia vos!',     icon: 'fa-motorcycle' },
        delivered:  { label: 'Entregado',         sub: '¡Llegó tu pedido!',              icon: 'fa-house' },
    } : {
        pending:   { label: 'Pedido enviado',      sub: 'Tu pedido llegó al local',        icon: 'fa-paper-plane' },
        confirmed: { label: 'Confirmado',          sub: 'El local aceptó tu pedido',       icon: 'fa-check-circle' },
        preparing: { label: 'En la plancha',       sub: '¡Tu burger está en preparación!', icon: 'fa-fire' },
        ready:     { label: 'Listo para retirar',  sub: 'Pasá a buscarlo al local',        icon: 'fa-store' },
        delivered: { label: 'Retirado',            sub: '¡Que lo disfrutes!',             icon: 'fa-check-circle' },
    };

    let currentStatus  = @json($order->status);
    let pollTimer      = null;
    let siteWasOffline = @json($isSiteOffline ?? false);

    const OFFLINE_STATUS_MSGS = {
        pending:    'tu pedido fue recibido y está siendo procesado',
        confirmed:  'tu pedido fue confirmado y pronto empieza la preparación',
        preparing:  'tu pedido está siendo preparado en este momento',
        ready:      'tu pedido ya está listo',
        on_the_way: 'tu pedido está en camino hacia vos',
    };

    function showOfflineBanner(status) {
        const banner    = document.getElementById('offline-tracking-banner');
        const spanActive    = document.getElementById('offline-banner-active');
        const spanDelivered = document.getElementById('offline-banner-delivered');
        const statusText    = document.getElementById('offline-status-text');
        if (!banner) return;
        if (status === 'delivered') {
            if (spanActive)    spanActive.style.display    = 'none';
            if (spanDelivered) spanDelivered.style.display = '';
        } else {
            if (statusText) statusText.textContent = OFFLINE_STATUS_MSGS[status] ?? 'tu pedido está en curso';
            if (spanActive)    spanActive.style.display    = '';
            if (spanDelivered) spanDelivered.style.display = 'none';
        }
        banner.style.display = '';
    }

    /* ── Polling ─────────────────────────────────── */
    function startPolling() {
        if (currentStatus === 'delivered') return;
        pollTimer = setInterval(fetchStatus, POLL_INTERVAL);
    }

    function stopPolling() {
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        const indicator = document.getElementById('polling-indicator');
        if (indicator) indicator.classList.add('paused');
    }

    function fetchStatus() {
        fetch(STATUS_URL, {
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(data => {
            // Detectar cierre dinámico del sitio
            if (data.site_offline && !siteWasOffline) {
                siteWasOffline = true;
                showOfflineBanner(currentStatus);
            }

            if (data.status !== currentStatus) {
                currentStatus = data.status;
                updateUI(data);
                if (data.status === 'delivered') {
                    stopPolling();
                    if (siteWasOffline) showOfflineBanner('delivered');
                    setTimeout(() => showCelebration(data.show_review_prompt), 600);
                } else if (siteWasOffline) {
                    // Actualizar texto del banner si el status cambió estando offline
                    showOfflineBanner(data.status);
                }
            }
        })
        .catch(() => { /* silencioso — red inestable en mobile */ });
    }

    /* ── UI Update ───────────────────────────────── */
    function updateUI(data) {
        const newStatus = data.status;
        const newIdx    = STATUS_ORDER.indexOf(newStatus);

        // Badge
        const badge = document.getElementById('status-badge');
        if (badge) {
            badge.textContent  = data.status_label;
            badge.dataset.status = newStatus;
        }

        // Steps
        STATUS_ORDER.forEach((key, idx) => {
            const stepEl     = document.getElementById(`step-${key}`);
            const timeEl     = document.getElementById(`time-${key}`);
            const connEl     = document.getElementById(`connector-${key}`);
            if (!stepEl) return;

            const isDone    = idx < newIdx;
            const isActive  = idx === newIdx;
            const tsKey = key === 'pending' ? 'created' : (key === 'on_the_way' ? 'on_the_way' : key);
            const ts    = data[`${tsKey}_at`];
            const tsDisplay = ts ? formatTime(ts) : '';

            stepEl.className = `tracker-step ${isDone ? 'step-done' : isActive ? 'step-active' : 'step-pending'}`;
            stepEl.dataset.step = key;

            // Update icon circle
            const circle = stepEl.querySelector('.step-circle');
            if (circle) {
                const icon = STEPS_META[key].icon;
                circle.innerHTML = isDone
                    ? '<i class="fas fa-check"></i>'
                    : `<i class="fas ${icon}"></i>`;
            }

            // Update connector fill
            if (connEl) connEl.classList.toggle('filled', isDone);

            // Update timestamp
            if (timeEl && (isDone || isActive) && tsDisplay) timeEl.textContent = tsDisplay;
        });

        // Loyalty
        if (data.loyalty) {
            const cur    = data.loyalty.current_stickers;
            const target = data.loyalty.target_stickers;
            const countEl = document.getElementById('sticker-count');
            if (countEl) countEl.textContent = cur;

            for (let i = 1; i <= target; i++) {
                const s = document.getElementById(`sticker-${i}`);
                if (s) {
                    s.classList.toggle('sticker-filled', i <= cur);
                    s.classList.toggle('sticker-empty',  i > cur);
                }
            }
        }
    }

    /* ── Celebration ─────────────────────────────── */
    function showCelebration(showReviewPrompt = SHOW_REVIEW_PROMPT) {
        const overlay = document.getElementById('celebration');
        if (!overlay) return;
        overlay.style.display = 'flex';
        overlay.removeAttribute('aria-hidden');

        const phaseMoto  = document.getElementById('phase-moto');
        const phaseCheck = document.getElementById('phase-check');
        const title      = document.getElementById('celebration-title');
        const sub        = document.getElementById('celebration-sub');
        const actions    = document.getElementById('celebration-actions');
        const reviewEl   = document.getElementById('celebration-review');

        // ── Fase 1: moto corre 4 segundos ──────────────────
        setTimeout(() => {

            // Fade out moto
            if (phaseMoto) {
                phaseMoto.style.transition = 'opacity .4s, transform .4s';
                phaseMoto.style.opacity    = '0';
                phaseMoto.style.transform  = 'scale(.85)';
            }

            setTimeout(() => {
                if (phaseMoto) phaseMoto.style.display = 'none';

                // ── Fase 2: check aparece ───────────────────
                if (phaseCheck) {
                    phaseCheck.style.display = 'flex';
                    // Re-trigger CSS animations clonando el SVG
                    const oldSvg = phaseCheck.querySelector('.check-svg');
                    if (oldSvg) {
                        const newSvg = oldSvg.cloneNode(true);
                        oldSvg.replaceWith(newSvg);
                    }
                    // Fade in
                    requestAnimationFrame(() => {
                        phaseCheck.style.transition = 'opacity .4s';
                        phaseCheck.style.opacity    = '1';
                    });
                }

                // Título y subtítulo
                if (title) {
                    title.style.transition  = 'opacity .5s .3s, transform .5s .3s';
                    title.style.opacity     = '1';
                    title.style.transform   = 'translateY(0)';
                }
                if (sub) {
                    sub.style.transition = 'opacity .5s .45s';
                    sub.style.opacity    = '1';
                }

                // ── Decidir qué acciones mostrar ────────────
                if (showReviewPrompt && reviewEl) {
                    if (actions) actions.style.display = 'none';
                    reviewEl.style.display = 'flex';
                    requestAnimationFrame(() => {
                        reviewEl.style.transition = 'opacity .5s .65s';
                        reviewEl.style.opacity    = '1';
                    });
                    initReviewStars();
                    initReviewHandlers();
                } else if (actions) {
                    actions.style.transition = 'opacity .5s .65s';
                    actions.style.opacity    = '1';
                }

                // ── Fase 3: confetti después del check ─────
                setTimeout(spawnConfetti, 700);

            }, 420);
        }, 4000);
    }

    /* ── Review stars interaction ──────────────────── */
    function initReviewStars() {
        const stars = document.querySelectorAll('.rv-star');
        const modal = document.getElementById('review-modal');
        let selected = 0;

        function setStars(val) {
            stars.forEach((s, i) => {
                if (i < val) s.classList.add('active');
                else s.classList.remove('active');
            });
        }

        function openModal() {
            if (!modal) return;
            modal.style.display = 'flex';
            requestAnimationFrame(() => modal.classList.add('open'));
        }

        stars.forEach(star => {
            star.addEventListener('mouseenter', () => {
                setStars(parseInt(star.dataset.val));
            });
            star.addEventListener('mouseleave', () => {
                setStars(selected);
            });
            star.addEventListener('click', () => {
                selected = parseInt(star.dataset.val);
                setStars(selected);
                star.classList.remove('pulse');
                void star.offsetWidth;
                star.classList.add('pulse');
                openModal();
            });
        });
    }

    /* ── Review handlers (modal dismiss + CTA) ─────── */
    function initReviewHandlers() {
        const modalCta      = document.getElementById('review-modal-cta');
        const modalClose     = document.getElementById('review-modal-close');
        const modalCompleted = document.getElementById('review-modal-completed');
        const modal          = document.getElementById('review-modal');
        const reviewEl       = document.getElementById('celebration-review');
        const actionsEl      = document.getElementById('celebration-actions');

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('open');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
        }

        function showOriginalButtons() {
            closeModal();
            if (reviewEl) {
                reviewEl.style.transition = 'opacity .3s';
                reviewEl.style.opacity = '0';
                setTimeout(() => {
                    reviewEl.style.display = 'none';
                    if (actionsEl) {
                        actionsEl.style.display = 'flex';
                        requestAnimationFrame(() => {
                            actionsEl.style.transition = 'opacity .4s';
                            actionsEl.style.opacity = '1';
                        });
                    }
                }, 300);
            }
        }

        function markDismissed() {
            fetch(DISMISS_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            }).catch(() => {});
        }

        function markCompleted() {
            fetch(COMPLETED_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            }).catch(() => {});
        }

        if (modalCta) {
            modalCta.addEventListener('click', () => {
                markDismissed();
            });
        }

        if (modalClose) {
            modalClose.addEventListener('click', () => {
                closeModal();
            });
        }

        if (modalCompleted) {
            modalCompleted.addEventListener('click', () => {
                markCompleted();
                showOriginalButtons();
            });
        }
    }

    function spawnConfetti() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        const colors = ['#00b4d8', '#4ade80', '#fbbf24', '#f472b6', '#a78bfa', '#fff'];
        for (let i = 0; i < 90; i++) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.cssText = [
                `left:${Math.random() * 100}%`,
                `background:${colors[Math.floor(Math.random() * colors.length)]}`,
                `animation-duration:${1.5 + Math.random() * 2.5}s`,
                `animation-delay:${Math.random() * 1.2}s`,
                `border-radius:${Math.random() > .5 ? '50%' : '2px'}`,
                `width:${6 + Math.random() * 7}px`,
                `height:${6 + Math.random() * 7}px`,
            ].join(';');
            container.appendChild(piece);
        }
    }

    /* ── Helpers ─────────────────────────────────── */
    function formatTime(isoString) {
        try {
            const d = new Date(isoString);
            return d.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
        } catch { return ''; }
    }

    /* ── Init ────────────────────────────────────── */
    if (currentStatus === 'delivered') {
        // Already delivered on page load — show celebration after brief delay
        setTimeout(showCelebration, 800);
    } else {
        startPolling();
    }

})();
</script>
@endpush
