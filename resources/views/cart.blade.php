@extends('layouts.app')

@section('title', 'Carrito de Compras - TCocina')

@section('content')
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-6">
        <!-- Error/Success Messages -->
        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <lord-icon
                        src="{{ asset('lordicons/error.json') }}"
                        colors="primary:#dc3545,secondary:#dc3545"
                        trigger="hover"
                        style="width:20px;height:20px;display:inline-block;vertical-align:middle;margin-right:12px;">
                    </lord-icon>
                    <p class="text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-display text-text-primary mb-2">Tu Carrito</h1>
                </div>
                <a href="{{ route('catalog') }}"
                    class="hidden md:flex items-center space-x-2 text-primary hover:text-primary-600 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Seguir Comprando</span>
                </a>
            </div>
        </div>

        <!-- Cart Content -->
        <div>
            <!-- Cart Items -->
            <div class="card">
                <div class="p-4 border-b border-border-light">
                    <h2 class="text-lg font-display text-text-primary">Productos en tu Carrito</h2>
                    <span id="itemCount" class="text-text-secondary text-sm">0 productos</span>
                </div>

                <div id="cartItemsList" class="divide-y divide-border-light">
                    <!-- Cart items will be dynamically inserted here -->
                </div>

                <!-- Order Summary - Integrated at the end of cart items -->
                <div id="orderSummaryContainer" class="p-4 space-y-3 border-t border-border-light hidden">
                    <!-- Day Disabled Warning -->
                    @if (!$isDayEnabled)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                            <div class="flex items-start">
                                <lord-icon
                                    src="{{ asset('lordicons/error.json') }}"
                                    colors="primary:#dc3545,secondary:#dc3545"
                                    trigger="hover"
                                    style="width:20px;height:20px;display:inline-block;vertical-align:middle;margin-right:8px;margin-top:2px;">
                                </lord-icon>
                                <div>
                                    <p class="text-red-800 text-sm font-medium mb-1">
                                        Día no habilitado
                                    </p>
                                    <p class="text-red-700 text-xs">
                                        Hoy no estamos tomando pedidos. Por favor, intenta otro día.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Order Totals -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-text-primary text-sm">
                            <span>Subtotal</span>
                            <span id="subtotalAmount">$0</span>
                        </div>

                        <div id="shippingRow" class="flex justify-between text-text-primary text-sm hidden">
                            <span>Envío</span>
                            <span id="shippingAmount">$0</span>
                        </div>

                        <div class="flex justify-between text-lg font-display text-text-primary pt-2 border-t border-border-light">
                            <span>Total</span>
                            <span id="totalAmount">$0</span>
                        </div>
                    </div>

                    <!-- Notas para el local -->
                    <div>
                        <label for="orderNotes" class="block text-sm font-medium text-text-primary mb-1">Notas para el local</label>
                        <textarea id="orderNotes" class="input-field" rows="2" placeholder="Agregá notas simples para que el local tenga en cuenta."></textarea>
                        <div class="text-xs text-text-secondary mt-1">Opcional: estas notas se imprimirán en el ticket.</div>
                    </div>

                    <!-- Otras personas también agregaron -->
                    @if($acompanamientosProducts && $acompanamientosProducts->count() > 0)
                    <div class="mt-6 pt-4 border-t border-border-light">
                        <h3 class="text-sm font-display text-text-primary mb-3">Otras personas también agregaron:</h3>
                        <div class="acompanamientos-grid">
                            @foreach($acompanamientosProducts as $product)
                            <div class="acompanamiento-item border border-border-light rounded-lg p-2 cursor-pointer hover:border-primary transition-colors" data-product-id="{{ $product->id }}">
                                <div class="relative">
                                    <input type="checkbox" class="acompanamiento-checkbox absolute top-1 left-1 w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" data-product-id="{{ $product->id }}">
                                    @if($product->image)
                                        <img src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded mb-2" onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="{{ $product->name }}" class="w-full h-20 object-cover rounded mb-2">
                                    @endif
                                </div>
                                <h4 class="text-xs font-medium text-text-primary truncate">{{ $product->name }}</h4>
                                <p class="text-sm font-bold text-primary">${{ number_format($product->base_price, 0, ',', '.') }}</p>
                                <button class="acompanamiento-add-btn w-full mt-1 text-xs bg-primary text-white rounded py-1 hover:bg-primary-600 transition-colors" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}" data-product-category-id="{{ $product->category_id }}">
                                    Agregar
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button id="addSelectedAcompanamientos" class="flex-1 btn-primary text-white text-sm py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hidden" disabled>
                                <i class="fas fa-plus mr-2"></i>Agregar seleccionados (0)
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Checkout Button -->
                    <div>
                        <button id="checkoutBtn"
                            class="btn-primary w-full text-white disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$isDayEnabled ? 'disabled data-day-disabled="true"' : 'disabled' }}>
                            <i class="fas fa-clock mr-2"></i>
                            @if (!$isDayEnabled)
                                Día no Habilitado
                            @else
                                {{ $skipTurnoSelection ? 'Ir al Checkout' : 'Seleccionar Turno' }}
                            @endif
                        </button>
                    </div>
                </div>

                <!-- Empty Cart State -->
                <div id="emptyCartState" class="p-8 text-center hidden">
                    <i class="fas fa-shopping-cart text-4xl text-text-secondary mb-4"></i>
                    <h3 class="text-lg font-medium text-text-primary mb-2">Tu carrito está vacío</h3>
                    <p class="text-text-secondary mb-4">Agrega algunos productos deliciosos para comenzar</p>
                    <a href="{{ route('catalog') }}" class="btn-primary inline-flex items-center space-x-2">
                        <i class="fas fa-utensils"></i>
                        <span>Ver Menú</span>
                    </a>
                </div>
            </div>

            <!-- Continue Shopping (Mobile) -->
            <div class="mt-4 md:hidden">
                <a href="{{ route('catalog') }}"
                    class="flex items-center justify-center space-x-2 w-full py-3 text-primary hover:text-primary-600 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Seguir Comprando</span>
                </a>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="mt-6">
            <!-- Desktop Version -->
            <div class="desktop-progress-appbar d-none d-md-flex justify-content-center align-items-center p-3">
                <!-- Step 1: Carrito (Activo) -->
                <div class="desktop-progress-step active">
                    <div class="progress-icon">
                        <img src="{{ asset('productos/fondo/1tcocina.png') }}" alt="Carrito" class="progress-step-img">
                    </div>
                    <span class="progress-label">CARRITO</span>
                </div>

                <!-- Progress Line -->
                <div class="progress-line"></div>

                <!-- Step 2: Checkout (Inactivo) -->
                <div class="desktop-progress-step">
                    <div class="progress-icon">
                        <img src="{{ asset('productos/fondo/2tcocina.png') }}" alt="Checkout" class="progress-step-img">
                    </div>
                    <span class="progress-label">CHECKOUT</span>
                </div>
            </div>

            <!-- Mobile Version -->
            <div class="mobile-progress-appbar d-md-none d-flex justify-content-center align-items-center p-2">
                <!-- Step 1: Carrito (Activo) -->
                <div class="mobile-progress-step active">
                    <div class="progress-icon">
                        <img src="{{ asset('productos/fondo/1tcocina.png') }}" alt="Carrito" class="progress-step-img">
                    </div>
                    <span class="progress-label">CARRITO</span>
                </div>

                <!-- Progress Line -->
                <div class="progress-line"></div>

                <!-- Step 2: Checkout (Inactivo) -->
                <div class="mobile-progress-step">
                    <div class="progress-icon">
                        <img src="{{ asset('productos/fondo/2tcocina.png') }}" alt="Checkout" class="progress-step-img">
                    </div>
                    <span class="progress-label">CHECKOUT</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed top-20 right-4 z-50 space-y-2"></div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50" id="modalBackdrop"></div>

        <!-- Modal Panel -->
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-warning mb-4"></i>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">Confirmar Eliminación</h3>
                    <p class="text-text-secondary mb-6">¿Estás seguro de que quieres eliminar este producto del carrito?</p>

                    <div class="flex space-x-3">
                        <button id="cancelRemove"
                            class="flex-1 py-2 px-4 border border-border rounded-lg text-text-primary hover:bg-background transition-colors">
                            Cancelar
                        </button>
                        <button id="confirmRemove"
                            class="flex-1 py-2 px-4 bg-error text-white rounded-lg hover:bg-error-600 transition-colors">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Item Template -->
    <template id="cartItemTemplate">
        <div class="cart-item p-4" data-product-id>
            <div class="flex space-x-4">
                <!-- Product Image -->
                <div class="flex-shrink-0">
                    <img class="w-16 h-16 rounded-lg object-cover" src alt />
                </div>

                <!-- Product Details -->
                <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-text-primary mb-1 item-name"></h4>
                    <p class="text-text-secondary text-sm mb-2 item-options"></p>

                    <!-- Quantity Controls -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <button
                                class="quantity-decrease w-8 h-8 rounded-full bg-border-light text-text-primary hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="quantity-display font-medium text-text-primary min-w-[2rem] text-center">1</span>
                            <button
                                class="quantity-increase w-8 h-8 rounded-full bg-border-light text-text-primary hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span class="item-total font-semibold text-primary"></span>
                            <button class="edit-item text-text-secondary hover:text-primary transition-colors p-1 hidden">
                                <lord-icon
                                    src="{{ asset('lordicons/edit.json') }}"
                                    colors="primary:#0a2540,secondary:#0a2540"
                                    trigger="hover"
                                    style="width:18px;height:18px;">
                                </lord-icon>
                            </button>
                            <button class="remove-item text-text-secondary hover:text-error transition-colors p-1">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('burger_house/css/main.css') }}" />
    <style>
        /* Progress Indicator Styles - Similar to filter-appbar - Always fixed */
        .desktop-progress-appbar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0;
            right: 0;
            background: #ffffffed;
            border-top: 1px solid #e0e0e0;
            border-radius: 0;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            gap: 32px;
        }
        
        /* Asegurar que el footer quede visible sobre la barra fija */
        footer.footer-themed {
            margin-bottom: 100px !important;
            position: relative;
            z-index: 999;
        }
        
        @media (max-width: 767px) {
            footer.footer-themed {
                margin-bottom: 100px !important;
            }
        }

        .desktop-progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 24px;
            border-radius: 16px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 80px;
            min-width: 160px;
            background: transparent;
        }

        .desktop-progress-step.active {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgb(40, 68, 151);
            color: white !important;
        }

        .desktop-progress-step.active.completed {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgba(40, 167, 69, 0.7);
            color: white !important;
        }

        .desktop-progress-step:not(.active) {
            opacity: 0.5;
        }

        .desktop-progress-step .progress-icon {
            font-size: 32px;
            margin-bottom: 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .desktop-progress-step .progress-label {
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            line-height: 1.2;
        }

        .desktop-progress-step.active .progress-label {
            color: white !important;
        }

        .desktop-progress-step.active .progress-step-img {
            transform: scale(3.5) translateY(-30%);
        }

        .progress-step-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            transition: transform 0.3s ease, filter 0.3s ease;
        }

        .progress-line {
            width: 60px;
            height: 3px;
            background: #e0e0e0;
            border-radius: 2px;
            transition: all 0.3s ease;
            position: relative;
        }

        .progress-line.active {
            background: linear-gradient(90deg, var(--beach-primary) 0%, var(--beach-primary) 100%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .progress-line::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: var(--beach-primary);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
        }

        .progress-line::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: var(--beach-primary);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
        }

        /* Mobile Progress Styles */
        .mobile-progress-appbar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0;
            right: 0;
            background: #ffffffed;
            border-top: 1px solid #e0e0e0;
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            gap: 16px;
        }

        /* Add padding to main content on mobile to prevent content from being hidden behind fixed progress bar */
        /* Add padding to main content to prevent content from being hidden behind fixed progress bar */
        main {
            padding-bottom: 120px;
        }

        @media (max-width: 767px) {
            main {
                padding-bottom: 100px;
            }
        }

        .mobile-progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 60px;
            flex: 1;
            background: transparent;
        }

        .mobile-progress-step.active {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgb(40, 68, 151);
            color: white !important;
        }

        .mobile-progress-step.active.completed {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgba(40, 167, 69, 0.7);
            color: white !important;
        }

        .mobile-progress-step:not(.active) {
            opacity: 0.5;
        }

        .mobile-progress-step .progress-icon {
            font-size: 24px;
            margin-bottom: 12px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-progress-step .progress-label {
            font-size: 11px;
            font-weight: 500;
            text-align: center;
            line-height: 1.2;
        }

        .mobile-progress-step.active .progress-label {
            color: white !important;
        }

        .mobile-progress-step.active .progress-step-img {
            transform: scale(3.5) translateY(-30%);
        }

        .mobile-progress-appbar .progress-line {
            width: 40px;
            height: 2px;
        }

        /* Acompanamientos Section Styles */
        .acompanamientos-grid {
            display: flex;
            gap: 8px;
            padding: 4px 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            flex-wrap: nowrap;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .acompanamientos-grid::-webkit-scrollbar {
            display: none;
        }

        .acompanamiento-item {
            transition: all 0.2s ease;
            min-width: 120px;
            max-width: 120px;
            flex-shrink: 0;
            scroll-snap-align: start;
        }

        .acompanamiento-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .acompanamiento-item.selected {
            border-color: var(--primary, #284497);
            background-color: rgba(40, 68, 151, 0.05);
        }

        .acompanamiento-item img {
            height: 60px;
        }

        .acompanamiento-checkbox {
            z-index: 10;
        }

        .acompanamiento-add-btn {
            transition: all 0.2s ease;
        }

        .acompanamiento-add-btn:hover {
            transform: scale(1.05);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Cart Management
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let itemToRemove = null;


        // DOM Elements
        const cartItemsList = document.getElementById('cartItemsList');
        const emptyCartState = document.getElementById('emptyCartState');
        const orderSummaryContainer = document.getElementById('orderSummaryContainer');
        const itemCount = document.getElementById('itemCount');
        const subtotalAmount = document.getElementById('subtotalAmount');
        const shippingAmount = document.getElementById('shippingAmount');
        const shippingRow = document.getElementById('shippingRow');
        const totalAmount = document.getElementById('totalAmount');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const toastContainer = document.getElementById('toastContainer');
        const confirmModal = document.getElementById('confirmModal');
        const addSelectedAcompanamientosBtn = document.getElementById('addSelectedAcompanamientos');

        // Acompanamientos selection
        let selectedAcompanamientos = new Set();

        console.log('DOM Elements loaded:', {
            cartItemsList: !!cartItemsList,
            emptyCartState: !!emptyCartState,
            orderSummaryContainer: !!orderSummaryContainer,
            itemCount: !!itemCount,
            subtotalAmount: !!subtotalAmount,
            shippingAmount: !!shippingAmount,
            shippingRow: !!shippingRow,
            totalAmount: !!totalAmount,
            checkoutBtn: !!checkoutBtn,
            toastContainer: !!toastContainer,
            confirmModal: !!confirmModal
        });

        // Initialize App
        document.addEventListener('DOMContentLoaded', function() {
            renderCart();
            updateTotals();
            setupEventListeners();
            setupAcompanamientosListeners();
            // setupProgressBarPosition(); // Deshabilitado - appbars siempre fijos
            // Actualizar carrito lateral al cargar la página
            if (typeof updateCartOffcanvas === 'function') {
                updateCartOffcanvas();
            }
        });

        // Función deshabilitada - Los appbars ahora siempre están fijos en bottom: 0
        // function setupProgressBarPosition() {
        //     // Esta función ha sido deshabilitada para mantener los appbars siempre fijos en la parte inferior
        // }

        // Event Listeners
        function setupEventListeners() {


            // Checkout button
            if (checkoutBtn) {
                checkoutBtn.addEventListener('click', function() {
                    // Verificar si el día está deshabilitado
                    const isDayDisabled = this.hasAttribute('data-day-disabled');

                    if (isDayDisabled) {
                        showToast('Hoy no estamos tomando pedidos. Por favor, intenta otro día.', 'error');
                        return;
                    }

                    if (cart.length > 0) {
                        // Siempre ir a /turnos: el controlador decide si redirigir a checkout
                        // según el setting actual (evita que el cache de view fije la decisión).
                        window.location.href = '{{ route('turnos') }}';
                    }
                });
            }

            // Confirmation modal
            document.getElementById('cancelRemove').addEventListener('click', closeConfirmModal);
            document.getElementById('confirmRemove').addEventListener('click', confirmItemRemoval);
            document.getElementById('modalBackdrop').addEventListener('click', closeConfirmModal);
        }

        // Acompanamientos Section Event Listeners
        function setupAcompanamientosListeners() {
            // Checkbox change events
            const checkboxes = document.querySelectorAll('.acompanamiento-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const productId = this.dataset.productId;
                    const item = this.closest('.acompanamiento-item');

                    if (this.checked) {
                        selectedAcompanamientos.add(productId);
                        item.classList.add('selected');
                    } else {
                        selectedAcompanamientos.delete(productId);
                        item.classList.remove('selected');
                    }

                    updateAddSelectedButton();
                });
            });

            // Item click to toggle checkbox (when not clicking on button or checkbox directly)
            const items = document.querySelectorAll('.acompanamiento-item');
            items.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Don't toggle if clicking on checkbox or add button
                    if (e.target.classList.contains('acompanamiento-checkbox') ||
                        e.target.classList.contains('acompanamiento-add-btn')) {
                        return;
                    }

                    const checkbox = this.querySelector('.acompanamiento-checkbox');
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            // Individual "Agregar" button clicks
            const addButtons = document.querySelectorAll('.acompanamiento-add-btn');
            addButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    const productPrice = parseFloat(this.dataset.productPrice);
                    const productImage = this.dataset.productImage;
                    const productCategoryId = this.dataset.productCategoryId;

                    addAcompanamientoToCart(productId, productName, productPrice, productImage, productCategoryId);
                });
            });

            // "Add selected" button click
            if (addSelectedAcompanamientosBtn) {
                addSelectedAcompanamientosBtn.addEventListener('click', function() {
                    addSelectedAcompanamientosToCart();
                });
            }
        }

        // Update the "Add selected" button state
        function updateAddSelectedButton() {
            if (!addSelectedAcompanamientosBtn) return;

            const count = selectedAcompanamientos.size;
            addSelectedAcompanamientosBtn.disabled = count === 0;

            if (count >= 2) {
                addSelectedAcompanamientosBtn.classList.remove('hidden');
                addSelectedAcompanamientosBtn.innerHTML = `<i class="fas fa-plus mr-2"></i>Agregar seleccionados (${count})`;
            } else {
                addSelectedAcompanamientosBtn.classList.add('hidden');
            }
        }

        // Add a single acompanamiento to cart
        function addAcompanamientoToCart(productId, productName, productPrice, productImage, productCategoryId) {
            const cartItem = {
                productId: parseInt(productId),
                name: productName,
                price: productPrice,
                quantity: 1,
                image: productImage,
                categoryId: parseInt(productCategoryId),
                categoryName: getCategoryName(productCategoryId),
                configuration: null
            };

            cart.push(cartItem);
            saveCart();
            renderCart();
            updateTotals();

            if (typeof updateCartOffcanvas === 'function') {
                updateCartOffcanvas();
            }

            showToast(`${productName} agregado al carrito`, 'success');
        }

        // Add all selected acompanamientos to cart
        function addSelectedAcompanamientosToCart() {
            const checkboxes = document.querySelectorAll('.acompanamiento-checkbox:checked');

            checkboxes.forEach(checkbox => {
                const productId = checkbox.dataset.productId;
                const item = checkbox.closest('.acompanamiento-item');
                const button = item.querySelector('.acompanamiento-add-btn');

                const productName = button.dataset.productName;
                const productPrice = parseFloat(button.dataset.productPrice);
                const productImage = button.dataset.productImage;
                const productCategoryId = button.dataset.productCategoryId;

                addAcompanamientoToCart(productId, productName, productPrice, productImage, productCategoryId);

                // Uncheck and remove from selection
                checkbox.checked = false;
                selectedAcompanamientos.delete(productId);
                item.classList.remove('selected');
            });

            updateAddSelectedButton();
        }

        // Get category name from ID
        function getCategoryName(categoryId) {
            const categories = {
                2: 'Acompañamientos',
                3: 'Bebidas',
                4: 'Combos',
                5: 'Postres'
            };
            return categories[categoryId] || 'Acompañamientos';
        }


        // Render Cart
        function renderCart() {
            // Recargar carrito desde localStorage por si fue modificado desde el offcanvas
            cart = JSON.parse(localStorage.getItem('cart')) || [];
            cartItemsList.innerHTML = '';

            if (cart.length === 0) {
                emptyCartState.classList.remove('hidden');
                cartItemsList.classList.add('hidden');
                if (orderSummaryContainer) {
                    orderSummaryContainer.classList.add('hidden');
                }
                updateItemCount(0);
                updateCheckoutButton();
                return;
            }

            emptyCartState.classList.add('hidden');
            cartItemsList.classList.remove('hidden');
            if (orderSummaryContainer) {
                orderSummaryContainer.classList.remove('hidden');
            }

            cart.forEach((item, index) => {
                const cartItem = createCartItem(item, index);
                cartItemsList.appendChild(cartItem);
            });

            updateItemCount(cart.length);
            updateCheckoutButton();
        }

        // Create Cart Item
        function createCartItem(item, index) {
            console.log('Creating cart item:', item, 'index:', index);
            const template = document.getElementById('cartItemTemplate');
            const cartItem = template.content.cloneNode(true);

            const itemElement = cartItem.querySelector('.cart-item');
            itemElement.dataset.productId = item.productId || index;

            // Set item info
            const img = cartItem.querySelector('img');
            img.src = item.image ?
                '/images/' + item.image :
                'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
            img.alt = item.name;
            cartItem.querySelector('.item-name').textContent = item.name;
            cartItem.querySelector('.quantity-display').textContent = item.quantity;

            // Calculate total price using pre-calculated price from cart
            const totalPrice = Number(item.price || 0) * item.quantity;
            cartItem.querySelector('.item-total').textContent = formatPrice(totalPrice);

            // Set options text using new configuration structure
            let optionsText = '';
            if (item.configuration) {
                const configParts = [];

                // Medallones
                if (item.configuration.medallones) {
                    configParts.push(`Medallones: ${item.configuration.medallones}`);
                }

                // Tipo de medallón
                if (item.configuration.tipo_medallon) {
                    configParts.push(`Tipo: ${item.configuration.tipo_medallon}`);
                }

                // Aderezos
                if (item.configuration.aderezos && item.configuration.aderezos.length > 0) {
                    configParts.push(`Aderezos: ${item.configuration.aderezos.join(', ')}`);
                }

                // Extras
                if (item.configuration.extras && item.configuration.extras.length > 0) {
                    configParts.push(`Extras: ${item.configuration.extras.join(', ')}`);
                }

                // Dips
                if (item.configuration.dips && item.configuration.dips.length > 0) {
                    configParts.push(`Dips: ${item.configuration.dips.join(', ')}`);
                }

                // Dip Extra
                if (item.configuration.dip_extra && item.configuration.dip_extra.length > 0) {
                    configParts.push(`Dip Extra: ${item.configuration.dip_extra.join(', ')}`);
                }

                optionsText = configParts.join(' | ');
            } else {
                // Fallback for old structure
                if (item.variants && item.variants.length > 0) {
                    optionsText += item.variants.map(v => v.value).join(', ');
                }
                if (item.options && item.options.length > 0) {
                    if (optionsText) optionsText += ', ';
                    optionsText += item.options.map(o => o.value).join(', ');
                }
            }
            cartItem.querySelector('.item-options').textContent = optionsText || 'Sin opciones adicionales';

            // Show edit button only for customizable products (hamburguesas and acompañamientos)
            const editBtn = cartItem.querySelector('.edit-item');
            const categoryName = (item.categoryName || item.category || '').toLowerCase();
            // More flexible matching for category names
            const isHamburguesa = categoryName.includes('hamburguesa') || categoryName.includes('hamburguesas');
            const isAcompanamiento = categoryName.includes('acompanamiento') || categoryName.includes('acompanamientos');
            const isCustomizable = isHamburguesa || isAcompanamiento;

            console.log('Buttons found:', {
                editBtn: !!editBtn,
                isCustomizable: isCustomizable
            });

            if (isCustomizable) {
                editBtn.classList.remove('hidden');
                editBtn.addEventListener('click', function() {
                    console.log('Edit button clicked for item:', item);
                    // Save the full item data for editing
                    localStorage.setItem('tcocina_edit_cart_item', JSON.stringify(item));
                    localStorage.setItem('tcocina_edit_cart_item_index', index);
                    // Redirect to catalog to edit (item stays in cart)
                    window.location.href = '{{ route('catalog') }}';
                });
            }

            // Setup quantity controls
            const decreaseBtn = cartItem.querySelector('.quantity-decrease');
            const increaseBtn = cartItem.querySelector('.quantity-increase');
            const removeBtn = cartItem.querySelector('.remove-item');

            console.log('Quantity buttons found:', {
                decreaseBtn: !!decreaseBtn,
                increaseBtn: !!increaseBtn,
                removeBtn: !!removeBtn
            });

            if (decreaseBtn) {
                decreaseBtn.addEventListener('click', function() {
                    console.log('Decrease button clicked for index:', index);
                    // If quantity is 1, show confirmation before removing
                    if (item.quantity === 1) {
                        showRemoveConfirmation(index);
                    } else {
                        updateItemQuantity(index, -1);
                    }
                });
            }

            if (increaseBtn) {
                increaseBtn.addEventListener('click', function() {
                    console.log('Increase button clicked for index:', index);
                    updateItemQuantity(index, 1);
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    console.log('Remove button clicked for index:', index);
                    showRemoveConfirmation(index);
                });
            }

            return cartItem;
        }

        // Update Item Quantity
        function updateItemQuantity(index, change) {
            if (index >= 0 && index < cart.length) {
                cart[index].quantity += change;

                if (cart[index].quantity <= 0) {
                    cart.splice(index, 1);
                    showToast('Producto eliminado del carrito');
                } else {
                    showToast('Cantidad actualizada');
                }

                saveCart();
                renderCart();
                updateTotals();
                // Actualizar carrito lateral
                if (typeof updateCartOffcanvas === 'function') {
                    updateCartOffcanvas();
                }
            }
        }

        // Show Remove Confirmation
        function showRemoveConfirmation(index) {
            itemToRemove = index;
            confirmModal.classList.remove('hidden');
        }

        // Close Confirmation Modal
        function closeConfirmModal() {
            confirmModal.classList.add('hidden');
            itemToRemove = null;
        }

        // Confirm Item Removal
        function confirmItemRemoval() {
            if (itemToRemove !== null && itemToRemove >= 0 && itemToRemove < cart.length) {
                cart.splice(itemToRemove, 1);
                saveCart();
                renderCart();
                updateTotals();
                showToast('Producto eliminado del carrito');
                closeConfirmModal();
                // Actualizar carrito lateral
                if (typeof updateCartOffcanvas === 'function') {
                    updateCartOffcanvas();
                }
            }
        }



        // Update Totals
        function updateTotals() {
            // Recargar carrito desde localStorage por si fue modificado desde el offcanvas
            cart = JSON.parse(localStorage.getItem('cart')) || [];
            const subtotal = cart.reduce((sum, item) => {
                // Use pre-calculated price from cart (already includes all modifiers)
                const unitPrice = Number(item.price || 0);
                return sum + (unitPrice * item.quantity);
            }, 0);

            // Calculate shipping (will be calculated in checkout based on delivery method)
            let shipping = 0;


            const total = subtotal;

            // Update display
            subtotalAmount.textContent = formatPrice(subtotal);
            shippingAmount.textContent = formatPrice(shipping);
            totalAmount.textContent = formatPrice(total);

            // Ocultar fila de envío ya que no hay costo
            shippingRow.classList.add('hidden');

        }

        // Update Item Count
        function updateItemCount(count) {
            itemCount.textContent = `${count} producto${count !== 1 ? 's' : ''}`;
        }

        // Update Checkout Button
        function updateCheckoutButton() {
            if (checkoutBtn) {
                // Verificar si el día está deshabilitado
                const isDayDisabled = checkoutBtn.hasAttribute('data-day-disabled');

                if (isDayDisabled) {
                    // Si el día está deshabilitado, mantener el botón deshabilitado
                    checkoutBtn.disabled = true;
                    checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else if (cart.length > 0) {
                    checkoutBtn.disabled = false;
                    checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    checkoutBtn.disabled = true;
                    checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Save Cart to localStorage
        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        // Format Price
        function formatPrice(price) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0
            }).format(price);
        }

        // Persist order notes
        const notesEl = document.getElementById('orderNotes');
        if (notesEl) {
            // Prefill from storage
            const savedNotes = localStorage.getItem('orderNotes');
            if (savedNotes) notesEl.value = savedNotes;
            notesEl.addEventListener('input', () => {
                localStorage.setItem('orderNotes', notesEl.value.trim());
            });
        }

        // Show Toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `bg-${type === 'success' ? 'success' : 'error'} text-white px-4 py-3 rounded-lg shadow-lg`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i>
                    <span>${message}</span>
        </div>
    `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 4000);
        }
    </script>
@endpush
