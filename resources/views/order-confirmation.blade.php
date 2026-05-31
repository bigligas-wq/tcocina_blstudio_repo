@extends('layouts.app')

@section('title', 'Pedido Pendiente - TecoCina')

@section('content')

@php $skipTurno = \App\Models\BusinessSetting::get('skip_turno_selection', false); @endphp
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
            <div class="flow-step-circle done"><i class="fas fa-check" style="font-size:.6rem;"></i></div>
            <span class="flow-step-label done d-none d-sm-inline">Checkout</span>
        </div>
        <div class="flow-step-line done"></div>
        <div class="flow-step">
            <div class="flow-step-circle active">{{ $skipTurno ? '3' : '4' }}</div>
            <span class="flow-step-label active d-none d-sm-inline">Confirmación</span>
        </div>
    </div>
</div>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Warning Icon & Message -->
        <div class="text-center mb-4">
            <div class="w-20 h-20 mx-auto warning-icon-container rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-exclamation-triangle text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-text-primary mb-2">↓ Casi listo! Confirma tu pedido ↓</h1>
            <div class="alert alert-danger border-danger bg-danger bg-opacity-10 border-2 mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-danger me-3 fs-4"></i>
                    <div>
                        <strong class="text-danger">IMPORTANTE:</strong> Tu pedido <strong>NO está confirmado</strong> hasta que te confirmemos por WhatsApp
                    </div>
                </div>
            </div>
        </div>


        <!-- WhatsApp Button -->
        <div class="text-center mb-6">
            <div class="mb-3">
                <h5 class="text-primary mb-2">Siguiente Paso: Escribinos por WhatsApp</h5>
                
            </div>

            @php
                // Formatear el número de WhatsApp (eliminar espacios y caracteres especiales)
                $whatsappNumber = \App\Models\BusinessSetting::get('whatsapp_number', '5492494015745');

                // Construir el mensaje para WhatsApp sin emojis
                $lines = [];
                $lines[] = '¡Hola! Acabo de realizar un pedido en T cocina:';
                $lines[] = '';
                $lines[] = "*Pedido #{$order->order_number}*";
                $customerName = $order->contact_name ?: ($order->user?->name ?: 'Invitado');
                $customerPhone = $order->contact_phone ?: ($order->user?->phone ?: '');
                $isPickup = $order->address_id === null;
                $methodText = $isPickup ? 'Retiro en Local' : 'Delivery';

                $lines[] = "Cliente: {$customerName}";
                if ($customerPhone) {
                    $lines[] = "Telefono: {$customerPhone}";
                }
                $lines[] = "Metodo: {$methodText}";

                // Agregar dirección solo si es delivery
                if (!$isPickup && $order->address) {
                    $addrParts = array_filter([
                        trim(($order->address->street ?? '') . ' ' . ($order->address->number ?? '')),
                        trim(($order->address->city ?? '') . ' ' . ($order->address->postal_code ?? '')),
                    ]);
                    $addrText = implode(', ', $addrParts);
                    if (!empty($addrText)) {
                        $lines[] = "Direccion: {$addrText}";
                    }
                }

                $lines[] = '';
                $lines[] = '*PRODUCTOS:*';
                foreach ($order->items as $item) {
                    $line = "- {$item->quantity}x {$item->product->name}";

                    // Use new configuration structure
                    if ($item->configuration_text) {
                        $line .= ' (' . $item->configuration_text . ')';
                    }

                    $line .= ' — $' . number_format($item->total_price, 2, ',', '.');
                    $lines[] = $line;
                }

                $lines[] = '';

                // Mostrar y debugear el subtotal y descuentos
                // For debug - asegurarse de que el subtotal esté correctamente calculado
                // Nota: En algunos setups, $order->subtotal_amount puede no estar seteado correctamente.
                // Si es 0, calcular la suma de los items manualmente:

                $realSubtotal = $order->subtotal;
                if ($realSubtotal == 0) {
                    foreach ($order->items as $item) {
                        // total_price puede tener descuento ya aplicado, usar unit_price * quantity si existe
                        if (isset($item->unit_price)) {
                            $realSubtotal += $item->unit_price * $item->quantity;
                        } else {
                            // fallback: sumar total_price
                            $realSubtotal += $item->total_price;
                        }
                    }
                }

                // Debug opcional: dejar en comentario/log
                // $lines[] = "(DEBUG Subtotal calculado: {$realSubtotal}, subtotal de order: {$order->subtotal_amount})";

                if ($order->discount_amount > 0) {
                    $lines[] = "*SUBTOTAL: $" . number_format($realSubtotal, 2, ',', '.') . '*';
                    $lines[] = "*DESCUENTO: -$" . number_format($order->discount_amount, 2, ',', '.') . '*';
                }

                $lines[] = "*TOTAL: $" . number_format($order->total_amount, 2, ',', '.') . '*';


                // Agregar información del método de pago
                if ($order->payment_method) {
                    $paymentMethod = $order->payment_method;
                    if ($paymentMethod === 'cash' && $order->discount_amount > 0) {
                        $lines[] = 'Método de pago: Efectivo (con descuento)';
                    } else {
                        $lines[] = 'Método de pago: ' . ucfirst($paymentMethod);
                    }
                }

                if ($order->notes) {
                    // Extraer información relevante de las notas
                    if (strpos($order->notes, 'Programado para:') !== false) {
                        preg_match('/Programado para: ([^|]+)/', $order->notes, $matches);
                        if (!empty($matches[1])) {
                            $lines[] = 'Horario: ' . trim($matches[1]);
                        }
                    }
                    if (strpos($order->notes, 'Pago:') !== false) {
                        preg_match('/Pago: ([^|]+)/', $order->notes, $matches);
                        if (!empty($matches[1])) {
                            $lines[] = trim($matches[1]);
                        }
                    }
                }

                $lines[] = '';
                $lines[] = '¡Gracias por tu pedido!';

                $message = implode("\n", $lines);

                // Crear el enlace de WhatsApp
                $whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($message);
            @endphp

            <a href="{{ $whatsappUrl }}" target="_blank"
                class="btn btn-success btn-lg px-5 py-3 rounded-pill shadow-lg pulse-animation"
                style="font-size: 1.2rem; font-weight: 600;"
                id="whatsapp-confirm-btn">
                <i class="fab fa-whatsapp me-3" style="font-size: 1.5rem;"></i>
                Confirmar pedido por WhatsApp
            </a>
            <div id="redirect-notice" style="display:none" class="mt-3 text-muted small">
                <i class="fas fa-spinner fa-spin me-1"></i> Volviendo al menú en <span id="redirect-count">3</span>s...
            </div>

            <div class="mt-3">
                <p class="text-muted small mb-2">Una vez que confirmes por WhatsApp, podés ver el estado en vivo:</p>
                <a href="{{ route('orders.tracking', $order->order_number) }}"
                   class="btn btn-outline-light rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2"
                   style="border-color: rgba(255,255,255,.3); font-size: .95rem;">
                    <span class="live-dot-conf"></span>
                    Ver estado del pedido en vivo
                </a>
            </div>

            <div class="py-3"></div>

           
        <!-- Estimated Time -->


        <!-- Order Details Card -->
        <div class="card p-6 mb-6">
            <div class="border-b border-border-light pb-4 mb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold text-text-primary mb-1">Pedido #{{ $order->order_number }}</h2>
                        <p class="text-sm text-text-secondary">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-warning text-dark rounded-pill text-sm fw-bold">
                        <i class="fas fa-clock me-1"></i>
                        PENDIENTE DE CONFIRMACIÓN
                    </span>
                </div>
            </div>
 <!-- Segundo Recordatorio -->
 <div class="alert alert-info border-info bg-info bg-opacity-10 border-2 mb-6">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info me-3 fs-4"></i>
                    <div>
                        <strong class="text-info">Recordatorio:</strong> 
                        Tu pedido está <strong>esperando confirmación</strong>. 
                        Debes contactarnos por WhatsApp para que podamos procesarlo y confirmar horario de entrega.
                    </div>
                </div>
            </div>
            <!-- Customer Info -->
            <div class="mb-6">
                <h3 class="font-semibold text-text-primary mb-3">Información del Cliente</h3>
                <div class="text-sm">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-text-secondary">Nombre:</span>
                        <span class="text-text-primary text-right">{{ $order->contact_name }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-text-secondary">Teléfono:</span>
                        <span class="text-text-primary text-right">{{ $order->contact_phone }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-text-secondary">Método:</span>
                        <span class="text-text-primary text-right">
                            {{ $order->address?->name === 'Retiro en local' ? 'Retiro en Local' : 'Delivery' }}
                        </span>
                    </div>
                </div>
                @if ($order->order_type === 'delivery' && $order->delivery_address)
                    <div class="mt-3">
                        <span class="text-text-secondary text-sm">Dirección de entrega:</span>
                        <p class="text-text-primary text-sm">{{ $order->delivery_address }}</p>
                    </div>
                @endif
            </div>

            <!-- Order Items -->
            <div class="mb-6">
                <h3 class="font-semibold text-text-primary mb-3">Productos</h3>
                <div>
                    @foreach ($order->items as $item)
                        <div class="flex items-center py-2 border-b border-border-light justify-between">
                            <div class="flex-1 text-left">
                                <span class="text-text-primary font-medium">
                                    {{ $item->quantity }}x {{ $item->product->name }}
                                </span>
                                @if ($item->configuration_text)
                                    <span class="text-sm text-text-secondary block">
                                        {{ $item->configuration_text }}
                                    </span>
                                @endif
                            </div>
                            <div class="ml-4 min-w-[75px] text-right font-medium text-text-primary">
                                ${{ number_format($item->total_price, 2, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Totals -->
            <div class="border-t border-border-light pt-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-text-secondary">Subtotal</span>
                        <span class="text-text-primary">${{ number_format($order->subtotal, 2, ',', '.') }}</span>
                    </div>
                    @if ($order->delivery_fee > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-text-secondary">Envío</span>
                            <span class="text-text-primary">${{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($order->discount_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-success">Descuento</span>
                            <span class="text-success">-${{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold text-lg pt-2 border-t border-border-light">
                        <span class="text-text-primary">Total</span>
                        <span class="text-primary">${{ number_format($order->total_amount, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if ($order->notes)
                <div class="mt-4 p-3 bg-background rounded-lg">
                    <p class="text-sm text-text-secondary">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ $order->notes }}
                    </p>
                </div>
            @endif
        </div>

      

        <!-- Action Buttons -->
        <div class="text-center mt-6 d-flex flex-column flex-md-row justify-content-center gap-3">
            <a href="{{ route('orders.tracking', $order->order_number) }}"
               class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                <span class="live-dot-conf"></span>
                Ver estado del pedido
            </a>
            @auth
                <a href="{{ route('loyalty.dashboard') }}" class="btn btn-outline-primary rounded-pill px-4 py-2">
                    <i class="fas fa-sun mr-2"></i>
                    Ver mi álbum de Tcocina
                </a>
            @endauth
            <a href="{{ route('catalog') }}" class="btn-primary">
                <i class="fas fa-utensils mr-2"></i>
                Seguir Comprando
            </a>
        </div>
    </main>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('burger_house/css/main.css') }}" />
    <style>
        /* ── Progress bar ── */
        .flow-progress-hero {
            background: linear-gradient(160deg, #0c1929 0%, #0f2744 60%, #0c1929 100%);
            padding: 1.1rem 1rem 1.3rem;
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
        .flow-step-label { font-size: .7rem; font-weight: 600; }
        .flow-step-label.done    { color: #10b981; }
        .flow-step-label.active  { color: #93c5fd; }
        .flow-step-line { width: 26px; height: 2px; border-radius: 1px; margin: 0 4px; flex-shrink: 0; }
        .flow-step-line.done { background: #10b981; }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }
        
        /* Estilos para el icono de advertencia principal */
        .warning-icon-container {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            animation: warning-pulse 3s infinite;
        }
        
        @keyframes warning-pulse {
            0% {
                box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            }
            50% {
                box-shadow: 0 4px 25px rgba(220, 53, 69, 0.5);
            }
            100% {
                box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            }
        }
        
        .warning-icon-container:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        
        .alert-warning {
            border-left: 4px solid #ffc107 !important;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .live-dot-conf {
            display: inline-block;
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #4ade80;
            flex-shrink: 0;
            animation: confDotPulse 1.8s ease-in-out infinite;
        }
        @keyframes confDotPulse {
            0%,100% { opacity:1; transform:scale(1);   box-shadow: 0 0 0 0 rgba(74,222,128,.55); }
            50%      { opacity:.8; transform:scale(1.2); box-shadow: 0 0 0 5px rgba(74,222,128,0); }
        }
    </style>
@endpush

@push('scripts')
<script>
(function () {
    const btn = document.getElementById('whatsapp-confirm-btn');
    const notice = document.getElementById('redirect-notice');
    const countEl = document.getElementById('redirect-count');
    if (!btn) return;

    btn.addEventListener('click', function () {
        if (!notice) return;
        notice.style.display = 'block';
        let seconds = 3;
        const timer = setInterval(function () {
            seconds--;
            if (countEl) countEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = '{{ route('catalog') }}';
            }
        }, 1000);
    });
})();
</script>
@endpush