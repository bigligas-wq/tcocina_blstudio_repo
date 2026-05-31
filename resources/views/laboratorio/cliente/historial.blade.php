@extends('layouts.admin')

@section('title', 'Historial · Laboratorio')

@include('laboratorio._head')

@section('content')
<div class="lab-app">

    <div class="lab-hero">
        @include('laboratorio._brand')
        <h1 class="lab-display">Tu historial</h1>
        <p>Todos tus pedidos del Laboratorio y el estado de cada mejora.</p>
        <div class="lab-hero-actions">
            <a href="{{ route('laboratorio.index') }}" class="lab-btn lab-btn-ghost">← Volver al catálogo</a>
            <span class="lab-credits-pill">💎 Créditos: <span class="amount">USD {{ number_format($wallet->balance_usd, 2) }}</span></span>
        </div>
    </div>

    @if ($movements->isNotEmpty())
        <div class="lab-history-card">
            <div class="lab-section-head" style="margin: 0 0 12px;">
                <div>
                    <span class="lab-eyebrow">Movimientos de créditos</span>
                    <h2 class="lab-display" style="font-size: 16px;">Últimos {{ $movements->count() }}</h2>
                </div>
            </div>
            @foreach ($movements as $m)
                <div class="lab-history-item">
                    <div class="lab-history-item-info">
                        <strong>{{ $m->descripcion }}</strong>
                        <div class="note" style="color: var(--lab-text-muted); font-style: normal;">
                            {{ $m->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <span class="lab-mono" style="color: {{ $m->tipo === 'credito' ? 'var(--lab-green)' : 'var(--lab-red)' }}; font-weight: 600;">
                        {{ $m->tipo === 'credito' ? '+' : '-' }} USD {{ number_format($m->monto_usd, 2) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($orders->isEmpty())
        <div class="lab-history-card" style="text-align:center; padding: 40px;">
            <p style="color: var(--lab-text-muted); margin-bottom: 16px;">Todavía no hiciste ningún pedido.</p>
            <a href="{{ route('laboratorio.index') }}" class="lab-btn lab-btn-primary">Ver mejoras disponibles</a>
        </div>
    @else
        @foreach ($orders as $order)
            <div class="lab-history-card">
                <div class="lab-history-head">
                    <div>
                        <span class="lab-history-num">{{ $order->order_number }}</span>
                        <div style="color: var(--lab-text-muted); font-size: 13px; margin-top: 2px;">
                            {{ $order->created_at->format('d/m/Y H:i') }}
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
                        <div class="lab-mono" style="color: var(--lab-amber); font-size: 20px; font-weight: 700; margin-top: 6px;">
                            USD {{ number_format($order->total_usd, 2) }}
                        </div>
                    </div>
                </div>

                @foreach ($order->items as $item)
                    <div class="lab-history-item">
                        <div class="lab-history-item-info">
                            <strong>{{ $item->nombre_snapshot }}</strong>
                            @if ($item->nota)
                                <div class="note">"{{ $item->nota }}"</div>
                            @endif
                        </div>
                        @php
                            $itemBadge = match($item->estado) {
                                'pendiente' => 'muted',
                                'en_proceso' => 'blue',
                                'activo' => 'green',
                                default => 'muted',
                            };
                            $itemLabel = match($item->estado) {
                                'pendiente' => 'Pendiente',
                                'en_proceso' => '⚙ En proceso',
                                'activo' => '✓ Activa',
                                default => $item->estado,
                            };
                        @endphp
                        <span class="lab-badge {{ $itemBadge }}">{{ $itemLabel }}</span>
                    </div>
                @endforeach

                @if ($order->comprobante_url)
                    <div style="margin-top: 12px;">
                        <a href="{{ $order->comprobante_url }}" target="_blank" class="lab-btn" style="font-size: 12px;">
                            📎 Comprobante enviado
                        </a>
                    </div>
                @endif
            </div>
        @endforeach

        <div style="margin-top: 16px;">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
