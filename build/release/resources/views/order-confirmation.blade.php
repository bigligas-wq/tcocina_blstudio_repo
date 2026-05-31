@extends('layouts.app')

@section('title', 'Pedido Pendiente - TecoCina')

@section('content')
    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Success Icon & Message -->
        <div class="text-center mb-4">
            <div class="w-20 h-20 mx-auto bg-warning rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-clock text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-text-primary mb-2">¡Pedido Pendiente!</h1>

        </div>


        <!-- WhatsApp Button -->
        <div class="text-center mb-6">
            @php
                // Formatear el número de WhatsApp (eliminar espacios y caracteres especiales)
                $whatsappNumber = '5492494015745';

                // Construir el mensaje para WhatsApp con secuencias Unicode (evita problemas de codificación)
                $lines = [];
                $lines[] = '¡Hola! Acabo de realizar un pedido en TecoCina:';
                $lines[] = '';
                $lines[] = "\u{1F4CB} *Pedido #{$order->order_number}*";
                $customerName = $order->contact_name ?: ($order->user?->name ?: 'Invitado');
                $customerPhone = $order->contact_phone ?: ($order->user?->phone ?: '');
                $isPickup = $order->address?->name === 'Retiro en local';
                $methodText = $isPickup ? 'Retiro en Local' : 'Delivery';

                $lines[] = "\u{1F464} Cliente: {$customerName}";
                if ($customerPhone) {
                    $lines[] = "\u{1F4DE} Teléfono: {$customerPhone}";
                }
                $lines[] = "\u{1F69A} Método: {$methodText}";

                if (!$isPickup && $order->address) {
                    $addrParts = array_filter([
                        trim(($order->address->street ?? '') . ' ' . ($order->address->number ?? '')),
                        trim(($order->address->city ?? '') . ' ' . ($order->address->postal_code ?? '')),
                    ]);
                    $addrText = implode(', ', $addrParts);
                    if (!empty($addrText)) {
                        $lines[] = "\u{1F4CD} Dirección: {$addrText}";
                    }
                }

                $lines[] = '';
                $lines[] = '*PRODUCTOS:*';
                foreach ($order->items as $item) {
                    $line = "- {$item->quantity}x {$item->product->name}";

                    $variants = $item->selected_variants;
                    if (!is_array($variants)) {
                        $variants = $variants ? (json_decode($variants, true) ?: []) : [];
                    }
                    $options = $item->selected_options;
                    if (!is_array($options)) {
                        $options = $options ? (json_decode($options, true) ?: []) : [];
                    }
                    $details = [];
                    if (!empty($variants)) {
                        $details[] = collect($variants)->pluck('value')->join(', ');
                    }
                    if (!empty($options)) {
                        $details[] = collect($options)->pluck('value')->join(', ');
                    }
                    if (!empty($details)) {
                        $line .= ' (' . implode(', ', $details) . ')';
                    }

                    $line .= ' — $' . number_format($item->total_price, 2);
                    $lines[] = $line;
                }

                $lines[] = '';
                $lines[] = "\u{1F4B0} *TOTAL: $" . number_format($order->total_amount, 2) . '*';

                if ($order->notes) {
                    // Extraer información relevante de las notas
                    if (strpos($order->notes, 'Programado para:') !== false) {
                        preg_match('/Programado para: ([^|]+)/', $order->notes, $matches);
                        if (!empty($matches[1])) {
                            $lines[] = "\u{23F0} Horario: " . trim($matches[1]);
                        }
                    }
                    if (strpos($order->notes, 'Pago:') !== false) {
                        preg_match('/Pago: ([^|]+)/', $order->notes, $matches);
                        if (!empty($matches[1])) {
                            $lines[] = "\u{1F4B3} " . trim($matches[1]);
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
                class="inline-flex items-center px-6 py-3 bg-success hover:bg-success-600 text-white font-medium rounded-lg transition-colors">
                <i class="fab fa-whatsapp text-2xl me-1"></i>
                Confirmar pedido por WhatsApp
            </a>

        </div>

        <!-- Estimated Time -->


        <!-- Order Details Card -->
        <div class="card p-6 mb-6">
            <div class="border-b border-border-light pb-4 mb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold text-text-primary mb-1">Pedido #{{ $order->order_number }}</h2>
                        <p class="text-sm text-text-secondary">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-warning text-white rounded-full text-sm">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="mb-6">
                <h3 class="font-semibold text-text-primary mb-3">Información del Cliente</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-text-secondary">Nombre:</span>
                        <span class="text-text-primary ml-2">{{ $order->customer_name }}</span>
                    </div>
                    <div>
                        <span class="text-text-secondary">Teléfono:</span>
                        <span class="text-text-primary ml-2">{{ $order->customer_phone }}</span>
                    </div>

                    <div>
                        <span class="text-text-secondary">Método:</span>
                        <span class="text-text-primary ml-2">
                            {{ $order->order_type === 'delivery' ? 'Delivery' : 'Retiro en Local' }}
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
                <div class="space-y-3">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between items-center py-2 border-b border-border-light">
                            <div class="flex-1">
                                <p class="text-text-primary">
                                    {{ $item->quantity }}x {{ $item->product->name }}
                                </p>
                                @if ($item->customizations)
                                    @php
                                        $customizations = is_array($item->customizations)
                                            ? $item->customizations
                                            : (json_decode($item->customizations, true) ?:
                                            []);
                                    @endphp
                                    @if (!empty($customizations['variants']) || !empty($customizations['options']))
                                        <p class="text-sm text-text-secondary">
                                            @if (!empty($customizations['variants']))
                                                {{ collect($customizations['variants'])->pluck('value')->join(', ') }}
                                            @endif
                                            @if (!empty($customizations['options']))
                                                @if (!empty($customizations['variants']))
                                                    ,
                                                @endif
                                                {{ collect($customizations['options'])->pluck('value')->join(', ') }}
                                            @endif
                                        </p>
                                    @endif
                                @endif
                            </div>
                            <span class="text-text-primary font-medium">
                                ${{ number_format($item->total_price, 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Totals -->
            <div class="border-t border-border-light pt-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-text-secondary">Subtotal</span>
                        <span class="text-text-primary">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if ($order->delivery_fee > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-text-secondary">Envío</span>
                            <span class="text-text-primary">${{ number_format($order->delivery_fee, 2) }}</span>
                        </div>
                    @endif
                    @if ($order->subtotal > $order->total)
                        <div class="flex justify-between text-sm">
                            <span class="text-success">Descuento</span>
                            <span class="text-success">-${{ number_format($order->subtotal - $order->total, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold text-lg pt-2 border-t border-border-light">
                        <span class="text-text-primary">Total</span>
                        <span class="text-primary">${{ number_format($order->total, 2) }}</span>
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

        <!-- Action Button -->
        <div class="text-center mt-6">
            <a href="{{ route('catalog') }}" class="btn-primary">
                <i class="fas fa-utensils mr-2"></i>
                Seguir Comprando
            </a>
        </div>
    </main>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('burger_house/css/main.css') }}" />
@endpush
