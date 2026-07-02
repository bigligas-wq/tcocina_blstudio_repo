<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Microturno;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductConfiguration;
use App\Models\Sauce;
use App\Models\TurnoConfig;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private LoyaltyService $loyaltyService;

    public function __construct()
    {
        $this->loyaltyService = app(LoyaltyService::class);
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Acceso denegado');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard principal del administrador
     */
    public function dashboard(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $endDate   = \Carbon\Carbon::parse($dateTo)->endOfDay();

        // Si el rango está invertido, corregirlo
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
            [$dateFrom, $dateTo]   = [$dateTo, $dateFrom];
        }

        $stats = [
            'total_orders'    => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_orders'  => Order::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_products'  => Product::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'confirmed_orders' => Order::where('status', 'confirmed')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'preparing_orders' => Order::where('status', 'preparing')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'ready_orders'    => Order::where('status', 'ready')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'delivered_orders' => Order::where('status', 'delivered')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue'   => Order::whereIn('status', ['confirmed', 'preparing', 'ready', 'delivered'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount'),
        ];

        $chartData = $this->getChartData($startDate, $endDate);

        $recentOrders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'chartData', 'dateFrom', 'dateTo'));
    }

    /**
     * Obtener datos para los gráficos según rango de fechas personalizado.
     * - <= 60 días  → agrupado por día
     * - <= 365 días → agrupado por semana
     * - > 365 días  → agrupado por mes
     */
    private function getChartData(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        $diffDays = (int) $startDate->diffInDays($endDate);

        $ordersByDay = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')->orderBy('date')->get();

        $revenueByDay = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivered'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')->orderBy('date')->get();

        $days = $orderCounts = $revenueData = [];

        if ($diffDays <= 60) {
            // Diario
            $cur = $startDate->copy()->startOfDay();
            while ($cur <= $endDate) {
                $date = $cur->format('Y-m-d');
                $days[]        = $cur->format('d/m');
                $orderCounts[] = $ordersByDay->where('date', $date)->first()->count ?? 0;
                $revenueData[] = $revenueByDay->where('date', $date)->first()->total ?? 0;
                $cur->addDay();
            }
        } elseif ($diffDays <= 365) {
            // Semanal
            $cur = $startDate->copy()->startOfWeek();
            while ($cur <= $endDate) {
                $weekEnd   = $cur->copy()->endOfWeek();
                $weekDates = [];
                $tmp = $cur->copy();
                while ($tmp <= $weekEnd && $tmp <= $endDate) {
                    $weekDates[] = $tmp->format('Y-m-d');
                    $tmp->addDay();
                }
                $days[]        = $cur->format('d/m');
                $orderCounts[] = $ordersByDay->whereIn('date', $weekDates)->sum('count');
                $revenueData[] = $revenueByDay->whereIn('date', $weekDates)->sum('total');
                $cur->addWeek();
            }
        } else {
            // Mensual
            $cur = $startDate->copy()->startOfMonth();
            while ($cur <= $endDate) {
                $y = $cur->year; $m = $cur->month;
                $days[]        = $cur->format('m/Y');
                $orderCounts[] = (int) Order::selectRaw('COUNT(*) as count')
                    ->whereYear('created_at', $y)->whereMonth('created_at', $m)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->value('count');
                $revenueData[] = (float) Order::selectRaw('COALESCE(SUM(total_amount),0) as total')
                    ->whereIn('status', ['confirmed', 'preparing', 'ready', 'delivered'])
                    ->whereYear('created_at', $y)->whereMonth('created_at', $m)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->value('total');
                $cur->addMonth();
            }
        }

        return [
            'orders_by_day'  => ['labels' => $days, 'data' => $orderCounts],
            'revenue_by_day' => ['labels' => $days, 'data' => $revenueData],
        ];
    }

    /**
     * Gestión de pedidos
     */
    public function orders(Request $request)
    {
        $hoy = now()->format('Y-m-d');

        // Pedidos de hoy
        $queryHoy = Order::with(['user', 'items.product', 'address', 'coupon', 'reviews'])
            ->whereDate('created_at', $hoy);

        // Filtros para pedidos de hoy
        if ($request->status) {
            $queryHoy->where('status', $request->status);
        }

        $pedidosHoy = $queryHoy->orderBy('created_at', 'desc')->get();

        // Pedidos anteriores: SOLO los del día anterior, sin paginación (todos visibles)
        $ayer = now()->subDay()->format('Y-m-d');
        $queryHistorico = Order::with(['user', 'items.product', 'address', 'coupon', 'reviews'])
            ->whereDate('created_at', $ayer);

        // Filtros para pedidos históricos
        if ($request->status) {
            $queryHistorico->where('status', $request->status);
        }

        if ($request->date_from) {
            $queryHistorico->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $queryHistorico->whereDate('created_at', '<=', $request->date_to);
        }

        $pedidosHistorico = $queryHistorico->orderBy('created_at', 'desc')->get();

        $selectedDate = $request->get('selected_date');
        // Obtener microturnos del día seleccionado (o de hoy por defecto) para los selects
        $dateForMicroturnos = $selectedDate ?: $hoy;
        $microturnosHoy = \App\Models\DynamicMicroturno::generarParaFecha($dateForMicroturnos);

        // Filtro de fecha para totales
        if ($selectedDate) {
            $pedidosFecha = Order::with(['user', 'items.product', 'address', 'coupon', 'reviews'])
                ->whereDate('created_at', $selectedDate)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $pedidosFecha = $pedidosHoy;
        }

        return view('admin.orders', compact('pedidosHoy', 'pedidosHistorico', 'microturnosHoy', 'selectedDate', 'pedidosFecha'));
    }

    /**
     * Detalles de un pedido
     */
    public function orderDetails($id)
    {
        $order = Order::with(['user', 'items.product', 'address', 'coupon'])
            ->findOrFail($id);

        return view('admin.order-details', compact('order'));
    }

    public function printOrder($id)
    {
        $order = Order::with(['user', 'items.product', 'address', 'coupon'])->findOrFail($id);

        // Obtener el horario del microturno
        $microturnoTimeRange = null;
        $microturnos = collect();
        // Intento 1: usar sort_order guardado
        if ($order->microturno_sort_order) {
            $fecha = $order->created_at->format('Y-m-d');
            $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
            $microturno = $microturnos->firstWhere('sort_order', $order->microturno_sort_order);
            if ($microturno) {
                $microturnoTimeRange = $microturno->formatted_time;
            }
        }
        // Intento 2: si no se encontró (o no había sort_order), calcular dinámicamente
        if (!$microturnoTimeRange) {
            $din = $order->microturno; // accessor en modelo Order
            if ($din) {
                // Usar accessor del modelo DynamicMicroturno
                if (method_exists($din, 'getFormattedTimeAttribute')) {
                    $microturnoTimeRange = $din->getFormattedTimeAttribute();
                } elseif (property_exists($din, 'formatted_time')) {
                    $microturnoTimeRange = $din->formatted_time;
                }
            }
        }

        // Debug: Log para verificar microturno
        \Log::info('DEBUG Ticket Print - Microturno', [
            'order_id' => $order->id,
            'microturno_sort_order' => $order->microturno_sort_order,
            'fecha' => $order->created_at->format('Y-m-d'),
            'microturnoTimeRange' => $microturnoTimeRange,
            'microturnos_count' => $microturnos ? $microturnos->count() : 0
        ]);

        // Debug: Log completo del pedido para auditoría del ticket
        try {
            \Log::info('DEBUG Ticket Print - Order payload', [
                'order' => $order->toArray(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('DEBUG Ticket Print - Error serializando order a array', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return view('admin.order-print', compact('order', 'microturnoTimeRange'));
    }

    /**
     * Actualizar detalles editables del pedido desde el modal (admin)
     */
    public function updateOrderDetails(Request $request, $id)
    {
        try {
            $order = Order::with('address')->findOrFail($id);

            $request->validate([
                'payment_method' => 'nullable|in:cash,card,transfer',
                'payment_status' => 'nullable|in:paid,pending,failed',
                'status' => 'nullable|in:pending,confirmed,preparing,delivered',
                'notes' => 'nullable|string',
                'contact_name' => 'nullable|string|max:255',
                'contact_phone' => 'nullable|string|max:255',
                'address.street' => 'nullable|string|max:255',
                'address.number' => 'nullable|string|max:50',
                'address.reference' => 'nullable|string|max:500',
            ]);

            $updateData = [];
            $changes = []; // Para registrar cambios
            
            foreach (['payment_method','payment_status','status','notes','contact_name','contact_phone'] as $field) {
                if ($request->has($field)) {
                    $newValue = $request->input($field);
                    $oldValue = $order->getAttribute($field);
                    
                    if ($oldValue !== $newValue) {
                        $updateData[$field] = $newValue;
                        $changes[] = [
                            'field' => $field,
                            'old_value' => $oldValue,
                            'new_value' => $newValue
                        ];
                    }
                }
            }

            if (!empty($updateData)) {
                $order->update($updateData);
                
                // Registrar cambios en el log de auditoría
                foreach ($changes as $change) {
                    $order->logChange(
                        $change['field'],
                        $change['old_value'],
                        $change['new_value'],
                        'admin'
                    );
                }
            }

            // Address nested updates
            if ($order->address && $request->has('address')) {
                $addr = $request->input('address');
                $addrUpdate = [];
                $addressChanges = [];
                
                foreach (['street','number','reference'] as $f) {
                    if (array_key_exists($f, $addr)) {
                        $newValue = $addr[$f];
                        $oldValue = $order->address->getAttribute($f);
                        
                        if ($oldValue !== $newValue) {
                            $addrUpdate[$f] = $newValue;
                            $addressChanges[] = [
                                'field' => "address.{$f}",
                                'old_value' => $oldValue,
                                'new_value' => $newValue
                            ];
                        }
                    }
                }
                
                if (!empty($addrUpdate)) {
                    $order->address->update($addrUpdate);
                    
                    // Registrar cambios de dirección en el log de auditoría
                    foreach ($addressChanges as $change) {
                        $order->logChange(
                            $change['field'],
                            $change['old_value'],
                            $change['new_value'],
                            'admin'
                        );
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pedido actualizado correctamente',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating order details', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar microturno de un pedido
     */
    public function updateOrderMicroturno(Request $request, $id)
    {
        $request->validate([
            'microturno_sort_order' => 'required|string',
        ]);

        $order = Order::findOrFail($id);

        // Permitir cambio en pedidos pendientes y confirmados
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'error' => 'Solo se puede cambiar el microturno de pedidos en estado pendiente o confirmado.'
            ], 400);
        }

        $order->update([
            'microturno_sort_order' => $request->microturno_sort_order
        ]);

        // Log para debugging
        \Log::info('Microturno actualizado', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'microturno_sort_order' => $request->microturno_sort_order,
            'status' => $order->status
        ]);

        // Obtener el nuevo microturno para devolver el horario formateado
        $hoy = now()->format('Y-m-d');
        $microturnosHoy = \App\Models\DynamicMicroturno::generarParaFecha($hoy);
        $microturnoAsignado = $microturnosHoy->firstWhere('sort_order', $request->microturno_sort_order);

        return response()->json([
            'success' => true,
            'message' => 'Microturno actualizado correctamente.',
            'formatted_time' => $microturnoAsignado ? $microturnoAsignado->getFormattedTimeAttribute() : 'Sin horario'
        ]);
    }

    /**
     * Actualizar estado del pedido
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,on_the_way,out_for_delivery,delivered,cancelled',
            'reason' => 'nullable|string|max:500'
        ]);

        $order = Order::with('items.product.category')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;
        $forceExceedCapacity = $request->input('force_exceed_capacity', false);

        // Validar capacidad del microturno si se está cambiando a un estado activo
        \Log::info('Debug updateOrderStatus - Validando cambio de estado', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'is_new_status_active' => in_array($newStatus, Order::ACTIVE_STATUSES),
            'is_old_status_active' => in_array($oldStatus, Order::ACTIVE_STATUSES),
            'should_validate' => in_array($newStatus, Order::ACTIVE_STATUSES) && !in_array($oldStatus, Order::ACTIVE_STATUSES)
        ]);

        if (in_array($newStatus, Order::ACTIVE_STATUSES) && !in_array($oldStatus, Order::ACTIVE_STATUSES)) {
            // El pedido está pasando de un estado inactivo a uno activo
            $microturno = $order->microturno;  // Usar el accessor dinámico

            \Log::info('Debug updateOrderStatus - Microturno encontrado', [
                'microturno_exists' => $microturno !== null,
                'microturno_sort_order' => $order->microturno_sort_order
            ]);

            if ($microturno) {
                // Calcular productos actuales del pedido que se va a activar
                $pedidoHamburguesas = 0;
                $pedidoAcompañamientos = 0;

                foreach ($order->items as $item) {
                    if (strtolower($item->product->category->name) === 'hamburguesas') {
                        $pedidoHamburguesas += $item->quantity;
                    } elseif (strtolower($item->product->category->name) === 'acompañamientos') {
                        $pedidoAcompañamientos += $item->quantity;
                    }
                }

                // Obtener productos ya activos en el microturno (excluyendo este pedido)
                $fecha = $order->created_at->format('Y-m-d');

                // Calcular productos activos manualmente excluyendo este pedido
                $ordersActivos = Order::whereDate('created_at', $fecha)
                    ->where('microturno_sort_order', $order->microturno_sort_order)
                    ->whereIn('status', Order::ACTIVE_STATUSES)
                    ->where('id', '!=', $order->id)
                    ->with('items.product.category')
                    ->get();

                $hamburguesasSinPedido = 0;
                $acompañamientosSinPedido = 0;

                foreach ($ordersActivos as $orderActivo) {
                    foreach ($orderActivo->items as $item) {
                        if (strtolower($item->product->category->name) === 'hamburguesas') {
                            $hamburguesasSinPedido += $item->quantity;
                        } elseif (strtolower($item->product->category->name) === 'acompañamientos') {
                            $acompañamientosSinPedido += $item->quantity;
                        }
                    }
                }

                // Obtener capacidades máximas
                $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($microturno->getDayOfWeek());

                // Verificar si hay espacio para este pedido
                $hamburguesasDespues = $hamburguesasSinPedido + $pedidoHamburguesas;
                $acompañamientosDespues = $acompañamientosSinPedido + $pedidoAcompañamientos;

                \Log::info('Debug updateOrderStatus - Cálculo de capacidad', [
                    'pedido_hamburguesas' => $pedidoHamburguesas,
                    'pedido_acompañamientos' => $pedidoAcompañamientos,
                    'hamburguesas_sin_pedido' => $hamburguesasSinPedido,
                    'acompañamientos_sin_pedido' => $acompañamientosSinPedido,
                    'hamburguesas_despues' => $hamburguesasDespues,
                    'acompañamientos_despues' => $acompañamientosDespues,
                    'max_hamburguesas' => $config->max_hamburguesas,
                    'max_acompañamientos' => $config->max_acompañamientos,
                    'excede_hamburguesas' => $hamburguesasDespues > $config->max_hamburguesas,
                    'excede_acompañamientos' => $acompañamientosDespues > $config->max_acompañamientos,
                    'orders_activos_count' => $ordersActivos->count(),
                    'orders_activos_ids' => $ordersActivos->pluck('id')->toArray()
                ]);

                // Verificar si se está forzando la confirmación
                
                if ($hamburguesasDespues > $config->max_hamburguesas ||
                        $acompañamientosDespues > $config->max_acompañamientos) {
                    
                    // Si se está forzando la confirmación, permitir continuar
                    if ($forceExceedCapacity) {
                        \Log::info('Confirmación forzada - excediendo capacidad', [
                            'order_id' => $order->id,
                            'hamburguesas_despues' => $hamburguesasDespues,
                            'max_hamburguesas' => $config->max_hamburguesas,
                            'acompañamientos_despues' => $acompañamientosDespues,
                            'max_acompañamientos' => $config->max_acompañamientos
                        ]);
                    } else {
                        // Crear mensaje detallado explicando el problema
                        $mensajeError = "No se puede confirmar este pedido porque excede la capacidad del microturno:\n\n";

                        // Mostrar qué aporta este pedido
                        $mensajeError .= "Este pedido aporta:\n";
                        if ($pedidoHamburguesas > 0) {
                            $mensajeError .= '• Hamburguesas: ' . $pedidoHamburguesas . "\n";
                        }
                        if ($pedidoAcompañamientos > 0) {
                            $mensajeError .= '• Acompañamientos: ' . $pedidoAcompañamientos . "\n";
                        }

                        $mensajeError .= "\nEstado actual del microturno:\n";

                        // Mostrar estado actual y si excede
                        if ($hamburguesasDespues > $config->max_hamburguesas) {
                            $mensajeError .= '• Hamburguesas: ' . $hamburguesasSinPedido . '/' . $config->max_hamburguesas . ' → ' . $hamburguesasDespues . " (EXCEDE)\n";
                        } else {
                            $mensajeError .= '• Hamburguesas: ' . $hamburguesasSinPedido . '/' . $config->max_hamburguesas . "\n";
                        }

                        if ($acompañamientosDespues > $config->max_acompañamientos) {
                            $mensajeError .= '• Acompañamientos: ' . $acompañamientosSinPedido . '/' . $config->max_acompañamientos . ' → ' . $acompañamientosDespues . " (EXCEDE)\n";
                        } else {
                            $mensajeError .= '• Acompañamientos: ' . $acompañamientosSinPedido . '/' . $config->max_acompañamientos . "\n";
                        }

                        $mensajeError .= "\nSolución: Libera espacio cambiando otros pedidos a 'Entregado' o 'Cancelado' antes de confirmar este pedido.";

                        return response()->json([
                            'success' => false,
                            'error' => $mensajeError,
                            'microturno_lleno' => true,
                            'capacidad_maxima' => $config->max_hamburguesas . ' hamburguesas, ' . $config->max_acompañamientos . ' acompañamientos',
                            'pedidos_activos' => $hamburguesasDespues . ' hamburguesas, ' . $acompañamientosDespues . ' acompañamientos',
                            'detalles' => [
                                'hamburguesas_actuales' => $hamburguesasSinPedido,
                                'hamburguesas_pedido' => $pedidoHamburguesas,
                                'hamburguesas_total_despues' => $hamburguesasDespues,
                                'hamburguesas_maximo' => $config->max_hamburguesas,
                                'acompañamientos_actuales' => $acompañamientosSinPedido,
                                'acompañamientos_pedido' => $pedidoAcompañamientos,
                                'acompañamientos_total_despues' => $acompañamientosDespues,
                                'acompañamientos_maximo' => $config->max_acompañamientos
                            ]
                        ], 400);
                    }
                }
            }
        }

        $update = ['status' => $newStatus];

        // Preservar el microturno_sort_order existente a menos que se proporcione uno nuevo
        if ($request->has('microturno_sort_order')) {
            // Si se proporciona un microturno específico, usarlo
            $update['microturno_sort_order'] = $request->input('microturno_sort_order');
        } else {
            // Si no se proporciona, preservar el existente (no sobrescribir)
            // El microturno_sort_order existente se mantiene automáticamente
        }

        if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
            $update['confirmed_at'] = now();
        }

        if ($newStatus === 'preparing' && $oldStatus !== 'preparing') {
            $update['preparing_at'] = now();
        }

        if ($newStatus === 'ready' && $oldStatus !== 'ready') {
            $update['ready_at'] = now();
        }

        if ($newStatus === 'on_the_way' && $oldStatus !== 'on_the_way') {
            $update['out_for_delivery_at'] = now();
        }

        // Solo actualizar delivered_at si el campo existe y el estado es delivered
        if ($newStatus === 'delivered') {
            $update['delivered_at'] = now();
        }

        // Agregar motivo del cambio a las notas si se proporcionó
        if ($request->has('reason') && !empty($request->reason)) {
            $timestamp = now()->format('d/m/Y H:i');
            $adminUser = auth()->user();
            $adminName = $adminUser ? $adminUser->name : 'Admin';
            
            $reasonText = "\n[{$timestamp}] Cambio de estado: {$oldStatus} → {$newStatus}";
            $reasonText .= "\nMotivo: {$request->reason}";
            $reasonText .= "\nAdmin: {$adminName}";
            
            // Agregar a las notas existentes
            $existingNotes = $order->notes ?: '';
            $update['notes'] = $existingNotes . $reasonText;
        }

        $order->update($update);

        // Enviar notificaciones al usuario según el nuevo estado
        $orderFresh = $order->fresh();
        if ($orderFresh->user && $orderFresh->user->email !== 'guest@tecocina.local') {
            match (true) {
                $newStatus === 'confirmed' && $oldStatus !== 'confirmed'
                    => \App\Services\NotificationService::notifyOrderConfirmed($orderFresh->user, $orderFresh),
                $newStatus === 'preparing' && $oldStatus !== 'preparing'
                    => \App\Services\NotificationService::notifyOrderPreparing($orderFresh->user, $orderFresh),
                $newStatus === 'ready' && $oldStatus !== 'ready'
                    => \App\Services\NotificationService::notifyOrderReady($orderFresh->user, $orderFresh),
                $newStatus === 'on_the_way' && $oldStatus !== 'on_the_way'
                    => \App\Services\NotificationService::notifyOrderOnTheWay($orderFresh->user, $orderFresh),
                $newStatus === 'delivered' && $oldStatus !== 'delivered'
                    => \App\Services\NotificationService::notifyOrderDelivered($orderFresh->user, $orderFresh),
                default => null,
            };

            // Enviar solicitud de reseña Google cuando se marca como entregado
            if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                try {
                    (new \App\Services\ReviewRequestService)->sendReviewRequest($orderFresh);
                } catch (\Exception $e) {
                    \Log::error('Error sending review request from admin: ' . $e->getMessage());
                }
            }
        }

        // Revertir figuritas si el pedido se cancela.
        // strategy: 'revert' (default), 'revert_and_cancel_redemption', 'keep'
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $strategy = $request->input('loyalty_strategy', 'revert');
            if (!in_array($strategy, ['revert', 'revert_and_cancel_redemption', 'keep'], true)) {
                $strategy = 'revert';
            }
            $this->loyaltyService->revokeFromOrder($order->fresh(), auth()->id(), $strategy);
        }

        // Log para debugging
        \Log::info('Estado actualizado', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'microturno_sort_order_before' => $order->microturno_sort_order,
            'microturno_sort_order_after' => $order->fresh()->microturno_sort_order,
            'update_data' => $update
        ]);

        // Refrescar y calcular el timestamp de referencia para el contador según el nuevo estado
        $order->refresh();
        $fromTs = match ($newStatus) {
            'confirmed'  => $order->confirmed_at ?: $order->created_at,
            'preparing'  => $order->preparing_at ?: $order->confirmed_at ?: $order->created_at,
            'ready'      => $order->ready_at ?: $order->preparing_at ?: $order->created_at,
            'on_the_way' => $order->out_for_delivery_at ?: $order->ready_at ?: $order->created_at,
            'delivered'  => $order->delivered_at ?: $order->out_for_delivery_at ?: $order->ready_at ?: $order->created_at,
            default => $order->created_at,
        };

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'from_ts' => optional($fromTs)->toIso8601String(),
            'status' => $newStatus,
        ]);
    }

    /**
     * Marcar pedido como "review solicitado" desde el panel admin
     */
    public function markReviewPrompted(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['review_prompt_sent_at' => now()]);
        return response()->json(['success' => true]);
    }

    /**
     * Obtener microturnos disponibles para selección manual
     */
    public function getMicroturnosDisponibles()
    {
        try {
            $fecha = now()->format('Y-m-d');
            $dayOfWeek = strtolower(now()->format('l'));
            
            \Log::info('Obteniendo microturnos para fecha: ' . $fecha . ', día: ' . $dayOfWeek);
            
            // Verificar todas las configuraciones disponibles
            $allConfigs = \App\Models\WeeklyTurnoConfig::all();
            \Log::info('Todas las configuraciones: ' . $allConfigs->toJson());
            
            // Verificar si hay configuración para el día actual
            $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($dayOfWeek);
            
            if (!$config) {
                \Log::warning('No hay configuración para el día: ' . $dayOfWeek);
                
                // Intentar crear configuraciones por defecto si no existen
                if ($allConfigs->isEmpty()) {
                    \Log::info('No hay configuraciones, creando por defecto...');
                    \App\Models\WeeklyTurnoConfig::createDefaultConfigs();
                    $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($dayOfWeek);
                }
                
                if (!$config) {
                    // Crear microturnos básicos como fallback
                    \Log::info('Creando microturnos básicos como fallback...');
                    $microturnosData = $this->createBasicMicroturnos();
                    return response()->json([
                        'success' => true,
                        'microturnos' => $microturnosData,
                        'debug' => [
                            'fecha' => $fecha,
                            'day_of_week' => $dayOfWeek,
                            'fallback_mode' => true,
                            'all_configs' => $allConfigs->toArray()
                        ]
                    ]);
                }
            }
            
            if (!$config->is_enabled) {
                \Log::warning('Configuración deshabilitada para el día: ' . $dayOfWeek);
                return response()->json([
                    'success' => false,
                    'error' => 'Los turnos están deshabilitados para el día actual'
                ], 400);
            }
            
            $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
            
            \Log::info('Microturnos generados: ' . $microturnos->count());
            
            $microturnosData = $microturnos->map(function ($microturno) {
                return [
                    'sort_order' => $microturno->getSortOrderAttribute(),
                    'formatted_time' => $microturno->getFormattedTimeAttribute(),
                    'start_time' => $microturno->start_time,
                    'end_time' => $microturno->end_time,
                ];
            });

            return response()->json([
                'success' => true,
                'microturnos' => $microturnosData,
                'debug' => [
                    'fecha' => $fecha,
                    'day_of_week' => $dayOfWeek,
                    'config_enabled' => $config->is_enabled,
                    'total_microturnos' => $microturnos->count()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener microturnos disponibles: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener microturnos disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear microturnos básicos como fallback
     */
    private function createBasicMicroturnos()
    {
        $microturnos = [];
        $horaInicio = 18; // 6 PM
        $horaFin = 23; // 11 PM
        $duracion = 30; // 30 minutos por microturno
        
        $sortOrder = 1;
        for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
            for ($minuto = 0; $minuto < 60; $minuto += $duracion) {
                $horaInicioStr = sprintf('%02d:%02d:00', $hora, $minuto);
                $horaFinStr = sprintf('%02d:%02d:00', $hora, $minuto + $duracion);
                
                // Ajustar si se pasa de la hora
                if ($minuto + $duracion >= 60) {
                    $horaFinStr = sprintf('%02d:00:00', $hora + 1);
                }
                
                $microturnos[] = [
                    'sort_order' => $sortOrder,
                    'formatted_time' => substr($horaInicioStr, 0, 5) . ' - ' . substr($horaFinStr, 0, 5),
                    'start_time' => $horaInicioStr,
                    'end_time' => $horaFinStr,
                ];
                
                $sortOrder++;
            }
        }
        
        return $microturnos;
    }

    /**
     * Debug: Obtener microturnos sin autenticación
     */
    public function debugMicroturnos()
    {
        try {
            $microturnosData = $this->createBasicMicroturnos();
            
            return response()->json([
                'success' => true,
                'microturnos' => $microturnosData,
                'debug' => [
                    'fecha' => now()->format('Y-m-d'),
                    'day_of_week' => strtolower(now()->format('l')),
                    'fallback_mode' => true,
                    'total_microturnos' => count($microturnosData)
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en debug microturnos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un pedido y sus ítems
     */
    public function destroyOrder(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        // Revertir figuritas antes de eliminar el pedido (si las otorgó).
        $strategy = $request->input('loyalty_strategy', 'revert');
        if (!in_array($strategy, ['revert', 'revert_and_cancel_redemption', 'keep'], true)) {
            $strategy = 'revert';
        }
        $this->loyaltyService->revokeFromOrder($order, auth()->id(), $strategy);

        // Los ítems se eliminan por cascade si la FK está con onDelete('cascade'). Por si acaso:
        foreach ($order->items as $item) {
            $item->delete();
        }
        $order->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pedido eliminado']);
        }
        return redirect()->route('admin.orders')->with('success', 'Pedido eliminado');
    }

    /**
     * Endpoint para que el front consulte el impacto de un pedido en figuritas
     * antes de cancelarlo o eliminarlo.
     */
    public function getOrderLoyaltyImpact($id)
    {
        $order = Order::findOrFail($id);
        $impact = $this->loyaltyService->getOrderLoyaltyImpact($order);
        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'impact' => $impact,
        ]);
    }

    /**
     * Repartir figuritas manualmente a un pedido entregado.
     * Solo funciona si el pedido está entregado, tiene usuario logueado
     * y aún no se le otorgaron figuritas.
     */
    public function awardOrderLoyalty(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->status !== Order::STATUS_DELIVERED) {
            return response()->json(['success' => false, 'message' => 'Solo se pueden repartir figuritas en pedidos entregados.'], 422);
        }

        if (!$order->user_id) {
            return response()->json(['success' => false, 'message' => 'El pedido no pertenece a un usuario registrado.'], 422);
        }

        // Verificar que aún no se haya otorgado
        $alreadyAwarded = \App\Models\UserLoyaltyMovement::where('order_id', $order->id)
            ->where('reason', 'order_confirmed')
            ->exists();

        if ($alreadyAwarded) {
            return response()->json(['success' => false, 'message' => 'Las figuritas ya fueron repartidas para este pedido.'], 422);
        }

        $this->loyaltyService->awardFromConfirmedOrder($order, auth()->id(), true);

        // Registrar en change_log del pedido
        $log = $order->change_log ?: [];
        $log[] = [
            'action'        => 'loyalty_awarded',
            'admin_id'      => auth()->id(),
            'admin_name'    => auth()->user()?->name ?? 'Admin',
            'timestamp'     => now()->toIso8601String(),
            'stickers_count'=> (int) max(0, $order->items()
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.category_id', 1)
                ->sum('order_items.quantity')),
            'message'       => 'Figuritas repartidas al usuario ' . ($order->user?->name ?? 'ID:' . $order->user_id),
        ];
        $order->update(['change_log' => $log]);

        return response()->json(['success' => true, 'message' => 'Figuritas repartidas correctamente.']);
    }

    /**
     * Eliminar múltiples pedidos
     */
    public function bulkDeleteOrders(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|integer|exists:orders,id'
        ]);

        try {
            $orderIds = $request->input('order_ids');
            $strategy = $request->input('loyalty_strategy', 'revert');
            if (!in_array($strategy, ['revert', 'revert_and_cancel_redemption', 'keep'], true)) {
                $strategy = 'revert';
            }
            $deletedCount = 0;

            foreach ($orderIds as $orderId) {
                $order = Order::with('items')->find($orderId);
                if ($order) {
                    // Revertir figuritas antes de borrar
                    $this->loyaltyService->revokeFromOrder($order, auth()->id(), $strategy);
                    // Eliminar ítems del pedido
                    foreach ($order->items as $item) {
                        $item->delete();
                    }
                    // Eliminar el pedido
                    $order->delete();
                    $deletedCount++;
                }
            }

            \Log::info('Bulk delete orders', [
                'requested_ids' => $orderIds,
                'deleted_count' => $deletedCount,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} pedidos correctamente",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in bulk delete orders', [
                'error' => $e->getMessage(),
                'order_ids' => $request->input('order_ids'),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar los pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar categoría por defecto del catálogo
     */
    public function setDefaultCatalogCategory(Request $request)
    {
        $request->validate([
            'default_view' => 'required|string|in:grid,list,carousel',
        ]);
        \App\Models\BusinessSetting::set('default_catalog_view', $request->input('default_view'));
        return response()->json(['success' => true, 'view' => $request->input('default_view')]);
    }

    /**
     * Gestión de productos
     */
    public function products(Request $request)
    {
        $categories = Category::orderBy('sort_order')->get();

        // Filtrado por categoría (slug). 'all' o vacío = todas.
        $selectedSlug = $request->query('category');
        $selectedCategory = null;
        if ($selectedSlug && $selectedSlug !== 'all') {
            $selectedCategory = $categories->firstWhere('slug', $selectedSlug);
        }

        $query = Product::with(['category', 'defaultSauce'])
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($selectedCategory) {
            $query->where('category_id', $selectedCategory->id);
        }

        $products = $query->paginate(20)->withQueryString();

        // Conteo por categoría para badges en las pestañas
        $countsByCategory = Product::selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');
        $totalProducts = Product::count();

        // Obtener todos los aderezos disponibles para el select
        $availableSauces = ProductConfiguration::where('name', 'Aderezos')
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('value')
            ->get();

        $defaultCatalogView = \App\Models\BusinessSetting::get('default_catalog_view', 'grid');

        return view('admin.products', compact(
            'products',
            'categories',
            'availableSauces',
            'selectedCategory',
            'selectedSlug',
            'countsByCategory',
            'totalProducts',
            'defaultCatalogView'
        ));
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_available' => 'nullable|in:on,1,true,0,false',
            'default_sauce_configuration_id' => 'nullable|exists:product_configurations,id',
        ]);

        $data['is_available'] = $request->boolean('is_available');

        // El aderezo de carta sólo aplica a la categoría Hamburguesas.
        $category = Category::find($data['category_id']);
        $isHamburguesa = $category && $category->slug === 'hamburguesas';
        if ($isHamburguesa && $request->filled('default_sauce_configuration_id')) {
            $sauceConfig = ProductConfiguration::find($request->input('default_sauce_configuration_id'));
            if (!$sauceConfig || $sauceConfig->name !== 'Aderezos') {
                return redirect()->back()->withErrors(['default_sauce_configuration_id' => 'El aderezo seleccionado no es válido.']);
            }
            $data['default_sauce_configuration_id'] = $sauceConfig->id;
        } else {
            $data['default_sauce_configuration_id'] = null;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public_images');
            $data['image'] = $path;  // guardar ruta relativa en disk public_images
        } else {
            unset($data['image']);
        }

        $product = Product::create($data);

        // Las configuraciones ahora son globales en ProductConfiguration
        // No se crean variantes/opciones específicas por producto

        return redirect()->route('admin.products')->with('success', 'Producto creado correctamente');
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_available' => 'nullable|in:on,1,true,0,false',
            'default_sauce_configuration_id' => 'nullable|exists:product_configurations,id',
        ]);

        $data['is_available'] = $request->boolean('is_available');

        // El aderezo de carta sólo aplica a la categoría Hamburguesas.
        $category = Category::find($data['category_id']);
        $isHamburguesa = $category && $category->slug === 'hamburguesas';
        if ($isHamburguesa && $request->filled('default_sauce_configuration_id')) {
            $sauceConfig = ProductConfiguration::find($request->input('default_sauce_configuration_id'));
            if (!$sauceConfig || $sauceConfig->name !== 'Aderezos') {
                return redirect()->back()->withErrors(['default_sauce_configuration_id' => 'El aderezo seleccionado no es válido.']);
            }
            $data['default_sauce_configuration_id'] = $sauceConfig->id;
        } else {
            $data['default_sauce_configuration_id'] = null;
        }

        if ($request->hasFile('image')) {
            // borrar imagen anterior
            if ($product->image) {
                Storage::disk('public_images')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public_images');
            $data['image'] = $path;
        } else {
            unset($data['image']);
        }

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Producto actualizado');
    }

    public function destroyProduct($id)
    {
        $product = Product::findOrFail($id);
        // SET NULL en order_items antes de borrar, para conservar la información del pedido
        DB::table('order_items')->where('product_id', $product->id)->update(['product_id' => null]);
        if ($product->image) {
            Storage::disk('public_images')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Producto eliminado');
    }

    /**
     * Gestión de categorías
     */
    public function categories()
    {
        $categories = Category::withCount('products')->paginate(20);

        return view('admin.categories', compact('categories'));
    }

    /**
     * Configuración del negocio
     */
    public function settings()
    {
        $settings = BusinessSetting::all()->keyBy('key');

        // Log para debug: ver qué se carga desde la base de datos
        \Log::info('AdminController::settings - Cargando settings desde BD', [
            'total_settings' => $settings->count(),
            'payment_methods_exists' => isset($settings['payment_methods']),
        ]);

        // Log específico para payment_methods
        if (isset($settings['payment_methods'])) {
            $pmSetting = $settings['payment_methods'];
            \Log::info('AdminController::settings - payment_methods desde BD', [
                'key' => $pmSetting->key,
                'value_raw' => $pmSetting->value,
                'type' => $pmSetting->type,
                'value_decoded' => BusinessSetting::castValue($pmSetting->value, $pmSetting->type),
                'is_array' => is_array(BusinessSetting::castValue($pmSetting->value, $pmSetting->type)),
            ]);
        } else {
            \Log::warning('AdminController::settings - payment_methods NO existe en BD');
        }

        // Migrar logo antiguo si existe y está en ruta antigua
        $logoUrl = optional($settings['brand_logo_url'] ?? null)->value;
        if ($logoUrl && (str_starts_with($logoUrl, '/storage/branding/') || str_starts_with($logoUrl, '/images/'))) {
            try {
                $oldFile = null;
                $extension = null;
                
                // Si es ruta antigua de storage
                if (str_starts_with($logoUrl, '/storage/branding/')) {
                    $oldPath = str_replace('/storage/', 'public/', $logoUrl);
                    $oldFile = storage_path('app/' . $oldPath);
                }
                // Si es ruta de images
                elseif (str_starts_with($logoUrl, '/images/')) {
                    $oldFile = public_path($logoUrl);
                }
                
                if ($oldFile && file_exists($oldFile)) {
                    $extension = pathinfo($oldFile, PATHINFO_EXTENSION);
                    $newFilename = 'logo.' . $extension;
                    $brandingDir = public_path('branding');
                    
                    // Asegurar que el directorio branding existe
                    if (!file_exists($brandingDir)) {
                        mkdir($brandingDir, 0755, true);
                    }
                    
                    $newPath = $brandingDir . '/' . $newFilename;
                    copy($oldFile, $newPath);
                    
                    // Actualizar la URL en la base de datos
                    $newUrl = '/branding/' . $newFilename;
                    BusinessSetting::set('brand_logo_url', $newUrl);
                    
                    // Actualizar el valor en $settings para que se muestre correctamente
                    $settings['brand_logo_url']->value = $newUrl;
                }
            } catch (\Throwable $e) {
                \Log::error('Error migrating old logo', ['error' => $e->getMessage()]);
            }
        }

        // Días habilitados para pedidos (WeeklyTurnoConfig) para la primera card de Configuración (siempre los 7 días)
        $weeklyConfigs = \App\Models\WeeklyTurnoConfig::getConfigsForSettings();

        // Obtener configuraciones agrupadas por tipo
        $configurations = ProductConfiguration::ordered()->get()->groupBy('name');

        // Mantener compatibilidad con vista existente
        $sauces = collect();
        $extras = collect();

        // Mapear configuraciones a formato de vista existente
        if ($configurations->has('Aderezos')) {
            $sauces = $configurations['Aderezos']->map(function ($config) {
                return (object) [
                    'id' => $config->id,
                    'name' => $config->value,
                    'type' => 'sauce',
                    'is_available' => $config->is_available,
                    'sort_order' => $config->sort_order,
                ];
            });
        }

        if ($configurations->has('Dips')) {
            $dips = $configurations['Dips']->map(function ($config) {
                return (object) [
                    'id' => $config->id,
                    'name' => $config->value,
                    'type' => 'dip',
                    'is_available' => $config->is_available,
                    'sort_order' => $config->sort_order,
                ];
            });
            $sauces = $sauces->merge($dips);
        }

        if ($configurations->has('Extras')) {
            $extras = $configurations['Extras']->map(function ($config) {
                return (object) [
                    'id' => $config->id,
                    'name' => $config->value,
                    'price' => $config->price_modifier,
                    'is_available' => $config->is_available,
                    'sort_order' => $config->sort_order,
                ];
            });
        }

        return view('admin.settings', compact('settings', 'configurations', 'sauces', 'extras', 'weeklyConfigs'));
    }

    /**
     * Actualizar configuración
     */
    public function updateSettings(Request $request)
    {
        // Log para debug
        \Log::info('updateSettings called', [
            'request_data' => $request->all(),
            'user' => auth()->user() ? auth()->user()->id : 'not_authenticated',
            'has_brand_logo' => $request->hasFile('brand_logo'),
            'has_brand_logo_left' => $request->hasFile('brand_logo_left'),
            'brand_logo_info' => $request->hasFile('brand_logo') ? [
                'name' => $request->file('brand_logo')->getClientOriginalName(),
                'size' => $request->file('brand_logo')->getSize(),
                'mime' => $request->file('brand_logo')->getMimeType(),
                'is_valid' => $request->file('brand_logo')->isValid(),
            ] : null,
            'brand_logo_left_info' => $request->hasFile('brand_logo_left') ? [
                'name' => $request->file('brand_logo_left')->getClientOriginalName(),
                'size' => $request->file('brand_logo_left')->getSize(),
                'mime' => $request->file('brand_logo_left')->getMimeType(),
                'is_valid' => $request->file('brand_logo_left')->isValid(),
            ] : null,
        ]);

        try {
            $request->validate([
                'business_name' => 'required|string|max:255',
                'business_phone' => 'required|string|max:255',
                'business_email' => 'required|email|max:255',
                'business_address' => 'required|string|max:500',
                'footer_description' => 'nullable|string|max:500',
                'site_offline_title' => 'nullable|string|max:255',
                'site_offline_message' => 'nullable|string|max:500',
                'loyalty_offline_message' => 'nullable|string|max:500',
                'whatsapp_number' => 'required|string|max:255',
                'payment_methods' => 'array',
                'payment_methods.*' => 'in:cash,card,transfer',
                'cash_discount_percentage' => 'nullable|numeric|min:0|max:100',
                'aderezo_de_carta_descripcion' => 'nullable|string|max:255',
                // Branding (se valida solo si viene archivo tradicional; con FilePond se usa upload async)
                'brand_logo' => 'nullable|image|max:8192',
                'brand_logo_left' => 'nullable|image|max:8192',
                'brand_primary_color' => ['nullable','regex:/^#([0-9a-fA-F]{3}){1,2}$/'],
                'brand_accent_color' => ['nullable','regex:/^#([0-9a-fA-F]{3}){1,2}$/'],
                'medallion_types' => 'nullable|array',
                'medallion_types.*.name' => 'required|string|max:255',
                'medallion_types.*.is_default' => 'boolean',
                'medallion_types.*.enabled' => 'boolean',
                'order_days' => 'nullable|array',
                'order_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            ]);
            
            \Log::info('Validación pasada correctamente', [
                'has_brand_logo_after_validation' => $request->hasFile('brand_logo'),
                'has_brand_logo_left_after_validation' => $request->hasFile('brand_logo_left'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en updateSettings', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        // Procesar configuración básica
        $basicSettings = [
            'business_name', 'business_phone', 'business_email', 'business_address',
            'footer_description', 'site_offline_title', 'site_offline_message', 'whatsapp_number', 'facebook_url', 'instagram_url', 'linkedin_url', 'whatsapp_url',
            'cash_discount_percentage', 'aderezo_de_carta_descripcion', 'loyalty_offline_message'
        ];

        foreach ($basicSettings as $key) {
            if ($request->has($key)) {
                BusinessSetting::set($key, $request->input($key));
            }
        }

        // Días habilitados para pedidos: actualizar is_enabled en WeeklyTurnoConfig
        $enabledDays = $request->input('order_days', []);
        $weeklyConfigsForUpdate = \App\Models\WeeklyTurnoConfig::getConfigsForSettings();
        foreach ($weeklyConfigsForUpdate as $config) {
            $config->update(['is_enabled' => in_array($config->day_of_week, $enabledDays)]);
        }

        // Marca: Colores
        if ($request->filled('brand_primary_color')) {
            BusinessSetting::set('brand_primary_color', $request->input('brand_primary_color'));
        }
        if ($request->filled('brand_accent_color')) {
            BusinessSetting::set('brand_accent_color', $request->input('brand_accent_color'));
        }

        // Array para acumular errores de logos
        $logoErrors = [];

        // Marca: Logo central
        if ($request->hasFile('brand_logo')) {
            try {
                \Log::info('Procesando brand_logo', [
                    'file_name' => $request->file('brand_logo')->getClientOriginalName(),
                    'file_size' => $request->file('brand_logo')->getSize(),
                    'file_mime' => $request->file('brand_logo')->getMimeType(),
                ]);
                
                // Eliminar logo anterior si existe
                $oldLogoUrl = BusinessSetting::get('brand_logo_url');
                if ($oldLogoUrl) {
                    // Si es ruta antigua (/storage/...), eliminar de storage
                    if (str_starts_with($oldLogoUrl, '/storage/')) {
                        $oldPath = str_replace('/storage/', 'public/', $oldLogoUrl);
                        if (\Storage::disk('public')->exists($oldPath)) {
                            \Storage::disk('public')->delete($oldPath);
                        }
                    }
                    // Si es ruta nueva (/branding/... o /images/...), eliminar de public
                    if (str_starts_with($oldLogoUrl, '/branding/') || str_starts_with($oldLogoUrl, '/images/')) {
                        $oldFilename = basename($oldLogoUrl);
                        $oldDir = str_starts_with($oldLogoUrl, '/branding/') ? 'branding' : 'images';
                        $oldFilePath = public_path($oldDir . '/' . $oldFilename);
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                }
                
                // Guardar directamente en public/branding/logo.png
                $file = $request->file('brand_logo');
                $brandingDir = public_path('branding');
                
                // Crear directorio si no existe
                if (!file_exists($brandingDir)) {
                    if (!mkdir($brandingDir, 0755, true)) {
                        throw new \Exception('No se pudo crear el directorio branding');
                    }
                    \Log::info('Directorio branding creado', ['path' => $brandingDir]);
                }
                
                // Verificar permisos de escritura
                if (!is_writable($brandingDir)) {
                    throw new \Exception('El directorio branding no tiene permisos de escritura');
                }
                
                $extension = $file->getClientOriginalExtension();
                $filename = 'logo.' . $extension;
                $fullPath = $brandingDir . '/' . $filename;
                
                \Log::info('Intentando mover archivo', [
                    'from' => $file->getRealPath(),
                    'to' => $fullPath,
                ]);
                
                if (!$file->move($brandingDir, $filename)) {
                    throw new \Exception('No se pudo mover el archivo al directorio destino');
                }
                
                // Verificar que el archivo se guardó correctamente
                if (!file_exists($fullPath)) {
                    throw new \Exception('El archivo no se guardó correctamente');
                }
                
                $url = '/branding/' . $filename;
                BusinessSetting::set('brand_logo_url', $url);
                
                \Log::info('Logo central guardado exitosamente', [
                    'url' => $url,
                    'file_size' => filesize($fullPath),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Error uploading brand logo', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $logoErrors['brand_logo'] = 'Error al subir el logo central: ' . $e->getMessage();
            }
        }

        // Marca: Logo izquierdo
        if ($request->hasFile('brand_logo_left')) {
            try {
                \Log::info('Procesando brand_logo_left', [
                    'file_name' => $request->file('brand_logo_left')->getClientOriginalName(),
                    'file_size' => $request->file('brand_logo_left')->getSize(),
                    'file_mime' => $request->file('brand_logo_left')->getMimeType(),
                ]);
                
                // Eliminar logo anterior si existe
                $oldLogoUrl = BusinessSetting::get('brand_logo_left_url');
                if ($oldLogoUrl) {
                    // Si es ruta antigua (/storage/...), eliminar de storage
                    if (str_starts_with($oldLogoUrl, '/storage/')) {
                        $oldPath = str_replace('/storage/', 'public/', $oldLogoUrl);
                        if (\Storage::disk('public')->exists($oldPath)) {
                            \Storage::disk('public')->delete($oldPath);
                        }
                    }
                    // Si es ruta nueva (/branding/... o /images/...), eliminar de public
                    if (str_starts_with($oldLogoUrl, '/branding/') || str_starts_with($oldLogoUrl, '/images/')) {
                        $oldFilename = basename($oldLogoUrl);
                        $oldDir = str_starts_with($oldLogoUrl, '/branding/') ? 'branding' : 'images';
                        $oldFilePath = public_path($oldDir . '/' . $oldFilename);
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                }
                
                // Guardar directamente en public/branding/logo_left.png
                $file = $request->file('brand_logo_left');
                $brandingDir = public_path('branding');
                
                // Crear directorio si no existe
                if (!file_exists($brandingDir)) {
                    if (!mkdir($brandingDir, 0755, true)) {
                        throw new \Exception('No se pudo crear el directorio branding');
                    }
                    \Log::info('Directorio branding creado', ['path' => $brandingDir]);
                }
                
                // Verificar permisos de escritura
                if (!is_writable($brandingDir)) {
                    throw new \Exception('El directorio branding no tiene permisos de escritura');
                }
                
                $extension = $file->getClientOriginalExtension();
                $filename = 'logo_left.' . $extension;
                $fullPath = $brandingDir . '/' . $filename;
                
                \Log::info('Intentando mover archivo', [
                    'from' => $file->getRealPath(),
                    'to' => $fullPath,
                ]);
                
                if (!$file->move($brandingDir, $filename)) {
                    throw new \Exception('No se pudo mover el archivo al directorio destino');
                }
                
                // Verificar que el archivo se guardó correctamente
                if (!file_exists($fullPath)) {
                    throw new \Exception('El archivo no se guardó correctamente');
                }
                
                $url = '/branding/' . $filename;
                BusinessSetting::set('brand_logo_left_url', $url);
                
                \Log::info('Logo izquierdo guardado exitosamente', [
                    'url' => $url,
                    'file_size' => filesize($fullPath),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Error uploading brand logo left', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $logoErrors['brand_logo_left'] = 'Error al subir el logo izquierdo: ' . $e->getMessage();
            }
        }

        // Si hay errores en los logos, retornar con los errores
        if (!empty($logoErrors)) {
            return redirect()->back()->withErrors($logoErrors);
        }

        // Procesar métodos de pago
        if ($request->has('payment_methods')) {
            BusinessSetting::set('payment_methods', $request->input('payment_methods'), 'json', 'Métodos de pago habilitados');
        }

        // Procesar configuración de saltar selección de turno (solo si viene en el request)
        // Esta configuración se maneja desde la pestaña de turnos, no desde settings
        // Por lo tanto, NO se debe actualizar aquí

        // Procesar tipos de medallón
        if ($request->has('medallion_types')) {
            $medallionTypes = $request->input('medallion_types');

            // Convertir is_default y enabled a boolean
            foreach ($medallionTypes as &$type) {
                $type['is_default'] = (bool) ($type['is_default'] ?? false);
                $type['enabled'] = (bool) ($type['enabled'] ?? true);
            }

            BusinessSetting::set('medallion_types', $medallionTypes, 'json', 'Tipos de medallón disponibles');

            // Actualizar ProductConfigurations correspondientes
            foreach ($medallionTypes as $type) {
                ProductConfiguration::where('name', 'Tipo de Medallón')
                    ->where('value', $type['name'])
                    ->update(['is_available' => $type['enabled']]);
            }
        }

        // Procesar estado de aderezos/dips
        if ($request->has('sauce_available')) {
            $sauceAvailability = $request->input('sauce_available', []);
            
            // Log para debugging
            \Log::info('Processing sauce availability', [
                'sauce_available' => $sauceAvailability,
                'count' => count($sauceAvailability)
            ]);
            
            // Solo actualizar items que están explícitamente en el request
            foreach ($sauceAvailability as $id => $value) {
                $isAvailable = (bool) $value;
                ProductConfiguration::where('id', $id)->update(['is_available' => $isAvailable]);
                
                \Log::info('Updated sauce availability', [
                    'id' => $id,
                    'is_available' => $isAvailable
                ]);
            }
            
            // NO deshabilitar automáticamente los que no están en el array
            // Solo actualizar los que explícitamente cambiaron
        }

        // Procesar estado de extras
        if ($request->has('extra_available')) {
            $extraAvailability = $request->input('extra_available', []);
            
            // Log para debugging
            \Log::info('Processing extra availability', [
                'extra_available' => $extraAvailability,
                'count' => count($extraAvailability)
            ]);
            
            // Solo actualizar items que están explícitamente en el request
            foreach ($extraAvailability as $id => $value) {
                $isAvailable = (bool) $value;
                ProductConfiguration::where('id', $id)->update(['is_available' => $isAvailable]);
                
                \Log::info('Updated extra availability', [
                    'id' => $id,
                    'is_available' => $isAvailable
                ]);
            }
            
            // NO deshabilitar automáticamente los que no están en el array
            // Solo actualizar los que explícitamente cambiaron
        }

        \Log::info('updateSettings completed successfully');

        return redirect()->back()->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Alternar sitio encendido/apagado (modo fuera de línea)
     */
    public function toggleSiteOffline(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean'
        ]);
        // enabled=true => sitio apagado para clientes
        $enabled = filter_var($request->enabled, FILTER_VALIDATE_BOOLEAN);
        BusinessSetting::set('site_offline', $enabled, 'boolean', 'Apagar/encender sitio para clientes');
        return response()->json(['success' => true, 'site_offline' => $enabled]);
    }

    /**
     * Alternar sistema de Mi Album encendido/apagado para clientes
     */
    public function toggleLoyaltyOffline(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        // enabled=true => Mi Album apagado para clientes
        $enabled = filter_var($request->enabled, FILTER_VALIDATE_BOOLEAN);
        BusinessSetting::set('loyalty_offline', $enabled, 'boolean', 'Apagar/encender sistema Mi Album para clientes');

        return response()->json([
            'success' => true,
            'loyalty_offline' => $enabled,
        ]);
    }

    /**
     * Subir logo de marca con progreso (usado por FilePond)
     */
    public function uploadBrandLogo(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:8192', // 8MB
        ]);

        try {
            // Eliminar logo anterior si existe
            $oldLogoUrl = BusinessSetting::get('brand_logo_url');
            if ($oldLogoUrl) {
                // Si es ruta antigua (/storage/...), eliminar de storage
                if (str_starts_with($oldLogoUrl, '/storage/')) {
                    $oldPath = str_replace('/storage/', 'public/', $oldLogoUrl);
                    if (\Storage::disk('public')->exists($oldPath)) {
                        \Storage::disk('public')->delete($oldPath);
                    }
                }
                // Si es ruta nueva (/branding/... o /images/...), eliminar de public
                if (str_starts_with($oldLogoUrl, '/branding/') || str_starts_with($oldLogoUrl, '/images/')) {
                    $oldFilename = basename($oldLogoUrl);
                    $oldDir = str_starts_with($oldLogoUrl, '/branding/') ? 'branding' : 'images';
                    $oldFilePath = public_path($oldDir . '/' . $oldFilename);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
            }
            
            // Guardar directamente en public/branding/logo.png
            $file = $request->file('file');
            $brandingDir = public_path('branding');
            
            // Crear directorio si no existe
            if (!file_exists($brandingDir)) {
                mkdir($brandingDir, 0755, true);
            }
            
            $filename = 'logo.' . $file->getClientOriginalExtension();
            $file->move($brandingDir, $filename);
            
            $url = '/branding/' . $filename;
            BusinessSetting::set('brand_logo_url', $url, 'string', 'URL del logo de la marca');

            // FilePond espera un "serverId" (texto). Enviamos la URL como texto plano.
            return response($url, 200)->header('Content-Type', 'text/plain');
        } catch (\Throwable $e) {
            \Log::error('Error uploading brand logo (async)', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el logo',
            ], 500);
        }
    }

    /**
     * Obtener tipos de medallón configurados
     */
    private function getMedallionTypes()
    {
        $medallionTypes = BusinessSetting::get('medallion_types', []);

        // Si no hay configuración, usar valores por defecto
        if (empty($medallionTypes)) {
            $medallionTypes = [
                ['name' => 'Carne', 'is_default' => true, 'enabled' => true],
                ['name' => 'Veggie Tomate Seco Aduki (Rúcula, Albahaca y Oliva)', 'is_default' => false, 'enabled' => true],
                ['name' => 'Veggie Zanahoria Romero (Arvejas, Yamaní y Chía)', 'is_default' => false, 'enabled' => true],
            ];
        }

        return $medallionTypes;
    }

    /**
     * API: Estadísticas para dashboard
     */
    public function getStats()
    {
        $stats = [
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'orders_this_week' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'orders_this_month' => Order::whereMonth('created_at', now()->month)->count(),
            'revenue_today' => Order::whereDate('created_at', today())->sum('total_amount'),
            'revenue_this_week' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
            'revenue_this_month' => Order::whereMonth('created_at', now()->month)->sum('total_amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Crear un nuevo aderezo o dip
     */
    public function storeSauce(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sauce,dip',
            'is_available' => 'nullable|boolean'
        ]);

        try {
            $configuration = ProductConfiguration::create([
                'name' => ucfirst($request->type),  // "sauce" -> "Sauce", "dip" -> "Dip"
                'value' => $request->name,
                'price_modifier' => 0,  // Las salsas no modifican precio
                'is_available' => $request->input('is_available', true),
                'sort_order' => ProductConfiguration::where('name', ucfirst($request->type))->max('sort_order') + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->type) . ' creado exitosamente',
                'sauce' => (object) [
                    'id' => $configuration->id,
                    'name' => $configuration->value,
                    'type' => $request->type,
                    'is_available' => $configuration->is_available,
                    'sort_order' => $configuration->sort_order,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el ' . $request->type . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un aderezo o dip
     */
    public function updateSauce(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_available' => 'nullable|boolean'
        ]);

        try {
            $configuration = ProductConfiguration::findOrFail($id);
            $updateData = ['value' => $request->name];
            if ($request->has('is_available')) {
                $updateData['is_available'] = $request->boolean('is_available');
            }
            $configuration->update($updateData);

            return response()->json([
                'success' => true,
                'message' => ucfirst($configuration->name) . ' actualizado exitosamente',
                'sauce' => (object) [
                    'id' => $configuration->id,
                    'name' => $configuration->value,
                    'type' => strtolower($configuration->name),
                    'is_available' => $configuration->is_available,
                    'sort_order' => $configuration->sort_order,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un aderezo o dip
     */
    public function destroySauce($id)
    {
        try {
            $configuration = ProductConfiguration::findOrFail($id);
            $type = strtolower($configuration->name);
            $configuration->delete();

            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar aderezos y dips con las opciones de productos
     */
    private function syncSaucesWithProducts()
    {
        try {
            // Obtener todos los productos de hamburguesas y acompañamientos
            $hamburgerProducts = Product::where('category_id', 1)->get();
            $accompanimentProducts = Product::where('category_id', 2)->get();
            $allProducts = $hamburgerProducts->merge($accompanimentProducts);

            foreach ($allProducts as $product) {
                // Eliminar opciones de aderezos y dips existentes
                ProductOption::where('product_id', $product->id)
                    ->whereIn('name', ['Aderezos', 'Dips'])
                    ->delete();

                // Obtener aderezos y dips actuales
                $sauces = Sauce::available()->sauces()->ordered()->get();
                $dips = Sauce::available()->dips()->ordered()->get();

                // Crear opciones de aderezos
                foreach ($sauces as $sauce) {
                    ProductOption::create([
                        'product_id' => $product->id,
                        'name' => 'Aderezos',
                        'value' => $sauce->name,
                        'price_modifier' => 0,
                        'is_available' => true,
                        'sort_order' => $sauce->sort_order,
                    ]);
                }

                // Crear opciones de dips
                foreach ($dips as $dip) {
                    ProductOption::create([
                        'product_id' => $product->id,
                        'name' => 'Dips',
                        'value' => $dip->name,
                        'price_modifier' => 0,
                        'is_available' => true,
                        'sort_order' => $dip->sort_order,
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error syncing sauces with products: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear un nuevo extra
     */
    public function storeExtra(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean'
        ]);

        try {
            $configuration = ProductConfiguration::create([
                'name' => 'Extras',
                'value' => $request->name,
                'price_modifier' => $request->price,
                'is_available' => $request->input('is_available', true),
                'sort_order' => ProductConfiguration::where('name', 'Extras')->max('sort_order') + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Extra creado exitosamente',
                'extra' => (object) [
                    'id' => $configuration->id,
                    'name' => $configuration->value,
                    'price' => $configuration->price_modifier,
                    'is_available' => $configuration->is_available,
                    'sort_order' => $configuration->sort_order,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el extra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un extra
     */
    public function updateExtra(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean'
        ]);

        try {
            $configuration = ProductConfiguration::findOrFail($id);
            $updateData = [
                'value' => $request->name,
                'price_modifier' => $request->price
            ];
            if ($request->has('is_available')) {
                $updateData['is_available'] = $request->boolean('is_available');
            }
            $configuration->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Extra actualizado exitosamente',
                'extra' => (object) [
                    'id' => $configuration->id,
                    'name' => $configuration->value,
                    'price' => $configuration->price_modifier,
                    'is_available' => $configuration->is_available,
                    'sort_order' => $configuration->sort_order,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un extra
     */
    public function destroyExtra($id)
    {
        try {
            $configuration = ProductConfiguration::findOrFail($id);
            $configuration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Extra eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista de gestión de turnos
     */
    public function turnos()
    {
        // Obtener configuraciones semanales
        $weeklyConfigs = \App\Models\WeeklyTurnoConfig::getAllConfigs();

        $hoy = now()->format('Y-m-d');
        $microturnosHoy = \App\Models\DynamicMicroturno::generarParaFecha($hoy);

        // Debug: Ver todos los microturnos generados
        \Log::info('Debug AdminController - Microturnos generados', [
            'fecha' => $hoy,
            'count' => $microturnosHoy->count(),
            'microturnos' => $microturnosHoy->map(function ($m) {
                return [
                    'sortOrder' => $m->getSortOrderAttribute(),
                    'horaInicio' => $m->getHoraInicioAttribute(),
                    'horaFin' => $m->getHoraFinAttribute(),
                    'formattedTime' => $m->getFormattedTimeAttribute()
                ];
            })
        ]);

        // Debug: Verificar pedidos activos
        $ordersActivos = \App\Models\Order::whereIn('status', ['confirmed', 'preparing'])
            ->whereDate('created_at', $hoy)
            ->with('items.product.category')
            ->get();

        \Log::info('Debug AdminController - Pedidos activos hoy', [
            'fecha' => $hoy,
            'count' => $ordersActivos->count(),
            'orders' => $ordersActivos->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'microturno_sort_order' => $order->microturno_sort_order,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_name' => $item->product->name,
                            'category' => $item->product->category->name,
                            'quantity' => $item->quantity
                        ];
                    })
                ];
            })
        ]);

        // Debug: Verificar productos por microturno
        foreach ($microturnosHoy as $microturno) {
            $productos = $microturno->getProductosActivos();
            \Log::info('Debug AdminController - Productos por microturno', [
                'microturno_sort_order' => $microturno->getSortOrderAttribute(),
                'hora_inicio' => $microturno->getHoraInicioAttribute(),
                'hora_fin' => $microturno->getHoraFinAttribute(),
                'productos' => $productos,
                'disponible' => $microturno->getIsDisponibleAttribute()
            ]);
        }

        // Estadísticas del día
        $estadisticas = [
            'total_microturnos' => $microturnosHoy->count(),
            'microturnos_disponibles' => $microturnosHoy->filter(function ($m) {
                return $m->getIsDisponibleAttribute();
            })->count(),
            'microturnos_llenos' => $microturnosHoy->filter(function ($m) {
                return !$m->getIsDisponibleAttribute();
            })->count(),
            'total_pedidos' => $microturnosHoy->sum(function ($m) {
                return $m->getPedidosActivosAttribute();
            }),
            'total_capacidad' => $microturnosHoy->sum(function ($m) {
                return $m->getCapacidadMaximaAttribute();
            }),
        ];

        if ($estadisticas['total_capacidad'] > 0) {
            $estadisticas['porcentaje_ocupacion'] = round(($estadisticas['total_pedidos'] / $estadisticas['total_capacidad']) * 100, 2);
        } else {
            $estadisticas['porcentaje_ocupacion'] = 0;
        }

        // Obtener días con microturnos (últimos 7 días hacia adelante)
        $diasConTurnos = collect();
        $fechaInicio = now()->subDays(7);
        $fechaFin = now()->addDays(30);

        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            $fechaStr = $fecha->format('Y-m-d');
            $microturnosDelDia = \App\Models\DynamicMicroturno::generarParaFecha($fechaStr);

            if ($microturnosDelDia->isNotEmpty()) {
                $totalPedidos = $microturnosDelDia->sum(function ($m) {
                    return $m->getPedidosActivosAttribute();
                });
                $totalCapacidad = $microturnosDelDia->sum(function ($m) {
                    return $m->getCapacidadMaximaAttribute();
                });
                $microturnosDisponibles = $microturnosDelDia->filter(function ($m) {
                    return $m->getIsDisponibleAttribute();
                })->count();
                $microturnosLlenos = $microturnosDelDia->filter(function ($m) {
                    return !$m->getIsDisponibleAttribute();
                })->count();

                $porcentajeOcupacion = $totalCapacidad > 0
                    ? round(($totalPedidos / $totalCapacidad) * 100, 2)
                    : 0;

                $diasConTurnos->push([
                    'fecha' => $fechaStr,
                    'fecha_formateada' => $fecha->format('d/m/Y'),
                    'dia_semana' => $fecha->locale('es')->dayName,
                    'total_microturnos' => $microturnosDelDia->count(),
                    'microturnos_disponibles' => $microturnosDisponibles,
                    'microturnos_llenos' => $microturnosLlenos,
                    'total_pedidos' => $totalPedidos,
                    'total_capacidad' => $totalCapacidad,
                    'porcentaje_ocupacion' => $porcentajeOcupacion,
                    'es_hoy' => $fechaStr === now()->format('Y-m-d'),
                    'es_pasado' => $fechaStr < now()->format('Y-m-d'),
                ]);
            }
        }

        // Leer setting de skip de selección de turno
        $skipTurnoSelection = BusinessSetting::get('skip_turno_selection', false);
        // Estado del sistema de turnos: ON => clientes eligen turno (no se salta)
        $systemTurnosEnabled = !$skipTurnoSelection;

        return view('admin.turnos', compact('weeklyConfigs', 'microturnosHoy', 'estadisticas', 'diasConTurnos', 'skipTurnoSelection', 'systemTurnosEnabled'));
    }

    /**
     * Actualizar configuración semanal
     */
    public function updateWeeklyConfig(Request $request)
    {
        try {
            $request->validate([
                'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'duracion_microturno_minutos' => 'required|integer|min:5|max:60',
                'max_hamburguesas' => 'required|integer|min:1|max:50',
                'max_acompañamientos' => 'required|integer|min:1|max:50',
                'is_enabled' => 'boolean',
            ]);

            $config = \App\Models\WeeklyTurnoConfig::updateOrCreate(
                ['day_of_week' => $request->day_of_week],
                [
                    'hora_inicio' => $request->hora_inicio . ':00',
                    'hora_fin' => $request->hora_fin . ':00',
                    'duracion_microturno_minutos' => $request->duracion_microturno_minutos,
                    'max_hamburguesas' => $request->max_hamburguesas,
                    'max_acompañamientos' => $request->max_acompañamientos,
                    'is_enabled' => $request->has('is_enabled') ? $request->is_enabled : true,
                    'is_active' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'config' => $config
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', collect($e->errors())->flatten()->toArray()),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternar la configuración de saltar selección de turno
     */
    public function toggleSkipTurnoSelection(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        try {
            $enabled = filter_var($request->enabled, FILTER_VALIDATE_BOOLEAN);
            // En la UI: enabled=true significa sistema de turnos ON (NO saltar)
            // Guardamos el inverso en skip_turno_selection
            BusinessSetting::set('skip_turno_selection', !$enabled, 'boolean', 'Saltar selección de turno y asignar automáticamente el primer microturno disponible');

            return response()->json([
                'success' => true,
                'enabled' => $enabled
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar microturnos para un rango de fechas (DEPRECATED - Los microturnos son dinámicos)
     */
    public function generateMicroturnos(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Los microturnos ahora se calculan dinámicamente basándose en la configuración semanal. No es necesario generar microturnos manualmente.',
            'microturnos_generados' => 0
        ]);
    }

    public function regenerarMicroturnosHoy(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Los microturnos ahora se calculan dinámicamente. No es necesario regenerarlos manualmente.',
            'count' => 0
        ]);
    }

    /**
     * Store a new product configuration
     */
    public function storeProductConfiguration(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'price_modifier' => 'required|numeric',
        ]);

        try {
            $config = ProductConfiguration::create([
                'name' => $request->name,
                'value' => $request->value,
                'price_modifier' => $request->price_modifier,
                'is_available' => true,
                'sort_order' => ProductConfiguration::where('name', $request->name)->max('sort_order') + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración creada exitosamente',
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a product configuration
     */
    public function updateProductConfiguration(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'price_modifier' => 'required|numeric',
        ]);

        try {
            $config = ProductConfiguration::findOrFail($id);
            $config->update([
                'name' => $request->name,
                'value' => $request->value,
                'price_modifier' => $request->price_modifier,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle availability of a product configuration
     */
    public function toggleProductConfiguration(Request $request, $id)
    {
        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        try {
            $config = ProductConfiguration::findOrFail($id);
            $config->update([
                'is_available' => $request->is_available
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product configuration
     */
    public function destroyProductConfiguration($id)
    {
        try {
            $config = ProductConfiguration::findOrFail($id);
            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Configuración eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh orders data for AJAX requests
     */
    public function latestOrderCheck(): \Illuminate\Http\JsonResponse
    {
        $order = Order::whereDate('created_at', today())
            ->latest('id')
            ->select('id', 'order_number', 'contact_name', 'created_at')
            ->first();

        return response()->json([
            'latest_id'      => $order?->id ?? 0,
            'order_number'   => $order?->order_number,
            'contact_name'   => $order?->contact_name,
            'created_at'     => $order?->created_at?->toIso8601String(),
        ]);
    }

    public function refreshOrders(Request $request)
    {
        try {
            // Soportar fecha seleccionada desde la UI
            $selectedDate = $request->get('selected_date');
            $targetDate = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->toDateString() : today();

            // Obtener pedidos del día objetivo (con relación user y campos de contacto)
            $pedidosHoy = Order::whereDate('created_at', $targetDate)
                ->with(['items.product', 'user', 'coupon', 'reviews'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Pedidos anteriores: solo el día anterior al objetivo, sin paginación
            $ayer = \Carbon\Carbon::parse($targetDate)->subDay()->toDateString();
            $pedidosHistorico = Order::whereDate('created_at', $ayer)
                ->with(['items.product', 'user', 'coupon', 'reviews'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener microturnos disponibles para el día objetivo y convertirlos a array simple
            $microturnosHoy = \App\Models\DynamicMicroturno::generarParaFecha($targetDate);
            $microturnosArray = [];
            
            foreach ($microturnosHoy as $microturno) {
                $microturnosArray[] = [
                    'sort_order' => $microturno->getSortOrderAttribute(),
                    'formatted_time' => $microturno->getFormattedTimeAttribute(),
                    'hora_inicio' => $microturno->getHoraInicioAttribute(),
                    'hora_fin' => $microturno->getHoraFinAttribute()
                ];
            }

            // Debug: Log de datos de clientes y ordenamiento
            \Log::info('Debug clientes en refreshOrders', [
                'pedidos_hoy_count' => $pedidosHoy->count(),
                'pedidos_ordenados_por' => 'created_at desc',
                'primeros_3_pedidos' => $pedidosHoy->take(3)->map(function($order) {
                    return [
                        'id' => $order->id,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'microturno_sort_order' => $order->microturno_sort_order,
                        'status' => $order->status
                    ];
                })->toArray(),
                'sample_order' => $pedidosHoy->first() ? [
                    'id' => $pedidosHoy->first()->id,
                    'user_id' => $pedidosHoy->first()->user_id,
                    'contact_name' => $pedidosHoy->first()->contact_name,
                    'contact_phone' => $pedidosHoy->first()->contact_phone,
                    'user_name' => $pedidosHoy->first()->user ? $pedidosHoy->first()->user->name : 'NO USER',
                    'user_phone' => $pedidosHoy->first()->user ? $pedidosHoy->first()->user->phone : 'NO USER PHONE'
                ] : 'NO ORDERS'
            ]);

            // Calcular estadísticas
            $totalVentasHoy = $pedidosHoy->sum('total_amount');
            $totalPedidosHoy = $pedidosHoy->count();

            // Preparar datos de respuesta
            $response = [
                'success' => true,
                'data' => [
                    'pedidos_hoy' => $pedidosHoy,
                    'pedidos_historico' => $pedidosHistorico,
                    'microturnos_hoy' => $microturnosArray,
                    'stats' => [
                        'total_ventas_hoy' => $totalVentasHoy,
                        'total_pedidos_hoy' => $totalPedidosHoy,
                        'total_pedidos_historico' => $pedidosHistorico->count()
                    ],
                    'last_updated' => now()->toISOString()
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Error refreshing orders: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.coupons', compact('coupons'));
    }

    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required','string','size:8','regex:/^[A-Z0-9]+$/','unique:coupons,code,NULL,id,deleted_at,NULL'],
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'usage_limit' => 'nullable|integer|min:1',
            'allow_cash_discount' => 'nullable|boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'description' => 'nullable|string|max:500',
        ]);

        $code = strtoupper($validated['code']);

        if (strlen($code) !== 8) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['code' => 'El código debe tener exactamente 8 caracteres.']);
        }

        Coupon::create([
            'code' => $code,
            'name' => $validated['name'],
            'discount_percentage' => $validated['discount_percentage'],
            'value' => $validated['discount_percentage'],
            'type' => 'percentage',
            'code_length' => 8,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'allow_cash_discount' => $request->boolean('allow_cash_discount'),
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.coupons')
            ->with('success', 'Cupón creado exitosamente');
    }

    public function updateCoupon(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required','string','size:8','regex:/^[A-Z0-9]+$/','unique:coupons,code,'.$id.',id,deleted_at,NULL'],
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'usage_limit' => 'nullable|integer|min:1',
            'allow_cash_discount' => 'nullable|boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $code = strtoupper($validated['code']);

        if (strlen($code) !== 8) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['code' => 'El código debe tener exactamente 8 caracteres.']);
        }

        $coupon->update([
            'code' => $code,
            'name' => $validated['name'],
            'discount_percentage' => $validated['discount_percentage'],
            'value' => $validated['discount_percentage'],
            'type' => 'percentage',
            'code_length' => 8,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'allow_cash_discount' => $request->boolean('allow_cash_discount'),
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.coupons')
            ->with('success', 'Cupón actualizado exitosamente');
    }

    public function destroyCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons')
            ->with('success', 'Cupón eliminado exitosamente');
    }

    public function bulkDeleteCoupons(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $count = Coupon::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.coupons')
            ->with('success', "{$count} cupón(es) eliminado(s) exitosamente");
    }

    public function getCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon);
    }
}
