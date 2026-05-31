@extends('layouts.app')

@section('title', 'Catálogo de Productos - TecoCina')

@section('content')
    <!-- Main Content -->
    <main class="container py-4">
        <!-- Page Header -->
        <div class="mb-4 text-center">
            <h1 class="display-6 fw-bold text-beach-dark mb-2 ultramono-title">Menú</h1>
        </div>

        <!-- Category Filter Pills -->
        <div class="mb-4 d-none">
            <div class="d-flex gap-2 overflow-auto pb-2 beach-scrollbar-hide">
                <button class="category-filter btn btn-beach-primary text-nowrap active" data-category="all">
                    Todos
                </button>
                @foreach ($categories as $category)
                    <button class="category-filter btn btn-outline-beach-primary text-nowrap"
                        data-category="{{ $category->slug }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Products Grid -->
        <div id="productsGrid" class="row g-4">
            @foreach ($products as $product)
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card h-100 beach-card product-card" data-category="{{ $product->category }}"
                        data-product-id="{{ $product->id }}">
                        <div class="position-relative overflow-hidden">
                            @if ($product->image)
                                @php
                                    $imgUrl = \Illuminate\Support\Str::startsWith($product->image, 'products/')
                                        ? \Illuminate\Support\Facades\Storage::url($product->image)
                                        : asset('images/products/' . $product->image);
                                @endphp
                                <img class="card-img-top" style="height: 200px; object-fit: cover;"
                                    src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy"
                                    onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                            @else
                                <img class="card-img-top" style="height: 200px; object-fit: cover;"
                                    src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    alt="{{ $product->name }}" loading="lazy" />
                            @endif

                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-beach-dark product-name">{{ $product->name }}</h5>
                            <p class="card-text text-beach-brown small mb-3 product-description">{{ $product->description }}
                            </p>

                            <!-- Product Options -->
                            <div class="product-options mb-3">
                                @if ($product->variants->count() > 0)
                                    @foreach ($product->variants->groupBy('name') as $variantName => $variants)
                                        <div class="mb-2">
                                            <label
                                                class="form-label small text-beach-dark fw-medium">{{ $variantName }}:</label>
                                            <select class="form-select form-select-sm variant-select"
                                                data-variant-name="{{ $variantName }}">
                                                @foreach ($variants as $variant)
                                                    <option value="{{ $variant->value }}"
                                                        data-price-modifier="{{ $variant->price_modifier }}"
                                                        {{ ($variantName === 'Tamaño' || $variantName === 'Medallones') && $variant->value === 'Doble' ? 'selected' : '' }}>
                                                        {{ $variant->value }}
                                                        @if ($variant->price_modifier != 0)
                                                            ({{ $variant->price_modifier > 0 ? '+' : '' }}${{ number_format($variant->price_modifier, 2) }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                @endif

                                @if ($product->options->count() > 0)
                                    @foreach ($product->options->groupBy('name') as $optionName => $options)
                                        <div class="mb-2">
                                            <label
                                                class="form-label small text-beach-dark fw-medium">{{ $optionName }}:</label>
                                            <select class="form-select form-select-sm option-select"
                                                data-option-name="{{ $optionName }}">
                                                <option value="">Seleccionar</option>
                                                @foreach ($options as $option)
                                                    <option value="{{ $option->value }}"
                                                        data-price-modifier="{{ $option->price_modifier }}">
                                                        {{ $option->value }}
                                                        @if ($option->price_modifier != 0)
                                                            ({{ $option->price_modifier > 0 ? '+' : '' }}${{ number_format($option->price_modifier, 2) }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="mt-auto d-flex align-items-center justify-content-between">
                                <div class="d-flex flex-column">
                                    <span
                                        class="h5 fw-bold text-beach-primary product-price">${{ number_format($product->base_price, 2) }}</span>
                                    <span
                                        class="text-decoration-line-through small text-beach-brown original-price d-none"></span>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="d-flex align-items-center gap-3">
                                    <div class="quantity-controls d-none d-flex align-items-center gap-2">
                                        <button
                                            class="quantity-decrease btn btn-sm btn-outline-beach-primary rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;">
                                            <i class="fas fa-minus small"></i>
                                        </button>
                                        <span class="quantity-display fw-medium text-beach-dark">0</span>
                                        <button
                                            class="quantity-increase btn btn-sm btn-outline-beach-primary rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;">
                                            <i class="fas fa-plus small"></i>
                                        </button>
                                    </div>

                                    <button class="add-to-cart-btn btn btn-beach-primary btn-sm"
                                        data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}"
                                        data-product-image="{{ $product->image }}">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="d-none text-center py-5">
            <div class="spinner-border text-beach-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-beach-brown">Cargando productos...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="d-none text-center py-5">
            <i class="fas fa-search display-1 text-beach-brown mb-4"></i>
            <h3 class="h5 fw-medium text-beach-dark mb-2">No se encontraron productos</h3>
            <p class="text-beach-brown">Intenta con otros términos de búsqueda o categorías</p>
        </div>
    </main>


    <!-- Sticky Mobile Cart CTA -->
    <div id="stickyCartCTA" class="fixed-bottom d-md-none bg-white border-top p-3 d-none">
        <button class="btn btn-beach-primary w-100 d-flex align-items-center justify-content-between">
            <span>Ver Carrito</span>
            <div class="d-flex align-items-center gap-2">
                <span id="stickyCartCount" class="badge bg-beach-secondary">0</span>
                <span id="stickyCartTotal">$0</span>
            </div>
        </button>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>
@endsection

@push('styles')
    <style>
        .beach-card {
            border: 1px solid var(--beach-border-light);
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .beach-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: var(--beach-primary);
        }

        .beach-scrollbar-hide {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .beach-scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .category-filter.active {
            background-color: var(--beach-primary) !important;
            border-color: var(--beach-primary) !important;
            color: var(--beach-dark) !important;
        }

        .product-card[data-category] {
            transition: opacity 0.3s ease;
        }

        .product-card.hidden {
            opacity: 0;
            pointer-events: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Esperar a que app.js esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño delay para asegurar que app.js esté completamente cargado
            setTimeout(function() {
                initializeCatalog();
            }, 100);
        });

        function initializeCatalog() {
            // Initialize category filters
            initializeCategoryFilters();

            // Initialize product interactions
            initializeProductInteractions();

            // Initialize cart functionality
            initializeCartOffcanvas();

            // Initialize search
            initializeSearch();

            // Actualizar el carrito lateral al cargar la página
            if (typeof updateCartOffcanvas === 'function') {
                updateCartOffcanvas();
            }
        }

        function initializeCategoryFilters() {
            const categoryFilters = document.querySelectorAll('.category-filter');
            const productCards = document.querySelectorAll('.product-card');

            categoryFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    // Update active state
                    categoryFilters.forEach(f => f.classList.remove('active'));
                    categoryFilters.forEach(f => f.classList.add('btn-outline-beach-primary'));
                    categoryFilters.forEach(f => f.classList.remove('btn-beach-primary'));

                    this.classList.add('active');
                    this.classList.remove('btn-outline-beach-primary');
                    this.classList.add('btn-beach-primary');

                    // Filter products
                    const selectedCategory = this.dataset.category;

                    productCards.forEach(card => {
                        if (selectedCategory === 'all' || card.dataset.category ===
                            selectedCategory) {
                            card.classList.remove('hidden');
                        } else {
                            card.classList.add('hidden');
                        }
                    });
                });
            });
        }

        function initializeProductInteractions() {
            // Add to cart buttons (evitar doble binding)
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                if (button.dataset.cartInit === 'true') return;
                button.dataset.cartInit = 'true';
                button.addEventListener('click', function(event) {
                    if (event && typeof event.stopImmediatePropagation === 'function') {
                        event.stopImmediatePropagation();
                    }
                    event.stopPropagation();
                    const productCard = this.closest('.product-card');
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    const productPrice = parseFloat(this.dataset.productPrice);
                    const productImage = this.dataset.productImage;

                    // Get selected variants and options
                    const selectedVariants = getSelectedVariants(productCard);
                    const selectedOptions = getSelectedOptions(productCard);

                    addToCartWithVariants(productId, productName, productPrice, productImage,
                        selectedVariants,
                        selectedOptions);

                    // Show quantity controls
                    showQuantityControls(this);
                });
            });

            // Quantity controls (evitar doble binding)
            document.querySelectorAll('.quantity-increase').forEach(button => {
                if (button.dataset.qtyIncInit === 'true') return;
                button.dataset.qtyIncInit = 'true';
                button.addEventListener('click', function(event) {
                    event.stopPropagation();
                    const productCard = this.closest('.product-card');
                    const quantityDisplay = productCard.querySelector('.quantity-display');
                    const currentQuantity = parseInt(quantityDisplay.textContent);
                    quantityDisplay.textContent = currentQuantity + 1;

                    updateCartItemQuantity(productCard);
                });
            });

            document.querySelectorAll('.quantity-decrease').forEach(button => {
                if (button.dataset.qtyDecInit === 'true') return;
                button.dataset.qtyDecInit = 'true';
                button.addEventListener('click', function(event) {
                    event.stopPropagation();
                    const productCard = this.closest('.product-card');
                    const quantityDisplay = productCard.querySelector('.quantity-display');
                    const currentQuantity = parseInt(quantityDisplay.textContent);

                    if (currentQuantity > 1) {
                        quantityDisplay.textContent = currentQuantity - 1;
                        updateCartItemQuantity(productCard);
                    } else {
                        removeFromCart(productCard);
                        hideQuantityControls(productCard.querySelector('.add-to-cart-btn'));
                    }
                });
            });

            // Variant/option changes (evitar doble binding)
            document.querySelectorAll('.variant-select, .option-select').forEach(select => {
                if (select.dataset.priceInit === 'true') return;
                select.dataset.priceInit = 'true';
                select.addEventListener('change', function() {
                    updateProductPrice(this.closest('.product-card'));
                });
            });
        }

        function getSelectedVariants(productCard) {
            const variants = [];
            productCard.querySelectorAll('.variant-select').forEach(select => {
                const selectedOption = select.selectedOptions[0];
                if (selectedOption) {
                    variants.push({
                        name: select.dataset.variantName,
                        value: selectedOption.value,
                        priceModifier: parseFloat(selectedOption.dataset.priceModifier || 0)
                    });
                }
            });
            return variants;
        }

        function getSelectedOptions(productCard) {
            const options = [];
            productCard.querySelectorAll('.option-select').forEach(select => {
                if (select.value) {
                    const selectedOption = select.selectedOptions[0];
                    options.push({
                        name: select.dataset.optionName,
                        value: selectedOption.value,
                        priceModifier: parseFloat(selectedOption.dataset.priceModifier || 0)
                    });
                }
            });
            return options;
        }

        function updateProductPrice(productCard) {
            const basePrice = parseFloat(productCard.querySelector('.add-to-cart-btn').dataset.productPrice);
            const selectedVariants = getSelectedVariants(productCard);
            const selectedOptions = getSelectedOptions(productCard);

            let totalPrice = basePrice;
            selectedVariants.forEach(variant => totalPrice += variant.priceModifier);
            selectedOptions.forEach(option => totalPrice += option.priceModifier);

            const priceElement = productCard.querySelector('.product-price');
            if (priceElement) {
                priceElement.textContent = '$' + totalPrice.toFixed(2);
            }
        }

        // Usar la función global addToCart, pero con soporte para variants y options
        function addToCartWithVariants(productId, productName, productPrice, productImage, variants = [], options = []) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Create unique key for this product configuration
            const configKey = `${productId}_${JSON.stringify(variants)}_${JSON.stringify(options)}`;

            // Check if this exact configuration already exists
            const existingItem = cart.find(item => item.configKey === configKey);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    productId: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    variants: variants,
                    options: options,
                    quantity: 1,
                    configKey: configKey
                });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            updateCartOffcanvas();
            showNotification('Producto agregado al carrito', 'success');
        }

        function showQuantityControls(addButton) {
            const productCard = addButton.closest('.product-card');
            const quantityControls = productCard.querySelector('.quantity-controls');
            const quantityDisplay = productCard.querySelector('.quantity-display');

            addButton.style.display = 'none';
            quantityControls.classList.remove('d-none');
            quantityDisplay.textContent = '1';
        }

        function hideQuantityControls(addButton) {
            const productCard = addButton.closest('.product-card');
            const quantityControls = productCard.querySelector('.quantity-controls');

            addButton.style.display = 'block';
            quantityControls.classList.add('d-none');
        }

        function updateCartItemQuantity(productCard) {
            const productId = productCard.dataset.productId;
            const quantity = parseInt(productCard.querySelector('.quantity-display').textContent);
            const selectedVariants = getSelectedVariants(productCard);
            const selectedOptions = getSelectedOptions(productCard);
            const configKey = `${productId}_${JSON.stringify(selectedVariants)}_${JSON.stringify(selectedOptions)}`;

            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const item = cart.find(item => item.configKey === configKey);

            if (item) {
                item.quantity = quantity;
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCartBadge();
                updateCartOffcanvas();
            }
        }

        function removeFromCart(productCard) {
            const productId = productCard.dataset.productId;
            const selectedVariants = getSelectedVariants(productCard);
            const selectedOptions = getSelectedOptions(productCard);
            const configKey = `${productId}_${JSON.stringify(selectedVariants)}_${JSON.stringify(selectedOptions)}`;

            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart = cart.filter(item => item.configKey !== configKey);

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            updateCartOffcanvas();
            showNotification('Producto eliminado del carrito', 'info');
        }

        function initializeCartOffcanvas() {
            // Ya no necesitamos duplicar esta función, usar la global de app.js
            // Solo configurar el botón específico de esta página
            const proceedToCart = document.getElementById('proceedToCart');
            if (proceedToCart) {
                proceedToCart.addEventListener('click', function() {
                    window.location.href = '/cart';
                });
            }
        }

        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    filterProducts(query);
                });
            }
        }

        function filterProducts(query) {
            const productCards = document.querySelectorAll('.product-card');

            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productDescription = card.querySelector('.product-description').textContent.toLowerCase();

                if (productName.includes(query) || productDescription.includes(query)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }


        // Format Price function
        function formatPrice(price) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0
            }).format(price);
        }
    </script>
@endpush
