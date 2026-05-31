@extends('layouts.menu')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Hero Section -->
        <section class="text-center animate-fade-in">
            <h2 class="font-playfair fw-bold mb-3 d-md-block d-none" style="font-size: 2rem;">
                Descubre Nuestra <span class="text-gradient">Carta Gourmet</span>
            </h2>
            <h2 class="font-playfair fw-bold mb-3 d-md-none d-block" style="font-size: 1.5rem;">
                Descubre Nuestra <span class="text-gradient">Carta Gourmet</span>
            </h2>

            <div class="gold-divider" style="max-width: 200px; margin: 1rem auto;"></div>
        </section>

        <!-- Popular Items Badge & View Toggle -->
        <section class="mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <!-- Filters Button -->
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#filtersModal">
                        <i class="bi bi-funnel"></i>
                        <span>Filtros</span>
                        <span id="active-filters-count" class="badge bg-white text-dark rounded-pill"
                            style="display: none;">0</span>
                    </button>

                    <!-- View Mode Toggle -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline" id="grid-view-btn" onclick="switchToGridView()">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" class="btn btn-outline" id="carousel-view-btn"
                            onclick="switchToCarouselView()">
                            <i class="bi bi-collection"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Menu Items Grid -->
        <section id="menu-grid" class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 mb-5">
            <!-- Menu items populated by JavaScript -->
        </section>

        <!-- Menu Items Carousel (Swiper) -->
        <section id="menu-carousel" class="mb-5" style="display: none;">
            <div class="swiper menuSwiper">
                <div class="swiper-wrapper">
                    <!-- Slides populated by JavaScript -->
                </div>

                <!-- Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>

                <!-- Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <!-- Loading State -->
        <div id="loading-state" class="d-none text-center py-5">
            <div class="spinner-custom mx-auto mb-3"></div>
            <p class="text-secondary">Cargando deliciosos platos...</p>
        </div>

        <!-- No Results State -->
        <div id="no-results" class="d-none text-center py-5">
            <i class="bi bi-search text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            <h3 class="font-playfair fw-semibold mt-3 mb-2">No se encontraron platos</h3>
            <p class="text-secondary">Intenta ajustar tus filtros de búsqueda</p>
        </div>
    </div>

    <!-- Shopping Cart Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" style="width: 420px; max-width: 90vw;">
        <div class="offcanvas-header navbar-custom p-3">
            <h6 class="offcanvas-title font-playfair fw-bold text-white mb-0">
                <i class="bi bi-bag-check me-2"></i>Tu Pedido
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body d-flex flex-column p-0">
            <!-- Cart Items -->
            <div id="cart-items" class="flex-grow-1 overflow-auto p-4">
                <div class="text-center py-5">
                    <i class="bi bi-bag-x text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                    <p class="text-secondary mt-3 mb-1 fw-semibold">Tu pedido está vacío</p>
                    <small class="text-muted">Agrega algunos platos deliciosos</small>
                </div>
            </div>

            <!-- Cart Footer -->
            <div class="border-top p-4" style="background-color: var(--neutral-beige);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold">Total:</span>
                    <span id="cart-total" class="price-display">€0,00</span>
                </div>
                <button id="checkout-btn" onclick="showConfirmOrderModal()" disabled
                    class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="bi bi-check-circle me-2"></i>Completar Orden
                </button>
                <button type="button" onclick="showOrdersModal()" class="btn btn-outline btn-sm w-100">
                    <i class="bi bi-receipt-cutoff me-2"></i>Ver Órdenes de Mesa
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Modal -->
    <div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
                <!-- Modal Header -->
                <div class="modal-header navbar-custom text-white border-0 p-3">
                    <h6 class="modal-title font-playfair fw-bold mb-0">
                        <i class="bi bi-funnel me-2"></i>Filtros y Búsqueda
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Search Input -->
                    <div class="mb-3">
                        <label for="search-input" class="form-label fw-semibold">
                            <i class="bi bi-search me-2 text-muted"></i>Buscar Platos
                        </label>
                        <input type="text" id="search-input" class="form-control"
                            placeholder="Buscar por nombre o descripción..." oninput="filterMenu()" />
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label for="category-filter" class="form-label fw-semibold">
                            <i class="bi bi-tag me-2 text-muted"></i>Categoría
                        </label>
                        <select id="category-filter" class="form-select" onchange="filterMenu()">
                            <option value="">Todas las categorías</option>
                            <!-- Categories populated by JavaScript -->
                        </select>
                    </div>

                    <!-- Price Sort -->
                    <div class="mb-3">
                        <label for="price-sort" class="form-label fw-semibold">
                            <i class="bi bi-sort-numeric-down me-2 text-muted"></i>Ordenar por Precio
                        </label>
                        <select id="price-sort" class="form-select" onchange="filterMenu()">
                            <option value="">Seleccionar orden</option>
                            <option value="asc">Menor a Mayor</option>
                            <option value="desc">Mayor a Menor</option>
                        </select>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline btn-sm" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Limpiar Filtros
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-check2 me-2"></i>
                        Aplicar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dish Details Modal (Compact) -->
    <div class="modal fade" id="dishModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
                <!-- Modal Header Minimal -->
                <div class="modal-header navbar-custom text-white border-0 py-3 px-3">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"
                        aria-label="Close" style="font-size: 0.7rem; padding: 0.25rem;"></button>
                </div>

                <!-- Modal Body Reorganizado -->
                <div class="modal-body p-3">
                    <div class="row g-3">
                        <!-- Columna Izquierda: Imagen + Info -->
                        <div class="col-md-7">
                            <!-- Imagen -->
                            <div class="img-hover-zoom rounded overflow-hidden mb-3" style="height: 180px;">
                                <img id="modal-main-image" src="" alt="" class="w-100 h-100"
                                    style="object-fit: cover;">
                            </div>

                            <!-- Título -->
                            <h4 id="modal-dish-name" class="font-playfair fw-bold mb-2">Plato</h4>

                            <!-- Category & Chef Badge -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span id="modal-dish-category" class="badge bg-light text-dark border">Categoría</span>
                                <span id="modal-chef-badge" class="badge badge-gold" style="display: none;">
                                    <i class="bi bi-star-fill me-1"></i>Chef
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <h6 class="font-playfair fw-semibold mb-2 small">Descripción</h6>
                                <p id="modal-dish-description" class="text-secondary small mb-0"
                                    style="line-height: 1.6;"></p>
                            </div>

                            <!-- Allergens Icons -->
                            <div id="modal-allergens-section" style="display: none;">
                                <h6 class="font-playfair fw-semibold mb-2 small">Alérgenos</h6>
                                <div id="modal-allergens" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            <div class="mb-3">
                                <h6 class="font-playfair fw-semibold mb-2">Ingredientes Principales</h6>
                                <div class="row g-2" id="modal-ingredients">
                                    <!-- Ingredients populated by JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Ingredientes + Formulario -->
                        <div class="col-md-5">
                            <div class="card bg-light">
                                <div class="card-body p-3 d-flex flex-column">
                                    <!-- Precio -->
                                    <div class="mb-3 text-center">
                                        <small class="text-secondary d-block mb-1">Precio</small>
                                        <div class="price-display fs-3" id="modal-dish-price">€0,00</div>
                                    </div>

                                    <hr class="my-2">

                                    <!-- Cantidad -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold mb-2 small">Cantidad</label>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="modalDecreaseQuantity()">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" id="modal-quantity" class="form-control text-center"
                                                min="1" value="1" oninput="modalUpdateTotalPrice()">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="modalIncreaseQuantity()">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Total -->
                                    <div class="mb-3 text-center p-2 bg-white rounded">
                                        <small class="text-secondary d-block mb-1">Total</small>
                                        <div class="price-display fs-2" id="modal-total-price">€0,00</div>
                                    </div>

                                    <!-- Notas -->
                                    <div class="mb-3">
                                        <label for="modal-notes" class="form-label fw-semibold mb-1 small">Notas
                                            (opcional)</label>
                                        <textarea id="modal-notes" class="form-control form-control-sm" rows="2" placeholder="Ej: Sin cebolla..."></textarea>
                                    </div>

                                    <!-- Botones -->
                                    <div class="mt-auto d-grid gap-2">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="modalAddToCart()">
                                            <i class="bi bi-cart-plus me-2"></i>
                                            Agregar al Pedido
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm"
                                            data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Orden -->
    <div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
                <!-- Modal Header -->
                <div class="modal-header navbar-custom text-white border-0 p-3">
                    <h6 class="modal-title font-playfair fw-bold mb-0">
                        <i class="bi bi-check-circle me-2"></i>Confirmar Orden
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-bag-check text-primary" style="font-size: 3rem;"></i>
                        <h4 class="font-playfair fw-semibold mt-3 mb-2">¿Completar tu pedido?</h4>
                        <p class="text-secondary">Se creará una orden pendiente con los siguientes items:</p>
                    </div>

                    <!-- Resumen del pedido -->
                    <div id="order-summary" class="mb-4">
                        <!-- Se llenará dinámicamente -->
                    </div>

                    <!-- Total -->
                    <div class="border-top pt-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Total del Pedido:</span>
                            <span id="modal-order-total" class="price-display fs-4 fw-bold">€0,00</span>
                        </div>
                    </div>

                    <!-- Notas adicionales -->
                    <div class="mb-3">
                        <label for="order-notes" class="form-label fw-semibold">
                            <i class="bi bi-chat-text me-2 text-muted"></i>Notas adicionales (opcional)
                        </label>
                        <textarea id="order-notes" class="form-control" rows="3" placeholder="Ej: Sin cebolla, bien cocido, etc..."></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="confirmOrder()">
                            <i class="bi bi-check-circle me-2"></i>Sí, Completar Orden
                        </button>
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
        <div id="toast" class="toast toast-custom hide" role="alert" style="display: none;">
            <div class="toast-header bg-success text-white border-0">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong class="me-auto">Éxito</strong>
                <button type="button" class="btn-close btn-close-white" onclick="hideToast()"></button>
            </div>
            <div class="toast-body bg-white">
                <span id="toast-message">Plato agregado al carrito</span>
            </div>
        </div>
    </div>

    <!-- Modal de Órdenes de Mesa -->
    <div class="modal fade" id="ordersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
                <div class="modal-header navbar-custom text-white border-0 p-3">
                    <h6 class="modal-title font-playfair fw-bold mb-0">
                        <i class="bi bi-receipt me-2"></i>Órdenes de la Mesa <span id="modal-mesa-number"></span>
                    </h6>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-light" onclick="loadOrdersForMesa()"
                            title="Actualizar órdenes">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <!-- Loading state -->
                    <div id="orders-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="text-muted mt-3">Cargando órdenes...</p>
                    </div>

                    <!-- Empty state -->
                    <div id="orders-empty" class="text-center py-5" style="display: none;">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h4 class="font-playfair fw-semibold mt-3 mb-2">Sin órdenes activas</h4>
                        <p class="text-secondary">No hay órdenes pendientes o en proceso para esta mesa.</p>
                    </div>

                    <!-- Orders list -->
                    <div id="orders-content" style="display: none;">
                        <div class="row" id="orders-container">
                            <!-- Orders will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize mesa number from URL
        const mesaNumber = {{ $mesa->numero ?? 'null' }};
    </script>
@endpush
