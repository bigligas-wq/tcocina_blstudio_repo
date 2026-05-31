@extends('layouts.admin')

@section('title', 'Cocina - TCocina')
@section('page-title', 'Gestión de Cocina')

@section('content')
    <!-- Kitchen Display Link -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <a href="{{ route('kitchen.display') }}" class="btn btn-primary btn-lg" target="_blank">
                <i class="fas fa-tv me-2"></i>Abrir Pantalla de Cocina
            </a>
        </div>
    </div>

    <!-- Microturnos del Día -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Microturnos de Hoy -
                        {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    @if ($microturnos->count() > 0)
        <div class="row g-3">
            @foreach ($microturnos as $microturno)
                @php
                    $pedidosEnMicroturno = $pedidosPorMicroturno->get($microturno->getSortOrderAttribute(), collect());
                    $pedidosConfirmados = $pedidosEnMicroturno->where('status', 'confirmed');
                    $pedidosPreparando = $pedidosEnMicroturno->where('status', 'preparing');
                    $totalPedidos = $pedidosEnMicroturno->count();
                    $capacidadRestante = $microturno->getCapacidadRestanteAttribute();
                @endphp

                <div class="col-12">
                    <div class="card microturno-card {{ $totalPedidos > 0 ? 'border-primary' : '' }}">
                        <div class="card-header {{ $totalPedidos > 0 ? 'bg-primary text-white' : 'bg-light' }}">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <h6 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        <strong>{{ $microturno->getFormattedTimeAttribute() }}</strong>
                                    </h6>
                                </div>
                                <div class="col-md-8 text-md-end mt-2 mt-md-0">
                                    @if ($totalPedidos > 0)
                                        <span class="badge bg-primary">
                                            <i class="fas fa-shopping-cart me-1"></i>
                                            {{ $totalPedidos }} pedido{{ $totalPedidos !== 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($totalPedidos > 0)
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach ($pedidosEnMicroturno->sortBy('created_at') as $order)
                                        <div class="col-lg-6">
                                            <div class="order-card p-3 border rounded {{ $order->status === 'preparing' ? 'border-info bg-light' : 'border-warning' }}"
                                                data-order-id="{{ $order->id }}">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0">#{{ $order->order_number }}</h6>
                                                    <div class="text-end">
                                                        <span
                                                            class="badge {{ $order->status === 'preparing' ? 'bg-info' : 'bg-warning text-dark' }}">
                                                            {{ $order->created_at->format('H:i') }}
                                                        </span><br>
                                                    </div>
                                                </div>

                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $order->contact_name ?: (optional($order->user)->name ?: 'Invitado') }}
                                                    <i class="fas fa-phone ms-2 me-1"></i>
                                                    {{ optional($order->user)->phone ?? $order->contact_phone }}
                                                </p>
                                                @if ($order->address)
                                                    <div class="mb-2">
                                                        <span class="badge bg-info text-dark">
                                                            <i class="fas fa-truck me-1"></i>
                                                            Delivery:
                                                            {{ trim(($order->address->street ?? '').' '.($order->address->number ?? '')) }}
                                                            @if ($order->address->neighborhood) - {{ $order->address->neighborhood }} @endif
                                                            @if ($order->address->city) - {{ $order->address->city }} @endif
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="mb-2">
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-store me-1"></i>
                                                            Retiro en local
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="order-items">
                                                    @foreach ($order->items as $item)
                                                        <div
                                                            class="item-row d-flex justify-content-between align-items-start py-2 border-bottom">
                                                            <div class="flex-grow-1">
                                                                <strong>{{ $item->quantity }}x
                                                                    {{ $item->product->name ?? 'Producto no disponible' }}</strong>
                                                                @if ($item->configuration_text)
                                                                    <br>
                                                                    <small
                                                                        class="text-muted">{{ $item->configuration_text }}</small>
                                                                @endif
                                                                @if ($item->special_instructions)
                                                                    <br><small class="text-warning">
                                                                        <i class="fas fa-exclamation-triangle"></i>
                                                                        {{ $item->special_instructions }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @if ($order->notes)
                                                    <div
                                                        class="order-notes mt-2 p-2 bg-warning bg-opacity-10 rounded border-start border-warning border-3">
                                                        <small>
                                                            <i class="fas fa-sticky-note me-1"></i>
                                                            <strong>Notas:</strong> {{ $order->notes }}
                                                        </small>
                                                    </div>
                                                @endif

                                                <div class="mt-3">
                                                    @if ($order->status === 'confirmed')
                                                        <button class="btn btn-success btn-sm w-100"
                                                            onclick="startPreparation({{ $order->id }}, this)"
                                                            data-action-btn>
                                                            <i class="fas fa-play me-1"></i>Iniciar Preparación
                                                        </button>
                                                    @else
                                                        <button class="btn btn-primary btn-sm w-100"
                                                            onclick="markReady({{ $order->id }}, this)" data-action-btn>
                                                            <i class="fas fa-check me-1"></i>Marcar como Entregado
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="card-body text-center text-muted py-3">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">No hay pedidos en este microturno</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay microturnos configurados para hoy</h5>
                        <p class="text-muted">Configura los microturnos en la sección de Gestión de Turnos</p>
                        <a href="{{ route('admin.turnos') }}" class="btn btn-primary">
                            <i class="fas fa-cog me-2"></i>Ir a Gestión de Turnos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .microturno-card {
            transition: all 0.3s ease;
        }

        .microturno-card.border-primary {
            border-width: 2px !important;
        }

        .order-card {
            transition: all 0.3s ease;
            background: white;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .order-card.border-info {
            border-width: 2px !important;
        }

        .item-row:last-child {
            border-bottom: none !important;
        }

        .badge {
            font-size: 0.85rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto refresh every 30 seconds
            setInterval(() => {
                location.reload();
            }, 30000);
        });

        function startPreparation(orderId, button) {
            if (button.disabled) return;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';

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
                        showNotification('Pedido en preparación', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(data.error || 'Error al iniciar preparación', 'error');
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-play me-1"></i>Iniciar Preparación';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al iniciar preparación', 'error');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-play me-1"></i>Iniciar Preparación';
                });
        }

        function markReady(orderId, button) {
            if (button.disabled) return;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';

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
                        showNotification('Pedido entregado', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(data.error || 'Error al marcar como entregado', 'error');
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-check me-1"></i>Marcar como Entregado';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al marcar como entregado', 'error');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check me-1"></i>Marcar como Entregado';
                });
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
                toastContainer.className = 'position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, {
                delay: 3000
            });
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
    </script>
@endpush
