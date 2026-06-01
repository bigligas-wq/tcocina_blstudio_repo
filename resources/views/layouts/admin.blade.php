<!DOCTYPE html>
<html lang="es">

<head>
    <script>(function(){var t=localStorage.getItem('admin-theme');if(t==='dark')document.documentElement.setAttribute('data-theme','dark');})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Administración - TCocina')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <style>
        :root{
            --brand-primary: {{ $businessSettings['brand_primary_color'] ?? '#00b4d8' }};
            --brand-accent:  {{ $businessSettings['brand_accent_color'] ?? '#ff6b35' }};
        }
    </style>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Roboto (Rocker theme) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Boxicons (Rocker theme) -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <!-- Lucide Icons - Iconos modernos y profesionales -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Lordicon -->
    <script src="https://cdn.lordicon.com/lordicon.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .sidebar {
            height: 100vh;                 /* ocupar exactamente una pantalla */
            min-height: 100vh;
            /* Gradiente dinámico fijo: arriba color de marca, abajo negro */
            background: linear-gradient(180deg,
                var(--brand-primary) 0%,
                color-mix(in srgb, var(--brand-primary), #0b1020 35%) 55%,
                #000000 100%);
            background-attachment: fixed;  /* no “se estira” cuando hay scroll interno */
            transition: width 0.3s ease;
            position: fixed;               /* sidebar fijo, independiente del alto del contenido */
            top: 0;
            left: 0;
            z-index: 1040;
            overflow-y: auto;              /* si el contenido supera 100vh, scrollea dentro */
            overflow-x: hidden;
        }

        .sidebar.ultra-mini {
            width: 16px !important;
            overflow: hidden !important;
            padding: 0 !important;
        }

        .sidebar.ultra-mini .logo-circle,
        .sidebar.ultra-mini .sidebar-header,
        .sidebar.ultra-mini .kitchen-mode-section,
        .sidebar.ultra-mini .nav,
        .sidebar.ultra-mini .config-section {
            display: none !important;
            opacity: 0;
        }

        .sidebar.collapsed {
            width: 70px !important;
        }

        .sidebar.expanded {
            width: 250px !important;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link {
            padding: 0.75rem 0.5rem;
            text-align: center;
            justify-content: center;
        }

        /* Centrar todos los iconos (logos) excepto el seleccionado */
        .sidebar.collapsed .nav-link:not(.active) {
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: 100% !important;
        }

        .sidebar.collapsed .nav-link:not(.active) > i,
        .sidebar.collapsed .nav-link:not(.active) > .modern-icon,
        .sidebar.collapsed .nav-link:not(.active) > lord-icon,
        .sidebar.collapsed .nav-link:not(.active) > div {
            margin: 0 auto !important;
            display: block;
        }

        .sidebar.collapsed .nav {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .sidebar.collapsed .nav-item {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #333;
            background-color: white;
            border-radius: 25px 0 0 25px;
            margin-left: -5px;
            margin-right: 0;
            padding-left: 25px;
            padding-right: 12px;
            box-shadow: 
                0 4px 20px rgba(0, 0, 0, 0.15),
                0 2px 8px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 10;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transform: translateX(0);
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: -15px;
            top: 0;
            width: 15px;
            height: 100%;
            background: white;
            border-radius: 25px 0 0 25px;
            z-index: 9;
        }
        
        .sidebar .nav-link.active::after {
            content: '';
            position: absolute;
            right: -1px;
            top: 0;
            width: 1px;
            height: 100%;
            background: white;
            z-index: 8;
            pointer-events: none;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .nav-link i {
            font-size: 1.2rem;
        }
        
        /* Estilos para iconos modernos Lucide */
        .modern-icon {
            width: 24px !important;
            height: 24px !important;
            stroke-width: 2;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover .modern-icon {
            transform: scale(1.1);
            stroke-width: 2.5;
        }
        
        .nav-link.active .modern-icon {
            transform: scale(1.05);
            stroke-width: 2.5;
            stroke: #333 !important;
        }
        
        /* Iconos más grandes en modo cocina */
        .kitchen-mode-link .modern-icon {
            width: 28px !important;
            height: 28px !important;
        }
        
        .kitchen-mode-link:hover .modern-icon {
            transform: scale(1.15);
        }
        
        /* Estilos especiales para el botón Home - Icono 3D realista */
        .home-link {
            background: transparent !important;
            border-radius: 0.5rem !important;
            margin: 0.25rem 0 !important;
            padding: 0.75rem 1rem !important;
            border: none !important;
            transition: all 0.3s ease !important;
            position: relative;
            overflow: hidden;
        }
        
        .home-link:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            transform: translateY(-2px) !important;
        }
        
        .home-link .home-icon {
            fill: #ff6b6b !important;
            stroke: #ff6b6b !important;
            stroke-width: 2 !important;
            filter: 
                drop-shadow(0 2px 4px rgba(255, 107, 107, 0.4))
                drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2)) !important;
            transition: all 0.3s ease !important;
        }
        
        .home-link:hover .home-icon {
            transform: scale(1.15) !important;
            fill: #ff5252 !important;
            stroke: #ff5252 !important;
            filter: 
                drop-shadow(0 3px 6px rgba(255, 107, 107, 0.6))
                drop-shadow(0 6px 12px rgba(0, 0, 0, 0.3)) !important;
        }
        
        .home-link span {
            font-weight: 600 !important;
            color: #ff6b6b !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
            transition: all 0.3s ease !important;
        }
        
        .home-link:hover span {
            color: #ff5252 !important;
        }
        
        /* Asegurar que el botón Home mantenga su estilo especial cuando está active */
        .home-link.active {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ff6b6b !important;
            border-radius: 0.5rem !important;
            margin-right: 0 !important;
            padding: 0.75rem 1rem !important;
        }
        
        .home-link.active .home-icon {
            fill: #ff6b6b !important;
            stroke: #ff6b6b !important;
        }
        
        .home-link.active span {
            color: #ff6b6b !important;
        }

        .main-content {
            background-color: white;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed+.main-content {
            margin-left: 70px;
        }

        .sidebar.expanded+.main-content {
            margin-left: 250px;
        }

        /* Botón toggle con flecha - fondo azul como sidebar */
        .sidebar-toggle-btn {
            position: fixed;
            left: 16px;
            top: 40px;
            transform: translateY(-50%);
            width: 20px;
            height: 40px;
            background: var(--brand-primary, #00b4d8);
            border: none;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            opacity: 0.8;
        }

        .sidebar.collapsed+.sidebar-toggle-btn,
        .sidebar-toggle-btn.collapsed-state {
            left: 70px;
        }

        .sidebar.expanded+.sidebar-toggle-btn,
        .sidebar-toggle-btn.expanded-state {
            left: 250px;
        }

        .sidebar-toggle-btn:hover {
            background: color-mix(in srgb, var(--brand-primary, #00b4d8), #000 10%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .sidebar-toggle-btn:active {
            transform: translateY(-50%) scale(0.95);
        }

        /* Flecha blanca - dirección según estado */
        .sidebar-toggle-btn::before {
            content: '';
            width: 0;
            height: 0;
            border-style: solid;
            transition: all 0.3s ease;
        }

        /* Ultra-mini: flecha a la derecha (indica expandir) */
        .sidebar-toggle-btn.ultra-mini-state::before {
            border-width: 4px 0 4px 6px;
            border-color: transparent transparent transparent #ffffff;
        }

        /* Collapsed: flecha a la derecha (indica expandir) */
        .sidebar-toggle-btn.collapsed-state::before {
            border-width: 4px 0 4px 6px;
            border-color: transparent transparent transparent #ffffff;
        }

        /* Expanded: flecha a la izquierda (indica colapsar) */
        .sidebar-toggle-btn.expanded-state::before {
            border-width: 4px 6px 4px 0;
            border-color: transparent #ffffff transparent transparent;
        }

        .sidebar-header {
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header {
            opacity: 0;
            height: 0;
            overflow: hidden;
        }

        .kitchen-mode-section {
            padding: 0.5rem;
            display: flex;
            justify-content: center;
        }

        .sidebar.collapsed .kitchen-mode-section {
            padding: 0.5rem;
            display: flex;
            justify-content: center;
        }

        .kitchen-mode-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border-radius: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .kitchen-mode-link:hover {
            background: linear-gradient(135deg, #e55a2b 0%, #e8821a 100%);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .kitchen-mode-link.active {
            background: linear-gradient(135deg, #d44a1f 0%, #d6730f 100%);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
        }

        .kitchen-mode-link i {
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }

        .sidebar.collapsed .kitchen-mode-link {
            padding: 0.5rem;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .kitchen-mode-link span {
            display: none;
        }

        .sidebar.collapsed .kitchen-mode-link i {
            font-size: 1.5rem;
            margin-right: 0;
        }

        .config-section {
            position: static;
            margin-top: auto;
            padding: 1rem;
            text-align: center;
            
        }

        .sidebar.collapsed .config-section {
            width: 70px;
            padding: 0 0.5rem;
        }

        .config-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .config-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .config-link.active {
            background-color: #007bff;
        }

        .sidebar.collapsed .config-link {
            padding: 0.5rem;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin: 0 auto;
        }

        .sidebar.collapsed .config-link span {
            display: none;
        }

        .sidebar.collapsed .config-link i {
            font-size: 1.2rem;
        }

        /* Logo Circle Styles */
        .logo-circle {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 60px;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .logo-circle img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        /* Adjust sidebar padding to accommodate logo */
        .sidebar {
            padding-top: 80px;
        }

        /* Fix main content positioning to prevent profile image cutoff */
        .main-content {
            margin-left: 70px;
            transition: margin-left 0.3s ease;
            width: calc(100% - 70px);
            padding-top: 10px;
            /* Adjusted padding to move content higher */
        }

        .sidebar.ultra-mini~.main-content {
            margin-left: 16px;
            width: calc(100% - 16px);
        }

        .sidebar.expanded~.main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .sidebar.collapsed~.main-content {
            margin-left: 70px;
            width: calc(100% - 70px);
        }

        /* Ensure proper positioning for all screen sizes */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar.expanded~.main-content {
                margin-left: 0;
                width: 100%;
            }
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--brand-primary) 0%, color-mix(in srgb, var(--brand-primary), #000 20%) 100%);
            color: white;
        }
        /* Mantener el botón de Modo Cocina con su identidad naranja original */
        .kitchen-mode-link { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); }
        .kitchen-mode-link:hover { background: linear-gradient(135deg, #e55a2b 0%, #e8821a 100%); }

        /* ============================================================
           ROCKER THEME OVERLAY · Aplicado al admin layout
           Mantiene los colores de marca (gradiente brand) y el toggle
           3-state existente. Solo refina la visual.
           ============================================================ */

        /* Tipografía Rocker */
        body, .main-content { font-family: 'Roboto', system-ui, -apple-system, Segoe UI, sans-serif; letter-spacing: .3px; }
        .main-content { background: #f7f7ff !important; }

        /* Sidebar: sombra y bordes Rocker */
        .sidebar {
            box-shadow: 2px 0 16px rgba(11, 16, 32, .18), 0 0 1px rgba(0,0,0,.04);
            border-right: 1px solid rgba(255,255,255,.04);
        }

        /* Items del nav: pill blanca al hover, leve transform Rocker */
        .sidebar .nav-link {
            position: relative;
            font-weight: 500;
            font-size: .92rem;
            padding: .7rem 1rem;
            margin: .2rem .5rem;
            border-radius: 10px;
            transition: background-color .25s ease, color .25s ease, transform .2s ease;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,.12);
            transform: translateX(2px);
        }

        /* Indicador izquierdo tipo metismenu en el activo (modo expandido) */
        .sidebar.expanded .nav-link.active::after {
            content: '';
            position: absolute;
            left: -5px;
            top: 8px; bottom: 8px;
            width: 4px;
            background: linear-gradient(180deg, var(--brand-primary, #00b4d8), color-mix(in srgb, var(--brand-primary, #00b4d8), #fff 35%));
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
        }

        /* Sidebar header: estilo Rocker con título centrado y tagline */
        .sidebar-header h4 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            letter-spacing: .5px;
            font-size: 1.15rem;
            margin-bottom: .15rem;
        }
        .sidebar-header small {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: .65;
        }

        /* Logo circle: anillo sutil Rocker */
        .logo-circle {
            box-shadow: 0 6px 20px rgba(0,0,0,.25);
            background: rgba(255,255,255,.08) !important;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,.18);
        }

        /* Sidebar texto del nav: peso medio, mejor jerarquía */
        .sidebar .nav-link span { font-weight: 500; }

        /* Sub-texto de "expanded" — la pill activa más rectangular Rocker */
        .sidebar.expanded .nav-link.active {
            border-radius: 10px !important;
            margin-left: .5rem !important;
            margin-right: .5rem !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            background-color: #ffffff !important;
            box-shadow: 0 6px 16px rgba(0,0,0,.12), 0 1px 3px rgba(0,0,0,.08);
        }
        .sidebar.expanded .nav-link.active::before { display: none; }

        /* Topbar Rocker dentro del main-content (la barra superior con border-bottom) */
        .main-content > .d-flex.justify-content-between.border-bottom {
            background: #fff !important;
            border-radius: 12px !important;
            border-bottom: none !important;
            box-shadow: 0 2px 6px rgba(218,218,253,.65), 0 2px 6px rgba(206,206,238,.54);
            padding: .75rem 1rem !important;
            margin-top: .5rem !important;
            margin-bottom: 1rem !important;
        }

        /* Cards: ligera animación uniforme Rocker en todo el admin */
        .main-content .card {
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,.04);
            box-shadow: 0 0 5px rgba(0,0,0,.06), 0 1px 3px rgba(0,0,0,.04);
            transition: box-shadow .25s ease, transform .25s ease;
        }
        .main-content .card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.05); }

        /* Alerts más Rocker */
        .main-content .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 6px rgba(0,0,0,.05);
        }

        /* Scrollbar fina en sidebar */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); border-radius: 3px; }

        /* Section divider Rocker dentro del sidebar (kitchen mode separator) */
        .kitchen-mode-section {
            position: relative;
            margin-top: .25rem;
            margin-bottom: .75rem !important;
            padding-bottom: .75rem !important;
            border-bottom: 1px dashed rgba(255,255,255,.12);
        }

        /* Config link (settings, abajo) con look Rocker pill */
        .config-link {
            border-radius: 12px !important;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
            background: rgba(255,255,255,.12) !important;
            transition: all .25s ease;
        }
        .config-link:hover {
            background: rgba(255,255,255,.22) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,.2);
        }

        /* ============================================================
           ROCKER SIDEBAR · Fondo azul brand (como catálogo web)
           Mantiene compatibilidad con el toggle 3-state existente
           (clases ultra-mini / collapsed / expanded en #sidebar)
           ============================================================ */

        /* Sidebar con fondo azul brand */
        .sidebar.rocker-sidebar {
            background: linear-gradient(180deg, var(--brand-primary, #284497) 0%, color-mix(in srgb, var(--brand-primary, #284497), #000 25%) 100%) !important;
            box-shadow: 2px 0 16px rgba(11, 16, 32, .18), 0 0 1px rgba(0,0,0,.04) !important;
            border-right: 1px solid rgba(255,255,255,.08) !important;
            padding-top: 0 !important;
            color: #ffffff;
        }

        /* Header Rocker dentro del sidebar (logo + texto + tagline) */
        .rocker-sidebar .rocker-header {
            display: flex;
            align-items: center;
            gap: 10px;
            height: 70px;
            padding: 10px 14px;
            background: rgba(255,255,255,.08);
            border-bottom: 1px solid rgba(255,255,255,.12);
            position: sticky;
            top: 0;
            z-index: 5;
            opacity: 1 !important;
            overflow: hidden;
        }
        .rocker-sidebar .rocker-header .logo-circle {
            position: static !important;
            top: auto !important; left: auto !important;
            transform: none !important;
            width: 50px !important; height: 50px !important;
            background: var(--brand-primary, #284497) !important;
            border-radius: 12px !important;
            border: 1px solid rgba(255,255,255,.2) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.15) !important;
            backdrop-filter: none !important;
            flex: 0 0 50px;
            padding: 6px;
            overflow: hidden;
            z-index: auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rocker-sidebar .rocker-header .logo-circle img {
            width: 100% !important;
            height: 100% !important;
            max-width: 100% !important;
            max-height: 100% !important;
            object-fit: contain !important;
            filter: none !important;
            display: block;
        }
        /* Header necesita margen suficiente para el logo y su sombra */
        .rocker-sidebar .rocker-header { overflow: visible !important; min-height: 70px; }
        .rocker-sidebar .logo-text-wrap { line-height: 1.1; min-width: 0; flex: 1 1 auto; }
        .rocker-sidebar .logo-text {
            font-family: 'Roboto', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #ffffff !important;
            letter-spacing: .5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rocker-sidebar .logo-tagline {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,.65);
            font-weight: 500;
        }

        /* Hide brand text & tagline cuando está colapsado/ultra-mini */
        .rocker-sidebar.collapsed .logo-text-wrap,
        .rocker-sidebar.ultra-mini .logo-text-wrap { display: none; }
        .rocker-sidebar.collapsed .rocker-header,
        .rocker-sidebar.ultra-mini .rocker-header { justify-content: center; padding-left: 0; padding-right: 0; }
        .rocker-sidebar.ultra-mini .rocker-header { display: none !important; }

        /* Restaurar el ::after del logo eliminado */
        .rocker-sidebar { padding-top: 0; }

        /* Metismenu list */
        .rocker-sidebar .metismenu {
            padding: 10px 8px;
            margin-top: 0;
            background: transparent;
        }
        .rocker-sidebar .metismenu .nav-item { display: block; }
        .rocker-sidebar .metismenu li + li { margin-top: 3px; }

        /* Menu labels (PRINCIPAL / CATÁLOGO / SITIO) */
        .rocker-sidebar .menu-label {
            padding: 18px 12px 6px;
            color: rgba(255,255,255,.55);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1.2px;
            font-weight: 700;
            list-style: none;
        }
        .rocker-sidebar .menu-label span { display: inline-block; }

        /* Esconder menu-labels cuando colapsado/ultra-mini */
        .rocker-sidebar.collapsed .menu-label,
        .rocker-sidebar.ultra-mini .menu-label { display: none; }

        /* Nav links Rocker - texto blanco sobre azul */
        .rocker-sidebar .nav-link {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 12px;
            padding: 9px 14px !important;
            margin: 0 !important;
            color: #ffffff !important;
            font-size: 14.5px;
            font-weight: 500;
            letter-spacing: .3px;
            border-radius: 8px;
            transition: background-color .25s ease, color .25s ease;
            text-decoration: none;
            position: relative;
        }
        .rocker-sidebar .nav-link:hover {
            background-color: rgba(255,255,255,.15) !important;
            color: #1a1a1a !important;
        }
        .rocker-sidebar .nav-link:hover .parent-icon {
            color: #1a1a1a !important;
        }

        /* Active state - pill blanca con texto brand */
        .rocker-sidebar .nav-link.active {
            background-color: #ffffff !important;
            color: var(--brand-primary, #284497) !important;
            margin: 0 !important;
            padding: 9px 14px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
            border: none !important;
            border-radius: 8px !important;
        }
        .rocker-sidebar .nav-link.active::before,
        .rocker-sidebar .nav-link.active::after { display: none !important; }

        /* Acento izquierdo blanco del activo (sobre fondo azul) */
        .rocker-sidebar.expanded .nav-link.active {
            position: relative;
            background: #ffffff !important;
        }
        .rocker-sidebar.expanded .nav-link.active::before {
            content: '' !important;
            display: block !important;
            position: absolute !important;
            left: 0 !important; top: 6px !important; bottom: 6px !important;
            width: 3px !important;
            background: rgba(255,255,255,.9) !important;
            border-radius: 0 3px 3px 0 !important;
            box-shadow: 0 0 8px rgba(255,255,255,.4);
        }

        /* Parent icon (iconos blancos, hover negro) */
        .rocker-sidebar .parent-icon {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 32px;
            height: 32px;
            font-size: 22px;
            line-height: 1;
            color: #ffffff;
            flex: 0 0 32px;
            transition: color .25s ease;
            position: relative;
        }
        /* Icono activo toma color brand (azul) cuando la pill está blanca */
        .rocker-sidebar .nav-link.active .parent-icon { color: var(--brand-primary, #284497) !important; }

        /* Menu title */
        .rocker-sidebar .menu-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1 1 auto;
        }

        /* Hide menu-title in collapsed/ultra-mini */
        .rocker-sidebar.collapsed .menu-title,
        .rocker-sidebar.ultra-mini .menu-title { display: none; }
        .rocker-sidebar.collapsed .nav-link,
        .rocker-sidebar.ultra-mini .nav-link {
            justify-content: center !important;
            align-items: center !important;
            padding: 9px 0 !important;
            gap: 0 !important;
        }
        .rocker-sidebar.collapsed .nav-link .parent-icon,
        .rocker-sidebar.ultra-mini .nav-link .parent-icon {
            width: 32px !important;
            height: 32px !important;
            flex: 0 0 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        /* Hover en collapsed: icono negro centrado */
        .rocker-sidebar.collapsed .nav-link:hover .parent-icon,
        .rocker-sidebar.ultra-mini .nav-link:hover .parent-icon {
            color: #1a1a1a !important;
        }

        /* Tooltip-like hover info en collapsed: muestra el menu-title como flotante */
        .rocker-sidebar.collapsed .nav-item { position: relative; }
        .rocker-sidebar.collapsed .nav-link:hover .menu-title {
            display: block !important;
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 12px;
            background: #2c3340;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
            z-index: 1100;
            pointer-events: none;
        }

        /* PRO badge en Reseñas - visible siempre */
        .pro-badge {
            position: absolute;
            top: -4px; right: -8px;
            background: linear-gradient(135deg, #FFD700, #FFA500) !important;
            color: #000 !important;
            font-size: 8px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 8px;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,.25);
            z-index: 10;
        }
        .rocker-sidebar .pro-badge {
            position: absolute;
            top: -4px; right: -8px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            font-size: 8px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 8px;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,.25);
            z-index: 10;
        }
        /* Asegurar que PRO badge se vea en collapsed (ajustar posición) */
        .rocker-sidebar.collapsed .nav-link .parent-icon .pro-badge,
        .rocker-sidebar.ultra-mini .nav-link .parent-icon .pro-badge {
            top: -6px !important;
            right: -6px !important;
            font-size: 7px !important;
            padding: 1px 4px !important;
        }

        /* Kitchen mode link (mantiene naranja) - ahora dentro del Rocker sidebar */
        .rocker-sidebar .kitchen-mode-section {
            padding: 10px 12px 6px;
            border-bottom: 1px dashed rgba(255,255,255,.2);
            margin-bottom: 4px !important;
        }
        .rocker-sidebar .kitchen-mode-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px !important;
            border-radius: 10px !important;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%) !important;
            color: #fff !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            letter-spacing: .3px;
            box-shadow: 0 4px 12px rgba(255,107,53,.30);
            transition: transform .2s ease, box-shadow .2s ease;
            text-decoration: none;
            justify-content: flex-start;
        }
        .rocker-sidebar .kitchen-mode-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(255,107,53,.40);
        }
        .rocker-sidebar .kitchen-mode-link.active {
            box-shadow: 0 0 0 3px rgba(255,107,53,.30), 0 4px 12px rgba(255,107,53,.30);
        }
        .rocker-sidebar.collapsed .kitchen-mode-section,
        .rocker-sidebar.ultra-mini .kitchen-mode-section { padding: 10px 8px 6px; }
        .rocker-sidebar.collapsed .kitchen-mode-link,
        .rocker-sidebar.ultra-mini .kitchen-mode-link {
            justify-content: center; gap: 0;
            width: 44px; height: 44px;
            border-radius: 12px !important;
            margin: 0 auto;
            padding: 0 !important;
        }
        .rocker-sidebar.collapsed .kitchen-mode-link span,
        .rocker-sidebar.ultra-mini .kitchen-mode-link span { display: none; }

        /* Home link (Volver al Sitio) - igual que otros nav links */
        .rocker-sidebar .home-link { background: transparent !important; color: #ffffff !important; }
        .rocker-sidebar .home-link:hover { background: rgba(255,255,255,.15) !important; color: #1a1a1a !important; }
        .rocker-sidebar .home-link .parent-icon i { color: #ffffff !important; }
        .rocker-sidebar .home-link:hover .parent-icon i { color: #1a1a1a !important; }
        .rocker-sidebar .home-link span { color: inherit !important; text-shadow: none !important; font-weight: 500 !important; }

        /* Config section (Settings, abajo) - compacto y centrado */
        .rocker-sidebar .config-section {
            padding: 10px 12px !important;
            border-top: 1px solid rgba(255,255,255,.15);
            margin-top: auto;
            width: 100% !important;
        }
        .rocker-sidebar .config-link {
            background: linear-gradient(135deg, rgba(255,255,255,.95), rgba(255,255,255,.85)) !important;
            color: var(--brand-primary, #284497) !important;
            border-radius: 8px !important;
            padding: 8px 10px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.12) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 8px !important;
            min-height: 38px !important;
            max-height: 38px !important;
        }
        .rocker-sidebar .config-link:hover {
            background: #ffffff !important;
            color: var(--brand-primary, #284497) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.18) !important;
        }
        .rocker-sidebar .config-link .parent-icon {
            color: var(--brand-primary, #284497) !important;
            width: 22px !important;
            height: 22px !important;
            flex: 0 0 22px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
            line-height: 1 !important;
        }
        .rocker-sidebar .config-link .menu-title {
            flex: 0 0 auto !important;
            font-weight: 500 !important;
            font-size: 13.5px !important;
            line-height: 1 !important;
        }
        .rocker-sidebar .config-link.active {
            box-shadow: 0 0 0 2px rgba(255,255,255,.4), 0 2px 8px rgba(0,0,0,.15) !important;
            background: #ffffff !important;
        }
        .rocker-sidebar.collapsed .config-section,
        .rocker-sidebar.ultra-mini .config-section { padding: 8px !important; }
        .rocker-sidebar.collapsed .config-link,
        .rocker-sidebar.ultra-mini .config-link {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            min-height: 38px !important;
            max-width: 38px !important;
            max-height: 38px !important;
            border-radius: 10px !important;
            margin: 0 auto !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0 !important;
            background: rgba(255,255,255,.95) !important;
        }
        .rocker-sidebar.collapsed .config-link .parent-icon,
        .rocker-sidebar.ultra-mini .config-link .parent-icon {
            width: 100% !important;
            height: 100% !important;
            flex: 0 0 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 22px !important;
            color: var(--brand-primary, #284497) !important;
        }

        /* Scrollbar para sidebar azul (blanco semi-transparente) */
        .rocker-sidebar::-webkit-scrollbar { width: 6px; }
        .rocker-sidebar::-webkit-scrollbar-track { background: transparent; }
        .rocker-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.25); border-radius: 3px; }
        .rocker-sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.4); }

        /* Botón toggle - cambiar para fondo blanco del nuevo sidebar */
        .rocker-sidebar ~ .sidebar-toggle-btn,
        .rocker-sidebar + .main-content + .sidebar-toggle-btn { /* no-op safety */ }

        /* Mobile: sidebar como off-canvas */
        @media (max-width: 768px) {
            .rocker-sidebar .rocker-header { padding: 10px; }
            .rocker-sidebar .logo-text { font-size: 1rem; }
        }

        /* Anular reglas del overlay anterior que coloreaban el sidebar viejo */
        .rocker-sidebar.expanded .nav-link.active {
            background: #ffffff !important;
            color: var(--brand-primary, #284497) !important;
        }

        /* ============================================================
           ROCKER TOGGLE BUTTON · Pastilla blanca flotante con sombra
           Reemplaza el botón azul original por el estilo Rocker
           ============================================================ */
        .sidebar-toggle-btn {
            top: 24px !important;
            width: 28px !important;
            height: 28px !important;
            background: #ffffff !important;
            border: 1px solid rgba(255,255,255,.3) !important;
            border-radius: 50% !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.2) !important;
            opacity: 1 !important;
            transform: translateY(0) !important;
            transition: all .25s ease !important;
            z-index: 1040 !important;
        }
        .sidebar-toggle-btn:hover {
            background: var(--brand-primary, #284497) !important;
            border-color: var(--brand-primary, #284497) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,.3) !important;
            transform: translateY(0) scale(1.08) !important;
        }
        .sidebar-toggle-btn:active { transform: translateY(0) scale(.95) !important; }

        /* Posiciones: cruzar el borde del sidebar, no quedar adentro */
        .sidebar-toggle-btn.collapsed-state { left: 58px !important; }
        .sidebar-toggle-btn.expanded-state  { left: 238px !important; }
        .sidebar-toggle-btn.ultra-mini-state { left: 4px !important; }

        /* Flecha brand por defecto (blanca en hover) */
        .sidebar-toggle-btn::before {
            border-color: transparent transparent transparent var(--brand-primary, #284497) !important;
            transition: border-color .2s ease;
        }
        .sidebar-toggle-btn.expanded-state::before {
            border-color: transparent var(--brand-primary, #284497) transparent transparent !important;
        }
        .sidebar-toggle-btn:hover::before {
            border-color: transparent transparent transparent #ffffff !important;
        }
        .sidebar-toggle-btn.expanded-state:hover::before {
            border-color: transparent #ffffff transparent transparent !important;
        }

        /* ================================================================
           DARK MODE — html[data-theme="dark"]
           ================================================================ */
        html.theme-switching * {
            transition: background-color .3s ease, color .25s ease,
                        border-color .3s ease, box-shadow .3s ease !important;
        }

        html[data-theme="dark"] body { background: #080d1a; }
        html[data-theme="dark"] .main-content {
            background: #080d1a !important;
            color: #e8edf5;
        }

        /* Topbar */
        html[data-theme="dark"] .main-content > .d-flex.justify-content-between {
            background: #0f1626 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.5) !important;
        }

        /* Cards */
        html[data-theme="dark"] .main-content .card {
            background-color: #0f1626 !important;
            border-color: #1a2840 !important;
            box-shadow: 0 0 8px rgba(0,0,0,.5), 0 2px 6px rgba(0,0,0,.3) !important;
        }
        html[data-theme="dark"] .main-content .card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,.65), 0 2px 8px rgba(0,0,0,.4) !important;
        }
        html[data-theme="dark"] .main-content .card-header {
            background-color: #111c2e !important;
            border-color: #1a2840 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .main-content .card-footer {
            background-color: #111c2e !important;
            border-color: #1a2840 !important;
        }
        html[data-theme="dark"] .main-content .card-body {
            background-color: #0f1626 !important;
            color: #e8edf5;
        }
        html[data-theme="dark"] .main-content .card-title,
        html[data-theme="dark"] .main-content .card-subtitle { color: #e8edf5 !important; }

        /* Text */
        html[data-theme="dark"] .main-content h1,
        html[data-theme="dark"] .main-content h2,
        html[data-theme="dark"] .main-content h3,
        html[data-theme="dark"] .main-content h4,
        html[data-theme="dark"] .main-content h5,
        html[data-theme="dark"] .main-content h6 { color: #e8edf5 !important; }
        html[data-theme="dark"] .main-content p { color: #c8d6e8; }
        html[data-theme="dark"] .main-content label,
        html[data-theme="dark"] .main-content .form-label { color: #c8d6e8 !important; }
        /* FIX #1: helper text era #445569 (muy oscuro, ilegible) → #6b8a9e */
        html[data-theme="dark"] .main-content small,
        html[data-theme="dark"] .main-content .form-text { color: #6b8a9e !important; }
        html[data-theme="dark"] .main-content .text-muted { color: #7a8fa8 !important; }
        html[data-theme="dark"] .main-content .text-secondary { color: #7a8fa8 !important; }
        html[data-theme="dark"] .main-content .text-dark { color: #e8edf5 !important; }
        /* FIX #2: fw-bold sin !important podía quedar pisado */
        html[data-theme="dark"] .main-content .fw-bold { color: #e8edf5 !important; }
        /* FIX #4: utilidades de color de texto */
        html[data-theme="dark"] .main-content .text-primary { color: var(--brand-primary) !important; }
        html[data-theme="dark"] .main-content .text-success { color: #6ee7b7 !important; }
        html[data-theme="dark"] .main-content .text-danger  { color: #fca5a5 !important; }
        html[data-theme="dark"] .main-content .text-warning { color: #fde68a !important; }
        html[data-theme="dark"] .main-content .text-info    { color: #7dd3fc !important; }

        /* Forms */
        html[data-theme="dark"] .main-content .form-control,
        html[data-theme="dark"] .main-content .form-select,
        html[data-theme="dark"] .main-content textarea,
        html[data-theme="dark"] .main-content input[type="text"],
        html[data-theme="dark"] .main-content input[type="email"],
        html[data-theme="dark"] .main-content input[type="number"],
        html[data-theme="dark"] .main-content input[type="password"],
        html[data-theme="dark"] .main-content input[type="tel"],
        /* FIX #5: tipos de input faltantes */
        html[data-theme="dark"] .main-content input[type="date"],
        html[data-theme="dark"] .main-content input[type="time"],
        html[data-theme="dark"] .main-content input[type="search"],
        html[data-theme="dark"] .main-content input[type="url"] {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
            color-scheme: dark;
        }
        html[data-theme="dark"] .main-content .form-control:focus,
        html[data-theme="dark"] .main-content .form-select:focus,
        html[data-theme="dark"] .main-content textarea:focus {
            background-color: #111c2e !important;
            border-color: var(--brand-primary) !important;
            color: #e8edf5 !important;
            box-shadow: 0 0 0 3px rgba(0,180,216,.18) !important;
        }
        html[data-theme="dark"] .main-content .form-control::placeholder,
        html[data-theme="dark"] .main-content textarea::placeholder { color: #3d5270 !important; }
        html[data-theme="dark"] .main-content .form-control.bg-light {
            background-color: #161f33 !important;
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .main-content .input-group-text {
            background-color: #161f33 !important;
            border-color: #1e2d47 !important;
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .main-content .form-check-input {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .main-content .form-check-input:checked {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
        }
        html[data-theme="dark"] .main-content .form-control-color {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
        }

        /* Tables */
        html[data-theme="dark"] .main-content .table {
            --bs-table-bg: #0f1626;
            --bs-table-striped-bg: #111c2e;
            --bs-table-hover-bg: #161f33;
            --bs-table-border-color: #1e2d47;
            --bs-table-color: #e8edf5;
            color: #e8edf5 !important;
            border-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .main-content .table th,
        html[data-theme="dark"] .main-content .table td {
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .main-content .table thead th {
            background-color: #111c2e !important;
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .main-content .table-light > * {
            background-color: #111c2e !important;
            color: #7a8fa8 !important;
        }
        /* FIX #9: table-dark dentro de dark mode queda sin distinción */
        html[data-theme="dark"] .main-content .table-dark {
            --bs-table-bg: #1e2d47 !important;
            background-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .main-content .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #111c2e !important;
        }
        html[data-theme="dark"] .main-content .table-hover > tbody > tr:hover > * {
            background-color: #161f33 !important;
        }

        /* Alerts */
        html[data-theme="dark"] .main-content .alert-success {
            background: rgba(16,185,129,.12) !important;
            border-color: rgba(16,185,129,.3) !important;
            color: #6ee7b7 !important;
        }
        html[data-theme="dark"] .main-content .alert-danger {
            background: rgba(239,68,68,.12) !important;
            border-color: rgba(239,68,68,.3) !important;
            color: #fca5a5 !important;
        }
        html[data-theme="dark"] .main-content .alert-warning {
            background: rgba(245,158,11,.12) !important;
            border-color: rgba(245,158,11,.3) !important;
            color: #fde68a !important;
        }
        html[data-theme="dark"] .main-content .alert-info {
            background: rgba(0,180,216,.12) !important;
            border-color: rgba(0,180,216,.3) !important;
            color: #7dd3fc !important;
        }
        html[data-theme="dark"] .main-content .alert .btn-close { filter: invert(1) !important; }

        /* Buttons */
        html[data-theme="dark"] .main-content .btn-light {
            background-color: #161f33 !important;
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .main-content .btn-light:hover { background-color: #1e2d47 !important; }
        html[data-theme="dark"] .main-content .btn-outline-secondary {
            border-color: #2a3a55 !important;
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .main-content .btn-outline-secondary:hover {
            background-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        /* FIX #6: outline buttons faltantes */
        html[data-theme="dark"] .main-content .btn-outline-primary {
            border-color: var(--brand-primary) !important;
            color: var(--brand-primary) !important;
        }
        html[data-theme="dark"] .main-content .btn-outline-primary:hover {
            background-color: var(--brand-primary) !important;
            color: #fff !important;
        }
        html[data-theme="dark"] .main-content .btn-outline-success {
            border-color: #6ee7b7 !important;
            color: #6ee7b7 !important;
        }
        html[data-theme="dark"] .main-content .btn-outline-success:hover {
            background-color: rgba(16,185,129,.2) !important;
            color: #6ee7b7 !important;
        }
        html[data-theme="dark"] .main-content .btn-outline-danger {
            border-color: #fca5a5 !important;
            color: #fca5a5 !important;
        }
        html[data-theme="dark"] .main-content .btn-outline-danger:hover {
            background-color: rgba(239,68,68,.2) !important;
            color: #fca5a5 !important;
        }
        html[data-theme="dark"] .main-content .btn-outline-warning {
            border-color: #fde68a !important;
            color: #fde68a !important;
        }
        html[data-theme="dark"] .main-content .btn-close { filter: invert(1) grayscale(100%) brightness(200%) !important; }

        /* Modals */
        html[data-theme="dark"] .modal-content {
            background-color: #0f1626 !important;
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .modal-header {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .modal-body { color: #e8edf5; }
        html[data-theme="dark"] .modal-footer {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .modal .btn-close { filter: invert(1) !important; }
        html[data-theme="dark"] .modal .form-control,
        html[data-theme="dark"] .modal .form-select,
        html[data-theme="dark"] .modal textarea,
        html[data-theme="dark"] .modal input[type="text"],
        html[data-theme="dark"] .modal input[type="number"],
        html[data-theme="dark"] .modal input[type="date"],
        html[data-theme="dark"] .modal input[type="time"] {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
            color-scheme: dark;
        }
        html[data-theme="dark"] .modal label { color: #c8d6e8 !important; }
        /* FIX #1 en modal: form-text también */
        html[data-theme="dark"] .modal .form-text { color: #6b8a9e !important; }
        html[data-theme="dark"] .modal h5,
        html[data-theme="dark"] .modal h4 { color: #e8edf5 !important; }

        /* Dropdowns */
        html[data-theme="dark"] .dropdown-menu {
            background-color: #0f1626 !important;
            border-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .dropdown-item { color: #e8edf5 !important; }
        html[data-theme="dark"] .dropdown-item:hover,
        html[data-theme="dark"] .dropdown-item:focus {
            background-color: #161f33 !important;
            color: #fff !important;
        }
        html[data-theme="dark"] .dropdown-divider { border-color: #1e2d47 !important; }

        /* List groups */
        html[data-theme="dark"] .main-content .list-group-item {
            background-color: #0f1626 !important;
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }

        /* Badges */
        html[data-theme="dark"] .main-content .badge.bg-light {
            background-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        /* FIX #3: bg-secondary le faltaba el color de texto */
        html[data-theme="dark"] .main-content .badge.bg-secondary {
            background-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }

        /* Nav tabs y paginación — FIX #8 */
        html[data-theme="dark"] .main-content .nav-tabs {
            border-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .main-content .nav-tabs .nav-link {
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .main-content .nav-tabs .nav-link.active {
            background-color: #0f1626 !important;
            border-color: #1e2d47 #1e2d47 #0f1626 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .main-content .nav-tabs .nav-link:hover {
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .main-content .pagination .page-link {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .main-content .pagination .page-item.active .page-link {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            color: #fff !important;
        }
        html[data-theme="dark"] .main-content .pagination .page-link:hover {
            background-color: #161f33 !important;
            color: #e8edf5 !important;
        }

        /* Tooltips — FIX #7 */
        html[data-theme="dark"] .tooltip .tooltip-inner {
            background-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .tooltip .tooltip-arrow::before {
            border-top-color: #1e2d47 !important;
        }

        /* Borders y separadores */
        html[data-theme="dark"] .main-content hr { border-color: #1e2d47 !important; opacity: 1 !important; }
        html[data-theme="dark"] .main-content .border-top    { border-top-color:    #1e2d47 !important; }
        html[data-theme="dark"] .main-content .border-bottom { border-bottom-color: #1e2d47 !important; }
        html[data-theme="dark"] .main-content .border        { border-color: #1e2d47 !important; }

        /* Background utilities */
        html[data-theme="dark"] .main-content .bg-light { background-color: #161f33 !important; }
        html[data-theme="dark"] .main-content .bg-white { background-color: #0f1626 !important; }

        /* Settings-specific */
        html[data-theme="dark"] .main-content .settings-section-title,
        html[data-theme="dark"] .main-content h5.text-primary { color: var(--brand-primary) !important; }
        html[data-theme="dark"] .main-content .settings-collapsible-card { background: #0f1626 !important; }
        html[data-theme="dark"] .main-content .settings-collapse-toggle { color: #c8d6e8 !important; }

        /* ================================================================
           DARK MODE — Admin Orders (DataTables)
           Usamos IDs específicos para superar la especificidad
           de los estilos propios de orders.blade.php
           ================================================================ */

        /* Wrapper completo */
        html[data-theme="dark"] #orders-table-historico_wrapper,
        html[data-theme="dark"] #orders-table_wrapper {
            background-color: #0f1626 !important;
            color: #e8edf5 !important;
        }

        /* DataTables controles: select, input, labels, info */
        html[data-theme="dark"] #orders-table-historico_wrapper .dataTables_length select,
        html[data-theme="dark"] #orders-table_wrapper .dataTables_length select,
        html[data-theme="dark"] #orders-table-historico_wrapper .dataTables_filter input,
        html[data-theme="dark"] #orders-table_wrapper .dataTables_filter input,
        html[data-theme="dark"] .dataTables_length select,
        html[data-theme="dark"] .dataTables_filter input {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .dataTables_length label,
        html[data-theme="dark"] .dataTables_filter label,
        html[data-theme="dark"] .dataTables_info {
            color: #7a8fa8 !important;
        }

        /* Table headers */
        html[data-theme="dark"] table#orders-table-historico thead th,
        html[data-theme="dark"] table#orders-table thead th,
        html[data-theme="dark"] table.dataTable thead th {
            background-color: #111c2e !important;
            color: #7a8fa8 !important;
            border-color: #1e2d47 !important;
        }

        /* Table body celdas */
        html[data-theme="dark"] table#orders-table-historico tbody td,
        html[data-theme="dark"] table#orders-table tbody td,
        html[data-theme="dark"] table.dataTable tbody td {
            color: #e8edf5 !important;
            border-color: #1e2d47 !important;
            background-color: transparent !important;
        }

        /* Table body rows — ID supera al #orders-table con !important del blade */
        html[data-theme="dark"] table#orders-table-historico tbody tr,
        html[data-theme="dark"] table#orders-table.dataTable tbody tr {
            background-color: #0f1626 !important;
            border-color: #1e2d47 !important;
            box-shadow: none !important;
        }
        html[data-theme="dark"] table#orders-table-historico tbody tr:nth-child(even),
        html[data-theme="dark"] table#orders-table.dataTable tbody tr:nth-child(even) {
            background-color: #111c2e !important;
        }

        /* Mobile cards (tbody tr como card) */
        html[data-theme="dark"] table#orders-table tbody tr {
            background: #0f1626 !important;
            border-color: #1e2d47 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.3) !important;
        }
        html[data-theme="dark"] table#orders-table tbody td {
            border-bottom-color: #1a2840 !important;
        }

        /* Hover y selected */
        html[data-theme="dark"] table#orders-table-historico tbody tr:hover td,
        html[data-theme="dark"] table#orders-table tbody tr:hover td {
            background-color: #161f33 !important;
        }
        html[data-theme="dark"] .main-content tr:has(.order-checkbox:checked),
        html[data-theme="dark"] .main-content tr:has(.order-checkbox:checked) td,
        html[data-theme="dark"] .main-content .table-row-selected,
        html[data-theme="dark"] .main-content .table-row-selected td {
            background-color: rgba(40,68,151,0.25) !important;
        }

        /* Pagination DataTables */
        html[data-theme="dark"] .dataTables_paginate .paginate_button {
            background-color: #111c2e !important;
            border-color: #1e2d47 !important;
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .dataTables_paginate .paginate_button:hover {
            background-color: #161f33 !important;
            border-color: #2a3a55 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .dataTables_paginate .paginate_button.current {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            color: #fff !important;
        }
        html[data-theme="dark"] .dataTables_paginate .paginate_button.disabled {
            background-color: #0a0f1a !important;
            color: #2a3a55 !important;
        }

        /* Pagination moderna */
        html[data-theme="dark"] .main-content .pagination-modern .page-link {
            background: #111c2e !important;
            border-color: #1e2d47 !important;
            color: #7a8fa8 !important;
        }
        html[data-theme="dark"] .main-content .pagination-modern .page-link:hover {
            background: #161f33 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .main-content .pagination-modern .page-item.disabled .page-link {
            background: #0a0f1a !important;
            color: #2a3a55 !important;
        }
        html[data-theme="dark"] .main-content .pagination-info strong {
            color: #e8edf5 !important;
        }

        /* Sales card (Total Ventas) */
        html[data-theme="dark"] .main-content .card.border-success {
            background: linear-gradient(135deg, #0a1f12 0%, #0d2218 100%) !important;
            border-color: rgba(16,185,129,.3) !important;
        }

        /* Loyalty overlay */
        html[data-theme="dark"] .main-content .loyalty-blur-overlay {
            background: rgba(10,15,30,0.88) !important;
        }
        html[data-theme="dark"] .main-content .loyalty-overlay-name { color: #6ee7b7 !important; }
        html[data-theme="dark"] .main-content .loyalty-overlay-avatar { color: #34d399 !important; }

        /* text-dark */
        html[data-theme="dark"] .main-content .text-dark,
        html[data-theme="dark"] .main-content .small.text-dark { color: #c8d6e8 !important; }

        /* List group hover */
        html[data-theme="dark"] .main-content .list-group-item:hover { background-color: #161f33 !important; }

        /* Dashboard — card-header con bg-transparent (Bootstrap !important lo hace blanco) */
        html[data-theme="dark"] .main-content .card-header.bg-transparent {
            background-color: #111c2e !important;
        }

        /* Dashboard — table-light en thead */
        html[data-theme="dark"] .main-content .table-light,
        html[data-theme="dark"] .main-content thead.table-light,
        html[data-theme="dark"] .main-content .table-light > tr > th,
        html[data-theme="dark"] .main-content .table-light > * > tr > th {
            background-color: #111c2e !important;
            color: #7a8fa8 !important;
            border-color: #1e2d47 !important;
        }

        /* Dashboard — date inputs con background rgba blanco hardcodeado */
        html[data-theme="dark"] .main-content input[style*="background:rgba(255,255,255"] {
            background: #111c2e !important;
            color: #e8edf5 !important;
            border-color: #1e2d47 !important;
        }

        /* Accordion */
        html[data-theme="dark"] .main-content .accordion-item {
            background-color: #0f1626 !important;
            border-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .main-content .accordion-button {
            background-color: #111c2e !important;
            color: #e8edf5 !important;
            box-shadow: none !important;
            border-color: #1e2d47 !important;
        }
        html[data-theme="dark"] .main-content .accordion-button:not(.collapsed) {
            background-color: #161f33 !important;
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .main-content .accordion-button::after {
            filter: invert(0.8) !important;
        }
        html[data-theme="dark"] .main-content .accordion-body,
        html[data-theme="dark"] .main-content .accordion-collapse {
            background-color: #0f1626 !important;
        }

        /* Scrollbar dark */
        html[data-theme="dark"] ::-webkit-scrollbar-track { background: #080d1a; }
        html[data-theme="dark"] ::-webkit-scrollbar-thumb { background: #1e2d47; border-radius: 4px; }
        html[data-theme="dark"] ::-webkit-scrollbar-thumb:hover { background: #2a3a55; }

        /* ================================================================
           THEME TOGGLE BUTTON (luna/sol en sidebar)
           ================================================================ */
        .theme-toggle-section { padding: 4px 12px 8px; }
        .rocker-sidebar.collapsed .theme-toggle-section,
        .rocker-sidebar.ultra-mini .theme-toggle-section { padding: 4px 8px 8px !important; }
        .theme-toggle-btn {
            display: flex !important; align-items: center !important;
            justify-content: flex-start !important; gap: 8px !important;
            width: 100%; padding: 8px 10px !important;
            background: rgba(255,255,255,.08) !important;
            border: none; border-radius: 8px !important;
            color: rgba(255,255,255,.85) !important;
            cursor: pointer; transition: all .25s ease !important;
            min-height: 36px; text-align: left;
        }
        .theme-toggle-btn:hover {
            background: rgba(255,255,255,.16) !important;
            color: #fff !important; transform: translateY(-1px);
        }
        .theme-toggle-btn .t-icon {
            display: flex !important; align-items: center !important;
            justify-content: center !important;
            width: 22px !important; height: 22px !important;
            flex: 0 0 22px !important; font-size: 18px !important;
            color: inherit !important; line-height: 1;
        }
        .theme-toggle-btn .t-label {
            font-size: 13.5px !important; font-weight: 500 !important;
            line-height: 1 !important; white-space: nowrap; color: inherit;
        }
        .rocker-sidebar.collapsed .theme-toggle-btn,
        .rocker-sidebar.ultra-mini .theme-toggle-btn {
            width: 38px !important; height: 38px !important;
            min-width: 38px !important; min-height: 38px !important;
            max-width: 38px !important; max-height: 38px !important;
            border-radius: 10px !important; margin: 0 auto !important;
            padding: 0 !important; justify-content: center !important; gap: 0 !important;
        }
        .rocker-sidebar.collapsed .theme-toggle-btn .t-icon,
        .rocker-sidebar.ultra-mini .theme-toggle-btn .t-icon {
            width: 100% !important; height: 100% !important;
            flex: 0 0 100% !important; font-size: 20px !important;
        }
        .rocker-sidebar.collapsed .theme-toggle-btn .t-label,
        .rocker-sidebar.ultra-mini .theme-toggle-btn .t-label { display: none !important; }

        /* ============================================================
           USER PILL · sidebar bottom (avatar + nombre + rol + logout)
           ============================================================ */
        .user-pill-section {
            position: relative;
            padding: 8px 10px 4px;
            border-top: 1px solid rgba(255,255,255,.10);
            margin-top: auto;
        }
        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 8px 10px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 12px;
            cursor: pointer;
            transition: background-color .2s ease, border-color .2s ease;
            text-align: left;
            color: #fff;
        }
        .user-pill:hover {
            background: rgba(255,255,255,.12);
            border-color: rgba(255,255,255,.20);
        }
        .user-pill-avatar {
            width: 32px; height: 32px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #0a0a0a;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,.25);
        }
        .user-pill-info {
            display: flex; flex-direction: column;
            line-height: 1.15;
            min-width: 0;
            flex: 1;
        }
        .user-pill-name {
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-pill-role {
            font-family: 'Roboto', sans-serif;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }
        .user-pill-chevron {
            color: rgba(255,255,255,.55);
            font-size: 18px;
            flex-shrink: 0;
            transition: transform .2s ease;
        }
        .user-pill-section.open .user-pill-chevron {
            transform: rotate(180deg);
        }

        /* Menu (dropdown que sube) */
        .user-pill-menu {
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: calc(100% - 4px);
            background: #1a1d2a;
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0,0,0,.45);
            overflow: hidden;
            transform-origin: bottom center;
            transform: translateY(8px) scale(.96);
            opacity: 0;
            pointer-events: none;
            transition: transform .18s ease, opacity .18s ease;
            z-index: 1100;
        }
        .user-pill-section.open .user-pill-menu {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }
        .user-pill-menu-head {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .user-pill-menu-name {
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 2px;
        }
        .user-pill-menu-email {
            color: rgba(255,255,255,.55);
            font-size: 11.5px;
            word-break: break-all;
        }
        .user-pill-menu-action {
            display: flex; align-items: center; gap: 10px;
            width: 100%;
            padding: 11px 14px;
            background: none;
            border: none;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-align: left;
            transition: background-color .15s ease, color .15s ease;
        }
        .user-pill-menu-action:hover {
            background: rgba(255,76,12,.12);
            color: #ff7a4d;
        }
        .user-pill-menu-action.danger:hover {
            background: rgba(255,76,12,.16);
            color: #ff7a4d;
        }
        .user-pill-menu-action i {
            font-size: 18px;
        }

        /* Collapsed: solo avatar centrado */
        .rocker-sidebar.collapsed .user-pill-section { padding: 8px; }
        .rocker-sidebar.collapsed .user-pill {
            justify-content: center;
            padding: 6px;
            gap: 0;
            background: transparent;
            border-color: transparent;
        }
        .rocker-sidebar.collapsed .user-pill:hover { background: rgba(255,255,255,.10); }
        .rocker-sidebar.collapsed .user-pill-info,
        .rocker-sidebar.collapsed .user-pill-chevron { display: none; }
        .rocker-sidebar.collapsed .user-pill-menu {
            left: calc(100% + 8px);
            right: auto;
            bottom: 0;
            width: 220px;
        }

        /* Ultra-mini: hidden */
        .rocker-sidebar.ultra-mini .user-pill-section { display: none; }
    </style>

    @stack('head')
    @stack('styles')
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Rocker visual replica) -->
            <nav class="sidebar collapsed rocker-sidebar" id="sidebar" data-state="collapsed">
                @php
                    $logoUrl = $businessSettings['brand_logo_url'] ?? null;
                    if ($logoUrl) {
                        if (str_starts_with($logoUrl, '/storage/') || str_starts_with($logoUrl, '/branding/') || str_starts_with($logoUrl, '/images/')) {
                            $logoUrl = asset($logoUrl);
                        }
                    } else {
                        $logoUrl = asset('images/log.png');
                    }
                    $bizName = $businessSettings['business_name'] ?? 'TCocina';
                @endphp

                {{-- Sidebar header Rocker (logo-icon + logo-text) --}}
                <div class="sidebar-header rocker-header">
                    <div class="logo-circle">
                        <img src="{{ $logoUrl }}" alt="{{ $bizName }}"
                            onerror="this.onerror=null; this.src='{{ asset('images/log.png') }}';" />
                    </div>
                    <div class="logo-text-wrap">
                        <h4 class="logo-text mb-0">{{ $bizName }}</h4>
                        <small class="logo-tagline">Admin Panel</small>
                    </div>
                </div>

                <div class="d-flex flex-column" style="height: calc(100vh - 80px);">
                    {{-- Kitchen Mode - destacado --}}
                    <div class="kitchen-mode-section">
                        <a class="kitchen-mode-link {{ request()->routeIs('kitchen*') ? 'active' : '' }}"
                            href="{{ route('kitchen.index') }}">
                            <lord-icon src="/lordicons/cocina.json" colors="primary:#ffffff,secondary:#ffffff" trigger="hover" style="width:28px;height:28px;"></lord-icon>
                            <span>Modo Cocina</span>
                        </a>
                    </div>

                    {{-- Navigation Rocker metismenu pattern --}}
                    <ul class="nav metismenu flex-column">
                        <li class="menu-label"><span>Principal</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}">
                                <div class="parent-icon"><i class='bx bx-home-circle'></i></div>
                                <span class="menu-title">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}"
                                href="{{ route('admin.orders') }}">
                                <div class="parent-icon"><i class='bx bxs-cart'></i></div>
                                <span class="menu-title">Pedidos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.turnos*') ? 'active' : '' }}"
                                href="{{ route('admin.turnos') }}">
                                <div class="parent-icon"><i class='bx bx-time-five'></i></div>
                                <span class="menu-title">Gestión de Turnos</span>
                            </a>
                        </li>

                        <li class="menu-label"><span>Catálogo</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}"
                                href="{{ route('admin.products') }}">
                                <div class="parent-icon"><i class='bx bxs-dish'></i></div>
                                <span class="menu-title">Productos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.coupons*') ? 'active' : '' }}"
                                href="{{ route('admin.coupons') }}">
                                <div class="parent-icon"><i class='bx bxs-purchase-tag'></i></div>
                                <span class="menu-title">Cupones</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.loyalty*') ? 'active' : '' }}"
                                href="{{ route('admin.loyalty.index') }}">
                                <div class="parent-icon"><i class='bx bxs-star'></i></div>
                                <span class="menu-title">Fidelización</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}"
                                href="{{ route('admin.reviews') }}">
                                <div class="parent-icon position-relative">
                                    <i class='bx bxs-message-detail'></i>
                                    <span class="pro-badge">PRO</span>
                                </div>
                                <span class="menu-title">Reseñas</span>
                            </a>
                        </li>

                        @if (in_array(auth()->user()?->role, ['developer', 'admin'], true))
                            <li class="menu-label"><span>BLStudio</span></li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('laboratorio.index') || request()->routeIs('laboratorio.historial') ? 'active' : '' }}"
                                    href="{{ route('laboratorio.index') }}">
                                    <div class="parent-icon"><i class='bx bxs-flask'></i></div>
                                    <span class="menu-title">Laboratorio</span>
                                </a>
                            </li>

                            @if (auth()->user()?->role === 'developer')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('laboratorio.admin.*') ? 'active' : '' }}"
                                        href="{{ route('laboratorio.admin.index') }}">
                                        <div class="parent-icon"><i class='bx bx-code-block'></i></div>
                                        <span class="menu-title">Gestionar Lab</span>
                                    </a>
                                </li>
                            @endif

                            <li class="menu-label"><span>Sistema</span></li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}"
                                    href="{{ route('admin.users.index') }}">
                                    <div class="parent-icon"><i class='bx bxs-user-detail'></i></div>
                                    <span class="menu-title">Usuarios</span>
                                </a>
                            </li>
                        @endif

                        <li class="menu-label"><span>Sitio</span></li>

                        <li class="nav-item">
                            <a class="nav-link home-link" href="{{ route('home') }}">
                                <div class="parent-icon"><i class='bx bx-store-alt' style="color:#ff6b6b;"></i></div>
                                <span class="menu-title">Volver al Sitio</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Theme toggle --}}
                    <div class="theme-toggle-section">
                        <button type="button" class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Cambiar tema">
                            <span class="t-icon"><i class="bx bx-moon" id="themeIcon"></i></span>
                            <span class="t-label" id="themeLabel">Modo oscuro</span>
                        </button>
                    </div>

                    {{-- User pill + logout dropdown --}}
                    @auth
                        @php
                            $u = auth()->user();
                            $userInitial = strtoupper(mb_substr(trim($u->name), 0, 1) ?: '?');
                            $roleLabel = config('permissions.roles.' . $u->role) ?? ucfirst($u->role);
                            $roleColor = match($u->role) {
                                'developer' => '#f5a623',
                                'admin'     => '#3ecf8e',
                                'cajero'    => '#38b6ff',
                                'kitchen'   => '#ff6b35',
                                'delivery'  => '#a78bfa',
                                default     => '#9a9a9a',
                            };
                        @endphp
                        <div class="user-pill-section" id="userPillSection">
                            <button type="button" class="user-pill" id="userPillBtn" onclick="toggleUserMenu(event)">
                                <span class="user-pill-avatar" style="background:{{ $roleColor }};">{{ $userInitial }}</span>
                                <span class="user-pill-info">
                                    <span class="user-pill-name">{{ $u->name }}</span>
                                    <span class="user-pill-role" style="color:{{ $roleColor }};">{{ $roleLabel }}</span>
                                </span>
                                <i class='bx bx-chevron-up user-pill-chevron'></i>
                            </button>
                            <div class="user-pill-menu" id="userPillMenu" role="menu">
                                <div class="user-pill-menu-head">
                                    <div class="user-pill-menu-name">{{ $u->name }}</div>
                                    <div class="user-pill-menu-email">{{ $u->email }}</div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="user-pill-menu-action danger">
                                        <i class='bx bx-log-out'></i>
                                        <span>Cerrar sesión</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                    {{-- Config section (Settings) - bottom pill Rocker --}}
                    <div class="config-section">
                        <a class="nav-link config-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}"
                            href="{{ route('admin.settings') }}">
                            <div class="parent-icon"><i class='bx bx-cog'></i></div>
                            <span class="menu-title">Configuración</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Botón toggle minimalista -->
                <div class="sidebar-toggle-btn collapsed-state" onclick="toggleSidebar()" title="Click: expandir/colapsar | Doble click: ultra-mini"></div>
                
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-1 pb-1 mb-2 border-bottom">
                    <!-- Removed Dashboard title as per user request -->
                    <!-- Moved to turnos view -->
                </div>

                <!-- Alerts -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <lord-icon
                            src="{{ asset('lordicons/error.json') }}"
                            colors="primary:#dc3545,secondary:#dc3545"
                            trigger="hover"
                            style="width:20px;height:20px;display:inline-block;vertical-align:middle;margin-right:8px;">
                        </lord-icon>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        function updateToggleButtonPosition(toggleBtn, state) {
            if (!toggleBtn) return;
            toggleBtn.classList.remove('ultra-mini-state', 'collapsed-state', 'expanded-state');
            toggleBtn.classList.add(state + '-state');
            // Update position based on state
            if (state === 'ultra-mini') {
                toggleBtn.style.left = '16px';
            } else if (state === 'collapsed') {
                toggleBtn.style.left = '70px';
            } else if (state === 'expanded') {
                toggleBtn.style.left = '250px';
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            const configSection = document.querySelector('.config-section');
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');

            // 3-State cycle: ultra-mini → collapsed → expanded → collapsed → ultra-mini
            if (sidebar.classList.contains('ultra-mini')) {
                // ultra-mini → collapsed
                sidebar.classList.remove('ultra-mini');
                sidebar.classList.add('collapsed');
                configSection.style.width = '50px';
                mainContent.style.marginLeft = '70px';
                mainContent.style.width = 'calc(100% - 70px)';
                updateToggleButtonPosition(toggleBtn, 'collapsed');
                localStorage.setItem('sidebarState', 'collapsed');
            } else if (sidebar.classList.contains('collapsed')) {
                // collapsed → expanded
                sidebar.classList.remove('collapsed');
                sidebar.classList.add('expanded');
                configSection.style.width = '250px';
                mainContent.style.marginLeft = '250px';
                mainContent.style.width = 'calc(100% - 250px)';
                updateToggleButtonPosition(toggleBtn, 'expanded');
                localStorage.setItem('sidebarState', 'expanded');
            } else if (sidebar.classList.contains('expanded')) {
                // expanded → collapsed
                sidebar.classList.remove('expanded');
                sidebar.classList.add('collapsed');
                configSection.style.width = '50px';
                mainContent.style.marginLeft = '70px';
                mainContent.style.width = 'calc(100% - 70px)';
                updateToggleButtonPosition(toggleBtn, 'collapsed');
                localStorage.setItem('sidebarState', 'collapsed');
            }
        }

        // Double-click toggle to go to ultra-mini from collapsed
        function toggleSidebarDouble() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            const configSection = document.querySelector('.config-section');
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');

            if (sidebar.classList.contains('collapsed')) {
                // collapsed → ultra-mini
                sidebar.classList.remove('collapsed');
                sidebar.classList.add('ultra-mini');
                configSection.style.width = '0';
                mainContent.style.marginLeft = '16px';
                mainContent.style.width = 'calc(100% - 16px)';
                updateToggleButtonPosition(toggleBtn, 'ultra-mini');
                localStorage.setItem('sidebarState', 'ultra-mini');
            } else if (sidebar.classList.contains('expanded')) {
                // expanded → collapsed (single click behavior)
                sidebar.classList.remove('expanded');
                sidebar.classList.add('collapsed');
                configSection.style.width = '50px';
                mainContent.style.marginLeft = '70px';
                mainContent.style.width = 'calc(100% - 70px)';
                updateToggleButtonPosition(toggleBtn, 'collapsed');
                localStorage.setItem('sidebarState', 'collapsed');
            } else if (sidebar.classList.contains('ultra-mini')) {
                // ultra-mini → collapsed
                sidebar.classList.remove('ultra-mini');
                sidebar.classList.add('collapsed');
                configSection.style.width = '50px';
                mainContent.style.marginLeft = '70px';
                mainContent.style.width = 'calc(100% - 70px)';
                updateToggleButtonPosition(toggleBtn, 'collapsed');
                localStorage.setItem('sidebarState', 'collapsed');
            }
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedState = localStorage.getItem('sidebarState') || 'collapsed';
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            const configSection = document.querySelector('.config-section');
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');

            // Reset classes
            sidebar.classList.remove('ultra-mini', 'collapsed', 'expanded');

            if (savedState === 'expanded') {
                sidebar.classList.add('expanded');
                configSection.style.width = '250px';
                mainContent.style.marginLeft = '250px';
                mainContent.style.width = 'calc(100% - 250px)';
                updateToggleButtonPosition(toggleBtn, 'expanded');
            } else if (savedState === 'ultra-mini') {
                sidebar.classList.add('ultra-mini');
                configSection.style.width = '0';
                mainContent.style.marginLeft = '16px';
                mainContent.style.width = 'calc(100% - 16px)';
                updateToggleButtonPosition(toggleBtn, 'ultra-mini');
            } else {
                // Default to collapsed
                sidebar.classList.add('collapsed');
                configSection.style.width = '50px';
                mainContent.style.marginLeft = '70px';
                mainContent.style.width = 'calc(100% - 70px)';
                updateToggleButtonPosition(toggleBtn, 'collapsed');
            }

            // Add double-click support for toggle button
            if (toggleBtn) {
                let lastClick = 0;
                toggleBtn.addEventListener('click', function(e) {
                    const now = Date.now();
                    if (now - lastClick < 300) {
                        // Double click detected
                        e.preventDefault();
                        e.stopPropagation();
                        toggleSidebarDouble();
                    }
                    lastClick = now;
                });
            }

            // Inicializar iconos Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
                console.log('🎨 Iconos Lucide inicializados');
            }
        });
    </script>

    {{-- =========================================================
         Rescate global de overlays/backdrops "pegados" en el admin.
         Casos cubiertos:
           - Bootstrap deja un .modal-backdrop huérfano sin .modal.show.
           - body queda con clase modal-open + padding-right inline.
           - Overlays "futuristas" personalizados (turnos/settings) que
             se crean con position:fixed; inset:0; backdrop-filter:blur
             y se quedan en el DOM si una promesa/fetch falla.
         Atajos: ESC simple = autocura suave. Doble ESC (<600ms) = pánico.
       ========================================================= --}}
    <script>
        (function () {
            'use strict';

            function isFullscreenOverlay(el) {
                if (!el || el.nodeType !== 1) return false;
                if (el === document.body || el === document.documentElement) return false;
                // Nunca tocar estructura propia del admin
                if (el.id === 'sidebar' || el.closest && el.closest('#sidebar')) return false;
                if (el.classList && (el.classList.contains('main-content') || el.classList.contains('sidebar-toggle-btn'))) return false;
                // Nunca eliminar modales de configuración propios
                if (el.classList && el.classList.contains('config-panel-overlay')) return false;
                const cs = window.getComputedStyle(el);
                // Ampliado: fixed, absolute y sticky pueden cubrir el viewport
                if (cs.position !== 'fixed' && cs.position !== 'absolute' && cs.position !== 'sticky') return false;
                if (cs.display === 'none' || cs.visibility === 'hidden') return false;
                if (parseFloat(cs.opacity) === 0) return false;
                const rect = el.getBoundingClientRect();
                const coversViewport = rect.width >= window.innerWidth * 0.9 &&
                                       rect.height >= window.innerHeight * 0.9 &&
                                       rect.top <= 5 && rect.left <= 5;
                if (!coversViewport) return false;
                const hasBlur = (cs.backdropFilter && cs.backdropFilter.indexOf('blur') !== -1) ||
                                (cs.webkitBackdropFilter && cs.webkitBackdropFilter.indexOf('blur') !== -1);
                const bg = cs.backgroundColor || '';
                const hasDarkBg = /rgba?\(\s*0\s*,\s*0\s*,\s*0/.test(bg) || /rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*0?\.[3-9]/.test(bg);
                // Capa transparente pero que bloquea clics con z-index alto => también es molesta
                const zi = parseInt(cs.zIndex, 10);
                const blocksClicks = cs.pointerEvents !== 'none' && !isNaN(zi) && zi >= 1000;
                return hasBlur || hasDarkBg || blocksClicks;
            }

            // Limpia filtros globales pegados en <html>/<body> (p.ej. brightness, blur, opacity).
            function cleanGlobalFilters() {
                const roots = [document.documentElement, document.body];
                let changed = 0;
                roots.forEach(function (r) {
                    if (!r) return;
                    const cs = window.getComputedStyle(r);
                    if (cs.filter && cs.filter !== 'none') { r.style.filter = 'none'; changed++; }
                    if (cs.backdropFilter && cs.backdropFilter !== 'none') { r.style.backdropFilter = 'none'; changed++; }
                    if (parseFloat(cs.opacity) < 1) { r.style.opacity = '1'; changed++; }
                    if (cs.pointerEvents === 'none') { r.style.pointerEvents = 'auto'; changed++; }
                });
                return changed;
            }

            function visibleBootstrapModalExists() {
                return !!document.querySelector('.modal.show');
            }

            function cleanBodyModalState() {
                document.body.classList.remove('modal-open');
                if (document.body.style.paddingRight) document.body.style.paddingRight = '';
                if (document.body.style.overflow) document.body.style.overflow = '';
            }

            // Limpieza suave: solo elimina backdrops huérfanos (no hay modal visible).
            function softCleanup() {
                if (visibleBootstrapModalExists()) return false;
                let removed = 0;
                document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                    b.parentNode && b.parentNode.removeChild(b);
                    removed++;
                });
                if (removed > 0 || document.body.classList.contains('modal-open')) {
                    cleanBodyModalState();
                    console.warn('[admin-rescue] Limpieza suave: ' + removed + ' backdrop(s) huérfanos removidos.');
                    return true;
                }
                return false;
            }

            // Limpieza pánico: ESC doble. Borra TODOS los overlays a pantalla
            // completa (incluidos los custom de turnos/settings) y backdrops.
            function panicCleanup() {
                let removed = 0;
                document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                    b.parentNode && b.parentNode.removeChild(b); removed++;
                });
                // Búsqueda profunda (no solo body.children): cualquier overlay fullscreen
                // en cualquier nivel del DOM que no sea un modal Bootstrap visible legítimo.
                document.querySelectorAll('body *').forEach(function (el) {
                    if (!el || el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
                    if (el.classList && el.classList.contains('modal') && el.classList.contains('show')) return;
                    if (el.closest && el.closest('.modal.show')) return;
                    if (el.classList && el.classList.contains('config-panel-overlay')) return;
                    if (isFullscreenOverlay(el)) {
                        el.parentNode && el.parentNode.removeChild(el); removed++;
                    }
                });
                // Además limpiar filtros globales aplicados a html/body
                removed += cleanGlobalFilters();
                cleanBodyModalState();
                console.warn('[admin-rescue] Limpieza pánico: ' + removed + ' overlay(s) eliminados.');
                return removed;
            }

            // Auto-cura tras cerrar un modal Bootstrap
            document.addEventListener('hidden.bs.modal', function () {
                setTimeout(softCleanup, 50);
            });

            // Auto-cura periódica defensiva: si hay backdrop pero ningún modal visible
            // por más de 1.5s, limpia.
            let stuckSince = 0;
            setInterval(function () {
                const hasBackdrop = !!document.querySelector('.modal-backdrop');
                const hasVisibleModal = visibleBootstrapModalExists();
                if (hasBackdrop && !hasVisibleModal) {
                    if (!stuckSince) stuckSince = Date.now();
                    else if (Date.now() - stuckSince > 1500) {
                        softCleanup();
                        stuckSince = 0;
                    }
                } else {
                    stuckSince = 0;
                }
            }, 750);

            // ESC doble = pánico
            let lastEsc = 0;
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape' && e.keyCode !== 27) return;
                const now = Date.now();
                if (now - lastEsc < 600) {
                    panicCleanup();
                    lastEsc = 0;
                } else {
                    lastEsc = now;
                    // ESC simple: dejar que Bootstrap cierre el modal, luego intentar cura suave
                    setTimeout(softCleanup, 100);
                }
            }, true);

            // Exponer helper manual desde consola
            window.__adminRescue = { soft: softCleanup, panic: panicCleanup, filters: cleanGlobalFilters };

            // Auto-ejecutar al cargar: si al entrar a /admin ya hay una "tela" pegada
            // (por navegación desde modal, por scripts previos, etc.), la quitamos sin
            // requerir que el usuario presione ESC.
            function autoRescueOnLoad() {
                try {
                    cleanGlobalFilters();
                    // Primero intento suave (backdrops huérfanos)
                    softCleanup();
                    // Si sigue habiendo overlay fullscreen sin modal visible, pánico.
                    if (!visibleBootstrapModalExists()) {
                        const stuck = Array.prototype.slice.call(document.querySelectorAll('body *'))
                            .some(function (el) { return isFullscreenOverlay(el); });
                        if (stuck) panicCleanup();
                    }
                } catch (e) { console.warn('[admin-rescue] auto error:', e); }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', autoRescueOnLoad);
            } else {
                autoRescueOnLoad();
            }
            // Y otra pasada al terminar de cargar todos los recursos (iconos, lord-icons, etc.)
            window.addEventListener('load', function () { setTimeout(autoRescueOnLoad, 300); });

            // Observador: si un script inyecta un overlay fullscreen mientras no hay modal,
            // lo eliminamos automáticamente.
            try {
                const mo = new MutationObserver(function (muts) {
                    if (visibleBootstrapModalExists()) return;
                    for (const m of muts) {
                        for (const n of m.addedNodes) {
                            if (n.nodeType !== 1) continue;
                            if (n.classList && n.classList.contains('modal-backdrop')) continue;
                            if (n.classList && n.classList.contains('config-panel-overlay')) continue;
                            if (isFullscreenOverlay(n)) {
                                n.parentNode && n.parentNode.removeChild(n);
                                console.warn('[admin-rescue] Overlay inyectado removido automáticamente:', n);
                            }
                        }
                    }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            } catch (e) { /* noop */ }
        })();
    </script>

    @stack('scripts')

    <script>
    /* ---- Dark mode theme toggle ---- */
    function applyTheme(theme, animate) {
        if (animate) {
            document.documentElement.classList.add('theme-switching');
            setTimeout(function() { document.documentElement.classList.remove('theme-switching'); }, 400);
        }
        var icon  = document.getElementById('themeIcon');
        var label = document.getElementById('themeLabel');
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (icon)  icon.className   = 'bx bx-sun';
            if (label) label.textContent = 'Modo claro';
        } else {
            document.documentElement.removeAttribute('data-theme');
            if (icon)  icon.className   = 'bx bx-moon';
            if (label) label.textContent = 'Modo oscuro';
        }
    }
    function toggleTheme() {
        var isDark   = document.documentElement.getAttribute('data-theme') === 'dark';
        var newTheme = isDark ? 'light' : 'dark';
        localStorage.setItem('admin-theme', newTheme);
        applyTheme(newTheme, true);
    }
    /* Aplicar tema guardado al cargar */
    (function() {
        var saved = localStorage.getItem('admin-theme') || 'light';
        applyTheme(saved, false);
    })();

    /* ---- User pill menu ---- */
    function toggleUserMenu(event) {
        event.stopPropagation();
        var section = document.getElementById('userPillSection');
        var menu    = document.getElementById('userPillMenu');
        var btn     = document.getElementById('userPillBtn');
        if (!section) return;

        var isOpen = section.classList.toggle('open');

        // Si el sidebar está colapsado, posicionamos el menú con fixed
        // para escapar del overflow:hidden del sidebar
        var sidebar = document.querySelector('.rocker-sidebar');
        var collapsed = sidebar && (sidebar.classList.contains('collapsed') || sidebar.classList.contains('ultra-mini'));
        if (menu && btn && collapsed) {
            if (isOpen) {
                var r = btn.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.left     = (r.right + 10) + 'px';
                menu.style.bottom   = (window.innerHeight - r.bottom) + 'px';
                menu.style.top      = 'auto';
                menu.style.right    = 'auto';
                menu.style.width    = '220px';
            } else {
                menu.style.position = '';
                menu.style.left = '';
                menu.style.bottom = '';
                menu.style.width = '';
            }
        } else if (menu) {
            menu.style.position = '';
            menu.style.left = '';
            menu.style.bottom = '';
            menu.style.width = '';
        }
    }
    document.addEventListener('click', function(e) {
        var section = document.getElementById('userPillSection');
        if (!section) return;
        if (!section.contains(e.target)) section.classList.remove('open');
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var section = document.getElementById('userPillSection');
            if (section) section.classList.remove('open');
        }
    });
    </script>
</body>

</html>
