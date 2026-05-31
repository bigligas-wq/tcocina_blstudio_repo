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
        $orders = Order::whereIn('status', ['confirmed', 'preparing'])
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('orders'));
    }

    /**
     * API: Obtener pedidos para la cocina
     */
    public function getOrders()
    {
        $orders = Order::whereIn('status', ['confirmed', 'preparing'])
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'asc')
            ->get();

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
     * Marcar pedido como listo
     */
    public function markReady($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'preparing') {
            return response()->json(['error' => 'El pedido no está en preparación'], 400);
        }

        $order->update(['status' => 'ready', 'ready_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Pedido listo']);
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
        $orders = Order::whereIn('status', ['confirmed', 'preparing'])
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('status');

        return response()->json($orders);
    }
}
