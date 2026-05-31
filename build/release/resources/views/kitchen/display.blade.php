<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pantalla de Cocina - TecoCina</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
            /* sin scroll */
        }

        body {
            background: linear-gradient(135deg, #00b4d8 0%, #0096c7 100%);
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 10px;
            min-height: 100vh;
        }

        .fullscreen-toggle {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1100;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: opacity 0.25s ease;
        }

        .fullscreen-toggle.fs-hidden {
            opacity: 0;
            pointer-events: none;
        }

        .kitchen-header {
            text-align: center;
            color: white;
            margin-bottom: 5px;
            position: relative;
        }

        .kitchen-header .time {
            font-size: 6rem;
            font-weight: bold;
            opacity: 1;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
            letter-spacing: 3px;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        .filter-menu {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .filter-menu button {
            padding: 8px 24px;
            font-size: 1.1rem;
            font-weight: 600;
            border: 2px solid white;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-menu button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .filter-menu button.active {
            background: white;
            color: #0096c7;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .order-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            height: 45vh;
            /* ocupar media pantalla aprox */
            overflow: hidden;
            /* no scroll dentro del bloque */
        }

        .section-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            border-radius: 10px;
        }

        .confirmed-title {
            background: linear-gradient(135deg, #ffc107, #ff8c00);
            color: white;
        }

        .preparing-title {
            background: linear-gradient(135deg, #17a2b8, #007bff);
            color: white;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 6px solid #ddd;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
        }

        .order-card.preparing {
            border-left-color: #17a2b8;
            background: rgba(23, 162, 184, 0.08);
            box-shadow: 0 6px 16px rgba(23, 162, 184, 0.15);
        }

        .order-card.confirmed {
            border-left-color: #ffc107;
            background: rgba(255, 193, 7, 0.08);
            box-shadow: 0 6px 16px rgba(255, 193, 7, 0.15);
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .order-number {
            display: none;
        }

        .order-time {
            font-size: 1.2rem;
            color: #666;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .customer-info {
            color: #222;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .order-items {
            margin-bottom: 8px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 700;
            color: #111;
            font-size: 1rem;
        }

        .item-details {
            font-size: 0.85rem;
            color: #666;
            margin-top: 2px;
        }

        .item-quantity {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 15px;
            font-weight: bold;
        }

        .order-notes {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 8px;
            margin-top: 6px;
        }

        .no-orders {
            text-align: center;
            color: #666;
            font-size: 1.2rem;
            padding: 40px;
        }

        .no-orders i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-confirmed {
            background: #ffc107;
            color: #000;
        }

        .status-preparing {
            background: #17a2b8;
            color: white;
        }

        /* Diferenciación visual marcada */
        .order-card.confirmed {
            background: #fffceb;
            border-left-color: #ffc107;
        }

        .order-card.preparing {
            background: #e9f9ff;
            border-left-color: #17a2b8;
        }

        .card-scroll {
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Modal flotante */
        .kd-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
        }

        .kd-modal.d-none {
            display: none;
        }

        .kd-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
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
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            padding: 20px;
            border-left: 6px solid #ffc107;
            /* coincide con confirmados */
        }

        .kd-close {
            position: absolute;
            top: 10px;
            right: 10px;
            border-radius: 50%;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kd-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-right: 56px;
            /* evita superposición con el botón de cerrar */
        }

        .kd-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .kd-section {
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }

        .kd-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .kd-item:last-child {
            border-bottom: 0;
        }

        .kd-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 8px;
        }

        /* Navegación modal */
        .kd-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .6);
            color: #fff;
            border: none;
            z-index: 1300;
            /* por encima del contenido del modal */
        }

        .kd-prev {
            left: 8px;
        }

        .kd-next {
            right: 8px;
        }

        .kd-nav-btn:hover {
            background: rgba(0, 0, 0, .75);
        }

        .kd-page-enter-left {
            animation: kdEnterLeft .25s ease both;
        }

        .kd-page-enter-right {
            animation: kdEnterRight .25s ease both;
        }

        @keyframes kdEnterLeft {
            from {
                transform: translateX(-40px);
                opacity: .0
            }

            to {
                transform: translateX(0);
                opacity: 1
            }
        }

        @keyframes kdEnterRight {
            from {
                transform: translateX(40px);
                opacity: .0
            }

            to {
                transform: translateX(0);
                opacity: 1
            }
        }
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

    <div class="filter-menu">
        <button class="filter-btn active" data-filter="all">Todos</button>
        <button class="filter-btn" data-filter="confirmed">Confirmados</button>
        <button class="filter-btn" data-filter="preparing">En Preparación</button>
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

        // Variable para filtro actual
        let currentFilter = 'all';

        // Load orders (fusiona confirmados y en preparación según filtro)
        function loadOrders() {
            fetch('/kitchen/display/orders')
                .then(response => response.json())
                .then(data => {
                    const confirmed = (data && data.confirmed) || [];
                    const preparing = (data && data.preparing) || [];
                    let merged = [];

                    // Aplicar filtro
                    if (currentFilter === 'all') {
                        merged = [...confirmed, ...preparing];
                    } else if (currentFilter === 'confirmed') {
                        merged = confirmed;
                    } else if (currentFilter === 'preparing') {
                        merged = preparing;
                    }

                    renderGrid(merged);
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                });
        }

        function renderGrid(orders) {
            const grid = document.getElementById('orders-grid');
            const header = document.querySelector('.kitchen-header');
            const filterMenu = document.querySelector('.filter-menu');
            const headerH = header ? header.offsetHeight : 100;
            const filterH = filterMenu ? filterMenu.offsetHeight : 50;
            const padding = 20; // padding body reducido
            const availH = window.innerHeight - headerH - filterH - padding;
            const availW = window.innerWidth - padding;

            if (!orders || orders.length === 0) {
                grid.innerHTML = `
                    <div class="no-orders text-center" style="height:${availH}px; display:flex; align-items:center; justify-content:center;">
                        <div>
                            <i class="fas fa-cookie-bite fa-3x mb-3" style="opacity:.5"></i>
                            <p class="text-muted">No hay pedidos para mostrar</p>
                        </div>
                    </div>`;
                return;
            }

            // calcular columnas/filas para encajar todo
            const cols = Math.ceil(Math.sqrt(orders.length));
            const rows = Math.ceil(orders.length / cols);
            const gap = 16;
            const cardH = Math.max(150, Math.floor((availH - gap * (rows + 1)) / rows));
            const cardW = Math.max(260, Math.floor((availW - gap * (cols + 1)) / cols));

            // grid container
            grid.style.display = 'grid';
            grid.style.gridTemplateColumns = `repeat(${cols}, ${cardW}px)`;
            grid.style.gridAutoRows = `${cardH}px`;
            grid.style.gap = `${gap}px`;
            grid.style.height = `${availH}px`;
            grid.style.overflow = 'hidden';
            grid.style.alignContent = 'start';
            grid.style.justifyContent = 'center';

            grid.innerHTML = orders.map(order => {
                const isPreparing = order.status === 'preparing';
                const badge = isPreparing ?
                    '<span class="badge bg-info"><i class="fas fa-fire me-1"></i>Preparación</span>' :
                    '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Confirmado</span>';
                return `
                <div class="order-card ${order.status}" style="height:${cardH}px; border-left-color:${isPreparing ? '#17a2b8' : '#ffc107'}" onclick="openKdModal(${order.id})">
                    <div class="d-flex justify-content-end align-items-center mb-1">
                        ${badge}
                        <div class="order-time ms-2">${formatTime(order.created_at)}</div>
                    </div>
                    <div class="customer-info">
                        ${order.user ? order.user.name : (order.contact_name || 'Invitado')}
                    </div>
                    <div class="order-items card-scroll" style="max-height:${Math.max(40, cardH - 120)}px;">
                        ${order.items.slice(0,3).map(item => `
                                                                                        <div class="order-item">
                                                                                            <div>
                                                                                                <div class="item-name">${item.quantity}x ${item.product.name}</div>
                                                                                                ${item.selected_variants ? `<div class=\"item-details\">${item.selected_variants.map(v => v.value).join(', ')}</div>` : ''}
                                                                                                ${item.selected_options ? `<div class=\"item-details text-info\">${item.selected_options.map(o => o.value).join(', ')}</div>` : ''}
                                                                                            </div>
                                                                                            <div class="item-quantity">${item.quantity}</div>
                                                                                        </div>
                                                                                    `).join('')}
                        ${order.items.length > 3 ? order.items.slice(3).map(item => `
                                                                                <div class=\"order-item\">
                                                                                    <div>
                                                                                        <div class=\"item-name\">${item.quantity}x ${item.product.name}</div>
                                                                                        ${item.selected_variants ? `<div class=\\\"item-details\\\">${item.selected_variants.map(v => v.value).join(', ')}</div>` : ''}
                                                                                        ${item.selected_options ? `<div class=\\\"item-details text-info\\\">${item.selected_options.map(o => o.value).join(', ')}</div>` : ''}
                                                                                    </div>
                                                                                    <div class=\"item-quantity\">${item.quantity}</div>
                                                                                </div>`).join('') : ''}
                        ${order.notes ? `<div class=\"item-details text-warning mt-1\"><i class=\"fas fa-exclamation-triangle\"></i> ${order.notes}</div>` : ''}
                    </div>
                    <div class="mt-2">
                        ${order.status === 'confirmed' ? `
                                                                                        <button class=\"btn btn-success btn-sm w-100\" onclick=\"event.stopPropagation(); startPreparation(${order.id})\"> 
                                                                                            <i class=\"fas fa-play me-1\"></i> Iniciar preparación
                                                                                        </button>
                                                                                    ` : `
                                                                                        <button class=\"btn btn-primary btn-sm w-100\" onclick=\"event.stopPropagation(); markReady(${order.id})\"> 
                                                                                            <i class=\"fas fa-check me-1\"></i> Marcar listo
                                                                                        </button>
                                                                                    `}
                    </div>
                </div>`;
            }).join('');
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Load orders initially and then every 10 seconds
        // Manejar filtros
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                loadOrders();
            });
        });

        loadOrders();
        setInterval(loadOrders, 10000);
        window.addEventListener('resize', loadOrders);

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
                const merged = [...(data.confirmed || []), ...(data.preparing || [])];
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
                        <div class="kd-title">${order.user ? order.user.name : (order.contact_name || 'Invitado')}</div>
                        <div>${isPreparing ? '<span class=\"badge bg-info\"><i class=\"fas fa-fire me-1\"></i>Preparación</span>' : '<span class=\"badge bg-warning text-dark\"><i class=\"fas fa-clock me-1\"></i>Confirmado</span>'} <span class="order-time ms-2">${formatTime(order.created_at)}</span></div>
                    </div>
                    <div class="kd-section">
                        ${order.items.map(item => `
                                                    <div class=\"order-item\">
                                                        <div>
                                                            <div class=\"item-name\">${item.quantity}x ${item.product.name}</div>
                                                            ${item.selected_variants ? `<div class=\\\"item-details\\\">${item.selected_variants.map(v => v.value).join(', ')}</div>` : ''}
                                                            ${item.selected_options ? `<div class=\\\"item-details text-info\\\">${item.selected_options.map(o => o.value).join(', ')}</div>` : ''}
                                                        </div>
                                                        <div class=\"item-quantity\">${item.quantity}</div>
                                                    </div>
                                                `).join('')}
                    </div>
                    ${order.notes ? `<div class=\"kd-section kd-note mt-2\"><i class=\"fas fa-exclamation-triangle\"></i> ${order.notes}</div>` : ''}
                    <div class=\"kd-section mt-3\"> 
                        ${order.status === 'confirmed' ? `
                                                    <button class=\"btn btn-success btn-sm w-100\" onclick=\"startPreparation(${order.id})\"><i class=\"fas fa-play me-1\"></i> Iniciar preparación</button>` : `
                                                    <button class=\"btn btn-primary btn-sm w-100\" onclick=\"markReady(${order.id})\"><i class=\"fas fa-check me-1\"></i> Marcar listo</button>`}
                    </div>
                `;
                modal.classList.remove('d-none');
                // ajustar borde del modal al estado
                const kdContent = modal.querySelector('.kd-content');
                if (kdContent) {
                    kdContent.style.borderLeftColor = isPreparing ? '#17a2b8' : '#ffc107';
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
