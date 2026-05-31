<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\BusinessSetting;
use App\Models\LoyaltySetting;
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
        // Verificar si el día actual está habilitado
        $hoy = now()->format('Y-m-d');
        $weeklyConfig = \App\Models\WeeklyTurnoConfig::getConfigForDate($hoy);
        $isDayEnabled = $weeklyConfig && $weeklyConfig->is_enabled;

        // Verificar si se debe saltar la selección de turno
        $skipTurnoSelection = BusinessSetting::get('skip_turno_selection', false);

        // Obtener productos de acompañamientos (categorías 2, 3, 4, 5)
        $acompanamientosCategories = [2, 3, 4, 5]; // Acompañamientos, Bebidas, Combos, Postres
        $acompanamientosProducts = Product::whereIn('category_id', $acompanamientosCategories)
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return \view('cart', compact('isDayEnabled', 'skipTurnoSelection', 'acompanamientosProducts'));
    }

    /**
     * Mostrar la selección de turnos
     */
    public function turnos()
    {
        if (BusinessSetting::get('site_offline', false)) {
            return redirect()->route('home')->with('error', 'El local está cerrado. Podés explorar el menú pero los pedidos no están disponibles por el momento.');
        }

        // Si está habilitado saltar selección de turno, redirigir directamente al checkout
        if (\App\Models\BusinessSetting::get('skip_turno_selection', false)) {
            return redirect()->route('checkout');
        }

        $hoy = now()->format('Y-m-d');

        // Verificar si el día actual está habilitado en la configuración semanal
        $weeklyConfig = \App\Models\WeeklyTurnoConfig::getConfigForDate($hoy);

        if (!$weeklyConfig || !$weeklyConfig->is_enabled) {
            return redirect()->route('cart')->with('error', 'Lo sentimos, hoy no estamos tomando pedidos. Por favor, intenta otro día.');
        }

        $config = \App\Models\TurnoConfig::getCurrentConfig();

        // Obtener TODOS los microturnos dinámicos para hoy (disponibles y no disponibles)
        $microturnosHoy = \App\Models\DynamicMicroturno::generarParaFecha($hoy);

        return view('turnos', compact('config', 'microturnosHoy', 'hoy'));
    }

    /**
     * Mostrar el proceso de checkout
     */
    public function checkout()
    {
        if (BusinessSetting::get('site_offline', false)) {
            return redirect()->route('home')->with('error', 'El local está cerrado. Podés explorar el menú pero los pedidos no están disponibles por el momento.');
        }

        $user = Auth::user();
        $addresses = $user ? $user->addresses()->orderByDesc('is_default')->latest()->get() : collect();
        $savedAddresses = $addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'name' => $address->name,
                'street' => $address->street,
                'number' => $address->number,
                'reference' => $address->reference,
                'is_default' => (bool) $address->is_default,
            ];
        })->values();
        [$firstName, $lastName] = $this->splitName($user?->name ?? '');
        $checkoutProfile = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $user?->phone ?? '',
        ];
        // businessSettings ya está disponible globalmente desde AppServiceProvider
        // No sobrescribir para que el header tenga acceso a todas las configuraciones (logos, etc.)

        // Obtener productos de acompañamientos (categorías 2, 3, 4, 5)
        $acompanamientosCategories = [2, 3, 4, 5]; // Acompañamientos, Bebidas, Combos, Postres
        $acompanamientosProducts = Product::whereIn('category_id', $acompanamientosCategories)
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return \view('checkout', compact('addresses', 'savedAddresses', 'checkoutProfile', 'acompanamientosProducts'));
    }

    /**
     * Procesar el pedido
     */
    public function store(Request $request)
    {
        if (BusinessSetting::get('site_offline', false)) {
            return response()->json([
                'success' => false,
                'error' => 'El local está cerrado. Los pedidos no están disponibles por el momento.',
            ], 403);
        }

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
            // Contact info
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            // Address handling (only for delivery)
            'address' => 'required_if:delivery_method,delivery|nullable|string|max:255',
            'address_id' => 'nullable|integer|exists:addresses,id',
            'save_address' => 'nullable|boolean',
            'address_label' => 'nullable|string|max:80',
            // Removidos ciudad y código postal del flujo de delivery
            'floor' => 'nullable|string|max:50',
            'delivery_notes' => 'nullable|string|max:500',
            // Delivery scheduling
            'deliveryTime' => 'nullable|in:now,scheduled',
            'deliveryDate' => 'nullable|date|after_or_equal:today',
            'deliveryTimeSlot' => 'nullable|string|max:20',
            // Microturno obligatorio solo si NO se salta la selección de turno y NO se coordina por WhatsApp
            'microturno_id' => (
                \App\Models\BusinessSetting::get('skip_turno_selection', false)
                || (int) $request->input('coordinate_by_whatsapp', 0) === 1
                    ? 'nullable'
                    : 'required'
            ) . '|string',
            'coordinate_by_whatsapp' => 'nullable|in:0,1',
            'cashAmount' => 'nullable|numeric|min:0',
        ];

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Protección anti-duplicados: mismo teléfono + mismo total en los últimos 60s
            $phone = $request->input('phone');
            $recentDuplicate = \App\Models\Order::where('contact_phone', $phone)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();

            if ($recentDuplicate) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Tu pedido ya fue registrado. Si necesitás hacer otro, esperá un momento.',
                ], 429);
            }

            // Validar que el día actual esté habilitado en la configuración semanal
            $hoy = now()->format('Y-m-d');
            $weeklyConfig = \App\Models\WeeklyTurnoConfig::getConfigForDate($hoy);

            if (!$weeklyConfig || !$weeklyConfig->is_enabled) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Lo sentimos, hoy no estamos tomando pedidos. Por favor, intenta otro día.'
                ], 400);
            }

            // Validar disponibilidad del microturno antes de crear el pedido
            $microturnoId = $request->input('microturno_id');
            if ($microturnoId) {
                // Obtener microturnos dinámicos para hoy
                $microturnosHoy = \App\Models\DynamicMicroturno::generarParaFecha($hoy);
                $microturno = $microturnosHoy->first(function ($m) use ($microturnoId) {
                    return $m->getSortOrderAttribute() == $microturnoId;
                });

                if (!$microturno) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'El horario seleccionado no existe.'
                    ], 400);
                }

                if (!$microturno->getIsDisponibleAttribute()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'El horario seleccionado está lleno. Por favor, elimine o saque de confirmado un pedido existente, o seleccione otro horario.',
                        'microturno_lleno' => true,
                        'capacidad_maxima' => $microturno->getCapacidadMaximaAttribute(),
                        'pedidos_activos' => $microturno->getPedidosActivosAttribute()
                    ], 400);
                }
            }

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

                // Calcular modificadores desde configuration_data
                $configurationModifiers = 0;
                if (!empty($item['configuration']) && is_array($item['configuration'])) {
                    // Buscar configuraciones en ProductConfiguration para obtener price_modifier
                    foreach ($item['configuration'] as $configType => $configValue) {
                        if (is_array($configValue)) {
                            // Para arrays (aderezos, extras, dips, dip_extra)
                            foreach ($configValue as $value) {
                                // Mapear nombres de configuración
                                $configName = match ($configType) {
                                    'aderezos' => 'Aderezos',
                                    'extras' => 'Extras',
                                    'dips' => 'Dip',
                                    'dip_extra' => 'Dip Extra',
                                    default => ucfirst($configType)
                                };

                                $config = \App\Models\ProductConfiguration::where('name', $configName)
                                    ->where('value', $value)
                                    ->where('is_available', true)
                                    ->first();
                                if ($config) {
                                    $configurationModifiers += (float) $config->price_modifier;
                                }
                            }
                        } else {
                            // Para strings (medallones, tipo_medallon)
                            $configName = match ($configType) {
                                'medallones' => 'Medallones',
                                'tipo_medallon' => 'Tipo de Medallón',
                                default => ucfirst($configType)
                            };

                            $config = \App\Models\ProductConfiguration::where('name', $configName)
                                ->where('value', $configValue)
                                ->where('is_available', true)
                                ->first();
                            if ($config) {
                                $configurationModifiers += (float) $config->price_modifier;
                            }
                        }
                    }
                }

                // Usar precio del frontend si está disponible, sino calcularlo
                if (isset($item['price']) && $item['price'] > 0) {
                    // El frontend ya calculó el precio total, usarlo directamente
                    $unitPrice = (int) $item['price'];
                    $totalPrice = (int) ($unitPrice * (int) $item['quantity']);
                } else {
                    // Fallback: calcular con redondeo entero para evitar errores de punto flotante
                    $unitPrice = (int) round(((float) $product->base_price) + $configurationModifiers);
                    $totalPrice = (int) ($unitPrice * (int) $item['quantity']);
                }

                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'configuration_data' => !empty($item['configuration']) ? $item['configuration'] : null,
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }

            // Sin costo de envío
            $deliveryFee = 0;

            // Procesar cupón si existe
            $couponDiscount = 0;
            $couponId = null;
            $couponAllowsCashDiscount = false;
            $paymentMethod = $request->input('payment_method');

            if ($request->has('coupon_id') && $request->coupon_id) {
                $coupon = \App\Models\Coupon::find($request->coupon_id);
                if ($coupon && $coupon->isValid()) {
                    $couponDiscount = (int) round($coupon->calculateDiscount($subtotal));
                    $couponId = $coupon->id;
                    $couponAllowsCashDiscount = (bool) $coupon->allow_cash_discount;
                }
            }

            // Descuento por efectivo: se aplica si no hay cupón o si el cupón permite acumular
            $cashDiscount = 0;
            if ($paymentMethod === 'cash' && ($couponDiscount == 0 || $couponAllowsCashDiscount)) {
                $cashDiscountPercentage = BusinessSetting::get('cash_discount_percentage', 0);
                if ($cashDiscountPercentage > 0) {
                    $cashDiscount = (int) round($subtotal * ((float) $cashDiscountPercentage / 100));
                }
            }

            $discountAmount = $couponDiscount + $cashDiscount;
            $totalAmount = (int) max(0, $subtotal + $deliveryFee - $discountAmount);

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

            $fullName = trim($request->input('firstName', '') . ' ' . $request->input('lastName', ''));
            if (Auth::check() && $fullName !== '') {
                $user->update([
                    'name' => $fullName,
                    'phone' => $request->input('phone', $user->phone),
                ]);
            }

            // Crear dirección mínima si corresponde (para delivery)
            $addressModel = null;
            if ($deliveryMethod === 'delivery') {
                $selectedAddressId = (int) $request->input('address_id');
                if ($selectedAddressId > 0) {
                    $addressModel = Address::where('id', $selectedAddressId)
                        ->where('user_id', $user->id)
                        ->first();
                }

                if (!$addressModel) {
                    [$street, $number] = $this->splitStreetNumber((string) $request->input('address', ''));
                    $saveAddress = (bool) $request->boolean('save_address') && Auth::check();
                    $addressName = trim((string) $request->input('address_label', ''));

                    $makeDefault = false;
                    if ($saveAddress) {
                        $makeDefault = !$user->addresses()->exists();
                        if ($makeDefault) {
                            $user->addresses()->update(['is_default' => false]);
                        }
                    }

                    $addressModel = Address::create([
                        'user_id' => $user->id,
                        'name' => $addressName !== '' ? $addressName : ($saveAddress ? 'Mi dirección' : 'Entrega puntual'),
                        'street' => $street ?: 'Dirección proporcionada en pedido',
                        'number' => $number,
                        'neighborhood' => '',
                        'city' => 'Olavarría',
                        'state' => 'Buenos Aires',
                        'postal_code' => '',
                        'reference' => $request->input('delivery_notes', ''),
                        'is_default' => $makeDefault,
                    ]);
                }
            }
            // Para pickup, NO crear dirección - dejar addressModel como null

            // Capturar información de horario de entrega (solo para lógica interna, no para notas)
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

            // Notas: separar tipos
            // - delivery_notes: SOLO instrucciones de entrega del usuario (se guardan en address.reference)
            // - notes: SOLO notas del pedido escritas por el usuario (sin nombre/teléfono/forma de pago)
            $orderNotes = trim($request->input('notes', '')) ?: null;
            $deliveryNotes = trim($request->input('delivery_notes', '')) ?: null;

            // Determinar microturno a asignar: si no viene desde el checkout, auto-asignar
            $microturnoSortOrder = $request->input('microturno_id');
            $coordinateByWhatsApp = (int) $request->input('coordinate_by_whatsapp', 0) === 1;

            if (empty($microturnoSortOrder) && !$coordinateByWhatsApp) {
                try {
                    $fecha = now()->format('Y-m-d');
                    $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
                    // Primero disponible segun capacidad
                    $available = $microturnos->first(function ($m) {
                        return method_exists($m, 'getIsDisponibleAttribute') ? $m->getIsDisponibleAttribute() : true;
                    });
                    // Si no hay ninguno disponible, dejar null (no asignar forzado)
                    if ($available && method_exists($available, 'getSortOrderAttribute')) {
                        $microturnoSortOrder = $available->getSortOrderAttribute();
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Auto-assign microturno failed', ['error' => $e->getMessage()]);
                }
            }

            // Si el pedido se coordina por WhatsApp, agregar nota interna
            if ($coordinateByWhatsApp) {
                $coordNote = '[Pedido grande: horario a coordinar por WhatsApp]';
                $orderNotes = $orderNotes ? ($coordNote . ' ' . $orderNotes) : $coordNote;
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'address_id' => $addressModel?->id,
                'microturno_sort_order' => $microturnoSortOrder,
                'contact_name' => trim($request->input('firstName', '') . ' ' . $request->input('lastName', '')) ?: 'Invitado',
                'contact_phone' => $request->input('phone', ''),
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'coupon_id' => $couponId,
                'total_amount' => $totalAmount,
                'notes' => $orderNotes,
            ]);

            // Crear los items del pedido
            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            if ($couponId) {
                try {
                    $coupon = \App\Models\Coupon::find($couponId);
                    if ($coupon) {
                        $coupon->incrementUsage();
                        $coupon->orders()->attach($order->id, [
                            'discount_applied' => $couponDiscount,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error processing coupon: ' . $e->getMessage(), [
                        'coupon_id' => $couponId,
                        'order_id' => $order->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Los microturnos dinámicos se calculan automáticamente
            // No necesitamos incrementar contadores manualmente

            DB::commit();

            \Log::info('Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            return \response()->json([
                'success' => true,
                'order' => $order->load(['items.product', 'address', 'coupon']),
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
     * Redirige al tracking del último pedido activo del usuario
     */
    public function myLatestTracking()
    {
        // Si está logueado, intenta redirigir a su pedido activo
        if (Auth::check()) {
            $order = Order::where('user_id', Auth::id())
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->latest()
                ->first();

            if ($order) {
                return redirect()->route('orders.tracking', ['orderNumber' => $order->order_number]);
            }
        }

        return view('orders.my-latest');
    }

    /**
     * Pantalla de seguimiento del pedido en tiempo real
     */
    public function tracking($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'address'])
            ->firstOrFail();

        $wallet  = Auth::user()?->loyaltyWallet;
        $setting = LoyaltySetting::first();

        return view('orders.tracking', compact('order', 'wallet', 'setting'));
    }

    /**
     * API: Estado actual del pedido para polling
     */
    public function getStatus($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->firstOrFail();

        $wallet  = Auth::user()?->loyaltyWallet;
        $setting = LoyaltySetting::first();

        $microturno = $order->microturno;
        $eta = $microturno ? $microturno->getFormattedTimeAttribute() : null;

        return response()->json([
            'status'          => $order->status,
            'status_label'    => $order->status_label,
            'is_delivery'     => $order->address_id !== null,
            'created_at'      => optional($order->created_at)->toIso8601String(),
            'confirmed_at'    => optional($order->confirmed_at)->toIso8601String(),
            'preparing_at'    => optional($order->preparing_at)->toIso8601String(),
            'ready_at'        => optional($order->ready_at)->toIso8601String(),
            'on_the_way_at'   => optional($order->out_for_delivery_at)->toIso8601String(),
            'delivered_at'    => optional($order->delivered_at)->toIso8601String(),
            'eta'             => $eta,
            'site_offline'    => (bool) BusinessSetting::get('site_offline', false),
            'loyalty'         => [
                'current_stickers' => $wallet?->current_stickers ?? 0,
                'target_stickers'  => $setting?->target_stickers ?? 8,
            ],
        ]);
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

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        return [$first, $last];
    }

    private function splitStreetNumber(string $rawAddress): array
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $rawAddress) ?? '');
        if ($normalized === '') {
            return ['', ''];
        }

        $parts = explode(' ', $normalized);
        $number = array_pop($parts);
        $street = trim(implode(' ', $parts));

        if ($street === '') {
            return [$normalized, ''];
        }

        return [$street, $number];
    }
}
