@extends('layouts.app')

@section('title', 'Carrito de Compras - TecoCina')

@section('content')
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-text-primary mb-2">Tu Carrito</h1>
                    <p class="text-text-secondary">Revisa tus productos y procede al pago</p>
                </div>
                <a href="{{ route('catalog') }}"
                    class="hidden md:flex items-center space-x-2 text-primary hover:text-primary-600 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Seguir Comprando</span>
                </a>
            </div>
        </div>

        <!-- Cart Content -->
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <!-- Cart Items Section -->
            <div class="lg:col-span-2">

                <!-- Cart Items -->
                <div class="card">
                    <div class="p-4 border-b border-border-light">
                        <h2 class="text-lg font-semibold text-text-primary">Productos en tu Carrito</h2>
                        <span id="itemCount" class="text-text-secondary text-sm">0 productos</span>
                    </div>

                    <div id="cartItemsList" class="divide-y divide-border-light">
                        <!-- Cart items will be dynamically inserted here -->
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

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1 mt-6 lg:mt-0">
                <div class="card sticky top-24">
                    <div class="p-4 border-b border-border-light">
                        <h3 class="text-lg font-semibold text-text-primary">Resumen del Pedido</h3>
                    </div>

                    <div class="p-4 space-y-4">
                        <!-- Coupon Code -->
                        <div>
                            <label for="couponCode" class="block text-sm font-medium text-text-primary mb-2">
                                Código de Descuento
                            </label>
                            <div class="flex space-x-2">
                                <input type="text" id="couponCode" placeholder="Ej: BURGER10"
                                    class="input-field flex-1 text-sm" />
                                <button id="applyCoupon"
                                    class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-secondary-600 transition-colors">
                                    Aplicar
                                </button>
                            </div>
                            <div id="couponMessage" class="mt-2 text-sm hidden"></div>
                        </div>

                        <!-- Order Totals -->
                        <div class="space-y-3 pt-4 border-t border-border-light">
                            <div class="flex justify-between text-text-primary">
                                <span>Subtotal</span>
                                <span id="subtotalAmount">$0</span>
                            </div>

                            <div id="shippingRow" class="justify-between text-text-primary hidden">
                                <span>Envío</span>
                                <span id="shippingAmount">$0</span>
                            </div>


                            <div id="discountRow" class="justify-between text-success hidden">
                                <span>Descuento <span id="discountCode"></span></span>
                                <span id="discountAmount">-$0</span>
                            </div>

                            <div
                                class="flex justify-between text-lg font-bold text-text-primary pt-3 border-t border-border-light">
                                <span>Total</span>
                                <span id="totalAmount">$0</span>
                            </div>
                        </div>


                        <!-- Checkout Button -->
                        <div class="pt-4">
                            <button id="checkoutBtn"
                                class="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <i class="fas fa-credit-card mr-2"></i>
                                Proceder al Pago
                            </button>
                        </div>

                    </div>
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
@endpush

@push('scripts')
    <script>
        // Cart Management
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let appliedCoupon = null;
        let itemToRemove = null;

        // Coupon codes
        const coupons = {
            'BURGER10': {
                discount: 0.10,
                description: '10% de descuento'
            }
        };

        // DOM Elements
        const cartItemsList = document.getElementById('cartItemsList');
        const emptyCartState = document.getElementById('emptyCartState');
        const itemCount = document.getElementById('itemCount');
        const subtotalAmount = document.getElementById('subtotalAmount');
        const shippingAmount = document.getElementById('shippingAmount');
        const shippingRow = document.getElementById('shippingRow');
        const discountAmount = document.getElementById('discountAmount');
        const discountRow = document.getElementById('discountRow');
        const discountCode = document.getElementById('discountCode');
        const totalAmount = document.getElementById('totalAmount');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const toastContainer = document.getElementById('toastContainer');
        const confirmModal = document.getElementById('confirmModal');

        // Initialize App
        document.addEventListener('DOMContentLoaded', function() {
            renderCart();
            updateTotals();
            setupEventListeners();
        });

        // Event Listeners
        function setupEventListeners() {
            // Coupon application
            document.getElementById('applyCoupon').addEventListener('click', applyCouponCode);
            document.getElementById('couponCode').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyCouponCode();
                }
            });


            // Checkout button
            if (checkoutBtn) {
                checkoutBtn.addEventListener('click', function() {
                    if (cart.length > 0) {
                        window.location.href = '{{ route('checkout') }}';
                    }
                });
            }

            // Confirmation modal
            document.getElementById('cancelRemove').addEventListener('click', closeConfirmModal);
            document.getElementById('confirmRemove').addEventListener('click', confirmItemRemoval);
            document.getElementById('modalBackdrop').addEventListener('click', closeConfirmModal);
        }


        // Render Cart
        function renderCart() {
            cartItemsList.innerHTML = '';

            if (cart.length === 0) {
                emptyCartState.classList.remove('hidden');
                cartItemsList.classList.add('hidden');
                updateItemCount(0);
                updateCheckoutButton();
                return;
            }

            emptyCartState.classList.add('hidden');
            cartItemsList.classList.remove('hidden');

            cart.forEach((item, index) => {
                const cartItem = createCartItem(item, index);
                cartItemsList.appendChild(cartItem);
            });

            updateItemCount(cart.length);
            updateCheckoutButton();
        }

        // Create Cart Item
        function createCartItem(item, index) {
            const template = document.getElementById('cartItemTemplate');
            const cartItem = template.content.cloneNode(true);

            const itemElement = cartItem.querySelector('.cart-item');
            itemElement.dataset.productId = item.productId || index;

            // Set item info
            const img = cartItem.querySelector('img');
            img.src = item.image ? '/images/products/' + item.image :
                'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
            img.alt = item.name;
            cartItem.querySelector('.item-name').textContent = item.name;
            cartItem.querySelector('.quantity-display').textContent = item.quantity;

            // Calculate total price incluyendo variantes y opciones
            const basePrice = Number(item.unitPrice || item.price || 0);
            const variantsTotal = Array.isArray(item.variants) ?
                item.variants.reduce((sum, v) => sum + Number(v.priceModifier || 0), 0) :
                0;
            const optionsTotal = Array.isArray(item.options) ?
                item.options.reduce((sum, o) => sum + Number(o.priceModifier || 0), 0) :
                0;
            const unitPrice = basePrice + variantsTotal + optionsTotal;
            const totalPrice = unitPrice * item.quantity;
            cartItem.querySelector('.item-total').textContent = formatPrice(totalPrice);

            // Set options text
            let optionsText = '';
            if (item.variants && item.variants.length > 0) {
                optionsText += item.variants.map(v => v.value).join(', ');
            }
            if (item.options && item.options.length > 0) {
                if (optionsText) optionsText += ', ';
                optionsText += item.options.map(o => o.value).join(', ');
            }
            cartItem.querySelector('.item-options').textContent = optionsText || 'Sin opciones adicionales';

            // Setup quantity controls
            const decreaseBtn = cartItem.querySelector('.quantity-decrease');
            const increaseBtn = cartItem.querySelector('.quantity-increase');
            const removeBtn = cartItem.querySelector('.remove-item');

            decreaseBtn.addEventListener('click', function() {
                updateItemQuantity(index, -1);
            });

            increaseBtn.addEventListener('click', function() {
                updateItemQuantity(index, 1);
            });

            removeBtn.addEventListener('click', function() {
                showRemoveConfirmation(index);
            });

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
            }
        }

        // Apply Coupon Code
        function applyCouponCode() {
            const couponInput = document.getElementById('couponCode');
            const couponMessage = document.getElementById('couponMessage');
            const code = couponInput.value.trim().toUpperCase();

            if (!code) {
                showCouponMessage('Ingresa un código de descuento', 'error');
                return;
            }

            if (coupons[code]) {
                appliedCoupon = {
                    code,
                    ...coupons[code]
                };
                showCouponMessage(`¡Código aplicado! ${coupons[code].description}`, 'success');
                couponInput.value = '';
                updateTotals();
            } else {
                showCouponMessage('Código de descuento inválido', 'error');
            }
        }

        // Show Coupon Message
        function showCouponMessage(message, type) {
            const couponMessage = document.getElementById('couponMessage');
            couponMessage.textContent = message;
            couponMessage.className = `mt-2 text-sm ${type === 'success' ? 'text-success' : 'text-error'}`;
            couponMessage.classList.remove('hidden');

            setTimeout(() => {
                couponMessage.classList.add('hidden');
            }, 4000);
        }

        // Update Service Fee Display
        function updateServiceFeeDisplay() {
            if (includeServiceFee) {
                serviceFeeRow.classList.remove('hidden');
            } else {
                serviceFeeRow.classList.add('hidden');
            }
        }

        // Update Totals
        function updateTotals() {
            const subtotal = cart.reduce((sum, item) => {
                const basePrice = Number(item.unitPrice || item.price || 0);
                const variantsTotal = Array.isArray(item.variants) ?
                    item.variants.reduce((acc, v) => acc + Number(v.priceModifier || 0), 0) :
                    0;
                const optionsTotal = Array.isArray(item.options) ?
                    item.options.reduce((acc, o) => acc + Number(o.priceModifier || 0), 0) :
                    0;
                const unitPrice = basePrice + variantsTotal + optionsTotal;
                return sum + (unitPrice * item.quantity);
            }, 0);

            // Calculate shipping
            let shipping = 0;
            // No hay costo de shipping, se acuerda personalmente
            shipping = 0;

            // No hay cargo por servicio
            let serviceFee = 0;

            // Calculate discount
            let discount = 0;
            if (appliedCoupon) {
                discount = Math.round(subtotal * appliedCoupon.discount);
            }

            const total = subtotal - discount;

            // Update display
            subtotalAmount.textContent = formatPrice(subtotal);
            shippingAmount.textContent = formatPrice(shipping);
            discountAmount.textContent = formatPrice(discount);
            totalAmount.textContent = formatPrice(total);

            // Ocultar fila de envío ya que no hay costo
            shippingRow.classList.add('hidden');

            // Update discount row visibility
            if (appliedCoupon) {
                discountRow.classList.remove('hidden');
                discountCode.textContent = `(${appliedCoupon.code})`;
            } else {
                discountRow.classList.add('hidden');
            }

        }

        // Update Item Count
        function updateItemCount(count) {
            itemCount.textContent = `${count} producto${count !== 1 ? 's' : ''}`;
        }

        // Update Checkout Button
        function updateCheckoutButton() {
            if (checkoutBtn) {
                if (cart.length > 0) {
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
