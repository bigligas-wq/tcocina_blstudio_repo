@extends('layouts.app')

@section('title', 'Horario de Entrega — TCocina')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Bangers&display=swap');

    .hidden { display: none !important; }

    /* ── Hero ── */
    .turnos-hero {
        background: linear-gradient(160deg, #0c1929 0%, #0f2744 60%, #0c1929 100%);
        padding: 1.6rem 1rem 2rem;
        text-align: center;
    }

    .turnos-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.4rem;
    }

    .t-step {
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .t-step-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .t-step-circle.done    { background: #10b981; color: #fff; }
    .t-step-circle.active  { background: #0096c7; color: #fff; }
    .t-step-circle.pending { background: rgba(255,255,255,.13); color: rgba(200,214,232,.45); }

    .t-step-label { font-size: .72rem; font-weight: 600; }
    .t-step-label.done    { color: #10b981; }
    .t-step-label.active  { color: #93c5fd; }
    .t-step-label.pending { color: rgba(200,214,232,.38); }

    .t-step-line {
        width: 28px;
        height: 2px;
        border-radius: 1px;
        margin: 0 5px;
        flex-shrink: 0;
    }

    .t-step-line.done    { background: #10b981; }
    .t-step-line.pending { background: rgba(255,255,255,.12); }

    .turnos-hero-title {
        font-family: 'Bangers', cursive;
        font-size: clamp(2.2rem, 5vw, 2.8rem);
        letter-spacing: 1.5px;
        color: #e8edf5;
        line-height: 1;
        margin-bottom: .35rem;
    }

    .turnos-hero-sub {
        color: rgba(200, 214, 232, .5);
        font-size: .85rem;
    }

    /* ── Body ── */
    .turnos-body {
        max-width: 640px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
    }

    /* Info pills */
    .turnos-pill {
        display: flex;
        align-items: center;
        gap: .65rem;
        background: rgba(0, 150, 199, 0.06);
        border: 1px solid rgba(0, 150, 199, 0.18);
        border-radius: 12px;
        padding: .7rem 1rem;
        margin-bottom: .6rem;
        font-size: .82rem;
        color: #1e3a5f;
    }

    .turnos-pill i { color: #0096c7; flex-shrink: 0; }

    /* Section label */
    .turnos-section-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #9ca3af;
        margin: 1.1rem 0 .6rem;
    }

    /* Slots grid */
    .turnos-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .55rem;
        margin-bottom: 1.1rem;
    }

    @media (max-width: 440px) {
        .turnos-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .microturno-btn {
        position: relative;
        padding: .8rem .4rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        text-align: center;
        font-size: .85rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: border-color .15s, background .15s, transform .12s, box-shadow .15s;
        line-height: 1.3;
        width: 100%;
    }

    /* Semáforo de capacidad — más visible con punto indicador */
    .microturno-btn.cap-green {
        border-color: #16a34a;
        background: linear-gradient(180deg, rgba(22,163,74,.14), rgba(22,163,74,.05));
        color: #14532d;
    }
    .microturno-btn.cap-yellow {
        border-color: #ca8a04;
        background: linear-gradient(180deg, rgba(202,138,4,.16), rgba(202,138,4,.06));
        color: #78350f;
    }
    .microturno-btn.cap-orange {
        border-color: #ea580c;
        background: linear-gradient(180deg, rgba(234,88,12,.18), rgba(234,88,12,.06));
        color: #7c2d12;
    }
    .microturno-btn.cap-red {
        border-color: #dc2626;
        background: linear-gradient(180deg, rgba(220,38,38,.18), rgba(220,38,38,.06));
        color: #7f1d1d;
    }

    /* Puntito de semáforo en la esquina superior izquierda */
    .microturno-btn::before {
        content: '';
        position: absolute;
        top: 6px;
        left: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #cbd5e1;
        box-shadow: 0 0 0 2px rgba(255,255,255,.85);
    }
    .microturno-btn.cap-green::before  { background: #16a34a; box-shadow: 0 0 0 2px rgba(255,255,255,.85), 0 0 8px rgba(22,163,74,.7); }
    .microturno-btn.cap-yellow::before { background: #ca8a04; box-shadow: 0 0 0 2px rgba(255,255,255,.85), 0 0 8px rgba(202,138,4,.7); }
    .microturno-btn.cap-orange::before { background: #ea580c; box-shadow: 0 0 0 2px rgba(255,255,255,.85), 0 0 8px rgba(234,88,12,.7); }
    .microturno-btn.cap-red::before    { background: #dc2626; box-shadow: 0 0 0 2px rgba(255,255,255,.85), 0 0 8px rgba(220,38,38,.7); }

    .microturno-btn:hover:not(:disabled) {
        filter: brightness(0.97);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,.12);
    }

    .microturno-btn:disabled {
        opacity: .38;
        cursor: not-allowed;
        border-color: #d1d5db;
        background: #f3f4f6;
        color: #9ca3af;
    }

    .microturno-btn.selected {
        border-color: #15803d !important;
        background: linear-gradient(180deg, #16a34a, #15803d) !important;
        color: #fff !important;
        box-shadow: 0 0 0 4px rgba(22,163,74,.22), 0 8px 22px rgba(22,163,74,.38);
        transform: translateY(-2px);
        font-weight: 800;
        letter-spacing: .3px;
        text-shadow: 0 1px 2px rgba(0,0,0,.18);
    }

    /* Tilde verde flotante en el botón seleccionado */
    .microturno-btn.selected::after {
        content: '\f00c'; /* fa-check */
        font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free', 'FontAwesome';
        font-weight: 900;
        position: absolute;
        top: -8px;
        right: -8px;
        width: 22px;
        height: 22px;
        background: #16a34a;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        box-shadow: 0 4px 10px rgba(22,163,74,.45), 0 0 0 2px #fff;
        animation: turnoCheckPop .35s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes turnoCheckPop {
        from { transform: scale(0); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }

    /* Ocultar el puntito de semáforo cuando está seleccionado (se reemplaza por el check) */
    .microturno-btn.selected::before { display: none; }

    /* ── Card "carrito demasiado grande" ── */
    .cart-too-large-card {
        background: linear-gradient(160deg, rgba(37,211,102,.10) 0%, rgba(20,160,77,.06) 100%);
        border: 1.5px solid rgba(37,211,102,.32);
        border-radius: 16px;
        padding: 1.6rem 1.3rem;
        margin-bottom: 1.4rem;
        text-align: center;
        animation: cartTooLargeIn .35s ease;
    }
    @keyframes cartTooLargeIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .cart-too-large-icon {
        width: 56px; height: 56px; border-radius: 50%;
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: #fff;
        font-size: 1.6rem;
        margin: 0 auto .9rem;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 20px rgba(37,211,102,.35);
    }
    .cart-too-large-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #14532d;
        margin-bottom: .55rem;
    }
    .cart-too-large-text {
        font-size: .88rem;
        color: #1e3a5f;
        line-height: 1.45;
        margin-bottom: 1rem;
    }

    /* Badge rojo de advertencia importante */
    .cart-too-large-badge {
        display: flex;
        align-items: center;
        gap: .6rem;
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: #fff;
        border-radius: 10px;
        padding: .7rem .85rem;
        font-size: .82rem;
        font-weight: 600;
        line-height: 1.35;
        text-align: left;
        box-shadow: 0 6px 16px rgba(220,38,38,.28);
        animation: badgePulse 2.2s ease-in-out infinite;
    }
    .cart-too-large-badge i {
        font-size: 1.1rem;
        flex-shrink: 0;
        color: #fef3c7;
    }
    @keyframes badgePulse {
        0%, 100% { box-shadow: 0 6px 16px rgba(220,38,38,.28); }
        50%      { box-shadow: 0 6px 20px rgba(220,38,38,.5), 0 0 0 4px rgba(220,38,38,.12); }
    }

    /* Selected card */
    .turnos-selected-card {
        background: rgba(0, 150, 199, 0.06);
        border: 1.5px solid rgba(0, 150, 199, 0.28);
        border-radius: 14px;
        padding: .9rem 1.1rem;
        margin-bottom: .85rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .turnos-sel-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #0096c7;
        margin-bottom: .1rem;
    }

    .turnos-sel-time {
        font-size: .92rem;
        font-weight: 600;
        color: #1e3a5f;
    }

    .turnos-clear-btn {
        background: none;
        border: none;
        color: #d1d5db;
        padding: .2rem .4rem;
        cursor: pointer;
        font-size: .95rem;
        transition: color .15s;
        line-height: 1;
    }

    .turnos-clear-btn:hover { color: #ef4444; }

    /* Continue button */
    .turnos-continue-btn {
        width: 100%;
        padding: .85rem;
        background: linear-gradient(135deg, #0096c7 0%, #0c6568 100%);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-weight: 700;
        font-size: .98rem;
        cursor: pointer;
        transition: opacity .2s, transform .15s;
    }

    .turnos-continue-btn:hover:not(:disabled) {
        opacity: .88;
        transform: translateY(-1px);
    }

    .turnos-continue-btn:disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
        transform: none;
        opacity: 1;
    }
</style>
@endpush

@section('content')

{{-- ── Hero ── --}}
<div class="turnos-hero">
    <div class="turnos-steps">
        <div class="t-step">
            <div class="t-step-circle done"><i class="fas fa-check" style="font-size:.65rem;"></i></div>
            <span class="t-step-label done d-none d-sm-inline">Carrito</span>
        </div>
        <div class="t-step-line done"></div>
        <div class="t-step">
            <div class="t-step-circle active">2</div>
            <span class="t-step-label active d-none d-sm-inline">Entrega</span>
        </div>
        <div class="t-step-line pending"></div>
        <div class="t-step">
            <div class="t-step-circle pending">3</div>
            <span class="t-step-label pending d-none d-sm-inline">Checkout</span>
        </div>
        <div class="t-step-line pending"></div>
        <div class="t-step">
            <div class="t-step-circle pending">4</div>
            <span class="t-step-label pending d-none d-sm-inline">Confirmación</span>
        </div>
    </div>

    <h1 class="turnos-hero-title">Horario de Entrega</h1>
    <p class="turnos-hero-sub">Elegí el horario en que querés recibir tu pedido</p>
</div>

{{-- ── Body ── --}}
<div class="turnos-body">

    <div class="turnos-pill">
        <i class="fas fa-bolt"></i>
        <span><strong>Tipo de entrega:</strong> Lo antes posible</span>
    </div>
    <div class="turnos-pill">
        <i class="fas fa-clock"></i>
        <span>
            <strong>Horario de atención:</strong>
            {{ \Carbon\Carbon::parse($config->hora_inicio)->format('H:i') }}
            a
            {{ \Carbon\Carbon::parse($config->hora_fin)->format('H:i') }} hs
        </span>
    </div>

    <div class="turnos-section-label">Seleccioná tu horario</div>

    {{-- Slots --}}
    <div id="microturnosContainer">
        <div class="turnos-grid">
            @foreach ($microturnosHoy as $microturno)
                <button type="button"
                    class="microturno-btn"
                    data-microturno-id="{{ $microturno->getSortOrderAttribute() }}"
                    data-capacity="{{ $microturno->getCapacidadRestanteAttribute() }}"
                    {{ !$microturno->getIsDisponibleAttribute() ? 'disabled' : '' }}>
                    {{ $microturno->getFormattedTimeAttribute() }}
                </button>
            @endforeach
        </div>

        @if ($microturnosHoy->count() === 0)
            <div class="text-center py-5">
                <i class="fas fa-calendar-times mb-3" style="font-size:2.5rem; color:#d1d5db;"></i>
                <p style="color:#9ca3af; font-size:.9rem;">No hay horarios disponibles para hoy</p>
            </div>
        @endif
    </div>

    {{-- Selected --}}
    <div id="selectedTurn" class="turnos-selected-card hidden">
        <div>
            <div class="turnos-sel-label">Entrega Seleccionada</div>
            <div class="turnos-sel-time" id="selectedTurnTime"></div>
        </div>
        <button type="button" id="clearSelection" class="turnos-clear-btn">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <p id="instructions" class="text-center mb-3" style="color:#9ca3af; font-size:.8rem;">
        Seleccioná un horario para continuar
    </p>

    <button id="continueBtn" class="turnos-continue-btn" disabled>
        Continuar
    </button>
</div>

{{-- Error modal --}}
<div id="validationErrorModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:1.5rem; max-width:420px; width:calc(100% - 2rem); margin:auto;">
        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1rem;">
            <i class="fas fa-exclamation-triangle" style="color:#f59e0b; font-size:1.4rem;"></i>
            <h3 style="margin:0; font-size:1rem; font-weight:700; color:#111827;">Horario no disponible</h3>
        </div>
        <p id="validationErrorText" style="color:#6b7280; font-size:.88rem; margin-bottom:1.25rem;"></p>
        <div style="text-align:right;">
            <button id="closeValidationModal" style="background:linear-gradient(135deg,#0096c7 0%,#0c6568 100%); border:none; border-radius:8px; color:#fff; padding:.5rem 1.25rem; font-weight:700; cursor:pointer;">
                Entendido
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedMicroturno = null;
        const productCache = new Map();

        loadMicroturnosForDate('{{ $hoy }}', true);

        document.addEventListener('click', function(e) {
            const microturnoBtn = e.target.closest('.microturno-btn');
            if (microturnoBtn && !microturnoBtn.disabled) {
                selectMicroturno(microturnoBtn);
            }
        });

        function selectMicroturno(button, isAutoSelected = false) {
            document.querySelectorAll('.microturno-btn').forEach(btn => {
                btn.classList.remove('selected');
            });

            button.classList.add('selected');
            selectedMicroturno = button.dataset.microturnoId;
            const time = button.textContent.trim();

            let timeText = time;
            if (isAutoSelected) {
                timeText += ' (Seleccionado automáticamente)';
            }

            document.getElementById('selectedTurnTime').textContent = timeText;
            document.getElementById('selectedTurn').classList.remove('hidden');
            document.getElementById('continueBtn').disabled = false;
        }

        document.getElementById('clearSelection').addEventListener('click', function() {
            document.querySelectorAll('.microturno-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            document.getElementById('selectedTurn').classList.add('hidden');
            document.getElementById('continueBtn').disabled = true;
            selectedMicroturno = null;
        });

        document.getElementById('continueBtn').addEventListener('click', async function() {
            if (selectedMicroturno === '__whatsapp__') {
                // Caso especial: pedido grande, coordinar por WhatsApp
                sessionStorage.setItem('coordinateByWhatsApp', '1');
                sessionStorage.removeItem('selectedMicroturno');
                sessionStorage.setItem('deliveryTime', 'whatsapp');
                sessionStorage.setItem('deliveryDate', '{{ $hoy }}');
                window.location.href = '{{ route('checkout') }}';
                return;
            }

            if (selectedMicroturno) {
                const isValid = await validateSelectedMicroturno();
                if (isValid) {
                    sessionStorage.removeItem('coordinateByWhatsApp');
                    sessionStorage.setItem('selectedMicroturno', selectedMicroturno);
                    // Guardar el label del horario para que el checkout pueda marcar DONE sin esperar fetch
                    const selectedBtn = document.querySelector(`.microturno-btn.selected`);
                    if (selectedBtn) {
                        sessionStorage.setItem('selectedTurnFormatted', selectedBtn.textContent.trim());
                    }
                    sessionStorage.setItem('deliveryTime', 'now');
                    sessionStorage.setItem('deliveryDate', '{{ $hoy }}');
                    window.location.href = '{{ route('checkout') }}';
                } else {
                    showValidationError('El horario seleccionado ya no tiene capacidad suficiente. Actualizando horarios disponibles...');
                    await loadMicroturnosForDate('{{ $hoy }}', false);
                }
            } else {
                alert('Por favor, seleccioná un horario de entrega');
            }
        });

        async function loadMicroturnosForDate(fecha, autoSelect = false) {
            const container = document.getElementById('microturnosContainer');
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin mb-3" style="font-size:2rem; color:#0096c7;"></i>
                    <p style="color:#9ca3af; font-size:.88rem;">Actualizando horarios disponibles...</p>
                </div>`;

            try {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const cartForAPI = await getCartWithCategories(cart);

                const response = await fetch(`/api/turnos/disponibles?fecha=${fecha}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ cart: cartForAPI })
                });
                const data = await response.json();

                if (data.success && data.microturnos.length > 0) {
                    let html = '<div class="turnos-grid">';
                    let earliestAvailable = null;
                    let anyVisible = false;
                    let anyTurnoConCapacidad = false; // hay turnos no llenos (sin contar el carrito)

                    data.microturnos.forEach((microturno) => {
                        // Ocultar turnos que ya pasaron
                        if (microturno.is_past) return;

                        anyVisible = true;
                        const isAvailable = microturno.disponible_para_carrito;
                        if (microturno.is_disponible) anyTurnoConCapacidad = true;
                        if (isAvailable && !earliestAvailable) earliestAvailable = microturno;

                        // Semáforo: calcular % de ocupación
                        let capClass = '';
                        if (isAvailable) {
                            const max = microturno.capacidad_maxima || 1;
                            const restante = microturno.capacidad_restante;
                            const ocupPct = Math.round(((max - restante) / max) * 100);
                            if (ocupPct < 40)       capClass = 'cap-green';
                            else if (ocupPct < 70)  capClass = 'cap-yellow';
                            else if (ocupPct < 90)  capClass = 'cap-orange';
                            else                    capClass = 'cap-red';
                        }

                        html += `
                            <button type="button"
                                class="microturno-btn ${capClass}"
                                data-microturno-id="${microturno.sort_order}"
                                data-capacity="${microturno.capacidad_restante}"
                                ${!isAvailable ? 'disabled' : ''}>
                                ${microturno.formatted_time}
                            </button>`;
                    });

                    html += '</div>';

                    // Caso especial: hay turnos con capacidad libre, pero NINGUNO acepta el tamaño del carrito
                    // → el pedido es grande, se coordina por WhatsApp
                    const cartTooLarge = anyVisible && !earliestAvailable && anyTurnoConCapacidad;

                    if (cartTooLarge) {
                        renderCartTooLargeView(container);
                        return;
                    }

                    if (!anyVisible) {
                        container.innerHTML = `
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times mb-3" style="font-size:2.5rem; color:#d1d5db;"></i>
                                <p style="color:#9ca3af; font-size:.9rem;">No hay horarios disponibles para hoy</p>
                            </div>`;
                    } else {
                        container.innerHTML = html;
                    }

                    if (autoSelect && earliestAvailable) {
                        setTimeout(() => {
                            const earliestBtn = document.querySelector(`[data-microturno-id="${earliestAvailable.sort_order}"]`);
                            if (earliestBtn) selectMicroturno(earliestBtn, true);
                        }, 100);
                    }
                } else {
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times mb-3" style="font-size:2.5rem; color:#d1d5db;"></i>
                            <p style="color:#9ca3af; font-size:.9rem;">No hay horarios disponibles para hoy</p>
                        </div>`;
                }
            } catch (error) {
                console.error('Error loading microturnos:', error);
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle mb-3" style="font-size:2.5rem; color:#fca5a5;"></i>
                        <p style="color:#ef4444; font-size:.9rem;">Error al cargar horarios</p>
                    </div>`;
            }
        }

        function renderCartTooLargeView(container) {
            container.innerHTML = `
                <div class="cart-too-large-card">
                    <div class="cart-too-large-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3 class="cart-too-large-title">Tu pedido es grande</h3>
                    <p class="cart-too-large-text">
                        Como el pedido supera la capacidad de cualquier turno disponible,
                        el horario de entrega lo coordinamos directamente por WhatsApp con la cocina
                        después de confirmar el pedido.
                    </p>
                    <div class="cart-too-large-badge">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Importante: la confirmación final del pedido queda sujeta a lo que coordines por WhatsApp.</span>
                    </div>
                </div>
            `;

            // Activar modo "coordinar por WhatsApp" y habilitar el botón Continuar
            selectedMicroturno = '__whatsapp__';
            document.getElementById('continueBtn').disabled = false;

            // Ocultar la card "Entrega Seleccionada" — no aplica en este caso
            const selectedTurnDiv = document.getElementById('selectedTurn');
            if (selectedTurnDiv) selectedTurnDiv.classList.add('hidden');
        }

        async function getCartWithCategories(cart) {
            const cartWithCategories = [];
            const productIds = [...new Set(cart.map(item => item.productId))];
            const uncachedIds = productIds.filter(id => !productCache.has(id));

            if (uncachedIds.length > 0) {
                try {
                    const response = await fetch('/api/products/batch', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ids: uncachedIds })
                    });
                    const data = await response.json();
                    if (data.success) {
                        data.products.forEach(product => {
                            productCache.set(product.id, product.category.name);
                        });
                    }
                } catch (error) {
                    console.error('Error fetching product categories:', error);
                }
            }

            cart.forEach(item => {
                const category = productCache.get(parseInt(item.productId)) || 'hamburguesas';
                cartWithCategories.push({ productId: item.productId, quantity: item.quantity, category });
            });

            return cartWithCategories;
        }

        async function validateSelectedMicroturno() {
            if (!selectedMicroturno) return false;
            try {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const cartForAPI = await getCartWithCategories(cart);
                const response = await fetch(`/api/turnos/disponibles?fecha={{ $hoy }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ cart: cartForAPI })
                });
                const data = await response.json();
                if (data.success) {
                    const microturno = data.microturnos.find(m => m.sort_order == selectedMicroturno);
                    return microturno && microturno.disponible_para_carrito;
                }
                return false;
            } catch (error) {
                console.error('Error validating microturno:', error);
                return false;
            }
        }

        function updateMicroturnosOnCartChange() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            if (cart.length > 0) loadMicroturnosForDate('{{ $hoy }}', false);
        }

        window.addEventListener('storage', function(e) {
            if (e.key === 'cart') updateMicroturnosOnCartChange();
        });

        const originalSetItem = localStorage.setItem;
        localStorage.setItem = function(key, value) {
            originalSetItem.apply(this, arguments);
            if (key === 'cart') setTimeout(updateMicroturnosOnCartChange, 100);
        };

        function showValidationError(message) {
            const modal = document.getElementById('validationErrorModal');
            document.getElementById('validationErrorText').textContent = message;
            modal.style.display = 'flex';
        }

        function closeValidationModal() {
            document.getElementById('validationErrorModal').style.display = 'none';
        }

        document.getElementById('closeValidationModal').addEventListener('click', closeValidationModal);
        document.getElementById('validationErrorModal').addEventListener('click', function(e) {
            if (e.target === this) closeValidationModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeValidationModal();
        });
    });
</script>

@endsection
