<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Mostrar el catálogo de productos
     */
    public function index(Request $request)
    {
        $categories = Category::active()->ordered()->get();

        $products = Product::available()
            ->ordered()
            ->with(['category', 'variants', 'options'])
            ->get();

        return view('catalog', compact('categories', 'products'));
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
            ->with(['variants', 'options'])
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
            ->with(['category', 'variants', 'options'])
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
            ->with(['variants', 'options'])
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
            ->with(['category', 'variants', 'options'])
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
            ->with(['category', 'variants', 'options'])
            ->get();

        return response()->json($products);
    }
}
