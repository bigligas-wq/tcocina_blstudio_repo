@extends('layouts.admin')

@section('title', 'Dashboard - TCocina Admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="rocker-scope">
    <!-- Welcome Header Rocker -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card radius-10 border-0 welcome-header-compact" style="background: linear-gradient(135deg, #0096c7 0%, #0c6568 100%);">
                <div class="card-body text-white py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">Hola, {{ Auth::user()->name }}</h5>
                            <small class="opacity-75">Resumen de tu negocio</small>
                        </div>
                        <form id="dateRangeForm" method="GET" action="{{ route('admin.dashboard') }}"
                              class="d-flex align-items-center gap-1 flex-wrap">
                            <small class="mb-0 text-white d-none d-md-block" style="white-space:nowrap;">Período:</small>
                            <input type="date" name="date_from" id="dateFrom"
                                   value="{{ $dateFrom }}"
                                   class="form-control form-control-sm date-range-input"
                                   style="width:130px;background:rgba(255,255,255,0.92);font-size:0.78rem;">
                            <small class="mb-0 text-white">al</small>
                            <input type="date" name="date_to" id="dateTo"
                                   value="{{ $dateTo }}"
                                   class="form-control form-control-sm date-range-input"
                                   style="width:130px;background:rgba(255,255,255,0.92);font-size:0.78rem;">
                            <button type="submit" class="btn btn-sm"
                                    style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.45);font-size:0.78rem;white-space:nowrap;padding:4px 10px;">
                                <i class="fas fa-search" style="font-size:0.7rem;"></i>
                                <span class="d-none d-sm-inline ms-1">Aplicar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Rocker -->
    <div class="row mb-4 stats-cards-container row-cols-1 row-cols-md-2 row-cols-xl-5 g-2">
        <div class="col stats-card-item">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="card radius-10 border-start border-0 border-4 border-info h-100 stat-rocker">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-card-text">
                                <p class="mb-0 text-secondary small">Total Pedidos</p>
                                <h4 class="my-1 text-info">{{ $stats['total_orders'] }}</h4>
                                <p class="mb-0 font-13 text-muted">Todos los pedidos</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto stat-card-icon">
                                <lord-icon src="/lordicons/carritolordicon.json" colors="primary:#ffffff,secondary:#ffffff" trigger="hover" style="width:28px;height:28px;"></lord-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col stats-card-item">
            <a href="#" class="text-decoration-none">
                <div class="card radius-10 border-start border-0 border-4 border-primary h-100 stat-rocker">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-card-text">
                                <p class="mb-0 text-secondary small">Usuarios</p>
                                <h4 class="my-1 text-primary">{{ $stats['total_customers'] }}</h4>
                                <p class="mb-0 font-13 text-muted">Registrados</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-cosmic text-white ms-auto stat-card-icon">
                                <lord-icon src="/lordicons/corona.json" colors="primary:#ffffff,secondary:#ffd700" trigger="hover" style="width:28px;height:28px;"></lord-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col stats-card-item">
            <a href="{{ route('admin.orders') }}?status=pending" class="text-decoration-none">
                <div class="card radius-10 border-start border-0 border-4 border-warning h-100 stat-rocker">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-card-text">
                                <p class="mb-0 text-secondary small">Pendientes</p>
                                <h4 class="my-1" style="color:#f76b27;">{{ $stats['pending_orders'] }}</h4>
                                <p class="mb-0 font-13 text-muted">Requieren atención</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto stat-card-icon">
                                <lord-icon src="/lordicons/notificaciones.json" colors="primary:#ffffff,secondary:#ff9900" trigger="hover" style="width:28px;height:28px;"></lord-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col stats-card-item">
            <a href="{{ route('admin.products') }}" class="text-decoration-none">
                <div class="card radius-10 border-start border-0 border-4 h-100 stat-rocker" style="border-color:#f7b82c !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-card-text">
                                <p class="mb-0 text-secondary small">Productos</p>
                                <h4 class="my-1" style="color:#f7b82c;">{{ $stats['total_products'] }}</h4>
                                <p class="mb-0 font-13 text-muted">En el menú</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-kyoto text-white ms-auto stat-card-icon">
                                <lord-icon src="/lordicons/cocina.json" colors="primary:#ffffff,secondary:#f9c74f" trigger="hover" style="width:28px;height:28px;"></lord-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col stats-card-item">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="card radius-10 border-start border-0 border-4 border-success h-100 stat-rocker">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-card-text" style="min-width:0;flex:1 1 0;">
                                <p class="mb-0 text-secondary small">Ingresos</p>
                                <h4 class="my-1 text-success revenue-value">${{ number_format($stats['total_revenue'], 2, ',', '.') }}</h4>
                                <p class="mb-0 font-13 text-muted" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white stat-card-icon" style="flex-shrink:0;">
                                <i class='bx bx-dollar-circle' style="font-size:28px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts Row Rocker -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7 d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0"><lord-icon src="/lordicons/Delivery.json" colors="primary:#0096c7,secondary:#0c6568" trigger="hover" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;"></lord-icon>Pedidos e Ingresos por Día</h6>
                        </div>
                        <div class="fs-6 ms-auto d-flex gap-2 flex-wrap">
                            <span class="border px-2 py-1 rounded" style="font-size:.75rem;">
                                <span style="display:inline-block;width:8px;height:8px;background:#0096c7;border-radius:50%;margin-right:4px;"></span>Pedidos
                            </span>
                            <span class="border px-2 py-1 rounded" style="font-size:.75rem;">
                                <span style="display:inline-block;width:8px;height:8px;background:#28a745;border-radius:50%;margin-right:4px;"></span>Ingresos
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-1">
                        <canvas id="dualChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5 d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0"><lord-icon src="/lordicons/Retiro.json" colors="primary:#6366f1,secondary:#4f46e5" trigger="hover" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;"></lord-icon>Estado de Pedidos</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Rocker -->
    <div class="row">
        <div class="col-12">
            <div class="card radius-10">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <lord-icon src="/lordicons/notificaciones.json" colors="primary:#0096c7,secondary:#0c6568" trigger="hover" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;"></lord-icon>Pedidos Recientes
                    </h6>
                    <a href="{{ route('admin.orders') }}" class="btn btn-sm text-white" style="background: linear-gradient(135deg, #0096c7 0%, #0c6568 100%); border: none;">
                        <lord-icon src="/lordicons/edit.json" colors="primary:#ffffff,secondary:#ffffff" trigger="hover" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;"></lord-icon>Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    @if ($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Pedido</th>
                                        <th class="border-0">Cliente</th>
                                        <th class="border-0">Total</th>
                                        <th class="border-0">Estado</th>
                                        <th class="border-0">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $order)
                                        <tr class="border-0">
                                            <td class="border-0">
                                                <strong style="color: #0096c7;">#{{ $order->order_number }}</strong>
                                            </td>
                                            <td class="border-0">
                                                @if($order->user && $order->user->name && trim($order->user->name) !== '')
                                                    {{ $order->user->name }}
                                                @elseif($order->contact_name && trim($order->contact_name) !== '')
                                                    {{ $order->contact_name }}
                                                @else
                                                    <span class="text-muted">Sin nombre</span>
                                                @endif
                                            </td>
                                            <td class="border-0 fw-bold text-success">${{ number_format($order->total_amount, 2, ',', '.') }}</td>
                                            <td class="border-0">
                                                <span class="badge rounded-pill px-3 py-2
                                                    @if($order->status === 'pending') text-white
                                                    @elseif($order->status === 'delivered') bg-success text-white
                                                    @elseif($order->status === 'confirmed') text-white
                                                    @elseif($order->status === 'preparing') text-white
                                                    @else bg-secondary text-white @endif"
                                                    style="@if($order->status === 'pending') background: linear-gradient(135deg, #f9844a 0%, #f76b27 100%);
                                                    @elseif($order->status === 'confirmed') background: linear-gradient(135deg, #0096c7 0%, #0c6568 100%);
                                                    @elseif($order->status === 'preparing') background: linear-gradient(135deg, #f9c74f 0%, #f7b82c 100%); @endif">
                                                    {{ $order->status_label }}
                                                </span>
                                            </td>
                                            <td class="border-0 text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class='bx bxs-cart text-muted mb-3' style="font-size:3rem;"></i>
                            <p class="text-muted">No hay pedidos recientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Boxicons (Rocker theme icons) -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
    /* ===== Rocker theme classes (scoped to .rocker-scope) ===== */
    .rocker-scope { font-family: Roboto, system-ui, sans-serif; }
    .rocker-scope .radius-10 { border-radius: 10px; }
    .rocker-scope .font-13 { font-size: 13px; }
    .rocker-scope .card { box-shadow: 0 0 5px rgba(0,0,0,.06), 0 0 1px rgba(0,0,0,.06); border: 1px solid rgba(0,0,0,.05); }
    .rocker-scope .card-header { background: transparent; border-bottom: 1px solid rgba(0,0,0,.06); padding: .75rem 1rem; }
    .rocker-scope .card-header h6 { font-weight: 600; }
    .rocker-scope .widgets-icons-2 {
        width: 56px; height: 56px;
        display: flex; align-items: center; justify-content: center;
        font-size: 27px; border-radius: 10px;
    }
    .rocker-scope .chart-container-1 { position: relative; height: 260px; }
    .rocker-scope .chart-container-2 { position: relative; height: 220px; }
    /* Hover lift on stat cards */
    .rocker-scope .stat-rocker { transition: transform .25s ease, box-shadow .25s ease; cursor: pointer; }
    .rocker-scope .stat-rocker:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.08); }
    .rocker-scope a { color: inherit; }
    /* Brand-aligned gradient overrides for the icon badges */
    .rocker-scope .bg-gradient-blues       { background: linear-gradient(135deg, #0096c7 0%, #0c6568 100%) !important; }
    .rocker-scope .bg-gradient-cosmic      { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; }
    .rocker-scope .bg-gradient-orange,
    .rocker-scope .bg-gradient-burning     { background: linear-gradient(135deg, #f9844a 0%, #f76b27 100%) !important; }
    .rocker-scope .bg-gradient-kyoto       { background: linear-gradient(135deg, #f9c74f 0%, #f7b82c 100%) !important; }
    .rocker-scope .bg-gradient-ohhappiness { background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important; }

    /* ===== Legacy mobile rules (kept for back-compat) ===== */
    .stat-card-modern {
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .stat-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }
    
    .stat-card-modern .card-body {
        padding: 1.5rem;
    }
    
    .text-white-50 {
        color: rgba(255, 255, 255, 0.5) !important;
    }
    
    .text-white-75 {
        color: rgba(255, 255, 255, 0.75) !important;
    }
    
    .card {
        border-radius: 15px;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge.rounded-pill {
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Mobile Responsive Styles - Solo para mobile */
    @media (max-width: 768px) {
        /* Rocker stats cards: stack icon position right, compact */
        .rocker-scope .widgets-icons-2 { width: 44px; height: 44px; font-size: 22px; }
        .rocker-scope .stats-cards-container { row-gap: .5rem; }
        .rocker-scope .stat-rocker .card-body { padding: .75rem; }
        .rocker-scope .stat-rocker h4 { font-size: 1.25rem; }
        /* Fix overflow del valor de ingresos en mobile */
        .rocker-scope .stat-rocker .stat-card-text { min-width: 0; flex: 1 1 0; }
        .rocker-scope .stat-rocker .revenue-value { font-size: clamp(0.85rem, 4vw, 1.25rem); overflow-wrap: break-word; word-break: break-all; }
        /* Inputs de fecha compactos en mobile */
        .date-range-input { width: 110px !important; font-size: 0.72rem !important; }
        .rocker-scope .stat-rocker .small,
        .rocker-scope .stat-rocker .font-13 { font-size: .7rem; }
        /* Header de bienvenida MUY compacto - casi invisible */
        .row.mb-4:first-child {
            margin-bottom: 0.5rem !important;
        }
        
        .row.mb-4:first-child .card-body {
            padding: 0.5rem 0.75rem !important;
        }
        
        .row.mb-4:first-child h3 {
            font-size: 0.9rem !important;
            margin-bottom: 0.1rem !important;
            line-height: 1.2 !important;
        }
        
        .row.mb-4:first-child p {
            display: none !important; /* Ocultar subtítulo en mobile */
        }
        
        .row.mb-4:first-child .d-flex {
            flex-direction: row;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.5rem !important;
        }
        
        .row.mb-4:first-child .form-label {
            display: none !important; /* Ocultar label "Período:" */
        }
        
        .row.mb-4:first-child .form-select {
            width: auto !important;
            min-width: 120px !important;
            font-size: 0.75rem !important;
            padding: 0.3rem 0.5rem !important;
        }

        /* Cards de estadísticas en scroll horizontal - MUY compactas */
        .stats-cards-container {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-bottom: 0.5rem;
            margin-bottom: 0.75rem !important;
        }
        
        .stats-cards-container::-webkit-scrollbar {
            height: 4px;
        }
        
        .stats-cards-container::-webkit-scrollbar-thumb {
            background: #0096c7;
            border-radius: 2px;
        }
        
        .stats-card-item {
            flex: 0 0 140px !important;
            max-width: 140px !important;
            margin-right: 0.5rem;
            margin-bottom: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        .stat-card-modern .card-body {
            padding: 0.75rem 0.5rem !important;
        }
        
        /* Optimizar textos - layout vertical compacto */
        .stat-card-modern .stat-card-content {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.3rem !important;
            position: relative;
        }
        
        .stat-card-modern .stat-card-text {
            width: 100%;
        }
        
        .stat-card-modern .stat-card-icon {
            position: absolute;
            top: 0;
            right: 0;
            opacity: 0.5;
        }
        
        .stat-card-modern .text-white-50.small {
            font-size: 0.65rem !important;
            margin-bottom: 0.15rem !important;
            line-height: 1.2 !important;
            display: block;
        }
        
        .stat-card-modern .h4 {
            font-size: 1.5rem !important;
            line-height: 1.1 !important;
            margin-bottom: 0.1rem !important;
            display: block;
        }
        
        .stat-card-modern .small.text-white-75 {
            font-size: 0.6rem !important;
            line-height: 1.2 !important;
            display: block;
            margin-top: 0 !important;
        }
        
        .stat-card-modern .modern-icon {
            width: 1.3rem !important;
            height: 1.3rem !important;
        }

        /* Gráficos a ancho completo - MÁS IMPORTANCIA Y ESPACIO */
        .row.mb-4:nth-child(3) {
            margin-bottom: 1rem !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        .row.mb-4:nth-child(3) .col-xl-8,
        .row.mb-4:nth-child(3) .col-xl-4,
        .row.mb-4:nth-child(3) .col-lg-7,
        .row.mb-4:nth-child(3) .col-lg-5 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            margin-bottom: 0.75rem;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        .row.mb-4:nth-child(3) .col-xl-4:last-child,
        .row.mb-4:nth-child(3) .col-lg-5:last-child {
            margin-bottom: 0;
        }
        
        .row.mb-4:nth-child(3) .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        }
        
        .row.mb-4:nth-child(3) .card-body {
            padding: 0.75rem !important;
        }
        
        .row.mb-4:nth-child(3) .card-header {
            padding: 0.6rem 0.75rem !important;
        }
        
        .row.mb-4:nth-child(3) .card-header h6 {
            font-size: 0.85rem !important;
            font-weight: 600 !important;
        }
        
        /* Gráficos más grandes y prominentes */
        .row.mb-4:nth-child(3) #dualChart {
            max-height: 300px !important;
            min-height: 280px !important;
        }
        
        .row.mb-4:nth-child(3) #statusChart {
            max-height: 280px !important;
            min-height: 260px !important;
        }

        /* Tabla de pedidos recientes más compacta */
        .row:last-child {
            margin-top: 0.5rem !important;
        }
        
        .row:last-child .card-body {
            padding: 0.6rem !important;
        }
        
        .row:last-child .table {
            font-size: 0.75rem;
        }
        
        .row:last-child .table th,
        .row:last-child .table td {
            padding: 0.4rem 0.3rem !important;
        }
        
        .row:last-child .table th {
            font-size: 0.65rem !important;
        }
        
        .row:last-child .badge {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        
        .row:last-child .card-header {
            padding: 0.6rem !important;
        }
        
        .row:last-child .card-header h6 {
            font-size: 0.8rem !important;
        }
        
        .row:last-child .btn-sm {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.4rem !important;
        }

        /* Ajustes generales para mobile */
        .main-content {
            padding: 0.5rem !important;
        }
        
        .mb-4 {
            margin-bottom: 0.75rem !important;
        }
    }
    
    /* LANDSCAPE MODE - Mobile horizontal - Dashboard */
    @media (max-width: 1024px) and (orientation: landscape) {
        /* Aprovechar todo el ancho disponible */
        .main-content {
            padding: 0.75rem !important;
            max-width: 100vw !important;
            overflow-x: hidden !important;
        }
        
        /* Header de bienvenida más compacto en landscape */
        .row.mb-4:first-child .card-body {
            padding: 0.5rem 0.75rem !important;
        }
        
        /* Cards de estadísticas - más espacio horizontal */
        .stats-cards-container {
            margin-bottom: 0.75rem !important;
        }
        
        .stats-card-item {
            flex: 0 0 180px !important;
            max-width: 180px !important;
        }
        
        .stat-card-modern .card-body {
            padding: 0.85rem 0.6rem !important;
        }
        
        .stat-card-modern .h4 {
            font-size: 1.6rem !important;
        }
        
        /* Gráficos más grandes en landscape */
        .row.mb-4:nth-child(3) #dualChart {
            max-height: 350px !important;
            min-height: 320px !important;
        }
        
        .row.mb-4:nth-child(3) #statusChart {
            max-height: 320px !important;
            min-height: 300px !important;
        }
        
        /* Gráficos lado a lado si hay espacio */
        .row.mb-4:nth-child(3) .col-xl-8,
        .row.mb-4:nth-child(3) .col-xl-4 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }
        
        /* Tabla más legible */
        .row:last-child .table {
            font-size: 0.85rem;
        }
        
        .row:last-child .table th,
        .row:last-child .table td {
            padding: 0.5rem 0.5rem !important;
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
    }
</style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Datos del servidor
        const chartData = @json($chartData);
        const stats = @json($stats);

        // Dual Chart - Pedidos e Ingresos
        const dualCtx = document.getElementById('dualChart').getContext('2d');
        const dualChart = new Chart(dualCtx, {
            type: 'line',
            data: {
                labels: chartData.orders_by_day.labels,
                datasets: [{
                    label: 'Pedidos',
                    data: chartData.orders_by_day.data,
                    borderColor: '#0096c7',
                    backgroundColor: 'rgba(0, 150, 199, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y'
                }, {
                    label: 'Ingresos ($)',
                    data: chartData.revenue_by_day ? chartData.revenue_by_day.data : [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Días'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Cantidad de Pedidos'
                        },
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Ingresos ($)'
                        },
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 1) {
                                    return 'Ingresos: $' + context.parsed.y.toLocaleString();
                                }
                                return 'Pedidos: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Confirmados', 'En Preparación', 'Listos', 'Entregados'],
                datasets: [{
                    data: [
                        stats.pending_orders || 0,
                        stats.confirmed_orders || 0,
                        stats.preparing_orders || 0,
                        stats.ready_orders || 0,
                        stats.delivered_orders || 0
                    ],
                    backgroundColor: [
                        '#f9844a',  // Pendientes - Naranja TCocina
                        '#0096c7',  // Confirmados - Azul TCocina
                        '#f9c74f',  // En Preparación - Amarillo TCocina
                        '#28a745',  // Listos - Verde
                        '#6c757d'   // Entregados - Gris
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Validar que date_from no sea mayor que date_to
        document.getElementById('dateFrom').addEventListener('change', function() {
            const to = document.getElementById('dateTo');
            if (this.value > to.value) to.value = this.value;
        });
        document.getElementById('dateTo').addEventListener('change', function() {
            const from = document.getElementById('dateFrom');
            if (this.value < from.value) from.value = this.value;
        });

        // Inicializar iconos Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>

    <!-- Mobile Responsive Styles for Dashboard -->
    <style>
        /* Mobile: Compact stat cards */
        @media (max-width: 767.98px) {
            .stats-cards-container {
                --bs-gutter-x: 0.5rem;
            }
            .stats-cards-container .col-6 {
                padding-right: 0.25rem;
                padding-left: 0.25rem;
            }
            .stat-card-modern .card-body {
                padding: 0.75rem !important;
            }
            .stat-card-icon .modern-icon {
                width: 1.5rem !important;
                height: 1.5rem !important;
            }
            .stat-card-text .h4 {
                font-size: 1.15rem !important;
            }
            .stat-card-text .small {
                font-size: 0.65rem !important;
                line-height: 1.2;
            }
            .stat-card-text .mb-1 {
                margin-bottom: 0.15rem !important;
            }
            /* Welcome header super compacto */
            .welcome-header-compact .card-body {
                padding: 0.5rem 0.75rem !important;
            }
            .welcome-header-compact h5 {
                font-size: 1rem !important;
            }
            .welcome-header-compact small {
                font-size: 0.7rem;
            }
            /* Form select compact */
            #timeFilter {
                font-size: 0.75rem;
                padding: 0.2rem 0.4rem;
                min-height: auto;
            }
            /* Chart cards */
            .col-xl-8 .card, .col-xl-4 .card {
                margin-bottom: 1rem;
            }
            /* Recent orders table */
            .table-responsive {
                font-size: 0.8rem;
            }
            .table-responsive .badge {
                font-size: 0.7rem;
                padding: 0.25em 0.5em;
            }
        }
        /* Tablet adjustments */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .stat-card-modern .card-body {
                padding: 1rem !important;
            }
            .stat-card-icon .modern-icon {
                width: 2rem !important;
                height: 2rem !important;
            }
        }
    </style>
@endpush
