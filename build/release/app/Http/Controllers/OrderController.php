<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Mostrar el carrito de compras
     */
    public function cart()
    {
        return \view('cart');
    }

    /**
     * Mostrar el proceso de checkout
     */
    public function checkout()
    {
        $user = Auth::user();
        $addresses = $user ? $user->addresses : collect();

        return \view('checkout', compact('addresses'));
    }

    /**
     * Procesar el pedido
     */
    public function store(Request $request)
    {
        // Log incoming payload for debugging
        \Log::info('Orders.store payload', ['payload' => $request->all()]);

        // Base validation
        $rules = [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|numeric|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,transfer',
            'notes' => 'nullable|string|max:500',
            'delivery_method' => 'required|in:delivery,pickup',
            'coupon_code' => 'nullable|string|max:50',
            // Contact info
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            // Address handling (only for delivery)
            'address' => 'required_if:delivery_method,delivery|nullable|string|max:255',
            'city' => 'required_if:delivery_method,delivery|nullable|string|max:120',
            'postal_code' => 'required_if:delivery_method,delivery|nullable|string|max:20',
            'floor' => 'nullable|string|max:50',
            'delivery_notes' => 'nullable|string|max:500',
            // Delivery scheduling
            'deliveryTime' => 'nullable|in:now,scheduled',
            'deliveryDate' => 'nullable|date|after_or_equal:today',
            'deliveryTimeSlot' => 'nullable|string|max:20',
            'cashAmount' => 'nullable|numeric|min:0',
        ];

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Determine delivery method
            $deliveryMethod = $request->input('delivery_method', 'delivery');

            // Calcular totales
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->is_available) {
                    return response()->json(['error' => "El producto {$product->name} no está disponible"], 400);
                }

                // Calcular modificadores de variantes y opciones
                $variantModifiers = 0;
                if (!empty($item['variants']) && is_array($item['variants'])) {
                    foreach ($item['variants'] as $v) {
                        $variantModifiers += isset($v['priceModifier']) ? (float) $v['priceModifier'] : 0;
                    }
                }

                $optionModifiers = 0;
                if (!empty($item['options']) && is_array($item['options'])) {
                    foreach ($item['options'] as $o) {
                        $optionModifiers += isset($o['priceModifier']) ? (float) $o['priceModifier'] : 0;
                    }
                }

                $unitPrice = (float) $product->base_price + $variantModifiers + $optionModifiers;
                $totalPrice = $unitPrice * (int) $item['quantity'];

                $subtotal += $totalPrice;

                // Combinar variants y options en customizations
                $customizations = [];
                if (!empty($item['variants'])) {
                    $customizations['variants'] = $item['variants'];
                }
                if (!empty($item['options'])) {
                    $customizations['options'] = $item['options'];
                }

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'selected_variants' => !empty($customizations['variants']) ? $customizations['variants'] : null,
                    'selected_options' => !empty($customizations['options']) ? $customizations['options'] : null,
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }

            // Aplicar cupón si existe
            $discountAmount = 0;
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();
                if ($coupon && $coupon->isValid()) {
                    $discountAmount = $coupon->calculateDiscount($subtotal);
                    $coupon->incrementUsage();
                }
            }

            // No hay costo de entrega ni cargo por servicio
            $deliveryFee = 0;

            $totalAmount = $subtotal - $discountAmount;

            // Crear el pedido usando el esquema real de la tabla
            $orderNumber = 'ORD-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));

            // Asegurar usuario (si no hay sesión, usar/crear invitado)
            $user = Auth::user();
            if (!$user) {
                $user = \App\Models\User::firstOrCreate(
                    ['email' => 'guest@tecocina.local'],
                    [
                        'name' => 'Invitado',
                        'password' => bcrypt(Str::random(16)),
                        'role' => 'customer',
                        'is_active' => true,
                    ]
                );
            }

            // Crear dirección mínima si corresponde (para delivery)
            $addressModel = null;
            if ($deliveryMethod === 'delivery') {
                $street = $request->input('address', '') ?: 'Dirección proporcionada en pedido';
                $city = $request->input('city', '') ?: 'Ciudad';
                $postalCode = $request->input('postal_code', '') ?: '';

                $addressModel = Address::create([
                    'user_id' => $user->id,
                    'name' => 'Entrega puntual',
                    'street' => $street,
                    'number' => '',
                    'neighborhood' => '',
                    'city' => $city,
                    'state' => '',
                    'postal_code' => $postalCode,
                    'reference' => $request->input('delivery_notes', ''),
                    'is_default' => false,
                ]);
            } else {
                // Para retiro, crear una address dummy vinculada al usuario
                $addressModel = Address::firstOrCreate([
                    'user_id' => $user->id,
                    'name' => 'Retiro en local',
                    'street' => 'Local',
                    'number' => 'N/A',
                    'neighborhood' => 'N/A',
                    'city' => 'Olavarría',
                    'state' => 'BA',
                    'postal_code' => '7400',
                ], [
                    'reference' => 'Pedido para retiro',
                    'is_default' => false,
                ]);
            }

            // Capturar información de horario de entrega
            $deliveryTime = $request->input('deliveryTime', 'now');
            $scheduledInfo = '';
            if ($deliveryTime === 'scheduled') {
                $deliveryDate = $request->input('deliveryDate', '');
                $deliveryTimeSlot = $request->input('deliveryTimeSlot', '');
                if ($deliveryDate && $deliveryTimeSlot) {
                    $scheduledInfo = "Programado para: {$deliveryDate} {$deliveryTimeSlot}";
                }
            } else {
                $scheduledInfo = 'Lo antes posible';
            }

            // Capturar información de método de pago
            $paymentMethod = $request->input('payment_method', 'cash');
            $paymentInfo = '';
            switch ($paymentMethod) {
                case 'cash':
                    $paymentInfo = 'Pago: Efectivo';
                    $cashAmount = $request->input('cashAmount', '');
                    if ($cashAmount) {
                        $paymentInfo .= ' (Con: $' . number_format($cashAmount, 0) . ')';
                    }
                    break;
                case 'card':
                    $paymentInfo = 'Pago: Tarjeta de Crédito/Débito';
                    break;
                case 'transfer':
                    $paymentInfo = 'Pago: Transferencia Bancaria';
                    break;
            }

            // Combinar todas las notas (incluye datos de contacto del checkout)
            $orderNotes = $request->input('notes', '');
            $deliveryNotes = $request->input('delivery_notes', '');

            $notesArray = array_filter([
                'Cliente: ' . trim($request->input('firstName', '') . ' ' . $request->input('lastName', '')),
                'Teléfono: ' . $request->input('phone', ''),
                $scheduledInfo,
                $paymentInfo,
                $deliveryNotes,
                $orderNotes
            ]);

            $combinedNotes = implode(' | ', $notesArray);

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'address_id' => $addressModel?->id,
                'contact_name' => trim($request->input('firstName', '') . ' ' . $request->input('lastName', '')) ?: 'Invitado',
                'contact_phone' => $request->input('phone', ''),
                'status' => 'pending',
                'payment_method' => $paymentMethod === 'transfer' ? 'online' : $paymentMethod,
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $combinedNotes ?: null,
            ]);

            // Crear los items del pedido
            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            DB::commit();

            \Log::info('Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            return \response()->json([
                'success' => true,
                'order' => $order->load(['items.product', 'address']),
                'message' => 'Pedido creado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Orders.store failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return \response()->json(['error' => 'Error al procesar el pedido'], 500);
        }
    }

    /**
     * Mostrar confirmación del pedido
     */
    public function confirmation($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'address'])
            ->firstOrFail();

        return \view('order-confirmation', compact('order'));
    }

    /**
     * API: Obtener pedidos del usuario
     */
    public function userOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return \response()->json($orders);
    }

    /**
     * API: Validar cupón
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return \response()->json(['error' => 'Cupón no encontrado'], 404);
        }

        if (!$coupon->isValid()) {
            return \response()->json(['error' => 'Cupón no válido o expirado'], 400);
        }

        $discount = $coupon->calculateDiscount($request->order_amount);

        return \response()->json([
            'valid' => true,
            'discount' => $discount,
            'coupon' => $coupon
        ]);
    }
}
