@extends('layouts.app')

@section('title', 'Finalizar Pedido - TCocina')

@php
    // Configuraciones para personalización rápida de acompañamientos / combos
    $checkoutDipConfigs = \App\Models\ProductConfiguration::where('name', 'Dip')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();
    $checkoutDipExtraConfigs = \App\Models\ProductConfiguration::where('name', 'Dip Extra')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();
@endphp

@section('content')

@php $skipTurno = $businessSettings['skip_turno_selection'] ?? false; @endphp
<div class="flow-progress-hero">
    <div class="flow-steps">
        <div class="flow-step">
            <div class="flow-step-circle done"><i class="fas fa-check" style="font-size:.6rem;"></i></div>
            <span class="flow-step-label done d-none d-sm-inline">Carrito</span>
        </div>
        <div class="flow-step-line done"></div>
        @if (!$skipTurno)
        <div class="flow-step">
            <div class="flow-step-circle done"><i class="fas fa-check" style="font-size:.6rem;"></i></div>
            <span class="flow-step-label done d-none d-sm-inline">Entrega</span>
        </div>
        <div class="flow-step-line done"></div>
        @endif
        <div class="flow-step">
            <div class="flow-step-circle active">{{ $skipTurno ? '2' : '3' }}</div>
            <span class="flow-step-label active d-none d-sm-inline">Checkout</span>
        </div>
        <div class="flow-step-line pending"></div>
        <div class="flow-step">
            <div class="flow-step-circle pending">{{ $skipTurno ? '3' : '4' }}</div>
            <span class="flow-step-label pending d-none d-sm-inline">Confirmación</span>
        </div>
    </div>
    <div style="text-align:center;margin-top:.85rem;">
        <h1 id="heroTitle" style="font-family:'Bangers',cursive;font-size:clamp(1.9rem,4.5vw,2.6rem);letter-spacing:1.5px;color:#e8edf5;line-height:1;margin-bottom:.3rem;">MÉTODO DE ENTREGA</h1>
        <p id="heroSubtitle" style="color:rgba(200,214,232,.5);font-size:.82rem;">Elegí cómo querés recibir tu pedido</p>
    </div>
</div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-6">
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-3" id="checkoutFormCol">
                <form id="checkoutForm" class="space-y-8">
                    <!-- Delivery/Pickup Toggle and Customer Information -->
                    <section class="card p-6 step-card" id="deliveryMethodSection" data-step="delivery" data-state="active" style="max-width:780px;margin:0 auto;">
                        <span class="step-locked-badge">Pendiente</span>

                        {{-- Estado DONE: header compacto --}}
                        <div class="step-done-header">
                            <div class="step-done-left">
                                <div class="step-done-check"><i class="fas fa-check"></i></div>
                                <div class="step-done-text">
                                    <div class="step-done-title">Método de entrega</div>
                                    <div class="step-done-summary" id="deliveryStepSummary">—</div>
                                </div>
                            </div>
                            <button type="button" class="step-edit-btn" data-edit-step="delivery">
                                <i class="fas fa-pen"></i>Editar
                            </button>
                        </div>

                        {{-- Estado ACTIVE: header + body --}}
                        <div class="step-active-header" style="cursor:pointer;" onclick="toggleDeliveryMethodSection()">
                            <div class="d-flex align-items-center justify-content-between">
                                <h2 class="text-lg font-display text-text-primary mb-0">Método de Entrega</h2>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="deliveryMethodSelectedLabel" class="badge text-white d-none" style="background:var(--beach-primary,#00b4d8);font-size:0.8rem;"></span>
                                    <i id="deliveryMethodChevron" class="fas fa-chevron-down text-muted" style="transition:transform .25s;font-size:0.85rem;"></i>
                                </div>
                            </div>
                        </div>

                        <div class="step-body">
                        <div id="deliveryMethodBody" style="overflow:hidden; transition: max-height .3s ease, opacity .25s ease; max-height: 600px; opacity: 1;">
                        <div class="mt-4"></div>

                        <!-- Mensaje de selección (se oculta al elegir Retiro o Delivery) -->
                        <div id="deliveryMethodPrompt" class="mb-4 p-3 rounded-lg" style="background-color: rgba(0, 180, 216, 0.1); border: 1px solid var(--beach-primary, #00b4d8);">
                            <p class="text-sm font-medium text-center" style="color: var(--beach-primary, #00b4d8);">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                Para avanzar, informanos cómo vas a recibir tu pedido
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            @php
                                $isDeliveryAvailable = $businessSettings['is_delivery_available'] ?? true;
                                $isPickupAvailable = $businessSettings['is_pickup_available'] ?? true;

                                if (is_string($isDeliveryAvailable)) {
                                    $isDeliveryAvailable = $isDeliveryAvailable === 'true' || $isDeliveryAvailable === '1';
                                }
                                if (is_string($isPickupAvailable)) {
                                    $isPickupAvailable = $isPickupAvailable === 'true' || $isPickupAvailable === '1';
                                }
                            @endphp

                            @if ($isDeliveryAvailable)
                                <button type="button" id="deliveryBtn" class="delivery-option">
                                    <lord-icon
                                        src="{{ asset('lordicons/Delivery.json') }}"
                                        colors="primary:#0a2540,secondary:#0a2540"
                                        trigger="hover"
                                        style="width:60px;height:60px;">
                                    </lord-icon>
                                    <div class="delivery-option-label">Delivery</div>
                                    <div class="delivery-option-sub">Te lo llevamos a casa</div>
                                </button>
                            @endif

                            @if ($isPickupAvailable)
                                <button type="button" id="pickupBtn" class="delivery-option">
                                    <lord-icon
                                        src="{{ asset('lordicons/Retiro.json') }}"
                                        colors="primary:#0a2540,secondary:#0a2540"
                                        trigger="hover"
                                        style="width:60px;height:60px;">
                                    </lord-icon>
                                    <div class="delivery-option-label">Retiro</div>
                                    <div class="delivery-option-sub">Pasás a buscar al local</div>
                                </button>
                            @endif
                        </div>
                        </div><!-- /deliveryMethodBody -->
                        </div><!-- /step-body -->
                    </section>

                    <!-- Resto del formulario - oculto inicialmente hasta que se seleccione método de entrega -->
                    <div id="checkoutFormContent" class="hidden space-y-6">
                    <!-- Información de Contacto -->
                    <section class="card p-6 step-card" id="contactStep" data-step="contact" data-state="locked">
                        <span class="step-locked-badge">Pendiente</span>

                        {{-- Estado DONE: header compacto --}}
                        <div class="step-done-header">
                            <div class="step-done-left">
                                <div class="step-done-check"><i class="fas fa-check"></i></div>
                                <div class="step-done-text">
                                    <div class="step-done-title">Información de contacto</div>
                                    <div class="step-done-summary" id="contactStepSummary">—</div>
                                </div>
                            </div>
                            <button type="button" class="step-edit-btn" data-edit-step="contact">
                                <i class="fas fa-pen"></i>Editar
                            </button>
                        </div>

                        {{-- Estado ACTIVE: header + form --}}
                        <div class="step-active-header">
                            <h2 class="text-lg font-semibold text-text-primary mb-4">
                                <i class="fas fa-user me-2" style="color:var(--brand-primary,#00b4d8);"></i>Información de Contacto
                            </h2>
                        </div>
                        <div class="step-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="firstName" class="block text-sm font-medium text-text-primary mb-1">Nombre *</label>
                                    <input type="text" id="firstName" name="firstName" required class="input-field"
                                        autocomplete="given-name" placeholder="Tu nombre"
                                        value="{{ old('firstName', $checkoutProfile['first_name'] ?? '') }}" />
                                    <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu nombre</div>
                                </div>
                                <div>
                                    <label for="lastName" class="block text-sm font-medium text-text-primary mb-1">Apellido *</label>
                                    <input type="text" id="lastName" name="lastName" required class="input-field"
                                        autocomplete="family-name" placeholder="Tu apellido"
                                        value="{{ old('lastName', $checkoutProfile['last_name'] ?? '') }}" />
                                    <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu apellido</div>
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-text-primary mb-1">Teléfono *</label>
                                    <input type="tel" id="phone" name="phone" required class="input-field"
                                        autocomplete="tel" placeholder="+54 11 1234-5678"
                                        value="{{ old('phone', $checkoutProfile['phone'] ?? '') }}" />
                                    <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu teléfono</div>
                                </div>
                            </div>
                            <button type="button" class="step-continue-btn" data-continue-step="contact">
                                Continuar <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </section>

                    <!-- Delivery Address (shown only for delivery) -->
                    <section id="deliverySection" class="card p-6 hidden step-card" data-step="address" data-state="locked">
                        <span class="step-locked-badge">Pendiente</span>

                        {{-- Estado DONE --}}
                        <div class="step-done-header">
                            <div class="step-done-left">
                                <div class="step-done-check"><i class="fas fa-check"></i></div>
                                <div class="step-done-text">
                                    <div class="step-done-title">Dirección de entrega</div>
                                    <div class="step-done-summary" id="addressStepSummary">—</div>
                                </div>
                            </div>
                            <button type="button" class="step-edit-btn" data-edit-step="address">
                                <i class="fas fa-pen"></i>Editar
                            </button>
                        </div>

                        <div class="step-active-header">
                            <h2 class="text-lg font-display text-text-primary mb-4">
                                <i class="fas fa-map-marker-alt me-2" style="color:var(--brand-primary,#00b4d8);"></i>Dirección de Entrega
                            </h2>
                        </div>
                        <div class="step-body space-y-4">
                            @auth
                                @if ($addresses->isNotEmpty())
                                    <div>
                                        <label for="savedAddressId" class="block text-sm font-medium text-text-primary mb-1">Direcciones guardadas</label>
                                        <select id="savedAddressId" class="input-field" style="padding-right: 2.5rem;">
                                            <option value="">Nueva dirección</option>
                                            @foreach ($addresses as $address)
                                                <option value="{{ $address->id }}" data-is-default="{{ $address->is_default ? '1' : '0' }}">
                                                    {{ $address->name }} - {{ $address->street }} {{ $address->number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="text-xs text-text-secondary mt-1">Elegí una guardada o cargá una nueva abajo.</div>
                                    </div>
                                @endif
                            @endauth

                            <!-- Calle y Número en la misma fila -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="street" class="block text-sm font-medium text-text-primary mb-1">Calle
                                        *</label>
                                    <input type="text" id="street" name="street" class="input-field"
                                        autocomplete="address-line1" placeholder="Av. Corrientes" />
                                    <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa la calle
                                    </div>
                                </div>
                                <div>
                                    <label for="number" class="block text-sm font-medium text-text-primary mb-1">Número
                                        *</label>
                                    <input type="text" id="number" name="number" class="input-field"
                                        autocomplete="address-line2" placeholder="1234" />
                                    <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa el
                                        número
                                    </div>
                                </div>
                            </div>

                            <!-- Depto -->
                            <div>
                                <label for="floor"
                                    class="block text-sm font-medium text-text-primary mb-1">Depto</label>
                                <input type="text" id="floor" name="floor" class="input-field"
                                    autocomplete="address-line3" placeholder="5° A" />
                            </div>

                            <!-- Notas de Entrega -->
                            <div>
                                <label for="deliveryNotes" class="block text-sm font-medium text-text-primary mb-1">Notas
                                    de Entrega</label>
                                <textarea id="deliveryNotes" name="deliveryNotes" rows="3" class="input-field"
                                    placeholder="Instrucciones adicionales para el delivery..."></textarea>
                            </div>

                            @auth
                                <div id="saveAddressWrap" class="pt-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="checkbox" id="saveAddress" checked>
                                        <label for="saveAddress" class="text-sm text-text-primary mb-0">Guardar esta dirección para próximos pedidos</label>
                                    </div>
                                    <div>
                                        <label for="addressLabel" class="block text-sm font-medium text-text-primary mb-1">Nombre de la dirección</label>
                                        <input type="text" id="addressLabel" class="input-field" placeholder="Casa / Trabajo / Otra">
                                    </div>
                                </div>
                            @endauth
                            <button type="button" class="step-continue-btn" data-continue-step="address">
                                Continuar <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </section>

                    <!-- Ubicación del local (solo visible cuando se elige Retiro) -->
                    <section id="pickupMapSection" class="card p-6 hidden">
                        <h2 class="text-lg font-semibold text-text-primary mb-2">Dirección de retiro</h2>
                        <p class="text-sm text-text-secondary mb-4">
                            <i class="fas fa-map-marker-alt me-2 text-base" style="color: var(--beach-primary, #00b4d8);"></i>
                            <strong>Avenida Pringles 3768</strong>, Olavarría, Buenos Aires
                            <a href="https://www.google.com/maps/search/?api=1&query=Avenida+Pringles+3768,Olavarría,Buenos+Aires,Argentina" target="_blank" rel="noopener noreferrer" class="ms-2 text-sm" style="color: var(--beach-primary, #00b4d8);">Abrir en Google Maps</a>
                        </p>
                        <div class="rounded-lg overflow-hidden border border-border" style="height: 280px; min-height: 280px;">
                            <iframe
                                src="https://www.google.com/maps?output=embed&q=Avenida+Pringles+3768,Olavarr%C3%ADa,Buenos+Aires,Argentina&z=18"
                                width="100%"
                                height="280"
                                style="border:0; display:block;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Ubicación del local - TCocina">
                            </iframe>
                        </div>
                    </section>

                    @if (!$businessSettings['skip_turno_selection'])
                        <!-- Horario de Entrega -->
                        <section class="card p-6 step-card" id="scheduleStep" data-step="schedule" data-state="locked">
                            <span class="step-locked-badge">Pendiente</span>

                            {{-- Estado DONE --}}
                            <div class="step-done-header">
                                <div class="step-done-left">
                                    <div class="step-done-check"><i class="fas fa-check"></i></div>
                                    <div class="step-done-text">
                                        <div class="step-done-title">Horario de entrega</div>
                                        <div class="step-done-summary" id="scheduleStepSummary">—</div>
                                    </div>
                                </div>
                                <button type="button" class="step-edit-btn" data-edit-step="schedule">
                                    <i class="fas fa-pen"></i>Editar
                                </button>
                            </div>

                            <div class="step-active-header">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="fas fa-clock" style="color:var(--brand-primary,#00b4d8);font-size:1rem;"></i>
                                    <h2 class="text-lg font-display mb-0" style="color:#0a2540;">Horario de entrega</h2>
                                </div>
                            </div>
                            <div class="step-body">
                                <div class="text-muted mb-3" style="font-size:.82rem;">Elegí uno de los horarios disponibles para hoy</div>
                                <div id="inlineMicroturnosContainer">
                                    <div class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;color:#0096c7;"></i>
                                        <p class="text-muted mt-2 mb-0" style="font-size:.85rem;">Cargando horarios disponibles...</p>
                                    </div>
                                </div>
                                <button type="button" class="step-continue-btn" data-continue-step="schedule" disabled>
                                    Continuar <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                            <input type="hidden" id="microturno_id" name="microturno_id" />
                        </section>

                        <style>
                            /* Selector inline de horarios */
                            .inline-turnos-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
                                gap: .55rem;
                            }
                            .inline-turno-btn {
                                position: relative;
                                padding: .7rem .4rem;
                                border: 2px solid #e5e7eb;
                                border-radius: 10px;
                                background: #fff;
                                font-size: .82rem;
                                font-weight: 700;
                                color: #1e3a5f;
                                cursor: pointer;
                                transition: all .15s ease;
                            }
                            .inline-turno-btn::before {
                                content: '';
                                position: absolute;
                                top: 5px;
                                left: 5px;
                                width: 7px;
                                height: 7px;
                                border-radius: 50%;
                                background: #cbd5e1;
                            }
                            .inline-turno-btn.cap-green::before  { background: #16a34a; box-shadow: 0 0 6px rgba(22,163,74,.65); }
                            .inline-turno-btn.cap-yellow::before { background: #ca8a04; box-shadow: 0 0 6px rgba(202,138,4,.65); }
                            .inline-turno-btn.cap-orange::before { background: #ea580c; box-shadow: 0 0 6px rgba(234,88,12,.65); }
                            .inline-turno-btn.cap-red::before    { background: #dc2626; box-shadow: 0 0 6px rgba(220,38,38,.65); }
                            .inline-turno-btn.cap-green  { border-color: #16a34a; background: linear-gradient(180deg, rgba(22,163,74,.10), rgba(22,163,74,.03)); }
                            .inline-turno-btn.cap-yellow { border-color: #ca8a04; background: linear-gradient(180deg, rgba(202,138,4,.10), rgba(202,138,4,.03)); }
                            .inline-turno-btn.cap-orange { border-color: #ea580c; background: linear-gradient(180deg, rgba(234,88,12,.10), rgba(234,88,12,.03)); }
                            .inline-turno-btn.cap-red    { border-color: #dc2626; background: linear-gradient(180deg, rgba(220,38,38,.10), rgba(220,38,38,.03)); }
                            .inline-turno-btn:hover:not(:disabled) {
                                transform: translateY(-1px);
                                box-shadow: 0 4px 12px rgba(0,0,0,.1);
                            }
                            .inline-turno-btn:disabled {
                                opacity: .35;
                                cursor: not-allowed;
                            }
                            .inline-turno-btn.selected {
                                border-color: #15803d !important;
                                background: linear-gradient(180deg, #16a34a, #15803d) !important;
                                color: #fff !important;
                                box-shadow: 0 0 0 3px rgba(22,163,74,.22), 0 6px 16px rgba(22,163,74,.32);
                            }
                            .inline-turno-btn.selected::before { display: none; }
                            .inline-turno-btn.selected::after {
                                content: '\f00c';
                                font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free', 'FontAwesome';
                                font-weight: 900;
                                position: absolute;
                                top: -7px; right: -7px;
                                width: 20px; height: 20px;
                                background: #16a34a;
                                color: #fff;
                                border-radius: 50%;
                                display: flex; align-items: center; justify-content: center;
                                font-size: .62rem;
                                box-shadow: 0 3px 8px rgba(22,163,74,.45), 0 0 0 2px #fff;
                            }
                        </style>
                    @endif

                    <!-- Payment Method -->
                    <section class="card p-6 step-card" id="paymentStep" data-step="payment" data-state="locked">
                        <span class="step-locked-badge">Pendiente</span>

                        {{-- Estado DONE --}}
                        <div class="step-done-header">
                            <div class="step-done-left">
                                <div class="step-done-check"><i class="fas fa-check"></i></div>
                                <div class="step-done-text">
                                    <div class="step-done-title">Método de pago</div>
                                    <div class="step-done-summary" id="paymentStepSummary">—</div>
                                </div>
                            </div>
                            <button type="button" class="step-edit-btn" data-edit-step="payment">
                                <i class="fas fa-pen"></i>Editar
                            </button>
                        </div>

                        <div class="step-active-header">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-wallet" style="color:var(--brand-primary,#00b4d8);font-size:1rem;"></i>
                                <h2 class="text-lg font-display mb-0" style="color:#0a2540;">Método de Pago</h2>
                            </div>
                        </div>
                        <div class="step-body">
                        <div class="payment-methods-grid">
                            @php
                                $paymentMethods = $businessSettings['payment_methods'] ?? null;
                                if (!is_array($paymentMethods) || empty($paymentMethods)) {
                                    $paymentMethods = ['cash', 'card'];
                                }

                                $availableMethods = [
                                    'cash' => [
                                        'name' => 'Efectivo',
                                        'icon' => 'fas fa-money-bill-wave',
                                        'accent' => '#16a34a',
                                        'description' => 'Pago contra entrega',
                                    ],
                                    'card' => [
                                        'name' => 'Tarjeta',
                                        'icon' => 'fas fa-credit-card',
                                        'accent' => '#0096c7',
                                        'description' => 'Crédito o Débito',
                                    ],
                                    'transfer' => [
                                        'name' => 'Transferencia',
                                        'icon' => 'fas fa-university',
                                        'accent' => '#7c3aed',
                                        'description' => 'Mercado Pago, Ualá',
                                    ],
                                ];
                                $firstEnabled = true;
                            @endphp
                            @php
                                $cashDiscountPct = (int) ($businessSettings['cash_discount_percentage'] ?? 0);
                            @endphp
                            @foreach ($availableMethods as $methodKey => $methodData)
                                @if (in_array($methodKey, $paymentMethods))
                                    <label class="payment-option {{ $firstEnabled ? 'active' : '' }}"
                                        data-accent="{{ $methodData['accent'] }}"
                                        style="--accent:{{ $methodData['accent'] }};">
                                        <input type="radio" name="paymentMethod" value="{{ $methodKey }}"
                                            {{ $firstEnabled ? 'checked' : '' }}
                                            class="payment-option-radio" />
                                        <div class="payment-option-icon" style="background:{{ $methodData['accent'] }}1a;color:{{ $methodData['accent'] }};">
                                            <i class="{{ $methodData['icon'] }}"></i>
                                        </div>
                                        <div class="payment-option-text">
                                            <div class="payment-option-name">
                                                {{ $methodData['name'] }}
                                                @if ($methodKey === 'cash' && $cashDiscountPct > 0)
                                                    <span class="payment-promo-badge">
                                                        <i class="fas fa-tag"></i>-{{ $cashDiscountPct }}% OFF
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="payment-option-desc">{{ $methodData['description'] }}</div>
                                            @if ($methodKey === 'cash' && $cashDiscountPct > 0)
                                                <div class="payment-savings-line" id="paymentSavingsLine">
                                                    <i class="fas fa-piggy-bank"></i>
                                                    Te ahorrás
                                                    <strong id="cashSavingsAmount">$0</strong>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="payment-option-check">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </label>
                                    @php $firstEnabled = false; @endphp
                                @endif
                            @endforeach

                            @if (empty($paymentMethods))
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No hay métodos de pago configurados. Por favor, configura al menos un método de pago en
                                    el panel de administración.
                                </div>
                            @endif
                        </div>

                        <!-- Cash Change -->
                        <div id="cashChangeSection" class="mt-3 rounded-3 p-3" style="background:rgba(22,163,74,.06); border:1px solid rgba(22,163,74,.22);">
                            <label for="cashAmount" class="d-block fw-semibold mb-2" style="font-size:.85rem;color:#15803d;">
                                <i class="fas fa-coins me-1"></i>¿Con cuánto vas a pagar?
                            </label>
                            <input type="number" id="cashAmount" name="cashAmount"
                                class="form-control"
                                style="border-radius:8px;border:1px solid rgba(22,163,74,.28);font-size:.95rem;"
                                placeholder="Monto en efectivo" min="0" step="100" />
                            <div class="mt-1" style="font-size:.72rem;color:#6b7280;">Opcional · para calcular el vuelto</div>
                        </div>
                        <button type="button" class="step-continue-btn" data-continue-step="payment">
                            Continuar <i class="fas fa-arrow-right"></i>
                        </button>
                        </div>{{-- /step-body --}}
                    </section>

                    <!-- Cupón colapsable -->
                    <section class="coupon-card" id="couponCard">
                        <button type="button" class="coupon-trigger" id="couponTrigger" aria-expanded="false">
                            <span class="coupon-trigger-icon"><i class="fas fa-ticket-alt"></i></span>
                            <span class="coupon-trigger-text">
                                <span class="coupon-trigger-title">¿Tenés un cupón?</span>
                                <span class="coupon-trigger-sub">Aplicalo y obtené tu descuento</span>
                            </span>
                            <span class="coupon-trigger-arrow"><i class="fas fa-chevron-down"></i></span>
                        </button>
                        <div id="couponContainer" class="coupon-body">
                            <div id="couponInputSection" class="pt-3">
                                <div class="mb-3">
                                    <div id="otpContainer" class="d-flex justify-content-center gap-2 mb-2"></div>
                                    <div id="couponError" class="text-danger text-sm mt-2 text-center hidden"></div>
                                </div>
                                <button type="button" id="applyCouponBtn" class="coupon-apply-btn">
                                    <i class="fas fa-check me-2"></i>Aplicar Cupón
                                </button>
                            </div>
                            <div id="couponAppliedSection" class="hidden mt-3">
                                <div class="coupon-applied">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="coupon-applied-check"><i class="fas fa-check"></i></div>
                                        <div>
                                            <div class="fw-bold" id="couponName" style="color:#15803d;font-size:.95rem;"></div>
                                            <div id="couponDiscount" style="color:#15803d;font-size:.8rem;"></div>
                                        </div>
                                    </div>
                                    <button type="button" id="removeCouponBtn" class="coupon-remove-btn" title="Quitar cupón">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Sugerencias para sumar al pedido -->
                    @if($acompanamientosProducts && $acompanamientosProducts->count() > 0)
                    <section class="card p-4 suggestions-card">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <h2 class="suggestions-title">
                                <i class="fas fa-users-line"></i>Otras personas agregaron
                            </h2>
                            <span class="suggestions-pill">SUMAR AL PEDIDO</span>
                        </div>
                        <div class="suggestions-scroll">
                            @foreach($acompanamientosProducts as $product)
                            <div class="suggestion-item acompanamiento-item" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}" data-product-category-id="{{ $product->category_id }}">
                                <input type="checkbox" class="acompanamiento-checkbox suggestion-checkbox" data-product-id="{{ $product->id }}" aria-label="Seleccionar {{ $product->name }}">
                                <span class="suggestion-added-badge"><i class="fas fa-check"></i></span>
                                <div class="suggestion-image-wrap">
                                    @if($product->image)
                                        <img src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}" class="suggestion-image" onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=400&auto=format&fit=crop'; this.onerror=null;">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=400&auto=format&fit=crop" alt="{{ $product->name }}" class="suggestion-image">
                                    @endif
                                </div>
                                <div class="suggestion-info">
                                    <h4 class="suggestion-name">{{ $product->name }}</h4>
                                    <div class="suggestion-price">${{ number_format($product->base_price, 0, ',', '.') }}</div>
                                </div>
                                <div class="suggestion-action">
                                    <button type="button"
                                        class="acompanamiento-add-btn suggestion-add-btn"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}"
                                        data-product-image="{{ $product->image }}"
                                        data-product-category-id="{{ $product->category_id }}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <div class="suggestion-stepper" data-product-id="{{ $product->id }}">
                                        <button type="button" class="suggestion-step-btn suggestion-step-minus" data-product-id="{{ $product->id }}"><i class="fas fa-minus"></i></button>
                                        <span class="suggestion-step-count">0</span>
                                        <button type="button" class="suggestion-step-btn suggestion-step-plus"
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-price="{{ $product->base_price }}"
                                            data-product-image="{{ $product->image }}"
                                            data-product-category-id="{{ $product->category_id }}"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="addSelectedAcompanamientos" class="suggestion-bulk-add hidden" disabled>
                            <i class="fas fa-plus me-2"></i>Agregar seleccionados (0)
                        </button>
                    </section>
                    @endif

                    <style>
                        /* ── Cupón colapsable ── */
                        .coupon-card {
                            position: relative;
                            border-radius: 14px;
                            border: 1.5px dashed rgba(0,180,216,.45);
                            background: #fff;
                            overflow: hidden;
                            transition: all .25s ease;
                            box-shadow: 0 4px 14px rgba(10,37,64,.06);
                        }
                        .coupon-card.open {
                            border-style: solid;
                            box-shadow: 0 8px 24px rgba(0,150,199,.14);
                        }
                        /* Notches a los lados (efecto ticket) */
                        .coupon-card::before,
                        .coupon-card::after {
                            content: '';
                            position: absolute;
                            width: 18px;
                            height: 18px;
                            background: var(--beach-sand, #f4ecd8);
                            border-radius: 50%;
                            top: 50%;
                            transform: translateY(-50%);
                            transition: background .25s;
                        }
                        .coupon-card::before { left: -9px; }
                        .coupon-card::after  { right: -9px; }
                        .order-flow-bg .coupon-card::before,
                        .order-flow-bg .coupon-card::after {
                            background: rgba(248,249,250,.95);
                        }

                        .coupon-trigger {
                            display: flex;
                            align-items: center;
                            gap: .9rem;
                            width: 100%;
                            padding: 1rem 1.2rem;
                            background: transparent;
                            border: none;
                            cursor: pointer;
                            text-align: left;
                        }
                        .coupon-trigger-icon {
                            width: 44px;
                            height: 44px;
                            border-radius: 12px;
                            background: linear-gradient(135deg, #00b4d8, #0c6568);
                            color: #fff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.15rem;
                            flex-shrink: 0;
                            box-shadow: 0 5px 14px rgba(0,150,199,.32);
                            animation: couponBob 2.6s ease-in-out infinite;
                        }
                        @keyframes couponBob {
                            0%, 100% { transform: rotate(-4deg); }
                            50%      { transform: rotate(4deg); }
                        }
                        .coupon-trigger-text {
                            flex: 1;
                            min-width: 0;
                        }
                        .coupon-trigger-title {
                            display: block;
                            font-weight: 700;
                            font-size: .98rem;
                            color: #0a2540;
                            line-height: 1.15;
                        }
                        .coupon-trigger-sub {
                            display: block;
                            font-size: .78rem;
                            color: #6b7280;
                            margin-top: 2px;
                        }
                        .coupon-trigger-arrow {
                            color: #6b7280;
                            transition: transform .3s;
                            flex-shrink: 0;
                        }
                        .coupon-card.open .coupon-trigger-arrow {
                            transform: rotate(180deg);
                            color: var(--brand-primary, #00b4d8);
                        }
                        .coupon-card.open .coupon-trigger-icon {
                            animation: none;
                            transform: rotate(0);
                        }

                        .coupon-body {
                            display: none;
                            padding: 0 1.2rem 1.2rem;
                        }
                        .coupon-card.open .coupon-body { display: block; }

                        .coupon-apply-btn {
                            width: 100%;
                            padding: .75rem 1rem;
                            background: linear-gradient(135deg, #00b4d8, #0c6568);
                            color: #fff;
                            border: none;
                            border-radius: 10px;
                            font-weight: 700;
                            font-size: .95rem;
                            cursor: pointer;
                            transition: transform .15s, box-shadow .2s;
                        }
                        .coupon-apply-btn:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 6px 18px rgba(0,150,199,.32);
                        }

                        /* Cupón aplicado */
                        .coupon-applied {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 1rem;
                            padding: .9rem 1rem;
                            border-radius: 10px;
                            background: linear-gradient(135deg, rgba(22,163,74,.10), rgba(22,163,74,.04));
                            border: 1px solid rgba(22,163,74,.32);
                        }
                        .coupon-applied-check {
                            width: 38px; height: 38px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, #16a34a, #15803d);
                            color: #fff;
                            display: flex; align-items: center; justify-content: center;
                            font-size: .9rem;
                            flex-shrink: 0;
                            box-shadow: 0 4px 10px rgba(22,163,74,.32);
                        }
                        .coupon-remove-btn {
                            width: 32px; height: 32px;
                            border: none;
                            background: rgba(220,38,38,.1);
                            color: #dc2626;
                            border-radius: 8px;
                            cursor: pointer;
                            transition: all .15s;
                        }
                        .coupon-remove-btn:hover {
                            background: #dc2626;
                            color: #fff;
                        }
                    </style>

                    <style>
                        /* ── Sugerencias horizontal scroll ── */
                        .suggestions-card {
                            background: #fff !important;
                            border: 1px solid rgba(0,180,216,.18) !important;
                        }
                        .suggestions-title {
                            display: inline-flex;
                            align-items: center;
                            gap: .5rem;
                            font-size: .98rem;
                            font-weight: 800;
                            color: #0a2540;
                            margin: 0;
                        }
                        .suggestions-title i { color: var(--brand-primary, #00b4d8); font-size: .9rem; }
                        .suggestions-pill {
                            font-size: .62rem;
                            font-weight: 800;
                            letter-spacing: .55px;
                            color: var(--brand-primary, #00b4d8);
                            background: rgba(0,180,216,.1);
                            padding: 3px 9px;
                            border-radius: 999px;
                            white-space: nowrap;
                        }

                        /* Carrusel horizontal */
                        .suggestions-scroll {
                            display: flex;
                            gap: .65rem;
                            overflow-x: auto;
                            overflow-y: hidden;
                            scroll-snap-type: x mandatory;
                            scrollbar-width: thin;
                            scrollbar-color: rgba(0,180,216,.4) transparent;
                            padding: .25rem .15rem .65rem;
                            margin: 0 -.15rem;
                            -webkit-overflow-scrolling: touch;
                        }
                        .suggestions-scroll::-webkit-scrollbar { height: 5px; }
                        .suggestions-scroll::-webkit-scrollbar-track { background: transparent; }
                        .suggestions-scroll::-webkit-scrollbar-thumb {
                            background: rgba(0,180,216,.35);
                            border-radius: 99px;
                        }
                        .suggestions-scroll::-webkit-scrollbar-thumb:hover { background: rgba(0,180,216,.55); }

                        .suggestion-item {
                            position: relative;
                            flex: 0 0 130px;
                            min-width: 130px !important;
                            max-width: 130px !important;
                            scroll-snap-align: start;
                            display: flex;
                            flex-direction: column;
                            background: #fff;
                            border: 1px solid #e5e7eb;
                            border-radius: 10px;
                            padding: .5rem;
                            transition: transform .18s, box-shadow .18s, border-color .18s;
                        }
                        .suggestion-item:hover {
                            transform: translateY(-2px);
                            border-color: rgba(0,180,216,.4);
                            box-shadow: 0 6px 16px rgba(10,37,64,.1);
                        }
                        .suggestion-checkbox {
                            position: absolute;
                            top: 7px;
                            left: 7px;
                            width: 15px;
                            height: 15px;
                            accent-color: var(--brand-primary, #00b4d8);
                            cursor: pointer;
                            z-index: 2;
                            border-radius: 3px;
                        }
                        .suggestion-image-wrap {
                            border-radius: 7px;
                            overflow: hidden;
                            margin-bottom: .4rem;
                            background: #f3f4f6;
                            aspect-ratio: 1 / 1;
                        }
                        .suggestion-image {
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            display: block;
                            transition: transform .3s;
                        }
                        .suggestion-item:hover .suggestion-image { transform: scale(1.06); }
                        .suggestion-info {
                            flex: 1;
                            display: flex;
                            flex-direction: column;
                            gap: 1px;
                            min-width: 0;
                            margin-bottom: .4rem;
                        }
                        .suggestion-name {
                            font-size: .72rem;
                            font-weight: 700;
                            color: #0a2540;
                            margin: 0;
                            line-height: 1.15;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            display: -webkit-box;
                            -webkit-line-clamp: 2;
                            -webkit-box-orient: vertical;
                            min-height: 1.66em;
                        }
                        .suggestion-price {
                            font-size: .78rem;
                            font-weight: 800;
                            color: var(--brand-primary, #00b4d8);
                        }
                        .suggestion-action { width: 100%; }
                        .suggestion-add-btn {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;
                            padding: .4rem;
                            background: linear-gradient(135deg, var(--brand-primary, #00b4d8), #0c6568);
                            color: #fff;
                            border: none;
                            border-radius: 7px;
                            font-size: .75rem;
                            font-weight: 700;
                            cursor: pointer;
                            transition: transform .15s, box-shadow .18s;
                        }
                        .suggestion-add-btn:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 5px 12px rgba(0,150,199,.28);
                        }
                        .suggestion-add-btn i { font-size: .72rem; }

                        /* Stepper +/- cuando el producto ya está en el carrito */
                        .suggestion-stepper {
                            display: none;
                            align-items: center;
                            justify-content: space-between;
                            background: linear-gradient(135deg, #16a34a, #15803d);
                            border-radius: 7px;
                            padding: 2px;
                            box-shadow: 0 4px 10px rgba(22,163,74,.22);
                        }
                        .suggestion-step-btn {
                            width: 26px;
                            height: 26px;
                            background: rgba(255,255,255,.18);
                            border: none;
                            border-radius: 5px;
                            color: #fff;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: .68rem;
                            transition: all .15s;
                        }
                        .suggestion-step-btn:hover { background: rgba(255,255,255,.32); }
                        .suggestion-step-btn:active { transform: scale(.92); }
                        .suggestion-step-count {
                            color: #fff;
                            font-weight: 800;
                            font-size: .92rem;
                            min-width: 28px;
                            text-align: center;
                            line-height: 1;
                        }

                        /* Estado "added": item con borde verde + badge */
                        .suggestion-item.added {
                            border-color: rgba(22,163,74,.45);
                            background: linear-gradient(180deg, rgba(22,163,74,.05), #fff 80%);
                        }
                        .suggestion-item.added .suggestion-add-btn { display: none; }
                        .suggestion-item.added .suggestion-stepper { display: flex; }
                        .suggestion-item.added .suggestion-checkbox { display: none; }

                        /* Badge "agregado" arriba a la izq */
                        .suggestion-added-badge {
                            position: absolute;
                            top: 6px;
                            left: 6px;
                            width: 20px;
                            height: 20px;
                            background: linear-gradient(135deg, #16a34a, #15803d);
                            color: #fff;
                            border-radius: 50%;
                            display: none;
                            align-items: center;
                            justify-content: center;
                            font-size: .6rem;
                            z-index: 2;
                            box-shadow: 0 3px 8px rgba(22,163,74,.4);
                            animation: suggestionCheckPop .35s cubic-bezier(.34,1.56,.64,1);
                        }
                        .suggestion-item.added .suggestion-added-badge { display: flex; }
                        @keyframes suggestionCheckPop {
                            from { transform: scale(0); opacity: 0; }
                            to   { transform: scale(1); opacity: 1; }
                        }
                        .suggestion-bulk-add {
                            width: 100%;
                            margin-top: .65rem;
                            padding: .65rem 1rem;
                            background: linear-gradient(135deg, #16a34a, #15803d);
                            color: #fff;
                            border: none;
                            border-radius: 10px;
                            font-weight: 700;
                            font-size: .88rem;
                            cursor: pointer;
                            transition: transform .15s, box-shadow .2s;
                        }
                        .suggestion-bulk-add:hover:not(:disabled) {
                            transform: translateY(-1px);
                            box-shadow: 0 6px 18px rgba(22,163,74,.32);
                        }
                        .suggestion-bulk-add:disabled {
                            background: #cbd5e1;
                            cursor: not-allowed;
                        }
                    </style>

                    </div> <!-- Cierre de checkoutFormContent -->
                </form>
            </div>

            <!-- Order Summary (Desktop Sidebar) -->
            <div id="orderSummarySidebar" class="lg:col-span-1 mt-8 lg:mt-0 hidden">
                <div class="sticky top-24">
                    <div class="card p-6">
                        <h2 class="text-lg font-display text-text-primary mb-4">Resumen del Pedido</h2>

                        <!-- Cart Items -->
                        <div id="orderItems" class="space-y-3 mb-4">
                            <!-- Items will be populated by JavaScript -->
                        </div>


                        <!-- Order Totals -->
                        <div class="border-t border-border-light pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-text-secondary">Subtotal</span>
                                <span id="subtotal" class="text-text-primary">$0</span>
                            </div>
                            <div id="shippingRow" class="flex justify-between text-sm hidden">
                                <span class="text-text-secondary">Envío</span>
                                <span id="shippingCost" class="text-text-primary">$0</span>
                            </div>
                            <div id="discountRow" class="flex justify-between text-sm hidden">
                                <span class="text-text-secondary">Descuento por pago en efectivo</span>
                                <span id="discountAmount" class="text-success">-$0</span>
                            </div>
                            <div id="couponDiscountRow" class="flex justify-between text-sm hidden">
                                <span id="couponDiscountLabel" class="text-text-secondary">Descuento por cupón</span>
                                <span id="couponDiscountAmount" class="text-success">-$0</span>
                            </div>
                            <div class="border-t border-border-light pt-2 flex justify-between font-semibold">
                                <span class="text-text-primary">Total</span>
                                <span id="finalTotal" class="text-primary text-lg">$0</span>
                            </div>
                        </div>

                        <!-- Notas al Local -->
                        <div class="mt-4">
                            <label for="notes" class="block text-sm font-medium text-text-primary mb-1">Notas al Local</label>
                            <textarea id="notes" name="notes" form="checkoutForm" rows="3" class="input-field"
                                placeholder="Agregá notas adicionales para tu pedido (opcional)..."></textarea>
                        </div>

                        <!-- Warning Notice -->
                        <div class="mt-4 mb-4">
                            <div class="alert alert-danger border-danger bg-danger bg-opacity-10 border-2 rounded-lg p-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle text-danger me-2 fs-5"></i>
                                    <div>
                                        <small class="text-danger fw-semibold">IMPORTANTE:</small>
                                        <small class="text-danger ms-1">Tu pedido <strong>NO está confirmado</strong> hasta
                                            que te lo confirmemos por WhatsApp</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" form="checkoutForm" id="placeOrderBtn" disabled
                            class="w-full mt-2 flex items-center justify-center px-6 py-3 rounded-lg font-semibold text-white transition-all duration-300"
                            style="background-color: #25D366; opacity:.55; cursor:not-allowed;"
                            onmouseover="if(!this.disabled){this.style.backgroundColor='#20BA5A'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(37, 211, 102, 0.3)';}"
                            onmouseout="this.style.backgroundColor='#25D366'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <i class="fab fa-whatsapp me-2 text-xl"></i>
                            <span id="placeOrderText">Confirmar pedido por WhatsApp</span>
                            <div id="placeOrderSpinner" class="hidden ml-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                            </div>
                        </button>

                        <!-- Security Notice -->
                        <div class="mt-4 flex items-center space-x-2 text-sm text-text-secondary">
                            <i class="fas fa-shield-alt text-success"></i>
                            <span>Tus datos están protegidos con encriptación SSL</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="mt-6">
            <!-- Desktop Version -->
            <div class="desktop-filter-appbar d-none d-md-flex justify-content-center p-2">
                <!-- Step 1: Carrito (Completado) -->
                <div class="desktop-filter-btn" id="step1Carrito" style="cursor: pointer;">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/1tcocina.png') }}" alt="Carrito" class="filter-burger-img">
                    </div>
                    <span class="filter-label">CARRITO</span>
                </div>

                <!-- Progress Line -->
                <div class="progress-line active"></div>

                <!-- Step 2: Checkout (Activo) -->
                <div class="desktop-filter-btn active">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/2tcocina.png') }}" alt="Checkout" class="filter-burger-img">
                    </div>
                    <span class="filter-label">CHECKOUT</span>
                </div>
            </div>

            <!-- Mobile Version -->
            <div class="mobile-filter-appbar d-md-none">
                <!-- Step 1: Carrito (Completado) -->
                <div class="mobile-filter-btn" id="step1CarritoMobile" style="cursor: pointer;">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/1tcocina.png') }}" alt="Carrito" class="filter-burger-img">
                    </div>
                    <span class="filter-label">CARRITO</span>
                </div>

                <!-- Progress Line -->
                <div class="progress-line active"></div>

                <!-- Step 2: Checkout (Activo) -->
                <div class="mobile-filter-btn active">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/2tcocina.png') }}" alt="Checkout" class="filter-burger-img">
                    </div>
                    <span class="filter-label">CHECKOUT</span>
                </div>
            </div>
        </div>
    </main>

    {{-- ── OVERLAY CONFIRMACIÓN DE PEDIDO ── --}}
    <div id="order-confirm-overlay" aria-hidden="true" style="display:none; z-index:99999; position:fixed; inset:0;">
        {{-- Fondo verde semitransparente --}}
        <div id="ocoverlay-bg" style="position:absolute; inset:0; background:rgba(22,163,74,.92); backdrop-filter:blur(6px); opacity:0; transition:opacity .4s ease;"></div>

        {{-- Contenido centrado --}}
        <div style="position:relative; z-index:2; display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; padding:2rem; text-align:center;">

            {{-- Check SVG animado en blanco --}}
            <div id="confirm-lottie" style="opacity:0; transform:scale(.6); transition: opacity .5s ease .3s, transform .5s cubic-bezier(.34,1.56,.64,1) .3s;">
                <svg id="oco-check-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:160px;height:160px;">
                    <circle class="oco-circle" cx="50" cy="50" r="44" stroke="rgba(255,255,255,.35)" stroke-width="3"/>
                    <circle class="oco-circle-fill" cx="50" cy="50" r="44" stroke="#fff" stroke-width="3.5"/>
                    <polyline class="oco-check" points="28,50 43,66 72,34" stroke="#fff" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            {{-- Textos --}}
            <h1 id="oco-title" style="font-family:'Bebas Neue',sans-serif; font-size:clamp(2.2rem,8vw,3.8rem); color:#fff; letter-spacing:.04em; margin-top:1.2rem; opacity:0; transform:translateY(16px); transition: opacity .5s ease .6s, transform .5s ease .6s;">
                ¡Pedido enviado!
            </h1>
            <p id="oco-sub" style="font-family:'DM Sans',sans-serif; color:rgba(255,255,255,.85); font-size:1rem; margin-top:.4rem; opacity:0; transition: opacity .5s ease .8s;">
                Abrimos WhatsApp para confirmar tu pedido
            </p>

            {{-- Botón Ver estado — público para todos --}}
            <a id="oco-action-btn" href="#"
               style="display:none; margin-top:2rem; opacity:0; background:#fff; color:#16a34a; padding:.85rem 2.2rem; border-radius:50px; font-family:'DM Sans',sans-serif; font-weight:700; font-size:1rem; text-decoration:none; box-shadow:0 4px 24px rgba(0,0,0,.2); transition: opacity .5s ease, transform .5s ease; transform:translateY(10px); align-items:center; gap:.6rem;">
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#4ade80;flex-shrink:0;animation:ocoDotPulse 1.8s ease-in-out infinite;"></span>
                Ver estado de mi pedido
            </a>
            <style>
                @keyframes ocoDotPulse {
                    0%,100% { opacity:1; transform:scale(1);   box-shadow:0 0 0 0 rgba(74,222,128,.55); }
                    50%     { opacity:.8; transform:scale(1.2); box-shadow:0 0 0 5px rgba(74,222,128,0); }
                }
            </style>
        </div>
    </div>

    <style>
        .oco-circle-fill {
            stroke-dasharray: 276;
            stroke-dashoffset: 276;
        }
        .oco-check {
            stroke-dasharray: 80;
            stroke-dashoffset: 80;
        }
        .oco-circle-fill.animate { animation: ocoDrawCircle .7s cubic-bezier(.4,0,.2,1) forwards; }
        .oco-check.animate       { animation: ocoDrawCheck  .45s ease .65s forwards; }
        @keyframes ocoDrawCircle { to { stroke-dashoffset: 0; } }
        @keyframes ocoDrawCheck  { to { stroke-dashoffset: 0; } }
    </style>

    <!-- Modal de Confirmación de Método de Entrega -->
    <div id="deliveryMethodModal" class="fixed inset-0 hidden" style="z-index: 9999;">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50" id="deliveryMethodModalBackdrop"></div>

        <!-- Modal Panel -->
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl mb-4" style="color: var(--beach-primary, #00b4d8);"></i>
                    <h3 class="text-lg font-semibold text-text-primary mb-4">Confirmar Método de Entrega</h3>
                    <div class="mb-4">
                        <strong id="selectedMethodName" class="text-2xl font-bold" style="color: var(--beach-primary, #00b4d8); text-transform: uppercase; letter-spacing: 0.05em;"></strong>
                    </div>
                    <div class="alert alert-danger border-danger bg-danger bg-opacity-10 border-2 rounded-lg p-3 mb-6">
                        <strong>IMPORTANTE:</strong> Esta selección no se podrá modificar más adelante. ¿Estás seguro?
                    </div>

                    <div class="flex space-x-3">
                        <button id="cancelDeliveryMethod"
                            class="flex-1 py-2 px-4 border border-border rounded-lg text-text-primary hover:bg-background transition-colors">
                            Cancelar
                        </button>
                        <button id="confirmDeliveryMethod"
                            class="flex-1 py-2 px-4 text-white rounded-lg transition-colors"
                            style="background-color: var(--beach-primary, #00b4d8);"
                            onmouseover="this.style.opacity='0.9'"
                            onmouseout="this.style.opacity='1'">
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @guest
        @if (!($loyaltyOffline ?? false))
        <!-- Modal: Beneficios por ingresar con Google (solo cuando el álbum está encendido) -->
        <div id="googleLoginUpsellModal" class="fixed inset-0 hidden" style="z-index: 9998;">
            <div id="googleLoginUpsellBackdrop" class="absolute inset-0 bg-black bg-opacity-55"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="google-upsell-panel w-full max-w-lg rounded-2xl p-5">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="google-upsell-icon">
                                <i class="fab fa-google"></i>
                            </span>
                            <h3 class="google-upsell-title mb-0">¿Sabías que podés sumar premios?</h3>
                        </div>
                        <button id="googleLoginUpsellClose" type="button" class="google-upsell-close" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <p class="google-upsell-text mb-3">
                        Si ingresás con Google, cada combo de tu pedido suma 1 figurita en tu Álbum de Tcocina.
                        Con 10 figuritas, podés canjear un premio.
                    </p>

                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a id="googleLoginUpsellCta" href="{{ route('auth.google.redirect', ['return_to' => route('checkout')]) }}" class="google-brand-btn google-brand-btn-md flex-fill">
                            <svg class="google-glyph" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                            </svg>
                            <span>Ingresar con Google</span>
                        </a>
                        <button id="googleLoginUpsellSkip" type="button" class="btn btn-outline-secondary flex-fill">
                            Seguir sin ingresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endguest



    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed top-20 right-4 z-50 space-y-2"></div>

    <!-- Order Item Template -->
    <template id="orderItemTemplate">
        <div class="flex items-center space-x-3 py-2">
            <img class="w-12 h-12 rounded-lg object-cover" src alt />
            <div class="flex-1 min-w-0">
                <h4 class="font-medium text-text-primary text-sm truncate item-name"></h4>
                <p class="text-text-secondary text-xs item-options"></p>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-sm text-text-secondary item-quantity">Cantidad: 1</span>
                    <span class="font-medium text-primary text-sm item-total">$0</span>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal de personalización rápida para acompañamientos / combos -->
    <div class="modal fade" id="checkoutCustomizeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutCustomizeModalTitle">Personalizar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="checkoutCustomizeProductName"></p>

                    <!-- Dip (solo acompañamientos cat 2) -->
                    <div class="mb-3" id="checkoutCustomizeDipWrapper">
                        <label class="form-label small fw-medium">Dip:</label>
                        <select class="form-select form-select-sm" id="checkoutCustomizeDipSelect">
                            @foreach ($checkoutDipConfigs as $index => $config)
                                <option value="{{ $config->value }}"
                                    data-price-modifier="{{ $config->price_modifier }}"
                                    {{ $index === 0 ? 'selected' : '' }}>
                                    {{ $config->display_value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dip Extra (acompañamientos cat 2 y combos cat 4) -->
                    <div class="mb-3" id="checkoutCustomizeDipExtraWrapper">
                        <label class="form-label small fw-medium">Dip Extra:</label>
                        <select class="form-select form-select-sm" id="checkoutCustomizeDipExtraSelect">
                            <option value="" data-price-modifier="0" selected>Sin dip extra</option>
                            @foreach ($checkoutDipExtraConfigs as $config)
                                <option value="{{ $config->value }}"
                                    data-price-modifier="{{ $config->price_modifier }}">
                                    {{ $config->display_value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" id="checkoutCustomizeConfirmBtn">
                            <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('burger_house/css/main.css') }}" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bangers&display=swap');

        /* ── Checkout stepper: estados locked / active / done ── */
        .step-card {
            position: relative;
            transition: opacity .3s ease, box-shadow .3s ease;
        }
        .step-card[data-state="locked"] {
            opacity: .55;
            pointer-events: none;
            filter: grayscale(.6);
        }
        .step-card[data-state="locked"] .step-body { display: none; }
        .step-card[data-state="active"] .step-body { display: block; }
        .step-card[data-state="done"] .step-body { display: none; }
        .step-card[data-state="done"] .step-active-header { display: none; }
        .step-card[data-state="locked"] .step-done-header { display: none; }
        .step-card[data-state="active"] .step-done-header { display: none; }
        .step-card .step-done-header { display: none; }

        /* Header en estado DONE: compacto con check + resumen + botón editar */
        .step-done-header {
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0;
            margin: 0;
        }
        .step-card[data-state="done"] .step-done-header { display: flex; }
        .step-done-left {
            display: flex;
            align-items: center;
            gap: .85rem;
            min-width: 0;
            flex: 1;
        }
        .step-done-check {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(22,163,74,.32);
        }
        .step-done-text { min-width: 0; flex: 1; }
        .step-done-title {
            font-size: .72rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .5px;
            line-height: 1;
            margin-bottom: 3px;
        }
        .step-done-summary {
            font-size: .95rem;
            font-weight: 700;
            color: #0a2540;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .step-edit-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: #fff;
            color: var(--brand-primary, #00b4d8);
            border: 1px solid rgba(0,180,216,.35);
            border-radius: 8px;
            padding: .45rem .85rem;
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s;
            flex-shrink: 0;
        }
        .step-edit-btn:hover {
            background: var(--brand-primary, #00b4d8);
            color: #fff;
        }

        /* Botón Continuar (cierra el step y avanza al siguiente) */
        .step-continue-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            width: 100%;
            margin-top: 1.25rem;
            padding: .75rem 1rem;
            background: linear-gradient(135deg, #0096c7, #0c6568);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: .95rem;
            cursor: pointer;
            transition: transform .15s, box-shadow .2s;
        }
        .step-continue-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0,150,199,.32);
        }
        .step-continue-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        /* Card con borde verde sutil cuando está done */
        .step-card[data-state="done"] {
            border: 1px solid rgba(22,163,74,.22) !important;
            background: linear-gradient(180deg, rgba(22,163,74,.04), rgba(255,255,255,1)) !important;
            padding: .85rem 1rem !important;
        }

        /* Agrupar cards done consecutivas: gap chico entre ellas */
        .step-card[data-state="done"] + .step-card[data-state="done"] {
            margin-top: -.85rem !important;
            border-top: 1px solid rgba(22,163,74,.14) !important;
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }
        .step-card[data-state="done"]:has(+ .step-card[data-state="done"]) {
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-bottom: none !important;
        }
        /* La que rompe la cadena (activa después de varias done) tiene espacio extra arriba */
        .step-card[data-state="done"] + .step-card[data-state="active"] {
            margin-top: 1.25rem !important;
        }

        /* Indicador de paso pendiente (locked) */
        .step-locked-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(107,114,128,.12);
            color: #6b7280;
            font-size: .68rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        .step-card[data-state="locked"] .step-locked-badge { display: inline-block; }
        .step-card .step-locked-badge { display: none; }

        /* ── Progress bar de flujo ── */
        .flow-progress-hero {
            background: linear-gradient(160deg, #0c1929 0%, #0f2744 60%, #0c1929 100%);
            padding: 1.1rem 1rem 1.8rem;
        }
        .flow-steps { display: flex; align-items: center; justify-content: center; }
        .flow-step  { display: flex; align-items: center; gap: .4rem; }
        .flow-step-circle {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 700; flex-shrink: 0;
        }
        .flow-step-circle.done    { background: #10b981; color: #fff; }
        .flow-step-circle.active  { background: #0096c7; color: #fff; }
        .flow-step-circle.pending { background: rgba(255,255,255,.13); color: rgba(200,214,232,.45); }
        .flow-step-label { font-size: .7rem; font-weight: 600; }
        .flow-step-label.done    { color: #10b981; }
        .flow-step-label.active  { color: #93c5fd; }
        .flow-step-label.pending { color: rgba(200,214,232,.38); }
        .flow-step-line { width: 26px; height: 2px; border-radius: 1px; margin: 0 4px; flex-shrink: 0; }
        .flow-step-line.done    { background: #10b981; }
        .flow-step-line.pending { background: rgba(255,255,255,.12); }

        /* ── Método de entrega ── */
        .delivery-option {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: 1.4rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s, background .18s, transform .14s, box-shadow .18s;
            width: 100%;
            text-align: center;
        }

        .delivery-option:hover {
            border-color: var(--beach-primary, #00b4d8);
            background: rgba(0, 180, 216, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 150, 199, 0.14);
        }

        .delivery-option.active {
            border-color: var(--beach-primary, #00b4d8) !important;
            background: rgba(0, 180, 216, 0.08) !important;
            box-shadow: 0 0 0 4px rgba(0, 180, 216, 0.12), 0 6px 18px rgba(0, 150, 199, 0.14) !important;
        }

        .delivery-option-label {
            font-weight: 700;
            font-size: .95rem;
            color: #1e3a5f;
            letter-spacing: .1px;
        }

        .delivery-option-sub {
            font-size: .73rem;
            color: #9ca3af;
            margin-top: -.2rem;
        }

        /* ── Métodos de pago (nuevo diseño) ── */
        .payment-methods-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: .65rem;
        }
        .payment-option {
            position: relative;
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: .9rem 1.1rem;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s, background .18s, transform .14s, box-shadow .18s;
            margin: 0;
        }
        .payment-option:hover {
            border-color: var(--accent, #00b4d8);
            background: color-mix(in srgb, var(--accent, #00b4d8) 4%, #fff);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px color-mix(in srgb, var(--accent, #00b4d8) 14%, transparent);
        }
        .payment-option.active {
            border-color: var(--accent, #00b4d8) !important;
            background: color-mix(in srgb, var(--accent, #00b4d8) 6%, #fff) !important;
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent, #00b4d8) 12%, transparent),
                        0 6px 16px color-mix(in srgb, var(--accent, #00b4d8) 16%, transparent) !important;
        }
        .payment-option-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .payment-option-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .payment-option-text { flex: 1; min-width: 0; }
        .payment-option-name {
            font-weight: 700;
            font-size: .95rem;
            color: #0a2540;
            line-height: 1.15;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            flex-wrap: wrap;
        }
        .payment-option-desc {
            font-size: .76rem;
            color: #6b7280;
            margin-top: 1px;
        }

        /* Badge -X% OFF al lado del nombre Efectivo */
        .payment-promo-badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .3px;
            padding: 2px 8px;
            border-radius: 999px;
            box-shadow: 0 3px 8px rgba(22,163,74,.32);
            text-transform: uppercase;
            animation: promoBadgePulse 2.4s ease-in-out infinite;
        }
        .payment-promo-badge i { font-size: .62rem; }
        @keyframes promoBadgePulse {
            0%, 100% { transform: scale(1); box-shadow: 0 3px 8px rgba(22,163,74,.32); }
            50%      { transform: scale(1.06); box-shadow: 0 5px 14px rgba(22,163,74,.5); }
        }

        /* Línea "Te ahorrás $X" — solo visible cuando efectivo está seleccionado */
        .payment-savings-line {
            display: none;
            align-items: center;
            gap: .35rem;
            margin-top: 6px;
            font-size: .82rem;
            font-weight: 600;
            color: #15803d;
        }
        .payment-savings-line strong {
            font-size: 1rem;
            color: #14532d;
            font-weight: 800;
        }
        .payment-savings-line i { color: #16a34a; }
        .payment-option.active .payment-savings-line { display: inline-flex; }
        .payment-option-check {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            color: transparent;
            flex-shrink: 0;
            transition: all .18s;
        }
        .payment-option.active .payment-option-check {
            background: var(--accent, #00b4d8);
            border-color: var(--accent, #00b4d8);
            color: #fff;
        }

        /* ── Prompt de selección ── */
        #deliveryMethodPrompt {
            background: rgba(0, 150, 199, 0.07) !important;
            border: 1px solid rgba(0, 150, 199, 0.25) !important;
            border-radius: 12px !important;
        }

        /* Estilos para el aviso de confirmación en checkout */
        .alert-danger {
            border-left: 4px solid #dc3545 !important;
            animation: subtle-pulse 3s infinite;
        }

        @keyframes subtle-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.2);
            }

            50% {
                box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.2);
            }
        }

        .alert-danger:hover {
            transform: translateY(-1px);
            transition: transform 0.2s ease;
        }

        /* Asegurar que el texto sea legible */
        .alert-danger small {
            line-height: 1.4;
        }

        .google-upsell-panel {
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            border: 1px solid rgba(0, 180, 216, 0.25);
            box-shadow: 0 20px 48px rgba(2, 8, 23, 0.35);
        }

        .google-upsell-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }

        .google-upsell-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, var(--beach-primary, #00b4d8), #1d4ed8);
            box-shadow: 0 8px 18px rgba(0, 180, 216, 0.35);
            flex-shrink: 0;
        }

        .google-upsell-text {
            font-size: .95rem;
            color: #334155;
            line-height: 1.5;
        }

        .google-upsell-close {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .2s ease;
        }

        .google-upsell-close:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* Estilos para el botón de WhatsApp */
        #placeOrderBtn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        #placeOrderBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        }

        #placeOrderBtn i.fab.fa-whatsapp {
            transition: transform 0.3s ease;
        }

        #placeOrderBtn:hover i.fab.fa-whatsapp {
            transform: scale(1.1);
        }

        /* Animación sutil para el icono cuando está cargando */
        #placeOrderBtn:disabled {
            opacity: 0.7;
        }

        /* Progress Indicator Styles - Fixed at bottom - Always fixed */
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
            align-items: center; /* Centrar verticalmente todos los elementos */
        }

        .desktop-filter-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 12px 20px;
            border-radius: 16px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 70px;
            min-width: 160px;
        }

        .desktop-filter-btn.active {
            background: linear-gradient(rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0.15)),
                url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgb(40, 68, 151);
            color: white !important;
        }

        .desktop-filter-btn.active .filter-label {
            color: white !important;
        }

        .desktop-filter-btn:not(.active) {
            color: #666;
            cursor: pointer;
        }

        .desktop-filter-btn:not(.active):hover {
            opacity: 0.8;
        }

        .desktop-filter-btn .filter-icon {
            font-size: 32px;
            margin-bottom: 12px;
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
            transform: scale(3.5) translateY(-32%);
        }

        /* Mobile App Bar - Fixed at bottom like catalog - Always fixed */
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
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
        }

        /* Add padding to main content to prevent content from being hidden behind fixed progress bar */
        main {
            padding-bottom: 120px;
        }
        
        /* Asegurar que el footer quede visible sobre la barra fija */
        footer.footer-themed {
            margin-bottom: 100px !important;
            position: relative;
            z-index: 999;
        }

        @media (max-width: 767px) {
            main {
                padding-bottom: 100px;
            }
            
            footer.footer-themed {
                margin-bottom: 100px !important;
            }
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
            flex: 1;
        }

        .mobile-filter-btn.active {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgb(40, 68, 151);
            color: white !important;
        }

        .mobile-filter-btn.active .filter-label {
            color: white !important;
        }

        .mobile-filter-btn:not(.active) {
            color: #666;
            cursor: pointer;
        }

        .mobile-filter-btn:not(.active):hover {
            opacity: 0.8;
        }

        .mobile-filter-btn .filter-icon {
            font-size: 24px;
            margin-bottom: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-burger-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .mobile-filter-btn.active .filter-burger-img {
            transform: scale(3.5) translateY(-32%);
        }

        .mobile-filter-btn .filter-label {
            font-size: 11px;
            font-weight: 500;
            text-align: center;
            line-height: 1.2;
        }

        .progress-line {
            width: 60px;
            height: 3px;
            background: #e0e0e0;
            border-radius: 2px;
            transition: all 0.3s ease;
            position: relative;
            align-self: center; /* Centrar verticalmente en el flex container */
            flex-shrink: 0; /* Evitar que se comprima */
        }

        .progress-line.active {
            background: linear-gradient(90deg, rgba(40, 167, 69, 0.8) 0%, var(--beach-primary) 100%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .progress-line::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: rgba(40, 167, 69, 0.8);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.6);
        }

        .progress-line::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: var(--beach-primary);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
        }

        /* Mobile filter appbar styles are already defined in catalog.blade.php */
        /* We just need to override the background color for active state */
        .mobile-filter-btn.active {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgb(40, 68, 151) !important;
        }

        /* OTP Input Styles */
        .otp-slot {
            width: 50px;
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
            text-transform: uppercase;
            background: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .otp-slot:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.2);
            transform: scale(1.05);
        }

        .otp-slot.filled {
            border-color: var(--brand-primary);
            background: rgba(0, 180, 216, 0.05);
        }

        .otp-slot.error {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.05);
        }

        @media (max-width: 768px) {
            .otp-slot {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }

        /* Animación para cupón aplicado */
        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }

        /* Botón aplicar cupón hover */
        #applyCouponBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Confetti Animation */
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
        }

        /* Acompanamientos Section Styles */
        .acompanamientos-grid {
            display: flex;
            gap: 8px;
            padding: 4px 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            flex-wrap: nowrap;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .acompanamientos-grid::-webkit-scrollbar {
            display: none;
        }

        .acompanamiento-item {
            transition: all 0.2s ease;
            min-width: 120px;
            max-width: 120px;
            flex-shrink: 0;
            scroll-snap-align: start;
        }

        .acompanamiento-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .acompanamiento-item.selected {
            border-color: var(--primary, #284497);
            background-color: rgba(40, 68, 151, 0.05);
        }

        .acompanamiento-item img {
            height: 60px;
        }

        .acompanamiento-checkbox {
            z-index: 10;
        }

        .acompanamiento-add-btn {
            transition: all 0.2s ease;
        }

        .acompanamiento-add-btn:hover {
            transform: scale(1.05);
        }

        /* Acompanamientos section card highlight */
        .acompanamientos-card {
            background: linear-gradient(135deg, rgba(0, 180, 216, 0.08) 0%, rgba(40, 68, 151, 0.08) 100%);
            border: 2px solid var(--beach-primary, #00b4d8);
            box-shadow: 0 4px 16px rgba(0, 180, 216, 0.2);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Get cart data from localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let isDelivery = false;
        let cashDiscountPercentage = {{ $businessSettings['cash_discount_percentage'] ?? 0 }};
        let whatsappNumber = '{{ $businessSettings['whatsapp_number'] ?? '5492494015745' }}';

        let appliedCoupon = null;
        let otpLength = 8;
        const savedAddresses = @json($savedAddresses ?? []);

        // Acompanamientos selection
        let selectedAcompanamientos = new Set();

        // DOM Elements
        const deliveryBtn = document.getElementById('deliveryBtn');
        const pickupBtn = document.getElementById('pickupBtn');
        const deliverySection = document.getElementById('deliverySection');
        const pickupMapSection = document.getElementById('pickupMapSection');

        console.log('DOM Elements check:', {
            deliveryBtn,
            pickupBtn,
            deliverySection,
            pickupMapSection
        });
        const orderItems = document.getElementById('orderItems');
        const subtotalEl = document.getElementById('subtotal');
        const shippingRow = document.getElementById('shippingRow');
        const shippingCost = document.getElementById('shippingCost');
        const discountRow = document.getElementById('discountRow');
        const discountAmount = document.getElementById('discountAmount');
        const finalTotal = document.getElementById('finalTotal');
        const mobileFinalTotal = document.getElementById('mobileFinalTotal');
        const checkoutForm = document.getElementById('checkoutForm');
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const mobilePlaceOrderBtn = document.getElementById('mobilePlaceOrderBtn');
        const placeOrderText = document.getElementById('placeOrderText');
        const placeOrderSpinner = document.getElementById('placeOrderSpinner');
        const toastContainer = document.getElementById('toastContainer');
        const addSelectedAcompanamientosBtn = document.getElementById('addSelectedAcompanamientos');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded - Inicializando checkout');

            // Redirect if cart is empty
            if (cart.length === 0) {
                window.location.href = '{{ route('catalog') }}';
                return;
            }

            @guest
                setupGoogleLoginUpsellModal();
            @endguest
            setupEventListeners();
            setupAcompanamientosListeners();
            renderOrderItems();
            updateOrderSummary();
            // Asegurar render del ahorro de efectivo en el primer paint y un fallback diferido
            updateCashSavingsDisplay();
            setTimeout(updateCashSavingsDisplay, 250);
            loadCustomerData();
            setupFormValidation();
            initCheckoutStepper();
            initCouponToggle();
            renderSuggestionsState();
            // setupProgressBarPosition(); // Deshabilitado - appbars siempre fijos
            // NO establecer método de entrega por defecto - el usuario debe seleccionarlo
        });

        // ════════════════════════════════════════════════════════════════════
        // ── Checkout Stepper: controla el avance secuencial de las cards ──
        // ════════════════════════════════════════════════════════════════════
        const CheckoutStepper = (function() {
            const skipTurno = {{ $businessSettings['skip_turno_selection'] ? 'true' : 'false' }};
            let stepsOrder = []; // se setea cuando se elige método de entrega
            let inlineTurnosLoaded = false;

            function setActive(stepId) {
                const card = document.querySelector(`[data-step="${stepId}"]`);
                if (!card) return;
                card.setAttribute('data-state', 'active');
                reorderSteps();
                setTimeout(() => card.scrollIntoView({ behavior: 'smooth', block: 'center' }), 50);
            }

            function setDone(stepId, summary) {
                const card = document.querySelector(`[data-step="${stepId}"]`);
                if (!card) return;
                card.setAttribute('data-state', 'done');
                if (summary) {
                    const summaryEl = card.querySelector('.step-done-summary');
                    if (summaryEl) summaryEl.textContent = summary;
                }
                updateConfirmButton();
                reorderSteps();
            }

            function setLocked(stepId) {
                const card = document.querySelector(`[data-step="${stepId}"]`);
                if (card) card.setAttribute('data-state', 'locked');
            }

            function nextStepOf(stepId) {
                const idx = stepsOrder.indexOf(stepId);
                if (idx < 0) return null;
                // Buscar el siguiente que NO esté done (para saltear los que ya vienen completos)
                for (let i = idx + 1; i < stepsOrder.length; i++) {
                    const card = document.querySelector(`[data-step="${stepsOrder[i]}"]`);
                    if (card?.getAttribute('data-state') !== 'done') return stepsOrder[i];
                }
                return null;
            }

            function setStepsForDeliveryMethod(isDelivery) {
                // Orden de steps según el método elegido (incluye 'delivery' para el check final)
                stepsOrder = ['delivery', 'contact'];
                if (isDelivery) stepsOrder.push('address');
                if (!skipTurno) stepsOrder.push('schedule');
                stepsOrder.push('payment');

                // 'delivery' ya viene como DONE desde setDeliveryMethod (no lo toco)
                // De los demás: contact ACTIVE, el resto LOCKED
                const remainingSteps = stepsOrder.slice(1);
                remainingSteps.forEach((step, i) => {
                    if (i === 0) setActive(step);
                    else setLocked(step);
                });
                updateConfirmButton();
            }

            function validateContact() {
                const firstName = document.getElementById('firstName')?.value.trim();
                const lastName  = document.getElementById('lastName')?.value.trim();
                const phone     = document.getElementById('phone')?.value.trim();
                if (!firstName || !lastName || !phone) {
                    showToast('Completá nombre, apellido y teléfono', 'error');
                    return null;
                }
                if (phone.replace(/\D/g, '').length < 8) {
                    showToast('Ingresá un teléfono válido', 'error');
                    return null;
                }
                return `${firstName} ${lastName} · ${phone}`;
            }

            function validateAddress() {
                const street = document.getElementById('street')?.value.trim();
                const number = document.getElementById('number')?.value.trim();
                if (!street || !number) {
                    showToast('Completá calle y número', 'error');
                    return null;
                }
                const floor = document.getElementById('floor')?.value.trim();
                return `${street} ${number}${floor ? ' · ' + floor : ''}`;
            }

            function validateSchedule() {
                const microturnoId = document.getElementById('microturno_id')?.value;
                if (!microturnoId) {
                    showToast('Elegí un horario', 'error');
                    return null;
                }
                const selectedBtn = document.querySelector(`.inline-turno-btn.selected`);
                if (selectedBtn) {
                    return selectedBtn.dataset.formattedTime || selectedBtn.textContent.trim();
                }
                return 'Horario seleccionado';
            }

            function validatePayment() {
                const selected = document.querySelector('input[name="paymentMethod"]:checked');
                if (!selected) {
                    showToast('Elegí un método de pago', 'error');
                    return null;
                }
                const label = selected.closest('.payment-option')?.querySelector('.payment-option-name')?.firstChild?.textContent?.trim();
                let summary = label || selected.value;
                if (selected.value === 'cash') {
                    const savings = document.getElementById('cashSavingsAmount')?.textContent;
                    if (savings && savings !== '$0') summary += ` · ahorrás ${savings}`;
                }
                return summary;
            }

            function continueStep(stepId) {
                let summary = null;
                if (stepId === 'contact')  summary = validateContact();
                if (stepId === 'address')  summary = validateAddress();
                if (stepId === 'schedule') summary = validateSchedule();
                if (stepId === 'payment')  summary = validatePayment();
                if (!summary) return;

                setDone(stepId, summary);
                const next = nextStepOf(stepId);
                if (next) {
                    setActive(next);
                    if (next === 'schedule') ensureInlineTurnos();
                }
            }

            function editStep(stepId) {
                setActive(stepId);
                if (stepId === 'schedule') ensureInlineTurnos();
                if (stepId === 'delivery') expandDeliveryMethodSection();
                reorderSteps();
            }

            // No reordenamos el DOM: el orden visual lo dicta el orden natural del HTML.
            // El CSS se encarga de compactar las cards DONE consecutivas.
            function reorderSteps() { /* noop */ }

            // ── Selector inline de horarios ─────────────────────────────────
            async function ensureInlineTurnos() {
                if (inlineTurnosLoaded) return;
                inlineTurnosLoaded = true;
                await loadInlineTurnos();
            }

            async function loadInlineTurnos() {
                const container = document.getElementById('inlineMicroturnosContainer');
                if (!container) return;
                container.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin" style="color:#0096c7;"></i><span class="ms-2 text-muted small">Cargando horarios...</span></div>';

                try {
                    const cart = JSON.parse(localStorage.getItem('cart')) || [];
                    const cartForAPI = (typeof getCartWithCategories === 'function')
                        ? await getCartWithCategories(cart)
                        : cart;
                    const fecha = '{{ now()->format("Y-m-d") }}';

                    const res = await fetch(`/api/turnos/disponibles?fecha=${fecha}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ cart: cartForAPI })
                    });
                    const data = await res.json();
                    if (!data.success || data.microturnos.length === 0) {
                        container.innerHTML = '<div class="text-center py-3 text-muted small">No hay horarios disponibles para hoy</div>';
                        return;
                    }

                    let html = '<div class="inline-turnos-grid">';
                    let anyVisible = false;
                    data.microturnos.forEach(m => {
                        if (m.is_past) return;
                        anyVisible = true;
                        let capClass = '';
                        if (m.disponible_para_carrito) {
                            const pct = Math.round(((m.capacidad_maxima - m.capacidad_restante) / m.capacidad_maxima) * 100);
                            capClass = pct < 40 ? 'cap-green' : pct < 70 ? 'cap-yellow' : pct < 90 ? 'cap-orange' : 'cap-red';
                        }
                        html += `<button type="button" class="inline-turno-btn ${capClass}" data-microturno-id="${m.sort_order}" data-formatted-time="${m.formatted_time}" ${!m.disponible_para_carrito ? 'disabled' : ''}>${m.formatted_time}</button>`;
                    });
                    html += '</div>';

                    if (!anyVisible) {
                        container.innerHTML = '<div class="text-center py-3 text-muted small">No hay horarios disponibles para hoy</div>';
                        return;
                    }
                    container.innerHTML = html;

                    // Pre-seleccionar el horario que viene de sessionStorage
                    const preSelected = sessionStorage.getItem('selectedMicroturno');
                    if (preSelected) {
                        const btn = container.querySelector(`[data-microturno-id="${preSelected}"]`);
                        if (btn) btn.classList.add('selected');
                        document.getElementById('microturno_id').value = preSelected;
                        const continueBtn = document.querySelector('[data-continue-step="schedule"]');
                        if (continueBtn) continueBtn.disabled = false;
                    }
                } catch (e) {
                    console.error('Error inline turnos:', e);
                    container.innerHTML = '<div class="text-center py-3 text-danger small">Error al cargar horarios</div>';
                }
            }

            // Click en horario inline
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.inline-turno-btn');
                if (!btn || btn.disabled) return;
                document.querySelectorAll('.inline-turno-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                document.getElementById('microturno_id').value = btn.dataset.microturnoId;
                sessionStorage.setItem('selectedMicroturno', btn.dataset.microturnoId);
                sessionStorage.setItem('deliveryDate', '{{ now()->format("Y-m-d") }}');
                const continueBtn = document.querySelector('[data-continue-step="schedule"]');
                if (continueBtn) continueBtn.disabled = false;
            });

            // Botones Continuar
            document.addEventListener('click', function(e) {
                const cont = e.target.closest('[data-continue-step]');
                if (cont) {
                    e.preventDefault();
                    continueStep(cont.dataset.continueStep);
                }
                const edit = e.target.closest('[data-edit-step]');
                if (edit) {
                    e.preventDefault();
                    editStep(edit.dataset.editStep);
                }
            });

            // Auto-avance para método de pago (radio change)
            document.addEventListener('change', function(e) {
                if (e.target.name === 'paymentMethod') {
                    // Solo auto-resolver si "payment" está active
                    const card = document.querySelector('[data-step="payment"]');
                    if (card?.getAttribute('data-state') === 'active') {
                        // Pequeña pausa para que el usuario vea su selección antes de cerrar
                        // No auto-avanza acá: dejamos que el usuario apriete Continuar
                        // (porque puede querer ver el ahorro y el cash change)
                    }
                }
            });

            // Habilita el botón final solo cuando todos los steps requeridos están DONE
            function updateConfirmButton() {
                // Si todavía no se eligió método de entrega, mantener disabled
                const allDone = stepsOrder.length > 0 && stepsOrder.every(step => {
                    const card = document.querySelector(`[data-step="${step}"]`);
                    return card?.getAttribute('data-state') === 'done';
                });

                const placeOrderBtn = document.getElementById('placeOrderBtn');
                const mobilePlaceOrderBtn = document.getElementById('mobilePlaceOrderBtn');
                [placeOrderBtn, mobilePlaceOrderBtn].forEach(btn => {
                    if (!btn) return;
                    btn.disabled = !allDone;
                    btn.style.opacity = allDone ? '1' : '.55';
                    btn.style.cursor = allDone ? 'pointer' : 'not-allowed';
                });
            }

            // Exponer API pública
            return {
                start: setStepsForDeliveryMethod,
                setDone, setActive, setLocked,
                updateConfirmButton,
                ensureInlineTurnos
            };
        })();

        function initCheckoutStepper() {
            // Si vienen con horario pre-seleccionado y el form ya estaba visible, no hacer nada
            // El stepper se inicia al elegir delivery/retiro
        }

        // ── Cupón colapsable ──────────────────────────────────────────────
        function initCouponToggle() {
            const card    = document.getElementById('couponCard');
            const trigger = document.getElementById('couponTrigger');
            if (!card || !trigger) return;

            trigger.addEventListener('click', () => {
                const isOpen = card.classList.toggle('open');
                trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        // Cuando se aplica un cupón con éxito, mantener la card abierta para mostrar el estado
        document.addEventListener('coupon:applied', () => {
            document.getElementById('couponCard')?.classList.add('open');
        });

        // Función deshabilitada - Los appbars ahora siempre están fijos en bottom: 0
        // function setupProgressBarPosition() {
        //     // Esta función ha sido deshabilitada para mantener los appbars siempre fijos en la parte inferior
        // }

        @guest
            function setupGoogleLoginUpsellModal() {
                const modal = document.getElementById('googleLoginUpsellModal');
                const backdrop = document.getElementById('googleLoginUpsellBackdrop');
                const closeBtn = document.getElementById('googleLoginUpsellClose');
                const skipBtn = document.getElementById('googleLoginUpsellSkip');
                const ctaBtn = document.getElementById('googleLoginUpsellCta');
                const seenKey = 'checkout_google_upsell_seen_v2';

                if (!modal || sessionStorage.getItem(seenKey) === '1') {
                    return;
                }

                const hideModal = () => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                };

                const markSeenAndHide = () => {
                    sessionStorage.setItem(seenKey, '1');
                    hideModal();
                };

                if (closeBtn) closeBtn.addEventListener('click', markSeenAndHide);
                if (skipBtn) skipBtn.addEventListener('click', markSeenAndHide);
                if (backdrop) backdrop.addEventListener('click', markSeenAndHide);
                if (ctaBtn) ctaBtn.addEventListener('click', () => sessionStorage.setItem(seenKey, '1'));

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        markSeenAndHide();
                    }
                });

                // Mostrar con una pequeña demora para no cortar la carga inicial.
                // Solo marcar como visto cuando el usuario cierra/salta (markSeenAndHide), no al mostrar.
                setTimeout(() => {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }, 650);
            }
        @endguest

        // Event Listeners
        function setupEventListeners() {
            // Step 1 (Carrito) click - redirect to cart
            const step1Carrito = document.getElementById('step1Carrito');
            const step1CarritoMobile = document.getElementById('step1CarritoMobile');
            
            if (step1Carrito) {
                step1Carrito.addEventListener('click', function() {
                    window.location.href = '{{ route('cart') }}';
                });
            }
            
            if (step1CarritoMobile) {
                step1CarritoMobile.addEventListener('click', function() {
                    window.location.href = '{{ route('cart') }}';
                });
            }

            // Delivery/Pickup toggle - mostrar modal de confirmación
            let pendingDeliveryMethod = null;
            const deliveryMethodModal = document.getElementById('deliveryMethodModal');
            const selectedMethodName = document.getElementById('selectedMethodName');
            const confirmDeliveryMethod = document.getElementById('confirmDeliveryMethod');
            const cancelDeliveryMethod = document.getElementById('cancelDeliveryMethod');
            const deliveryMethodModalBackdrop = document.getElementById('deliveryMethodModalBackdrop');

            @if ($isDeliveryAvailable)
                deliveryBtn.addEventListener('click', function() {
                    pendingDeliveryMethod = true;
                    selectedMethodName.textContent = 'DELIVERY';
                    showDeliveryMethodModal();
                });
            @endif

            @if ($isPickupAvailable)
                pickupBtn.addEventListener('click', function() {
                    pendingDeliveryMethod = false;
                    selectedMethodName.textContent = 'RETIRO EN LOCAL';
                    showDeliveryMethodModal();
                });
            @endif

            // Event listeners del modal
            if (confirmDeliveryMethod) {
                confirmDeliveryMethod.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Confirm button clicked, pendingDeliveryMethod:', pendingDeliveryMethod);
                    try {
                        if (pendingDeliveryMethod !== null) {
                            setDeliveryMethod(pendingDeliveryMethod);
                        }
                    } catch (error) {
                        console.error('Error setting delivery method:', error);
                    }
                    console.log('About to hide modal');
                    hideDeliveryMethodModal();
                    console.log('Modal hidden');
                });
            }

            if (cancelDeliveryMethod) {
                cancelDeliveryMethod.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Cancel button clicked');
                    pendingDeliveryMethod = null;
                    hideDeliveryMethodModal();
                });
            }

            if (deliveryMethodModalBackdrop) {
                deliveryMethodModalBackdrop.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    hideDeliveryMethodModal();
                });
            }

            // Funciones para mostrar/ocultar el modal
            function showDeliveryMethodModal() {
                console.log('Showing modal');
                if (deliveryMethodModal) {
                    deliveryMethodModal.classList.remove('hidden');
                }
            }

            function hideDeliveryMethodModal() {
                console.log('Hiding modal');
                if (deliveryMethodModal) {
                    deliveryMethodModal.classList.add('hidden');
                }
                pendingDeliveryMethod = null;
            }

            // Load selected turn information sólo si el sistema de turnos está ON
            @if (!$businessSettings['skip_turno_selection'])
                loadSelectedTurn();
            @endif

            // Payment method changes
            document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    // Actualizar visual de selección
                    document.querySelectorAll('.payment-option').forEach(label => label.classList.remove('active'));
                    this.closest('.payment-option')?.classList.add('active');

                    const cashSection = document.getElementById('cashChangeSection');
                    if (this.value === 'cash') {
                        cashSection.classList.remove('hidden');
                    } else {
                        cashSection.classList.add('hidden');
                    }
                    updateOrderSummary(); // This will recalculate discount
                });
            });


            // Form submission
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', handleFormSubmit);
            } else {
                console.error('checkoutForm no encontrado');
            }

            // También agregar evento directo al botón por si acaso
            if (placeOrderBtn) {
                placeOrderBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (checkoutForm) {
                        checkoutForm.dispatchEvent(new Event('submit'));
                    }
                });
            }

            // Mobile button submission
            if (mobilePlaceOrderBtn) {
                mobilePlaceOrderBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Mobile button clicked');
                    
                    // Obtener el formulario y crear un evento de submit
                    const form = document.getElementById('checkoutForm');
                    if (form) {
                        console.log('Form found, calling handleFormSubmit');
                        // Crear un evento sintético con el formulario como target
                        const submitEvent = {
                            preventDefault: function() {},
                            target: form
                        };
                        handleFormSubmit(submitEvent);
                    } else {
                        console.error('Form not found!');
                        showToast('Error: Formulario no encontrado', 'error');
                    }
                });
            } else {
                console.warn('mobilePlaceOrderBtn not found');
            }

            // Auto-save customer data
            const customerFields = ['firstName', 'lastName', 'phone'];
            customerFields.forEach(field => {
                document.getElementById(field).addEventListener('input', saveCustomerData);
            });

            const savedAddressSelect = document.getElementById('savedAddressId');
            if (savedAddressSelect) {
                savedAddressSelect.addEventListener('change', applySelectedAddress);
                const defaultAddress = savedAddresses.find(address => address.is_default);
                if (defaultAddress) {
                    savedAddressSelect.value = String(defaultAddress.id);
                    applySelectedAddress();
                }
            }
        }

        // Set delivery method
        function setDeliveryMethod(delivery) {
            console.log('setDeliveryMethod called with delivery:', delivery);
            isDelivery = delivery;

            // Ocultar el mensaje "Para avanzar, informanos cómo vas a recibir tu pedido"
            const deliveryMethodPrompt = document.getElementById('deliveryMethodPrompt');
            if (deliveryMethodPrompt) {
                deliveryMethodPrompt.classList.add('hidden');
            }

            // Mostrar el resto del formulario cuando se selecciona un método
            const checkoutFormContent = document.getElementById('checkoutFormContent');
            if (checkoutFormContent) {
                checkoutFormContent.classList.remove('hidden');
            }

            // Colapsar la sección de método de entrega y mostrar el label elegido
            const label = delivery ? 'Delivery' : 'Retiro en local';
            const badge = document.getElementById('deliveryMethodSelectedLabel');
            if (badge) { badge.textContent = label; badge.classList.remove('d-none'); }
            collapseDeliveryMethodSection();

            if (typeof CheckoutStepper !== 'undefined') {
                CheckoutStepper.setDone('delivery', label);
            }

            // Mostrar el resumen del pedido cuando se selecciona un método
            const orderSummarySidebar = document.getElementById('orderSummarySidebar');
            if (orderSummarySidebar) {
                orderSummarySidebar.classList.remove('hidden');
            }

            // Cambiar columna del form a 2/3 para dar lugar al sidebar
            const checkoutFormCol = document.getElementById('checkoutFormCol');
            if (checkoutFormCol) {
                checkoutFormCol.classList.replace('lg:col-span-3', 'lg:col-span-2');
            }

            // Quitar el max-width centrado de la card de delivery para alinear con el resto
            const deliveryMethodSection = document.getElementById('deliveryMethodSection');
            if (deliveryMethodSection) {
                deliveryMethodSection.style.maxWidth = '';
                deliveryMethodSection.style.margin = '';
            }

            // Iniciar el stepper con el orden correcto según el método elegido
            if (typeof CheckoutStepper !== 'undefined') {
                // Si viene con horario pre-seleccionado, marcarlo ANTES de start() para evitar
                // race condition donde el cliente complete contact antes de que schedule sea DONE
                const preSelected = sessionStorage.getItem('selectedMicroturno');
                const preLabel    = sessionStorage.getItem('selectedTurnFormatted') || 'Horario seleccionado';

                CheckoutStepper.start(delivery);

                if (preSelected) {
                    document.getElementById('microturno_id').value = preSelected;
                    CheckoutStepper.setDone('schedule', preLabel);
                    // Cargar inline turnos en background para que el botón Editar funcione bien
                    CheckoutStepper.ensureInlineTurnos();
                }
            }

            // Cambiar título del hero a "FINALIZAR PEDIDO"
            const heroTitle = document.getElementById('heroTitle');
            const heroSubtitle = document.getElementById('heroSubtitle');
            if (heroTitle) {
                heroTitle.style.transition = 'opacity .25s';
                heroTitle.style.opacity = '0';
                setTimeout(() => {
                    heroTitle.textContent = 'FINALIZAR PEDIDO';
                    heroTitle.style.opacity = '1';
                }, 250);
            }
            if (heroSubtitle) {
                heroSubtitle.style.transition = 'opacity .25s';
                heroSubtitle.style.opacity = '0';
                setTimeout(() => {
                    heroSubtitle.textContent = 'Completá tu información y confirmá';
                    heroSubtitle.style.opacity = '1';
                }, 250);
            }
            
            // Función deshabilitada - Los appbars ahora siempre están fijos en bottom: 0
            // setTimeout(() => {
            //     if (typeof updateProgressBarPosition === 'function') {
            //         updateProgressBarPosition();
            //     } else if (typeof setupProgressBarPosition === 'function') {
            //         setupProgressBarPosition();
            //     }
            // }, 100);

            if (delivery) {
                console.log('Setting delivery to true');
                deliveryBtn.classList.add('active', 'border-primary', 'bg-primary-50');
                deliveryBtn.classList.remove('border-border', 'bg-surface');
                const deliveryIcon = deliveryBtn.querySelector('lord-icon');
                if (deliveryIcon) {
                    deliveryIcon.classList.add('text-primary');
                    deliveryIcon.classList.remove('text-text-secondary');
                }
                const deliveryText = deliveryBtn.querySelector('.font-medium');
                if (deliveryText) {
                    deliveryText.classList.add('text-primary');
                    deliveryText.classList.remove('text-text-primary');
                }

                pickupBtn.classList.remove('active', 'border-primary', 'bg-primary-50');
                pickupBtn.classList.add('border-border', 'bg-surface');
                const pickupIcon = pickupBtn.querySelector('lord-icon');
                if (pickupIcon) {
                    pickupIcon.classList.remove('text-primary');
                    pickupIcon.classList.add('text-text-secondary');
                }
                const pickupText = pickupBtn.querySelector('.font-medium');
                if (pickupText) {
                    pickupText.classList.remove('text-primary');
                    pickupText.classList.add('text-text-primary');
                }

                console.log('Showing delivery section, deliverySection:', deliverySection);
                deliverySection.classList.remove('hidden');
                if (pickupMapSection) pickupMapSection.classList.add('hidden');
                // Make address fields required
                ['street', 'number'].forEach(field => {
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        fieldElement.required = true;
                    }
                });
                const saveAddressWrap = document.getElementById('saveAddressWrap');
                if (saveAddressWrap) {
                    saveAddressWrap.classList.remove('hidden');
                }
            } else {
                pickupBtn.classList.add('active', 'border-primary', 'bg-primary-50');
                pickupBtn.classList.remove('border-border', 'bg-surface');
                const pickupIcon = pickupBtn.querySelector('lord-icon');
                if (pickupIcon) {
                    pickupIcon.classList.add('text-primary');
                    pickupIcon.classList.remove('text-text-secondary');
                }
                const pickupText = pickupBtn.querySelector('.font-medium');
                if (pickupText) {
                    pickupText.classList.add('text-primary');
                    pickupText.classList.remove('text-text-primary');
                }

                deliveryBtn.classList.remove('active', 'border-primary', 'bg-primary-50');
                deliveryBtn.classList.add('border-border', 'bg-surface');
                const deliveryIcon = deliveryBtn.querySelector('lord-icon');
                if (deliveryIcon) {
                    deliveryIcon.classList.remove('text-primary');
                    deliveryIcon.classList.add('text-text-secondary');
                }
                const deliveryText = deliveryBtn.querySelector('.font-medium');
                if (deliveryText) {
                    deliveryText.classList.remove('text-primary');
                    deliveryText.classList.add('text-text-primary');
                }

                deliverySection.classList.add('hidden');
                if (pickupMapSection) pickupMapSection.classList.remove('hidden');
                // Remove address field requirements
                ['street', 'number'].forEach(field => {
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        fieldElement.required = false;
                    }
                });
                const saveAddressWrap = document.getElementById('saveAddressWrap');
                if (saveAddressWrap) {
                    saveAddressWrap.classList.add('hidden');
                }
            }

            updateOrderSummary();
        }

        function applySelectedAddress() {
            const select = document.getElementById('savedAddressId');
            if (!select) {
                return;
            }

            const selectedId = Number(select.value || 0);
            const selectedAddress = savedAddresses.find(address => Number(address.id) === selectedId);
            const streetField = document.getElementById('street');
            const numberField = document.getElementById('number');
            const notesField = document.getElementById('deliveryNotes');
            const saveAddressCheckbox = document.getElementById('saveAddress');
            const addressLabelInput = document.getElementById('addressLabel');

            if (selectedAddress) {
                if (streetField) streetField.value = selectedAddress.street || '';
                if (numberField) numberField.value = selectedAddress.number || '';
                if (streetField) streetField.readOnly = true;
                if (numberField) numberField.readOnly = true;
                if (notesField && !notesField.value) notesField.value = selectedAddress.reference || '';
                if (saveAddressCheckbox) {
                    saveAddressCheckbox.checked = false;
                    saveAddressCheckbox.disabled = true;
                }
                if (addressLabelInput) {
                    addressLabelInput.value = selectedAddress.name || '';
                    addressLabelInput.readOnly = true;
                }
                return;
            }

            if (saveAddressCheckbox) {
                saveAddressCheckbox.disabled = false;
                if (!saveAddressCheckbox.checked) saveAddressCheckbox.checked = true;
            }
            if (streetField) streetField.readOnly = false;
            if (numberField) numberField.readOnly = false;
            if (addressLabelInput) {
                addressLabelInput.readOnly = false;
                if (!addressLabelInput.value) {
                    addressLabelInput.value = 'Casa';
                }
            }
        }

        // Render order items
        function renderOrderItems() {
            orderItems.innerHTML = '';

            cart.forEach(item => {
                const orderItem = createOrderItem(item);
                orderItems.appendChild(orderItem);
            });
        }

        // Formatea configuration (objeto) a texto legible, replicando la lógica de OrderItem::getConfigurationText
        function formatConfigurationText(configuration) {
            if (!configuration || typeof configuration !== 'object') return '';

            const parts = [];
            const labels = {
                medallones: 'Medallones',
                tipo_medallon: 'Tipo',
                aderezos: 'Aderezos',
                extras: 'Extras',
                dips: 'Dips',
                dip_extra: 'Dip Extra'
            };
            // Orden de display
            const order = ['medallones', 'tipo_medallon', 'aderezos', 'extras', 'dips', 'dip_extra'];
            const seen = new Set();

            const pushPart = (key) => {
                if (seen.has(key)) return;
                seen.add(key);
                const value = configuration[key];
                if (value === undefined || value === null || value === '') return;
                if (Array.isArray(value)) {
                    if (value.length === 0) return;
                    parts.push(`${labels[key] || key}: ${value.join(', ')}`);
                } else {
                    // Para singulares (medallones, tipo_medallon) mostrar solo el valor
                    if (key === 'medallones' || key === 'tipo_medallon') {
                        parts.push(String(value));
                    } else {
                        parts.push(`${labels[key] || key}: ${value}`);
                    }
                }
            };

            order.forEach(pushPart);
            // Cualquier otra clave no prevista
            Object.keys(configuration).forEach(k => {
                if (!seen.has(k)) {
                    const value = configuration[k];
                    if (Array.isArray(value) && value.length > 0) {
                        parts.push(`${k}: ${value.join(', ')}`);
                    } else if (value !== null && value !== undefined && value !== '') {
                        parts.push(`${k}: ${value}`);
                    }
                }
            });

            return parts.join(' | ');
        }

        // Create order item
        function createOrderItem(item) {
            const template = document.getElementById('orderItemTemplate');
            const orderItem = template.content.cloneNode(true);

            const basePrice = Number(item.unitPrice || item.price || 0);
            const variantsTotal = Array.isArray(item.variants) ?
                item.variants.reduce((sum, v) => sum + Number(v.priceModifier || 0), 0) :
                0;
            const optionsTotal = Array.isArray(item.options) ?
                item.options.reduce((sum, o) => sum + Number(o.priceModifier || 0), 0) :
                0;
            const unitPrice = basePrice + variantsTotal + optionsTotal;
            const totalPrice = unitPrice * item.quantity;

            orderItem.querySelector('img').src = item.image ?
                '/images/' + item.image :
                'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
            orderItem.querySelector('img').alt = item.name;
            orderItem.querySelector('.item-name').textContent = item.name;
            orderItem.querySelector('.item-quantity').textContent = `Cantidad: ${item.quantity}`;
            orderItem.querySelector('.item-total').textContent = formatPrice(totalPrice);

            // Set options text
            let optionsText = '';
            if (item.variants && item.variants.length > 0) {
                optionsText += item.variants.map(v => v.value).join(', ');
            }
            if (item.options && item.options.length > 0) {
                if (optionsText) optionsText += ', ';
                optionsText += item.options.map(o => o.value).join(', ');
            }

            // Fallback: armar desde item.configuration (formato usado por catálogo y modal de checkout)
            if (!optionsText && item.configuration && typeof item.configuration === 'object') {
                optionsText = formatConfigurationText(item.configuration);
            }

            orderItem.querySelector('.item-options').textContent = optionsText || 'Sin opciones adicionales';

            return orderItem;
        }

        // Actualiza el monto "Te ahorrás" dentro del payment-option de Efectivo
        function updateCashSavingsDisplay(subtotal) {
            if (typeof cashDiscountPercentage === 'undefined' || cashDiscountPercentage <= 0) return;

            const el = document.getElementById('cashSavingsAmount');
            if (!el) return;

            // Si no nos pasaron subtotal, lo calculamos desde el cart
            if (typeof subtotal !== 'number') {
                const items = JSON.parse(localStorage.getItem('cart')) || [];
                subtotal = Math.round(items.reduce((sum, item) => {
                    const basePrice = Number(item.unitPrice || item.price || 0);
                    const variantsTotal = Array.isArray(item.variants)
                        ? item.variants.reduce((a, v) => a + Number(v.priceModifier || 0), 0) : 0;
                    const optionsTotal = Array.isArray(item.options)
                        ? item.options.reduce((a, o) => a + Number(o.priceModifier || 0), 0) : 0;
                    return sum + (basePrice + variantsTotal + optionsTotal) * (item.quantity || 1);
                }, 0));
            }

            const savings = Math.round(subtotal * (cashDiscountPercentage / 100));
            el.textContent = formatPrice(savings);
        }

        // Update order summary
        function updateOrderSummary() {
            // Recargar carrito desde localStorage por si fue modificado desde el offcanvas
            cart = JSON.parse(localStorage.getItem('cart')) || [];

            const subtotal = Math.round(cart.reduce((sum, item) => {
                const basePrice = Number(item.unitPrice || item.price || 0);
                const variantsTotal = Array.isArray(item.variants) ?
                    item.variants.reduce((acc, v) => acc + Number(v.priceModifier || 0), 0) :
                    0;
                const optionsTotal = Array.isArray(item.options) ?
                    item.options.reduce((acc, o) => acc + Number(o.priceModifier || 0), 0) :
                    0;
                const unitPrice = basePrice + variantsTotal + optionsTotal;
                return sum + unitPrice * (item.quantity || 1);
            }, 0));
            let shipping = 0;
            // Sin costo de envío

            // Calculate discount if payment method is cash
            let discount = 0;
            const selectedPaymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
            if (selectedPaymentMethod && selectedPaymentMethod.value === 'cash' && cashDiscountPercentage > 0) {
                discount = Math.round(subtotal * (cashDiscountPercentage / 100));
            }

            // Actualizar el monto del ahorro mostrado dentro del payment-option de Efectivo
            updateCashSavingsDisplay(subtotal);

            const total = Math.round(subtotal + shipping - discount);

            // Update UI - verificar que los elementos existan antes de acceder a ellos
            if (subtotalEl) {
                subtotalEl.textContent = formatPrice(subtotal);
            }

            // Sin costo de envío - ocultar siempre
            if (shippingRow) {
                shippingRow.classList.add('hidden');
            }

            // Update discount display
            if (discount > 0) {
                if (discountRow) {
                    discountRow.classList.remove('hidden');
                }
                if (discountAmount) {
                    discountAmount.textContent = '-' + formatPrice(discount);
                }
            } else {
                if (discountRow) {
                    discountRow.classList.add('hidden');
                }
            }

            if (finalTotal) {
                finalTotal.textContent = formatPrice(total);
            }
            if (mobileFinalTotal) {
                mobileFinalTotal.textContent = formatPrice(total);
            }
        }


        // Form validation setup
        function setupFormValidation() {
            const form = document.getElementById('checkoutForm');
            const inputs = form.querySelectorAll('input[required], select[required]');

            inputs.forEach(input => {
                input.addEventListener('blur', validateField);
                input.addEventListener('input', clearFieldError);
            });
        }

        // Validate individual field
        function validateField(event) {
            const field = event.target;
            if (!field || !field.parentNode) {
                return false;
            }

            const feedback = field.parentNode.querySelector('.invalid-feedback');

            if (!field.checkValidity()) {
                field.classList.add('border-error');
                if (feedback) {
                    feedback.classList.remove('hidden');
                }
                return false;
            } else {
                field.classList.remove('border-error');
                if (feedback) {
                    feedback.classList.add('hidden');
                }
                return true;
            }
        }

        // Clear field error
        function clearFieldError(event) {
            const field = event.target;
            if (!field || !field.parentNode) {
                return;
            }

            const feedback = field.parentNode.querySelector('.invalid-feedback');

            if (field.checkValidity()) {
                field.classList.remove('border-error');
                if (feedback) {
                    feedback.classList.add('hidden');
                }
            }
        }

        // Acompanamientos Section Event Listeners
        function setupAcompanamientosListeners() {
            // Checkbox change events
            const checkboxes = document.querySelectorAll('.acompanamiento-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const productId = this.dataset.productId;
                    const item = this.closest('.acompanamiento-item');

                    if (this.checked) {
                        selectedAcompanamientos.add(productId);
                        item.classList.add('selected');
                    } else {
                        selectedAcompanamientos.delete(productId);
                        item.classList.remove('selected');
                    }

                    updateAddSelectedButton();
                });
            });

            // Item click to toggle checkbox (when not clicking on button or checkbox directly)
            const items = document.querySelectorAll('.acompanamiento-item');
            items.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Don't toggle if clicking on checkbox or add button
                    if (e.target.classList.contains('acompanamiento-checkbox') ||
                        e.target.classList.contains('acompanamiento-add-btn')) {
                        return;
                    }

                    const checkbox = this.querySelector('.acompanamiento-checkbox');
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            // Individual "Agregar" button clicks
            const addButtons = document.querySelectorAll('.acompanamiento-add-btn');
            addButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    const productPrice = parseFloat(this.dataset.productPrice);
                    const productImage = this.dataset.productImage;
                    const productCategoryId = parseInt(this.dataset.productCategoryId);

                    // Acompañamientos (2) y Combos (4) requieren personalización (Dip / Dip Extra)
                    if (productCategoryId === 2 || productCategoryId === 4) {
                        openCheckoutCustomizeModal({
                            productId,
                            productName,
                            productPrice,
                            productImage,
                            productCategoryId
                        });
                    } else {
                        addAcompanamientoToCart(productId, productName, productPrice, productImage, productCategoryId);
                    }
                });
            });

            // Confirmar personalización desde modal
            const confirmBtn = document.getElementById('checkoutCustomizeConfirmBtn');
            if (confirmBtn && !confirmBtn.dataset.bound) {
                confirmBtn.dataset.bound = 'true';
                confirmBtn.addEventListener('click', confirmCheckoutCustomization);
            }

            // "Add selected" button click
            if (addSelectedAcompanamientosBtn) {
                addSelectedAcompanamientosBtn.addEventListener('click', function() {
                    addSelectedAcompanamientosToCart();
                });
            }
        }

        // Update the "Add selected" button state
        function updateAddSelectedButton() {
            if (!addSelectedAcompanamientosBtn) return;

            const count = selectedAcompanamientos.size;
            addSelectedAcompanamientosBtn.disabled = count === 0;

            if (count >= 2) {
                addSelectedAcompanamientosBtn.classList.remove('hidden');
                addSelectedAcompanamientosBtn.innerHTML = `<i class="fas fa-plus mr-2"></i>Agregar seleccionados (${count})`;
            } else {
                addSelectedAcompanamientosBtn.classList.add('hidden');
            }
        }

        // Add a single acompanamiento to cart
        function addAcompanamientoToCart(productId, productName, productPrice, productImage, productCategoryId, configuration = null) {
            const catId = parseInt(productCategoryId);
            const lineType = catId === 1 ? 'hamburger' :
                (catId === 2 ? 'acompanamiento' : null);

            const cartItem = {
                productId: parseInt(productId),
                name: productName,
                price: productPrice,
                quantity: 1,
                image: productImage,
                categoryId: catId,
                categoryName: getCategoryName(catId),
                configuration: configuration,
                lineType: lineType
            };

            cart.push(cartItem);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderOrderItems();
            updateOrderSummary();

            showToast(`${productName} agregado al carrito`, 'success');
            renderSuggestionsState();
        }

        // ── Sugerencias: refrescar estado (added + cantidad) ──
        function getSuggestionCartCount(productId) {
            const items = JSON.parse(localStorage.getItem('cart')) || [];
            const pid = parseInt(productId);
            return items.reduce((sum, it) => {
                return parseInt(it.productId) === pid ? sum + (parseInt(it.quantity) || 1) : sum;
            }, 0);
        }

        function renderSuggestionsState() {
            const items = document.querySelectorAll('.suggestion-item');
            items.forEach(item => {
                const pid = item.dataset.productId;
                const count = getSuggestionCartCount(pid);
                const countEl = item.querySelector('.suggestion-step-count');
                if (countEl) countEl.textContent = count;
                if (count > 0) {
                    item.classList.add('added');
                    const cb = item.querySelector('.suggestion-checkbox');
                    if (cb && cb.checked) {
                        cb.checked = false;
                        selectedAcompanamientos.delete(pid);
                        updateAddSelectedButton?.();
                    }
                } else {
                    item.classList.remove('added');
                }
            });
        }

        // Listener delegado para +/-
        document.addEventListener('click', function(e) {
            const minus = e.target.closest('.suggestion-step-minus');
            if (minus) {
                e.preventDefault();
                e.stopPropagation();
                const pid = parseInt(minus.dataset.productId);
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                // Quitar el ÚLTIMO item con ese productId (el más reciente agregado)
                for (let i = cart.length - 1; i >= 0; i--) {
                    if (parseInt(cart[i].productId) === pid) {
                        if ((cart[i].quantity || 1) > 1) {
                            cart[i].quantity -= 1;
                        } else {
                            cart.splice(i, 1);
                        }
                        break;
                    }
                }
                localStorage.setItem('cart', JSON.stringify(cart));
                window.cart = cart;
                if (typeof renderOrderItems === 'function') renderOrderItems();
                if (typeof updateOrderSummary === 'function') updateOrderSummary();
                renderSuggestionsState();
                return;
            }

            const plus = e.target.closest('.suggestion-step-plus');
            if (plus) {
                e.preventDefault();
                e.stopPropagation();
                const productId = plus.dataset.productId;
                const productName = plus.dataset.productName;
                const productPrice = parseFloat(plus.dataset.productPrice);
                const productImage = plus.dataset.productImage;
                const productCategoryId = parseInt(plus.dataset.productCategoryId);

                // Cat 2 / 4 requieren personalización (modal). El resto agrega directo.
                if (productCategoryId === 2 || productCategoryId === 4) {
                    openCheckoutCustomizeModal({ productId, productName, productPrice, productImage, productCategoryId });
                } else {
                    addAcompanamientoToCart(productId, productName, productPrice, productImage, productCategoryId);
                }
            }
        });

        // Abrir modal de personalización para acompañamientos / combos
        let pendingCheckoutCustomizeProduct = null;
        function openCheckoutCustomizeModal(product) {
            pendingCheckoutCustomizeProduct = product;

            const titleEl = document.getElementById('checkoutCustomizeModalTitle');
            const nameEl = document.getElementById('checkoutCustomizeProductName');
            const dipWrapper = document.getElementById('checkoutCustomizeDipWrapper');
            const dipExtraWrapper = document.getElementById('checkoutCustomizeDipExtraWrapper');

            // Cat 2 (acompañamientos) → Dip + Dip Extra ; Cat 4 (combos) → solo Dip Extra
            const isAcompanamiento = product.productCategoryId === 2;
            if (dipWrapper) dipWrapper.style.display = isAcompanamiento ? '' : 'none';
            if (dipExtraWrapper) dipExtraWrapper.style.display = '';

            if (titleEl) titleEl.textContent = `Personalizar ${product.productName}`;
            if (nameEl) nameEl.textContent = product.productName;

            // Reset selects
            const dipSelect = document.getElementById('checkoutCustomizeDipSelect');
            const dipExtraSelect = document.getElementById('checkoutCustomizeDipExtraSelect');
            if (dipSelect) dipSelect.selectedIndex = 0;
            if (dipExtraSelect) dipExtraSelect.selectedIndex = 0;

            const modalEl = document.getElementById('checkoutCustomizeModal');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        }

        function confirmCheckoutCustomization() {
            if (!pendingCheckoutCustomizeProduct) return;
            const product = pendingCheckoutCustomizeProduct;

            const isAcompanamiento = product.productCategoryId === 2;
            const dipSelect = document.getElementById('checkoutCustomizeDipSelect');
            const dipExtraSelect = document.getElementById('checkoutCustomizeDipExtraSelect');

            const configuration = {};
            let priceModifier = 0;

            if (isAcompanamiento && dipSelect && dipSelect.options.length > 0) {
                const opt = dipSelect.options[dipSelect.selectedIndex];
                configuration.dips = [opt.value];
                priceModifier += parseFloat(opt.dataset.priceModifier || 0) || 0;
            }

            if (dipExtraSelect && dipExtraSelect.options.length > 0) {
                const opt = dipExtraSelect.options[dipExtraSelect.selectedIndex];
                if (opt.value !== '') {
                    configuration.dip_extra = [opt.value];
                    priceModifier += parseFloat(opt.dataset.priceModifier || 0) || 0;
                }
            }

            const finalPrice = product.productPrice + priceModifier;
            const finalConfig = Object.keys(configuration).length > 0 ? configuration : null;

            addAcompanamientoToCart(
                product.productId,
                product.productName,
                finalPrice,
                product.productImage,
                product.productCategoryId,
                finalConfig
            );

            const modalEl = document.getElementById('checkoutCustomizeModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            pendingCheckoutCustomizeProduct = null;
        }

        // Add all selected acompanamientos to cart
        function addSelectedAcompanamientosToCart() {
            const checkboxes = document.querySelectorAll('.acompanamiento-checkbox:checked');

            checkboxes.forEach(checkbox => {
                const productId = checkbox.dataset.productId;
                const item = checkbox.closest('.acompanamiento-item');
                const button = item.querySelector('.acompanamiento-add-btn');

                const productName = button.dataset.productName;
                const productPrice = parseFloat(button.dataset.productPrice);
                const productImage = button.dataset.productImage;
                const productCategoryId = button.dataset.productCategoryId;

                addAcompanamientoToCart(productId, productName, productPrice, productImage, productCategoryId);

                // Uncheck and remove from selection
                checkbox.checked = false;
                selectedAcompanamientos.delete(productId);
                item.classList.remove('selected');
            });

            updateAddSelectedButton();
        }

        // Get category name from ID
        function getCategoryName(categoryId) {
            const categories = {
                2: 'Acompañamientos',
                3: 'Bebidas',
                4: 'Combos',
                5: 'Postres'
            };
            return categories[categoryId] || 'Acompañamientos';
        }

        // Handle form submission - Valida y crea pedido directamente
        // Guard global: bloquea doble submit aunque el usuario clickee rápido
        let _orderSubmitting = false;

        function handleFormSubmit(event) {
            if (event && event.preventDefault) {
                event.preventDefault();
            }

            if (_orderSubmitting) return;
            _orderSubmitting = true;

            // Validate form - obtener el formulario correctamente
            let form = document.getElementById('checkoutForm');
            // Si event.target es el formulario, usarlo; si no, usar el obtenido por ID
            if (event && event.target && event.target.tagName === 'FORM') {
                form = event.target;
            }
            
            if (!form) {
                console.error('Formulario no encontrado');
                showToast('Error: Formulario no encontrado', 'error');
                _orderSubmitting = false;
                return;
            }

            const formData = new FormData(form);
            let isValid = true;

            // Check required fields based on delivery method
            const requiredFields = ['firstName', 'lastName', 'phone'];
            if (isDelivery) {
                requiredFields.push('street', 'number');
            }

            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (!validateField({
                        target: field
                    })) {
                    isValid = false;
                }
            });

            if (!isValid) {
                showToast('Por favor completa todos los campos requeridos', 'error');
                _orderSubmitting = false;
                return;
            }

            // Validación extra explícita para Delivery (evita nulos)
            if (isDelivery) {
                const street = (document.getElementById('street').value || '').trim();
                const number = (document.getElementById('number').value || '').trim();

                if (!street) {
                    showToast('Por favor ingresa la calle', 'error');
                    _orderSubmitting = false;
                    return;
                }

                if (!number) {
                    showToast('Por favor ingresa el número', 'error');
                    _orderSubmitting = false;
                    return;
                }
            }

            // Obtener método de pago seleccionado
            const selectedPaymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
            if (!selectedPaymentMethod) {
                showToast('Por favor selecciona un método de pago', 'error');
                _orderSubmitting = false;
                return;
            }

            // Preparar datos del pedido
            const orderData = {
                items: cart.map(item => ({
                    product_id: item.productId,
                    quantity: item.quantity,
                    configuration: item.configuration || null,
                    variants: item.variants || [],
                    options: item.options || [],
                    special_instructions: item.specialInstructions || null,
                })),
                // Datos de contacto
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                phone: formData.get('phone'),
                payment_method: selectedPaymentMethod.value,
                notes: (formData.get('notes') || '').trim() || null,
                delivery_method: isDelivery ? 'delivery' : 'pickup',
                ...(isDelivery ? {
                    address: `${(formData.get('street') || '').trim()} ${(formData.get('number') || '').trim()}`
                        .trim(),
                    address_id: Number(document.getElementById('savedAddressId')?.value || 0) || null,
                    save_address: document.getElementById('saveAddress')?.checked ? 1 : 0,
                    address_label: (document.getElementById('addressLabel')?.value || '').trim(),
                    floor: formData.get('floor') || '',
                    delivery_notes: formData.get('deliveryNotes') || ''
                } : {}),
                // Información de horario de entrega
                deliveryTime: formData.get('deliveryTime'),
                deliveryDate: formData.get('deliveryDate'),
                deliveryTimeSlot: formData.get('deliveryTimeSlot'),
                // Microturno seleccionado
                microturno_id: formData.get('microturno_id'),
                // Pedido grande: horario a coordinar por WhatsApp
                coordinate_by_whatsapp: sessionStorage.getItem('coordinateByWhatsApp') === '1' ? 1 : 0,
                // Información de pago
                cashAmount: formData.get('cashAmount'),
                coupon_id: appliedCoupon ? appliedCoupon.id : null,
            };

            // Crear pedido y abrir WhatsApp directamente
            createOrderAndOpenWhatsApp(orderData);
        }

        // Crear pedido y abrir WhatsApp
        async function createOrderAndOpenWhatsApp(payload) {
            console.log('createOrderAndOpenWhatsApp llamado con payload:', payload);

            // Abrir ventana vacía ANTES del fetch (síncrono = el navegador lo permite)
            // En iOS Safari, window.open() bloqueado si se llama después de un await
            const waWindow = window.open('', '_blank');

            // Show loading state
            placeOrderText.textContent = 'Procesando...';
            placeOrderSpinner.classList.remove('hidden');
            placeOrderBtn.disabled = true;

            // Disable mobile button if exists
            if (mobilePlaceOrderBtn) {
                mobilePlaceOrderBtn.disabled = true;
                mobilePlaceOrderBtn.textContent = 'Procesando...';
            }

            try {
                const resp = await fetch('{{ route('orders.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                const data = await resp.json();
                if (!resp.ok || !data.success) {
                    // Verificar si es un error de microturno lleno
                    if (data.microturno_lleno) {
                        const errorMsg = data.error || 'El horario seleccionado está lleno.';
                        showToast(errorMsg, 'error');

                        // Mostrar alerta con más detalles
                        setTimeout(() => {
                            if (confirm(`${errorMsg}\n\n¿Desea volver a seleccionar un horario diferente?`)) {
                                window.location.href = '{{ route('turnos') }}';
                            }
                        }, 1000);

                        throw new Error(errorMsg);
                    }
                    throw new Error(data.error || 'Error al procesar el pedido');
                }

                // Clear cart
                localStorage.removeItem('cart');
                saveCustomerData();

                // Construir mensaje de WhatsApp
                const whatsappMessage = buildWhatsAppMessage(data.order, payload);

                // Limpiar flag de coordinación por WhatsApp tras enviar
                sessionStorage.removeItem('coordinateByWhatsApp');
                const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(whatsappMessage);
                const trackingUrl = '/pedido/' + data.order.order_number + '/seguimiento';

                // Navegar la ventana ya abierta a WhatsApp (evita bloqueo de popup por async)
                if (waWindow && !waWindow.closed) {
                    waWindow.location.href = whatsappUrl;
                } else {
                    // Fallback: si la ventana fue bloqueada igual, intentar de nuevo
                    window.open(whatsappUrl, '_blank');
                }

                // Reset button state
                placeOrderText.textContent = 'Confirmar pedido por WhatsApp';
                placeOrderSpinner.classList.add('hidden');
                placeOrderBtn.disabled = false;
                if (mobilePlaceOrderBtn) {
                    mobilePlaceOrderBtn.disabled = false;
                    mobilePlaceOrderBtn.textContent = 'Confirmar pedido por WhatsApp';
                }
                // Pedido creado exitosamente — el flag queda en true (no se puede reenviar)

                // ── Mostrar overlay de confirmación ──
                showOrderConfirmOverlay(whatsappUrl, trackingUrl);

            } catch (error) {
                console.error('Order processing error:', error);
                console.error('Error details:', error.message, error.stack);
                
                // Solo mostrar toast si no fue error de microturno lleno (ya se mostró antes)
                if (!error.message || !error.message.includes('horario')) {
                    const errorMsg = error.message || 'Error al procesar el pedido. Intenta nuevamente.';
                    showToast(errorMsg, 'error');
                    alert('Error: ' + errorMsg); // Alert adicional para mobile
                }

                // Cerrar la ventana vacía si el pedido falló
                if (waWindow && !waWindow.closed) waWindow.close();

                // Reset button state y flag — el usuario puede reintentar
                _orderSubmitting = false;
                if (placeOrderText) {
                    placeOrderText.textContent = 'Confirmar pedido por WhatsApp';
                }
                if (placeOrderSpinner) {
                    placeOrderSpinner.classList.add('hidden');
                }
                if (placeOrderBtn) {
                    placeOrderBtn.disabled = false;
                }

                // Reset mobile button if exists
                if (mobilePlaceOrderBtn) {
                    mobilePlaceOrderBtn.disabled = false;
                    mobilePlaceOrderBtn.textContent = 'Confirmar pedido por WhatsApp';
                }
            }
        }

        // Función para formatear números como PHP number_format($num, 2, ',', '.')
        function formatNumber(num) {
            const numStr = parseFloat(num || 0).toFixed(2);
            const parts = numStr.split('.');
            const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return integerPart + ',' + parts[1];
        }

        // Construir mensaje de WhatsApp (idéntico a order-confirmation.blade.php)
        function buildWhatsAppMessage(order, payload) {
            const couponInfo = appliedCoupon || order.coupon || null;
            const paymentMethod = payload.payment_method || order.payment_method;
            const cashDiscountPercentage = {{ $businessSettings['cash_discount_percentage'] ?? 0 }};
            
            const lines = [];
            lines.push('¡Hola! Acabo de realizar un pedido en T cocina:');
            lines.push('');
            lines.push('*Pedido #' + order.order_number + '*');

            // Obtener nombre del cliente (igual que en PHP)
            const customerName = order.contact_name || (order.user && order.user.name) || 'Invitado';
            const customerPhone = order.contact_phone || (order.user && order.user.phone) || '';
            const isPickup = order.address_id === null;
            const methodText = isPickup ? 'Retiro en Local' : 'Delivery';

            lines.push('Cliente: ' + customerName);
            if (customerPhone) {
                lines.push('Telefono: ' + customerPhone);
            }
            lines.push('Metodo: ' + methodText);

            // Agregar dirección solo si es delivery (igual que en PHP)
            if (!isPickup && order.address) {
                const addrParts = [];
                const streetPart = ((order.address.street || '') + ' ' + (order.address.number || '')).trim();
                if (streetPart) {
                    addrParts.push(streetPart);
                }
                const cityPart = ((order.address.city || '') + ' ' + (order.address.postal_code || '')).trim();
                if (cityPart) {
                    addrParts.push(cityPart);
                }
                const addrText = addrParts.filter(p => p).join(', ');
                if (addrText) {
                    lines.push('Direccion: ' + addrText);
                }
            }

            lines.push('');
            lines.push('*PRODUCTOS:*');

            order.items.forEach(item => {
                let line = '- ' + item.quantity + 'x ' + item.product.name;

                // Use new configuration structure (igual que en PHP)
                if (item.configuration_text) {
                    line += ' (' + item.configuration_text + ')';
                }

                // Formatear precio igual que PHP: number_format($item->total_price, 2, ',', '.')
                line += ' — $' + formatNumber(item.total_price);
                lines.push(line);
            });

            lines.push('');

            // Calcular subtotal real (igual que en PHP)
            let realSubtotal = parseFloat(order.subtotal_amount || order.subtotal || 0);
            if (realSubtotal == 0) {
                order.items.forEach(item => {
                    // total_price puede tener descuento ya aplicado, usar unit_price * quantity si existe
                    if (item.unit_price) {
                        realSubtotal += parseFloat(item.unit_price) * item.quantity;
                    } else {
                        // fallback: sumar total_price
                        realSubtotal += parseFloat(item.total_price);
                    }
                });
            }
            
            // Calcular descuentos (cupón + efectivo opcional acumulable)
            const couponDiscount = couponInfo
                ? (Number(couponInfo.discount_amount) || Math.round(realSubtotal * ((Number(couponInfo.discount_percentage || 0)) / 100)))
                : 0;
            let cashDiscount = 0;
            
            // Calcular descuento por efectivo
            if (paymentMethod === 'cash' && cashDiscountPercentage > 0) {
                if (couponInfo) {
                    if (couponInfo.allow_cash_discount) {
                        // Si hay acumulación, descuento efectivo es el remanente del descuento total aplicado
                        const totalDiscount = Number(order.discount_amount || 0);
                        cashDiscount = Math.max(0, totalDiscount - couponDiscount);
                    }
                } else if (order.discount_amount && order.discount_amount > 0) {
                    cashDiscount = parseFloat(order.discount_amount);
                } else {
                    cashDiscount = Math.round(realSubtotal * (cashDiscountPercentage / 100));
                }
            }

            lines.push('*SUBTOTAL: $' + formatNumber(realSubtotal) + '*');

            // Mostrar descuento por cupón con nombre si existe
            if (couponInfo && couponDiscount > 0) {
                lines.push('*DESCUENTO CUPÓN (' + couponInfo.name + '): -$' + formatNumber(couponDiscount) + '*');
            }
            
            // Mostrar descuento por pago en efectivo si existe
            if (cashDiscount > 0) {
                lines.push('*DESCUENTO POR PAGO EN EFECTIVO: -$' + formatNumber(cashDiscount) + '*');
            }

            lines.push('*TOTAL: $' + formatNumber(order.total_amount) + '*');

            // Agregar información del método de pago (igual que en PHP)
            if (order.payment_method) {
                const paymentMethod = order.payment_method;
                // Mostrar "con descuento" si hay descuento por efectivo aplicado
                if (paymentMethod === 'cash' && cashDiscount > 0) {
                    lines.push('Método de pago: Efectivo (con descuento)');
                } else {
                    // ucfirst en PHP capitaliza solo la primera letra
                    const capitalized = paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1);
                    lines.push('Método de pago: ' + capitalized);
                }
            }

            // Agregar horario programado si el cliente eligió esa opción
            if (payload.deliveryTime === 'scheduled' && payload.deliveryDate && payload.deliveryTimeSlot) {
                lines.push('Horario programado: ' + payload.deliveryDate + ' ' + payload.deliveryTimeSlot);
            }

            // Pedido grande: el horario se coordina por WhatsApp
            if (sessionStorage.getItem('coordinateByWhatsApp') === '1') {
                lines.push('Horario: a coordinar por WhatsApp (pedido grande)');
            }

            // Agregar notas del cliente si existen
            const userNotes = order.notes || payload.notes;
            if (userNotes) {
                lines.push('Notas: ' + userNotes);
            }

            lines.push('');
            lines.push('⏳ *IMPORTANTE:* Espera nuestra confirmación por WhatsApp antes de considerar tu pedido confirmado.');
            lines.push('');
            lines.push('¡Gracias por tu pedido!');

            return lines.join('\n');
        }

        // Calculate final total
        function calculateFinalTotal() {
            const subtotal = cart.reduce((sum, item) => {
                const basePrice = Number(item.unitPrice || item.price || 0);
                const variantsTotal = Array.isArray(item.variants) ?
                    item.variants.reduce((acc, v) => acc + Number(v.priceModifier || 0), 0) :
                    0;
                const optionsTotal = Array.isArray(item.options) ?
                    item.options.reduce((acc, o) => acc + Number(o.priceModifier || 0), 0) :
                    0;
                const unitPrice = basePrice + variantsTotal + optionsTotal;
                return sum + (unitPrice * item.quantity);
            }, 0);
            let shipping = 0;

            // No hay costo de shipping ni cargo por servicio
            shipping = 0;

            return subtotal;
        }

        // Save customer data
        function saveCustomerData() {
            const customerData = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                phone: document.getElementById('phone').value
            };

            localStorage.setItem('customerData', JSON.stringify(customerData));
        }

        // Load customer data
        function loadCustomerData() {
            const savedData = localStorage.getItem('customerData');
            if (savedData) {
                const customerData = JSON.parse(savedData);

                Object.keys(customerData).forEach(key => {
                    const field = document.getElementById(key);
                    if (field && customerData[key] && !field.value) {
                        field.value = customerData[key];
                    }
                });
            }
        }

        // Format price
        function formatPrice(price) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0
            }).format(price);
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className =
                `bg-${type === 'success' ? 'success' : 'error'} text-white px-4 py-3 rounded-lg shadow-lg animate-toast-slide`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i>
                    <span>${message}</span>
                </div>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 4000);
        }


        // Load microturnos for a specific date
        async function loadMicroturnos(fecha) {
            const select = document.getElementById('microturno_id');
            const loading = document.getElementById('microturnos-loading');
            const error = document.getElementById('microturnos-error');

            // Show loading state
            loading.classList.remove('hidden');
            error.classList.add('hidden');
            select.disabled = true;

            try {
                const response = await fetch(`/api/turnos/disponibles?fecha=${fecha}`);
                const data = await response.json();

                if (data.success) {
                    // Clear existing options
                    select.innerHTML = '';

                    if (data.microturnos.length > 0) {
                        data.microturnos.forEach((microturno, index) => {
                            const option = document.createElement('option');
                            option.value = microturno.sort_order;
                            if (index === 0) {
                                option.selected = true;
                            }
                            option.textContent =
                                `${microturno.formatted_time} (${microturno.capacidad_restante} cupos disponibles)`;
                            select.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No hay horarios disponibles para esta fecha';
                        option.disabled = true;
                        select.appendChild(option);
                    }
                } else {
                    throw new Error('Error al cargar microturnos');
                }
            } catch (err) {
                console.error('Error loading microturnos:', err);
                error.classList.remove('hidden');
                select.innerHTML = '<option value="">Error al cargar horarios</option>';
            } finally {
                loading.classList.add('hidden');
                select.disabled = false;
            }
        }

        // Load selected turn information
        async function loadSelectedTurn() {
            const selectedMicroturno = sessionStorage.getItem('selectedMicroturno');
            const coordinateByWhatsApp = sessionStorage.getItem('coordinateByWhatsApp') === '1';
            const deliveryTime = sessionStorage.getItem('deliveryTime');
            const deliveryDate = sessionStorage.getItem('deliveryDate');
            const skipTurnoSelection = {{ $businessSettings['skip_turno_selection'] ? 'true' : 'false' }};

            if (skipTurnoSelection) {
                // Si se salta la selección de turno, no autoasignar ni redirigir
                // Limpiar cualquier microturno previo
                document.getElementById('microturno_id').value = '';
                return;
            }

            if (coordinateByWhatsApp) {
                // Pedido grande: no hay microturno, se coordina por WhatsApp
                document.getElementById('microturno_id').value = '';
                const timeEl = document.getElementById('selectedTurnTime');
                if (timeEl) timeEl.innerHTML = '<i class="fab fa-whatsapp me-1" style="color:#25D366;"></i>A coordinar por WhatsApp';
                return;
            }

            if (selectedMicroturno) {
                // Set the microturno_id in the form
                document.getElementById('microturno_id').value = selectedMicroturno;
                // El stepper se encarga de marcar schedule como DONE
            } else {
                // No hay horario: el stepper inline se encargará de mostrar el selector
                // (ya no redirigimos a /turnos)
                document.getElementById('microturno_id').value = '';
            }
        }

        // Auto-select first available microturno
        async function autoSelectFirstAvailableMicroturno() {
            try {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const cartForAPI = await getCartWithCategories(cart);
                // Usar fecha del servidor (timezone correcto), no UTC
                const today = '{{ now()->format("Y-m-d") }}';

                const response = await fetch(`/api/turnos/disponibles?fecha=${today}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        cart: cartForAPI
                    })
                });

                const data = await response.json();

                if (data.success && data.microturnos.length > 0) {
                    // Buscar el primer microturno disponible
                    const firstAvailable = data.microturnos.find(m => m.disponible_para_carrito);

                    if (firstAvailable) {
                        // Asignar el microturno
                        document.getElementById('microturno_id').value = firstAvailable.sort_order;
                        document.getElementById('selectedTurnTime').textContent = firstAvailable.formatted_time +
                            ' (Asignado automáticamente)';

                        // Guardar en sessionStorage para futuras referencias
                        sessionStorage.setItem('selectedMicroturno', firstAvailable.sort_order);
                        sessionStorage.setItem('deliveryTime', 'now');
                        sessionStorage.setItem('deliveryDate', today);
                    } else {
                        // No hay microturnos disponibles
                        alert('No hay horarios disponibles para tu pedido. Por favor, intenta más tarde.');
                        window.location.href = '{{ route('cart') }}';
                    }
                } else {
                    alert('No hay horarios disponibles. Por favor, intenta más tarde.');
                    window.location.href = '{{ route('cart') }}';
                }
            } catch (error) {
                console.error('Error al auto-seleccionar microturno:', error);
                alert('Error al cargar horarios disponibles.');
                window.location.href = '{{ route('cart') }}';
            }
        }

        // Helper function to get cart with categories
        async function getCartWithCategories(cart) {
            const cartWithCategories = [];
            const productIds = [...new Set(cart.map(item => item.productId))];

            try {
                const response = await fetch('/api/products/batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        ids: productIds
                    })
                });

                const data = await response.json();
                const productMap = new Map();

                if (data.success) {
                    data.products.forEach(product => {
                        productMap.set(product.id, product.category.name);
                    });
                }

                cart.forEach(item => {
                    const category = productMap.get(parseInt(item.productId)) || 'hamburguesas';
                    cartWithCategories.push({
                        productId: item.productId,
                        quantity: item.quantity,
                        category: category
                    });
                });

                return cartWithCategories;
            } catch (error) {
                console.error('Error fetching product categories:', error);
                return cart.map(item => ({
                    productId: item.productId,
                    quantity: item.quantity,
                    category: 'hamburguesas'
                }));
            }
        }

        // Load turn details
        async function loadTurnDetails(microturnoId) {
            try {
                // Usar la fecha guardada en sessionStorage (timezone del servidor), no UTC
                const fecha = sessionStorage.getItem('deliveryDate') || '{{ now()->format("Y-m-d") }}';
                const response = await fetch(`/api/turnos/verificar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        microturno_id: microturnoId,
                        fecha: fecha
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('selectedTurnTime').textContent = data.microturno.formatted_time;
                } else {
                    // Turn no longer available, redirect to turnos
                    window.location.href = '{{ route('turnos') }}';
                }
            } catch (error) {
                console.error('Error loading turn details:', error);
                // On error, redirect to turnos
                window.location.href = '{{ route('turnos') }}';
            }
        }

        // ========== OTP Input Component (Cupones) ==========
        const otpContainer = document.getElementById('otpContainer');
        const applyCouponBtn = document.getElementById('applyCouponBtn');
        const removeCouponBtn = document.getElementById('removeCouponBtn');
        const couponInputSection = document.getElementById('couponInputSection');
        const couponAppliedSection = document.getElementById('couponAppliedSection');
        const couponError = document.getElementById('couponError');
        const couponName = document.getElementById('couponName');
        const couponDiscount = document.getElementById('couponDiscount');
        const couponDiscountRow = document.getElementById('couponDiscountRow');
        const couponDiscountLabel = document.getElementById('couponDiscountLabel');
        const couponDiscountAmount = document.getElementById('couponDiscountAmount');

        // Initialize OTP Input - Siempre 8 slots fijos
        function initOTPInput() {
            otpLength = 8; // Siempre 8
            otpContainer.innerHTML = '';
            
            for (let i = 0; i < 8; i++) {
                const slot = document.createElement('input');
                slot.type = 'text';
                slot.className = 'otp-slot';
                slot.maxLength = 1;
                slot.setAttribute('data-index', i);
                slot.setAttribute('autocomplete', 'off');
                
                slot.addEventListener('input', handleOTPInput);
                slot.addEventListener('keydown', handleOTPKeydown);
                slot.addEventListener('paste', handleOTPPaste);
                slot.addEventListener('focus', () => slot.select());
                
                otpContainer.appendChild(slot);
            }
            
            // Focus first slot
            if (otpContainer.firstChild) {
                otpContainer.firstChild.focus();
            }
        }

        function handleOTPInput(e) {
            const slot = e.target;
            const value = e.data || slot.value;
            
            // Only allow alphanumeric
            if (value && !/^[A-Z0-9]$/i.test(value)) {
                slot.value = '';
                return;
            }
            
            slot.value = value.toUpperCase();
            
            if (value) {
                slot.classList.add('filled');
                slot.classList.remove('error');
                focusNextSlot(parseInt(slot.dataset.index));
            } else {
                slot.classList.remove('filled');
            }
        }

        function handleOTPKeydown(e) {
            const slot = e.target;
            const index = parseInt(slot.dataset.index);
            
            if (e.key === 'Backspace' && !slot.value && index > 0) {
                e.preventDefault();
                focusPrevSlot(index);
            } else if (e.key === 'ArrowLeft' && index > 0) {
                e.preventDefault();
                focusPrevSlot(index);
            } else if (e.key === 'ArrowRight' && index < otpLength - 1) {
                e.preventDefault();
                focusNextSlot(index);
            } else if (e.key === 'Enter' && getOTPCode().length === otpLength) {
                e.preventDefault();
                applyCouponBtn.click();
            }
        }

        function handleOTPPaste(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            if (pastedData.length > 0) {
                const slots = otpContainer.querySelectorAll('.otp-slot');
                const startIndex = parseInt(e.target.dataset.index);
                
                for (let i = 0; i < pastedData.length && (startIndex + i) < otpLength; i++) {
                    const slot = slots[startIndex + i];
                    slot.value = pastedData[i];
                    slot.classList.add('filled');
                    slot.classList.remove('error');
                }
                
                // Focus next empty slot or last slot
                const nextEmptyIndex = Math.min(startIndex + pastedData.length, otpLength - 1);
                slots[nextEmptyIndex].focus();
            }
        }

        function focusNextSlot(currentIndex) {
            if (currentIndex < otpLength - 1) {
                const nextSlot = otpContainer.querySelector(`[data-index="${currentIndex + 1}"]`);
                if (nextSlot) {
                    nextSlot.focus();
                }
            }
        }

        function focusPrevSlot(currentIndex) {
            if (currentIndex > 0) {
                const prevSlot = otpContainer.querySelector(`[data-index="${currentIndex - 1}"]`);
                if (prevSlot) {
                    prevSlot.focus();
                    prevSlot.value = '';
                    prevSlot.classList.remove('filled');
                }
            }
        }

        function getOTPCode() {
            const slots = otpContainer.querySelectorAll('.otp-slot');
            return Array.from(slots).map(slot => slot.value).join('');
        }

        function clearOTPInput() {
            const slots = otpContainer.querySelectorAll('.otp-slot');
            slots.forEach(slot => {
                slot.value = '';
                slot.classList.remove('filled', 'error');
            });
            if (slots[0]) {
                slots[0].focus();
            }
        }

        // ========== Coupon Management ==========
        async function applyCoupon() {
            const code = getOTPCode();
            
            if (code.length !== 8) {
                showCouponError('El código debe tener exactamente 8 caracteres');
                return;
            }

            // Get current subtotal
            const subtotal = Math.round(cart.reduce((sum, item) => {
                const basePrice = Number(item.unitPrice || item.price || 0);
                const variantsTotal = Array.isArray(item.variants) ?
                    item.variants.reduce((acc, v) => acc + Number(v.priceModifier || 0), 0) : 0;
                const optionsTotal = Array.isArray(item.options) ?
                    item.options.reduce((acc, o) => acc + Number(o.priceModifier || 0), 0) : 0;
                const unitPrice = basePrice + variantsTotal + optionsTotal;
                return sum + unitPrice * (item.quantity || 1);
            }, 0));

            try {
                const response = await fetch('{{ route("api.coupons.validate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ code, subtotal }),
                });

                const data = await response.json();

                if (data.success) {
                    appliedCoupon = data.coupon;
                    
                    couponName.textContent = data.coupon.name;
                    couponDiscount.textContent = `${data.coupon.discount_percentage}% de descuento - Ahorras ${formatPrice(data.coupon.discount_amount)}`;
                    
                    couponInputSection.classList.add('hidden');
                    couponAppliedSection.classList.remove('hidden');
                    couponError.classList.add('hidden');
                    
                    updateOrderSummary();
                    showToast('¡Cupón aplicado exitosamente!', 'success');

                    // Mantener la card del cupón abierta tras aplicar
                    document.dispatchEvent(new CustomEvent('coupon:applied'));

                    // Lanzar animación de confetti
                    createConfettiExplosion();
                } else {
                    showCouponError(data.message || 'Cupón no válido');
                    // Mark slots as error
                    const slots = otpContainer.querySelectorAll('.otp-slot');
                    slots.forEach(slot => slot.classList.add('error'));
                }
            } catch (error) {
                console.error('Error validating coupon:', error);
                showCouponError('Error al validar el cupón. Intenta nuevamente.');
            }
        }

        function removeCoupon() {
            appliedCoupon = null;
            try { localStorage.removeItem('tcocina_mobile_sheet_coupon'); } catch(e) {}
            clearOTPInput();
            couponInputSection.classList.remove('hidden');
            couponAppliedSection.classList.add('hidden');
            couponError.classList.add('hidden');
            updateOrderSummary();
            showToast('Cupón removido', 'info');
        }

        function showCouponError(message) {
            couponError.textContent = message;
            couponError.classList.remove('hidden');
        }

        // Función para crear animación de confetti y celebración de cupón
        function createConfettiExplosion() {
            // Reproducir sonido de éxito
            try {
                const audio = new Audio('{{ asset("audio/success.mp3") }}');
                audio.volume = 0.5; // Volumen al 50%
                audio.play().catch(e => console.log('Audio no disponible:', e));
            } catch (e) {
                console.log('Error al reproducir audio:', e);
            }

            // Crear overlay con blur
            const existingOverlay = document.getElementById('coupon-celebration-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }

            const overlay = document.createElement('div');
            overlay.id = 'coupon-celebration-overlay';
            overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.3);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:1.5rem;padding:2rem;';
            
            overlay.innerHTML = `
                <lord-icon
                    src="{{ asset('lordicons/cupon.json') }}"
                    colors="primary:#ffffff,secondary:#ffffff"
                    trigger="loop"
                    style="width:160px;height:160px;">
                </lord-icon>
                <h2 style="color:#fff;font-size:2rem;font-weight:700;text-align:center;margin:0;text-shadow:0 2px 8px rgba(0,0,0,0.3);">Cupon Válido <span style="color:#22c55e;">✓</span></h2>
                <button id="coupon-confirm-btn" style="background:#0d6efd;color:#fff;border:none;border-radius:8px;padding:.65rem 2rem;font-size:1rem;font-weight:600;cursor:pointer;box-shadow:0 4px 14px rgba(13,110,253,.4);margin-top:.5rem;animation:lbBtnPulse 2s ease-in-out infinite;">
                    Confirmar
                </button>
                <style>@keyframes lbBtnPulse{0%,100%{box-shadow:0 4px 14px rgba(13,110,253,.4),0 0 0 0 rgba(13,110,253,.0)}50%{box-shadow:0 4px 20px rgba(13,110,253,.6),0 0 18px 6px rgba(13,110,253,.25)}}</style>
            `;
            
            document.body.appendChild(overlay);

            // Cerrar overlay al hacer click en el botón
            document.getElementById('coupon-confirm-btn').addEventListener('click', function() {
                overlay.remove();
            });

            // Remover confetti anterior si existe
            const existingConfetti = document.getElementById('confetti-container');
            if (existingConfetti) {
                existingConfetti.remove();
            }

            const confettiCount = 100;
            const colors = ["#ef4444", "#3b82f6", "#22c55e", "#eab308", "#8b5cf6", "#f97316"];
            
            // Crear contenedor de confetti (por encima del overlay)
            const container = document.createElement('div');
            container.id = 'confetti-container';
            container.className = 'confetti-container';
            container.style.cssText = 'position:fixed;inset:0;z-index:100000;pointer-events:none;';
            document.body.appendChild(container);

            // Crear confettis
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti-piece';
                
                const color = colors[i % colors.length];
                const left = Math.random() * 100;
                const top = -20 + Math.random() * 10;
                const rotation = Math.random() * 360;
                const duration = 2.5 + Math.random() * 2.5;
                const delay = Math.random() * 2;
                
                confetti.style.left = `${left}%`;
                confetti.style.top = `${top}%`;
                confetti.style.backgroundColor = color;
                confetti.style.transform = `rotate(${rotation}deg)`;
                confetti.style.animation = `confetti-fall ${duration}s ${delay}s linear forwards`;
                
                container.appendChild(confetti);
            }

            // Remover confetti después de 6 segundos
            setTimeout(() => {
                if (container.parentNode) {
                    container.remove();
                }
            }, 6000);
        }

        // Event listeners
        applyCouponBtn?.addEventListener('click', applyCoupon);
        removeCouponBtn?.addEventListener('click', removeCoupon);

        // Initialize OTP on page load - Siempre 8 slots
        document.addEventListener('DOMContentLoaded', function() {
            initOTPInput(); // Siempre 8 slots fijos

            // Precargar cupón guardado desde el carrito mobile
            try {
                const raw = localStorage.getItem('tcocina_mobile_sheet_coupon');
                if (raw) {
                    const saved = JSON.parse(raw);
                    if (saved && saved.code) {
                        appliedCoupon = saved;
                        const nameEl     = document.getElementById('couponName');
                        const discountEl = document.getElementById('couponDiscount');
                        if (nameEl)     nameEl.textContent = (saved.name || saved.code).toUpperCase();
                        if (discountEl) discountEl.textContent = saved.discount_percentage
                            ? `${saved.discount_percentage}% de descuento`
                            : 'Cupón aplicado';
                        if (couponInputSection)  couponInputSection.classList.add('hidden');
                        if (couponAppliedSection) couponAppliedSection.classList.remove('hidden');
                        updateOrderSummary();
                    }
                }
            } catch(e) {}
        });

        // Override updateOrderSummary to include coupon discount
        const originalUpdateOrderSummary = updateOrderSummary;
        updateOrderSummary = function() {
            // Recalculate subtotal
            const subtotal = Math.round(cart.reduce((sum, item) => {
                const basePrice = Number(item.unitPrice || item.price || 0);
                const variantsTotal = Array.isArray(item.variants) ?
                    item.variants.reduce((acc, v) => acc + Number(v.priceModifier || 0), 0) : 0;
                const optionsTotal = Array.isArray(item.options) ?
                    item.options.reduce((acc, o) => acc + Number(o.priceModifier || 0), 0) : 0;
                const unitPrice = basePrice + variantsTotal + optionsTotal;
                return sum + unitPrice * (item.quantity || 1);
            }, 0));
            
            // Calculate coupon discount
            let couponDiscountValue = 0;
            if (appliedCoupon) {
                couponDiscountValue = appliedCoupon.discount_amount;
            }
            
            // Calculate cash discount (acumulable solo si el cupón lo permite)
            let cashDiscount = 0;
            const selectedPaymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
            const canStackCashDiscount = appliedCoupon?.allow_cash_discount === true;
            if (selectedPaymentMethod && selectedPaymentMethod.value === 'cash' && cashDiscountPercentage > 0 && (!appliedCoupon || canStackCashDiscount)) {
                cashDiscount = Math.round(subtotal * (cashDiscountPercentage / 100));
            }
            
            // Update UI
            if (subtotalEl) {
                subtotalEl.textContent = formatPrice(subtotal);
            }
            
            // Update coupon discount row
            if (couponDiscountValue > 0 && couponDiscountRow) {
                couponDiscountRow.classList.remove('hidden');
                if (couponDiscountLabel) {
                    couponDiscountLabel.textContent = appliedCoupon?.name ? `Descuento por cupón (${appliedCoupon.name})` : 'Descuento por cupón';
                }
                couponDiscountAmount.textContent = '-' + formatPrice(couponDiscountValue);
            } else if (couponDiscountRow) {
                couponDiscountRow.classList.add('hidden');
                if (couponDiscountLabel) {
                    couponDiscountLabel.textContent = 'Descuento por cupón';
                }
            }
            
            // Update cash discount row
            if (cashDiscount > 0 && discountRow) {
                discountRow.classList.remove('hidden');
                discountAmount.textContent = '-' + formatPrice(cashDiscount);
            } else if (discountRow) {
                discountRow.classList.add('hidden');
            }
            
            // Calculate and update total
            const total = Math.round(subtotal - couponDiscountValue - cashDiscount);
            
            if (finalTotal) {
                finalTotal.textContent = formatPrice(total);
            }
            if (mobileFinalTotal) {
                mobileFinalTotal.textContent = formatPrice(total);
            }
        };

        // ── Colapso sección Método de Entrega ──────────────────────
        let _deliveryMethodCollapsed = false;

        function collapseDeliveryMethodSection() {
            const body = document.getElementById('deliveryMethodBody');
            const chevron = document.getElementById('deliveryMethodChevron');
            if (!body) return;
            body.style.maxHeight = '0px';
            body.style.opacity = '0';
            if (chevron) chevron.style.transform = 'rotate(-90deg)';
            _deliveryMethodCollapsed = true;
        }

        function expandDeliveryMethodSection() {
            const body = document.getElementById('deliveryMethodBody');
            const chevron = document.getElementById('deliveryMethodChevron');
            if (!body) return;
            body.style.maxHeight = '600px';
            body.style.opacity = '1';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            _deliveryMethodCollapsed = false;
        }

        function toggleDeliveryMethodSection() {
            if (_deliveryMethodCollapsed) {
                expandDeliveryMethodSection();
            } else {
                collapseDeliveryMethodSection();
            }
        }
        // ────────────────────────────────────────────────────────────

        // ── Overlay de confirmación de pedido ──────────────────────
        function showOrderConfirmOverlay(whatsappUrl, trackingUrl) {
            const overlay   = document.getElementById('order-confirm-overlay');
            const bg        = document.getElementById('ocoverlay-bg');
            const checkWrap = document.getElementById('confirm-lottie');
            const title     = document.getElementById('oco-title');
            const sub       = document.getElementById('oco-sub');
            const actionBtn = document.getElementById('oco-action-btn');
            if (!overlay) return;

            if (actionBtn) actionBtn.href = trackingUrl;

            // Mostrar overlay
            overlay.style.display = 'block';
            overlay.removeAttribute('aria-hidden');

            // Sonido de éxito
            try {
                const audio = new Audio('{{ asset("audio/success.mp3") }}');
                audio.volume = 0.5;
                audio.play().catch(() => {});
            } catch (e) {}

            // Fade in fondo
            requestAnimationFrame(() => {
                bg.style.opacity = '1';

                setTimeout(() => {
                    // Mostrar y animar el SVG check
                    if (checkWrap) {
                        checkWrap.style.opacity = '1';
                        checkWrap.style.transform = 'scale(1)';
                        const circle = checkWrap.querySelector('.oco-circle-fill');
                        const check  = checkWrap.querySelector('.oco-check');
                        if (circle) circle.classList.add('animate');
                        if (check)  check.classList.add('animate');
                    }
                    if (title) { title.style.opacity = '1'; title.style.transform = 'translateY(0)'; }
                    if (sub)   { sub.style.opacity = '1'; }
                }, 50);
            });

            // Mostrar botón/mensaje al terminar la animación (~1.2s)
            let btnShown = false;
            function showActionButton() {
                if (btnShown || !actionBtn) return;
                btnShown = true;
                actionBtn.style.display = 'inline-flex';
                actionBtn.style.alignItems = 'center';
                requestAnimationFrame(() => {
                    actionBtn.style.opacity = '1';
                    actionBtn.style.transform = 'translateY(0)';
                });
            }
            setTimeout(showActionButton, 1400);
        }
        // ────────────────────────────────────────────────────────────
    </script>
@endpush
