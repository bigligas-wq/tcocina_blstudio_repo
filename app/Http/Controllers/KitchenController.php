<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitchenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!in_array(Auth::user()->role, ['admin', 'kitchen'])) {
                abort(403, 'Acceso denegado');
            }
            return $next($request);
        });
    }

    /**
     * Vista principal de la cocina
     */
    public function index()
    {
        // Solo pedidos de hoy
        $hoy = now()->format('Y-m-d');

        $orders = Order::whereIn('status', ['confirmed', 'preparing'])
            ->whereDate('created_at', $hoy)
            ->with([
                'user',
                'address',
                'items' => function ($query) {
                    $query->with('product.category');
                }
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        // Agregar configuration_text a cada item
        $orders->each(function ($order) {
            $order->items->each(function ($item) {
                $item->configuration_text = $item->configuration_text;
            });
        });

        // Obtener microturnos dinámicos del día actual
        $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($hoy);

        // Agrupar pedidos por microturno dinámico
        $pedidosPorMicroturno = collect();
        foreach ($microturnos as $microturno) {
            $pedidosDelMicroturno = $orders->filter(function ($order) use ($microturno) {
                return $microturno->contienePedido($order);
            });
            // Incluir todos los microturnos, incluso si no tienen pedidos
            $pedidosPorMicroturno->put($microturno->getSortOrderAttribute(), $pedidosDelMicroturno);
        }

        // Convertir pedidosPorMicroturno a arrays para consistencia
        $pedidosPorMicroturno = $pedidosPorMicroturno->map(function ($pedidos) {
            return $pedidos->values();
        });

        return view('kitchen.index', compact('orders', 'microturnos', 'pedidosPorMicroturno'));
    }

    /**
     * API: Obtener pedidos para la cocina
     */
    public function getOrders()
    {
        // Solo pedidos de hoy
        $hoy = now()->format('Y-m-d');

        $orders = Order::whereIn('status', ['confirmed', 'preparing'])
            ->whereDate('created_at', $hoy)
            ->with(['user', 'items' => function ($query) {
                $query->with('product');
            }])
            ->orderBy('created_at', 'asc')
            ->get();

        // Agregar configuration_text a cada item
        $orders->each(function ($order) {
            $order->items->each(function ($item) {
                $item->configuration_text = $item->configuration_text;
            });
        });

        // Convertir pedidosPorMicroturno a arrays para consistencia
        $orders->each(function ($order) {
            if (isset($order->pedidosPorMicroturno)) {
                $order->pedidosPorMicroturno = $order->pedidosPorMicroturno->map(function ($pedidos) {
                    return $pedidos->values()->toArray();
                });
            }
        });

        return response()->json($orders);
    }

    /**
     * Marcar pedido como en preparación
     */
    public function startPreparation($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'confirmed') {
            return response()->json(['error' => 'El pedido no está confirmado'], 400);
        }

        $order->update(['status' => 'preparing', 'preparing_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Pedido en preparación']);
    }

    /**
     * Marcar pedido como entregado
     */
    public function markReady($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'preparing') {
            return response()->json(['error' => 'El pedido no está en preparación'], 400);
        }

        $order->update(['status' => 'delivered', 'delivered_at' => now()]);

        // Los microturnos dinámicos se calculan automáticamente
        // No necesitamos liberar espacio manualmente

        // Enviar solicitud de reseña
        try {
            \App\Services\ReviewRequestService::sendReviewRequest($order);
            \App\Services\ReviewNotificationService::notifyPendingReview($order->user, $order);
        } catch (\Exception $e) {
            // Log error but don't fail the order delivery
            \Log::error('Error sending review request: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Pedido entregado']);
    }

    /**
     * Vista de pantalla completa para cocina
     */
    public function display()
    {
        return view('kitchen.display');
    }

    /**
     * API: Obtener pedidos para pantalla de cocina
     */
    public function getDisplayOrders()
    {
        // Solo pedidos de hoy
        $hoy = now()->format('Y-m-d');

        $orders = Order::whereIn('status', ['confirmed', 'preparing'])
            ->whereDate('created_at', $hoy)
            ->with([
                'user',
                'address',
                'items' => function ($query) {
                    $query->with('product.category');
                }
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        // Obtener microturnos dinámicos del día actual
        $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($hoy);

        // Agrupar pedidos por microturno dinámico
        $pedidosPorMicroturno = collect();
        foreach ($microturnos as $microturno) {
            $pedidosDelMicroturno = $orders->filter(function ($order) use ($microturno) {
                return $microturno->contienePedido($order);
            });
            // Incluir todos los microturnos, incluso si no tienen pedidos
            $pedidosPorMicroturno->put($microturno->getSortOrderAttribute(), $pedidosDelMicroturno);
        }

        // Agregar configuration_text a cada item + etiqueta del turno asignado
        $orders->each(function ($order) {
            $order->items->each(function ($item) {
                $item->configuration_text = $item->configuration_text;
            });
            $order->append('turno_label');
        });

        return response()->json([
            'orders' => $orders,
            'microturnos' => $microturnos->map(function ($m) {
                return $m->toArray();
            }),
            'pedidosPorMicroturno' => $pedidosPorMicroturno->map(function ($pedidos) {
                return $pedidos->values()->toArray();  // Convertir a array indexado
            })
        ]);
    }
}
