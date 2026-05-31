<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function __construct()
    {
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
    public function dashboard()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_products' => Product::count(),
            'total_customers' => User::where('role', 'customer')->count(),
        ];

        $recentOrders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }

    /**
     * Gestión de pedidos
     */
    public function orders(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'address']);

        // Filtros
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.orders', compact('orders'));
    }

    /**
     * Detalles de un pedido
     */
    public function orderDetails($id)
    {
        $order = Order::with(['user', 'items.product', 'address'])
            ->findOrFail($id);

        return view('admin.order-details', compact('order'));
    }

    public function printOrder($id)
    {
        $order = Order::with(['user', 'items.product', 'address'])->findOrFail($id);
        return view('admin.order-print', compact('order'));
    }

    /**
     * Actualizar estado del pedido
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $newStatus = $request->status;

        $update = ['status' => $newStatus];
        $now = now();

        if ($newStatus === 'confirmed' && !$order->confirmed_at) {
            $update['confirmed_at'] = $now;
        }
        if ($newStatus === 'preparing' && !$order->preparing_at) {
            $update['preparing_at'] = $now;
        }
        if ($newStatus === 'ready' && !$order->ready_at) {
            $update['ready_at'] = $now;
        }
        if ($newStatus === 'out_for_delivery' && !$order->out_for_delivery_at) {
            $update['out_for_delivery_at'] = $now;
        }

        $order->update($update);

        // Refrescar y calcular el timestamp de referencia para el contador según el nuevo estado
        $order->refresh();
        $fromTs = match ($newStatus) {
            'confirmed' => $order->confirmed_at ?: $order->created_at,
            'preparing' => $order->preparing_at ?: $order->confirmed_at ?: $order->created_at,
            'ready' => $order->ready_at ?: $order->preparing_at ?: $order->created_at,
            'out_for_delivery' => $order->out_for_delivery_at ?: $order->ready_at ?: $order->created_at,
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
     * Eliminar un pedido y sus ítems
     */
    public function destroyOrder($id)
    {
        $order = Order::with('items')->findOrFail($id);
        // Los ítems se eliminan por cascade si la FK está con onDelete('cascade'). Por si acaso:
        foreach ($order->items as $item) {
            $item->delete();
        }
        $order->delete();

        return redirect()->route('admin.orders')->with('success', 'Pedido eliminado');
    }

    /**
     * Gestión de productos
     */
    public function products()
    {
        $products = Product::with('category')->paginate(20);
        $categories = Category::all();

        return view('admin.products', compact('products', 'categories'));
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
        ]);

        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;  // guardar ruta relativa en disk public
        } else {
            unset($data['image']);
        }

        $product = Product::create($data);

        // Crear variantes y opciones por defecto para que aparezcan en catálogo
        try {
            // Variantes: Medallones
            $variantsMedallones = [
                ['name' => 'Medallones', 'value' => 'Simple', 'price_modifier' => -2000, 'sort_order' => 1],
                ['name' => 'Medallones', 'value' => 'Doble', 'price_modifier' => 0, 'sort_order' => 2],
                ['name' => 'Medallones', 'value' => 'Triple', 'price_modifier' => 2500, 'sort_order' => 3],
            ];
            foreach ($variantsMedallones as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $v['name'],
                    'value' => $v['value'],
                    'price_modifier' => $v['price_modifier'],
                    'is_available' => true,
                    'sort_order' => $v['sort_order'],
                ]);
            }

            // Variantes: Tipo de Medallón (Veggie ajusta a $15.500)
            $veggieTargetPrice = 15500;
            $veggieModifier = (float) $veggieTargetPrice - (float) $product->base_price;
            $variantsTipo = [
                ['name' => 'Tipo de Medallón', 'value' => 'Carne', 'price_modifier' => 0, 'sort_order' => 1],
                ['name' => 'Tipo de Medallón', 'value' => 'Veggie', 'price_modifier' => $veggieModifier, 'sort_order' => 2],
            ];
            foreach ($variantsTipo as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $v['name'],
                    'value' => $v['value'],
                    'price_modifier' => $v['price_modifier'],
                    'is_available' => true,
                    'sort_order' => $v['sort_order'],
                ]);
            }

            // Opciones: Extras con costo
            $extras = [
                ['name' => 'Extras', 'value' => 'Bacon + Carne + Cheddar', 'price_modifier' => 2500, 'sort_order' => 1],
                ['name' => 'Extras', 'value' => 'Provoleta', 'price_modifier' => 1000, 'sort_order' => 2],
                ['name' => 'Extras', 'value' => 'Queso Azul', 'price_modifier' => 1000, 'sort_order' => 3],
                ['name' => 'Extras', 'value' => 'Champigñón', 'price_modifier' => 1000, 'sort_order' => 4],
                ['name' => 'Extras', 'value' => 'Huevo a la plancha', 'price_modifier' => 1000, 'sort_order' => 5],
                ['name' => 'Extras', 'value' => 'Doble Provolone', 'price_modifier' => 1000, 'sort_order' => 6],
                ['name' => 'Extras', 'value' => 'Porción aros de cebolla + dip', 'price_modifier' => 8500, 'sort_order' => 7],
                ['name' => 'Extras', 'value' => 'Porción papas extra + dip', 'price_modifier' => 5500, 'sort_order' => 8],
            ];
            foreach ($extras as $opt) {
                ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $opt['name'],
                    'value' => $opt['value'],
                    'price_modifier' => $opt['price_modifier'],
                    'is_available' => true,
                    'sort_order' => $opt['sort_order'],
                ]);
            }

            // Opciones: Aderezos sin costo
            $aderezos = [
                'Cheddar',
                'Ketchup',
                'Barbacoa',
                'Mostaza',
                'Mayonesa',
                'Ketchup americano',
                'Mostaza y miel',
                'Mayonesa ahumada',
                'Mayonesa de ajo',
                'Mayonesa picante',
            ];
            $order = 1;
            foreach ($aderezos as $val) {
                ProductOption::create([
                    'product_id' => $product->id,
                    'name' => 'Aderezos',
                    'value' => $val,
                    'price_modifier' => 0,
                    'is_available' => true,
                    'sort_order' => $order++,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron crear variantes/opciones por defecto', ['error' => $e->getMessage()]);
        }

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
        ]);

        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            // borrar imagen anterior si era del disk public
            if ($product->image && str_contains($product->image, '/')) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
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
        if ($product->image && str_contains($product->image, '/')) {
            Storage::disk('public')->delete($product->image);
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

        return view('admin.settings', compact('settings'));
    }

    /**
     * Actualizar configuración
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_phone' => 'required|string|max:20',
            'business_email' => 'required|email|max:255',
            'business_address' => 'required|string|max:500',
            'delivery_fee' => 'required|numeric|min:0',
            'minimum_order_amount' => 'required|numeric|min:0',
            'estimated_delivery_time' => 'required|integer|min:1',
        ]);

        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['_token', '_method']))
                continue;

            BusinessSetting::set($key, $value);
        }

        return redirect()->back()->with('success', 'Configuración actualizada');
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
}
