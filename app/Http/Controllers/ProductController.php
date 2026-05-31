<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\LoyaltySetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\UserLoyaltyWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Mostrar el catálogo de productos
     */
    public function index(Request $request)
    {
        $categories = Category::active()->ordered()->get();

        $products = Product::available()
            ->with(['category', 'defaultSauce'])
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('categories.sort_order')
            ->orderBy('products.sort_order')
            ->select('products.*')
            ->get();

        $defaultCategory = 'hamburguesas';
        $defaultCatalogView = \App\Models\BusinessSetting::get('default_catalog_view', 'grid');

        $loyaltyWallet = null;
        $loyaltySetting = null;
        $loyaltyProgress = null;
        if (Auth::check()) {
            $loyaltySetting = LoyaltySetting::active();
            $loyaltyWallet = UserLoyaltyWallet::firstOrCreate(
                ['user_id' => Auth::id()],
                ['current_stickers' => 0, 'total_earned' => 0, 'total_redeemed' => 0]
            );
            $loyaltyProgress = min(100, ($loyaltyWallet->current_stickers / max(1, $loyaltySetting->target_stickers)) * 100);
        }

        $activeOrder = null;
        if (Auth::check()) {
            $activeOrder = Order::where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'on_the_way'])
                ->whereDate('created_at', today())
                ->latest()
                ->first();
        }

        return view('catalog', compact('categories', 'products', 'defaultCategory', 'defaultCatalogView', 'loyaltyWallet', 'loyaltySetting', 'loyaltyProgress', 'activeOrder'));
    }

    /**
     * Mostrar productos por categoría
     */
    public function category($slug)
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();

        $products = Product::where('category_id', $category->id)
            ->available()
            ->ordered()
            ->get();

        return view('category', compact('category', 'products'));
    }

    /**
     * Mostrar detalles de un producto
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->available()
            ->with(['category'])
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->available()
            ->take(4)
            ->get();

        return view('product-details', compact('product', 'relatedProducts'));
    }

    /**
     * API: Obtener productos por categoría
     */
    public function getByCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)
            ->available()
            ->get();

        return response()->json($products);
    }

    /**
     * API: Buscar productos
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->available()
            ->with(['category'])
            ->get();

        return response()->json($products);
    }

    /**
     * API: Obtener productos destacados
     */
    public function featured()
    {
        $products = Product::available()
            ->featured()
            ->ordered()
            ->with(['category'])
            ->get();

        return response()->json($products);
    }

    /**
     * API: Obtener producto por ID
     */
    public function getById($id)
    {
        $product = Product::available()
            ->with(['category'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    /**
     * API: Obtener múltiples productos por IDs
     */
    public function getBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id'
        ]);

        \Log::info('Debug ProductController - getBatch request', [
            'ids' => $request->ids
        ]);

        $products = Product::available()
            ->with(['category'])
            ->whereIn('id', $request->ids)
            ->get();

        \Log::info('Debug ProductController - Productos encontrados', [
            'count' => $products->count(),
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? 'NULL'
                ];
            })
        ]);

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }
}
