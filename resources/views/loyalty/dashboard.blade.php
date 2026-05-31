@extends('layouts.app')

@section('title', 'Mi progreso - TCocina')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bangers&display=swap');

        @property --reward-rotate {
            syntax: "<angle>";
            initial-value: 132deg;
            inherits: false;
        }

        .album-title {
            font-family: 'Bangers', cursive;
            font-size: clamp(2.2rem, 5vw, 3.4rem);
            letter-spacing: 1px;
            text-align: center;
            line-height: 1;
        }

        .album-description {
            text-align: center;
        }

        .reward-info-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 1rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }

        .reward-info-card h3 {
            margin-bottom: .45rem;
            color: #111827;
            font-size: 1.05rem;
        }

        .reward-info-card h5 {
            margin-bottom: .3rem;
            color: #111827;
        }

        .reward-info-card p {
            margin-bottom: 0;
            color: #6b7280;
        }

        .sticker-slot {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .sticker-slot img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .sticker-slot.empty img {
            filter: grayscale(1) brightness(0.75) contrast(1.1);
            opacity: 0.9;
        }

        .sticker-slot.filled {
            background: #fff6bf;
            border-color: #facc15;
        }

        .sticker-slot.filled img {
            /* Paint logo to yellow/gold tone */
            filter: brightness(0) saturate(100%) invert(80%) sepia(60%) saturate(1527%) hue-rotate(356deg) brightness(102%) contrast(98%);
        }

        .stickers-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            width: 100%;
        }

        @media (max-width: 576px) {
            .stickers-grid {
                gap: 6px;
            }

            .sticker-slot img {
                width: 40px;
                height: 40px;
            }
        }

        .reward-card {
            --reward-rotate: 132deg;
            position: relative;
            border-radius: 14px;
            padding: 3px;
            isolation: isolate;
            margin-bottom: 1.5rem;
        }

        .reward-card::before {
            content: "";
            position: absolute;
            z-index: -1;
            top: -1%;
            left: -1%;
            width: 102%;
            height: 102%;
            border-radius: 16px;
            background-image: linear-gradient(var(--reward-rotate), #5ddcff, #3c67e3 43%, #4e00c2);
            animation: reward-spin 2.5s linear infinite;
        }

        .reward-card::after {
            content: "";
            position: absolute;
            z-index: -1;
            left: 0;
            right: 0;
            top: 30%;
            margin: 0 auto;
            width: 100%;
            height: 100%;
            transform: scale(0.9);
            filter: blur(28px);
            opacity: .75;
            background-image: linear-gradient(var(--reward-rotate), #5ddcff, #3c67e3 43%, #4e00c2);
            animation: reward-spin 2.5s linear infinite;
        }

        .reward-card-inner {
            border-radius: 12px;
            overflow: hidden;
            background: #191c29;
            color: #e5e7eb;
        }

        .reward-meta h5 {
            color: #111827;
        }

        .reward-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: #111827;
            display: block;
            cursor: zoom-in;
            transition: transform .25s ease;
        }

        .reward-card a:hover img {
            transform: scale(1.02);
        }

        .reward-image-container {
            position: relative;
            overflow: hidden;
        }

        .reward-progress-pill {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.70);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #fff;
            font-size: .82rem;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 999px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 2;
            border: 1px solid rgba(255, 255, 255, 0.15);
            letter-spacing: .2px;
        }

        .reward-progress-pill--done {
            background: rgba(16, 185, 129, 0.88);
            border-color: rgba(16, 185, 129, 0.45);
        }

        .reward-lightbox-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            opacity: 0;
            transition: opacity .25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .reward-image-container:hover .reward-lightbox-overlay {
            opacity: 1;
        }

        .reward-lightbox-overlay i {
            color: #fff;
            font-size: 2rem;
        }

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
            max-height: 68vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            display: block;
        }

        .lightbox-title {
            color: rgba(255, 255, 255, 0.65);
            font-size: .88rem;
            margin-top: .55rem;
            font-weight: 500;
            letter-spacing: .2px;
        }

        .lightbox-album-note {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .55rem;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.38);
            border-radius: 999px;
            padding: .35rem 1rem;
            color: #fcd34d;
            font-size: .78rem;
            font-weight: 600;
        }

        .lightbox-close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #fff;
            font-size: 2.4rem;
            font-weight: bold;
            cursor: pointer;
            z-index: 9998;
            background: rgba(0, 0, 0, 0.5);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .album-help-btn {
            border-radius: 999px;
            padding: .6rem 1.2rem;
            font-weight: 700;
        }

        .album-help-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9996;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(2, 6, 23, 0.65);
            backdrop-filter: blur(6px);
        }

        .album-help-modal.show {
            display: flex;
        }

        .album-help-panel {
            position: relative;
            width: min(720px, 95vw);
            border-radius: 22px;
            border: 1px solid rgba(0, 0, 0, .12);
            background: #ffffff;
            box-shadow: 0 16px 45px rgba(0, 0, 0, .2);
            color: #111827;
            overflow: hidden;
            transform: translateY(8px) scale(.985);
            opacity: 0;
            transition: transform .22s ease, opacity .22s ease;
        }

        .album-help-modal.show .album-help-panel {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .album-help-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            border: 1px solid rgba(255, 255, 255, .08);
            mask: linear-gradient(transparent, black 20%, black 80%, transparent);
        }

        .album-help-shine,
        .album-help-glow {
            position: absolute;
            pointer-events: none;
            border-radius: inherit;
            display: none;
        }

        .album-help-shine {
            inset: -1px;
            border: 1px solid transparent;
            background:
                conic-gradient(from -35deg, rgba(129, 140, 248, 0) 0 10%, rgba(165, 180, 252, .9) 18%, rgba(129, 140, 248, 0) 28%) border-box;
            mask:
                linear-gradient(transparent, transparent),
                linear-gradient(#000, #000);
            mask-clip: padding-box, border-box;
            mask-composite: subtract;
            opacity: .65;
            animation: albumHelpShineTop 2.2s ease-in-out infinite;
        }

        .album-help-shine-bottom {
            transform: rotate(180deg);
            animation: albumHelpShineBottom 2.5s ease-in-out infinite;
        }

        .album-help-glow {
            width: 72%;
            aspect-ratio: 1 / 1;
            border: 10px solid transparent;
            inset: -38px -34px auto auto;
            background:
                conic-gradient(from -45deg, rgba(244, 114, 182, 0) 4%, rgba(244, 114, 182, .9) 18%, rgba(129, 140, 248, .8) 27%, rgba(129, 140, 248, 0) 42%) border-box;
            filter: blur(10px) saturate(1.2);
            opacity: .55;
            mix-blend-mode: screen;
            animation: albumHelpGlowPulse 2.8s ease-in-out infinite;
        }

        .album-help-glow-bottom {
            inset: auto auto -38px -34px;
            transform: rotate(180deg);
            animation-delay: .2s;
        }

        .album-help-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 18px 10px;
        }

        .album-help-title {
            margin: 0;
            color: #111827;
            font-size: 1.15rem;
            letter-spacing: .2px;
            font-weight: 700;
        }

        .album-help-close {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, .15);
            background: rgba(0, 0, 0, .06);
            color: #374151;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .album-help-body {
            padding: 6px 18px 20px;
        }

        .album-help-body p {
            margin-bottom: .7rem;
            color: #111827;
            line-height: 1.6;
            font-weight: 600;
        }

        .album-help-body p:last-child {
            margin-bottom: 0;
        }

        @keyframes albumHelpShineTop {
            0%, 100% { opacity: .3; filter: blur(.2px); }
            35% { opacity: .85; filter: blur(0); }
            60% { opacity: .45; }
        }

        @keyframes albumHelpShineBottom {
            0%, 100% { opacity: .28; filter: blur(.2px); }
            40% { opacity: .75; filter: blur(0); }
            70% { opacity: .42; }
        }

        @keyframes albumHelpGlowPulse {
            0%, 100% { opacity: .35; transform: scale(1); }
            45% { opacity: .72; transform: scale(1.03); }
            75% { opacity: .46; transform: scale(1.01); }
        }

        @keyframes reward-spin {
            0% {
                --reward-rotate: 0deg;
            }

            100% {
                --reward-rotate: 360deg;
            }
        }

        .solar-progress-wrap {
            margin-bottom: .75rem;
            position: relative;
        }

        .loyalty-chart {
            perspective: 1000px;
            perspective-origin: 50% 50%;
        }

        .loyalty-bar {
            --progress-value: 0%;
            --progress-start: #dc2626;
            --progress-end: #ef4444;
            position: relative;
            font-size: 16px;
            height: 7.5em;
            transform: rotateX(60deg);
            transform-style: preserve-3d;
        }

        .loyalty-bar .face {
            position: relative;
            width: 100%;
            height: 2em;
            background: rgba(255, 255, 255, 0.35);
        }

        .loyalty-bar .side-a,
        .loyalty-bar .side-b {
            width: 2em;
        }

        .loyalty-bar .side-a {
            transform: rotateX(90deg) rotateY(-90deg) translateX(2em) translateY(1em) translateZ(1em);
        }

        .loyalty-bar .side-b {
            position: absolute;
            right: 0;
            transform: rotateX(90deg) rotateY(-90deg) translateX(4em) translateY(1em) translateZ(-1em);
        }

        .loyalty-bar .side-0 {
            transform: rotateX(90deg) rotateY(0deg) translateX(0) translateY(1em) translateZ(-1em);
        }

        .loyalty-bar .side-1 {
            transform: rotateX(90deg) rotateY(0deg) translateX(0) translateY(1em) translateZ(3em);
        }

        .loyalty-bar .top {
            transform: rotateX(0deg) rotateY(0deg) translateX(0) translateY(4em) translateZ(2em);
        }

        .loyalty-bar .floor {
            box-shadow: 0 .12em .8em rgba(0, 0, 0, .25), .5em -.5em 3em rgba(0, 0, 0, .18), 1em -1em 8em rgba(118, 201, 0, .35);
        }

        .loyalty-bar .growing-bar {
            width: var(--progress-value);
            height: 2em;
            background: linear-gradient(90deg, var(--progress-start) 0%, var(--progress-end) 50%, var(--progress-start) 100%);
            background-size: 200% 100%;
            transition: width .35s ease-in-out;
            will-change: background-position, filter, box-shadow, opacity;
            backface-visibility: hidden;
            transform: translateZ(0);
            animation: loyaltyProgressFlow 4s linear infinite, loyaltyProgressPump 3.6s cubic-bezier(0.42, 0, 0.58, 1) infinite;
        }

        .loyalty-bar .side-a,
        .loyalty-bar .growing-bar {
            background-color: var(--progress-start);
        }

        .loyalty-bar .side-0 .growing-bar {
            box-shadow: -0.5em -1.5em 4em color-mix(in srgb, var(--progress-end), transparent 45%);
        }

        .loyalty-bar .floor .growing-bar {
            box-shadow: 0 0 2.5em color-mix(in srgb, var(--progress-end), transparent 50%);
        }

        .progress-percent-text {
            position: absolute;
            right: 12px;
            top: 52%;
            transform: translateY(-50%);
            font-family: 'Bangers', cursive;
            font-size: clamp(1.2rem, 2.2vw, 1.7rem);
            letter-spacing: .5px;
            color: #111827;
            text-shadow: 0 1px 0 rgba(255, 255, 255, .25);
            z-index: 4;
            pointer-events: none;
        }

        @keyframes loyaltyProgressFlow {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 200% 50%;
            }
        }

        @keyframes loyaltyProgressPump {
            0%, 100% {
                filter: saturate(1) brightness(1);
                opacity: .98;
                box-shadow: 0 0 0 rgba(0, 0, 0, 0);
            }

            50% {
                filter: saturate(1.08) brightness(1.04);
                opacity: 1;
                box-shadow: 0 0 18px color-mix(in srgb, var(--progress-end), transparent 65%);
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        @php
                            $waUrl = isset($businessSettings) ? ($businessSettings['whatsapp_url'] ?? null) : null;
                            if (!$waUrl && isset($businessSettings) && !empty($businessSettings['whatsapp_number'])) {
                                $waUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $businessSettings['whatsapp_number']);
                            }
                        @endphp
                        @if (!empty($loyaltyOffline))
                            <div class="mb-3">
                                <h2 class="fw-bold mb-2 album-title">Mi album Tcocina</h2>
                            </div>

                            <div class="alert alert-warning border-0 shadow-sm mb-3">
                                <h5 class="fw-bold mb-2">Sistema de Mi album temporalmente apagado</h5>
                                <p class="mb-0">
                                    {{ $loyaltyOfflineMessage ?? 'Por el momento Mi álbum no está disponible. Intenta nuevamente más tarde.' }}
                                </p>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('catalog') }}" class="btn btn-beach-primary">Ir a comprar</a>
                                @if ($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                                        Consultar por WhatsApp
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="mb-3">
                                <h2 class="fw-bold mb-2 album-title">Bienvenido a tu album Tcocina</h2>
                            </div>

                        @if (isset($setting))
                        <div class="reward-info-card text-center">
                            <h3 class="fw-bold">Premio del dia</h3>
                            <div class="reward-meta">
                                <h5>{{ $setting->reward_value }}</h5>
                                @if (isset($wallet))
                                    @php $infoPillRemaining = max(0, $setting->target_stickers - $wallet->current_stickers); @endphp
                                    @if ($infoPillRemaining === 0)
                                        <p class="small mb-0 fw-semibold" style="color: #10b981;">
                                            <i class="fas fa-check-circle me-1"></i>¡Álbum completo, ya podés canjearlo!
                                        </p>
                                    @else
                                        <div class="d-inline-flex align-items-center gap-1 mt-1 px-3 py-1"
                                             style="background: rgba(245,158,11,0.10); border: 1px solid rgba(245,158,11,0.35); border-radius: 999px; color: #92400e; font-size: .78rem; font-weight: 600;">
                                            <i class="fas fa-lock" style="font-size: .7rem;"></i>
                                            Te faltan <strong style="margin: 0 2px;">{{ $infoPillRemaining }}</strong> {{ $infoPillRemaining === 1 ? 'figurita' : 'figuritas' }} para ganarlo
                                        </div>
                                    @endif
                                @else
                                    <div class="d-inline-flex align-items-center gap-1 mt-1 px-3 py-1"
                                         style="background: rgba(245,158,11,0.10); border: 1px solid rgba(245,158,11,0.35); border-radius: 999px; color: #92400e; font-size: .78rem; font-weight: 600;">
                                        <i class="fas fa-lock" style="font-size: .7rem;"></i>
                                        Completá el álbum para ganarlo
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="reward-card mb-4">
                            <div class="reward-card-inner">
                                @if (!empty($setting->reward_image))
                                    @php $rewardImageUrl = asset('images/' . $setting->reward_image); @endphp
                                    <div class="reward-image-container">
                                        <img class="reward-image" src="{{ $rewardImageUrl }}" alt="{{ $setting->reward_value }}"
                                            data-lightbox="{{ $rewardImageUrl }}" data-lightbox-title="{{ $setting->reward_value }}"
                                            onerror="this.onerror=null; this.src='{{ asset('images/log.png') }}'; this.dataset.lightbox='{{ asset('images/log.png') }}';">
                                        @if (isset($wallet) && isset($setting))
                                            @php $pillRemaining = max(0, $setting->target_stickers - $wallet->current_stickers); @endphp
                                            @if ($pillRemaining === 0)
                                                <div class="reward-progress-pill reward-progress-pill--done">
                                                    <i class="fas fa-check-circle me-1"></i>¡Álbum completo!
                                                </div>
                                            @else
                                                <div class="reward-progress-pill">
                                                    <i class="fas fa-lock me-1"></i>Faltan {{ $pillRemaining }} {{ $pillRemaining === 1 ? 'figurita' : 'figuritas' }}
                                                </div>
                                            @endif
                                        @endif
                                        <div class="reward-lightbox-overlay">
                                            <i class="fas fa-search-plus"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="reward-image-container">
                                        <img class="reward-image" src="{{ asset('images/log.png') }}" alt="Premio vigente"
                                            data-lightbox="{{ asset('images/log.png') }}" data-lightbox-title="Premio vigente">
                                        @if (isset($wallet) && isset($setting))
                                            @php $pillRemaining = max(0, $setting->target_stickers - $wallet->current_stickers); @endphp
                                            @if ($pillRemaining === 0)
                                                <div class="reward-progress-pill reward-progress-pill--done">
                                                    <i class="fas fa-check-circle me-1"></i>¡Álbum completo!
                                                </div>
                                            @else
                                                <div class="reward-progress-pill">
                                                    <i class="fas fa-lock me-1"></i>Faltan {{ $pillRemaining }} {{ $pillRemaining === 1 ? 'figurita' : 'figuritas' }}
                                                </div>
                                            @endif
                                        @endif
                                        <div class="reward-lightbox-overlay">
                                            <i class="fas fa-search-plus"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if (isset($wallet) && isset($setting))
                            @php
                                $progressPercent = min(100, ($wallet->current_stickers / max(1, $setting->target_stickers)) * 100);
                                $progressHue = (int) round(($progressPercent / 100) * 120); // 0:red -> 120:green
                                $progressHue = max(0, min(120, $progressHue));
                                $progressHueEnd = min(120, $progressHue + 16);
                                $progressStartColor = "hsl({$progressHue} 90% 46%)";
                                $progressEndColor = "hsl({$progressHueEnd} 92% 56%)";
                                $defaultAlbumHelp = 'Con {target_stickers} figuritas canjeás el premio del día (ver arriba en esta vista). Retiro en local o envío a tu cargo.';
                                $albumHelpTemplate = trim((string) ($setting->album_help_message ?? ''));
                                if ($albumHelpTemplate === '') {
                                    $albumHelpTemplate = $defaultAlbumHelp;
                                }
                                $albumHelpMessage = str_replace('{target_stickers}', (string) $setting->target_stickers, $albumHelpTemplate);
                            @endphp
                            <div class="solar-progress-wrap" role="progressbar" aria-label="Progreso de soles"
                                aria-valuenow="{{ (int) $progressPercent }}"
                                aria-valuemin="0" aria-valuemax="100">
                                <div class="loyalty-chart">
                                    <div class="loyalty-bar white" style="--progress-value: {{ $progressPercent }}%; --progress-start: {{ $progressStartColor }}; --progress-end: {{ $progressEndColor }};">
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
                                <div class="progress-percent-text">{{ (int) round($progressPercent) }}%</div>
                            </div>
                        @endif

                        @if (isset($wallet) && isset($setting))
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-0">Mi progreso</h3>
                                    @if ($wallet->current_stickers < $setting->target_stickers)
                                        <small style="color: #9ca3af; font-size: .72rem; line-height: 1.3;">Completá el álbum para ganar el premio</small>
                                    @endif
                                </div>
                                <span class="badge text-bg-dark">{{ $wallet->current_stickers }}/{{ $setting->target_stickers }}</span>
                            </div>

                            @php
                                $filled = min($wallet->current_stickers, $setting->target_stickers);
                                $stickerLogo = isset($businessSettings) && isset($businessSettings['brand_logo_url']) ? $businessSettings['brand_logo_url'] : asset('images/log.png');
                                if (!str_starts_with($stickerLogo, 'http')) {
                                    $stickerLogo = asset(ltrim($stickerLogo, '/'));
                                }
                            @endphp
                        @endif

                        {{-- Mensaje informativo sobre el canje en proceso --}}
                        @if (isset($pendingRedemption) && $pendingRedemption)
                            <div class="alert alert-warning text-center mb-3" style="border: none; border-radius: 10px;">
                                <i class="fas fa-hourglass-half me-2"></i>
                                <strong>Tu canje está en proceso</strong><br>
                                <small class="text-muted">Ya usaste tus figuritas para solicitar este premio. Te notificaremos cuando esté listo.</small>
                            </div>
                        @endif

                        @if (isset($setting) && isset($filled))
                            <div class="stickers-grid mb-4">
                                @for ($i = 1; $i <= $setting->target_stickers; $i++)
                                    <span class="sticker-slot {{ $i <= $filled ? 'filled' : 'empty' }}">
                                        <img src="{{ $stickerLogo }}" alt="Sol"
                                            onerror="this.onerror=null; this.src='{{ asset('images/log.png') }}';">
                                    </span>
                                @endfor
                            </div>
                        @endif

                        {{-- Banner de canje en proceso (llamativo) --}}
                        @if (isset($pendingRedemption) && $pendingRedemption)
                            <div class="card border-0 shadow-lg mb-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; overflow: hidden;">
                                <div class="card-body p-4 text-center text-white position-relative">
                                    {{-- Icono decorativo de fondo --}}
                                    <div style="position: absolute; top: -20px; right: -20px; font-size: 120px; opacity: 0.15;">🎁</div>

                                    <div class="position-relative" style="z-index: 1;">
                                        <div style="font-size: 48px; margin-bottom: 8px;">🎉</div>
                                        <h3 class="fw-bold mb-2" style="font-size: 1.6rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                            ¡Solicitud de Canje enviada!
                                        </h3>
                                        <p class="mb-3" style="font-size: 1.05rem; opacity: 0.95;">
                                            Tu álbum está completo y ya solicitaste el canje.
                                        </p>

                                        <div class="bg-white bg-opacity-25 rounded-3 p-3 mb-3" style="backdrop-filter: blur(4px);">
                                            <div class="small text-uppercase" style="opacity: 0.9; letter-spacing: 0.5px;">Premio solicitado</div>
                                            <div class="fw-bold" style="font-size: 1.25rem;">
                                                {{ $pendingRedemption->reward_snapshot['reward_value'] ?? (isset($setting) ? $setting->reward_value : 'Premio') }}
                                            </div>
                                        </div>

                                        @php
                                            $statusLabels = [
                                                'pending' => ['text' => 'Pendiente de aprobación', 'color' => '#fbbf24', 'icon' => '⏳'],
                                                'approved' => ['text' => 'Aprobado', 'color' => '#60a5fa', 'icon' => '✅'],
                                                'delivered' => ['text' => 'Entregado', 'color' => '#34d399', 'icon' => '🎁'],
                                                'cancelled' => ['text' => 'Cancelado', 'color' => '#f87171', 'icon' => '❌'],
                                            ];
                                            $statusInfo = $statusLabels[$pendingRedemption->status] ?? ['text' => 'En proceso', 'color' => '#fbbf24', 'icon' => '⏳'];
                                            $couponCode = $pendingRedemption->reward_snapshot['coupon_code'] ?? null;
                                        @endphp

                                        {{-- Estado de la solicitud --}}
                                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mb-3" style="background: rgba(0,0,0,0.25);">
                                            <span style="font-size: 1.2rem;">{{ $statusInfo['icon'] }}</span>
                                            <span class="fw-semibold" style="color: {{ $statusInfo['color'] }};">
                                                Estado: {{ $statusInfo['text'] }}
                                            </span>
                                        </div>

                                        {{-- Acción según tipo de premio --}}
                                        @if ($pendingRedemption->status === 'approved')
                                            @php
                                                $rewardCategory = $pendingRedemption->reward_snapshot['reward_category'] ?? 'other';
                                                $couponCode = $pendingRedemption->reward_snapshot['coupon_code'] ?? null;
                                                $rewardValue = $pendingRedemption->reward_snapshot['reward_value'] ?? 'tu premio';
                                                $whatsappSetting = App\Models\BusinessSetting::where('key', 'whatsapp_number')->first();
                                                $whatsappNumber = $whatsappSetting ? $whatsappSetting->value : null;
                                            @endphp

                                            @if ($rewardCategory === 'coupon' && $couponCode)
                                                {{-- Cupón de descuento: mostrar código --}}
                                                <div class="mt-3">
                                                    <p class="small mb-2" style="opacity: 0.9;">Tu código de descuento:</p>
                                                    <div class="d-flex align-items-center justify-content-center gap-2 bg-white rounded-3 p-3" style="box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                                                        <span id="couponCode" class="fw-bold text-dark" style="font-size: 1.5rem; letter-spacing: 2px; font-family: monospace;">{{ $couponCode }}</span>
                                                        <button type="button" class="btn btn-sm btn-dark" onclick="copyCouponCode()" style="border-radius: 8px;">
                                                            <i class="fas fa-copy me-1"></i> Copiar
                                                        </button>
                                                    </div>
                                                    <p class="small mt-2" style="opacity: 0.8;">Usalo al finalizar tu próximo pedido</p>
                                                </div>
                                            @else
                                                {{-- Premio físico u otro: mostrar botón de WhatsApp --}}
                                                <div class="mt-3">
                                                    <p class="small mb-2" style="opacity: 0.9;">Tu premio está listo para retirar</p>
                                                    @if ($whatsappNumber)
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}?text={{ urlencode('Hola! Acabo de completar mi álbum y quería solicitar el canje de mi premio: ' . $rewardValue) }}"
                                                           target="_blank"
                                                           class="btn btn-success w-100"
                                                           style="border-radius: 10px; background: #25D366; border: none;">
                                                            <i class="fab fa-whatsapp me-2"></i>Contactar por WhatsApp
                                                        </a>
                                                        <p class="small mt-2" style="opacity: 0.8;">Click para coordinar retiro</p>
                                                    @else
                                                        <p class="small" style="opacity: 0.8;">Contactate con nosotros para coordinar el retiro de tu premio.</p>
                                                    @endif
                                                </div>
                                            @endif
                                        @elseif ($pendingRedemption->status === 'approved' && $pendingRedemption->approved_at)
                                            <div class="mt-3 small" style="opacity: 0.9;">
                                                Aprobado el {{ $pendingRedemption->approved_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (isset($pendingRedemption) && !$pendingRedemption)
                            <div class="alert {{ isset($canRedeem) && $canRedeem ? 'alert-success' : 'alert-secondary' }} mb-4 d-flex align-items-center justify-content-between gap-2">
                                <div>
                                    @if (isset($canRedeem) && $canRedeem && isset($setting))
                                        Canje habilitado. Premio actual: <strong>{{ $setting->reward_value }}</strong>.
                                    @elseif (isset($setting) && isset($wallet))
                                        Te faltan <strong>{{ max(0, $setting->target_stickers - $wallet->current_stickers) }}</strong> soles para canjear.
                                    @endif
                                </div>

                                @if (isset($canRedeem) && !$canRedeem)
                                    <a href="{{ route('catalog') }}" class="btn btn-sm btn-beach-primary text-nowrap">
                                        Ir a comprar
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if (isset($canRedeem) && $canRedeem)
                            <form method="POST" action="{{ route('loyalty.redeem.request') }}">
                                @csrf
                                <button type="submit" class="btn btn-beach-primary">Solicitar canje</button>
                            </form>
                        @endif

                        {{-- Review Section --}}
                        @php
                            $userReview = auth()->check() ? \App\Models\Review::where('user_id', auth()->id())->first() : null;
                        @endphp
                        <div class="mt-4 pt-4 border-top">
                            <div class="reward-info-card text-center">
                                <h3 class="fw-bold mb-2">
                                    <i class="fas fa-star text-warning me-2"></i>Reseña de la Web
                                </h3>
                                @if ($userReview)
                                    <div class="d-flex align-items-center justify-content-center gap-2 text-success">
                                        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                                        <span class="fw-semibold">¡Ya dejaste tu reseña!</span>
                                    </div>
                                    <div class="mt-2">
                                        <div class="text-warning mb-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $userReview->rating)
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        @if ($userReview->comment)
                                            <p class="small text-muted mb-0">{{ Str::limit($userReview->comment, 100) }}</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="d-flex align-items-center justify-content-center gap-2 text-warning mb-2">
                                        <i class="fas fa-clock" style="font-size: 1.2rem;"></i>
                                        <span class="fw-semibold">Pendiente</span>
                                    </div>
                                    <p class="small text-muted mb-3">Ayudanos a mejorar dejando tu opinión sobre la web.</p>
                                    <button type="button" id="openReviewModalBtn" class="btn btn-outline-primary btn-sm" style="border-radius: 20px;">
                                        <i class="far fa-star me-2"></i>Dejar reseña de la web
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Historial de canjes --}}
                        @if (isset($redemptionHistory) && $redemptionHistory->count() > 0)
                            <div class="mt-5 pt-4 border-top">
                                <h5 class="mb-3 text-center" style="color: #4b5563;">
                                    <i class="fas fa-history me-2"></i>Tus canjes completados
                                </h5>
                                <div class="list-group" style="border-radius: 12px; overflow: hidden;">
                                    @foreach ($redemptionHistory as $historyItem)
                                        <div class="list-group-item d-flex justify-content-between align-items-center p-3" style="border-left: 4px solid #10b981;">
                                            <div>
                                                <div class="fw-semibold" style="color: #111827;">
                                                    {{ $historyItem->reward_snapshot['reward_value'] ?? 'Premio' }}
                                                </div>
                                                <small class="text-muted">
                                                    @if (($historyItem->reward_snapshot['reward_category'] ?? 'other') === 'coupon')
                                                        <span class="badge bg-info text-dark me-1">Cupón</span>
                                                    @elseif (($historyItem->reward_snapshot['reward_category'] ?? 'other') === 'physical')
                                                        <span class="badge bg-warning text-dark me-1">Premio físico</span>
                                                    @endif
                                                    Entregado el {{ $historyItem->delivered_at ? $historyItem->delivered_at->format('d/m/Y') : 'Fecha no disponible' }}
                                                </small>
                                            </div>
                                            <span class="badge bg-success rounded-pill">
                                                <i class="fas fa-check me-1"></i>Canjeado
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @endif

                            <div class="mt-4 text-center">
                                <button type="button" id="albumHelpOpen" class="btn btn-outline-primary album-help-btn">
                                    Como funciona el álbum de Tcocina?
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="lightboxModal" class="lightbox-modal">
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img id="lightboxImage" class="lightbox-img" src="" alt="">
            <div id="lightboxTitle" class="lightbox-title"></div>
            <div class="lightbox-album-note">
                <i class="fas fa-lock" style="font-size:.7rem;"></i>
                Completá el álbum para ganar este premio
            </div>
        </div>
    </div>

    @if (empty($loyaltyOffline))
        <div id="albumHelpModal" class="album-help-modal" aria-hidden="true">
            <div class="album-help-panel" role="dialog" aria-modal="true" aria-labelledby="albumHelpTitle">
                <span class="album-help-shine album-help-shine-top"></span>
                <span class="album-help-shine album-help-shine-bottom"></span>
                <span class="album-help-glow album-help-glow-top"></span>
                <span class="album-help-glow album-help-glow-bottom"></span>
                <div class="album-help-head">
                    <h4 id="albumHelpTitle" class="album-help-title">Como funciona el álbum de Tcocina?</h4>
                    <button type="button" id="albumHelpClose" class="album-help-close" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="album-help-body">
                    <p>
                        1 combo = 1 figurita al confirmar tu pedido. Se marca en tu álbum y suma al progreso.
                    </p>
                    <p>
                        {!! nl2br(e($albumHelpMessage)) !!}
                    </p>
                    @if ($waUrl)
                    <p>
                        ¿Dudas? Escribinos por <a href="{{ $waUrl }}" target="_blank" rel="noopener" style="color: var(--beach-primary, #00b4d8); font-weight: 700;">WhatsApp</a>.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Review Modal -->
    <div id="reviewModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="border: none; padding: 20px 24px;">
                    <h5 class="modal-title fw-bold" style="color: #202124; font-family: 'Google Sans', Roboto, Arial, sans-serif;">
                        ¿Qué tal tu experiencia?
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 1.5rem;"></button>
                </div>
                <div class="modal-body" style="padding: 0 24px 24px;">
                    <p style="color: #5f6368; font-family: 'Google Sans', Roboto, Arial, sans-serif; font-size: 14px; margin-bottom: 16px;">
                        Tu opinión nos ayuda a mejorar. Califica tu experiencia de compra en la web (opcional).
                    </p>
                    <div class="mb-3">
                        <div id="starRating" style="display: flex; gap: 8px; justify-content: center; margin-bottom: 16px;">
                            <i class="far fa-star star-icon" data-rating="1" style="font-size: 32px; color: #dadce0; cursor: pointer; transition: color 0.2s;"></i>
                            <i class="far fa-star star-icon" data-rating="2" style="font-size: 32px; color: #dadce0; cursor: pointer; transition: color 0.2s;"></i>
                            <i class="far fa-star star-icon" data-rating="3" style="font-size: 32px; color: #dadce0; cursor: pointer; transition: color 0.2s;"></i>
                            <i class="far fa-star star-icon" data-rating="4" style="font-size: 32px; color: #dadce0; cursor: pointer; transition: color 0.2s;"></i>
                            <i class="far fa-star star-icon" data-rating="5" style="font-size: 32px; color: #dadce0; cursor: pointer; transition: color 0.2s;"></i>
                        </div>
                        <input type="hidden" id="reviewRating" name="rating" value="">
                    </div>
                    <div class="mb-3">
                        <textarea id="reviewComment" class="form-control" rows="3" placeholder="Cuéntanos más sobre tu experiencia (opcional)" style="border: 1px solid #dadce0; border-radius: 8px; font-family: 'Google Sans', Roboto, Arial, sans-serif; font-size: 14px; resize: none;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border: none; padding: 16px 24px 24px;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="color: #5f6368; font-family: 'Google Sans', Roboto, Arial, sans-serif; font-size: 14px; font-weight: 500;">
                        Cancelar
                    </button>
                    <button type="button" id="submitReviewBtn" class="btn" style="background: #1a73e8; color: white; font-family: 'Google Sans', Roboto, Arial, sans-serif; font-size: 14px; font-weight: 500; padding: 8px 24px; border-radius: 20px; border: none;">
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successReviewModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-body text-center" style="padding: 32px;">
                    <div style="margin-bottom: 16px;">
                        <i class="fas fa-check-circle" style="font-size: 56px; color: #28a745;"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color: #202124; font-family: 'Google Sans', Roboto, Arial, sans-serif;">¡Reseña enviada!</h5>
                    <p style="color: #5f6368; font-family: 'Google Sans', Roboto, Arial, sans-serif; font-size: 14px; margin-bottom: 24px;">
                        Muchas gracias por tu opinión. Nos ayuda a mejorar cada día.
                    </p>
                    <button type="button" id="closeSuccessBtn" class="btn" style="background: #28a745; color: white; font-family: 'Google Sans', Roboto, Arial, sans-serif; font-size: 14px; font-weight: 500; padding: 8px 24px; border-radius: 20px; border: none;">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('lightboxModal');
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxTitle = document.getElementById('lightboxTitle');
            const closeBtn = document.querySelector('.lightbox-close');
            const albumHelpModal = document.getElementById('albumHelpModal');
            const albumHelpOpen = document.getElementById('albumHelpOpen');
            const albumHelpClose = document.getElementById('albumHelpClose');

            // Review Modal Logic
            const reviewModal = document.getElementById('reviewModal');
            const successReviewModal = document.getElementById('successReviewModal');
            const openReviewModalBtn = document.getElementById('openReviewModalBtn');
            const submitReviewBtn = document.getElementById('submitReviewBtn');
            const closeSuccessBtn = document.getElementById('closeSuccessBtn');
            const starIcons = document.querySelectorAll('.star-icon');
            const reviewRatingInput = document.getElementById('reviewRating');
            const reviewCommentInput = document.getElementById('reviewComment');

            function showModal(el) {
                if (el.classList.contains('modal')) {
                    const bs = new bootstrap.Modal(el);
                    bs.show();
                } else {
                    el.classList.add('show');
                    el.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }
            function hideModal(el) {
                if (el.classList.contains('modal')) {
                    const bs = bootstrap.Modal.getInstance(el);
                    if (bs) bs.hide();
                } else {
                    el.classList.remove('show');
                    el.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }

            if (openReviewModalBtn && reviewModal) {
                openReviewModalBtn.addEventListener('click', function() {
                    showModal(reviewModal);
                });
            }

            if (starIcons.length > 0) {
                starIcons.forEach(star => {
                    star.addEventListener('mouseenter', function() {
                        const rating = this.getAttribute('data-rating');
                        highlightStars(rating);
                    });

                    star.addEventListener('mouseleave', function() {
                        const currentRating = reviewRatingInput.value;
                        highlightStars(currentRating);
                    });

                    star.addEventListener('click', function() {
                        const rating = this.getAttribute('data-rating');
                        reviewRatingInput.value = rating;
                        highlightStars(rating);
                    });
                });
            }

            function highlightStars(rating) {
                starIcons.forEach(star => {
                    const starRating = star.getAttribute('data-rating');
                    if (starRating <= rating) {
                        star.classList.remove('far');
                        star.classList.add('fas');
                        star.style.color = '#fbbc04';
                    } else {
                        star.classList.remove('fas');
                        star.classList.add('far');
                        star.style.color = '#dadce0';
                    }
                });
            }

            if (submitReviewBtn) {
                submitReviewBtn.addEventListener('click', async function() {
                    if (!reviewRatingInput.value) {
                        alert('Por favor selecciona una calificación de estrellas.');
                        return;
                    }

                    const rating = reviewRatingInput.value;
                    const comment = reviewCommentInput.value;

                    // Ocultar review modal mientras se envía
                    hideModal(reviewModal);

                    try {
                        const response = await fetch('/reviews', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                rating: rating,
                                comment: comment
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            showModal(successReviewModal);
                        } else {
                            alert(data.message || 'Error al enviar la reseña.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al enviar la reseña. Por favor intenta nuevamente.');
                    }
                });
            }

            if (closeSuccessBtn && successReviewModal) {
                closeSuccessBtn.addEventListener('click', function() {
                    hideModal(successReviewModal);
                    location.reload();
                });
            }

            function openLightbox(imageSrc, title) {
                lightboxImage.src = imageSrc;
                lightboxImage.alt = title || 'Premio';
                lightboxTitle.textContent = title || 'Premio';
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }

            function openAlbumHelpModal() {
                if (!albumHelpModal) return;
                albumHelpModal.classList.add('show');
                albumHelpModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeAlbumHelpModal() {
                if (!albumHelpModal) return;
                albumHelpModal.classList.remove('show');
                albumHelpModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = 'auto';
            }

            if (albumHelpOpen) {
                albumHelpOpen.addEventListener('click', openAlbumHelpModal);
            }
            if (albumHelpClose) {
                albumHelpClose.addEventListener('click', closeAlbumHelpModal);
            }

            document.addEventListener('click', function(e) {
                const image = e.target.closest('.reward-image');
                const overlay = e.target.closest('.reward-lightbox-overlay');

                if (image || overlay) {
                    const imgEl = image || e.target.closest('.reward-image-container')?.querySelector('.reward-image');
                    if (imgEl) {
                        openLightbox(imgEl.dataset.lightbox || imgEl.src, imgEl.dataset.lightboxTitle || imgEl.alt);
                    }
                }

                if (e.target === modal || e.target === closeBtn) {
                    closeLightbox();
                }

                if (e.target === albumHelpModal) {
                    closeAlbumHelpModal();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    closeLightbox();
                }
                if (e.key === 'Escape' && albumHelpModal && albumHelpModal.classList.contains('show')) {
                    closeAlbumHelpModal();
                }
            });
        });

        // Función para copiar código de cupón
        function copyCouponCode() {
            const codeElement = document.getElementById('couponCode');
            if (!codeElement) return;

            const code = codeElement.textContent.trim();

            navigator.clipboard.writeText(code).then(function() {
                // Mostrar feedback visual
                const button = document.querySelector('button[onclick="copyCouponCode()"]');
                if (button) {
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check me-1"></i> Copiado';
                    button.classList.remove('btn-dark');
                    button.classList.add('btn-success');

                    setTimeout(function() {
                        button.innerHTML = originalHTML;
                        button.classList.remove('btn-success');
                        button.classList.add('btn-dark');
                    }, 2000);
                }
            }).catch(function(err) {
                console.error('Error al copiar:', err);
                // Fallback para navegadores que no soportan clipboard API
                const textArea = document.createElement('textarea');
                textArea.value = code;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    const button = document.querySelector('button[onclick="copyCouponCode()"]');
                    if (button) {
                        const originalHTML = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check me-1"></i> Copiado';
                        setTimeout(function() {
                            button.innerHTML = originalHTML;
                        }, 2000);
                    }
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                }
                document.body.removeChild(textArea);
            });
        }
    </script>
@endpush
