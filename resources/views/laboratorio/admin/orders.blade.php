@extends('layouts.admin')

@section('title', 'Pedidos del Laboratorio · BLStudio')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    @if (session('success'))
        <div class="lab-alert success">{{ session('success') }}</div>
    @endif

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Pedidos recibidos</h1>
        <p>Cada pedido del cliente, con su comprobante, notas y estado de cada mejora.</p>
        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.admin.index') }}" class="lab-btn lab-btn-ghost">← Volver al catálogo</a>
        </div>
    </div>

    @if ($orders->isEmpty())
        <div class="lab-history-card" style="text-align:center; padding: 40px;">
            <p style="color: var(--lab-text-muted);">Todavía no llegó ningún pedido.</p>
        </div>
    @else
        @foreach ($orders as $order)
            <div class="lab-history-card">
                <div class="lab-history-head">
                    <div>
                        <span class="lab-history-num">PEDIDO · {{ $order->order_number }}</span>
                        <div style="margin-top: 4px; color: var(--lab-text-muted); font-size: 13px;">
                            {{ $order->user->name }} · {{ $order->user->email }} · {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        @php
                            $stateClass = match($order->estado) {
                                'pendiente_pago' => 'amber',
                                'confirmado' => 'blue',
                                'en_proceso' => 'purple',
                                'activo_parcial' => 'blue',
                                'activo' => 'green',
                                'cancelado' => 'red',
                                default => 'muted',
                            };
                        @endphp
                        <span class="lab-badge {{ $stateClass }}">{{ \App\Models\LabOrder::ESTADOS[$order->estado] ?? $order->estado }}</span>
                        <div class="lab-mono" style="color: var(--lab-amber); font-size: 22px; font-weight: 700; margin-top: 8px;">
                            USD {{ number_format($order->total_usd, 2) }}
                        </div>
                    </div>
                </div>

                @foreach ($order->items as $item)
                    <div class="lab-history-item">
                        <div class="lab-history-item-info">
                            <strong>{{ $item->nombre_snapshot }}</strong>
                            <span class="lab-mono" style="color: var(--lab-text-muted); margin-left: 8px;">USD {{ number_format($item->precio_usd_snapshot, 2) }}</span>
                            @if ($item->nota)
                                <div class="note">↳ "{{ $item->nota }}"</div>
                            @endif
                        </div>
                        <div style="display:flex; gap:6px; align-items:center;">
                            @php
                                $itemBadge = match($item->estado) {
                                    'pendiente' => 'muted',
                                    'en_proceso' => 'blue',
                                    'activo' => 'green',
                                    default => 'muted',
                                };
                            @endphp
                            <span class="lab-badge {{ $itemBadge }}">{{ $item->estado }}</span>
                            @if ($item->estado !== 'activo' && $order->estado === 'confirmado' || $order->estado === 'en_proceso' || $order->estado === 'activo_parcial')
                                <form action="{{ route('laboratorio.admin.activar-item', [$order->id, $item->id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button class="lab-btn" style="font-size: 11px; padding: 6px 10px;">Marcar activa</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div style="display:flex; gap: 8px; margin-top: 16px; align-items: center; flex-wrap: wrap;">
                    @if ($order->comprobante_url)
                        <a href="{{ $order->comprobante_url }}" target="_blank" class="lab-btn">
                            📎 Ver comprobante
                        </a>
                    @else
                        <span class="lab-badge muted">Sin comprobante</span>
                    @endif

                    @if ($order->whatsapp_enviado_at)
                        <span class="lab-badge green">WhatsApp enviado {{ $order->whatsapp_enviado_at->diffForHumans() }}</span>
                    @endif

                    @if ($order->estado === 'pendiente_pago')
                        <form action="{{ route('laboratorio.admin.confirmar-pago', $order->id) }}" method="POST" style="margin-left:auto;">
                            @csrf
                            <button class="lab-btn lab-btn-amber">✓ Confirmar pago</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach

        {{ $orders->links() }}
    @endif
</div>
@endsection
