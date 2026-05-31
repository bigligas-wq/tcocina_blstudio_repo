@extends('layouts.admin')

@section('title', 'Turnos — TCocina Admin')
@section('page-title', 'Gestión de Turnos')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 gap-3 flex-wrap">
        <div>
            <h4 class="fw-bold mb-0" style="color:#0a2540;">
                <i class="fas fa-clock me-2" style="color:var(--brand-primary,#00b4d8);"></i>Turnos de Entrega
            </h4>
            <p class="text-muted mb-0" style="font-size:.82rem;">
                {{ ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }}
            </p>
        </div>

        {{-- Toggle Sistema de Turnos --}}
        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 border bg-white shadow-sm">
            <div>
                <div class="fw-semibold" style="font-size:.85rem;color:#0a2540;">Sistema de Turnos</div>
                <div class="text-muted" style="font-size:.72rem;" id="skipTurnoSubtext">
                    {{ $systemTurnosEnabled ? '4 pasos: Carrito → Turnos → Checkout → Confirmación' : '3 pasos: Carrito → Checkout → Confirmación' }}
                </div>
            </div>
            <button type="button" id="skipTurnoSwitch"
                class="btn btn-sm fw-bold px-3"
                style="min-width:56px; background:{{ $systemTurnosEnabled ? '#16a34a' : '#dc2626' }}; color:#fff; border:none; border-radius:8px; transition:background .2s;">
                {{ $systemTurnosEnabled ? 'ON' : 'OFF' }}
            </button>
        </div>
    </div>

    {{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-left:4px solid #00b4d8 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:44px;height:44px;background:rgba(0,180,216,.1);">
                        <i class="fas fa-clock" style="color:#00b4d8;font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Slots totales</div>
                        <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#0a2540;">{{ $estadisticas['total_microturnos'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-left:4px solid #16a34a !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:44px;height:44px;background:rgba(22,163,74,.1);">
                        <i class="fas fa-check-circle" style="color:#16a34a;font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Disponibles</div>
                        <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#0a2540;">{{ $estadisticas['microturnos_disponibles'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-left:4px solid #dc2626 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:44px;height:44px;background:rgba(220,38,38,.1);">
                        <i class="fas fa-ban" style="color:#dc2626;font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Llenos</div>
                        <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#0a2540;">{{ $estadisticas['microturnos_llenos'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-left:4px solid #f59e0b !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:44px;height:44px;background:rgba(245,158,11,.1);">
                        <i class="fas fa-chart-pie" style="color:#f59e0b;font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Ocupación</div>
                        <div class="fw-bold" style="font-size:1.5rem;line-height:1;color:#0a2540;">{{ $estadisticas['porcentaje_ocupacion'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Slots de Hoy ────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-calendar-day" style="color:var(--brand-primary,#00b4d8);"></i>
                <span class="fw-semibold" style="color:#0a2540;">Slots de hoy</span>
                <span class="badge rounded-pill ms-1" style="background:rgba(0,180,216,.12);color:#0369a1;font-size:.72rem;">
                    {{ ucfirst(now()->locale('es')->dayName) }}
                </span>
            </div>
        </div>

        <div class="card-body">
            @if ($microturnosHoy->count() > 0)
                <div class="row g-3">
                    @foreach ($microturnosHoy as $microturno)
                        @php
                            $productos  = $microturno->getProductosActivos();
                            $cfg        = \App\Models\WeeklyTurnoConfig::getConfigForDay($microturno->getDayOfWeek());
                            $disponible = $microturno->getIsDisponibleAttribute();

                            $hPct = $cfg->max_hamburguesas > 0
                                ? round($productos['hamburguesas'] / $cfg->max_hamburguesas * 100) : 0;
                            $aPct = $cfg->max_acompañamientos > 0
                                ? round($productos['acompañamientos'] / $cfg->max_acompañamientos * 100) : 0;
                            $ocupPct = max($hPct, $aPct);

                            $barColor = $ocupPct < 40 ? '#16a34a'
                                : ($ocupPct < 70 ? '#ca8a04'
                                : ($ocupPct < 90 ? '#ea580c' : '#dc2626'));

                            $borderColor = $disponible ? $barColor : '#dc2626';
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="rounded-3 border p-3 h-100"
                                style="border-color:{{ $borderColor }}!important; background:{{ $disponible ? 'rgba(0,0,0,.02)' : 'rgba(220,38,38,.04)' }};">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold" style="font-size:.88rem;color:#0a2540;">
                                        {{ $microturno->getFormattedTimeAttribute() }}
                                    </span>
                                    @if ($disponible)
                                        <span class="badge" style="background:#dcfce7;color:#15803d;font-size:.68rem;">libre</span>
                                    @else
                                        <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.68rem;">lleno</span>
                                    @endif
                                </div>

                                {{-- Hamburguesas --}}
                                <div class="mb-1">
                                    <div class="d-flex justify-content-between" style="font-size:.72rem;color:#6b7280;margin-bottom:2px;">
                                        <span><i class="fas fa-hamburger me-1"></i>Hamburguesas</span>
                                        <span class="fw-semibold">{{ $productos['hamburguesas'] }}/{{ $cfg->max_hamburguesas }}</span>
                                    </div>
                                    <div class="rounded-pill overflow-hidden" style="height:5px;background:#e5e7eb;">
                                        <div class="rounded-pill" style="height:100%;width:{{ $hPct }}%;background:{{ $barColor }};transition:width .3s;"></div>
                                    </div>
                                </div>

                                {{-- Acompañamientos --}}
                                <div>
                                    <div class="d-flex justify-content-between" style="font-size:.72rem;color:#6b7280;margin-bottom:2px;">
                                        <span><i class="fas fa-utensils me-1"></i>Acompañamientos</span>
                                        <span class="fw-semibold">{{ $productos['acompañamientos'] }}/{{ $cfg->max_acompañamientos }}</span>
                                    </div>
                                    <div class="rounded-pill overflow-hidden" style="height:5px;background:#e5e7eb;">
                                        <div class="rounded-pill" style="height:100%;width:{{ $aPct }}%;background:{{ $barColor }};transition:width .3s;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-2x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-0">No hay slots configurados para hoy</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Configuración Semanal ───────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3"
            style="cursor:pointer;" onclick="toggleConfigSemanal()">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-calendar-week" style="color:var(--brand-primary,#00b4d8);"></i>
                <span class="fw-semibold" style="color:#0a2540;">Configuración semanal</span>
            </div>
            <i class="fas fa-chevron-down text-muted" id="cfg-chevron" style="transition:transform .25s;"></i>
        </div>

        <div id="cfg-body" class="card-body" style="display:none;">
            <div class="row g-3">
                @foreach ($weeklyConfigs as $cfg)
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="rounded-3 border p-3 h-100"
                            style="{{ $cfg->is_enabled ? 'border-color:#00b4d8!important;background:rgba(0,180,216,.03);' : 'border-color:#e5e7eb!important;background:#fafafa;opacity:.7;' }}">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="fw-bold" style="font-size:.88rem;color:#0a2540;">{{ $cfg->day_name }}</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                        id="enabled_{{ $cfg->day_of_week }}"
                                        {{ $cfg->is_enabled ? 'checked' : '' }}
                                        onchange="toggleDayConfig('{{ $cfg->day_of_week }}', this.checked)"
                                        style="cursor:pointer;">
                                </div>
                            </div>

                            <form class="weekly-config-form" data-day="{{ $cfg->day_of_week }}">
                                <input type="hidden" name="day_of_week" value="{{ $cfg->day_of_week }}">

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#6b7280;">Inicio</label>
                                        <input type="time" class="form-control form-control-sm" name="hora_inicio"
                                            value="{{ \Carbon\Carbon::parse($cfg->hora_inicio)->format('H:i') }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#6b7280;">Fin</label>
                                        <input type="time" class="form-control form-control-sm" name="hora_fin"
                                            value="{{ \Carbon\Carbon::parse($cfg->hora_fin)->format('H:i') }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#6b7280;">Slot (min)</label>
                                        <input type="number" class="form-control form-control-sm" name="duracion_microturno_minutos"
                                            value="{{ $cfg->duracion_microturno_minutos }}" min="5" max="60" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#6b7280;">Max 🍔</label>
                                        <input type="number" class="form-control form-control-sm" name="max_hamburguesas"
                                            value="{{ $cfg->max_hamburguesas ?? 6 }}" min="1" max="50" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#6b7280;">Max 🍟</label>
                                        <input type="number" class="form-control form-control-sm" name="max_acompañamientos"
                                            value="{{ $cfg->max_acompañamientos ?? 6 }}" min="1" max="50" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                    style="background:var(--brand-primary,#00b4d8);color:#fff;border:none;border-radius:8px;font-size:.78rem;">
                                    <i class="fas fa-save me-1"></i>Guardar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ── Toast de confirmación ───────────────────────────────────────────────── --}}
<div id="adminToast" style="display:none;position:fixed;top:20px;left:50%;transform:translateX(-50%) translateY(-60px);
    padding:12px 20px;border-radius:12px;color:#fff;font-size:.875rem;font-weight:500;
    z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,.18);transition:transform .35s cubic-bezier(.34,1.56,.64,1);">
</div>

<style>
    .form-control-sm { font-size: .8rem; }

    /* ─────────── DARK MODE OVERRIDES — admin/turnos ─────────── */
    html[data-theme="dark"] h4[style*="color:#0a2540"],
    html[data-theme="dark"] .fw-bold[style*="color:#0a2540"],
    html[data-theme="dark"] .fw-semibold[style*="color:#0a2540"],
    html[data-theme="dark"] span[style*="color:#0a2540"],
    html[data-theme="dark"] div[style*="color:#0a2540"] {
        color: #e8edf5 !important;
    }
    html[data-theme="dark"] p[style*="color:#6b7280"],
    html[data-theme="dark"] .form-label[style*="color:#6b7280"],
    html[data-theme="dark"] div[style*="color:#6b7280"] {
        color: #9ca3af !important;
    }
    html[data-theme="dark"] .text-muted { color: #9ca3af !important; }

    /* Toggle Sistema de Turnos: fondo oscuro */
    html[data-theme="dark"] .d-flex.bg-white {
        background: #131c2e !important;
        border-color: #2a3447 !important;
    }
    html[data-theme="dark"] .rounded-3.border.bg-white {
        background: #131c2e !important;
        border-color: #2a3447 !important;
    }

    /* Slots de hoy: header */
    html[data-theme="dark"] .card-header.bg-white {
        background: #131c2e !important;
        border-color: #2a3447 !important;
    }

    /* Cards de configuración semanal: aplicar fondo oscuro a las que están enabled */
    html[data-theme="dark"] #cfg-body .rounded-3.border[style*="background:rgba(0,180,216,.03)"] {
        background: #131c2e !important;
        border-color: rgba(0,180,216,.45) !important;
    }
    /* Cards deshabilitadas */
    html[data-theme="dark"] #cfg-body .rounded-3.border[style*="background:#fafafa"] {
        background: #0f1623 !important;
        border-color: #2a3447 !important;
    }

    /* Inputs dentro de cards de config semanal */
    html[data-theme="dark"] #cfg-body .form-control,
    html[data-theme="dark"] #cfg-body .form-control-sm {
        background: #0a1220 !important;
        border-color: #2a3447 !important;
        color: #e8edf5 !important;
    }
    html[data-theme="dark"] #cfg-body .form-control:focus {
        background: #0a1220 !important;
        border-color: #00b4d8 !important;
        color: #e8edf5 !important;
        box-shadow: 0 0 0 .2rem rgba(0,180,216,.18) !important;
    }

    /* Slots de hoy: cards individuales */
    html[data-theme="dark"] .card-body .rounded-3.border[style*="background:rgba(0,0,0,.02)"] {
        background: #0f1623 !important;
    }
    html[data-theme="dark"] .card-body .rounded-3.border[style*="background:rgba(220,38,38,.04)"] {
        background: rgba(220,38,38,.10) !important;
    }

    /* Badge "Slots de hoy" */
    html[data-theme="dark"] .badge.rounded-pill[style*="background:rgba(0,180,216,.12)"] {
        background: rgba(0,180,216,.22) !important;
        color: #93dbed !important;
    }

    /* Stat cards icon backgrounds que se ven raros */
    html[data-theme="dark"] .card-body .rounded-3[style*="background:rgba(0,180,216,.1)"],
    html[data-theme="dark"] .card-body .rounded-3[style*="background:rgba(22,163,74,.1)"],
    html[data-theme="dark"] .card-body .rounded-3[style*="background:rgba(220,38,38,.1)"],
    html[data-theme="dark"] .card-body .rounded-3[style*="background:rgba(245,158,11,.1)"] {
        background: rgba(255,255,255,.06) !important;
    }

    /* Barras de progreso (track gris claro → gris oscuro) */
    html[data-theme="dark"] .rounded-pill.overflow-hidden[style*="background:#e5e7eb"] {
        background: #2a3447 !important;
    }

    /* Badges libre/lleno: mejorar contraste en oscuro */
    html[data-theme="dark"] .badge[style*="background:#dcfce7"] {
        background: rgba(22,163,74,.22) !important;
        color: #6ee7a3 !important;
    }
    html[data-theme="dark"] .badge[style*="background:#fee2e2"] {
        background: rgba(220,38,38,.22) !important;
        color: #fca5a5 !important;
    }

    /* Card "No hay slots configurados" texto */
    html[data-theme="dark"] .text-center.py-5 .text-muted { color: #9ca3af !important; }

    /* Tipo de cursor del card-header de Configuración semanal */
    html[data-theme="dark"] #cfg-chevron { color: #9ca3af !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle sistema de turnos ────────────────────────────────────────────
    document.getElementById('skipTurnoSwitch').addEventListener('click', function () {
        const isOn = this.textContent.trim() === 'ON';
        const desired = !isOn;

        fetch('{{ route('admin.turnos.toggle-skip') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ enabled: desired ? 1 : 0 })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById('skipTurnoSwitch');
                btn.textContent = desired ? 'ON' : 'OFF';
                btn.style.background = desired ? '#16a34a' : '#dc2626';

                // Actualizar el subtexto con los pasos correspondientes
                const subtext = document.getElementById('skipTurnoSubtext');
                if (subtext) {
                    subtext.textContent = desired
                        ? '4 pasos: Carrito → Turnos → Checkout → Confirmación'
                        : '3 pasos: Carrito → Checkout → Confirmación';
                }

                showToast('Sistema de turnos ' + (desired ? 'activado' : 'desactivado'), desired ? '#16a34a' : '#dc2626');
            } else {
                showToast(data.message || 'Error al actualizar', '#dc2626');
            }
        })
        .catch(() => showToast('Error de red', '#dc2626'));
    });

    // ── Config semanal: formularios ────────────────────────────────────────
    document.querySelectorAll('.weekly-config-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const dayOfWeek = this.dataset.day;
            const formData = new FormData(this);

            fetch('/admin/turnos/weekly-config', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Configuración guardada', '#16a34a');
                    const today = new Date();
                    const dayNames = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                    const spanishToEnglish = {
                        'lunes':'monday','martes':'tuesday','miércoles':'wednesday',
                        'jueves':'thursday','viernes':'friday','sábado':'saturday','domingo':'sunday'
                    };
                    if ((spanishToEnglish[dayOfWeek.toLowerCase()] || dayOfWeek) === dayNames[today.getDay()]) {
                        setTimeout(() => location.reload(), 1200);
                    }
                } else {
                    showToast(data.message || 'Error al guardar', '#dc2626');
                }
            })
            .catch(() => showToast('Error de red', '#dc2626'));
        });
    });

    // ── Toggle día habilitado/deshabilitado ───────────────────────────────
    window.toggleDayConfig = function (dayOfWeek, isEnabled) {
        const form = document.querySelector(`[data-day="${dayOfWeek}"]`);
        const formData = new FormData(form);
        formData.append('is_enabled', isEnabled ? '1' : '0');

        fetch('/admin/turnos/weekly-config', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(dayOfWeek + (isEnabled ? ' habilitado' : ' deshabilitado'), '#16a34a');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(data.message || 'Error', '#dc2626');
            }
        })
        .catch(() => showToast('Error de red', '#dc2626'));
    };

    // ── Acordeón config semanal ───────────────────────────────────────────
    window.toggleConfigSemanal = function () {
        const body    = document.getElementById('cfg-body');
        const chevron = document.getElementById('cfg-chevron');
        const open    = body.style.display === 'none';
        body.style.display = open ? 'block' : 'none';
        chevron.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
    };

    // ── Toast helper ───────────────────────────────────────────────────────
    window.showToast = function (message, bg = '#16a34a') {
        const el = document.getElementById('adminToast');
        el.textContent = message;
        el.style.background = bg;
        el.style.display = 'block';
        requestAnimationFrame(() => {
            el.style.transform = 'translateX(-50%) translateY(0)';
        });
        setTimeout(() => {
            el.style.transform = 'translateX(-50%) translateY(-60px)';
            setTimeout(() => { el.style.display = 'none'; }, 350);
        }, 3000);
    };
});
</script>
@endsection
