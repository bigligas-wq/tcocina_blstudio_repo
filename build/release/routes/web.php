<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');
Route::get('/category/{slug}', [ProductController::class, 'category'])->name('category');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product');
Route::get('/cart', [OrderController::class, 'cart'])->name('cart');
Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/order/{orderNumber}/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');

// Rutas de autenticación (solo para panel)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas protegidas para clientes
Route::middleware('auth')->group(function () {});

// Rutas de API
Route::prefix('api')->group(function () {
    // Productos
    Route::get('/products/category/{categoryId}', [ProductController::class, 'getByCategory']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/featured', [ProductController::class, 'featured']);

    // Pedidos
    // Validación de cupones pública
    Route::post('/coupons/validate', [OrderController::class, 'validateCoupon']);

    // Rutas de pedidos que requieren autenticación
    Route::middleware('auth')->group(function () {
        Route::get('/orders', [OrderController::class, 'userOrders']);
    });
});

// Rutas de administración
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/orders/{id}', [AdminController::class, 'orderDetails'])->name('admin.order.details');
    Route::get('/orders/{id}/print', [AdminController::class, 'printOrder'])->name('admin.order.print');
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.order.status');
    Route::delete('/orders/{id}', [AdminController::class, 'destroyOrder'])->name('admin.order.destroy');
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::delete('/products/{id}', [AdminController::class, 'destroyProduct'])->name('admin.products.destroy');
    Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::get('/stats', [AdminController::class, 'getStats'])->name('admin.stats');
});

// Rutas de cocina
Route::prefix('kitchen')->middleware(['auth', 'kitchen'])->group(function () {
    Route::get('/', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::get('/display', [KitchenController::class, 'display'])->name('kitchen.display');
    Route::get('/orders', [KitchenController::class, 'getOrders']);
    Route::get('/display/orders', [KitchenController::class, 'getDisplayOrders']);
    Route::post('/orders/{id}/start', [KitchenController::class, 'startPreparation']);
    Route::post('/orders/{id}/ready', [KitchenController::class, 'markReady']);
});

// Desactivar scaffolding duplicado
