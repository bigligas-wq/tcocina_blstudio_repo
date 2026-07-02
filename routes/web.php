<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLoyaltyController;
use App\Http\Controllers\AdminProductReviewController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');
Route::get('/category/{slug}', [ProductController::class, 'category'])->name('category');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product');
Route::get('/cart', [OrderController::class, 'cart'])->name('cart');
Route::get('/turnos', [OrderController::class, 'turnos'])->name('turnos');
Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/order/{orderNumber}/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');

// Rutas públicas de reseñas de productos
Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index']);
Route::get('/reviews/{review}', [ProductReviewController::class, 'show']);

// Rutas legales
Route::get('/privacy', [App\Http\Controllers\LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms', [App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms');
Route::get('/shipping', [App\Http\Controllers\LegalController::class, 'shipping'])->name('legal.shipping');
Route::get('/faq', [App\Http\Controllers\LegalController::class, 'faq'])->name('legal.faq');

// Rutas de autenticación (solo para panel)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Tracking público (sin login requerido)
// TODO: cuando el admin quiera requerir login, ver guia_para_vovler_la_condicion_logueado_para_traking.md
Route::get('/pedido/{orderNumber}/seguimiento', [OrderController::class, 'tracking'])->name('orders.tracking');
Route::get('/mi-pedido', [OrderController::class, 'myLatestTracking'])->name('orders.my-latest');

// Pantalla de gracias / solicitud de reseña (pública)
Route::get('/gracias/{orderNumber}', [OrderController::class, 'thanks'])->name('thanks.show');
Route::post('/gracias/{orderNumber}/dismiss', [OrderController::class, 'dismissThanks'])->name('thanks.dismiss');
Route::match(['get', 'post'], '/gracias/{orderNumber}/completada', [OrderController::class, 'markReviewCompleted'])->name('thanks.completed');

// Rutas protegidas para clientes
Route::middleware('auth')->group(function () {
    Route::get('/mi-progreso', [LoyaltyController::class, 'dashboard'])->name('loyalty.dashboard');
    Route::post('/mi-progreso/canjear', [LoyaltyController::class, 'requestRedemption'])->name('loyalty.redeem.request');

    // Sistema de reseñas (antiguo - para reseñas de la web)
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Sistema de reseñas de productos
    Route::get('/my-reviews', [ProductReviewController::class, 'myReviews']);
    Route::get('/pending-reviews', [ProductReviewController::class, 'pendingReviews']);
    Route::post('/product-reviews', [ProductReviewController::class, 'store']);
    Route::put('/product-reviews/{review}', [ProductReviewController::class, 'update']);
    Route::delete('/product-reviews/{review}', [ProductReviewController::class, 'destroy']);
    Route::post('/product-reviews/{review}/upload-image', [ProductReviewController::class, 'uploadImage']);
    Route::delete('/review-images/{image}', [ProductReviewController::class, 'deleteImage']);
    Route::post('/product-reviews/{review}/report', [ProductReviewController::class, 'report']);

    Route::get('/mis-datos', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/mis-datos', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/mis-datos/direcciones', [UserProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/mis-datos/direcciones/{address}', [UserProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/mis-datos/direcciones/{address}', [UserProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
    Route::post('/mis-datos/direcciones/{address}/predeterminada', [UserProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default');
});

// Rutas de API
Route::prefix('api')->middleware(['throttle:60,1'])->group(function () {
    // Productos
    Route::get('/products/{id}', [ProductController::class, 'getById']);
    Route::post('/products/batch', [ProductController::class, 'getBatch']);
    Route::get('/products/category/{categoryId}', [ProductController::class, 'getByCategory']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/featured', [ProductController::class, 'featured']);

    // Pedidos

    // Pedido estado — público (polling del tracking, sin login)
    // TODO: ver guia_para_vovler_la_condicion_logueado_para_traking.md para reactivar auth
    Route::get('/pedido/{orderNumber}/estado', [OrderController::class, 'getStatus'])->name('orders.status');

    // Rutas de pedidos que requieren autenticación
    Route::middleware('auth')->group(function () {
        Route::get('/orders', [OrderController::class, 'userOrders']);
        // Notificaciones de álbum/canje
        Route::get('/loyalty/notifications', [LoyaltyController::class, 'pendingNotifications']);
        Route::post('/loyalty/notifications/{id}/seen', [LoyaltyController::class, 'markNotificationSeen']);

    // Sistema de campanita de notificaciones
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    Route::post('/coupons/validate', function (Request $request) {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = \App\Models\Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no encontrado',
            ], 404);
        }

        if (!$coupon->isValid()) {
            $message = 'Cupón no válido';
            if (!$coupon->is_active) {
                $message = 'Cupón inactivo';
            } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                $message = 'Cupón agotado';
            } elseif ($coupon->valid_until && now()->gt($coupon->valid_until)) {
                $message = 'Cupón expirado';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 400);
        }

        $discount = $coupon->calculateDiscount($request->subtotal);

        return response()->json([
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_percentage' => $coupon->discount_percentage,
                'code_length' => $coupon->code_length,
                'allow_cash_discount' => (bool) $coupon->allow_cash_discount,
                'discount_amount' => $discount,
            ],
        ]);
    })->name('api.coupons.validate');
});


// Rutas de administración
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/orders/refresh', [AdminController::class, 'refreshOrders'])->name('admin.orders.refresh');
    Route::get('/orders/latest-check', [AdminController::class, 'latestOrderCheck'])->name('admin.orders.latest-check');
    Route::get('/orders/microturnos-disponibles', [AdminController::class, 'getMicroturnosDisponibles'])->name('admin.orders.microturnos-disponibles');
    Route::post('/orders/bulk-delete', [AdminController::class, 'bulkDeleteOrders'])->name('admin.orders.bulk-delete');
    // Rutas específicas de orden (deben ir antes de la ruta general /orders/{id})
    Route::get('/orders/{id}/print', [AdminController::class, 'printOrder'])->name('admin.order.print');
    Route::get('/orders/{id}/loyalty-impact', [AdminController::class, 'getOrderLoyaltyImpact'])->name('admin.order.loyalty-impact');
    Route::post('/orders/{id}/award-loyalty', [AdminController::class, 'awardOrderLoyalty'])->name('admin.order.award-loyalty');
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.order.status');
    Route::post('/orders/{id}/mark-review-prompted', [AdminController::class, 'markReviewPrompted'])->name('admin.order.mark-review-prompted');
    Route::put('/orders/{id}/microturno', [AdminController::class, 'updateOrderMicroturno'])->name('admin.order.microturno');
    Route::put('/orders/{id}/details', [AdminController::class, 'updateOrderDetails'])->name('admin.order.details.update');
    Route::delete('/orders/{id}', [AdminController::class, 'destroyOrder'])->name('admin.order.destroy');
    // Ruta general de detalles (debe ir al final)
    Route::get('/orders/{id}', [AdminController::class, 'orderDetails'])->name('admin.order.details');
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::post('/products/default-category', [AdminController::class, 'setDefaultCatalogCategory'])->name('admin.products.default-category');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::delete('/products/{id}', [AdminController::class, 'destroyProduct'])->name('admin.products.destroy');
    Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::post('/settings/toggle-site', [AdminController::class, 'toggleSiteOffline'])->name('admin.settings.toggle-site');
    Route::post('/settings/toggle-loyalty', [AdminController::class, 'toggleLoyaltyOffline'])->name('admin.settings.toggle-loyalty');
    Route::post('/settings/upload-logo', [AdminController::class, 'uploadBrandLogo'])->name('admin.settings.upload-logo');
    Route::get('/stats', [AdminController::class, 'getStats'])->name('admin.stats');
    Route::post('/turnos/toggle-skip', [AdminController::class, 'toggleSkipTurnoSelection'])->name('admin.turnos.toggle-skip');

    // Rutas para gestión de aderezos y dips
    Route::post('/sauces', [AdminController::class, 'storeSauce'])->name('admin.sauces.store');
    Route::put('/sauces/{id}', [AdminController::class, 'updateSauce'])->name('admin.sauces.update');
    Route::delete('/sauces/{id}', [AdminController::class, 'destroySauce'])->name('admin.sauces.destroy');

    // Rutas para gestión de extras
    Route::post('/extras', [AdminController::class, 'storeExtra'])->name('admin.extras.store');
    Route::put('/extras/{id}', [AdminController::class, 'updateExtra'])->name('admin.extras.update');
    Route::delete('/extras/{id}', [AdminController::class, 'destroyExtra'])->name('admin.extras.destroy');

    // Rutas para gestión de configuraciones de productos
    Route::post('/product-configurations', [AdminController::class, 'storeProductConfiguration'])->name('admin.product-configurations.store');
    Route::put('/product-configurations/{id}', [AdminController::class, 'updateProductConfiguration'])->name('admin.product-configurations.update');
    Route::patch('/product-configurations/{id}/toggle', [AdminController::class, 'toggleProductConfiguration'])->name('admin.product-configurations.toggle');
    Route::delete('/product-configurations/{id}', [AdminController::class, 'destroyProductConfiguration'])->name('admin.product-configurations.destroy');

    Route::get('/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
    Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('admin.coupons.store');
    Route::post('/coupons/bulk-delete', [AdminController::class, 'bulkDeleteCoupons'])->name('admin.coupons.bulk-delete');
    Route::get('/coupons/{id}', [AdminController::class, 'getCoupon'])->name('admin.coupons.get');
    Route::put('/coupons/{id}', [AdminController::class, 'updateCoupon'])->name('admin.coupons.update');
    Route::delete('/coupons/{id}', [AdminController::class, 'destroyCoupon'])->name('admin.coupons.destroy');

    Route::get('/loyalty', [AdminLoyaltyController::class, 'index'])->name('admin.loyalty.index');
    Route::put('/loyalty/settings', [AdminLoyaltyController::class, 'updateSettings'])->name('admin.loyalty.settings.update');
    Route::post('/loyalty/redemptions/{id}/approve', [AdminLoyaltyController::class, 'approveRedemption'])->name('admin.loyalty.redemptions.approve');
    Route::post('/loyalty/redemptions/{id}/deliver', [AdminLoyaltyController::class, 'deliverRedemption'])->name('admin.loyalty.redemptions.deliver');

    // Rutas para gestión de reseñas (antiguo - para reseñas de la web)
    Route::get('/reviews', [ReviewController::class, 'index'])->name('admin.reviews');

    // Rutas para gestión de reseñas de productos
    Route::get('/product-reviews', [AdminProductReviewController::class, 'index']);
    Route::get('/product-reviews/{review}', [AdminProductReviewController::class, 'show']);
    Route::post('/product-reviews/{review}/approve', [AdminProductReviewController::class, 'approve']);
    Route::post('/product-reviews/{review}/reject', [AdminProductReviewController::class, 'reject']);
    Route::delete('/product-reviews/{review}', [AdminProductReviewController::class, 'destroy']);
    Route::get('/product-reviews/stats', [AdminProductReviewController::class, 'getStats']);
    Route::get('/product-review-reports', [AdminProductReviewController::class, 'reports']);
    Route::post('/product-review-reports/{report}/review', [AdminProductReviewController::class, 'reviewReport']);
});

// Laboratorio BLStudio · gestión (solo developer)
Route::prefix('admin/laboratorio/gestionar')->middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/', [App\Http\Controllers\LaboratorioAdminController::class, 'index'])->name('laboratorio.admin.index');
    Route::get('/pedidos', [App\Http\Controllers\LaboratorioAdminController::class, 'orders'])->name('laboratorio.admin.orders');
    Route::post('/pedidos/{labOrder}/confirmar-pago', [App\Http\Controllers\LaboratorioAdminController::class, 'confirmarPago'])->name('laboratorio.admin.confirmar-pago');
    Route::post('/pedidos/{labOrder}/items/{labOrderItem}/activar', [App\Http\Controllers\LaboratorioAdminController::class, 'activarItem'])->name('laboratorio.admin.activar-item');
    Route::get('/configuracion', [App\Http\Controllers\LaboratorioAdminController::class, 'configuracion'])->name('laboratorio.admin.config');
    Route::put('/configuracion', [App\Http\Controllers\LaboratorioAdminController::class, 'updateConfiguracion'])->name('laboratorio.admin.config.update');

    // Changelog
    Route::get('/changelog', [App\Http\Controllers\LaboratorioAdminController::class, 'changelogIndex'])->name('laboratorio.admin.changelog');
    Route::post('/changelog', [App\Http\Controllers\LaboratorioAdminController::class, 'changelogStore'])->name('laboratorio.admin.changelog.store');
    Route::delete('/changelog/{entry}', [App\Http\Controllers\LaboratorioAdminController::class, 'changelogDestroy'])->name('laboratorio.admin.changelog.destroy');

    // Bundles
    Route::get('/bundles', [App\Http\Controllers\LaboratorioAdminController::class, 'bundlesIndex'])->name('laboratorio.admin.bundles');
    Route::get('/bundles/nuevo', [App\Http\Controllers\LaboratorioAdminController::class, 'bundleCreate'])->name('laboratorio.admin.bundles.create');
    Route::post('/bundles', [App\Http\Controllers\LaboratorioAdminController::class, 'bundleStore'])->name('laboratorio.admin.bundles.store');
    Route::get('/bundles/{labBundle}/editar', [App\Http\Controllers\LaboratorioAdminController::class, 'bundleEdit'])->name('laboratorio.admin.bundles.edit');
    Route::put('/bundles/{labBundle}', [App\Http\Controllers\LaboratorioAdminController::class, 'bundleUpdate'])->name('laboratorio.admin.bundles.update');
    Route::delete('/bundles/{labBundle}', [App\Http\Controllers\LaboratorioAdminController::class, 'bundleDestroy'])->name('laboratorio.admin.bundles.destroy');

    // Créditos
    Route::get('/creditos', [App\Http\Controllers\LaboratorioAdminController::class, 'creditsIndex'])->name('laboratorio.admin.credits');
    Route::post('/creditos/otorgar', [App\Http\Controllers\LaboratorioAdminController::class, 'creditsGrant'])->name('laboratorio.admin.credits.grant');

    // Mejoras (al final por wildcard)
    Route::get('/nueva', [App\Http\Controllers\LaboratorioAdminController::class, 'create'])->name('laboratorio.admin.create');
    Route::post('/', [App\Http\Controllers\LaboratorioAdminController::class, 'store'])->name('laboratorio.admin.store');
    Route::get('/{labImprovement}/editar', [App\Http\Controllers\LaboratorioAdminController::class, 'edit'])->name('laboratorio.admin.edit');
    Route::put('/{labImprovement}', [App\Http\Controllers\LaboratorioAdminController::class, 'update'])->name('laboratorio.admin.update');
    Route::patch('/{labImprovement}/estado', [App\Http\Controllers\LaboratorioAdminController::class, 'toggleEstado'])->name('laboratorio.admin.toggle-estado');
    Route::delete('/{labImprovement}', [App\Http\Controllers\LaboratorioAdminController::class, 'destroy'])->name('laboratorio.admin.destroy');
});

// Laboratorio BLStudio · cliente (developer + admin)
Route::prefix('admin/laboratorio')->middleware(['auth', 'role:developer,admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\LaboratorioController::class, 'index'])->name('laboratorio.index');
    Route::get('/historial', [App\Http\Controllers\LaboratorioController::class, 'historial'])->name('laboratorio.historial');
    Route::get('/wishlist', [App\Http\Controllers\LaboratorioController::class, 'wishlist'])->name('laboratorio.wishlist');
    Route::post('/wishlist/toggle', [App\Http\Controllers\LaboratorioController::class, 'toggleWishlist'])->name('laboratorio.wishlist.toggle');
    Route::post('/bundle/{labBundle}/comprar', [App\Http\Controllers\LaboratorioController::class, 'comprarBundle'])->name('laboratorio.bundle.comprar');
    Route::post('/orden', [App\Http\Controllers\LaboratorioController::class, 'crearOrden'])->name('laboratorio.orden.store');
    Route::post('/orden/{labOrder}/whatsapp-enviado', [App\Http\Controllers\LaboratorioController::class, 'marcarWhatsappEnviado'])->name('laboratorio.orden.whatsapp');
    Route::post('/idea', [App\Http\Controllers\LaboratorioController::class, 'proponerIdea'])->name('laboratorio.idea');
});

// Gestión de usuarios (solo developer y admin)
Route::prefix('admin/users')->middleware(['auth', 'role:developer,admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/nuevo', [App\Http\Controllers\UserManagementController::class, 'create'])->name('admin.users.create');
    Route::post('/', [App\Http\Controllers\UserManagementController::class, 'store'])->name('admin.users.store');
    Route::get('/{user}/editar', [App\Http\Controllers\UserManagementController::class, 'edit'])->name('admin.users.edit');
    Route::put('/{user}', [App\Http\Controllers\UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/{user}', [App\Http\Controllers\UserManagementController::class, 'destroy'])->name('admin.users.destroy');
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

// Rutas para sistema de turnos
Route::prefix('api/turnos')->group(function () {
    Route::get('/config', [TurnoController::class, 'getConfig']);
    Route::get('/disponibles', [TurnoController::class, 'getDisponibles']);
    Route::post('/disponibles', [TurnoController::class, 'getDisponibles']);
    Route::post('/verificar', [TurnoController::class, 'verificarDisponibilidad']);
    Route::get('/estadisticas', [TurnoController::class, 'getEstadisticas']);
});

// Rutas admin para gestión de turnos
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/turnos', [AdminController::class, 'turnos'])->name('admin.turnos');
    Route::post('/turnos/weekly-config', [AdminController::class, 'updateWeeklyConfig']);
    Route::post('/turnos/generar', [AdminController::class, 'generateMicroturnos']);
    Route::post('/turnos/regenerar-hoy', [AdminController::class, 'regenerarMicroturnosHoy']);
    // Mantener rutas antiguas para compatibilidad
    Route::post('/turnos/config', [TurnoController::class, 'updateConfig']);
});

// Desactivar scaffolding duplicado
