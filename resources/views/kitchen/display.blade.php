<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pantalla de Cocina - TCocina</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Bangers (misma fuente que los títulos del checkout) -->
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=DM+Sans:wght@500;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --kd-bg-1: #0c1929;
            --kd-bg-2: #0f2744;
            --kd-cyan: #00b4d8;
            --kd-cyan-dark: #0c6568;
            --kd-prep: #00b4d8;      /* preparing → cyan de marca */
            --kd-conf: #f59e0b;      /* confirmed → naranja cálido */
            --kd-ready: #16a34a;
            --kd-card-bg: #ffffff;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            background:
                linear-gradient(160deg, var(--kd-bg-1) 0%, var(--kd-bg-2) 60%, var(--kd-bg-1) 100%);
            font-family: 'DM Sans', system-ui, -apple-system, Segoe UI, sans-serif;
            margin: 0;
            padding: 12px 16px;
            min-height: 100vh;
            color: #e8edf5;
        }

        /* ── Header con la hora ── */
        .kitchen-header {
            text-align: center;
            margin-bottom: 12px;
            position: relative;
        }

        .kitchen-header .time {
            font-family: 'Bangers', cursive;
            font-size: clamp(4rem, 9vw, 7rem);
            font-weight: 400;
            color: #e8edf5;
            letter-spacing: 5px;
            line-height: 1;
            margin: 0;
            text-shadow: 0 6px 28px rgba(0, 180, 216, .35),
                         0 2px 4px rgba(0, 0, 0, .4);
        }

        /* ── Botón fullscreen ── */
        .fullscreen-toggle {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 1100;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .18);
            backdrop-filter: blur(8px);
            transition: opacity .25s ease, background .2s;
        }
        .fullscreen-toggle:hover { background: rgba(255, 255, 255, .22); color: #fff; }
        .fullscreen-toggle.fs-hidden { opacity: 0; pointer-events: none; }

        /* ── Filtros de microturnos ── */
        .filter-menu {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 22px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: .3px;
            border: 1.5px solid rgba(255, 255, 255, .25);
            background: rgba(255, 255, 255, .08);
            color: #e8edf5;
            border-radius: 999px;
            cursor: pointer;
            transition: all .2s ease;
            backdrop-filter: blur(6px);
        }
        .filter-btn:hover {
            background: rgba(255, 255, 255, .16);
            border-color: rgba(255, 255, 255, .45);
            transform: translateY(-1px);
        }
        .filter-btn.active {
            background: linear-gradient(135deg, var(--kd-cyan), var(--kd-cyan-dark));
            color: #fff;
            border-color: var(--kd-cyan);
            box-shadow: 0 6px 18px rgba(0, 180, 216, .42);
        }

        /* Badge de cantidad dentro del filter button */
        .filter-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 6px;
            min-width: 22px;
            height: 22px;
            padding: 0 7px;
            border-radius: 999px;
            background: rgba(245, 158, 11, .85);
            color: #fff;
            font-size: .72rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 2px 6px rgba(245, 158, 11, .4);
        }
        .filter-btn.active .filter-count {
            background: rgba(255, 255, 255, .95);
            color: var(--kd-cyan-dark);
            box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
        }

        /* ── Order cards ── */
        .order-card {
            background: var(--kd-card-bg);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 0;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .22);
            border-left: 6px solid #cbd5e1;
            transition: transform .15s, box-shadow .15s;
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(0, 0, 0, .28);
        }

        /* Estados → solo el borde indica estado (sin degradados) */
        .order-card.confirmed { border-left-color: var(--kd-conf); }
        .order-card.preparing { border-left-color: var(--kd-prep); }

        /* Header del pedido (badge + tiempo) */
        .order-top {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .kd-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .3px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .kd-pill.prep {
            background: linear-gradient(135deg, var(--kd-prep), var(--kd-cyan-dark));
            color: #fff;
            box-shadow: 0 3px 10px rgba(0, 180, 216, .32);
        }
        .kd-pill.conf {
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            color: #fff;
            box-shadow: 0 3px 10px rgba(245, 158, 11, .32);
        }
        .kd-pill.delivery {
            background: rgba(0, 180, 216, .12);
            color: #075b6e;
            border: 1px solid rgba(0, 180, 216, .35);
        }
        .kd-pill.pickup {
            background: rgba(245, 158, 11, .14);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, .4);
        }

        .order-time {
            font-size: .82rem;
            color: #6b7280;
            background: rgba(0, 0, 0, .05);
            padding: 3px 10px;
            border-radius: 999px;
            font-weight: 600;
        }

        /* Renglón 1: nombre (izq) · chips (centro) · estado (der) */
        .kd-card-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .customer-info {
            color: #0a2540;
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: .3px;
            line-height: 1.15;
            min-width: 0;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kd-head-status {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            min-width: 0;
        }

        /* Renglón 2: entrega (izq) · turno asignado (der) */
        .kd-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }
        .kd-pill.turno {
            background: rgba(0, 180, 216, .12);
            color: #075b6e;
            border: 1px solid rgba(0, 180, 216, .4);
            font-size: .82rem;
        }

        /* Resumen de cantidades por categoría (centrado) */
        .kd-summary {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 0 0 auto;
            flex-wrap: wrap;
            justify-content: center;
        }
        .kd-sum-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 8px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: .95rem;
            line-height: 1;
        }
        .kd-sum-chip i { font-size: .95rem; }
        .kd-ic-svg {
            display: inline-flex;
            align-items: center;
        }
        .kd-ic-svg svg { display: block; }
        .kd-sum-chip b {
            font-weight: 800;
            color: #0a2540;
            font-size: 1rem;
        }
        /* Hamburguesas: chip resaltado */
        .kd-sum-chip.burger {
            background: #fff7ed;
            border-color: #fdba74;
        }
        .kd-sum-chip.burger b { color: #c2410c; }

        .order-items { margin-bottom: 8px; }

        /* Título de sección por categoría (Hamburguesas, Bebidas, etc.) */
        .kd-cat-head {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: #1e293b;
            margin: 10px 0 2px;
            padding-bottom: 3px;
            border-bottom: 2px solid #cbd5e1;
        }
        .kd-cat-head:first-child { margin-top: 0; }
        .kd-cat-head .kd-cat-ic { color: #64748b; font-size: .9rem; }
        /* Hamburguesas: la sección más importante, acentuada */
        .kd-cat-head.cat-hamburguesas {
            color: #c2410c;
            border-bottom-color: #fdba74;
            font-size: .82rem;
        }
        .kd-cat-head.cat-hamburguesas .kd-cat-ic { color: #ea580c; }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid rgba(10, 37, 64, .07);
        }
        .order-item:last-child { border-bottom: none; }

        .item-name {
            font-weight: 700;
            color: #0a2540;
            font-size: .98rem;
        }
        /* Hamburguesas con nombre más grande/destacado */
        .order-item.is-burger .item-name {
            font-size: 1.12rem;
            font-weight: 800;
        }
        .item-details {
            font-size: .9rem;
            color: #334155;
            margin-top: 3px;
            font-weight: 600;
            line-height: 1.35;
        }
        /* Ingredientes de hamburguesas: más grandes y visibles para la cocina */
        .order-item.is-burger .item-details {
            font-size: 1.02rem;
            color: #1e293b;
            font-weight: 700;
            line-height: 1.4;
        }
        .item-quantity {
            background: linear-gradient(135deg, var(--kd-cyan), var(--kd-cyan-dark));
            color: #fff;
            min-width: 28px;
            text-align: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-weight: 800;
            font-size: .88rem;
            box-shadow: 0 3px 8px rgba(0, 180, 216, .3);
        }

        .order-notes {
            background: linear-gradient(135deg, rgba(245, 158, 11, .12), rgba(245, 158, 11, .04));
            border-left: 3px solid #f59e0b;
            border-radius: 6px;
            padding: 7px 10px;
            margin-top: 6px;
            font-size: .82rem;
            color: #92400e;
            font-weight: 600;
        }

        /* Acciones */
        .order-actions { margin-top: 10px; }
        .kd-action-btn {
            width: 100%;
            padding: 9px 14px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: .92rem;
            color: #fff;
            cursor: pointer;
            transition: transform .15s, box-shadow .2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .kd-action-btn.start {
            background: linear-gradient(135deg, #16a34a, #15803d);
            box-shadow: 0 4px 12px rgba(22, 163, 74, .32);
        }
        .kd-action-btn.ready {
            background: linear-gradient(135deg, var(--kd-cyan), var(--kd-cyan-dark));
            box-shadow: 0 4px 12px rgba(0, 180, 216, .32);
        }
        .kd-action-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .2);
        }

        /* Estado vacío */
        .no-orders {
            text-align: center;
            color: rgba(232, 237, 245, .65);
            font-size: 1.05rem;
            padding: 40px;
        }
        .no-orders i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: .35;
            color: #e8edf5;
        }

        .card-scroll {
            overflow: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(10, 37, 64, .25) transparent;
        }
        .card-scroll::-webkit-scrollbar { width: 4px; }
        .card-scroll::-webkit-scrollbar-thumb {
            background: rgba(10, 37, 64, .25);
            border-radius: 4px;
        }

        /* ── Modal flotante ── */
        .kd-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
        }
        .kd-modal.d-none { display: none; }

        .kd-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(8, 18, 36, .72);
            backdrop-filter: blur(6px);
        }

        .kd-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: min(900px, 92vw);
            max-height: 88vh;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, .55);
            padding: 22px;
            border-left: 6px solid var(--kd-conf);
        }

        .kd-close {
            position: absolute;
            top: 12px;
            right: 12px;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .08);
            color: #0a2540;
            border: none;
            transition: all .15s;
        }
        .kd-close:hover { background: rgba(220, 38, 38, .14); color: #dc2626; }

        .kd-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-right: 56px;
            gap: 12px;
        }

        .kd-title {
            font-family: 'Bangers', cursive;
            font-size: 1.6rem;
            font-weight: 400;
            color: #0a2540;
            letter-spacing: 1.5px;
            line-height: 1;
        }

        .kd-section {
            border-top: 1px solid rgba(10, 37, 64, .08);
            padding-top: 12px;
            margin-top: 12px;
        }

        .kd-item {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid rgba(10, 37, 64, .07);
        }
        .kd-item:last-child { border-bottom: 0; }

        .kd-note {
            background: linear-gradient(135deg, rgba(245, 158, 11, .14), rgba(245, 158, 11, .06));
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 10px 12px;
            color: #92400e;
            font-weight: 600;
        }

        /* Navegación modal */
        .kd-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .25);
            backdrop-filter: blur(8px);
            z-index: 1300;
            transition: all .2s;
        }
        .kd-prev { left: 14px; }
        .kd-next { right: 14px; }
        .kd-nav-btn:hover {
            background: rgba(255, 255, 255, .28);
            transform: translateY(-50%) scale(1.06);
        }

        .kd-page-enter-left  { animation: kdEnterLeft  .25s ease both; }
        .kd-page-enter-right { animation: kdEnterRight .25s ease both; }
        @keyframes kdEnterLeft  { from { transform: translateX(-40px); opacity: 0 } to { transform: translateX(0); opacity: 1 } }
        @keyframes kdEnterRight { from { transform: translateX( 40px); opacity: 0 } to { transform: translateX(0); opacity: 1 } }
    </style>
</head>

<body>
    <button id="fullscreen-btn" class="btn btn-dark fullscreen-toggle" onclick="toggleFullscreen()"
        title="Pantalla completa" aria-label="Pantalla completa">
        <i class="fas fa-expand"></i>
    </button>
    <div class="kitchen-header">
        <div class="time" id="current-time"></div>
    </div>

    <div class="filter-menu" id="microturno-filters">
        <!-- Microturnos como filtros se cargarán aquí -->
    </div>

    <div id="orders-grid" class="container-fluid"></div>

    <!-- Modal flotante de pedido -->
    <div id="kd-modal" class="kd-modal d-none">
        <div class="kd-backdrop" onclick="closeKdModal()"></div>
        <button class="kd-nav-btn kd-prev" onclick="kdPrev()" aria-label="Anterior"><i
                class="fas fa-chevron-left"></i></button>
        <div class="kd-content" role="dialog" aria-modal="true" aria-labelledby="kd-title">
            <button class="btn btn-sm btn-dark kd-close" onclick="closeKdModal()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
            <div id="kd-body"></div>
        </div>
        <button class="kd-nav-btn kd-next" onclick="kdNext()" aria-label="Siguiente"><i
                class="fas fa-chevron-right"></i></button>
    </div>

    <script>
        // Update time every second
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        updateTime();
        setInterval(updateTime, 1000);

        // Variables globales
        // Set vacío = "Todos". Si tiene elementos, se filtra por esos microturnos.
        let selectedMicroturnos = new Set();
        let allData = null;

        // Load orders and microturnos
        function loadOrders() {
            fetch('/kitchen/display/orders')
                .then(response => response.json())
                .then(data => {
                    allData = data;
                    renderMicroturnoFilters(data);
                    renderOrders(data);
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                });
        }

        function renderMicroturnoFilters(data) {
            const filtersContainer = document.getElementById('microturno-filters');
            const microturnos = data.microturnos || [];
            const pedidosPorMicroturno = data.pedidosPorMicroturno || {};

            const allActive = selectedMicroturnos.size === 0;

            let html = `<button class="filter-btn ${allActive ? 'active' : ''}" data-microturno="all">Todos</button>`;

            microturnos.forEach(microturno => {
                const pedidos = pedidosPorMicroturno[microturno.sort_order] || [];
                const count = pedidos.length;
                const isActive = selectedMicroturnos.has(microturno.sort_order);
                const countBadge = count > 0 ? `<span class="filter-count">${count}</span>` : '';

                html += `
                    <button class="filter-btn ${isActive ? 'active' : ''}" data-microturno="${microturno.sort_order}">
                        ${microturno.formatted_time}${countBadge}
                    </button>
                `;
            });

            filtersContainer.innerHTML = html;

            // Click handlers (multi-selección)
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const value = this.dataset.microturno;

                    if (value === 'all') {
                        // "Todos" → limpiar selección
                        selectedMicroturnos.clear();
                    } else {
                        const key = parseInt(value);
                        if (selectedMicroturnos.has(key)) {
                            selectedMicroturnos.delete(key);
                        } else {
                            selectedMicroturnos.add(key);
                        }
                    }

                    // Re-render solo los filtros (no toda la data) para reflejar el nuevo estado
                    renderMicroturnoFilters(allData);
                    renderOrders(allData);
                });
            });
        }

        // SVG de papas en cajita (estilo McDonald's) para acompañamientos
        const FRIES_SVG = `<svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 8V3.5"/><path d="M11 8V2.5"/><path d="M14 8V3"/><path d="M16 8V4.5"/><path d="M5 8h14l-1.4 12.2a1 1 0 0 1-1 .8H7.4a1 1 0 0 1-1-.8L5 8z"/><path d="M5.6 12h12.8"/></svg>`;

        // ── Agrupación de items por categoría (hamburguesas siempre primero) ──
        const KD_CAT_ORDER = [
            { slug: 'hamburguesas',   label: 'Hamburguesas',          icon: 'fa-burger',        color: '#ea580c' },
            { slug: 'combos',         label: 'Combos',                icon: 'fa-box',           color: '#7c3aed' },
            { slug: 'acompanamientos', label: 'Acompañamientos extra', svg: FRIES_SVG,           color: '#0891b2' },
            { slug: 'bebidas',        label: 'Bebidas',               icon: 'fa-bottle-water',  color: '#2563eb' },
            { slug: 'postres',        label: 'Postres',               icon: 'fa-ice-cream',     color: '#db2777' },
        ];

        function getItemCatSlug(item) {
            return (item.product && item.product.category && item.product.category.slug)
                ? item.product.category.slug
                : '__otros';
        }

        // Devuelve el HTML del ícono de la categoría (SVG custom o FontAwesome)
        function catIconHtml(cat, cls, useColor) {
            const colorStyle = useColor ? ` style="color:${cat.color}"` : '';
            if (cat.svg) return `<span class="kd-ic-svg ${cls || ''}"${colorStyle}>${cat.svg}</span>`;
            return `<i class="fas ${cat.icon} ${cls || ''}"${colorStyle}></i>`;
        }

        // Resumen de cantidades por categoría (chips ícono + número)
        function renderCatSummary(items) {
            const counts = {};
            (items || []).forEach(it => {
                const slug = getItemCatSlug(it);
                counts[slug] = (counts[slug] || 0) + (parseInt(it.quantity) || 0);
            });
            let html = '';
            KD_CAT_ORDER.forEach(cat => {
                const n = counts[cat.slug];
                if (!n) return;
                const isBurger = cat.slug === 'hamburguesas';
                html += `<span class="kd-sum-chip${isBurger ? ' burger' : ''}" title="${cat.label}: ${n}">
                    ${catIconHtml(cat, '', true)}<b>${n}</b>
                </span>`;
            });
            return html ? `<div class="kd-summary">${html}</div>` : '';
        }

        function renderItemRow(item, isBurger) {
            return `
                <div class="order-item${isBurger ? ' is-burger' : ''}">
                    <div style="min-width:0;flex:1;">
                        <div class="item-name">${item.product ? item.product.name : 'N/A'}</div>
                        ${item.configuration_text ? `<div class="item-details">${item.configuration_text}</div>` : ''}
                    </div>
                    <div class="item-quantity">${item.quantity}</div>
                </div>
            `;
        }

        function renderItemsByCategory(items) {
            const groups = {};
            (items || []).forEach(it => {
                const slug = getItemCatSlug(it);
                (groups[slug] = groups[slug] || []).push(it);
            });

            let html = '';
            const used = new Set();
            KD_CAT_ORDER.forEach(cat => {
                const list = groups[cat.slug];
                if (!list || !list.length) return;
                used.add(cat.slug);
                html += `<div class="kd-cat-head cat-${cat.slug}">${catIconHtml(cat, 'kd-cat-ic', false)}${cat.label}</div>`;
                html += list.map(item => renderItemRow(item, cat.slug === 'hamburguesas')).join('');
            });

            // Categorías no listadas o sin categoría → "Otros"
            const otros = [];
            Object.keys(groups).forEach(slug => {
                if (!used.has(slug)) otros.push(...groups[slug]);
            });
            if (otros.length) {
                html += `<div class="kd-cat-head"><i class="fas fa-utensils kd-cat-ic"></i>Otros</div>`;
                html += otros.map(item => renderItemRow(item, false)).join('');
            }
            return html;
        }

        function renderOrders(data) {
            const grid = document.getElementById('orders-grid');
            const header = document.querySelector('.kitchen-header');
            const filterMenu = document.querySelector('.filter-menu');
            const headerH = header ? header.offsetHeight : 100;
            const filterH = filterMenu ? filterMenu.offsetHeight : 50;
            const padding = 20;
            const availH = window.innerHeight - headerH - filterH - padding;
            const availW = window.innerWidth - padding;

            const pedidosPorMicroturno = data.pedidosPorMicroturno || {};
            const orders = data.orders || [];

            // Filtrar pedidos según los microturnos seleccionados (multi-selección)
            let filteredOrders = [];
            if (selectedMicroturnos.size === 0) {
                // Sin filtros → mostrar todos
                filteredOrders = orders;
            } else {
                // Unir todos los pedidos de los microturnos seleccionados (sin duplicar)
                const seen = new Set();
                selectedMicroturnos.forEach(key => {
                    const list = pedidosPorMicroturno[key] || [];
                    list.forEach(o => {
                        if (!seen.has(o.id)) {
                            seen.add(o.id);
                            filteredOrders.push(o);
                        }
                    });
                });
            }

            if (!filteredOrders || filteredOrders.length === 0) {
                grid.innerHTML = `
                    <div class="no-orders text-center" style="height:${availH}px; display:flex; align-items:center; justify-content:center;">
                        <div>
                            <i class="fas fa-cookie-bite fa-3x mb-3" style="opacity:.5; color: white;"></i>
                            <p class="text-white">No hay pedidos en este microturno</p>
                        </div>
                    </div>`;
                return;
            }

            // Calcular grid dinámico
            const cols = Math.ceil(Math.sqrt(filteredOrders.length));
            const rows = Math.ceil(filteredOrders.length / cols);
            const gap = 16;
            const cardH = Math.max(150, Math.floor((availH - gap * (rows + 1)) / rows));
            const cardW = Math.max(260, Math.floor((availW - gap * (cols + 1)) / cols));

            grid.style.display = 'grid';
            grid.style.gridTemplateColumns = `repeat(${cols}, ${cardW}px)`;
            grid.style.gridAutoRows = `${cardH}px`;
            grid.style.gap = `${gap}px`;
            grid.style.height = `${availH}px`;
            grid.style.overflow = 'hidden';
            grid.style.alignContent = 'start';
            grid.style.justifyContent = 'center';

            grid.innerHTML = filteredOrders.map(order => {
                const isPreparing = order.status === 'preparing';
                const badge = isPreparing
                    ? '<span class="kd-pill prep"><i class="fas fa-fire"></i>Preparación</span>'
                    : '<span class="kd-pill conf"><i class="fas fa-clock"></i>Confirmado</span>';

                const addr = order.address
                    ? `${(order.address.street || '')} ${(order.address.number || '')}`.trim()
                        + `${order.address.neighborhood ? ' · ' + order.address.neighborhood : ''}`
                        + `${order.address.city ? ' · ' + order.address.city : ''}`
                    : null;
                const addrBadge = addr
                    ? `<span class="kd-pill delivery"><i class="fas fa-truck"></i>${addr}</span>`
                    : `<span class="kd-pill pickup"><i class="fas fa-store"></i>Retiro en local</span>`;

                const turnoBadge = order.turno_label
                    ? `<span class="kd-pill turno"><i class="fas fa-clock"></i>${order.turno_label}</span>`
                    : '';

                return `
                <div class="order-card ${order.status}" style="height:${cardH}px;" onclick="openKdModal(${order.id})">
                    <div class="kd-card-head">
                        <div class="customer-info">
                            ${order.contact_name || (order.user ? order.user.name : 'Invitado')}
                        </div>
                        ${renderCatSummary(order.items)}
                        <div class="kd-head-status">${badge}</div>
                    </div>
                    <div class="kd-meta-row">
                        ${addrBadge}
                        ${turnoBadge}
                    </div>
                    <div class="order-items card-scroll" style="max-height:${Math.max(40, cardH - 140)}px;">
                        ${renderItemsByCategory(order.items)}
                        ${order.notes ? `<div class="order-notes"><i class="fas fa-exclamation-triangle me-1"></i>${order.notes}</div>` : ''}
                    </div>
                    <div class="order-actions">
                        ${order.status === 'confirmed'
                            ? `<button class="kd-action-btn start" onclick="event.stopPropagation(); startPreparation(${order.id})"><i class="fas fa-play"></i>Iniciar preparación</button>`
                            : `<button class="kd-action-btn ready" onclick="event.stopPropagation(); markReady(${order.id})"><i class="fas fa-check"></i>Marcar entregado</button>`
                        }
                    </div>
                </div>
                `;
            }).join('');
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Load orders initially and then every 5 seconds (refresco automático)
        loadOrders();
        setInterval(loadOrders, 5000);

        // Refrescar al instante cuando la pestaña vuelve a foco (ej: tras confirmar un pedido en otra ventana)
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') loadOrders();
        });

        // Actions
        function startPreparation(orderId) {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';
            fetch(`/kitchen/orders/${orderId}/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                }).then(res => res.ok ? res.json() : Promise.reject(res))
                .then(() => loadOrders())
                .catch(() => alert('No se pudo iniciar preparación (CSRF o permisos).'));
        }

        function markReady(orderId) {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';
            fetch(`/kitchen/orders/${orderId}/ready`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                }).then(res => res.ok ? res.json() : Promise.reject(res))
                .then(() => loadOrders())
                .catch(() => alert('No se pudo marcar como listo (CSRF o permisos).'));
        }

        // Fullscreen functionality
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F11') {
                e.preventDefault();
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            }
        });

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        }

        function updateFullscreenButton() {
            const btn = document.getElementById('fullscreen-btn');
            if (!btn) return;
            if (document.fullscreenElement) {
                btn.innerHTML = '<i class="fas fa-compress"></i>';
                btn.title = 'Salir de pantalla completa';
            } else {
                btn.innerHTML = '<i class="fas fa-expand"></i>';
                btn.title = 'Pantalla completa';
            }
            handleFsAutoHide();
        }
        document.addEventListener('fullscreenchange', updateFullscreenButton);

        // Auto-ocultar botón en fullscreen, mostrar al mover el mouse
        let fsHideTimeout = null;

        function handleFsAutoHide() {
            const btn = document.getElementById('fullscreen-btn');
            if (!btn) return;
            if (!document.fullscreenElement) {
                btn.classList.remove('fs-hidden');
                if (fsHideTimeout) clearTimeout(fsHideTimeout);
                return;
            }
            // mostrar y programar ocultado
            btn.classList.remove('fs-hidden');
            if (fsHideTimeout) clearTimeout(fsHideTimeout);
            fsHideTimeout = setTimeout(() => btn.classList.add('fs-hidden'), 2000);
        }
        document.addEventListener('mousemove', () => {
            if (document.fullscreenElement) handleFsAutoHide();
        });

        // Modal helpers con navegación
        let kdOrderIds = [];
        let kdCurrentIndex = -1;

        function openKdModal(orderId, direction) {
            const modal = document.getElementById('kd-modal');
            const body = document.getElementById('kd-body');
            if (!modal || !body) return;

            fetch('/kitchen/display/orders').then(r => r.json()).then(data => {
                const merged = data.orders || [];
                kdOrderIds = merged.map(o => o.id);
                kdCurrentIndex = kdOrderIds.indexOf(orderId);
                const currentId = kdCurrentIndex >= 0 ? kdOrderIds[kdCurrentIndex] : orderId;
                const order = merged.find(o => o.id === currentId);
                if (!order) return;
                const isPreparing = order.status === 'preparing';
                body.className = direction === 'left' ? 'kd-page-enter-left' : (direction === 'right' ?
                    'kd-page-enter-right' : '');
                body.innerHTML = `
                    <div class="kd-head">
                        <div class="kd-title">#${order.order_number} · ${order.contact_name || (order.user ? order.user.name : 'Invitado')}</div>
                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                            ${order.microturno ? `<span class="kd-pill" style="background:rgba(0,180,216,.14);color:#075b6e;border:1px solid rgba(0,180,216,.4);"><i class="fas fa-clock"></i>${order.microturno.formatted_time}</span>` : ''}
                            ${isPreparing
                                ? '<span class="kd-pill prep"><i class="fas fa-fire"></i>Preparación</span>'
                                : '<span class="kd-pill conf"><i class="fas fa-clock"></i>Confirmado</span>'}
                        </div>
                    </div>
                    <div class="kd-section">
                        ${order.address
                            ? `<span class="kd-pill delivery"><i class="fas fa-truck"></i>${
                                [`${order.address.street || ''} ${order.address.number || ''}`.trim(),
                                 order.address.neighborhood || '',
                                 order.address.city || ''].filter(Boolean).join(' · ')
                              }</span>`
                            : `<span class="kd-pill pickup"><i class="fas fa-store"></i>Retiro en local</span>`
                        }
                    </div>
                    <div class="kd-section">
                        ${renderItemsByCategory(order.items)}
                    </div>
                    ${order.notes ? `<div class="kd-section kd-note mt-2"><i class="fas fa-exclamation-triangle me-1"></i>${order.notes}</div>` : ''}
                    <div class="kd-section mt-3">
                        ${order.status === 'confirmed'
                            ? `<button class="kd-action-btn start" onclick="startPreparation(${order.id})"><i class="fas fa-play"></i>Iniciar preparación</button>`
                            : `<button class="kd-action-btn ready" onclick="markReady(${order.id})"><i class="fas fa-check"></i>Marcar como entregado</button>`
                        }
                    </div>
                `;
                modal.classList.remove('d-none');
                // ajustar borde del modal al estado
                const kdContent = modal.querySelector('.kd-content');
                if (kdContent) {
                    kdContent.style.borderLeftColor = isPreparing ? 'var(--kd-prep)' : 'var(--kd-conf)';
                }
            });
        }

        function closeKdModal() {
            const modal = document.getElementById('kd-modal');
            if (modal) modal.classList.add('d-none');
        }

        function kdPrev() {
            if (kdOrderIds.length === 0) return;
            kdCurrentIndex = (kdCurrentIndex - 1 + kdOrderIds.length) % kdOrderIds.length;
            openKdModal(kdOrderIds[kdCurrentIndex], 'left');
        }

        function kdNext() {
            if (kdOrderIds.length === 0) return;
            kdCurrentIndex = (kdCurrentIndex + 1) % kdOrderIds.length;
            openKdModal(kdOrderIds[kdCurrentIndex], 'right');
        }

        document.addEventListener('keydown', (e) => {
            const modal = document.getElementById('kd-modal');
            if (modal && !modal.classList.contains('d-none')) {
                if (e.key === 'ArrowLeft') kdPrev();
                if (e.key === 'ArrowRight') kdNext();
                if (e.key === 'Escape') closeKdModal();
            }
        });
    </script>
</body>

</html>
