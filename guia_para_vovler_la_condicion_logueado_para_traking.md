# Guía: Activar login obligatorio para el seguimiento de pedidos

## Contexto

El seguimiento de pedidos (`/pedido/{code}/seguimiento`) fue hecho **público** intencionalmente
en la primera etapa del proyecto para facilitar la adopción. El código para requerir login
ya existía y fue desactivado — esta guía explica exactamente cómo volver a activarlo.

**Fecha en que se desactivó:** Mayo 2025  
**Motivo:** El admin quería que todos pudieran trackear su pedido mientras "educaban" a los
clientes a registrarse. El plan es activarlo más adelante como incentivo de registro.

---

## Archivos a modificar (4 cambios)

### 1. `routes/web.php`

Mover las rutas de tracking de vuelta al grupo `auth`. Actualmente están fuera:

```php
// ESTADO ACTUAL (público):
Route::get('/pedido/{orderNumber}/seguimiento', [OrderController::class, 'tracking'])->name('orders.tracking');
Route::get('/mi-pedido', [OrderController::class, 'myLatestTracking'])->name('orders.my-latest');

// En el bloque api/ (también público):
Route::get('/pedido/{orderNumber}/estado', [OrderController::class, 'getStatus'])->name('orders.status');
```

Cambiar a — mover dentro del grupo `Route::middleware('auth')->group(...)`:

```php
// DENTRO del bloque Route::middleware('auth')->group(function () { ... }):
Route::get('/pedido/{orderNumber}/seguimiento', [OrderController::class, 'tracking'])->name('orders.tracking');
Route::get('/mi-pedido', [OrderController::class, 'myLatestTracking'])->name('orders.my-latest');

// Y dentro del bloque api/ con auth:
// Route::middleware('auth')->group(function () { dentro de api/
Route::get('/pedido/{orderNumber}/estado', [OrderController::class, 'getStatus'])->name('orders.status');
```

---

### 2. `app/Http/Controllers/OrderController.php` — método `tracking()`

**Estado actual (público):** busca el pedido solo por `order_number`, sin filtrar por usuario.
**Objetivo:** filtrar por usuario logueado y asumir que wallet/setting siempre existen.

```php
// CAMBIAR ESTO:
public function tracking($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)
        ->with(['items.product', 'address'])
        ->firstOrFail();

    $wallet  = Auth::user()?->loyaltyWallet;
    $setting = LoyaltySetting::first();

    return view('orders.tracking', compact('order', 'wallet', 'setting'));
}

// POR ESTO:
public function tracking($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)
        ->where('user_id', Auth::id())          // <-- re-agregar esta línea
        ->with(['items.product', 'address'])
        ->firstOrFail();

    $wallet  = Auth::user()->loyaltyWallet;     // <-- sin ?-> (ya sabemos que está logueado)
    $setting = LoyaltySetting::first();

    return view('orders.tracking', compact('order', 'wallet', 'setting'));
}
```

---

### 3. `app/Http/Controllers/OrderController.php` — método `getStatus()`

```php
// CAMBIAR ESTO:
public function getStatus($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)
        ->firstOrFail();

    $wallet  = Auth::user()?->loyaltyWallet;
    $setting = LoyaltySetting::first();
    // ...resto igual

// POR ESTO:
public function getStatus($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)
        ->where('user_id', Auth::id())          // <-- re-agregar
        ->firstOrFail();

    $wallet  = Auth::user()->loyaltyWallet;     // <-- sin ?->
    $setting = LoyaltySetting::first();
    // ...resto igual
```

---

### 4. `app/Http/Controllers/OrderController.php` — método `myLatestTracking()`

Este método también cambió. Hay que revertirlo a la versión simple (sin el `if Auth::check()`):

```php
// CAMBIAR ESTO (versión pública):
public function myLatestTracking()
{
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

// POR ESTO (versión con auth requerido):
public function myLatestTracking()
{
    $order = Order::where('user_id', Auth::id())
        ->whereNotIn('status', ['delivered', 'cancelled'])
        ->latest()
        ->first();

    if ($order) {
        return redirect()->route('orders.tracking', ['orderNumber' => $order->order_number]);
    }

    return view('orders.my-latest');
}
```

(La ruta en el grupo `auth` garantiza que `Auth::id()` siempre tiene valor.)

---

## Verificar después de activar

1. Entrar al tracker con una URL directa sin estar logueado → debe redirigir a `/login`
2. Después de loguearse → debe volver al tracker correctamente (Laravel guarda la URL intentada)
3. El botón "Seguir mi pedido" en el menú del usuario sigue funcionando (ese flow ya requería login)
4. El overlay de in-app browser sigue activo — esto es deseable porque explica por qué redirige a login

## Lo que NO hay que tocar

- `resources/views/orders/tracking.blade.php` — la vista no cambia
- `resources/views/orders/my-latest.blade.php` — la vista de "sin pedidos" sigue siendo útil
- El overlay de in-app browser en `app.blade.php` y `login.blade.php` — sigue siendo útil
- El link "Seguir mi pedido" en el fab del usuario — ya estaba dentro de `@auth`
