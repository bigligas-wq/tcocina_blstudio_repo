@extends('layouts.admin')

@section('title', 'Gestión de Pedidos - TecoCina Admin')
@section('page-title', 'Gestión de Pedidos')

@section('content')
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="orders-filter-form" method="GET" action="{{ route('admin.orders') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente
                                </option>
                                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>
                                    Confirmado</option>
                                <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>
                                    Preparando</option>
                                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Listo</option>
                                <option value="out_for_delivery"
                                    {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>En camino</option>
                                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>
                                    Entregado</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                                    Cancelado</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales: Ver pedido -->
    @foreach ($orders as $order)
        <div class="modal fade" id="modalOrder{{ $order->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pedido #{{ $order->order_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="mb-2">Cliente</h6>
                                <div><strong>{{ $order->contact_name ?: $order->user->name ?? 'Invitado' }}</strong>
                                </div>
                                <div class="text-muted small">{{ $order->contact_phone ?: $order->user->phone ?? '' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Detalles</h6>
                                <div class="small">Estado: <strong>{{ ucfirst($order->status) }}</strong></div>
                                <div class="small">Pago: <strong>{{ ucfirst($order->payment_method) }}</strong> · <span
                                        class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">{{ $order->payment_status_label }}</span>
                                </div>
                                <div class="small">Fecha: {{ $order->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>

                        @if ($order->address)
                            <div class="mt-3">
                                <h6 class="mb-2">Entrega</h6>
                                <div class="small text-muted">
                                    {{ trim(($order->address->street ?? '') . ' ' . ($order->address->number ?? '')) }},
                                    {{ trim(($order->address->city ?? '') . ' ' . ($order->address->postal_code ?? '')) }}
                                </div>
                            </div>
                        @endif

                        <div class="mt-3">
                            <h6 class="mb-2">Productos</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Detalles</th>
                                            <th class="text-end">Cant.</th>
                                            <th class="text-end">Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->items as $item)
                                            <tr>
                                                <td>{{ optional($item->product)->name ?? 'Producto eliminado' }}</td>
                                                <td class="small text-muted">
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
                                                            $options = $options
                                                                ? (json_decode($options, true) ?:
                                                                [])
                                                                : [];
                                                        }
                                                    @endphp
                                                    @if (!empty($variants) || !empty($options))
                                                        <ul class="mb-0 ps-3">
                                                            @foreach (collect($variants)->groupBy('name') as $name => $vals)
                                                                <li><strong>{{ $name }}:</strong>
                                                                    {{ collect($vals)->pluck('value')->join(', ') }}</li>
                                                            @endforeach
                                                            @foreach (collect($options)->groupBy('name') as $name => $vals)
                                                                <li><strong>{{ $name }}:</strong>
                                                                    {{ collect($vals)->pluck('value')->join(', ') }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $item->quantity }}</td>
                                                <td class="text-end">${{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h6 class="mb-2">Totales</h6>
                            <div class="d-flex justify-content-between small">
                                <span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            @if ($order->delivery_fee > 0)
                                <div class="d-flex justify-content-between small">
                                    <span>Envío</span><span>${{ number_format($order->delivery_fee, 2) }}</span>
                                </div>
                            @endif
                            @if ($order->discount_amount > 0)
                                <div class="d-flex justify-content-between small text-success"><span>Descuento</span><span>-
                                        ${{ number_format($order->discount_amount, 2) }}</span></div>
                            @endif
                            <div class="d-flex justify-content-between fw-semibold">
                                <span>Total</span><span>${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        @if ($order->notes)
                            <div class="mt-3">
                                <h6 class="mb-2">Notas</h6>
                                <div class="small text-muted">{{ $order->notes }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-outline-primary" onclick="printOrder({{ $order->id }})"><i
                                class="fas fa-print me-2"></i>Imprimir</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Lista de Pedidos</h6>
                </div>
                <div class="card-body">
                    @if ($orders->count() > 0)
                        <div class="table-responsive">
                            <table id="orders-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido</th>
                                        <th>Cliente</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Pago</th>
                                        <th>Generado hace</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-tbody">
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>
                                                <strong>#{{ $order->order_number }}</strong>
                                                @if ($order->notes)
                                                    <br><small
                                                        class="text-muted">{{ Str::limit($order->notes, 30) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $order->user->name ?? ($order->contact_name ?: 'Invitado') }}
                                                <br><small
                                                    class="text-muted">{{ $order->user->phone ?? $order->contact_phone }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $order->items->count() }} items</span>
                                            </td>
                                            <td>
                                                <strong>${{ number_format($order->total_amount, 2) }}</strong>
                                                @if ($order->discount_amount > 0)
                                                    <br><small
                                                        class="text-success">-{{ number_format($order->discount_amount, 2) }}
                                                        desc.</small>
                                                @endif
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm status-select"
                                                    data-order-id="{{ $order->id }}">
                                                    <option value="pending"
                                                        {{ $order->status === 'pending' ? 'selected' : '' }}>Pendiente
                                                    </option>
                                                    <option value="confirmed"
                                                        {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado
                                                    </option>
                                                    <option value="preparing"
                                                        {{ $order->status === 'preparing' ? 'selected' : '' }}>Preparando
                                                    </option>
                                                    <option value="ready"
                                                        {{ $order->status === 'ready' ? 'selected' : '' }}>Listo</option>
                                                    <option value="out_for_delivery"
                                                        {{ $order->status === 'out_for_delivery' ? 'selected' : '' }}>En
                                                        camino</option>
                                                    <option value="delivered"
                                                        {{ $order->status === 'delivered' ? 'selected' : '' }}>Entregado
                                                    </option>
                                                    <option value="cancelled"
                                                        {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ $order->payment_status_label }}
                                                </span>
                                                <br><small class="text-muted">{{ ucfirst($order->payment_method) }}</small>
                                            </td>
                                            <td>
                                                @if ($order->status !== 'ready')
                                                    <small class="text-primary" data-timer
                                                        data-from="{{ $order->created_at ? $order->created_at->format('c') : '' }}">
                                                        00:00
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown text-end">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown">
                                                        Acciones
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a href="#" class="dropdown-item"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalOrder{{ $order->id }}">
                                                                <i class="fas fa-eye me-2"></i>Ver pedido
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" class="dropdown-item"
                                                                onclick="printOrder({{ $order->id }})">
                                                                <i class="fas fa-print me-2"></i>Imprimir
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" class="dropdown-item text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalDeleteOrder{{ $order->id }}">
                                                                <i class="fas fa-trash me-2"></i>Eliminar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="orders-pagination" class="d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron pedidos</h5>
                            <p class="text-muted">No hay pedidos que coincidan con los filtros seleccionados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modales: Eliminar pedido -->
    @foreach ($orders as $order)
        <div class="modal fade" id="modalDeleteOrder{{ $order->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.order.destroy', $order->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ¿Seguro que deseas eliminar el pedido #{{ $order->order_number }}?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script>
        // Timers for table (mm:ss from data-from)
        function attachTimer(el) {
            const fromIso = el.getAttribute('data-from');
            if (!fromIso) return;
            const start = new Date(fromIso);
            const update = () => {
                const diffMs = Date.now() - start.getTime();
                const totalSec = Math.max(0, Math.floor(diffMs / 1000));
                const mm = String(Math.floor(totalSec / 60)).padStart(2, '0');
                const ss = String(totalSec % 60).padStart(2, '0');
                el.textContent = `${mm}:${ss}`;
            };
            // limpiar previo
            if (el.dataset.timerId) {
                clearInterval(Number(el.dataset.timerId));
            }
            update();
            const id = setInterval(update, 1000);
            el.dataset.timerId = String(id);
        }

        function initTimers() {
            document.querySelectorAll('[data-timer]').forEach(attachTimer);
        }

        document.addEventListener('DOMContentLoaded', initTimers);

        // Update order status
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;

                fetch(`/admin/orders/${orderId}/status`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Estado actualizado correctamente', 'success');
                            // Resetear el timer del pedido a 00:00 con el nuevo estado
                            const row = this.closest('tr');
                            const timerEl = row ? row.querySelector('[data-timer]') : null;
                            if (newStatus === 'ready') {
                                if (timerEl) {
                                    if (timerEl.dataset.timerId) {
                                        clearInterval(Number(timerEl.dataset.timerId));
                                    }
                                    timerEl.remove();
                                }
                            } else if (timerEl) {
                                // usar el timestamp del estado devuelto por el backend para NO reiniciar indebidamente
                                const fromIso = (data && data.from_ts) ? data.from_ts : new Date()
                                    .toISOString();
                                timerEl.setAttribute('data-from', fromIso);
                                timerEl.textContent = '00:00';
                                attachTimer(timerEl);
                            }
                            // guardar nuevo valor como original
                            this.dataset.originalValue = newStatus;

                            // Actualizar tabla asincrónicamente sin recargar
                            refreshOrdersTable();
                        } else {
                            showNotification('Error al actualizar estado', 'error');
                            // Revert selection
                            this.value = this.dataset.originalValue;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error al actualizar estado', 'error');
                        this.value = this.dataset.originalValue;
                    });
            });

            // Store original value
            select.dataset.originalValue = select.value;
        });

        function printOrder(orderId) {
            // Open print window for order
            window.open(`/admin/orders/${orderId}/print`, '_blank');
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
        // Recarga asincrónica del tbody y paginación
        function refreshOrdersTable(pageUrl) {
            const form = document.getElementById('orders-filter-form');
            const params = new URLSearchParams(new FormData(form));
            const url = pageUrl || `${form.action}?${params.toString()}`;

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTbody = doc.getElementById('orders-tbody');
                    const newPagination = doc.getElementById('orders-pagination');
                    if (newTbody) {
                        document.getElementById('orders-tbody').innerHTML = newTbody.innerHTML;
                    }
                    if (newPagination) {
                        document.getElementById('orders-pagination').innerHTML = newPagination.innerHTML;
                    }
                    // Reenganchar listeners de selects de estado y timers
                    rebindStatusSelects();
                    initTimers();
                    bindPaginationLinks();
                });
        }

        // Enviar filtro on change por AJAX
        document.getElementById('status').addEventListener('change', function() {
            refreshOrdersTable();
        });

        function rebindStatusSelects() {
            document.querySelectorAll('.status-select').forEach(select => {
                // evitar duplicados
                select.replaceWith(select.cloneNode(true));
            });
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', function() {
                    const orderId = this.dataset.orderId;
                    const newStatus = this.value;
                    fetch(`/admin/orders/${orderId}/status`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('Estado actualizado correctamente', 'success');
                                const row = this.closest('tr');
                                const timerEl = row ? row.querySelector('[data-timer]') : null;
                                if (newStatus === 'ready') {
                                    if (timerEl && timerEl.dataset.timerId) {
                                        clearInterval(Number(timerEl.dataset.timerId));
                                        timerEl.remove();
                                    }
                                } else if (timerEl) {
                                    const fromIso = (data && data.from_ts) ? data.from_ts : new Date()
                                        .toISOString();
                                    timerEl.setAttribute('data-from', fromIso);
                                    timerEl.textContent = '00:00';
                                    attachTimer(timerEl);
                                }
                                this.dataset.originalValue = newStatus;
                                refreshOrdersTable();
                            } else {
                                showNotification('Error al actualizar estado', 'error');
                                this.value = this.dataset.originalValue;
                            }
                        })
                        .catch(() => {
                            showNotification('Error al actualizar estado', 'error');
                            this.value = this.dataset.originalValue;
                        });
                });
                // Store original
                select.dataset.originalValue = select.value;
            });
        }

        function bindPaginationLinks() {
            const container = document.getElementById('orders-pagination');
            if (!container) return;
            container.querySelectorAll('a.page-link, .pagination a').forEach(a => {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    if (url) refreshOrdersTable(url);
                });
            });
        }

        // Inicializar al cargar
        document.addEventListener('DOMContentLoaded', () => {
            bindPaginationLinks();
        });
    </script>
@endpush
