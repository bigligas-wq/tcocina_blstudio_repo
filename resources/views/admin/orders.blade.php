@extends('layouts.admin')

@section('title', 'Gestión de Pedidos - TCocina Admin')
@section('page-title', 'Gestión de Pedidos')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Flatpickr CSS for consistent dd/mm/yyyy date UI -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<style>
    /* Badge personalizado para estado 'ready' */
    .badge.bg-ready, .bg-ready {
        background-color: #f59e0b !important;
        color: #000 !important;
    }
    /* Badge personalizado para estado 'on_the_way' */
    .badge.bg-on-the-way, .bg-on-the-way {
        background-color: #7c3aed !important;
        color: #fff !important;
    }

    #daily-total-amount {
        visibility: hidden;
    }

    /* ── New-order row reveal animation ── */
    @keyframes newOrderRowIn {
        0%   { background: rgba(22,163,74,.38); }
        12%  { background: rgba(22,163,74,.22); }
        45%  { background: rgba(22,163,74,.08); }
        100% { background: transparent; }
    }
    @keyframes newOrderLeftBorder {
        0%   { border-left: 3px solid #16a34a; }
        60%  { border-left: 3px solid rgba(22,163,74,.35); }
        100% { border-left: 3px solid transparent; }
    }
    @keyframes toastBikeBounce {
        from { transform: translateX(0) rotate(-8deg); }
        to   { transform: translateX(4px) rotate(8deg); }
    }
    tr.new-order-highlight td {
        animation: newOrderRowIn 4.2s ease-out forwards;
    }
    tr.new-order-highlight td:first-child {
        animation: newOrderRowIn 4.2s ease-out forwards,
                   newOrderLeftBorder 4.2s ease-out forwards;
    }

    /* DataTables responsive adjustments */
    table.dataTable td.child {
        padding: 0.5rem 1rem;
    }
    table.dataTable td.child ul {
        margin: 0;
        padding-left: 1.5rem;
    }
    table.dataTable td.child ul li {
        margin-bottom: 0.25rem;
    }
    table.dataTable td.child ul li span {
        font-weight: 600;
    }

    /* DataTables styling to match web design */
    .dataTables_wrapper {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        font-size: 0.925rem;
    }

    /* Length menu (Show entries) styling */
    .dataTables_length select {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        font-size: 0.9rem;
        padding: 0.375rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
        color: #212529;
    }

    .dataTables_length label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0;
    }

    /* Search input styling */
    .dataTables_filter input {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        font-size: 0.9rem;
        padding: 0.375rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
        color: #212529;
        margin-left: 0.5rem;
    }

    .dataTables_filter label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0;
    }

    /* Info display styling */
    .dataTables_info {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        font-size: 0.875rem;
        color: #6c757d;
        padding-top: 0.75rem;
    }

    /* Pagination styling */
    .dataTables_paginate {
        padding-top: 0.75rem;
        text-align: right;
    }

    .dataTables_paginate .paginate_button {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
        color: #212529;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .dataTables_paginate .paginate_button:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
    }

    .dataTables_paginate .paginate_button.current {
        background-color: var(--brand-primary, #00b4d8);
        border-color: var(--brand-primary, #00b4d8);
        color: #fff;
    }

    .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f8f9fa;
    }

    /* Table styling */
    table.dataTable {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
    }

    table.dataTable thead th {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    table.dataTable tbody td {
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        font-size: 0.9rem;
        color: #212529;
        vertical-align: middle;
    }

    /* Fix for cut-off content in historical table */
    #orders-table-historico_wrapper {
        overflow-x: auto;
        padding-top: 1rem;
    }

    #orders-table-historico {
        min-width: 1000px;
    }

    /* Barra de controles — top: todo centrado en una sola línea */
    #orders-table-historico_wrapper .dt-controls-top {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        flex-wrap: nowrap;
        margin-bottom: 0.75rem;
    }

    #orders-table-historico_wrapper .dataTables_length {
        flex-shrink: 0;
        white-space: nowrap;
    }

    #orders-table-historico_wrapper .dataTables_length label,
    #orders-table-historico_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0;
        white-space: nowrap;
    }

    #orders-table-historico_wrapper .dataTables_filter {
        flex-shrink: 0;
        white-space: nowrap;
    }

    #orders-table-historico_wrapper .dataTables_filter input {
        display: inline-block;
        width: auto;
    }

    /* Bottom: info + paginación centrados */
    #orders-table-historico_wrapper .dt-controls-bottom {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 0.75rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    #orders-table-historico_wrapper .dataTables_info {
        padding-top: 0;
    }

    #orders-table-historico_wrapper .dataTables_paginate {
        padding-top: 0;
        text-align: center;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        #orders-table-historico_wrapper .dt-controls-top {
            flex-wrap: wrap;
            justify-content: center;
        }
        #orders-table-historico_wrapper .dataTables_filter {
            text-align: center;
        }
        .dataTables_paginate {
            text-align: center;
        }
        .dataTables_info {
            text-align: center;
        }

        /* Historico table mobile: scroll horizontal táctil */
        #orders-table-historico_wrapper .dataTables_scroll {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        #orders-table-historico_wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            max-height: none !important;
        }
        #orders-table-historico_wrapper .dataTables_scrollHead {
            overflow: hidden !important;
        }
    }

    /* Romper el overflow del table-responsive para que los dropdowns salgan */
    .card-body .table-responsive:has(#orders-table) {
        overflow: visible !important;
    }
    /* También para el wrapper de DataTables de la tabla histórica */
    #orders-table-historico_wrapper {
        overflow: visible !important;
    }

    /* Dropdown del filtro Pago: siempre por encima del listado de pedidos */
    .pago-filter-menu {
        z-index: 5000 !important;
    }
    th.pago-filter-th,
    th.pago-filter-th .dropdown {
        overflow: visible;
    }
    /* Elevar el th mientras el menú está abierto (gana a overlays/selects de las filas) */
    th.pago-filter-th:has(.pago-filter-menu.show) {
        position: relative;
        z-index: 1060;
    }
    thead:has(.pago-filter-menu.show) {
        position: relative;
        z-index: 1060;
    }
    /* La tabla histórica clona el encabezado dentro de .dataTables_scrollHead (overflow hidden):
       liberarlo mientras el menú está abierto para que no lo recorte */
    #orders-table-historico_wrapper .dataTables_scrollHead:has(.pago-filter-menu.show) {
        overflow: visible !important;
        position: relative;
        z-index: 1060;
    }

    /* Hover en items del dropdown de acciones */
    .dropdown-menu .dropdown-item {
        transition: background-color 0.2s ease, color 0.2s ease;
        border-radius: 6px;
        margin: 2px 6px;
        padding: 0.5rem 0.75rem;
    }
    .dropdown-menu .dropdown-item:hover {
        background-color: #e0f7fa;
        color: #006064;
    }
    .dropdown-menu .dropdown-item.text-success:hover {
        background-color: #e8f5e9;
        color: #1b5e20;
    }
    .dropdown-menu .dropdown-item.text-warning:hover {
        background-color: #fff3e0;
        color: #e65100;
    }
    .dropdown-menu .dropdown-item.text-danger:hover {
        background-color: #ffebee;
        color: #b71c1c;
    }

    /* Fila de pedido entregado — verde visible en tema oscuro */
    .order-row-delivered {
        background-color: rgba(34, 197, 94, 0.18) !important;
        border-left: 3px solid #22c55e;
    }
    .order-row-delivered td {
        background-color: rgba(34, 197, 94, 0.18) !important;
    }
    .order-row-delivered:hover td,
    .order-row-delivered:hover {
        background-color: rgba(34, 197, 94, 0.30) !important;
    }

    /* ===== TABLA PEDIDOS DEL DÍA — MOBILE CARDS ===== */
    @media (max-width: 768px) {
        /* Quitar el min-width heredado */
        table#orders-table,
        table#orders-table.dataTable {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            border-collapse: separate !important;
        }

        table#orders-table thead {
            display: none !important;
        }

        table#orders-table tbody {
            display: block !important;
            width: 100% !important;
        }

        /* Cada fila = card */
        table#orders-table tbody tr {
            display: block !important;
            width: 100% !important;
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            margin-bottom: 0.85rem !important;
            padding: 0.75rem 0.9rem !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.06) !important;
        }

        /* Cada celda = bloque con label */
        table#orders-table tbody td {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.75rem !important;
            padding: 0.45rem 0 !important;
            border: none !important;
            border-bottom: 1px solid #f1f3f5 !important;
            font-size: 0.88rem !important;
            width: 100% !important;
            min-width: 0 !important;
            white-space: normal !important;
        }

        table#orders-table tbody td:last-child {
            border-bottom: none !important;
        }

        /* Checkbox primera celda */
        table#orders-table tbody td:first-child {
            justify-content: flex-start !important;
            padding-bottom: 0.5rem !important;
            border-bottom: 1px solid #f1f3f5 !important;
        }

        /* Label generado por data-label */
        table#orders-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
            flex-shrink: 0;
        }

        table#orders-table tbody td:first-child::before,
        table#orders-table tbody td:not([data-label])::before {
            content: none !important;
        }

        /* Valor a la derecha */
        table#orders-table tbody td > *:not(::before) {
            text-align: right;
            max-width: 65%;
        }

        /* Selects: ancho auto */
        table#orders-table tbody td .form-select {
            width: auto !important;
            min-width: 130px;
            max-width: 180px;
        }

        /* Ocultar columna Items en mobile */
        table#orders-table tbody td[data-label="Items"] {
            display: none !important;
        }

        /* Loyalty overlay */
        table#orders-table tbody td .loyalty-blur-overlay {
            position: relative !important;
            width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
    @php
        $alreadyAwardedIds = \App\Models\UserLoyaltyMovement::whereIn('order_id',
            $pedidosFecha->pluck('id')->merge($pedidosHistorico->pluck('id'))->unique()->values()
        )->where('reason', 'order_confirmed')->pluck('order_id')->toArray();

        function orderHasLoyaltyAwarded($order, $alreadyAwardedIds) {
            if (in_array($order->id, $alreadyAwardedIds)) {
                return true;
            }
            $log = $order->change_log ?: [];
            foreach ($log as $entry) {
                if (($entry['action'] ?? null) === 'loyalty_awarded') {
                    return true;
                }
            }
            return false;
        }
    @endphp

    <!-- Filters y Total Ventas -->
    <div class="row mb-4">
        <!-- Contenedor para Estado y Fecha (50/50 en mobile) -->
        <div class="col-md-8 filters-row">
            <div class="row g-2">
                <!-- Filtro Estado -->
                <div class="col-md-6 col-6 filter-status">
                    <div class="card">
                        <div class="card-body">
                            <form id="orders-filter-form" method="GET" action="{{ route('admin.orders') }}">
                                    <label for="status" class="form-label">Estado</label>
                                    <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                        <option value="">Todos los estados</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente
                                        </option>
                                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>
                                            Confirmado</option>
                                        <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>
                                            En Preparación</option>
                                        <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>
                                            Listo</option>
                                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>
                                            Entregado</option>
                                    </select>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Filtro Fecha -->
                <div class="col-md-6 col-6 filter-date">
                    <div class="card">
                        <div class="card-body">
                            <form id="orders-date-form" method="GET" action="{{ route('admin.orders') }}">
                                <label for="selected_date" class="form-label">Fecha</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="selected_date"
                                    name="selected_date"
                                    value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                                />
                                <div class="d-flex gap-2 mt-2">
                                    <a class="btn btn-outline-secondary btn-sm"
                                       href="{{ route('admin.orders', ['selected_date' => now()->format('Y-m-d')]) }}">Hoy</a>
                                    <a class="btn btn-outline-secondary btn-sm"
                                       href="{{ route('admin.orders', ['selected_date' => now()->subDay()->format('Y-m-d')]) }}">Ayer</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Ventas del Día -->
        <div class="col-md-4 filter-total">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    @php
                        $coleccion = isset($pedidosFecha) ? $pedidosFecha : $pedidosHoy;
                        $fechaLabel = isset($selectedDate) && $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') : 'Hoy';
                    @endphp
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fas fa-chart-line text-success me-2" style="font-size: 1.5rem;"></i>
                        <h6 class="mb-0 text-success fw-bold">Total Ventas - {{ $fechaLabel }}</h6>
                    </div>
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="display-6 fw-bold text-success" id="daily-total-amount">
                            ${{ number_format($coleccion->sum('total_amount'), 2, ',', '.') }}
                        </div>
                        <button type="button" class="btn btn-link text-success ms-2 p-0" onclick="toggleDailyTotal()" title="Ocultar/Mostrar total">
                            <lord-icon
                                src="{{ asset('lordicons/ojopassword.json') }}"
                                colors="primary:#198754,secondary:#198754"
                                trigger="hover"
                                style="width:20px;height:20px;">
                            </lord-icon>
                        </button>
                        <script>
                            (function() {
                                const isHidden = localStorage.getItem('dailyTotalHidden') === 'true';
                                const amountElement = document.getElementById('daily-total-amount');
                                if (isHidden && amountElement) {
                                    amountElement.dataset.originalAmount = amountElement.textContent;
                                    amountElement.textContent = '••••••';
                                }
                                // Mostrar el elemento después de procesar el estado
                                if (amountElement) {
                                    amountElement.style.visibility = 'visible';
                                }
                            })();
                        </script>
                    </div>
                    <small class="text-muted">
                        {{ $coleccion->count() }} pedido{{ $coleccion->count() !== 1 ? 's' : '' }}
                    </small>
            </div>
        </div>
    </div>

        @php
            // Cálculos de cocina (hamburguesas / acompañamientos / otros) del día seleccionado
            $coleccionCocina = isset($pedidosFecha) ? $pedidosFecha : $pedidosHoy;
            $totalHamburguesasFecha = 0;
            $totalAcompFecha = 0;
            $totalOtrosFecha = 0;
            foreach ($coleccionCocina as $orderTot) {
                if (in_array($orderTot->status, ['cancelled'], true)) continue;
                foreach ($orderTot->items as $itemTot) {
                    $catName = strtolower(optional(optional($itemTot->product)->category)->name ?? '');
                    if ($catName === 'hamburguesas') {
                        $totalHamburguesasFecha += $itemTot->quantity;
                    } elseif ($catName === 'acompañamientos') {
                        $totalAcompFecha += $itemTot->quantity;
                    } else {
                        $totalOtrosFecha += $itemTot->quantity;
                    }
                }
            }
        @endphp
    </div>

    <!-- Acciones Masivas -->
    <div class="row mb-4" id="bulk-actions-container" style="display: none;">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tasks me-2"></i>
                        <h6 class="mb-0">Acciones Masivas</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span class="fw-semibold" id="selected-count">0 pedidos seleccionados</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success fs-6 px-3 py-2" id="selected-total">
                                        <i class="fas fa-dollar-sign me-1"></i>
                                        <span id="total-amount">$0,00</span>
                                    </span>
                                </div>
                        </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" id="bulk-print-btn" disabled>
                                <i class="fas fa-print me-1"></i>Imprimir Seleccionados
                            </button>
                                <button type="button" class="btn btn-outline-warning" id="bulk-status-btn" disabled>
                                    <i class="fas fa-edit me-1"></i>Cambiar Estado
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="bulk-delete-btn" disabled>
                                <i class="fas fa-trash me-1"></i>Eliminar Seleccionados
                            </button>
                                <button type="button" class="btn btn-outline-secondary" id="clear-selection-btn">
                                    <i class="fas fa-times me-1"></i>Limpiar
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales: Ver pedido - Día seleccionado -->
    @foreach ($pedidosFecha as $order)
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
                                <div class="input-group input-group-sm mb-1">
                                    <span class="input-group-text">Nombre</span>
                                    <input type="text" class="form-control order-edit" data-field="contact_name" value="{{ $order->contact_name ?: ($order->user->name ?? '') }}">
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Tel</span>
                                    <input type="text" class="form-control order-edit" data-field="contact_phone" value="{{ $order->contact_phone ?: ($order->user->phone ?? '') }}">
                                    @php
                                        $modalPhoneToday = $order->contact_phone ?: ($order->user->phone ?? '');
                                        $modalCleanPhoneToday = preg_replace('/[^0-9]/', '', $modalPhoneToday);
                                        if (strlen($modalCleanPhoneToday) === 10 && substr($modalCleanPhoneToday, 0, 2) !== '54') {
                                            $modalCleanPhoneToday = '54' . $modalCleanPhoneToday;
                                        }
                                        $modalNameToday = $order->contact_name ?: ($order->user->name ?? 'Cliente');
                                        $modalWhatsappLinkToday = "https://wa.me/{$modalCleanPhoneToday}?text=" . urlencode("Hola {$modalNameToday}, soy de T Cocina. Te escribo respecto a tu pedido #{$order->order_number}.");
                                    @endphp
                                    @if($modalPhoneToday && $modalCleanPhoneToday)
                                        <a href="{{ $modalWhatsappLinkToday }}" target="_blank" class="input-group-text text-success" style="text-decoration: none;" title="Abrir chat WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                </div>
                                @if($order->user && $order->user->email)
                                <div class="input-group input-group-sm mt-1">
                                    <span class="input-group-text">Email</span>
                                    <input type="text" class="form-control" value="{{ $order->user->email }}" readonly>
                                    @if($order->user->google_id)
                                        <span class="input-group-text" title="Login con Google">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z"/></svg>
                                        </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Detalles</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small m-0">Estado</label>
                                        <select class="form-select form-select-sm order-edit" data-field="status">
                                            <option value="pending" {{ $order->status==='pending'?'selected':'' }}>Pendiente</option>
                                            <option value="confirmed" {{ $order->status==='confirmed'?'selected':'' }}>Confirmado</option>
                                            <option value="preparing" {{ $order->status==='preparing'?'selected':'' }}>En preparación</option>
                                            <option value="ready" {{ $order->status==='ready'?'selected':'' }}>Listo ✓</option>
                                            @if($order->address_id !== null)
                                            <option value="on_the_way" {{ $order->status==='on_the_way'?'selected':'' }}>En camino 🛵</option>
                                            @endif
                                            <option value="delivered" {{ $order->status==='delivered'?'selected':'' }}>Entregado</option>
                                        </select>
                                </div>
                                    <div class="col-6">
                                        <label class="form-label small m-0">Forma de pago</label>
                                        <select class="form-select form-select-sm order-edit" data-field="payment_method">
                                            <option value="cash" {{ $order->payment_method==='cash'?'selected':'' }}>Efectivo</option>
                                            <option value="card" {{ $order->payment_method==='card'?'selected':'' }}>Tarjeta</option>
                                            <option value="transfer" {{ $order->payment_method==='transfer'?'selected':'' }}>Transferencia</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small m-0">Estado de pago</label>
                                        <select class="form-select form-select-sm order-edit" data-field="payment_status">
                                            <option value="pending" {{ $order->payment_status==='pending'?'selected':'' }}>Pendiente</option>
                                            <option value="paid" {{ $order->payment_status==='paid'?'selected':'' }}>Pagado</option>
                                            <option value="failed" {{ $order->payment_status==='failed'?'selected':'' }}>Fallido</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small m-0">Fecha</label>
                                        <div class="form-control form-control-sm bg-light">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($order->address)
                            <div class="mt-3">
                                <h6 class="mb-2">Entrega</h6>
                                <div class="row g-2">
                                    <div class="col-8">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Calle</span>
                                            <input type="text" class="form-control order-edit" data-field="address.street" value="{{ $order->address->street }}">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Número</span>
                                            <input type="text" class="form-control order-edit" data-field="address.number" value="{{ $order->address->number }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Notas entrega</span>
                                            <input type="text" class="form-control order-edit" data-field="address.reference" value="{{ $order->address->reference }}">
                                        </div>
                                    </div>
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
                                                    @if ($item->configuration_text)
                                                        {{ $item->configuration_text }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $item->quantity }}</td>
                                                <td class="text-end">${{ number_format($item->total_price, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h6 class="mb-2">Totales</h6>
                            <div class="d-flex justify-content-between small">
                                <span>Subtotal</span><span>${{ number_format($order->subtotal, 2, ',', '.') }}</span>
                            </div>
                            @if ($order->delivery_fee > 0)
                                <div class="d-flex justify-content-between small">
                                    <span>Envío</span><span>${{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($order->discount_amount > 0)
                                <div class="d-flex justify-content-between small text-muted"><span>Descuento</span><span>-
                                        ${{ number_format($order->discount_amount, 2, ',', '.') }}</span></div>
                            @endif
                            @if ($order->coupon)
                                <div class="d-flex justify-content-between small text-primary">
                                    <span>Cupón aplicado</span><span>{{ $order->coupon->name }} ({{ $order->coupon->code }})</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between fw-semibold">
                                <span>Total</span><span>${{ number_format($order->total_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        @php
                            $loyaltyAward = \App\Models\UserLoyaltyMovement::where('order_id', $order->id)->where('reason', 'order_confirmed')->first();
                        @endphp
                        @if ($loyaltyAward)
                            <div class="mt-3 p-2 rounded border-start border-3 border-success bg-success bg-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-envelope-open-text text-success fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-success small">Figuritas repartidas</div>
                                        <div class="small text-dark">
                                            Se otorgaron <strong>{{ $loyaltyAward->delta }}</strong> figurita{{ $loyaltyAward->delta > 1 ? 's' : '' }}
                                            @if ($loyaltyAward->created_at)
                                                <span class="text-muted">· {{ $loyaltyAward->created_at->format('d/m/Y H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @php
                                $logEntry = null;
                                foreach ($order->change_log ?: [] as $entry) {
                                    if (($entry['action'] ?? null) === 'loyalty_awarded') {
                                        $logEntry = $entry;
                                        break;
                                    }
                                }
                            @endphp
                            @if ($logEntry)
                                <div class="mt-3 p-2 rounded border-start border-3 border-success bg-success bg-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-envelope-open-text text-success fs-5"></i>
                                        <div>
                                            <div class="fw-bold text-success small">Figuritas repartidas</div>
                                            <div class="small text-dark">
                                                @if (!empty($logEntry['stickers_count']))
                                                    Se otorgaron <strong>{{ $logEntry['stickers_count'] }}</strong> figurita{{ $logEntry['stickers_count'] > 1 ? 's' : '' }}
                                                @else
                                                    {{ $logEntry['message'] ?? 'Figuritas repartidas manualmente' }}
                                                @endif
                                                @if (!empty($logEntry['timestamp']))
                                                    <span class="text-muted">· {{ \Carbon\Carbon::parse($logEntry['timestamp'])->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                            <div class="mt-3">
                                <h6 class="mb-2">Notas</h6>
                            <textarea class="form-control form-control-sm order-edit" data-field="notes" rows="2" placeholder="Notas para el local">{{ $order->notes }}</textarea>
                            </div>

                        <div class="mt-3">
                            <h6 class="mb-2">Microturno</h6>
                            @php
                                $fecha = $order->created_at->format('Y-m-d');
                                $mts = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
                                $label = '';
                                if($order->microturno_sort_order){
                                    // Buscar el microturno usando foreach
                                    $m = null;
                                    foreach ($mts as $microturno) {
                                        if ($microturno->getSortOrderAttribute() == $order->microturno_sort_order) {
                                            $m = $microturno;
                                            break;
                                        }
                                    }
                                    $label = $m ? $m->getFormattedTimeAttribute() : '';
                                }
                                if(!$label && $order->microturno){ $label = $order->microturno->getFormattedTimeAttribute(); }
                            @endphp
                            <div class="form-control form-control-sm bg-light">{{ $label ?: 'Sin horario' }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="saveOrderDetails({{ $order->id }}, this)"><i class="fas fa-save me-2"></i>Guardar</button>
                        <button type="button" class="btn btn-outline-primary" onclick="printOrder({{ $order->id }})"><i
                                class="fas fa-print me-2"></i>Imprimir</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modales: Ver pedido - Pedidos Históricos -->
    @foreach ($pedidosHistorico as $order)
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
                                <div class="input-group input-group-sm mb-1">
                                    <span class="input-group-text">Nombre</span>
                                    <input type="text" class="form-control order-edit" data-field="contact_name" value="{{ $order->contact_name ?: ($order->user->name ?? '') }}">
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Tel</span>
                                    <input type="text" class="form-control order-edit" data-field="contact_phone" value="{{ $order->contact_phone ?: ($order->user->phone ?? '') }}">
                                    @php
                                        $modalPhoneHist = $order->contact_phone ?: ($order->user->phone ?? '');
                                        $modalCleanPhoneHist = preg_replace('/[^0-9]/', '', $modalPhoneHist);
                                        if (strlen($modalCleanPhoneHist) === 10 && substr($modalCleanPhoneHist, 0, 2) !== '54') {
                                            $modalCleanPhoneHist = '54' . $modalCleanPhoneHist;
                                        }
                                        $modalNameHist = $order->contact_name ?: ($order->user->name ?? 'Cliente');
                                        $modalWhatsappLinkHist = "https://wa.me/{$modalCleanPhoneHist}?text=" . urlencode("Hola {$modalNameHist}, soy de T Cocina. Te escribo respecto a tu pedido #{$order->order_number}.");
                                    @endphp
                                    @if($modalPhoneHist && $modalCleanPhoneHist)
                                        <a href="{{ $modalWhatsappLinkHist }}" target="_blank" class="input-group-text text-success" style="text-decoration: none;" title="Abrir chat WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                </div>
                                @if($order->user && $order->user->email)
                                <div class="input-group input-group-sm mt-1">
                                    <span class="input-group-text">Email</span>
                                    <input type="text" class="form-control" value="{{ $order->user->email }}" readonly>
                                    @if($order->user->google_id)
                                        <span class="input-group-text" title="Login con Google">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z"/></svg>
                                        </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Detalles</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small m-0">Estado</label>
                                        <select class="form-select form-select-sm order-edit" data-field="status">
                                            <option value="pending" {{ $order->status==='pending'?'selected':'' }}>Pendiente</option>
                                            <option value="confirmed" {{ $order->status==='confirmed'?'selected':'' }}>Confirmado</option>
                                            <option value="preparing" {{ $order->status==='preparing'?'selected':'' }}>En preparación</option>
                                            <option value="ready" {{ $order->status==='ready'?'selected':'' }}>Listo ✓</option>
                                            @if($order->address_id !== null)
                                            <option value="on_the_way" {{ $order->status==='on_the_way'?'selected':'' }}>En camino 🛵</option>
                                            @endif
                                            <option value="delivered" {{ $order->status==='delivered'?'selected':'' }}>Entregado</option>
                                        </select>
                                </div>
                                    <div class="col-6">
                                        <label class="form-label small m-0">Forma de pago</label>
                                        <select class="form-select form-select-sm order-edit" data-field="payment_method">
                                            <option value="cash" {{ $order->payment_method==='cash'?'selected':'' }}>Efectivo</option>
                                            <option value="card" {{ $order->payment_method==='card'?'selected':'' }}>Tarjeta</option>
                                            <option value="transfer" {{ $order->payment_method==='transfer'?'selected':'' }}>Transferencia</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small m-0">Estado de pago</label>
                                        <select class="form-select form-select-sm order-edit" data-field="payment_status">
                                            <option value="pending" {{ $order->payment_status==='pending'?'selected':'' }}>Pendiente</option>
                                            <option value="paid" {{ $order->payment_status==='paid'?'selected':'' }}>Pagado</option>
                                            <option value="failed" {{ $order->payment_status==='failed'?'selected':'' }}>Fallido</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small m-0">Fecha</label>
                                        <div class="form-control form-control-sm bg-light">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($order->address)
                            <div class="mt-3">
                                <h6 class="mb-2">Entrega</h6>
                                <div class="row g-2">
                                    <div class="col-8">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Calle</span>
                                            <input type="text" class="form-control order-edit" data-field="address.street" value="{{ $order->address->street }}">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Número</span>
                                            <input type="text" class="form-control order-edit" data-field="address.number" value="{{ $order->address->number }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Notas entrega</span>
                                            <input type="text" class="form-control order-edit" data-field="address.reference" value="{{ $order->address->reference }}">
                                        </div>
                                    </div>
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
                                                    @if ($item->configuration_text)
                                                        {{ $item->configuration_text }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $item->quantity }}</td>
                                                <td class="text-end">${{ number_format($item->total_price, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h6 class="mb-2">Totales</h6>
                            <div class="d-flex justify-content-between small">
                                <span>Subtotal</span><span>${{ number_format($order->subtotal, 2, ',', '.') }}</span>
                            </div>
                            @if ($order->delivery_fee > 0)
                                <div class="d-flex justify-content-between small">
                                    <span>Envío</span><span>${{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($order->discount_amount > 0)
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Descuento</span><span>-
                                        ${{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($order->coupon)
                                <div class="d-flex justify-content-between small text-primary">
                                    <span>Cupón aplicado</span><span>{{ $order->coupon->name }} ({{ $order->coupon->code }})</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between fw-semibold">
                                <span>Total</span><span>${{ number_format($order->total_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        @php
                            $loyaltyAward = \App\Models\UserLoyaltyMovement::where('order_id', $order->id)->where('reason', 'order_confirmed')->first();
                        @endphp
                        @if ($loyaltyAward)
                            <div class="mt-3 p-2 rounded border-start border-3 border-success bg-success bg-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-envelope-open-text text-success fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-success small">Figuritas repartidas</div>
                                        <div class="small text-dark">
                                            Se otorgaron <strong>{{ $loyaltyAward->delta }}</strong> figurita{{ $loyaltyAward->delta > 1 ? 's' : '' }}
                                            @if ($loyaltyAward->created_at)
                                                <span class="text-muted">· {{ $loyaltyAward->created_at->format('d/m/Y H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @php
                                $logEntry = null;
                                foreach ($order->change_log ?: [] as $entry) {
                                    if (($entry['action'] ?? null) === 'loyalty_awarded') {
                                        $logEntry = $entry;
                                        break;
                                    }
                                }
                            @endphp
                            @if ($logEntry)
                                <div class="mt-3 p-2 rounded border-start border-3 border-success bg-success bg-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-envelope-open-text text-success fs-5"></i>
                                        <div>
                                            <div class="fw-bold text-success small">Figuritas repartidas</div>
                                            <div class="small text-dark">
                                                @if (!empty($logEntry['stickers_count']))
                                                    Se otorgaron <strong>{{ $logEntry['stickers_count'] }}</strong> figurita{{ $logEntry['stickers_count'] > 1 ? 's' : '' }}
                                                @else
                                                    {{ $logEntry['message'] ?? 'Figuritas repartidas manualmente' }}
                                                @endif
                                                @if (!empty($logEntry['timestamp']))
                                                    <span class="text-muted">· {{ \Carbon\Carbon::parse($logEntry['timestamp'])->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                            <div class="mt-3">
                                <h6 class="mb-2">Notas</h6>
                            <textarea class="form-control form-control-sm order-edit" data-field="notes" rows="2" placeholder="Notas para el local">{{ $order->notes }}</textarea>
                            </div>

                        <div class="mt-3">
                            <h6 class="mb-2">Microturno</h6>
                            @php
                                $fecha = $order->created_at->format('Y-m-d');
                                $mts = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
                                $label = '';
                                if($order->microturno_sort_order){
                                    // Buscar el microturno usando foreach
                                    $m = null;
                                    foreach ($mts as $microturno) {
                                        if ($microturno->getSortOrderAttribute() == $order->microturno_sort_order) {
                                            $m = $microturno;
                                            break;
                                        }
                                    }
                                    $label = $m ? $m->getFormattedTimeAttribute() : '';
                                }
                                if(!$label && $order->microturno){ $label = $order->microturno->getFormattedTimeAttribute(); }
                            @endphp
                            <div class="form-control form-control-sm bg-light">{{ $label ?: 'Sin horario' }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="saveOrderDetails({{ $order->id }}, this)"><i class="fas fa-save me-2"></i>Guardar</button>
                        <button type="button" class="btn btn-outline-primary"
                            onclick="printOrder({{ $order->id }})"><i class="fas fa-print me-2"></i>Imprimir</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Pedidos del día seleccionado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white modern-header modern-header--compact">
                    <div class="modern-header__row">
                        <!-- Izquierda: título + fecha -->
                        <div class="modern-header__left">
                            <div class="header-icon-container">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="modern-header__titles">
                                @php
                                    $labelFecha = isset($selectedDate) && $selectedDate
                                        ? \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM')
                                        : now()->locale('es')->isoFormat('dddd, D [de] MMMM');
                                @endphp
                                <h6 class="mb-0 modern-title">Pedidos del Día</h6>
                                <small class="opacity-75 modern-subtitle">{{ $labelFecha }}</small>
                            </div>
                        </div>

                        <!-- Centro: contadores de cocina inline -->
                        <div class="modern-header__counters" role="group" aria-label="Conteo de cocina del día">
                            <div class="kitchen-chip kitchen-chip--burger" title="Hamburguesas confirmadas (sin cancelados)">
                                <i class="fas fa-hamburger kitchen-chip__icon"></i>
                                <span class="kitchen-chip__value">{{ $totalHamburguesasFecha }}</span>
                                <span class="kitchen-chip__label">Hamb.</span>
                            </div>
                            <div class="kitchen-chip kitchen-chip--side" title="Acompañamientos">
                                <i class="fas fa-drumstick-bite kitchen-chip__icon"></i>
                                <span class="kitchen-chip__value">{{ $totalAcompFecha }}</span>
                                <span class="kitchen-chip__label">Acomp.</span>
                            </div>
                            <div class="kitchen-chip kitchen-chip--other {{ $totalOtrosFecha === 0 ? 'kitchen-chip--muted' : '' }}" title="Bebidas, postres y otros">
                                <i class="fas fa-glass-water kitchen-chip__icon"></i>
                                <span class="kitchen-chip__value">{{ $totalOtrosFecha }}</span>
                                <span class="kitchen-chip__label">Otros</span>
                            </div>
                        </div>

                        <!-- Derecha: refresh + última actualización (oculto, hover) -->
                        <div class="modern-header__right">
                            <div class="last-update-info" aria-hidden="true">
                                <small>
                                    <i class="fas fa-clock me-1"></i>
                                    <span id="last-update-text">Hace <span id="update-time">0</span>s</span>
                                </small>
                            </div>
                            <button type="button" class="btn btn-refresh" onclick="refreshOrders()" title="Actualizar pedidos" aria-label="Actualizar pedidos">
                                <i class="fas fa-sync-alt refresh-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($pedidosFecha->count() > 0)
                        <div class="table-responsive">
                            <table id="orders-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <div class="form-check">
                                                <input type="checkbox" id="select-all-hoy" class="form-check-input">
                                                <label for="select-all-hoy" class="form-check-label visually-hidden">Seleccionar todos</label>
                                            </div>
                                        </th>
                                        <th>Cliente</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th class="pago-filter-th">
                                            <div class="dropdown">
                                                <span role="button" class="pago-filter-toggle text-nowrap" data-bs-toggle="dropdown"
                                                      data-bs-boundary="window" data-bs-strategy="fixed" data-table="orders-table"
                                                      title="Filtrar por método de pago">Pago <i class="fas fa-caret-down"></i></span>
                                                <ul class="dropdown-menu pago-filter-menu" data-table="orders-table">
                                                    <li><a class="dropdown-item pago-filter-option d-flex justify-content-between gap-3" href="#" data-method="">Todos <span class="pago-total text-muted small"></span></a></li>
                                                    <li><a class="dropdown-item pago-filter-option d-flex justify-content-between gap-3" href="#" data-method="cash">Efectivo <span class="pago-total text-muted small"></span></a></li>
                                                    <li><a class="dropdown-item pago-filter-option d-flex justify-content-between gap-3" href="#" data-method="transfer">Transferencia <span class="pago-total text-muted small"></span></a></li>
                                                </ul>
                                            </div>
                                        </th>
                                        <th>Horario</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-tbody-hoy">
                                    @foreach ($pedidosFecha as $order)
                                        <tr class="{{ $order->status === 'delivered' ? 'order-row-delivered' : '' }}">
                                            <td>
                                                <div class="form-check">
                                                <input type="checkbox" class="form-check-input order-checkbox"
                                                           value="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                                    <label class="form-check-label visually-hidden">Seleccionar pedido</label>
                                                </div>
                                            </td>
                                            <td data-label="Cliente">
                                                <div class="d-flex align-items-center">
                                                    <div class="modern-profile-icon-container">
                                                        <i class="fas fa-user-tie"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold d-flex align-items-center gap-1">
                                                            {{ $order->user && $order->user->name !== 'Invitado' ? $order->user->name : ($order->contact_name ?: 'Invitado') }}
                                                            @if($order->user && $order->user->google_id)
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 18 18" style="flex-shrink:0;vertical-align:middle" title="Login con Google"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z"/></svg>
                                                            @endif
                                                            @if($order->reviews->isNotEmpty())
                                                            <i class="fas fa-star text-warning" style="font-size: 0.75rem;" title="Dejó reseña"></i>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <small class="text-muted">{{ $order->user && $order->user->phone ? $order->user->phone : ($order->contact_phone ?: 'Sin teléfono') }}</small>
                                                            @php
                                                                $todayPhone = $order->user && $order->user->phone ? $order->user->phone : $order->contact_phone;
                                                                $todayCleanPhone = preg_replace('/[^0-9]/', '', $todayPhone);
                                                                if (strlen($todayCleanPhone) === 10 && substr($todayCleanPhone, 0, 2) !== '54') {
                                                                    $todayCleanPhone = '54' . $todayCleanPhone;
                                                                }
                                                            @endphp
                                                            @if($todayPhone && $todayCleanPhone)
                                                                <a href="https://wa.me/{{ $todayCleanPhone }}" target="_blank" class="text-success" style="font-size: 0.85rem;" title="Abrir chat WhatsApp">
                                                                    <i class="fab fa-whatsapp"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Items">
                                                @php
                                                    $hamburguesas = 0;
                                                    $acompañamientos = 0;
                                                    foreach ($order->items as $item) {
                                                        if (
                                                            strtolower($item->product->category->name) ===
                                                            'hamburguesas'
                                                        ) {
                                                            $hamburguesas += $item->quantity;
                                                        } elseif (
                                                            strtolower($item->product->category->name) ===
                                                            'acompañamientos'
                                                        ) {
                                                            $acompañamientos += $item->quantity;
                                                        }
                                                    }
                                                @endphp
                                                @if ($hamburguesas > 0)
                                                    <span class="badge bg-primary">{{ $hamburguesas }} hamb.</span>
                                                @endif
                                                @if ($acompañamientos > 0)
                                                    <span class="badge bg-success">{{ $acompañamientos }} acomp.</span>
                                                @endif
                                                @if ($hamburguesas === 0 && $acompañamientos === 0)
                                                    <span class="badge bg-secondary">{{ $order->items->sum('quantity') }}
                                                        items</span>
                                                @endif
                                            </td>
                                            <td data-label="Total">
                                                <div>
                                                    <div class="fw-bold text-success modern-total-font">${{ number_format($order->total_amount, 2, ',', '.') }}</div>
                                                @if ($order->discount_amount > 0)
                                                        <small class="text-danger modern-discount-font">-{{ number_format($order->discount_amount, 2, ',', '.') }} desc.</small>
                                                @endif
                                                @if ($order->coupon)
                                                    <small class="text-primary d-block modern-discount-font">{{ $order->coupon->name }}</small>
                                                @endif
                                                </div>
                                            </td>
                                            <td data-label="Pago">
                                                @php $pmOrder = $order->payment_method ?: 'cash'; @endphp
                                                <span class="badge payment-toggle {{ $pmOrder === 'transfer' ? 'bg-info text-dark' : ($pmOrder === 'card' ? 'bg-secondary' : 'bg-success') }}"
                                                    data-order-id="{{ $order->id }}" data-method="{{ $pmOrder }}" data-amount="{{ $order->total_amount }}"
                                                    role="button" style="cursor: pointer;"
                                                    title="{{ $pmOrder === 'card' ? 'Tarjeta: se edita desde el detalle del pedido' : 'Click para alternar Efectivo/Transferencia' }}">
                                                    {{ $order->payment_method_label }}
                                                </span>
                                            </td>
                                            <td data-label="Horario" class="position-relative" style="min-width:130px;">
                                                @if ($order->status === 'pending')
                                                    <select class="form-select form-select-sm microturno-select"
                                                        data-order-id="{{ $order->id }}">
                                                        @foreach ($microturnosHoy as $microturno)
                                                            <option value="{{ $microturno->getSortOrderAttribute() }}"
                                                                {{ $order->microturno_sort_order == $microturno->getSortOrderAttribute() ? 'selected' : '' }}>
                                                                {{ $microturno->getFormattedTimeAttribute() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    @php
                                                        $horarioAsignado = null;
                                                        if ($order->microturno_sort_order) {
                                                            $microturnoAsignado = null;
                                                            foreach ($microturnosHoy as $microturno) {
                                                                if ($microturno->getSortOrderAttribute() == $order->microturno_sort_order) {
                                                                    $microturnoAsignado = $microturno;
                                                                    break;
                                                                }
                                                            }
                                                            if ($microturnoAsignado) {
                                                                $horarioAsignado = $microturnoAsignado->getFormattedTimeAttribute();
                                                            }
                                                        }
                                                    @endphp
                                                    @if ($horarioAsignado)
                                                        <span class="badge badge-schedule-professional">
                                                            {{ $horarioAsignado }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Sin horario</span>
                                                    @endif
                                                @endif

                                                @if ($order->status === 'delivered' && $order->user_id && $order->user && $order->user->email !== 'guest@tecocina.local' && !orderHasLoyaltyAwarded($order, $alreadyAwardedIds))
                                                    <div class="loyalty-blur-overlay">
                                                        <button type="button" class="btn btn-loyalty-green"
                                                            onclick="openAwardLoyaltyModal({{ $order->id }})"
                                                            title="Repartir figuritas al usuario">
                                                            <i class="fas fa-envelope-open-text"></i>
                                                            <span>Repartir figuritas</span>
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="Estado">
                                                <select class="form-select form-select-sm status-select"
                                                    data-order-id="{{ $order->id }}">
                                                    <option value="pending"
                                                        {{ $order->status === 'pending' ? 'selected' : '' }}>Pendiente
                                                    </option>
                                                    <option value="confirmed"
                                                        {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado
                                                    </option>
                                                    <option value="preparing"
                                                        {{ $order->status === 'preparing' ? 'selected' : '' }}>En
                                                        Preparación
                                                    </option>
                                                    <option value="ready"
                                                        {{ $order->status === 'ready' ? 'selected' : '' }}>Listo ✓
                                                    </option>
                                                    @if($order->address_id !== null)
                                                    <option value="on_the_way"
                                                        {{ $order->status === 'on_the_way' ? 'selected' : '' }}>En camino 🛵
                                                    </option>
                                                    @endif
                                                    <option value="delivered"
                                                        {{ $order->status === 'delivered' ? 'selected' : '' }}>Entregado
                                                    </option>
                                                </select>
                                            </td>
                                            <td data-label="Acciones">
                                                <div class="dropdown text-end">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-boundary="window" data-bs-strategy="fixed">
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
                                                            @php
                                                                $phone = $order->user && $order->user->phone ? $order->user->phone : $order->contact_phone;
                                                                $name = $order->user && $order->user->name !== 'Invitado' ? $order->user->name : ($order->contact_name ?: 'Cliente');
                                                                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                                                                if (strlen($cleanPhone) === 10 && substr($cleanPhone, 0, 2) !== '54') {
                                                                    $cleanPhone = '54' . $cleanPhone;
                                                                }
                                                                $whatsappLink = "https://wa.me/{$cleanPhone}?text=" . urlencode("Hola {$name}, soy de T Cocina. Te escribo respecto a tu pedido #{$order->order_number}.");
                                                            @endphp
                                                            @if($phone && $cleanPhone)
                                                                <a href="{{ $whatsappLink }}" target="_blank" class="dropdown-item text-success">
                                                                    <i class="fab fa-whatsapp me-2"></i>Enviar WhatsApp
                                                                </a>
                                                            @endif
                                                        </li>
                                                        <li>
                                                            @php
                                                                $reviewMsg = urlencode("¡Hola {$name}! ¿Te gustaron nuestras hamburguesas? Si tenés un minuto, nos ayudaría mucho tu opinión en Google: https://g.page/r/CepJ7XpQQOkyEBM/review");
                                                                $reviewWhatsappLink = "https://wa.me/{$cleanPhone}?text={$reviewMsg}";
                                                            @endphp
                                                            @if($phone && $cleanPhone)
                                                                <a href="{{ $reviewWhatsappLink }}" target="_blank" class="dropdown-item text-warning"
                                                                   onclick="markReviewPrompted({{ $order->id }})" id="review-btn-{{ $order->id }}">
                                                                    <i class="fas fa-star me-2"></i>Solicitar reseña
                                                                </a>
                                                            @endif
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
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-day fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay pedidos hoy</h5>
                            <p class="text-muted">Aún no se han recibido pedidos en el día de hoy.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pedidos Históricos -->
    <div class="row">
        <div class="col-12">
            <div class="accordion" id="accordionPedidos">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingPedidos">
                        <button class="accordion-button collapsed accordion-button--with-counters" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapsePedidos" aria-expanded="false" aria-controls="collapsePedidos">
                            <span class="accordion-title-group">
                                <i class="fas fa-history me-2"></i>
                                <span>Pedidos del día anterior</span>
                                <span class="badge bg-secondary ms-2">{{ $pedidosHistorico->count() }}</span>
                            </span>
                            <!-- Contadores dinámicos sobre la barra celeste -->
                            <span class="historico-counters-inline" id="historico-counters-inline">
                                <span class="historico-chip-sm historico-chip-sm--burger" id="historico-chip-burger">
                                    <i class="fas fa-hamburger"></i>
                                    <span id="historico-hamb-count">0</span>
                                </span>
                                <span class="historico-chip-sm historico-chip-sm--side" id="historico-chip-side">
                                    <i class="fas fa-drumstick-bite"></i>
                                    <span id="historico-side-count">0</span>
                                </span>
                                <span class="historico-chip-sm historico-chip-sm--other" id="historico-chip-other">
                                    <i class="fas fa-glass-water"></i>
                                    <span id="historico-other-count">0</span>
                                </span>
                            </span>
                        </button>
                    </h2>
                    <div id="collapsePedidos" class="accordion-collapse collapse" aria-labelledby="headingPedidos"
                        data-bs-parent="#accordionPedidos">
                        <div class="accordion-body p-0">
                            @if ($pedidosHistorico->count() > 0)
                                <div class="table-responsive">
                                    <table id="orders-table-historico" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <div class="form-check">
                                                        <input type="checkbox" id="select-all-historico" class="form-check-input">
                                                        <label for="select-all-historico" class="form-check-label visually-hidden">Seleccionar todos</label>
                                                    </div>
                                                </th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Items</th>
                                                <th>Total</th>
                                                <th class="pago-filter-th">
                                                    <div class="dropdown">
                                                        <span role="button" class="pago-filter-toggle text-nowrap" data-bs-toggle="dropdown"
                                                              data-bs-boundary="window" data-bs-strategy="fixed" data-table="orders-table-historico"
                                                              title="Filtrar por método de pago">Pago <i class="fas fa-caret-down"></i></span>
                                                        <ul class="dropdown-menu pago-filter-menu" data-table="orders-table-historico">
                                                            <li><a class="dropdown-item pago-filter-option d-flex justify-content-between gap-3" href="#" data-method="">Todos <span class="pago-total text-muted small"></span></a></li>
                                                            <li><a class="dropdown-item pago-filter-option d-flex justify-content-between gap-3" href="#" data-method="cash">Efectivo <span class="pago-total text-muted small"></span></a></li>
                                                            <li><a class="dropdown-item pago-filter-option d-flex justify-content-between gap-3" href="#" data-method="transfer">Transferencia <span class="pago-total text-muted small"></span></a></li>
                                                        </ul>
                                                    </div>
                                                </th>
                                                <th>Horario</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="orders-tbody-historico">
                                            @foreach ($pedidosHistorico as $order)
                                                @php
                                                    $hambCount = 0;
                                                    $sideCount = 0;
                                                    $otherCount = 0;
                                                    foreach ($order->items as $item) {
                                                        $catName = strtolower($item->product->category->name ?? '');
                                                        if ($catName === 'hamburguesas') {
                                                            $hambCount += $item->quantity;
                                                        } elseif ($catName === 'acompañamientos') {
                                                            $sideCount += $item->quantity;
                                                        } else {
                                                            $otherCount += $item->quantity;
                                                        }
                                                    }
                                                @endphp
                                                <tr class="{{ $order->status === 'delivered' ? 'order-row-delivered' : '' }}" data-hamb="{{ $hambCount }}" data-side="{{ $sideCount }}" data-other="{{ $otherCount }}">
                                                    <td>
                                                        <div class="form-check">
                                                        <input type="checkbox" class="form-check-input order-checkbox" 
                                                                   value="{{ $order->id }}" data-order-number="{{ $order->order_number }}">
                                                            <label class="form-check-label visually-hidden">Seleccionar pedido</label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small>{{ $order->created_at->format('d/m/Y') }}</small>
                                                        <br><small
                                                            class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="modern-profile-icon-container">
                                                                <i class="fas fa-user-tie"></i>
                                                            </div>
                                                                <div>
                                                                    <div class="fw-semibold d-flex align-items-center gap-1">
                                                                        {{ $order->user && $order->user->name !== 'Invitado' ? $order->user->name : ($order->contact_name ?: 'Invitado') }}
                                                                        @if($order->user && $order->user->google_id)
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 18 18" style="flex-shrink:0;vertical-align:middle" title="Login con Google"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z"/></svg>
                                                                        @endif
                                                                        @if($order->reviews->isNotEmpty())
                                                                        <i class="fas fa-star text-warning" style="font-size: 0.75rem;" title="Dejó reseña"></i>
                                                                        @endif
                                                                    </div>
                                                                    <div class="d-flex align-items-center gap-1">
                                                                        <small class="text-muted">{{ $order->user && $order->user->phone ? $order->user->phone : ($order->contact_phone ?: 'Sin teléfono') }}</small>
                                                                        @php
                                                                            $histPhone = $order->user && $order->user->phone ? $order->user->phone : $order->contact_phone;
                                                                            $histCleanPhone = preg_replace('/[^0-9]/', '', $histPhone);
                                                                            if (strlen($histCleanPhone) === 10 && substr($histCleanPhone, 0, 2) !== '54') {
                                                                                $histCleanPhone = '54' . $histCleanPhone;
                                                                            }
                                                                        @endphp
                                                                        @if($histPhone && $histCleanPhone)
                                                                            <a href="https://wa.me/{{ $histCleanPhone }}" target="_blank" class="text-success" style="font-size: 0.85rem;" title="Abrir chat WhatsApp">
                                                                                <i class="fab fa-whatsapp"></i>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $hamburguesas = 0;
                                                            $acompañamientos = 0;
                                                            foreach ($order->items as $item) {
                                                                if (
                                                                    strtolower($item->product->category->name) ===
                                                                    'hamburguesas'
                                                                ) {
                                                                    $hamburguesas += $item->quantity;
                                                                } elseif (
                                                                    strtolower($item->product->category->name) ===
                                                                    'acompañamientos'
                                                                ) {
                                                                    $acompañamientos += $item->quantity;
                                                                }
                                                            }
                                                        @endphp
                                                        @if ($hamburguesas > 0)
                                                            <span class="badge bg-primary">{{ $hamburguesas }}
                                                                hamb.</span>
                                                        @endif
                                                        @if ($acompañamientos > 0)
                                                            <span class="badge bg-success">{{ $acompañamientos }}
                                                                acomp.</span>
                                                        @endif
                                                        @if ($hamburguesas === 0 && $acompañamientos === 0)
                                                            <span
                                                                class="badge bg-secondary">{{ $order->items->sum('quantity') }}
                                                                items</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="fw-bold text-success modern-total-font">${{ number_format($order->total_amount, 2, ',', '.') }}</div>
                                                        @if ($order->discount_amount > 0)
                                                                <small class="text-danger modern-discount-font">-{{ number_format($order->discount_amount, 2, ',', '.') }} desc.</small>
                                                        @endif
                                                        @if ($order->coupon)
                                                            <small class="text-primary d-block modern-discount-font">{{ $order->coupon->name }}</small>
                                                        @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @php $pmOrderHist = $order->payment_method ?: 'cash'; @endphp
                                                        <span class="badge payment-toggle {{ $pmOrderHist === 'transfer' ? 'bg-info text-dark' : ($pmOrderHist === 'card' ? 'bg-secondary' : 'bg-success') }}"
                                                            data-order-id="{{ $order->id }}" data-method="{{ $pmOrderHist }}" data-amount="{{ $order->total_amount }}"
                                                            role="button" style="cursor: pointer;"
                                                            title="{{ $pmOrderHist === 'card' ? 'Tarjeta: se edita desde el detalle del pedido' : 'Click para alternar Efectivo/Transferencia' }}">
                                                            {{ $order->payment_method_label }}
                                                        </span>
                                                    </td>
                                                    <td class="position-relative" style="min-width:130px;">
                                                        @php
                                                            $fecha = $order->created_at->format('Y-m-d');
                                                            $mts = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
                                                            $horarioAsignado = null;
                                                            if ($order->microturno_sort_order) {
                                                                $microturnoAsignado = null;
                                                                foreach ($mts as $microturno) {
                                                                    if ($microturno->getSortOrderAttribute() == $order->microturno_sort_order) {
                                                                        $microturnoAsignado = $microturno;
                                                                        break;
                                                                    }
                                                                }
                                                                if ($microturnoAsignado) {
                                                                    $horarioAsignado = $microturnoAsignado->getFormattedTimeAttribute();
                                                                }
                                                            }
                                                        @endphp
                                                        @if ($horarioAsignado)
                                                            <span class="badge badge-schedule-professional">
                                                                {{ $horarioAsignado }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">Sin horario</span>
                                                        @endif

                                                        @if ($order->status === 'delivered' && $order->user_id && $order->user && $order->user->email !== 'guest@tecocina.local' && !orderHasLoyaltyAwarded($order, $alreadyAwardedIds))
                                                            <div class="loyalty-blur-overlay">
                                                                <button type="button" class="btn btn-loyalty-green"
                                                                    onclick="openAwardLoyaltyModal({{ $order->id }})"
                                                                    title="Repartir figuritas al usuario">
                                                                    <i class="fas fa-envelope-open-text"></i>
                                                                    <span>Repartir figuritas</span>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'ready' ? 'ready' : ($order->status === 'preparing' ? 'info' : ($order->status === 'confirmed' ? 'warning' : 'secondary'))) }}">
                                                            {{ $order->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown text-end">
                                                            <button
                                                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                type="button" data-bs-toggle="dropdown"
                                                                data-bs-boundary="window" data-bs-strategy="fixed">
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
                                                                    @php
                                                                        $histDropdownPhone = $order->user && $order->user->phone ? $order->user->phone : $order->contact_phone;
                                                                        $histDropdownName = $order->user && $order->user->name !== 'Invitado' ? $order->user->name : ($order->contact_name ?: 'Cliente');
                                                                        $histDropdownCleanPhone = preg_replace('/[^0-9]/', '', $histDropdownPhone);
                                                                        if (strlen($histDropdownCleanPhone) === 10 && substr($histDropdownCleanPhone, 0, 2) !== '54') {
                                                                            $histDropdownCleanPhone = '54' . $histDropdownCleanPhone;
                                                                        }
                                                                        $histWhatsappLink = "https://wa.me/{$histDropdownCleanPhone}?text=" . urlencode("Hola {$histDropdownName}, soy de T Cocina. Te escribo respecto a tu pedido #{$order->order_number}.");
                                                                    @endphp
                                                                    @if($histDropdownPhone && $histDropdownCleanPhone)
                                                                        <a href="{{ $histWhatsappLink }}" target="_blank" class="dropdown-item text-success">
                                                                            <i class="fab fa-whatsapp me-2"></i>Enviar WhatsApp
                                                                        </a>
                                                                    @endif
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
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No hay pedidos anteriores</h5>
                                    <p class="text-muted">No hay pedidos que coincidan con los filtros seleccionados.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales: Eliminar pedido - Día seleccionado -->
    @foreach ($pedidosFecha as $order)
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

    <!-- Modales: Eliminar pedido - Pedidos Históricos -->
    @foreach ($pedidosHistorico as $order)
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

    <!-- Modal de confirmación para impresión masiva -->
    <div class="modal fade" id="bulkPrintModal" tabindex="-1" aria-labelledby="bulkPrintModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="bulkPrintModalLabel">
                        <i class="fas fa-print me-2"></i>Confirmar Impresión Masiva
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Información importante:</strong> Se abrirá una nueva pestaña por cada pedido seleccionado.
                    </div>
                    <p class="mb-3">¿Deseas imprimir los siguientes pedidos?</p>
                    <div class="list-group" id="orders-to-print-list">
                        <!-- Se llenará dinámicamente -->
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Nota:</strong> Asegúrate de que tu navegador permita ventanas emergentes para este sitio.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="confirm-bulk-print">
                        <i class="fas fa-print me-1"></i>Imprimir Pedidos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminación masiva -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación Masiva
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-warning me-2"></i>
                        <strong>¡Advertencia!</strong> Esta acción no se puede deshacer.
                    </div>
                    <p class="mb-3">¿Estás seguro de que deseas eliminar los siguientes pedidos?</p>
                    <div class="list-group" id="orders-to-delete-list">
                        <!-- Se llenará dinámicamente -->
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Los pedidos y todos sus datos asociados serán eliminados permanentemente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirm-bulk-delete">
                        <i class="fas fa-trash me-1"></i>Eliminar Pedidos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Repartir Figuritas -->
    <div class="modal fade" id="awardLoyaltyModal" tabindex="-1" aria-labelledby="awardLoyaltyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="awardLoyaltyModalLabel">
                        <i class="fas fa-envelope-open-text me-2"></i>Repartir Figuritas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                            <i class="fas fa-envelope-open-text fa-2x text-success"></i>
                        </div>
                    </div>
                    <h5 class="fw-semibold mb-2">¿Confirmar repartir figuritas?</h5>
                    <p class="text-muted mb-3">Esta acción otorgará las figuritas correspondientes al usuario de este pedido.</p>
                    <div class="alert alert-warning border-0 mb-0">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                        <strong>Atención:</strong> Una vez repartidas, esta acción <span class="text-danger fw-bold">no puede deshacerse</span>.
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-success px-4 fw-bold" id="confirm-award-loyalty">
                        <i class="fas fa-envelope-open-text me-1"></i>Sí, repartir figuritas
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('selected_date');
            if (input && window.flatpickr) {
                flatpickr(input, {
                    dateFormat: 'Y-m-d',     // valor que se envía al servidor
                    altInput: true,          // input visible para el usuario
                    altFormat: 'd/m/Y',      // formato visible: DIA/MES/AÑO
                    defaultDate: input.value || '{{ now()->format('Y-m-d') }}',
                    allowInput: true,
                    locale: {
                        firstDayOfWeek: 1
                    },
                    onChange: function () {
                        // Enviar automáticamente al cambiar
                        const form = document.getElementById('orders-date-form');
                        if (form) form.submit();
                    }
                });
            }
        });

        function toggleDailyTotal() {
            const amountElement = document.getElementById('daily-total-amount');

            if (amountElement.dataset.originalAmount) {
                // Show original amount
                amountElement.textContent = amountElement.dataset.originalAmount;
                delete amountElement.dataset.originalAmount;
                // Guardar estado en localStorage
                localStorage.setItem('dailyTotalHidden', 'false');
            } else {
                // Hide amount
                amountElement.dataset.originalAmount = amountElement.textContent;
                amountElement.textContent = '••••••';
                // Guardar estado en localStorage
                localStorage.setItem('dailyTotalHidden', 'true');
            }
        }
    </script>
    <style>
        /* CHECKBOXES MODERNOS Y PROFESIONALES */
        .form-check-input {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            width: 20px !important;
            height: 20px !important;
            border: 2px solid #d1d5db !important;
            border-radius: 6px !important;
            background: linear-gradient(145deg, #ffffff, #f8fafc) !important;
            cursor: pointer !important;
            position: relative !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 
                0 2px 4px rgba(0, 0, 0, 0.1),
                inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 1 !important;
        }
        
        .form-check-input:hover {
            border-color: #3b82f6 !important;
            transform: scale(1.1) !important;
            box-shadow: 
                0 4px 12px rgba(59, 130, 246, 0.3),
                0 0 0 4px rgba(59, 130, 246, 0.1),
                inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
            background: linear-gradient(145deg, #ffffff, #f0f9ff) !important;
        }
        
        .form-check-input:checked {
            background: linear-gradient(145deg, #3b82f6, #1d4ed8) !important;
            border-color: #3b82f6 !important;
            transform: scale(1.05) !important;
            box-shadow: 
                0 4px 12px rgba(59, 130, 246, 0.4),
                0 0 0 4px rgba(59, 130, 246, 0.2),
                inset 0 1px 2px rgba(255, 255, 255, 0.3) !important;
        }
        
        .form-check-input:checked::after {
            content: '' !important;
            position: absolute !important;
            left: 50% !important;
            top: 50% !important;
            width: 5px !important;
            height: 8px !important;
            border: 2px solid #ffffff !important;
            border-top: none !important;
            border-left: none !important;
            transform: translate(-50%, -60%) rotate(45deg) !important;
            animation: checkmark 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .form-check-input:indeterminate {
            background: linear-gradient(145deg, #f59e0b, #d97706) !important;
            border-color: #f59e0b !important;
            transform: scale(1.05) !important;
            box-shadow: 
                0 4px 12px rgba(245, 158, 11, 0.4),
                0 0 0 4px rgba(245, 158, 11, 0.2),
                inset 0 1px 2px rgba(255, 255, 255, 0.3) !important;
        }
        
        .form-check-input:indeterminate::after {
            content: '' !important;
            position: absolute !important;
            left: 50% !important;
            top: 50% !important;
            width: 8px !important;
            height: 2px !important;
            background: #ffffff !important;
            transform: translate(-50%, -50%) !important;
            border-radius: 1px !important;
            animation: indeterminate 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        /* Animaciones */
        @keyframes checkmark {
            0% {
                opacity: 0;
                transform: translate(-50%, -60%) rotate(45deg) scale(0.5);
            }
            50% {
                opacity: 1;
                transform: translate(-50%, -60%) rotate(45deg) scale(1.2);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -60%) rotate(45deg) scale(1);
            }
        }
        
        @keyframes indeterminate {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        
        /* Efecto de pulso al hacer clic */
        .form-check-input:active {
            transform: scale(0.95) !important;
            transition: transform 0.1s ease !important;
        }
        
        /* Asegurar que los checkboxes sean visibles */
        .form-check {
            display: block !important;
            visibility: visible !important;
        }
        
        .order-checkbox {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Efecto especial para el checkbox "Seleccionar todo" */
        #select-all-hoy, #select-all-historico {
            border: 2px solid #6366f1 !important;
            background: linear-gradient(145deg, #ffffff, #f8fafc) !important;
        }
        
        #select-all-hoy:hover, #select-all-historico:hover {
            border-color: #4f46e5 !important;
            box-shadow: 
                0 4px 12px rgba(79, 70, 229, 0.3),
                0 0 0 4px rgba(79, 70, 229, 0.1) !important;
        }
        
        #select-all-hoy:checked, #select-all-historico:checked {
            background: linear-gradient(145deg, #6366f1, #4f46e5) !important;
            border-color: #6366f1 !important;
        }
        
        /* Efectos adicionales para mayor atractivo */
        .form-check-input::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            border-radius: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .form-check-input:hover::before {
            opacity: 1;
        }
        
        /* Efecto de ondas al hacer clic */
        .form-check-input:active::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        }
        
        @keyframes ripple {
            0% {
                width: 0;
                height: 0;
                opacity: 1;
            }
            100% {
                width: 40px;
                height: 40px;
                opacity: 0;
            }
        }
        
        /* Mejora para el estado focus */
        .form-check-input:focus {
            outline: none !important;
            box-shadow: 
                0 4px 12px rgba(59, 130, 246, 0.4),
                0 0 0 4px rgba(59, 130, 246, 0.2),
                inset 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }
        
        /* Efecto de brillo sutil */
        .form-check-input:checked::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            right: 2px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            border-radius: 1px;
            animation: shine 2s ease-in-out infinite;
        }
        
        @keyframes shine {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }
        
        /* Animación para la sección de acciones masivas */
        #bulk-actions-container {
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Estilos para los botones de acciones masivas */
        .btn-group .btn {
            transition: all 0.2s ease;
        }
        
        .btn-group .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Estilos para las filas de la tabla cuando están seleccionadas */
        tr:has(.order-checkbox:checked) {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }
        
        /* Fallback para navegadores que no soportan :has() */
        .order-checkbox:checked {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
        }
        
        /* Estilos para los modales */
        .modal-content {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .list-group-item {
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
        }
        
        .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        /* Estilos para la card de total de ventas */
        .card.border-success {
            background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
            border: 2px solid #28a745 !important;
            transition: all 0.3s ease;
        }
        
        .card.border-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.15) !important;
        }
        
        .card.border-success .display-6 {
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: linear-gradient(45deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card.border-success .fas.fa-chart-line {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* ===== Header compacto + contadores de cocina inline ===== */
        .modern-header--compact {
            padding: .55rem .9rem !important;
        }
        .modern-header__row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            gap: 12px;
        }
        .modern-header__left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .modern-header--compact .header-icon-container {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }
        .modern-header--compact .header-icon-container i { font-size: 1rem; }
        .modern-header__titles { line-height: 1.1; min-width: 0; }
        .modern-header--compact .modern-title {
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .01em;
        }
        .modern-header--compact .modern-subtitle {
            font-size: .72rem;
            text-transform: capitalize;
        }

        /* Contadores en línea, dentro de la barra azul */
        .modern-header__counters {
            display: flex;
            align-items: stretch;
            gap: 6px;
            flex-wrap: nowrap;
            justify-content: center;
        }
        .kitchen-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            min-width: 96px;
            border-radius: 8px;
            color: #ffffff;
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
            line-height: 1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.18);
        }
        .kitchen-chip:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        .kitchen-chip__icon {
            font-size: 1rem;
            opacity: .95;
        }
        .kitchen-chip__value {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: .01em;
            color: #ffffff;
        }
        .kitchen-chip__label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255, 255, 255, 0.92);
        }
        /* Hamburguesas → naranja sólido (alto contraste sobre azul) */
        .kitchen-chip--burger {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-color: rgba(255, 255, 255, 0.28);
        }
        /* Acompañamientos → verde esmeralda */
        .kitchen-chip--side {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-color: rgba(255, 255, 255, 0.28);
        }
        /* Otros → slate oscuro */
        .kitchen-chip--other {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            border-color: rgba(255, 255, 255, 0.22);
        }
        .kitchen-chip--muted { opacity: .55; }

        /* Contadores de Pedidos Anteriores - dentro de la barra celeste del acordeón */
        .accordion-button--with-counters {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 12px;
        }
        .accordion-title-group {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .historico-counters-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            margin-right: 12px;
        }
        .historico-chip-sm {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: .8rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            transition: opacity .2s ease, transform .2s ease;
        }
        .historico-chip-sm--burger {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }
        .historico-chip-sm--side {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .historico-chip-sm--other {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        }
        .historico-chip-sm.opacity-50 {
            opacity: 0.4;
            transform: scale(0.95);
        }
        .historico-chip-sm i {
            font-size: .75rem;
        }

        /* Lado derecho: refresh + última actualización oculta hasta hover */
        .modern-header__right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            min-width: 0;
        }
        .modern-header__right .last-update-info {
            opacity: 0;
            visibility: hidden;
            transform: translateX(6px);
            transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
            white-space: nowrap;
            color: rgba(255, 255, 255, 0.85);
        }
        .modern-header__right:hover .last-update-info,
        .modern-header__right:focus-within .last-update-info {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }
        .modern-header__right .last-update-info small {
            font-size: .7rem;
            color: rgba(255, 255, 255, 0.85);
        }

        @media (max-width: 991px) {
            .modern-header__row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .modern-header__counters { justify-content: center; flex-wrap: wrap; }
            .modern-header__right { justify-content: flex-end; }
            .modern-header__right .last-update-info {
                opacity: 1; visibility: visible; transform: none;
            }
        }
        @media (max-width: 575px) {
            .kitchen-chip { min-width: 0; padding: 5px 10px; gap: 6px; }
            .kitchen-chip__label { display: none; }
            .kitchen-chip__value { font-size: 1rem; }
        }
        
        /* Estilos para filas seleccionadas - CORREGIDO */
        tr:has(.order-checkbox:checked) {
            background-color: #f0f9ff !important;
            border-left: 4px solid #3b82f6 !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1) !important;
            transition: all 0.3s ease;
        }
        
        tr:has(.order-checkbox:checked) td {
            background-color: #f0f9ff !important;
            border-color: #e0f2fe !important;
        }
        
        tr:has(.order-checkbox:checked):hover {
            background-color: #e0f2fe !important;
        }
        
        tr:has(.order-checkbox:checked):hover td {
            background-color: #e0f2fe !important;
        }
        
        /* Fallback para navegadores que no soportan :has() */
        .table-row-selected {
            background-color: #f0f9ff !important;
            border-left: 4px solid #3b82f6 !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1) !important;
            transition: all 0.3s ease;
        }
        
        .table-row-selected td {
            background-color: #f0f9ff !important;
            border-color: #e0f2fe !important;
        }
        
        .table-row-selected:hover {
            background-color: #e0f2fe !important;
        }
        
        .table-row-selected:hover td {
            background-color: #e0f2fe !important;
        }
        
        /* Asegurar que no haya conflictos con otros estilos */
        .table tbody tr.table-row-selected {
            background-color: #f0f9ff !important;
        }
        
        .table tbody tr.table-row-selected td {
            background-color: #f0f9ff !important;
        }
        
        /* Hacer las filas clickeables */
        .table tbody tr {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc !important;
        }
        
        /* Evitar que los elementos interactivos dentro de la fila activen la selección */
        .table tbody tr .btn,
        .table tbody tr .dropdown-toggle,
        .table tbody tr .form-select,
        .table tbody tr .form-check-input,
        .table tbody tr a {
            cursor: default;
            pointer-events: auto;
        }
        
        /* Indicador visual de que la fila es clickeable */
        .table tbody tr:not(.table-row-selected):hover {
            background-color: #f1f5f9 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* ===== PAGINACIÓN MODERNA Y PROFESIONAL ===== */
        .pagination-modern {
            gap: 4px;
        }

        .pagination-modern .page-item {
            margin: 0;
        }

        .pagination-modern .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        /* Asegurar que los iconos no se desborden */
        .pagination-modern .page-link > * {
            max-width: 100%;
            max-height: 100%;
            overflow: hidden;
        }
        
        /* Forzar tamaño del contenedor de iconos */
        .pagination-modern .page-link i {
            flex-shrink: 0;
            width: auto;
            height: auto;
            max-width: 12px;
            max-height: 12px;
            overflow: visible;
        }

        .pagination-modern .page-link:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #374151;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pagination-modern .page-item.active .page-link {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-color: #3b82f6;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .pagination-modern .page-item.disabled .page-link {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #d1d5db;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .pagination-modern .page-item.disabled .page-link:hover {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #d1d5db;
            transform: none;
            box-shadow: none;
        }

        /* Iconos de navegación */
        .pagination-modern .page-link i {
            font-size: 12px !important;
            font-weight: 600;
            line-height: 1;
            display: inline-block;
            width: 12px !important;
            height: 12px !important;
        }
        
        .pagination-modern .page-link i.fas {
            font-size: 12px !important;
            width: 12px !important;
            height: 12px !important;
        }
        
        /* Corregir SVG de Font Awesome en paginación */
        .pagination-modern .page-link i svg,
        .pagination-modern .page-link svg {
            width: 12px !important;
            height: 12px !important;
            max-width: 12px !important;
            max-height: 12px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            box-sizing: border-box !important;
        }
        
        .pagination-modern .page-link i.fas svg,
        .pagination-modern .page-link i.fa-chevron-left svg,
        .pagination-modern .page-link i.fa-chevron-right svg {
            width: 12px !important;
            height: 12px !important;
            max-width: 12px !important;
            max-height: 12px !important;
            box-sizing: border-box !important;
        }
        
        /* Forzar tamaño en todos los SVG dentro de paginación */
        .pagination-modern svg {
            width: 12px !important;
            height: 12px !important;
            max-width: 12px !important;
            max-height: 12px !important;
            flex-shrink: 0 !important;
        }

        /* Información de paginación */
        .pagination-info {
            font-size: 14px;
            color: #6b7280;
        }

        .pagination-info strong {
            color: #374151;
            font-weight: 600;
        }

        /* Responsive - Mobile Optimizations */
        @media (max-width: 768px) {
            /* ELIMINAR TEXTOS INNECESARIOS */
            .text-muted.small {
                font-size: 0.7rem !important;
            }
            
            .table .small.text-muted {
                display: none !important; /* Ocultar detalles innecesarios en tabla */
            }
            
            /* Filtros - Estado y Fecha en la misma línea (50/50) */
            .row.mb-4:first-child {
                margin-bottom: 1rem !important;
            }
            
            .filters-row {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                margin-bottom: 0.75rem;
            }
            
            .filter-status,
            .filter-date {
                flex: 0 0 50% !important;
                max-width: 50% !important;
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }
            
            .filter-total {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
            
            .row.mb-4:first-child .card-body {
                padding: 0.6rem 0.5rem !important;
            }
            
            .row.mb-4:first-child .form-label {
                font-size: 0.75rem !important;
                margin-bottom: 0.3rem !important;
            }
            
            .row.mb-4:first-child .form-select,
            .row.mb-4:first-child .form-control {
                font-size: 0.8rem !important;
                padding: 0.4rem 0.5rem !important;
            }
            
            .row.mb-4:first-child .btn-sm {
                font-size: 0.7rem !important;
                padding: 0.3rem 0.5rem !important;
            }
            
            /* Ocultar botones "Hoy" y "Ayer" en mobile para ahorrar espacio */
            .filter-date .d-flex.gap-2 {
                display: none !important;
            }
            
            /* Card de total ventas más compacta */
            .card.border-success .card-body {
                padding: 0.75rem !important;
            }
            
            .card.border-success h6 {
                font-size: 0.85rem !important;
            }
            
            .card.border-success .display-6 {
                font-size: 1.5rem !important;
            }
            
            .card.border-success .fas.fa-chart-line {
                font-size: 1.2rem !important;
            }
            
            /* Tabla más compacta */
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table th,
            .table td {
                padding: 0.5rem 0.4rem !important;
                font-size: 0.8rem !important;
            }
            
            .table th {
                font-size: 0.7rem !important;
                white-space: nowrap;
            }
            
            .table .btn-sm {
                font-size: 0.7rem !important;
                padding: 0.25rem 0.4rem !important;
            }
            
            .table .badge {
                font-size: 0.65rem !important;
                padding: 0.25rem 0.5rem !important;
            }
            
            .table .form-select {
                font-size: 0.75rem !important;
                padding: 0.25rem 0.4rem !important;
            }
            
            /* Card header más compacto */
            .card-header {
                padding: 0.75rem !important;
            }
            
            .card-header h6 {
                font-size: 0.9rem !important;
            }
            
            /* Paginación */
            .pagination-modern .page-link {
                min-width: 36px;
                height: 36px;
                padding: 0 8px;
                font-size: 13px;
            }
            
            .pagination-info {
                font-size: 13px;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 16px;
                align-items: center !important;
            }
            
            /* Acciones masivas más compactas */
            #bulk-actions-container .card-body {
                padding: 0.75rem !important;
            }

            /* Layout de acciones masivas en mobile */
            #bulk-actions-container .card-body > .row {
                flex-direction: column !important;
                gap: 1rem !important;
            }

            #bulk-actions-container .col-md-6 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }

            /* Contador y total alineados horizontalmente en mobile */
            #bulk-actions-container .col-md-6:first-child .d-flex {
                flex-direction: row !important;
                justify-content: space-between !important;
                width: 100% !important;
                gap: 0.5rem !important;
            }

            /* Badge del total más pequeño en mobile */
            #selected-total {
                font-size: 0.85rem !important;
                padding: 0.4rem 0.6rem !important;
            }

            /* Botones apilados verticalmente en mobile */
            #bulk-actions-container .btn-group {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                gap: 0.5rem !important;
            }

            #bulk-actions-container .btn-group .btn {
                width: 100% !important;
                text-align: center !important;
                font-size: 0.85rem !important;
                padding: 0.6rem 0.75rem !important;
            }

            #bulk-actions-container .btn {
                font-size: 0.8rem !important;
                padding: 0.5rem 0.75rem !important;
            }
            
            /* Modales más compactos */
            .modal-body {
                padding: 1rem !important;
            }
            
            .modal-body .table {
                font-size: 0.8rem;
            }
            
            /* Ajustes generales */
            .main-content {
                padding: 0.5rem !important;
            }
            
            .mb-4 {
                margin-bottom: 1rem !important;
            }
        }
        
        /* LANDSCAPE MODE - Mobile horizontal - Orders */
        @media (max-width: 1024px) and (orientation: landscape) {
            /* Aprovechar todo el ancho disponible */
            .main-content {
                padding: 0.75rem !important;
                max-width: 100vw !important;
                overflow-x: hidden !important;
            }
            
            /* Filtros en una línea - Estado, Fecha y Total */
            .filters-row {
                flex: 0 0 66.666667% !important;
                max-width: 66.666667% !important;
            }
            
            .filter-status,
            .filter-date {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
            
            .filter-total {
                flex: 0 0 33.333333% !important;
                max-width: 33.333333% !important;
            }
            
            /* Mostrar botones "Hoy" y "Ayer" en landscape */
            .filter-date .d-flex.gap-2 {
                display: flex !important;
            }
            
            /* Tabla más legible en landscape */
            .table {
                font-size: 0.85rem !important;
            }
            
            .table th,
            .table td {
                padding: 0.5rem 0.4rem !important;
                font-size: 0.85rem !important;
            }
            
            .table th {
                font-size: 0.75rem !important;
            }
            
            /* Cards más espaciosas */
            .card-body {
                padding: 0.85rem !important;
            }
            
            /* Asegurar que no haya overflow */
            * {
                max-width: 100% !important;
            }
            
            .container-fluid,
            .row {
                max-width: 100% !important;
                overflow-x: hidden !important;
            }
            
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Animación suave para cambios de página - Agregado a la regla existente */
        .pagination-modern .page-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .pagination-modern .page-link:hover::before {
            left: 100%;
        }

        /* ===== FUENTES MODERNAS PARA TOTALES Y DESCUENTOS ===== */
        .modern-total-font {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: -0.025em;
            line-height: 1.2;
        }

        .modern-discount-font {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 500;
            font-size: 0.85rem;
            letter-spacing: 0.025em;
            line-height: 1.3;
        }

        /* Mejoras adicionales para los totales */
        .text-end .modern-total-font {
            margin-bottom: 0.25rem;
        }

        .text-end .modern-discount-font {
            display: block;
            margin-top: 0.125rem;
        }

        /* Efectos sutiles para mejor legibilidad */
        .modern-total-font {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .modern-discount-font {
            opacity: 0.9;
        }

        /* ===== ICONO DE PERFIL MODERNO ===== */
        .modern-profile-icon {
            font-size: 1.25rem;
            color: #3b82f6 !important;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(59, 130, 246, 0.2));
        }

        .modern-profile-icon:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.3));
        }

        /* Alternativa con fondo circular para mejor visibilidad */
        .modern-profile-icon-container {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
            transition: all 0.3s ease;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .modern-profile-icon-container:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .modern-profile-icon-container i {
            color: white !important;
            font-size: 0.9rem;
            background: none !important;
            -webkit-background-clip: unset !important;
            -webkit-text-fill-color: unset !important;
            background-clip: unset !important;
            line-height: 1;
        }

        /* ===== BADGE DE HORARIO PROFESIONAL ===== */
        .badge-schedule-professional {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            border: none;
            transition: all 0.3s ease;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            letter-spacing: 0.025em;
        }

        .badge-schedule-professional:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Alternativa con gradiente más sutil */
        .badge-schedule-modern {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
            color: white !important;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
            border: none;
            transition: all 0.3s ease;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            letter-spacing: 0.025em;
        }

        .badge-schedule-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
        }

        /* ===== BADGE DE TOTAL SELECCIONADO ===== */
        #selected-total {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            color: white !important;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
            border: none;
            transition: all 0.3s ease;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            letter-spacing: 0.025em;
            min-width: 120px;
            text-align: center;
        }

        #selected-total:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
        }

        #selected-total i {
            font-size: 0.9rem;
        }

        /* ===== HEADER MODERNO PARA PEDIDOS DE HOY ===== */
        .modern-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            border: none;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }

        .header-icon-container {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-icon-container i {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .modern-title {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-weight: 600;
            font-size: 1.125rem;
            letter-spacing: -0.025em;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 0.25rem;
        }

        .modern-subtitle {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-weight: 400;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.75);
            letter-spacing: 0.025em;
        }

        /* Botón de actualización moderno */
        .btn-refresh {
            width: 40px;
            height: 40px;
            border: none;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-refresh:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-refresh:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .refresh-icon {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            transition: transform 0.3s ease;
        }

        .btn-refresh:hover .refresh-icon {
            transform: rotate(180deg);
        }

        .btn-refresh:active .refresh-icon {
            transform: rotate(360deg);
        }

        /* Información de última actualización */
        .last-update-info {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .last-update-info small {
            font-size: 0.75rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.75);
        }

        .last-update-info i {
            font-size: 0.75rem;
        }

        /* Animación de carga para el botón de actualización */
        .btn-refresh.loading .refresh-icon {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive para el header */
        @media (max-width: 768px) {
            .modern-header {
                padding: 1rem;
            }
            
            .header-icon-container {
                width: 40px;
                height: 40px;
            }
            
            .header-icon-container i {
                font-size: 1rem;
            }
            
            .modern-title {
                font-size: 1rem;
            }
            
            .modern-subtitle {
                font-size: 0.8rem;
            }
            
            .btn-refresh {
                width: 36px;
                height: 36px;
            }
            
            .refresh-icon {
                font-size: 0.9rem;
            }
            
            .last-update-info small {
                font-size: 0.7rem;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.75rem;
            }
            
            .d-flex.align-items-center:last-child {
                align-self: flex-end;
            }
        }

        /* Overlay con blur para Repartir Figuritas */
        .loyalty-blur-overlay {
            position: absolute;
            inset: 0;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            background: rgba(255,255,255,0.25);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .btn-loyalty-green {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px !important;
            text-transform: uppercase !important;
            font-size: 0.6rem !important;
            padding: 0.3rem 0.5rem !important;
            border-radius: 6px !important;
            box-shadow: 0 2px 6px rgba(40,167,69,0.35) !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.25rem !important;
            line-height: 1.1 !important;
        }
        .btn-loyalty-green:hover {
            background: linear-gradient(135deg, #218838 0%, #19692c 100%) !important;
            transform: translateY(-2px) scale(1.04) !important;
            box-shadow: 0 5px 14px rgba(40,167,69,0.45) !important;
        }
        .btn-loyalty-green:active {
            transform: translateY(0) scale(0.97) !important;
            box-shadow: 0 2px 5px rgba(40,167,69,0.35) !important;
        }
        .btn-loyalty-green i {
            font-size: 1rem !important;
        }
    </style>
    <script>
        // SOLUCIÓN DEFINITIVA - CHECKBOXES FUNCIONALES
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM cargado, inicializando checkboxes...');
            initCheckboxes();
            initUpdateTimer();
        });

        // ===== FUNCIONALIDAD DE ACTUALIZACIÓN =====
        let lastUpdateTime = new Date();
        let updateTimer;

        function initUpdateTimer() {
            updateTimer = setInterval(updateTimeDisplay, 1000);
        }

        function updateTimeDisplay() {
            const now = new Date();
            const diffInSeconds = Math.floor((now - lastUpdateTime) / 1000);
            const updateTimeElement = document.getElementById('update-time');
            
            if (updateTimeElement) {
                if (diffInSeconds < 60) {
                    updateTimeElement.textContent = `${diffInSeconds} segundos`;
                } else if (diffInSeconds < 3600) {
                    const minutes = Math.floor(diffInSeconds / 60);
                    updateTimeElement.textContent = `${minutes} minuto${minutes !== 1 ? 's' : ''}`;
                } else {
                    const hours = Math.floor(diffInSeconds / 3600);
                    updateTimeElement.textContent = `${hours} hora${hours !== 1 ? 's' : ''}`;
                }
            }
        }

        let isRefreshing         = false;
        let activeStatusUpdates  = 0;

        function refreshOrders() {
            if (isRefreshing) return;
            if (activeStatusUpdates > 0) {
                showNotification('Esperá que termine el cambio de estado antes de actualizar', 'warning');
                return;
            }
            isRefreshing = true;

            const refreshBtn  = document.querySelector('.btn-refresh');
            const refreshIcon = document.querySelector('.refresh-icon');

            refreshBtn.classList.add('loading');
            refreshIcon.style.transform = 'rotate(360deg)';

            fetch('{{ route("admin.orders.refresh") }}?selected_date={{ $selectedDate ?? now()->format('Y-m-d') }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateOrdersTable(data.data);
                    updateStats(data.data.stats);
                    lastUpdateTime = new Date();
                    showNotification('Pedidos actualizados correctamente', 'success');
                } else {
                    throw new Error(data.message || 'Error al actualizar pedidos');
                }
            })
            .catch(error => {
                console.error('Error refreshing orders:', error);
                showNotification('Error al actualizar pedidos: ' + error.message, 'error');
            })
            .finally(() => {
                isRefreshing = false;
                refreshBtn.classList.remove('loading');
                refreshIcon.style.transform = 'rotate(0deg)';
            });
        }

        function updateOrdersTable(data) {
            // Actualizar tabla de pedidos de hoy
            if (data.pedidos_hoy) {
                if ($.fn.DataTable.isDataTable('#orders-table')) {
                    const dt = $('#orders-table').DataTable();
                    dt.clear();
                    data.pedidos_hoy.forEach(order => {
                        dt.row.add(createOrderRow(order, data.microturnos_hoy));
                    });
                    dt.draw();
                } else {
                    const tbody = document.getElementById('orders-tbody-hoy');
                    if (tbody) {
                        tbody.innerHTML = '';
                        data.pedidos_hoy.forEach(order => {
                            const row = createOrderRow(order, data.microturnos_hoy);
                            tbody.appendChild(row);
                        });
                    }
                }
            }

            // Actualizar tabla de pedidos históricos si es necesario
            if (data.pedidos_historico) {
                const historico = Array.isArray(data.pedidos_historico)
                    ? data.pedidos_historico
                    : (data.pedidos_historico.data || []);

                if ($.fn.DataTable.isDataTable('#orders-table-historico')) {
                    const dt = $('#orders-table-historico').DataTable();
                    dt.clear();
                    historico.forEach(order => {
                        dt.row.add(createOrderRow(order, data.microturnos_hoy, true));
                    });
                    dt.draw();
                } else {
                    const historicoTbody = document.getElementById('orders-tbody-historico');
                    if (historicoTbody) {
                        historicoTbody.innerHTML = '';
                        historico.forEach(order => {
                            const row = createOrderRow(order, data.microturnos_hoy, true);
                            historicoTbody.appendChild(row);
                        });
                    }
                }
            }
        }

        function updateStats(stats) {
            // Actualizar total de ventas del día
            const totalVentasElement = document.querySelector('.display-6');
            if (totalVentasElement && stats.total_ventas_hoy !== undefined) {
                // Verificar si el total estaba oculto antes de actualizar
                const wasHidden = totalVentasElement.dataset.originalAmount !== undefined;
                const newAmount = '$' + new Intl.NumberFormat('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(stats.total_ventas_hoy);

                if (wasHidden) {
                    // Mantener oculto, actualizar el valor original pero seguir mostrando puntos
                    totalVentasElement.dataset.originalAmount = newAmount;
                } else {
                    // Mostrar el nuevo valor
                    totalVentasElement.textContent = newAmount;
                }
            }

            // Actualizar contador de pedidos
            const pedidosCountElement = document.querySelector('.text-muted');
            if (pedidosCountElement && stats.total_pedidos_hoy !== undefined) {
                const count = stats.total_pedidos_hoy;
                pedidosCountElement.textContent = `${count} pedido${count !== 1 ? 's' : ''}`;
            }
        }

        function jsOrderHasLoyaltyAwarded(order) {
            if (!order.change_log || !Array.isArray(order.change_log)) return false;
            return order.change_log.some(entry => entry && entry.action === 'loyalty_awarded');
        }

        function createOrderRow(order, microturnosHoy, isHistorico = false) {
            const row = document.createElement('tr');
            row.dataset.orderNumber = order.order_number;
            if (order.status === 'delivered') row.classList.add('order-row-delivered');

            // Datos para WhatsApp y solicitud de reseña
            const phone = (order.user && order.user.phone ? order.user.phone : order.contact_phone) || '';
            let cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length === 10 && !cleanPhone.startsWith('54')) {
                cleanPhone = '54' + cleanPhone;
            }
            const wName = (order.user && order.user.name !== 'Invitado') ? order.user.name : (order.contact_name || 'Cliente');
            const wText = encodeURIComponent(`Hola ${wName}, soy de T Cocina. Te escribo respecto a tu pedido #${order.order_number}.`);
            const wLink = `https://wa.me/${cleanPhone}?text=${wText}`;
            const rText = encodeURIComponent(`¡Hola ${wName}! ¿Te gustaron nuestras hamburguesas? Si tenés un minuto, nos ayudaría mucho tu opinión en Google: https://g.page/r/CepJ7XpQQOkyEBM/review`);
            const rLink = `https://wa.me/${cleanPhone}?text=${rText}`;

            // Determinar el horario asignado
            let horarioAsignado = 'Sin horario';
            if (order.microturno_sort_order && microturnosHoy) {
                const microturnoAsignado = microturnosHoy.find(m => m.sort_order == order.microturno_sort_order);
                if (microturnoAsignado) {
                    horarioAsignado = microturnoAsignado.formatted_time;
                }
            }
            
            // Determinar el estado del pedido
            const statusClass = getStatusClass(order.status);
            const statusText = getStatusText(order.status);
            
            const fechaCell = isHistorico ? `
                <td data-label="Fecha">
                    <small>${order.created_at ? new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' }) : ''}</small>
                    <br><small class="text-muted">${order.created_at ? new Date(order.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) : ''}</small>
                </td>
            ` : '';

            row.innerHTML = `
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input order-checkbox"
                               value="${order.id}" data-order-number="${order.order_number}"
                               style="transform: scale(1.2);">
                        <label class="form-check-label visually-hidden">Seleccionar pedido</label>
                    </div>
                </td>
                ${fechaCell}
                <td data-label="Cliente">
                    <div class="d-flex align-items-center">
                        <div class="modern-profile-icon-container">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <div class="fw-semibold d-flex align-items-center gap-1">
                                ${order.user && order.user.name !== 'Invitado' ? order.user.name : (order.contact_name || 'Invitado')}
                                ${order.user && order.user.google_id ? '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 18 18" style="flex-shrink:0;vertical-align:middle" title="Login con Google"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z"/></svg>' : ''}
                                ${order.reviews && order.reviews.length > 0 ? '<i class="fas fa-star text-warning" style="font-size: 0.75rem;" title="Dejó reseña"></i>' : ''}
                            </div>
                            <small class="text-muted">${order.user && order.user.phone ? order.user.phone : (order.contact_phone || 'Sin teléfono')}</small>
                        </div>
                    </div>
                </td>
                <td data-label="Items">
                    <div class="d-flex flex-wrap gap-1">
                        ${order.items.map(item => `
                            <span class="badge bg-primary">${item.quantity} ${item.product.name}</span>
                        `).join('')}
                    </div>
                </td>
                <td data-label="Total">
                    <div>
                        <div class="fw-bold text-success modern-total-font">$${new Intl.NumberFormat('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(order.total_amount)}</div>
                        ${order.discount_amount > 0 ? `
                            <small class="text-danger modern-discount-font">-${new Intl.NumberFormat('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(order.discount_amount)} desc.</small>
                        ` : ''}
                        ${order.coupon ? `<small class="text-primary d-block modern-discount-font">${order.coupon.name}</small>` : ''}
                    </div>
                </td>
                <td data-label="Pago">
                    <span class="badge payment-toggle ${order.payment_method === 'transfer' ? 'bg-info text-dark' : (order.payment_method === 'card' ? 'bg-secondary' : 'bg-success')}"
                          data-order-id="${order.id}" data-method="${order.payment_method || 'cash'}" data-amount="${order.total_amount}"
                          role="button" style="cursor: pointer;"
                          title="${order.payment_method === 'card' ? 'Tarjeta: se edita desde el detalle del pedido' : 'Click para alternar Efectivo/Transferencia'}">
                        ${order.payment_method === 'transfer' ? 'TRANSFERENCIA' : (order.payment_method === 'card' ? 'TARJETA' : 'EFECTIVO')}
                    </span>
                </td>
                <td data-label="Horario" class="position-relative">
                    ${order.status === 'pending' ? `
                        <select class="form-select microturno-select" data-order-id="${order.id}">
                            <option value="">Sin horario</option>
                            ${microturnosHoy ? microturnosHoy.map(microturno => `
                                <option value="${microturno.sort_order}"
                                        ${microturno.sort_order == order.microturno_sort_order ? 'selected' : ''}>
                                    ${microturno.formatted_time}
                                </option>
                            `).join('') : ''}
                        </select>
                    ` : `
                        <span class="badge badge-schedule-professional">${horarioAsignado}</span>
                    `}
                    ${order.status === 'delivered' && order.user_id && order.user && order.user.email !== 'guest@tecocina.local' && !jsOrderHasLoyaltyAwarded(order) ? `
                        <div class="loyalty-blur-overlay">
                            <button type="button" class="btn btn-loyalty-green"
                                onclick="openAwardLoyaltyModal(${order.id})"
                                title="Repartir figuritas al usuario">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>Repartir figuritas</span>
                            </button>
                        </div>
                    ` : ''}
                </td>
                <td data-label="Estado">
                    <select class="form-select form-select-sm status-select" data-order-id="${order.id}">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pendiente</option>
                        <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Confirmado</option>
                        <option value="preparing" ${order.status === 'preparing' ? 'selected' : ''}>En Preparación</option>
                        <option value="ready" ${order.status === 'ready' ? 'selected' : ''}>Listo ✓</option>
                        ${order.address_id ? `<option value="on_the_way" ${order.status === 'on_the_way' ? 'selected' : ''}>En camino 🛵</option>` : ''}
                        <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Entregado</option>
                    </select>
                </td>
                <td data-label="Acciones">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            data-bs-boundary="window" data-bs-strategy="fixed">
                            Acciones
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="viewOrder(${order.id})">
                                <i class="fas fa-eye me-2"></i>Ver pedido
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="printOrder(${order.id})">
                                <i class="fas fa-print me-2"></i>Imprimir
                            </a></li>
                            ${phone && cleanPhone ? `
                            <li><a class="dropdown-item text-success" href="${wLink}" target="_blank">
                                <i class="fab fa-whatsapp me-2"></i>Enviar WhatsApp
                            </a></li>
                            <li><a class="dropdown-item text-warning" href="${rLink}" target="_blank" onclick="markReviewPrompted(${order.id})" id="review-btn-${order.id}">
                                <i class="fas fa-star me-2"></i>Solicitar reseña
                            </a></li>
                            ` : ''}
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteOrder(${order.id})">
                                <i class="fas fa-trash me-2"></i>Eliminar
                            </a></li>
                        </ul>
                    </div>
                </td>
            `;
            
            return row;
        }

        function getStatusClass(status) {
            const classes = {
                'pending': 'bg-warning',
                'confirmed': 'bg-info',
                'preparing': 'bg-primary',
                'ready': 'bg-success',
                'delivered': 'bg-dark',
                'cancelled': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        }

        function getStatusText(status) {
            const texts = {
                'pending': 'Pendiente',
                'confirmed': 'Confirmado',
                'preparing': 'Preparando',
                'ready': 'Listo',
                'delivered': 'Entregado',
                'cancelled': 'Cancelado'
            };
            return texts[status] || status;
        }

        function showNotification(message, type = 'info') {
            // Crear notificación toast
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
            `;
            
            // Agregar estilos
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : '#3b82f6'};
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                font-family: 'Inter', sans-serif;
                font-size: 14px;
                font-weight: 500;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            // Animar entrada
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // ── Polling: notificación de nuevo pedido ────────────────────────────
        (function initNewOrderPolling() {
            let lastKnownId = null;

            async function checkLatestOrder() {
                try {
                    const res = await fetch('{{ route("admin.orders.latest-check") }}', { cache: 'no-store' });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (lastKnownId === null) { lastKnownId = data.latest_id; return; }
                    if (data.latest_id > lastKnownId) {
                        lastKnownId = data.latest_id;
                        showNewOrderToast(data.order_number, data.contact_name);
                    }
                } catch(e) {}
            }

            function playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                    osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.35);
                } catch(e) {}
            }

            function doSilentRefresh(cb) {
                if (isRefreshing) { cb?.(); return; }
                fetch('{{ route("admin.orders.refresh") }}?selected_date={{ $selectedDate ?? now()->format("Y-m-d") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        updateOrdersTable(data.data);
                        updateStats(data.data.stats);
                        lastUpdateTime = new Date();
                    }
                    cb?.();
                })
                .catch(() => cb?.());
            }

            function highlightRow(orderNumber) {
                const tbody = document.getElementById('orders-tbody-hoy');
                if (!tbody) return;
                const row = tbody.querySelector(`tr[data-order-number="${orderNumber}"]`);
                if (!row) return;
                row.classList.add('new-order-highlight');
                row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                setTimeout(() => row.classList.remove('new-order-highlight'), 4500);
            }

            function animateToastOut(toast, orderNumber, alreadyRefreshed) {
                if (!toast.parentNode) return;

                // Fase 1 (0–230ms): toast se comprime en pastilla compacta
                toast.style.transition = 'all .22s cubic-bezier(.4,0,.2,1)';
                toast.style.padding = '9px 18px';
                toast.style.minWidth = 'unset';
                toast.style.maxWidth = '175px';
                toast.style.borderRadius = '24px';
                toast.style.overflow = 'hidden';
                toast.innerHTML = `<span style="white-space:nowrap;font-weight:700;font-size:.84rem;display:flex;align-items:center;gap:6px;"><span style="animation:toastBikeBounce .4s ease infinite alternate;display:inline-block;">🛵</span> #${orderNumber}</span>`;

                setTimeout(() => {
                    // Fase 2 (230–730ms): pastilla vuela hacia la fila en la tabla
                    const tbody  = document.getElementById('orders-tbody-hoy');
                    const target = tbody?.querySelector(`tr[data-order-number="${orderNumber}"]`)
                                ?? tbody?.querySelector('tr');
                    const toastRect  = toast.getBoundingClientRect();
                    const targetRect = (target ?? tbody)?.getBoundingClientRect();

                    if (targetRect) {
                        const dx = (targetRect.left + targetRect.width  / 2)
                                 - (toastRect.left  + toastRect.width   / 2);
                        const dy = (targetRect.top  + targetRect.height / 2)
                                 - (toastRect.top   + toastRect.height  / 2);
                        toast.style.transition = 'transform .48s cubic-bezier(.5,0,.6,1), opacity .44s ease .06s';
                        toast.style.transform  = `translateX(calc(-50% + ${dx}px)) translateY(${dy}px) scale(0.12)`;
                        toast.style.opacity    = '0';
                    } else {
                        toast.style.transition = 'transform .35s ease, opacity .35s ease';
                        toast.style.transform  = 'translateX(-50%) translateY(-80px)';
                        toast.style.opacity    = '0';
                    }

                    // Fase 3 (730ms): remover toast + resaltar fila nueva
                    setTimeout(() => {
                        toast.remove();
                        if (alreadyRefreshed) {
                            highlightRow(orderNumber);
                        } else {
                            doSilentRefresh(() => highlightRow(orderNumber));
                        }
                    }, 500);
                }, 240);
            }

            function showNewOrderToast(orderNumber, contactName) {
                const prev = document.getElementById('new-order-toast');
                if (prev) { clearTimeout(prev._t); prev.remove(); }

                const toast = document.createElement('div');
                toast.id = 'new-order-toast';
                toast.innerHTML = `
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="font-size:1.55rem;line-height:1;animation:toastBikeBounce .55s ease infinite alternate;display:inline-block;">🛵</div>
                        <div>
                            <div style="font-weight:700;font-size:.95rem;margin-bottom:2px;">¡Nuevo pedido recibido!</div>
                            <div style="font-size:.82rem;opacity:.88;">Pedido <strong>#${orderNumber}</strong> — ${contactName || 'Cliente'}</div>
                        </div>
                        <button class="toast-close-x" style="margin-left:auto;background:rgba(255,255,255,.22);border:none;border-radius:6px;color:#fff;width:28px;height:28px;cursor:pointer;font-size:.9rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">✕</button>
                    </div>
                `;
                toast.style.cssText = `
                    position:fixed; top:16px; left:50%;
                    transform:translateX(-50%) translateY(-90px);
                    background:linear-gradient(135deg,#16a34a,#0d9488);
                    color:#fff; padding:14px 20px; border-radius:14px;
                    box-shadow:0 10px 36px rgba(0,0,0,.26);
                    z-index:99999; min-width:320px; max-width:480px;
                    font-family:inherit;
                    transition:transform .42s cubic-bezier(.34,1.56,.64,1);
                    cursor:default;
                `;
                document.body.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.style.transform = 'translateX(-50%) translateY(0)';
                });

                playBeep();

                // Pre-refrescar tabla a los 5s para que la nueva fila ya esté en DOM
                let alreadyRefreshed = false;
                setTimeout(() => doSilentRefresh(() => { alreadyRefreshed = true; }), 5000);

                // Auto-cerrar a los 8s con animación de vuelo hacia la tabla
                const dismiss = () => {
                    clearTimeout(toast._t);
                    animateToastOut(toast, orderNumber, alreadyRefreshed);
                };
                toast._t = setTimeout(dismiss, 8000);
                toast.querySelector('.toast-close-x').addEventListener('click', dismiss);
            }

            checkLatestOrder();
            setInterval(checkLatestOrder, 20000);
        })();
        // ─────────────────────────────────────────────────────────────────────

        function initCheckboxes() {
            console.log('🔧 Inicializando checkboxes...');
            
            // Función para mostrar/ocultar acciones masivas
            function toggleBulkActions() {
                const checked = document.querySelectorAll('.order-checkbox:checked');
                const container = document.getElementById('bulk-actions-container');
                const countElement = document.getElementById('selected-count');
                const totalElement = document.getElementById('total-amount');
                const printBtn = document.getElementById('bulk-print-btn');
                const statusBtn = document.getElementById('bulk-status-btn');
                const deleteBtn = document.getElementById('bulk-delete-btn');
                
                console.log('🔄 Toggle bulk actions - Checkboxes marcados:', checked.length);
                
                // Actualizar resaltado de filas
                updateRowHighlighting();

                // Actualizar contadores de Pedidos Anteriores
                updateHistoricoCounters();
                
                if (container) {
                    if (checked.length > 0) {
                        console.log('✅ Mostrando acciones masivas');
                        container.style.display = 'block';
                        if (countElement) {
                            countElement.textContent = `${checked.length} pedido${checked.length > 1 ? 's' : ''} seleccionado${checked.length > 1 ? 's' : ''}`;
                        }
                        
                        // Calcular total de pedidos seleccionados
                        let totalAmount = 0;
                        checked.forEach(checkbox => {
                            const row = checkbox.closest('tr');
                            if (row) {
                                // Determinar si es tabla histórica (tiene columna de Fecha)
                                const isHistorico = row.closest('#orders-tbody-historico') !== null;
                                // Buscar la celda del total: columna 4 para hoy, columna 5 para histórico
                                const totalCell = row.querySelector(isHistorico ? 'td:nth-child(5)' : 'td:nth-child(4)');
                                if (totalCell) {
                                    const totalText = totalCell.textContent.trim();
                                    console.log('🔍 Texto de total encontrado:', totalText);
                                    // Extraer el número del total (formato: $15.700,00)
                                    const match = totalText.match(/\$([\d.,]+)/);
                                    if (match) {
                                        // Convertir formato argentino a número (15.700,00 -> 15700.00)
                                        const cleanNumber = match[1].replace(/\./g, '').replace(',', '.');
                                        const amount = parseFloat(cleanNumber);
                                        console.log('💰 Cantidad extraída:', amount);
                                        if (!isNaN(amount)) {
                                            totalAmount += amount;
                                        }
                                    }
                                }
                            }
                        });
                        
                        // Actualizar el total en el badge
                        console.log('💵 Total calculado:', totalAmount);
                        if (totalElement) {
                            const formattedTotal = new Intl.NumberFormat('es-ES', {
                                style: 'currency',
                                currency: 'ARS',
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(totalAmount).replace('ARS', '').trim();
                            console.log('🎯 Total formateado:', formattedTotal);
                            totalElement.textContent = formattedTotal;
                        }
                        
                        // Habilitar botones
                        if (printBtn) printBtn.disabled = false;
                        if (statusBtn) statusBtn.disabled = false;
                        if (deleteBtn) deleteBtn.disabled = false;
                    } else {
                        console.log('❌ Ocultando acciones masivas');
                        container.style.display = 'none';
                        // Resetear el total
                        if (totalElement) {
                            totalElement.textContent = '$0,00';
                        }
                        // Deshabilitar botones
                        if (printBtn) printBtn.disabled = true;
                        if (statusBtn) statusBtn.disabled = true;
                        if (deleteBtn) deleteBtn.disabled = true;
                    }
                } else {
                    console.error('❌ No se encontró bulk-actions-container');
                }
            }
            
            // Función para actualizar el resaltado de filas
            function updateRowHighlighting() {
                const allCheckboxes = document.querySelectorAll('.order-checkbox');

                allCheckboxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    if (row) {
                        if (checkbox.checked) {
                            // Agregar clase de selección
                            row.classList.add('table-row-selected');
                            console.log('✅ Fila resaltada para pedido:', checkbox.value);
                        } else {
                            // Remover clase de selección
                            row.classList.remove('table-row-selected');
                        }
                    }
                });
            }

            // Función para actualizar contadores de Pedidos Anteriores según selección
            function updateHistoricoCounters() {
                const historicoCheckboxes = document.querySelectorAll('#orders-tbody-historico .order-checkbox:checked');
                const hambCountEl = document.getElementById('historico-hamb-count');
                const sideCountEl = document.getElementById('historico-side-count');
                const otherCountEl = document.getElementById('historico-other-count');
                const burgerChip = document.getElementById('historico-chip-burger');
                const sideChip = document.getElementById('historico-chip-side');
                const otherChip = document.getElementById('historico-chip-other');

                if (!hambCountEl || !sideCountEl || !otherCountEl) return;

                let totalHamb = 0, totalSide = 0, totalOther = 0;

                historicoCheckboxes.forEach(cb => {
                    const row = cb.closest('tr');
                    if (row) {
                        totalHamb += parseInt(row.dataset.hamb || 0, 10);
                        totalSide += parseInt(row.dataset.side || 0, 10);
                        totalOther += parseInt(row.dataset.other || 0, 10);
                    }
                });

                // Actualizar valores
                hambCountEl.textContent = totalHamb;
                sideCountEl.textContent = totalSide;
                otherCountEl.textContent = totalOther;

                // Añadir/quitar clase muted si es cero
                if (burgerChip) burgerChip.classList.toggle('opacity-50', totalHamb === 0);
                if (sideChip) sideChip.classList.toggle('opacity-50', totalSide === 0);
                if (otherChip) otherChip.classList.toggle('opacity-50', totalOther === 0);
            }
            
            // Event listener para checkboxes individuales
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('order-checkbox')) {
                    console.log('✅ Checkbox individual:', e.target.checked);
                    toggleBulkActions();
                }
                
                // Select all hoy
                if (e.target.id === 'select-all-hoy') {
                    console.log('🔄 Select all hoy:', e.target.checked);
                    const checkboxes = document.querySelectorAll('#orders-tbody-hoy .order-checkbox');
                    checkboxes.forEach(cb => cb.checked = e.target.checked);
                    toggleBulkActions();
                }
                
                // Select all historico
                if (e.target.id === 'select-all-historico') {
                    console.log('🔄 Select all historico:', e.target.checked);
                    const checkboxes = document.querySelectorAll('#orders-tbody-historico .order-checkbox');
                    checkboxes.forEach(cb => cb.checked = e.target.checked);
                    toggleBulkActions();
                }
            });
            
            // Hacer las filas clickeables para seleccionar/deseleccionar
            document.addEventListener('click', function(e) {
                // Buscar la fila (tr) más cercana
                const row = e.target.closest('tbody tr');
                if (!row) return;
                
                // Verificar si el click fue en un elemento interactivo que NO debe activar la selección
                const interactiveElements = e.target.closest('.btn, .dropdown-toggle, .form-select, .form-check-input, a, .dropdown-menu');
                if (interactiveElements) {
                    console.log('🚫 Click en elemento interactivo, no activando selección');
                    return;
                }
                
                // Buscar el checkbox en la fila
                const checkbox = row.querySelector('.order-checkbox');
                if (!checkbox) return;
                
                console.log('🖱️ Click en fila, alternando checkbox:', checkbox.value);
                
                // Alternar el estado del checkbox
                checkbox.checked = !checkbox.checked;
                
                // Disparar evento change para que se actualice la UI
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
            
            // Event listeners para botones
            document.addEventListener('click', function(e) {
                console.log('🖱️ Click detectado en:', e.target);
                
                // Limpiar selección
                if (e.target.id === 'clear-selection-btn' || e.target.closest('#clear-selection-btn')) {
                    console.log('🧹 Limpiando selección');
                    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
                    toggleBulkActions();
                }
                
                // Imprimir seleccionados
                if (e.target.id === 'bulk-print-btn' || e.target.closest('#bulk-print-btn')) {
                    console.log('🖨️ Botón de imprimir clickeado');
                    const checked = document.querySelectorAll('.order-checkbox:checked');
                    console.log('📊 Pedidos seleccionados para imprimir:', checked.length);
                    
                    if (checked.length > 0) {
                        alert(`Se abrirán ${checked.length} ventanas de impresión`);
                        checked.forEach((cb, index) => {
                            console.log(`🖨️ Abriendo ventana ${index + 1} para pedido:`, cb.value);
                            setTimeout(() => {
                                window.open(`/admin/orders/${cb.value}/print`, '_blank');
                            }, index * 200);
                        });
                    } else {
                        console.log('⚠️ No hay pedidos seleccionados para imprimir');
                    }
                }
                
                // Cambiar estado masivo
                if (e.target.id === 'bulk-status-btn' || e.target.closest('#bulk-status-btn')) {
                    console.log('📝 Botón de cambiar estado clickeado');
                    const checked = document.querySelectorAll('.order-checkbox:checked');
                    console.log('📊 Pedidos seleccionados para cambiar estado:', checked.length);
                    
                    if (checked.length > 0) {
                        // Llenar el modal con información de los pedidos
                        const ordersList = document.getElementById('bulk-orders-list');
                        const countElement = document.getElementById('bulk-status-count');
                        
                        if (countElement) {
                            countElement.textContent = checked.length;
                        }
                        
                        if (ordersList) {
                            ordersList.innerHTML = '';
                            checked.forEach(cb => {
                                const orderNumber = cb.dataset.orderNumber;
                                const listItem = document.createElement('div');
                                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                                listItem.innerHTML = `
                                    <div>
                                        <i class="fas fa-receipt me-2 text-primary"></i>
                                        <strong>Pedido #${orderNumber}</strong>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">ID: ${cb.value}</span>
                                `;
                                ordersList.appendChild(listItem);
                            });
                        }
                        
                        // Mostrar el modal
                        const modal = new bootstrap.Modal(document.getElementById('bulkStatusModal'));
                        modal.show();
                    } else {
                        console.log('⚠️ No hay pedidos seleccionados para cambiar estado');
                    }
                }
                
                // Eliminar seleccionados
                if (e.target.id === 'bulk-delete-btn' || e.target.closest('#bulk-delete-btn')) {
                    console.log('🗑️ Botón de eliminar clickeado');
                    const checked = document.querySelectorAll('.order-checkbox:checked');
                    console.log('📊 Pedidos seleccionados para eliminar:', checked.length);
                    
                    if (checked.length > 0) {
                        if (confirm(`¿Estás seguro de que quieres eliminar ${checked.length} pedidos? Esta acción no se puede deshacer.`)) {
                            console.log('✅ Usuario confirmó eliminación');
                            
                            // Obtener IDs de los pedidos seleccionados
                            const orderIds = Array.from(checked).map(cb => cb.value);
                            console.log('📋 IDs de pedidos a eliminar:', orderIds);
                            
                            // Mostrar loading en el botón
                            const deleteBtn = e.target.closest('#bulk-delete-btn') || e.target;
                            const originalText = deleteBtn.innerHTML;
                            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Eliminando...';
                            deleteBtn.disabled = true;
                            
                            // Enviar petición AJAX
                            fetch('/admin/orders/bulk-delete', {
                                method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                    order_ids: orderIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                                console.log('📥 Respuesta del servidor:', data);
                                
                            if (data.success) {
                                    showNotification(data.message || `${data.deleted_count} pedidos eliminados correctamente`, 'success');
                                    // Recargar la página para actualizar la lista
                                    setTimeout(() => location.reload(), 1000);
                            } else {
                                    showNotification(data.error || 'Error al eliminar pedidos', 'error');
                                    // Restaurar botón
                                    deleteBtn.innerHTML = originalText;
                                    deleteBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                                console.error('❌ Error en la petición:', error);
                                showNotification('Error al eliminar pedidos', 'error');
                                // Restaurar botón
                                deleteBtn.innerHTML = originalText;
                                deleteBtn.disabled = false;
                            });
                            } else {
                            console.log('❌ Usuario canceló eliminación');
                        }
                    } else {
                        console.log('⚠️ No hay pedidos seleccionados para eliminar');
                    }
                }
            });
            
            console.log('✅ Checkboxes inicializados correctamente');
        }
        
        // Event listeners para el modal de cambio de estado masivo
        document.addEventListener('DOMContentLoaded', function() {
            // Habilitar/deshabilitar botón de confirmación según selección de estado
            const statusSelect = document.getElementById('bulk-status-select');
            const confirmBtn = document.getElementById('confirm-bulk-status');
            
            if (statusSelect && confirmBtn) {
                statusSelect.addEventListener('change', function() {
                    confirmBtn.disabled = !this.value;
                });
            }
            
            // Contador de caracteres para el motivo
            const reasonTextarea = document.getElementById('bulk-status-reason');
            const charCount = document.getElementById('bulk-reason-char-count');
            
            if (reasonTextarea && charCount) {
                reasonTextarea.addEventListener('input', function() {
                    const count = this.value.length;
                    charCount.textContent = count;
                    
                    // Cambiar color si se acerca al límite
                    if (count > 450) {
                        charCount.style.color = '#dc3545'; // Rojo
                    } else if (count > 400) {
                        charCount.style.color = '#fd7e14'; // Naranja
                    } else {
                        charCount.style.color = '#6c757d'; // Gris
                    }
                });
            }
            
            // Event listener para confirmar cambio de estado masivo
            document.addEventListener('click', function(e) {
                if (e.target.id === 'confirm-bulk-status') {
                    console.log('✅ Confirmando cambio de estado masivo');
                    
                    const checked = document.querySelectorAll('.order-checkbox:checked');
                    const newStatus = document.getElementById('bulk-status-select').value;
                    const reason = document.getElementById('bulk-status-reason').value.trim();
                    
                    if (checked.length === 0 || !newStatus) {
                        console.log('⚠️ No hay pedidos seleccionados o estado no seleccionado');
                        return;
                    }
                    
                    // Obtener IDs de los pedidos
                    const orderIds = Array.from(checked).map(cb => cb.value);
                    console.log('📋 IDs de pedidos a modificar:', orderIds);
                    console.log('📝 Nuevo estado:', newStatus);
                    
                    // Mostrar loading en el botón
                    const originalText = e.target.innerHTML;
                    e.target.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';
                    e.target.disabled = true;
                    
                    // Preparar datos para enviar
                    const requestData = {
                        order_ids: orderIds,
                        status: newStatus
                    };
                    
                    if (reason) {
                        requestData.reason = reason;
                    }
                    
                    // Enviar petición AJAX para cada pedido
                    let completed = 0;
                    let errors = 0;
                    
                    orderIds.forEach((orderId, index) => {
                        setTimeout(() => {
                            fetch(`/admin/orders/${orderId}/status`, {
                                method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                                    status: newStatus,
                                    reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                                completed++;
                                console.log(`✅ Pedido ${orderId} actualizado:`, data);
                                
                                // Verificar si el microturno está lleno
                                if (data.microturno_lleno) {
                                    console.log(`⚠️ Microturno lleno para pedido ${orderId}`);
                                    // Mostrar modal de confirmación para microturno lleno
                                    showMicroturnoFullModal(orderId, newStatus, reason, data);
                                    return;
                                }
                                
                                if (completed === orderIds.length) {
                                    // Todos los pedidos procesados
                                    if (errors === 0) {
                                        showNotification(`Estado actualizado correctamente para ${orderIds.length} pedidos`, 'success');
                                        // Cerrar modal y recargar página
                                        bootstrap.Modal.getInstance(document.getElementById('bulkStatusModal')).hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                                        showNotification(`Se actualizaron ${completed - errors} de ${orderIds.length} pedidos. ${errors} fallaron.`, 'warning');
                                        e.target.innerHTML = originalText;
                                        e.target.disabled = false;
                                    }
                    }
                })
                .catch(error => {
                                errors++;
                                completed++;
                                console.error(`❌ Error actualizando pedido ${orderId}:`, error);
                                
                                if (completed === orderIds.length) {
                                    if (errors === orderIds.length) {
                                        showNotification('Error al actualizar los pedidos', 'error');
                                    } else {
                                        showNotification(`Se actualizaron ${completed - errors} de ${orderIds.length} pedidos. ${errors} fallaron.`, 'warning');
                                    }
                                    e.target.innerHTML = originalText;
                                    e.target.disabled = false;
                                }
                            });
                        }, index * 100); // Pequeño delay entre peticiones
                    });
                }
            });
        });

        // Funciones básicas necesarias
        function printOrder(orderId) {
            window.open(`/admin/orders/${orderId}/print`, '_blank');
        }

        function markReviewPrompted(orderId) {
            fetch(`/admin/orders/${orderId}/mark-review-prompted`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    const btn = document.getElementById('review-btn-' + orderId);
                    if (btn) {
                        btn.classList.add('disabled', 'text-muted');
                        btn.style.pointerEvents = 'none';
                        btn.innerHTML = '<i class="fas fa-check me-2"></i>Reseña solicitada';
                    }
                }
            }).catch(() => {});
        }

        let _awardLoyaltyTargetId = null;
        let _awardLoyaltyBtnElement = null;

        function openAwardLoyaltyModal(orderId) {
            _awardLoyaltyTargetId = orderId;
            // Find the clicked button by searching for the one with the matching onclick
            _awardLoyaltyBtnElement = document.querySelector(`button[onclick="openAwardLoyaltyModal(${orderId})"]`);
            const modalEl = document.getElementById('awardLoyaltyModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        async function awardOrderLoyalty(orderId, btnElement) {
            if (!btnElement) return;
            const overlay = btnElement.closest('.loyalty-blur-overlay');
            btnElement.style.pointerEvents = 'none';
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Repartiendo...';
            try {
                const res = await fetch(`/admin/orders/${orderId}/award-loyalty`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    // Breve tilde verde antes de desaparecer
                    btnElement.innerHTML = '<i class="fas fa-check me-1"></i>Listo!';
                    btnElement.style.background = 'linear-gradient(135deg, #198754 0%, #146c43 100%) !important';
                    setTimeout(() => {
                        if (overlay) {
                            overlay.style.transition = 'opacity 0.4s ease';
                            overlay.style.opacity = '0';
                            setTimeout(() => overlay.remove(), 400);
                        }
                        showNotification(data.message, 'success');
                    }, 800);
                } else {
                    showNotification(data.message || 'No se pudieron repartir figuritas.', 'danger');
                    btnElement.innerHTML = '<i class="fas fa-envelope-open-text me-1"></i><span>Repartir figuritas</span>';
                    btnElement.style.pointerEvents = '';
                }
            } catch (err) {
                showNotification('Error de red al repartir figuritas.', 'danger');
                btnElement.innerHTML = '<i class="fas fa-envelope-open-text me-1"></i><span>Repartir figuritas</span>';
                btnElement.style.pointerEvents = '';
            }
        }

        // Confirm button in modal
        document.getElementById('confirm-award-loyalty').addEventListener('click', function() {
            const modalEl = document.getElementById('awardLoyaltyModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            if (_awardLoyaltyTargetId && _awardLoyaltyBtnElement) {
                awardOrderLoyalty(_awardLoyaltyTargetId, _awardLoyaltyBtnElement);
            }
        });

        function showNotification(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.style.minWidth = '350px';
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

        // Función para mostrar modal de microturno lleno
        function showMicroturnoFullModal(orderId, newStatus, reason, data) {
            // Crear modal dinámicamente
            const modalHtml = `
                <div class="modal fade" id="microturnoFullModal" tabindex="-1" aria-labelledby="microturnoFullModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title" id="microturnoFullModalLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Microturno Completo
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Advertencia:</strong> El microturno está completo y no puede aceptar más pedidos.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Capacidad Máxima:</h6>
                                        <p class="text-muted">${data.capacidad_maxima}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Pedidos Activos:</h6>
                                        <p class="text-muted">${data.pedidos_activos}</p>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <h6>Detalles:</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Hamburguesas:</strong> ${data.detalles.hamburguesas_actuales} actuales + ${data.detalles.hamburguesas_pedido} del pedido = ${data.detalles.hamburguesas_total_despues} (máximo: ${data.detalles.hamburguesas_maximo})</li>
                                        <li><strong>Acompañamientos:</strong> ${data.detalles.acompañamientos_actuales} actuales + ${data.detalles.acompañamientos_pedido} del pedido = ${data.detalles.acompañamientos_total_despues} (máximo: ${data.detalles.acompañamientos_maximo})</li>
                                    </ul>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>¿Desea confirmar el pedido de todas maneras?</strong><br>
                                    Esto agregará el pedido al microturno aunque exceda la capacidad máxima.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </button>
                                <button type="button" class="btn btn-warning" id="confirmExceedCapacity">
                                    <i class="fas fa-check me-1"></i>Sí, confirmar de todas maneras
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal existente si existe
            const existingModal = document.getElementById('microturnoFullModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Agregar modal al DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('microturnoFullModal'));
            modal.show();
            
            // Event listener para confirmar exceder capacidad
            document.getElementById('confirmExceedCapacity').addEventListener('click', function() {
                console.log('✅ Usuario confirmó exceder capacidad para pedido', orderId);
                
                // Cerrar modal
                modal.hide();
                
                // Enviar petición con force_exceed_capacity = true
                fetch(`/admin/orders/${orderId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        reason: reason,
                        force_exceed_capacity: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Pedido confirmado exitosamente (excediendo capacidad)', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error al confirmar pedido: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al confirmar pedido', 'error');
                });
            });
        }

        // ===== SISTEMA DE AUTO-SAVE PARA CAMPOS EDITABLES =====
        let saveTimeouts = new Map(); // Para manejar debounce por campo
        let hasUnsavedChanges = false; // Flag para detectar cambios pendientes

        // Función para mostrar indicador de guardado
        function showSaveIndicator(element, status) {
            const indicator = element.parentNode.querySelector('.save-indicator');
            if (!indicator) return;

            indicator.className = 'save-indicator';
            switch(status) {
                case 'saving':
                    indicator.innerHTML = '<i class="fas fa-spinner fa-spin text-warning"></i>';
                    indicator.className += ' text-warning';
                    break;
                case 'saved':
                    indicator.innerHTML = '<i class="fas fa-check text-success"></i>';
                    indicator.className += ' text-success';
                    setTimeout(() => {
                        indicator.innerHTML = '';
                        indicator.className = 'save-indicator';
                    }, 2000);
                    break;
                case 'error':
                    indicator.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i>';
                    indicator.className += ' text-danger';
                    setTimeout(() => {
                        indicator.innerHTML = '';
                        indicator.className = 'save-indicator';
                    }, 3000);
                    break;
            }
        }

        // Función para agregar indicador de guardado a un campo
        function addSaveIndicator(element) {
            if (element.parentNode.querySelector('.save-indicator')) return;
            
            const indicator = document.createElement('span');
            indicator.className = 'save-indicator position-absolute';
            indicator.style.right = '5px';
            indicator.style.top = '50%';
            indicator.style.transform = 'translateY(-50%)';
            indicator.style.fontSize = '12px';
            
            element.parentNode.style.position = 'relative';
            element.parentNode.appendChild(indicator);
        }

        // Función principal de auto-save
        function autoSaveField(orderId, field, value, element) {
            // Limpiar timeout anterior si existe
            const timeoutKey = `${orderId}-${field}`;
            if (saveTimeouts.has(timeoutKey)) {
                clearTimeout(saveTimeouts.get(timeoutKey));
            }

            // Mostrar indicador de guardando
            showSaveIndicator(element, 'saving');
            hasUnsavedChanges = true;

            // Configurar nuevo timeout con debounce de 500ms
            const timeout = setTimeout(async () => {
                try {
                    console.log(`💾 Auto-guardando campo ${field} para pedido ${orderId}:`, value);

                    const response = await fetch(`/admin/orders/${orderId}/details`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            [field]: value
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showSaveIndicator(element, 'saved');
                        hasUnsavedChanges = false;
                        console.log(`✅ Campo ${field} guardado exitosamente`);
                        
                        // Mostrar toast de confirmación solo para cambios importantes
                        if (['status', 'payment_status'].includes(field)) {
                            showNotification(`Campo ${field} actualizado correctamente`, 'success');
                        }
                    } else {
                        throw new Error(data.message || 'Error al guardar');
                    }
                } catch (error) {
                    console.error(`❌ Error guardando campo ${field}:`, error);
                    showSaveIndicator(element, 'error');
                    showNotification(`Error al guardar ${field}: ${error.message}`, 'error');
                } finally {
                    saveTimeouts.delete(timeoutKey);
                }
            }, 500);

            saveTimeouts.set(timeoutKey, timeout);
        }

        // ===== Filtro por método de pago desde el encabezado de la columna Pago =====
        const PAGO_LABELS = { cash: 'Efectivo', transfer: 'Transferencia', card: 'Tarjeta' };
        const PAGO_CELL_TEXT = { cash: 'EFECTIVO', transfer: 'TRANSFERENCIA', card: 'TARJETA' };
        const PAGO_COL_INDEX = { 'orders-table': 4, 'orders-table-historico': 5 };

        function formatPagoMoney(n) {
            return '$' + new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        }

        // Suma ingresos y cuenta pedidos por método, incluyendo filas filtradas/paginadas
        function computePagoTotals(tableId) {
            const totals = { '': { sum: 0, count: 0 }, cash: { sum: 0, count: 0 }, transfer: { sum: 0, count: 0 }, card: { sum: 0, count: 0 } };
            let badges = [];
            if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().rows().nodes().to$().find('.payment-toggle').each(function() {
                    badges.push(this);
                });
            } else {
                badges = Array.from(document.querySelectorAll('#' + tableId + ' tbody .payment-toggle'));
            }
            badges.forEach(b => {
                const m = b.dataset.method || 'cash';
                const amt = parseFloat(b.dataset.amount || '0') || 0;
                totals[''].sum += amt;
                totals[''].count++;
                if (totals[m]) {
                    totals[m].sum += amt;
                    totals[m].count++;
                }
            });
            return totals;
        }

        // Al abrir el menú, mostrar el total de ingreso de cada método
        document.addEventListener('click', function(e) {
            const toggle = e.target.closest('.pago-filter-toggle');
            if (!toggle) return;
            const totals = computePagoTotals(toggle.dataset.table);
            const menu = toggle.parentElement.querySelector('.pago-filter-menu');
            if (!menu) return;
            menu.querySelectorAll('.pago-filter-option').forEach(opt => {
                const t = totals[opt.dataset.method] || { sum: 0, count: 0 };
                const span = opt.querySelector('.pago-total');
                if (span) span.textContent = `${t.count} · ${formatPagoMoney(t.sum)}`;
            });
        });

        // Al elegir una opción, filtrar la tabla y mostrar el total del grupo en el encabezado
        document.addEventListener('click', function(e) {
            const opt = e.target.closest('.pago-filter-option');
            if (!opt) return;
            e.preventDefault();
            const menu = opt.closest('.pago-filter-menu');
            const tableId = menu.dataset.table;
            const method = opt.dataset.method;

            if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable()
                    .column(PAGO_COL_INDEX[tableId])
                    .search(method ? PAGO_CELL_TEXT[method] : '')
                    .draw();
            } else {
                // Fallback sin DataTables: ocultar filas manualmente
                document.querySelectorAll('#' + tableId + ' tbody tr').forEach(tr => {
                    const b = tr.querySelector('.payment-toggle');
                    tr.style.display = (!method || (b && b.dataset.method === method)) ? '' : 'none';
                });
            }

            // Encabezado: "Pago" o "Efectivo · $total (n)"
            const totals = computePagoTotals(tableId);
            document.querySelectorAll(`.pago-filter-toggle[data-table="${tableId}"]`).forEach(tg => {
                if (method) {
                    const t = totals[method] || { sum: 0, count: 0 };
                    tg.innerHTML = `${PAGO_LABELS[method]} · ${formatPagoMoney(t.sum)} (${t.count}) <i class="fas fa-caret-down"></i>`;
                    tg.classList.add('text-primary', 'fw-bold');
                } else {
                    tg.innerHTML = 'Pago <i class="fas fa-caret-down"></i>';
                    tg.classList.remove('text-primary', 'fw-bold');
                }
            });
        });

        // Actualiza los badges de la columna Pago para un pedido
        function syncPaymentBadges(orderId, method) {
            const labels = { cash: 'EFECTIVO', transfer: 'TRANSFERENCIA', card: 'TARJETA' };
            document.querySelectorAll(`.payment-toggle[data-order-id="${orderId}"]`).forEach(b => {
                b.dataset.method = method;
                b.textContent = labels[method] || 'EFECTIVO';
                b.classList.remove('bg-success', 'bg-info', 'text-dark', 'bg-secondary');
                if (method === 'transfer') {
                    b.classList.add('bg-info', 'text-dark');
                } else if (method === 'card') {
                    b.classList.add('bg-secondary');
                } else {
                    b.classList.add('bg-success');
                }
            });

            // Refrescar caché de DataTables para que el filtro de la columna Pago siga funcionando
            if (window.jQuery && $.fn.DataTable) {
                ['orders-table', 'orders-table-historico'].forEach(id => {
                    if ($.fn.DataTable.isDataTable('#' + id)) {
                        $('#' + id).DataTable().rows().invalidate();
                    }
                });
            }
        }

        // Reflejar en el badge los cambios de forma de pago hechos desde el modal
        document.addEventListener('change', function(e) {
            if (!e.target.matches('select.order-edit[data-field="payment_method"]')) return;
            const modalEl = e.target.closest('.modal');
            if (!modalEl || !modalEl.id.startsWith('modalOrder')) return;
            syncPaymentBadges(modalEl.id.replace('modalOrder', ''), e.target.value);
        });

        // Toggle rápido de método de pago (Efectivo <-> Transferencia) desde la columna Pago
        document.addEventListener('click', async function(e) {
            const badge = e.target.closest('.payment-toggle');
            if (!badge) return;

            const currentMethod = badge.dataset.method;
            if (currentMethod === 'card') return; // Tarjeta se edita desde el modal
            if (badge.dataset.saving === '1') return;

            const orderId = badge.dataset.orderId;
            const newMethod = currentMethod === 'transfer' ? 'cash' : 'transfer';
            badge.dataset.saving = '1';
            badge.style.opacity = '0.5';

            try {
                const response = await fetch(`/admin/orders/${orderId}/details`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ payment_method: newMethod })
                });

                const data = await response.json();
                if (!data.success) throw new Error(data.message || 'Error al guardar');

                // Actualizar todos los badges de este pedido (hoy + histórico)
                syncPaymentBadges(orderId, newMethod);

                // Sincronizar el select del modal para no quedar desactualizado
                const modalSelect = document.querySelector(`#modalOrder${orderId} select[data-field="payment_method"]`);
                if (modalSelect) modalSelect.value = newMethod;

                showNotification(`Método de pago cambiado a ${newMethod === 'transfer' ? 'Transferencia' : 'Efectivo'}`, 'success');
            } catch (error) {
                console.error('❌ Error cambiando método de pago:', error);
                showNotification(`Error al cambiar método de pago: ${error.message}`, 'error');
            } finally {
                delete badge.dataset.saving;
                badge.style.opacity = '';
            }
        });

        // Función para manejar campos de dirección (nested)
        function autoSaveAddressField(orderId, field, value, element) {
            const timeoutKey = `${orderId}-address-${field}`;
            if (saveTimeouts.has(timeoutKey)) {
                clearTimeout(saveTimeouts.get(timeoutKey));
            }

            showSaveIndicator(element, 'saving');
            hasUnsavedChanges = true;

            const timeout = setTimeout(async () => {
                try {
                    console.log(`💾 Auto-guardando dirección ${field} para pedido ${orderId}:`, value);

                    const response = await fetch(`/admin/orders/${orderId}/details`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            address: {
                                [field]: value
                            }
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showSaveIndicator(element, 'saved');
                        hasUnsavedChanges = false;
                        console.log(`✅ Dirección ${field} guardada exitosamente`);
                    } else {
                        throw new Error(data.message || 'Error al guardar');
                    }
                } catch (error) {
                    console.error(`❌ Error guardando dirección ${field}:`, error);
                    showSaveIndicator(element, 'error');
                    showNotification(`Error al guardar dirección: ${error.message}`, 'error');
                } finally {
                    saveTimeouts.delete(timeoutKey);
                }
            }, 500);

            saveTimeouts.set(timeoutKey, timeout);
        }

        // Función para guardar todos los campos de un pedido (botón Guardar manual)
        async function saveOrderDetails(orderId, buttonElement) {
            const modal = buttonElement.closest('.modal');
            const fields = modal.querySelectorAll('.order-edit');
            
            // Mostrar loading en el botón
            const originalText = buttonElement.innerHTML;
            buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
            buttonElement.disabled = true;

            try {
                const data = {};
                const addressData = {};

                fields.forEach(field => {
                    const fieldName = field.getAttribute('data-field');
                    const value = field.value;

                    if (fieldName.startsWith('address.')) {
                        const addressField = fieldName.replace('address.', '');
                        addressData[addressField] = value;
                    } else {
                        data[fieldName] = value;
                    }
                });

                if (Object.keys(addressData).length > 0) {
                    data.address = addressData;
                }

                console.log(`💾 Guardando todos los campos para pedido ${orderId}:`, data);

                const response = await fetch(`/admin/orders/${orderId}/update-details`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Pedido actualizado correctamente', 'success');
                    hasUnsavedChanges = false;
                    
                    // Limpiar todos los timeouts pendientes para este pedido
                    for (const [key, timeout] of saveTimeouts.entries()) {
                        if (key.startsWith(`${orderId}-`)) {
                            clearTimeout(timeout);
                            saveTimeouts.delete(key);
                        }
                    }
                } else {
                    throw new Error(result.message || 'Error al guardar');
                }
            } catch (error) {
                console.error('❌ Error guardando pedido:', error);
                showNotification(`Error al guardar: ${error.message}`, 'error');
            } finally {
                buttonElement.innerHTML = originalText;
                buttonElement.disabled = false;
            }
        }

        // Event listeners para auto-save
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Inicializando sistema de auto-save...');

            // Agregar indicadores de guardado a todos los campos editables
            document.querySelectorAll('.order-edit').forEach(element => {
                addSaveIndicator(element);
            });

            // Event listeners para campos editables
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('order-edit')) {
                    const modalEl = e.target.closest('.modal');
                    if (!modalEl) return; // order-edit solo se auto-guarda dentro de un modal
                    const orderId = modalEl.id.replace('modalOrder', '');
                    const field = e.target.getAttribute('data-field');
                    const value = e.target.value;

                    if (field.startsWith('address.')) {
                        const addressField = field.replace('address.', '');
                        autoSaveAddressField(orderId, addressField, value, e.target);
                    } else {
                        autoSaveField(orderId, field, value, e.target);
                    }
                }
            });

            // Event listeners para selects (cambio inmediato)
            document.addEventListener('change', function(e) {
                // Excluir microturno-select del auto-save para evitar conflictos
                if (e.target.classList.contains('order-edit') && e.target.tagName === 'SELECT' && !e.target.classList.contains('microturno-select')) {
                    const modalEl = e.target.closest('.modal');
                    if (!modalEl) return; // order-edit solo se auto-guarda dentro de un modal
                    const orderId = modalEl.id.replace('modalOrder', '');
                    const field = e.target.getAttribute('data-field');
                    const value = e.target.value;

                    // Para selects, guardar inmediatamente sin debounce
                    clearTimeout(saveTimeouts.get(`${orderId}-${field}`));
                    
                    if (field.startsWith('address.')) {
                        const addressField = field.replace('address.', '');
                        autoSaveAddressField(orderId, addressField, value, e.target);
                    } else {
                        autoSaveField(orderId, field, value, e.target);
                    }
                }
            });

            // Prevenir pérdida de datos al cerrar/recargar página
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    const message = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
                    e.returnValue = message;
                    return message;
                }
            });

            // Limpiar flag al cerrar modales
            document.addEventListener('hidden.bs.modal', function(e) {
                if (e.target.classList.contains('modal')) {
                    hasUnsavedChanges = false;
                    
                    // Limpiar timeouts pendientes
                    const orderId = e.target.id.replace('modalOrder', '');
                    for (const [key, timeout] of saveTimeouts.entries()) {
                        if (key.startsWith(`${orderId}-`)) {
                            clearTimeout(timeout);
                            saveTimeouts.delete(key);
                        }
                    }
                }
            });

            console.log('✅ Sistema de auto-save inicializado correctamente');
        });

        // ===== MANEJO DE CAMBIOS DE MICROTURNO =====
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('microturno-select')) {
                // Prevenir que otros event listeners interfieran
                e.stopPropagation();
                e.preventDefault();
                
                const orderId = e.target.getAttribute('data-order-id');
                const microturnoSortOrder = e.target.value;
                
                console.log(`🕐 Cambiando microturno para pedido ${orderId}:`, microturnoSortOrder);
                
                // Mostrar loading en el select
                e.target.disabled = true;
                const originalValue = e.target.value;
                
                fetch(`/admin/orders/${orderId}/microturno`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        microturno_sort_order: microturnoSortOrder
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Microturno actualizado exitosamente');
                        showNotification('Horario actualizado correctamente', 'success');
                        
                        // No intentar actualizar el texto de la opción, solo mantener el valor seleccionado
                        console.log('Microturno guardado:', microturnoSortOrder);
                    } else {
                        throw new Error(data.message || 'Error al actualizar horario');
                    }
                })
                .catch(error => {
                    console.error('❌ Error actualizando microturno:', error);
                    showNotification(`Error al actualizar horario: ${error.message}`, 'error');
                    
                    // Revertir el valor
                    e.target.value = originalValue;
                })
                .finally(() => {
                    e.target.disabled = false;
                });
            }
        });

        // Guardar valor anterior del select ANTES de que cambie
        document.addEventListener('mousedown', function(e) {
            const sel = e.target.closest('.status-select');
            if (sel && !sel.disabled) sel.dataset.prevValue = sel.value;
        });

        // ===== MANEJO DE CAMBIOS DE ESTADO =====
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('status-select')) {
                const orderId     = e.target.getAttribute('data-order-id');
                const newStatus   = e.target.value;
                const prevValue   = e.target.dataset.prevValue ?? newStatus;

                console.log(`📝 Cambiando estado para pedido ${orderId}:`, prevValue, '→', newStatus);

                e.target.disabled = true;
                activeStatusUpdates++;

                fetch(`/admin/orders/${orderId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.microturno_lleno) {
                        console.log(`⚠️ Microturno lleno para pedido ${orderId}`);
                        showMicroturnoFullModal(orderId, newStatus, '', data);
                        // Revertir visualmente al valor anterior
                        const sel = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                        if (sel) sel.value = prevValue;
                        return;
                    }

                    if (data.success) {
                        console.log('✅ Estado actualizado exitosamente');
                        showNotification('Estado actualizado correctamente', 'success');

                        // Actualizar el select (puede ser uno nuevo si el DOM fue reconstruido por refresh)
                        const sel = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                        if (sel) {
                            sel.value = newStatus;
                            sel.dataset.prevValue = newStatus;
                        }

                        const row = document.querySelector(`.status-select[data-order-id="${orderId}"]`)?.closest('tr');
                        if (row) {
                            const statusBadge = row.querySelector('.status-badge');
                            if (statusBadge) {
                                statusBadge.textContent = data.status;
                                statusBadge.className = `status-badge badge bg-${getStatusColor(data.status)}`;
                            }

                            const horarioCell = row.querySelector('td:nth-child(5)');
                            if (horarioCell && ['confirmed','preparing','ready','delivered'].includes(newStatus)) {
                                const microturnoSelect = horarioCell.querySelector('.microturno-select');
                                if (microturnoSelect) {
                                    const selectedText = microturnoSelect.options[microturnoSelect.selectedIndex].text;
                                    horarioCell.innerHTML = `<span class="badge badge-schedule-professional">${selectedText}</span>`;
                                }
                            } else if (horarioCell && newStatus === 'pending') {
                                const badge = horarioCell.querySelector('.badge');
                                if (badge) setTimeout(() => window.location.reload(), 1000);
                            }
                        }
                    } else {
                        throw new Error(data.message || 'Error al actualizar estado');
                    }
                })
                .catch(error => {
                    console.error('❌ Error actualizando estado:', error);
                    showNotification(`Error al actualizar estado: ${error.message}`, 'error');
                    // Revertir al valor anterior confirmado
                    const sel = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                    if (sel) sel.value = prevValue;
                })
                .finally(() => {
                    activeStatusUpdates--;
                    const sel = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                    if (sel) sel.disabled = false;
                });
            }
        });

        // Función auxiliar para obtener color de estado
        function getStatusColor(status) {
            const colors = {
                'pending':    'warning',
                'confirmed':  'primary',
                'preparing':  'info',
                'ready':      'ready',
                'on_the_way': 'on-the-way',
                'delivered':  'success',
                'cancelled':  'danger'
            };
            return colors[status] || 'secondary';
        }
    </script>

    <!-- Modal de confirmación para cambio de estado -->
    <div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusChangeModalLabel">Confirmar cambio de estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="statusChangeMessage">¿Está seguro de que desea cambiar el estado de este pedido?</p>
                    <div class="alert alert-warning" id="statusChangeWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Este pedido ya está confirmado. ¿Desea continuar con el cambio de estado?
                    </div>
                    
                    <div class="mt-3">
                        <label for="statusChangeReason" class="form-label">
                            <i class="fas fa-sticky-note me-1"></i>
                            Motivo del cambio (opcional)
                        </label>
                        <textarea 
                            class="form-control" 
                            id="statusChangeReason" 
                            rows="3" 
                            placeholder="Ej: Cliente canceló, problema con el pedido, cambio de horario, etc..."
                            maxlength="500"
                        ></textarea>
                        <div class="form-text">
                            <small class="text-muted">
                                <span id="reasonCharCount">0</span>/500 caracteres
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusChange">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambio de estado masivo -->
    <div class="modal fade" id="bulkStatusModal" tabindex="-1" aria-labelledby="bulkStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="bulkStatusModalLabel">
                        <i class="fas fa-edit me-2"></i>Cambiar Estado de Pedidos Seleccionados
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Información:</strong> Se cambiará el estado de <span id="bulk-status-count">0</span> pedidos seleccionados.
                    </div>
                    
                    <div class="mb-3">
                        <label for="bulk-status-select" class="form-label">Nuevo Estado:</label>
                        <select class="form-select" id="bulk-status-select">
                            <option value="">Selecciona un estado...</option>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmado</option>
                            <option value="preparing">En Preparación</option>
                            <option value="ready">Listo ✓</option>
                            <option value="on_the_way">En camino 🛵</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bulk-status-reason" class="form-label">Motivo del cambio (opcional):</label>
                        <textarea class="form-control" id="bulk-status-reason" rows="3" maxlength="500" placeholder="Explique brevemente el motivo del cambio de estado..."></textarea>
                        <div class="form-text">
                            <span id="bulk-reason-char-count">0</span>/500 caracteres
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Pedidos que se modificarán:</h6>
                        <div id="bulk-orders-list" class="list-group" style="max-height: 200px; overflow-y: auto;">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="confirm-bulk-status" disabled>
                        <i class="fas fa-check me-1"></i>Cambiar Estado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de decisión para pedidos con figuritas (cancelar/eliminar) -->
    <div class="modal fade" id="loyaltyDecisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b 0%,#facc15 100%);">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-coins me-2"></i>Decision sobre figuritas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="loyaltyDecisionText" class="mb-3"></p>
                    <div id="loyaltyDecisionOptions" class="d-flex flex-column gap-2"></div>
                    <input type="hidden" id="loyaltyDecisionOrderId">
                    <input type="hidden" id="loyaltyDecisionAction"> <!-- 'cancel' o 'delete' -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para verificar impacto de loyalty antes de cancelar/eliminar
        async function checkLoyaltyImpactAndDecide(orderId, action, orderNumber) {
            const modalEl = document.getElementById('loyaltyDecisionModal');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

            // Cargando
            document.getElementById('loyaltyDecisionText').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Consultando figuritas...';
            document.getElementById('loyaltyDecisionOptions').innerHTML = '';
            document.getElementById('loyaltyDecisionOrderId').value = orderId;
            document.getElementById('loyaltyDecisionAction').value = action;
            modal.show();

            try {
                const res = await fetch(`/admin/orders/${orderId}/loyalty-impact`);
                const data = await res.json();
                if (!data.success) throw new Error('Error');

                const impact = data.impact;
                if (!impact.has_stickers) {
                    // Sin figuritas → ejecutar directo
                    modal.hide();
                    executeAction(orderId, action, 'keep');
                    return;
                }

                // Con figuritas → mostrar opciones
                const txt = document.getElementById('loyaltyDecisionText');
                const opts = document.getElementById('loyaltyDecisionOptions');

                txt.innerHTML = `Este pedido otorgó <strong>${impact.stickers_amount} figurita${impact.stickers_amount>1?'s':''}</strong> al cliente.<br>
                    Estado actual del álbum: <strong>${impact.wallet_current} figuritas</strong>.`;

                let buttons = `
                    <button class="btn btn-success text-start" onclick="executeAction(${orderId}, '${action}', 'revert')">
                        <i class="fas fa-undo me-2"></i>Devolver ${impact.stickers_amount} figurita${impact.stickers_amount>1?'s':''} al cliente
                        <div class="small text-white-50">Recomendado: el cliente pierde las figuritas que ganó con este pedido</div>
                    </button>
                `;

                if (impact.has_pending_redemption) {
                    buttons += `
                        <button class="btn btn-warning text-start" onclick="executeAction(${orderId}, '${action}', 'revert_and_cancel_redemption')">
                            <i class="fas fa-undo-alt me-2"></i>Devolver figuritas + cancelar canje pendiente
                            <div class="small text-dark">El cliente tiene un canje pendiente. Se cancelará y se devolverán las figuritas.</div>
                        </button>
                    `;
                }

                buttons += `
                    <button class="btn btn-outline-secondary text-start" onclick="executeAction(${orderId}, '${action}', 'keep')">
                        <i class="fas fa-hand-paper me-2"></i>No tocar figuritas
                        <div class="small text-muted">Solo ${action==='cancel'?'cancelar':'eliminar'} el pedido, dejar las figuritas como están</div>
                    </button>
                `;

                opts.innerHTML = buttons;
            } catch (e) {
                document.getElementById('loyaltyDecisionText').innerHTML = 'No se pudo consultar el impacto. ¿Querés continuar igual?';
                document.getElementById('loyaltyDecisionOptions').innerHTML = `
                    <button class="btn btn-primary" onclick="executeAction(${orderId}, '${action}', 'keep')">Continuar sin verificar</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                `;
            }
        }

        // Ejecutar la acción (cancelar o eliminar) con la estrategia elegida
        async function executeAction(orderId, action, strategy) {
            const modalEl = document.getElementById('loyaltyDecisionModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            if (action === 'delete') {
                // Eliminar individual
                try {
                    const res = await fetch(`/admin/orders/${orderId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ loyalty_strategy: strategy })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showNotification('Pedido eliminado', 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showNotification(data.error || 'Error al eliminar', 'error');
                    }
                } catch (e) {
                    showNotification('Error de conexión', 'error');
                }
            } else if (action === 'cancel') {
                // Actualizar estado a cancelled
                try {
                    const res = await fetch(`/admin/orders/${orderId}/status`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ status: 'cancelled', loyalty_strategy: strategy })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showNotification('Pedido cancelado', 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showNotification(data.error || 'Error al cancelar', 'error');
                    }
                } catch (e) {
                    showNotification('Error de conexión', 'error');
                }
            }
        }

        // Hook para el modal de eliminación individual (sobreescribir comportamiento)
        document.addEventListener('DOMContentLoaded', function() {
            // Interceptar clicks en "Eliminar" de los modales individuales
            document.querySelectorAll('[data-bs-target^="#modalDeleteOrder"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const modalId = this.getAttribute('data-bs-target');
                    const orderId = modalId.replace('#modalDeleteOrder', '');
                    const orderNumber = this.closest('tr')?.querySelector('td:nth-child(3)')?.textContent?.trim() || orderId;
                    checkLoyaltyImpactAndDecide(orderId, 'delete', orderNumber);
                });
            });
        });

        // Eliminar paginación y select al final de la página
        document.addEventListener('DOMContentLoaded', function() {
            // ELIMINAR SVG ESPECÍFICOS CON ESOS PATHS
            function eliminarSVGProblematico() {
                const svgs = document.querySelectorAll('svg');
                svgs.forEach(svg => {
                    const viewBox = svg.getAttribute('viewBox');
                    if (viewBox === '0 0 17 17') {
                        const path1 = svg.querySelector('path[d*="M5.207 8.471"]');
                        const path2 = svg.querySelector('path[d*="M13.207 8.472"]');
                        if (path1 || path2) {
                            svg.remove();
                        }
                    }
                    // También buscar por el path directamente sin importar el viewBox
                    const path1 = svg.querySelector('path[d*="M5.207 8.471"]');
                    const path2 = svg.querySelector('path[d*="M13.207 8.472"]');
                    if (path1 || path2) {
                        svg.remove();
                    }
                });
            }
            
            // Ejecutar inmediatamente
            eliminarSVGProblematico();
            
            // Ejecutar después de un pequeño delay para elementos que se cargan después
            setTimeout(eliminarSVGProblematico, 100);
            setTimeout(eliminarSVGProblematico, 500);
            setTimeout(eliminarSVGProblematico, 1000);
            
            // ELIMINAR ELEMENTOS DE FLATPICKR
            function eliminarFlatpickr() {
                // Eliminar elementos específicos de flatpickr
                const flatpickrCurrentMonth = document.querySelector('.flatpickr-current-month');
                if (flatpickrCurrentMonth) {
                    flatpickrCurrentMonth.remove();
                }
                
                const dayContainer = document.querySelector('.dayContainer');
                if (dayContainer) {
                    dayContainer.remove();
                }
                
                const weekdayContainer = document.querySelector('.flatpickr-weekdaycontainer');
                if (weekdayContainer) {
                    weekdayContainer.remove();
                }
                
                // Eliminar todo el contenedor de flatpickr si existe
                const flatpickrCalendar = document.querySelector('.flatpickr-calendar');
                if (flatpickrCalendar && flatpickrCalendar.closest('.modal') === null) {
                    flatpickrCalendar.remove();
                }
            }
            
            // Observar cambios en el DOM para eliminar SVG y flatpickr que aparezcan después
            const observer = new MutationObserver(function(mutations) {
                eliminarSVGProblematico();
                eliminarFlatpickr();
            });
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Ejecutar eliminación de flatpickr
            eliminarFlatpickr();
            setTimeout(eliminarFlatpickr, 100);
            setTimeout(eliminarFlatpickr, 500);
            setTimeout(eliminarFlatpickr, 1000);
            
            // Eliminar elementos de paginación
            const paginations = document.querySelectorAll('.pagination, .pagination-modern, nav[aria-label*="pagination"], nav[aria-label*="Pagination"]');
            paginations.forEach(p => {
                if (p.closest('.modal') === null) {
                    p.remove();
                }
            });
            
            // Eliminar select de "per page" o similar
            const selects = document.querySelectorAll('select[class*="per"], select[name*="per"], select[id*="per"], select[class*="page"], select[name*="page"]');
            selects.forEach(s => {
                if (s.closest('.modal') === null && s.closest('.table') === null && s.closest('.card-body') === null) {
                    s.parentElement?.remove();
                }
            });
            
            // Eliminar cualquier elemento con clase pagination que esté al final del body
            const allPagination = document.querySelectorAll('.pagination, [class*="pagination"]');
            allPagination.forEach(el => {
                if (el.closest('.modal') === null) {
                    const rect = el.getBoundingClientRect();
                    // Si está cerca del final de la página (últimos 200px)
                    if (rect.top > window.innerHeight - 200) {
                        el.remove();
                    }
                }
            });
        });

        // ===== DATATABLES INITIALIZATION =====
        // Load jQuery first (required by DataTables)
        if (typeof jQuery === 'undefined') {
            const jqueryScript = document.createElement('script');
            jqueryScript.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
            jqueryScript.onload = function() {
                loadDataTables();
            };
            document.head.appendChild(jqueryScript);
        } else {
            loadDataTables();
        }

        function loadDataTables() {
            // Load DataTables libraries
            const dataTablesScript = document.createElement('script');
            dataTablesScript.src = 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js';
            dataTablesScript.onload = function() {
                const bootstrap5Script = document.createElement('script');
                bootstrap5Script.src = 'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js';
                bootstrap5Script.onload = function() {
                    const responsiveScript = document.createElement('script');
                    responsiveScript.src = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
                    responsiveScript.onload = function() {
                        const responsiveBootstrap5Script = document.createElement('script');
                        responsiveBootstrap5Script.src = 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js';
                        responsiveBootstrap5Script.onload = function() {
                            initializeDataTables();
                        };
                        document.head.appendChild(responsiveBootstrap5Script);
                    };
                    document.head.appendChild(responsiveScript);
                };
                document.head.appendChild(bootstrap5Script);
            };
            document.head.appendChild(dataTablesScript);
        }

        function initializeDataTables() {
            // Initialize today's orders table
            if ($('#orders-table').length) {
                $('#orders-table').DataTable({
                    responsive: false,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    columnDefs: [
                        { responsivePriority: 1, targets: 0 }, // Checkbox
                        { responsivePriority: 2, targets: 1 }, // Cliente
                        { responsivePriority: 3, targets: 7 }, // Acciones
                        { responsivePriority: 4, targets: 6 }, // Estado
                        { responsivePriority: 5, targets: 2 }, // Items
                        { responsivePriority: 6, targets: 3 }, // Total
                        { responsivePriority: 7, targets: 5 }, // Horario
                        { responsivePriority: 8, targets: 4, orderable: false }  // Pago
                    ],
                    order: [],
                    dom: 'rt',
                    paging: false
                });
            }

            // Initialize historical orders table
            if ($('#orders-table-historico').length) {
                var isMobile = window.innerWidth <= 768;
                $('#orders-table-historico').DataTable({
                    responsive: false,
                    pageLength: -1,
                    lengthMenu: [[-1, 25, 50, 100], ['Todos', 25, 50, 100]],
                    language: {
                        lengthMenu: 'Mostrar _MENU_ registros',
                        search: 'Buscar:',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ pedidos',
                        infoEmpty: 'Mostrando 0 a 0 de 0 pedidos',
                        infoFiltered: '(filtrado de _MAX_ pedidos en total)',
                        paginate: {
                            first: 'Primero',
                            last: 'Último',
                            next: 'Siguiente',
                            previous: 'Anterior'
                        },
                        zeroRecords: 'No se encontraron pedidos',
                        emptyTable: 'No hay pedidos anteriores'
                    },
                    columnDefs: [
                        { targets: 0, width: '50px' },
                        { targets: 1, width: '100px' },
                        { targets: 2, width: '200px' },
                        { targets: 3, width: '120px' },
                        { targets: 4, width: '120px' },
                        { targets: 5, width: '120px', orderable: false },
                        { targets: 6, width: '130px' },
                        { targets: 7, width: '100px' },
                        { targets: 8, width: '100px' }
                    ],
                    order: [[1, 'desc']],
                    dom: '<"dt-controls-top"lf>rt<"dt-controls-bottom"ip>',
                    scrollX: true,
                    autoWidth: false
                });
            }

            // Realinear columnas cada vez que el accordion abre
            var collapseEl = document.getElementById('collapsePedidos');
            if (collapseEl) {
                collapseEl.addEventListener('shown.bs.collapse', function() {
                    if ($.fn.DataTable.isDataTable('#orders-table-historico')) {
                        $('#orders-table-historico').DataTable().columns.adjust().draw(false);
                    }
                });
            }

            // Realinear columnas al cambiar tamaño/orientación
            window.addEventListener('resize', function() {
                if ($.fn.DataTable.isDataTable('#orders-table-historico')) {
                    $('#orders-table-historico').DataTable().columns.adjust();
                }
            });
        }
    </script>
@endpush
