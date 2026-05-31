@extends('layouts.app')

@section('title', 'Finalizar Pedido - TecoCina')

@section('content')
    <!-- Progress Indicator -->
    <div class="bg-surface border-b border-border-light">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-center space-x-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-success rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                    <span class="ml-2 text-sm font-medium text-success">Carrito</span>
                </div>
                <div class="w-8 h-0.5 bg-success"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">2</span>
                    </div>
                    <span class="ml-2 text-sm font-medium text-primary">Checkout</span>
                </div>
                <div class="w-8 h-0.5 bg-border"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-border rounded-full flex items-center justify-center">
                        <span class="text-text-secondary text-sm font-bold">3</span>
                    </div>
                    <span class="ml-2 text-sm font-medium text-text-secondary">Confirmación</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <form id="checkoutForm" class="space-y-8">
                    <!-- Delivery/Pickup Toggle -->
                    <section class="card p-6">
                        <h2 class="text-lg font-semibold text-text-primary mb-4">Método de Entrega</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" id="deliveryBtn"
                                class="delivery-option p-4 border-2 rounded-lg text-center transition-colors border-border bg-surface">
                                <i class="fas fa-truck text-xl mb-2 text-text-secondary"></i>
                                <div class="font-medium text-text-primary">Delivery</div>
                                <div class="text-sm text-text-secondary">30-45 min</div>
                            </button>
                            <button type="button" id="pickupBtn"
                                class="delivery-option p-4 border-2 rounded-lg text-center transition-colors hover:border-primary hover:bg-primary-50 text-text-secondary active border-primary bg-primary-50">
                                <i class="fas fa-store text-xl mb-2 text-primary"></i>
                                <div class="font-medium text-primary">Retiro</div>
                                <div class="text-sm text-text-secondary">15-20 min</div>
                            </button>
                        </div>
                    </section>

                    <!-- Customer Information -->
                    <section class="card p-6">
                        <h2 class="text-lg font-semibold text-text-primary mb-4">Información de Contacto</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-text-primary mb-1">Nombre
                                    *</label>
                                <input type="text" id="firstName" name="firstName" required class="input-field"
                                    placeholder="Tu nombre" />
                                <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu nombre
                                </div>
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-text-primary mb-1">Apellido
                                    *</label>
                                <input type="text" id="lastName" name="lastName" required class="input-field"
                                    placeholder="Tu apellido" />
                                <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu apellido
                                </div>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-text-primary mb-1">Teléfono
                                    *</label>
                                <input type="tel" id="phone" name="phone" required class="input-field"
                                    placeholder="+54 11 1234-5678" />
                                <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu teléfono
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Delivery Address (shown only for delivery) -->
                    <section id="deliverySection" class="card p-6">
                        <h2 class="text-lg font-semibold text-text-primary mb-4">Dirección de Entrega</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="address" class="block text-sm font-medium text-text-primary mb-1">Dirección
                                    *</label>
                                <input type="text" id="address" name="address" class="input-field"
                                    placeholder="Av. Corrientes 1234" />
                                <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu dirección
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-text-primary mb-1">Ciudad
                                        *</label>
                                    <input type="text" id="city" name="city" class="input-field"
                                        placeholder="Buenos Aires" />
                                    <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu ciudad
                                    </div>
                                </div>
                                <div>
                                    <label for="postalCode" class="block text-sm font-medium text-text-primary mb-1">Código
                                        Postal *</label>
                                    <input type="text" id="postalCode" name="postalCode" class="input-field"
                                        placeholder="1000" />
                                    <div class="invalid-feedback text-error text-sm mt-1 hidden">Por favor ingresa tu
                                        código postal</div>
                                </div>
                                <div>
                                    <label for="floor"
                                        class="block text-sm font-medium text-text-primary mb-1">Piso/Depto</label>
                                    <input type="text" id="floor" name="floor" class="input-field"
                                        placeholder="5° A" />
                                </div>
                            </div>
                            <div>
                                <label for="deliveryNotes" class="block text-sm font-medium text-text-primary mb-1">Notas
                                    de Entrega</label>
                                <textarea id="deliveryNotes" name="deliveryNotes" rows="3" class="input-field"
                                    placeholder="Instrucciones adicionales para el delivery..."></textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Delivery Scheduling -->
                    <section class="card p-6">
                        <h2 class="text-lg font-semibold text-text-primary mb-4">Horario de Entrega</h2>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="deliveryTime" value="now" checked
                                        class="w-4 h-4 text-primary border-border focus:ring-primary" />
                                    <span class="ml-2 text-text-primary">Lo antes posible</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="deliveryTime" value="scheduled"
                                        class="w-4 h-4 text-primary border-border focus:ring-primary" />
                                    <span class="ml-2 text-text-primary">Programar entrega</span>
                                </label>
                            </div>
                            <div id="scheduledDelivery" class="hidden grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="deliveryDate"
                                        class="block text-sm font-medium text-text-primary mb-1">Fecha</label>
                                    <input type="date" id="deliveryDate" name="deliveryDate" class="input-field"
                                        min="2025-01-11" />
                                </div>
                                <div>
                                    <label for="deliveryTimeSlot"
                                        class="block text-sm font-medium text-text-primary mb-1">Horario</label>
                                    <select id="deliveryTimeSlot" name="deliveryTimeSlot" class="input-field">
                                        <option value>Seleccionar horario</option>
                                        <option value="12:00-13:00">12:00 - 13:00</option>
                                        <option value="13:00-14:00">13:00 - 14:00</option>
                                        <option value="14:00-15:00">14:00 - 15:00</option>
                                        <option value="19:00-20:00">19:00 - 20:00</option>
                                        <option value="20:00-21:00">20:00 - 21:00</option>
                                        <option value="21:00-22:00">21:00 - 22:00</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Payment Method -->
                    <section class="card p-6">
                        <h2 class="text-lg font-semibold text-text-primary mb-4">Método de Pago</h2>
                        <div class="space-y-3">
                            <label
                                class="payment-option flex items-center p-4 border border-border rounded-lg cursor-pointer hover:border-primary transition-colors">
                                <input type="radio" name="paymentMethod" value="cash" checked
                                    class="w-4 h-4 text-primary border-border focus:ring-primary" />
                                <div class="ml-3 flex items-center space-x-3">
                                    <i class="fas fa-money-bill-wave text-success text-lg"></i>
                                    <div>
                                        <div class="font-medium text-text-primary">Efectivo</div>
                                        <div class="text-sm text-text-secondary">Pago contra entrega</div>
                                    </div>
                                </div>
                            </label>
                            <label
                                class="payment-option flex items-center p-4 border border-border rounded-lg cursor-pointer hover:border-primary transition-colors">
                                <input type="radio" name="paymentMethod" value="card"
                                    class="w-4 h-4 text-primary border-border focus:ring-primary" />
                                <div class="ml-3 flex items-center space-x-3">
                                    <i class="fas fa-credit-card text-primary text-lg"></i>
                                    <div>
                                        <div class="font-medium text-text-primary">Tarjeta de Crédito/Débito</div>
                                        <div class="text-sm text-text-secondary">Visa, Mastercard, American Express</div>
                                    </div>
                                </div>
                            </label>
                            <label
                                class="payment-option flex items-center p-4 border border-border rounded-lg cursor-pointer hover:border-primary transition-colors">
                                <input type="radio" name="paymentMethod" value="transfer"
                                    class="w-4 h-4 text-primary border-border focus:ring-primary" />
                                <div class="ml-3 flex items-center space-x-3">
                                    <i class="fas fa-university text-accent text-lg"></i>
                                    <div>
                                        <div class="font-medium text-text-primary">Transferencia Bancaria</div>
                                        <div class="text-sm text-text-secondary">Mercado Pago, Ualá, Brubank</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Cash Change -->
                        <div id="cashChangeSection" class="mt-4">
                            <label for="cashAmount" class="block text-sm font-medium text-text-primary mb-1">¿Con cuánto
                                vas a pagar?</label>
                            <input type="number" id="cashAmount" name="cashAmount" class="input-field"
                                placeholder="Monto en efectivo" min="0" step="100" />
                            <div class="text-sm text-text-secondary mt-1">Opcional - para calcular el vuelto</div>
                        </div>
                    </section>

                </form>
            </div>

            <!-- Order Summary (Desktop Sidebar) -->
            <div class="lg:col-span-1 mt-8 lg:mt-0">
                <div class="sticky top-24">
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold text-text-primary mb-4">Resumen del Pedido</h2>

                        <!-- Cart Items -->
                        <div id="orderItems" class="space-y-3 mb-4">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Coupon Section -->
                        <div class="border-t border-border-light pt-4 mb-4">
                            <div class="flex space-x-2">
                                <input type="text" id="couponCode" placeholder="Código de descuento"
                                    class="flex-1 px-3 py-2 border border-border rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm" />
                                <button type="button" id="applyCoupon"
                                    class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-secondary-600 transition-colors text-sm font-medium">
                                    Aplicar
                                </button>
                            </div>
                            <div id="couponMessage" class="text-sm mt-2 hidden"></div>
                        </div>

                        <!-- Order Totals -->
                        <div class="border-t border-border-light pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-text-secondary">Subtotal</span>
                                <span id="subtotal" class="text-text-primary">$0</span>
                            </div>
                            <div id="discountRow" class="justify-between text-sm hidden">
                                <span class="text-success">Descuento</span>
                                <span id="discountAmount" class="text-success">-$0</span>
                            </div>
                            <div id="shippingRow" class="flex justify-between text-sm">
                                <span class="text-text-secondary">Envío</span>
                                <span id="shippingCost" class="text-text-primary">$1.500</span>
                            </div>
                            <div id="serviceFeeRow" class="justify-between text-sm hidden">
                                <span class="text-text-secondary">Servicio (3%)</span>
                                <span id="serviceFee" class="text-text-primary">$0</span>
                            </div>
                            <div class="border-t border-border-light pt-2 flex justify-between font-semibold">
                                <span class="text-text-primary">Total</span>
                                <span id="finalTotal" class="text-primary text-lg">$0</span>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" form="checkoutForm" id="placeOrderBtn"
                            class="btn-primary w-full mt-6 flex items-center justify-center">
                            <span id="placeOrderText">Confirmar Pedido</span>
                            <div id="placeOrderSpinner" class="hidden ml-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                            </div>
                        </button>

                        <!-- Security Notice -->
                        <div class="mt-4 flex items-center space-x-2 text-sm text-text-secondary">
                            <i class="fas fa-shield-alt text-success"></i>
                            <span>Tus datos están protegidos con encriptación SSL</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Order Summary (Hidden on Desktop) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-surface border-t border-border-light p-4 z-40">
        <div class="flex items-center justify-between mb-3">
            <span class="font-medium text-text-primary">Total del Pedido</span>
            <span id="mobileFinalTotal" class="text-lg font-bold text-primary">$0</span>
        </div>
        <button type="submit" form="checkoutForm" class="btn-primary w-full flex items-center justify-center">
            <span>Confirmar Pedido</span>
        </button>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed top-20 right-4 z-50 space-y-2"></div>

    <!-- Order Item Template -->
    <template id="orderItemTemplate">
        <div class="flex items-center space-x-3 py-2">
            <img class="w-12 h-12 rounded-lg object-cover" src alt />
            <div class="flex-1 min-w-0">
                <h4 class="font-medium text-text-primary text-sm truncate item-name"></h4>
                <p class="text-text-secondary text-xs item-options"></p>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-sm text-text-secondary item-quantity">Cantidad: 1</span>
                    <span class="font-medium text-primary text-sm item-total">$0</span>
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
        // Get cart data from localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let appliedCoupon = null;
        let isDelivery = true;

        // DOM Elements
        const deliveryBtn = document.getElementById('deliveryBtn');
        const pickupBtn = document.getElementById('pickupBtn');
        const deliverySection = document.getElementById('deliverySection');
        const orderItems = document.getElementById('orderItems');
        const subtotalEl = document.getElementById('subtotal');
        const discountRow = document.getElementById('discountRow');
        const discountAmount = document.getElementById('discountAmount');
        const shippingRow = document.getElementById('shippingRow');
        const shippingCost = document.getElementById('shippingCost');
        const serviceFee = document.getElementById('serviceFee');
        const finalTotal = document.getElementById('finalTotal');
        const mobileFinalTotal = document.getElementById('mobileFinalTotal');
        const checkoutForm = document.getElementById('checkoutForm');
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const placeOrderText = document.getElementById('placeOrderText');
        const placeOrderSpinner = document.getElementById('placeOrderSpinner');
        const toastContainer = document.getElementById('toastContainer');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Redirect if cart is empty
            if (cart.length === 0) {
                window.location.href = '{{ route('catalog') }}';
                return;
            }

            setupEventListeners();
            renderOrderItems();
            updateOrderSummary();
            loadCustomerData();
            setupFormValidation();
            // Asegurar que Delivery quede activado y campos requeridos al cargar
            setDeliveryMethod(true);
        });

        // Event Listeners
        function setupEventListeners() {
            // Delivery/Pickup toggle
            deliveryBtn.addEventListener('click', function() {
                setDeliveryMethod(true);
            });

            pickupBtn.addEventListener('click', function() {
                setDeliveryMethod(false);
            });

            // Delivery time scheduling
            document.querySelectorAll('input[name="deliveryTime"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const scheduledSection = document.getElementById('scheduledDelivery');
                    if (this.value === 'scheduled') {
                        scheduledSection.classList.remove('hidden');
                        // Set minimum date to today
                        const today = new Date().toISOString().split('T')[0];
                        document.getElementById('deliveryDate').min = today;
                    } else {
                        scheduledSection.classList.add('hidden');
                    }
                });
            });

            // Payment method changes
            document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const cashSection = document.getElementById('cashChangeSection');
                    if (this.value === 'cash') {
                        cashSection.classList.remove('hidden');
                    } else {
                        cashSection.classList.add('hidden');
                    }
                    updateOrderSummary();
                });
            });

            // Coupon application
            document.getElementById('applyCoupon').addEventListener('click', applyCouponCode);
            document.getElementById('couponCode').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyCouponCode();
                }
            });

            // Form submission
            checkoutForm.addEventListener('submit', handleFormSubmit);

            // Auto-save customer data
            const customerFields = ['firstName', 'lastName', 'phone'];
            customerFields.forEach(field => {
                document.getElementById(field).addEventListener('input', saveCustomerData);
            });
        }

        // Set delivery method
        function setDeliveryMethod(delivery) {
            isDelivery = delivery;

            if (delivery) {
                deliveryBtn.classList.add('active', 'border-primary', 'bg-primary-50');
                deliveryBtn.classList.remove('border-border', 'bg-surface');
                deliveryBtn.querySelector('i').classList.add('text-primary');
                deliveryBtn.querySelector('i').classList.remove('text-text-secondary');
                deliveryBtn.querySelector('.font-medium').classList.add('text-primary');
                deliveryBtn.querySelector('.font-medium').classList.remove('text-text-primary');

                pickupBtn.classList.remove('active', 'border-primary', 'bg-primary-50');
                pickupBtn.classList.add('border-border', 'bg-surface');
                pickupBtn.querySelector('i').classList.remove('text-primary');
                pickupBtn.classList.add('text-text-secondary');
                pickupBtn.querySelector('.font-medium').classList.remove('text-primary');
                pickupBtn.querySelector('.font-medium').classList.add('text-text-primary');

                deliverySection.classList.remove('hidden');
                // Make address fields required
                ['address', 'city', 'postalCode'].forEach(field => {
                    document.getElementById(field).required = true;
                });
            } else {
                pickupBtn.classList.add('active', 'border-primary', 'bg-primary-50');
                pickupBtn.classList.remove('border-border', 'bg-surface');
                pickupBtn.querySelector('i').classList.add('text-primary');
                pickupBtn.querySelector('i').classList.remove('text-text-secondary');
                pickupBtn.querySelector('.font-medium').classList.add('text-primary');
                pickupBtn.querySelector('.font-medium').classList.remove('text-text-primary');

                deliveryBtn.classList.remove('active', 'border-primary', 'bg-primary-50');
                deliveryBtn.classList.add('border-border', 'bg-surface');
                deliveryBtn.querySelector('i').classList.remove('text-primary');
                deliveryBtn.querySelector('i').classList.add('text-text-secondary');
                deliveryBtn.querySelector('.font-medium').classList.remove('text-primary');
                deliveryBtn.querySelector('.font-medium').classList.add('text-text-primary');

                deliverySection.classList.add('hidden');
                // Remove address field requirements
                ['address', 'city', 'postalCode'].forEach(field => {
                    document.getElementById(field).required = false;
                });
            }

            updateOrderSummary();
        }

        // Render order items
        function renderOrderItems() {
            orderItems.innerHTML = '';

            cart.forEach(item => {
                const orderItem = createOrderItem(item);
                orderItems.appendChild(orderItem);
            });
        }

        // Create order item
        function createOrderItem(item) {
            const template = document.getElementById('orderItemTemplate');
            const orderItem = template.content.cloneNode(true);

            const basePrice = Number(item.unitPrice || item.price || 0);
            const variantsTotal = Array.isArray(item.variants) ?
                item.variants.reduce((sum, v) => sum + Number(v.priceModifier || 0), 0) :
                0;
            const optionsTotal = Array.isArray(item.options) ?
                item.options.reduce((sum, o) => sum + Number(o.priceModifier || 0), 0) :
                0;
            const unitPrice = basePrice + variantsTotal + optionsTotal;
            const totalPrice = unitPrice * item.quantity;

            orderItem.querySelector('img').src = item.image ? '/images/products/' + item.image :
                'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
            orderItem.querySelector('img').alt = item.name;
            orderItem.querySelector('.item-name').textContent = item.name;
            orderItem.querySelector('.item-quantity').textContent = `Cantidad: ${item.quantity}`;
            orderItem.querySelector('.item-total').textContent = formatPrice(totalPrice);

            // Set options text
            let optionsText = '';
            if (item.variants && item.variants.length > 0) {
                optionsText += item.variants.map(v => v.value).join(', ');
            }
            if (item.options && item.options.length > 0) {
                if (optionsText) optionsText += ', ';
                optionsText += item.options.map(o => o.value).join(', ');
            }
            orderItem.querySelector('.item-options').textContent = optionsText || 'Sin opciones adicionales';

            return orderItem;
        }

        // Update order summary
        function updateOrderSummary() {
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
            let discount = 0;
            let shipping = 0;
            let service = 0;

            // Apply coupon discount
            if (appliedCoupon) {
                if (appliedCoupon.type === 'percentage') {
                    discount = subtotal * (appliedCoupon.value / 100);
                } else {
                    discount = appliedCoupon.value;
                }
            }

            // No hay costo de shipping ni cargo por servicio
            shipping = 0;
            service = 0;

            const total = subtotal - discount;

            // Update UI
            subtotalEl.textContent = formatPrice(subtotal);

            if (discount > 0) {
                discountRow.classList.remove('hidden');
                discountAmount.textContent = `-${formatPrice(discount)}`;
            } else {
                discountRow.classList.add('hidden');
            }

            // Ocultar fila de envío y cargo por servicio ya que no hay costo
            shippingRow.classList.add('hidden');

            const serviceFeeRow = document.getElementById('serviceFeeRow');
            if (serviceFeeRow) {
                serviceFeeRow.classList.add('hidden');
            }
            finalTotal.textContent = formatPrice(total);
            mobileFinalTotal.textContent = formatPrice(total);
        }

        // Apply coupon code
        function applyCouponCode() {
            const couponCode = document.getElementById('couponCode').value.trim().toUpperCase();
            const couponMessage = document.getElementById('couponMessage');

            if (!couponCode) {
                showCouponMessage('Por favor ingresa un código de descuento', 'error');
                return;
            }

            // Demo coupon: BURGER10 for 10% discount
            if (couponCode === 'BURGER10') {
                if (appliedCoupon && appliedCoupon.code === 'BURGER10') {
                    showCouponMessage('Este cupón ya está aplicado', 'warning');
                    return;
                }

                appliedCoupon = {
                    code: 'BURGER10',
                    type: 'percentage',
                    value: 10,
                    description: '10% de descuento'
                };

                showCouponMessage('¡Cupón aplicado! 10% de descuento', 'success');
                updateOrderSummary();
            } else {
                showCouponMessage('Código de descuento inválido', 'error');
            }
        }

        // Show coupon message
        function showCouponMessage(message, type) {
            const couponMessage = document.getElementById('couponMessage');
            couponMessage.textContent = message;
            couponMessage.className =
                `text-sm mt-2 ${type === 'success' ? 'text-success' : type === 'warning' ? 'text-warning' : 'text-error'}`;
            couponMessage.classList.remove('hidden');

            setTimeout(() => {
                couponMessage.classList.add('hidden');
            }, 5000);
        }

        // Form validation setup
        function setupFormValidation() {
            const form = document.getElementById('checkoutForm');
            const inputs = form.querySelectorAll('input[required], select[required]');

            inputs.forEach(input => {
                input.addEventListener('blur', validateField);
                input.addEventListener('input', clearFieldError);
            });
        }

        // Validate individual field
        function validateField(event) {
            const field = event.target;
            const feedback = field.parentNode.querySelector('.invalid-feedback');

            if (!field.checkValidity()) {
                field.classList.add('border-error');
                if (feedback) {
                    feedback.classList.remove('hidden');
                }
                return false;
            } else {
                field.classList.remove('border-error');
                if (feedback) {
                    feedback.classList.add('hidden');
                }
                return true;
            }
        }

        // Clear field error
        function clearFieldError(event) {
            const field = event.target;
            const feedback = field.parentNode.querySelector('.invalid-feedback');

            if (field.checkValidity()) {
                field.classList.remove('border-error');
                if (feedback) {
                    feedback.classList.add('hidden');
                }
            }
        }

        // Handle form submission
        async function handleFormSubmit(event) {
            event.preventDefault();

            // Validate form
            const form = event.target;
            const formData = new FormData(form);
            let isValid = true;

            // Check required fields based on delivery method
            const requiredFields = ['firstName', 'lastName', 'phone'];
            if (isDelivery) {
                requiredFields.push('address', 'city', 'postalCode');
            }

            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (!validateField({
                        target: field
                    })) {
                    isValid = false;
                }
            });

            if (!isValid) {
                showToast('Por favor completa todos los campos requeridos', 'error');
                return;
            }

            // Validación extra explícita para Delivery (evita nulos)
            if (isDelivery) {
                const addr = (document.getElementById('address').value || '').trim();
                const city = (document.getElementById('city').value || '').trim();
                const postal = (document.getElementById('postalCode').value || '').trim();
                if (!addr || !city || !postal) {
                    showToast('Completa dirección, ciudad y código postal para Delivery', 'error');
                    return;
                }
            }

            // Show loading state
            placeOrderText.textContent = 'Procesando...';
            placeOrderSpinner.classList.remove('hidden');
            placeOrderBtn.disabled = true;

            try {
                // Build payload for backend
                const payload = {
                    items: cart.map(item => ({
                        product_id: item.productId,
                        quantity: item.quantity,
                        variants: item.variants || [],
                        options: item.options || [],
                        special_instructions: item.specialInstructions || null,
                    })),
                    // Datos de contacto
                    firstName: formData.get('firstName'),
                    lastName: formData.get('lastName'),
                    phone: formData.get('phone'),
                    payment_method: formData.get('paymentMethod'),
                    notes: null, // Ya no hay orderNotes separados
                    delivery_method: isDelivery ? 'delivery' : 'pickup',
                    ...(isDelivery ? {
                        address: (formData.get('address') || '').trim(),
                        city: (formData.get('city') || '').trim(),
                        postal_code: (formData.get('postalCode') || '').trim(),
                        floor: formData.get('floor') || '',
                        delivery_notes: formData.get('deliveryNotes') || ''
                    } : {}),
                    // Información de horario de entrega
                    deliveryTime: formData.get('deliveryTime'),
                    deliveryDate: formData.get('deliveryDate'),
                    deliveryTimeSlot: formData.get('deliveryTimeSlot'),
                    // Información de pago
                    cashAmount: formData.get('cashAmount'),
                    coupon_code: appliedCoupon ? appliedCoupon.code : null,
                };

                const resp = await fetch('{{ route('orders.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                const data = await resp.json();
                if (!resp.ok || !data.success) {
                    throw new Error(data.error || 'Error al procesar el pedido');
                }

                // Clear cart
                localStorage.removeItem('cart');
                saveCustomerData();

                // Redirect to confirmation
                window.location.href = '{{ route('order.confirmation', ['orderNumber' => 'temp']) }}'.replace('temp',
                    data.order.order_number);

            } catch (error) {
                console.error('Order processing error:', error);
                showToast('Error al procesar el pedido. Intenta nuevamente.', 'error');

                // Reset button state
                placeOrderText.textContent = 'Confirmar Pedido';
                placeOrderSpinner.classList.add('hidden');
                placeOrderBtn.disabled = false;
            }
        }

        // Calculate final total
        function calculateFinalTotal() {
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
            let discount = 0;
            let shipping = 0;
            let service = 0;

            if (appliedCoupon) {
                if (appliedCoupon.type === 'percentage') {
                    discount = subtotal * (appliedCoupon.value / 100);
                } else {
                    discount = appliedCoupon.value;
                }
            }

            // No hay costo de shipping ni cargo por servicio
            shipping = 0;
            service = 0;

            return subtotal - discount;
        }

        // Save customer data
        function saveCustomerData() {
            const customerData = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                phone: document.getElementById('phone').value
            };

            localStorage.setItem('customerData', JSON.stringify(customerData));
        }

        // Load customer data
        function loadCustomerData() {
            const savedData = localStorage.getItem('customerData');
            if (savedData) {
                const customerData = JSON.parse(savedData);

                Object.keys(customerData).forEach(key => {
                    const field = document.getElementById(key);
                    if (field && customerData[key]) {
                        field.value = customerData[key];
                    }
                });
            }
        }

        // Format price
        function formatPrice(price) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0
            }).format(price);
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className =
                `bg-${type === 'success' ? 'success' : 'error'} text-white px-4 py-3 rounded-lg shadow-lg animate-toast-slide`;
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
