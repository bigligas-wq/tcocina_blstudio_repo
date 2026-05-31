@extends('layouts.admin')

@section('title', 'Cocina - TecoCina')
@section('page-title', 'Gestión de Cocina')

@section('content')
    <div class="row">
        <!-- Pending Orders -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clock me-2"></i>Pedidos Confirmados
                    </h6>
                </div>
                <div class="card-body">
                    <div id="pending-orders">
                        @foreach ($orders->where('status', 'confirmed') as $order)
                            <div class="order-card mb-3 p-3 border rounded" data-order-id="{{ $order->id }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">#{{ $order->order_number }}</h6>
                                    <div class="text-end">
                                        <span class="badge bg-warning">{{ $order->created_at->format('H:i') }}</span><br>
                                        <small class="text-primary" data-timer
                                            data-from="{{ optional($order->confirmed_at ?: $order->created_at)->format('c') }}">00:00</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-2">
                                    {{ optional($order->user)->name ?? ($order->contact_name ?: 'Invitado') }} -
                                    {{ optional($order->user)->phone ?? $order->contact_phone }}</p>

                                <div class="order-items">
                                    @foreach ($order->items as $item)
                                        <div class="item-row d-flex justify-content-between align-items-center py-1">
                                            <div>
                                                <strong>{{ $item->quantity }}x {{ $item->product->name }}</strong>
                                                @php
                                                    $variants = $item->selected_variants;
                                                    if (!is_array($variants)) {
                                                        $variants = $variants
                                                            ? (json_decode($variants, true) ?:
                                                            [])
                                                            : [];
                                                    }
                                                    $options = $item->selected_options;
                                                    if (!is_array($options)) {
                                                        $options = $options ? (json_decode($options, true) ?: []) : [];
                                                    }
                                                @endphp
                                                @if (!empty($variants))
                                                    <br>
                                                    @foreach (collect($variants)->groupBy('name') as $name => $vals)
                                                        <small class="text-muted"><strong>{{ $name }}:</strong>
                                                            {{ collect($vals)->pluck('value')->join(', ') }}</small><br>
                                                    @endforeach
                                                @endif
                                                @if (!empty($options))
                                                    <br>
                                                    @foreach (collect($options)->groupBy('name') as $name => $vals)
                                                        <small class="text-info"><strong>{{ $name }}:</strong>
                                                            {{ collect($vals)->pluck('value')->join(', ') }}</small><br>
                                                    @endforeach
                                                @endif
                                                @if ($item->special_instructions)
                                                    <br><small class="text-warning"><i
                                                            class="fas fa-exclamation-triangle"></i>
                                                        {{ $item->special_instructions }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($order->notes)
                                    <div class="order-notes mt-2 p-2 bg-light rounded">
                                        <small><strong>Notas:</strong> {{ $order->notes }}</small>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <button class="btn btn-success btn-sm w-100"
                                        onclick="startPreparation({{ $order->id }}, this)" data-action-btn>
                                        <i class="fas fa-play me-1"></i>Iniciar Preparación
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        @if ($orders->where('status', 'confirmed')->count() === 0)
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-muted">No hay pedidos confirmados</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Preparing Orders -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-fire me-2"></i>En Preparación
                    </h6>
                </div>
                <div class="card-body">
                    <div id="preparing-orders">
                        @foreach ($orders->where('status', 'preparing') as $order)
                            <div class="order-card mb-3 p-3 border rounded" data-order-id="{{ $order->id }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">#{{ $order->order_number }}</h6>
                                    <div class="text-end">
                                        <span class="badge bg-info">{{ $order->created_at->format('H:i') }}</span><br>
                                        <small class="text-primary" data-timer
                                            data-from="{{ optional($order->preparing_at ?: $order->confirmed_at ?: $order->created_at)->format('c') }}">00:00</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-2">
                                    {{ optional($order->user)->name ?? ($order->contact_name ?: 'Invitado') }} -
                                    {{ optional($order->user)->phone ?? $order->contact_phone }}</p>

                                <div class="order-items">
                                    @foreach ($order->items as $item)
                                        <div class="item-row d-flex justify-content-between align-items-center py-1">
                                            <div>
                                                <strong>{{ $item->quantity }}x {{ $item->product->name }}</strong>
                                                @php
                                                    $variants = $item->selected_variants;
                                                    if (!is_array($variants)) {
                                                        $variants = $variants
                                                            ? (json_decode($variants, true) ?:
                                                            [])
                                                            : [];
                                                    }
                                                    $options = $item->selected_options;
                                                    if (!is_array($options)) {
                                                        $options = $options ? (json_decode($options, true) ?: []) : [];
                                                    }
                                                @endphp
                                                @if (!empty($variants))
                                                    <br>
                                                    @foreach (collect($variants)->groupBy('name') as $name => $vals)
                                                        <small class="text-muted"><strong>{{ $name }}:</strong>
                                                            {{ collect($vals)->pluck('value')->join(', ') }}</small><br>
                                                    @endforeach
                                                @endif
                                                @if (!empty($options))
                                                    <br>
                                                    @foreach (collect($options)->groupBy('name') as $name => $vals)
                                                        <small class="text-info"><strong>{{ $name }}:</strong>
                                                            {{ collect($vals)->pluck('value')->join(', ') }}</small><br>
                                                    @endforeach
                                                @endif
                                                @if ($item->special_instructions)
                                                    <br><small class="text-warning"><i
                                                            class="fas fa-exclamation-triangle"></i>
                                                        {{ $item->special_instructions }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($order->notes)
                                    <div class="order-notes mt-2 p-2 bg-light rounded">
                                        <small><strong>Notas:</strong> {{ $order->notes }}</small>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <button class="btn btn-success btn-sm w-100"
                                        onclick="markReady({{ $order->id }}, this)" data-action-btn>
                                        <i class="fas fa-check me-1"></i>Marcar como Listo
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        @if ($orders->where('status', 'preparing')->count() === 0)
                            <div class="text-center py-4">
                                <i class="fas fa-fire fa-3x text-info mb-3"></i>
                                <p class="text-muted">No hay pedidos en preparación</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kitchen Display Link -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('kitchen.display') }}" class="btn btn-primary btn-lg" target="_blank">
                <i class="fas fa-tv me-2"></i>Abrir Pantalla de Cocina
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .order-card {
            transition: all 0.3s ease;
            border-left: 4px solid #ffc107;
        }

        .order-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .item-row {
            border-bottom: 1px solid #eee;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .order-notes {
            border-left: 3px solid #ffc107;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Timers (mm:ss desde data-from)
        function initTimers() {
            const els = document.querySelectorAll('[data-timer]');
            els.forEach(el => {
                const fromIso = el.getAttribute('data-from');
                const start = fromIso ? new Date(fromIso) : null;
                if (!start) return;
                const update = () => {
                    const diffMs = Date.now() - start.getTime();
                    const totalSec = Math.max(0, Math.floor(diffMs / 1000));
                    const mm = String(Math.floor(totalSec / 60)).padStart(2, '0');
                    const ss = String(totalSec % 60).padStart(2, '0');
                    el.textContent = `${mm}:${ss}`;
                };
                update();
                setInterval(update, 1000);
            });
        }

        document.addEventListener('DOMContentLoaded', initTimers);

        const inFlight = new Set();

        function setButtonLoading(btn, loading) {
            if (!btn) return;
            if (loading) {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
            } else {
                btn.disabled = false;
                if (btn.dataset.originalText) btn.innerHTML = btn.dataset.originalText;
            }
        }

        function startPreparation(orderId, btn) {
            if (inFlight.has(orderId)) return;
            inFlight.add(orderId);
            setButtonLoading(btn, true);
            fetch(`/kitchen/orders/${orderId}/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Pedido iniciado en preparación', 'success');
                        // Move order to preparing section
                        moveOrderToSection(orderId, 'preparing');
                    } else {
                        showNotification('Error al iniciar preparación', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al iniciar preparación', 'error');
                })
                .finally(() => {
                    inFlight.delete(orderId);
                    setButtonLoading(btn, false);
                });
        }

        function markReady(orderId, btn) {
            if (inFlight.has(orderId)) return;
            inFlight.add(orderId);
            setButtonLoading(btn, true);
            fetch(`/kitchen/orders/${orderId}/ready`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Pedido marcado como listo', 'success');
                        // Remove order from preparing section
                        removeOrderFromSection(orderId);
                    } else {
                        showNotification('Error al marcar como listo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al marcar como listo', 'error');
                })
                .finally(() => {
                    inFlight.delete(orderId);
                    setButtonLoading(btn, false);
                });
        }

        function moveOrderToSection(orderId, section) {
            const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
            if (orderCard) {
                orderCard.remove();
                // Refresh the page to show updated orders
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        }

        function removeOrderFromSection(orderId) {
            const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
            if (orderCard) {
                orderCard.remove();
            }
        }

        function showNotification(message, type) {
            const toast = document.createElement('div');
            toast.className =
                `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
@endpush
