# PROJECT_CONTEXT.md — TCocina

Contexto completo para retomar el trabajo sin leer el código. Fecha de escritura: 2026-05-24.

---

## Qué estamos construyendo

**TCocina** es una app web de pedidos online para un restaurante de hamburguesas artesanales. Los clientes navegan el catálogo, arman su pedido con configuraciones (medallones, aderezos, extras, salsas), eligen un turno horario si aplica, pagan y siguen el estado en tiempo real. El negocio gestiona todo desde un panel de admin y una pantalla de cocina.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 + PHP 8.2+ |
| Frontend | Blade templates (SSR) |
| CSS | TailwindCSS v4 + Bootstrap 5 |
| JS | Vite 7, Axios, vanilla JS |
| BD (dev) | SQLite (`database/database.sqlite`) |
| BD (prod) | MySQL |
| Auth | Laravel UI + Google OAuth (Socialite) |
| Imágenes | intervention/image-laravel |
| Correos | SMTP configurable vía .env |
| Assets | Compilados con Vite (`public/build/`) |

---

## Estructura de carpetas clave

```
app/
  Http/Controllers/
    AdminController.php          ← panel admin: pedidos, productos, config, turnos
    AdminLoyaltyController.php   ← canjes y configuración de fidelidad
    AdminProductReviewController.php ← moderación de reseñas de productos
    KitchenController.php        ← vista cocina
    OrderController.php          ← flujo del cliente: carrito → turnos → checkout → confirmación
    ProductController.php        ← catálogo público
    TurnoController.php          ← API de turnos/microturnos
    LoyaltyController.php        ← dashboard "mi-progreso" del cliente
    ProductReviewController.php  ← reseñas de productos (cliente)
    ReviewController.php         ← sistema de reseñas genérico (legado)
    NotificationController.php   ← campanita de notificaciones
    UserProfileController.php    ← perfil + direcciones guardadas

  Models/
    Order.php             ← pedidos, estados, timestamps por estado
    OrderItem.php         ← ítems con configuration_data (JSON)
    Product.php           ← productos con sort_order, is_available, review_stats
    Category.php
    DynamicMicroturno.php ← NO es Eloquent; clase PHP que calcula microturnos on-the-fly
    WeeklyTurnoConfig.php ← configuración por día de la semana
    ProductConfiguration.php ← aderezos, dips, extras, medallones (global, no por producto)
    Sauce.php / Extra.php
    Coupon.php            ← cupones con soft-deletes
    LoyaltySetting.php / UserLoyaltyWallet.php / UserLoyaltyMovement.php / LoyaltyRedemption.php
    ProductReview.php / ProductReviewImage.php / ProductReviewHistory.php / ProductReviewReport.php
    UserNotification.php
    BusinessSetting.php   ← config del negocio (key/value en BD)
    Address.php / User.php

  Services/
    LoyaltyService.php
    NotificationService.php
    ReviewModerationService.php
    ReviewNotificationService.php
    ReviewRequestService.php

resources/views/
  layouts/app.blade.php       ← layout público
  layouts/admin.blade.php     ← layout panel admin
  catalog.blade.php           ← catálogo principal
  cart.blade.php
  turnos.blade.php
  checkout.blade.php
  order-confirmation.blade.php
  orders/tracking.blade.php   ← seguimiento en tiempo real
  kitchen/index.blade.php
  kitchen/display.blade.php
  admin/dashboard.blade.php
  admin/orders.blade.php
  admin/products.blade.php
  admin/settings.blade.php
  admin/turnos.blade.php
  admin/coupons.blade.php
  admin/loyalty/index.blade.php
  admin/reviews.blade.php     ← moderación reseñas de productos
  loyalty/dashboard.blade.php ← "mi progreso" del cliente
  profile/edit.blade.php
  emails/                     ← plantillas de correo
  legal/                      ← privacy, terms, shipping, faq
  maintenance.blade.php

database/migrations/          ← historial completo de migraciones
routes/web.php                ← todas las rutas
config/loyalty.php            ← config de fidelidad
```

---

## Arquitectura general

### Flujo de pedido del cliente
1. `/` o `/catalog` → catálogo de productos
2. `/cart` → carrito (localStorage)
3. `/turnos` → selector de microturno horario (omitible con `skip_turno_selection`)
4. `/checkout` → datos de contacto, delivery/pickup, método de pago
5. `POST /orders` → crea pedido en estado `pending`
6. `/order/{orderNumber}/confirmation` → resumen
7. `/pedido/{orderNumber}/seguimiento` → tracking en tiempo real (polling AJAX)

### Sistema de microturnos
- **`WeeklyTurnoConfig`**: configuración por día (hora inicio/fin, duración, capacidad de hamburguesas y acompañamientos, habilitado/no).
- **`DynamicMicroturno`**: clase PHP (no modelo) que genera los microturnos del día on-the-fly a partir de `WeeklyTurnoConfig`. **No se persisten en BD**.
- La capacidad se calcula sumando pedidos en estados activos que caen en ese `microturno_sort_order`.
- El `microturno_sort_order` es un entero (1, 2, 3…) que identifica el slot; se guarda en la tabla `orders`.

### Estados de pedido
```
pending → confirmed → preparing → ready → on_the_way → delivered
                                                      ↓
                                                   cancelled (cualquier momento)
```
- **Estados activos** (cuentan para capacidad): `confirmed`, `preparing`, `ready`, `on_the_way`
- **Estados inactivos**: `pending`, `delivered`, `cancelled`
- `ready` y `on_the_way` fueron las últimas adiciones (migraciones 2026-05-20).

### Panel de admin
- Accesible en `/admin` con rol `admin`.
- Funciones: pedidos (cambio de estado, edición, impresión, eliminación masiva), productos CRUD, categorías, configuración del negocio, turnos, cupones, lealtad, reseñas.
- La validación de capacidad ocurre al confirmar (`pending → confirmed`): si excede, bloquea salvo `force_exceed_capacity = true`.

### Vista de cocina
- `/kitchen` y `/kitchen/display` con rol `kitchen` o `admin`.
- Solo muestra pedidos de hoy en `confirmed` o `preparing`.
- Acciones: iniciar preparación (`confirmed → preparing`), marcar listo (`preparing → ready`).
- Actualización por polling AJAX.

---

## Modelos de datos críticos

### Order
- `microturno_sort_order` (int): identifica el slot horario, no FK a tabla.
- `configuration_data` está en `OrderItem` como JSON:
  ```json
  { "aderezos": ["Mayonesa"], "extras": ["Bacon"], "medallones": 2, "tipo_medallon": "Carne" }
  ```
- Timestamps por estado: `confirmed_at`, `preparing_at`, `ready_at`, `out_for_delivery_at`, `delivered_at`.

### ProductConfiguration (global)
- Tipos: `Aderezos`, `Dips`, `Extras` (con `price_modifier`), `Medallones`, `Tipo de Medallón`.
- Son configuraciones globales del negocio, **no por producto**.

### Precios de medallones (valores actuales)
- Simple: −$2.000 | Doble: $0 (default) | Triple: +$2.500 | Cuádruple: +$5.000 | Quíntuple: +$7.500

### Sistema de lealtad
- `UserLoyaltyWallet`: saldo de "soles" del usuario.
- `UserLoyaltyMovement`: historial de movimientos.
- `LoyaltyRedemption`: solicitudes de canje con estados `pending`, `approved`, `delivered`.
- Se acreditan soles al confirmar pedidos desde el admin.
- Configuración en `LoyaltySetting` y `config/loyalty.php`.

### Cupones
- `Coupon` con soft-deletes.
- Validación vía `POST /api/coupons/validate`.
- Campo `allow_cash_discount`: si el descuento por efectivo aplica además del cupón.

### Reseñas de productos
- `ProductReview` con moderación (`pending`, `approved`, `rejected`).
- Soporte de imágenes (`ProductReviewImage`) y reportes (`ProductReviewReport`).
- Historial de moderación (`ProductReviewHistory`).
- Notificaciones de reseña via `ReviewNotificationService`.
- El catálogo muestra `review_avg_rating` y `review_count` (columnas cacheadas en `products`).

---

## Roles de usuario

| Rol | Acceso |
|---|---|
| `developer` | Laboratorio BLStudio completo. Acceso total a features + carga de mejoras. |
| `admin` | Panel completo (pedidos, productos, settings, turnos, cupones, lealtad, reseñas), cocina, laboratorio (lectura), gestión de roles/permisos. |
| `cajero` | Permisos granulares configurables por admin. Por defecto: ver órdenes, cobrar, cambiar estado a delivered. |
| `kitchen` | Solo vista de cocina (confirmar → preparing → ready). |
| `delivery` | Manejo de reparto (enum existe; lógica en desarrollo). |
| `customer` | Pedidos, mi-progreso, reseñas de productos. |
| *(invitado)* | `guest@tcocina.local` si no hay sesión; puede hacer pedidos anónimos. |

### Cuenta admin (TCocina)
- Email: `admin@tcocina.org`
- ID: 1
- Nombre mostrado: "Juan Pruebas" (cliente de referencia)
- Rol: `admin` (acceso panel completo)

### Cuenta developer (Laboratorio BLStudio)
- Email: `blstudio@tcocina.org`
- Password: `Admin2026`
- Nombre a mostrar: BLStudio
- Rol: `developer` (Laboratorio completo)
- Datos de transferencia:
  - Alias: `biglstudio`
  - CBU: `0110383830038320470947`
  - Banco: Banco Nación
  - Titular: BLStudio (Juan Ignacio Ibarlucia)
- Email de notificaciones: `grandesligasarg@gmail.com`

---

## Laboratorio BLStudio (Fase 1)

**Ubicación**: `/admin/laboratorio` (accesible solo para roles `admin` y `developer`).

**Propósito**: Dashboard de gestión y desarrollo de BLStudio como piloto de sistema de gestión para otras cuentas futuras. TCocina es el caso de uso inicial.

### Características en Fase 1
- **Dashboard de agencia**: vista de métricas y estado general.
- **Gestión de roles y permisos**: grilla de usuarios con asignación de roles (admin, cajero, kitchen, delivery, customer) y permisos granulares por rol.
- **Datos de transferencia**: muestra alias, CBU y banco de la agencia para coordinación de pagos.
- **Logs de actividad**: auditoría de cambios en configuración y permisos (si está implementado).

### Acceso
- **`developer` (BLStudio)**: Acceso total al Laboratorio. Puede crear mejoras, cambiar configuración global, gestionar roles y permisos.
- **`admin` (TCocina)**: Acceso de lectura + permisos para gestionar usuarios locales (asignar roles, ajustar permisos del `cajero`).
- **Otros roles**: Sin acceso al Laboratorio.

### Decisiones tomadas
- El Laboratorio es **aditivo** a TCocina, no invasivo. Las mejoras se cargan como features reutilizables.
- Patrones reutilizables sin sobre-ingeniería: preferir soluciones simples y directas que funcionen para TCocina primero.
- TCocina es piloto; el Laboratorio busca descubrir patrones que escalen a otras agencias / negocios futuros.

---

## Convenciones de código

- **Laravel estándar**: PSR-4, Eloquent, Blade, rutas en `routes/web.php`.
- **Sin tests** activos (PHPUnit instalado pero sin tests de negocio escritos).
- **Configuración de negocio** via `BusinessSetting` (clave/valor en BD) + `config/loyalty.php` para lo de fidelidad.
- **Sin comentarios** a menos que el WHY sea no obvio.
- **Sin abstracciones prematuras**: lógica directa en controladores salvo Services existentes.
- **Blade puro** para vistas (sin Livewire, sin Inertia).
- **JS vanilla + Axios** para las partes interactivas (carrito, polling, modales).
- **Tailwind v4** para estilos nuevos; Bootstrap 5 para partes legacy del admin.
- **Imágenes de productos** en `public/images/products/` (hash random) o `public/storage/products/`.
- **Timezone**: `America/Argentina/Buenos_Aires`.
- **Moneda**: pesos argentinos (sin símbolo, solo número + ".- ARS" o similar).

---

## Variables de entorno importantes

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql              # sqlite en desarrollo
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=.../auth/google/callback
MAIL_MAILER=smtp
LOYALTY_WELCOME_EMAIL_ENABLED=true
LOYALTY_WELCOME_EMAIL_QUEUE=false
```

---

## Estado actual del trabajo (2026-05-24)

### Implementado y funcional
- Flujo completo de pedidos (catálogo → carrito → turnos → checkout → confirmación → tracking)
- Sistema de microturnos dinámicos con control de capacidad por hamburguesas y acompañamientos
- Panel admin completo (pedidos, productos, categorías, settings, turnos, cupones)
- Vista de cocina con display de pantalla completa
- Sistema de lealtad ("soles"): acumulación, canje, aprobación admin, emails
- Login con Google OAuth + email de bienvenida
- Cupones de descuento con soft-delete
- Sistema de reseñas de productos (moderación, imágenes, reportes)
- Notificaciones in-app (campanita)
- Perfil de usuario + gestión de direcciones guardadas
- Estados `ready` y `on_the_way` recién agregados (mayo 2026) para tracking más granular
- Seguimiento de pedido en tiempo real (`/pedido/{n}/seguimiento`)
- Descuento por pago en efectivo (15%, configurable)
- Modo offline del sitio (`site_offline` en BusinessSetting)
- Build de producción en `build/release/`
- **Laboratorio BLStudio (Fase 1)**: gestión de roles, permisos granulares para `cajero`, dashboard de agencia, datos de transferencia

### Archivos de referencia existentes
- `ANALISIS_COMPORTAMIENTO_APP.md` — análisis profundo de flujos y puntos críticos
- `BUILD-FINAL-INFO.md` — estado del último build y funcionalidades confirmadas
- `README-PRODUCCION.md` — guía de despliegue y variables de producción
- `README-ENV.md` — configuración de entornos y Google OAuth

---

## Comandos de desarrollo

```bash
# Desarrollo (servidor + queue + logs + vite en paralelo)
composer run dev

# Solo backend
php artisan serve

# Solo frontend
npm run dev

# Build producción
npm run build

# Migraciones
php artisan migrate

# Limpiar caches
php artisan optimize:clear
```

---

## Notas críticas para no romper nada

1. **`DynamicMicroturno` NO es Eloquent** — es una clase PHP normal. No busques su tabla en la BD.
2. **Los microturnos no se guardan** — `microturno_sort_order` en `orders` es solo un entero que representa el slot del día.
3. **La capacidad se recalcula on-the-fly** — cambia si cambias qué estados son "activos" (`Order::ACTIVE_STATUSES`).
4. **`configuration_data` en `OrderItem` es JSON** — cambios de estructura rompen la visualización de pedidos históricos.
5. **`ProductConfiguration` es global** — las configuraciones (aderezos, extras, etc.) aplican a todos los productos, no a uno en particular.
6. **El usuario invitado** se crea automáticamente si no hay sesión; no bloquear pedidos sin login.
7. **`skip_turno_selection`** en BusinessSetting puede estar activo — en ese caso, el flujo salta directamente de carrito a checkout.
8. **Google OAuth** requiere que `GOOGLE_REDIRECT_URI` coincida exactamente con lo configurado en Google Cloud Console.
9. **Producción usa MySQL**, desarrollo usa SQLite — no asumir comportamiento idéntico en enums/tipos.
10. Los estados `ready` y `on_the_way` son recientes — verificar que las vistas de cocina, admin y tracking los soporten al modificar lógica de estados.
