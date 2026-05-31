# BRIEF COMPLETO — Laboratorio BLStudio
## Para implementar en tcocina.org (Laravel 12 + PHP 8.2)
**Fecha:** Mayo 2026 | **Dev:** Bruno Bettiga | **Cliente:** Tomas

---

## 1. QUÉ ES ESTO

Una sección nueva dentro del panel admin de tcocina llamada **"Laboratorio"**.
Es el canal oficial entre Bruno (developer) y Tomas (cliente) para gestionar,
mostrar y cobrar mejoras de la web de forma ordenada y visual.

Tomas entra a su admin de siempre, ve un nuevo ícono en el sidebar, y al
entrar encuentra una experiencia visualmente completamente diferente al resto
del panel — estética oscura, tipografía display, badges de colores, sensación
"cyberpunk / BLStudio". Es intencionalmente distinto para que se sienta como
algo especial, no como una página más del admin.

---

## 2. STACK Y CONTEXTO DEL PROYECTO

- Laravel 12 + PHP 8.2+
- Blade templates (SSR) — sin Livewire, sin Inertia
- TailwindCSS v4 + Bootstrap 5 (legacy admin)
- Vite 7, Axios, vanilla JS
- SQLite (dev) / MySQL (prod)
- Auth: Laravel UI (ya implementado)
- Storage: intervention/image-laravel (ya configurado)
- Sin tests activos
- Timezone: America/Argentina/Buenos_Aires
- Moneda: pesos argentinos

**IMPORTANTE:** No romper nada existente. El Laboratorio es una feature
completamente aditiva. Cero modificaciones al core de pedidos, cocina, etc.

---

## 3. SISTEMA DE ROLES (prerequisito del Laboratorio)

Antes de implementar el Laboratorio hay que implementar el sistema de roles,
porque el Laboratorio tiene permisos exclusivos del rol Developer.

### 3.1 Roles definidos

| Rol | Nivel | Descripción |
|-----|-------|-------------|
| `developer` | 1 (máximo) | Bruno. Acceso total + cargar mejoras al Lab |
| `admin` | 2 | Tomas. Acceso total al negocio excepto cargar mejoras |
| `cajero` | 3 | El chico que opera la PC. Permisos configurables |
| `kitchen` | 4 | Ya existe — solo vista de cocina |

### 3.2 Reglas del sistema de roles

- El Developer tiene TODO sin excepción, incluyendo la carga de mejoras
  al Laboratorio (única feature exclusiva del Developer por ahora).
- El Admin tiene todo EXCEPTO cargar/editar mejoras en el Laboratorio
  (puede verlas, comprarlas, proponer ideas, pero no cargarlas).
- Los permisos de roles por debajo del Admin (cajero, kitchen, etc.)
  son **configurables y editables por Admin y Developer** desde un panel
  de gestión de usuarios.
- Cada permiso es un checkbox seleccionable: ver pedidos, cambiar estados,
  ver productos, editar productos, ver cupones, ver lealtad, etc.
- La sección de gestión de usuarios/roles es accesible SOLO por Admin y Developer.

### 3.3 Migraciones necesarias

```
- Agregar columna `role` a tabla `users` (enum: developer, admin, cajero, kitchen)
- Crear tabla `role_permissions` (role, permission_key, allowed boolean)
- Crear tabla `users` ya existe — solo agregar columna role
```

### 3.4 Middleware

Crear middleware `CheckRole` y `CheckPermission` para proteger rutas.
Aplicar a todas las rutas de admin existentes según corresponda.

### 3.5 Vista de gestión de usuarios

Ruta: `/admin/users`
Acceso: Admin + Developer únicamente
Funciones:
- Listar usuarios del sistema
- Crear nuevo usuario (nombre, email, password, rol)
- Editar permisos granulares por usuario (checkboxes)
- El Developer no puede ser degradado ni editado por el Admin

---

## 4. LABORATORIO — ESTRUCTURA GENERAL

### 4.1 Acceso

- Ruta: `/admin/laboratorio`
- Nuevo ícono en el sidebar del admin (ícono de matraz/laboratorio)
- Visible para todos los roles (cada uno ve lo que le corresponde)
- Solo Developer puede acceder a la carga/edición de mejoras

### 4.2 Estética (MUY IMPORTANTE)

El Laboratorio usa el mismo sidebar del admin de tcocina PERO todo el
contenido dentro tiene una estética completamente diferente:

- Fondo: `#0e0d0c` (casi negro)
- Superficies de cards: `#1a1814` / `#201e1b`
- Tipografía display: Syne (800) para títulos y números
- Tipografía mono: DM Mono para datos, precios, labels
- Tipografía body: DM Sans / Instrument Sans
- Sistema de color semántico con badges:
  - Amber `#f5a623` — precio, destacado, nuevo
  - Green `#3ecf8e` — activo, incluido, confirmado
  - Blue `#38b6ff` — preview, información, UX
  - Purple `#a78bfa` — performance, técnico
  - Red `#ff4c0c` — acción principal, brand BLStudio
- Borders sutiles: `rgba(255,255,255,0.07)`
- Sin bordes blancos, sin fondos claros, todo en capas oscuras

Debe sentirse como entrar a otro sistema — "cyberpunk / BLStudio" —
mientras el sidebar de tcocina sigue siendo el de siempre.

### 4.3 Fuentes a cargar

```html
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
```

---

## 5. VISTAS DEL LABORATORIO

### 5.1 Vista principal — Lo que ve Tomas (Admin)

**Hero section:**
- Saludo "Hola Tomas" con eyebrow label
- Título "El Laboratorio de tu web" en Syne 800
- Descripción breve del concepto
- Stats en cards: mejoras activas (las que ya compró y están live),
  mejoras nuevas disponibles

**Banner de mejora destacada (featured):**
- La mejora marcada como destacada por el Developer
- Ocupa ancho completo, visual más grande
- Badge "NUEVO" si fue cargada en los últimos 7 días
- Precio grande, botón "Ver preview" y "Agregar"

**Tabs de filtro:**
- Todas / Visual / UX / Performance / Admin

**Grid de mejoras:**
- Cards con: ícono, nombre, descripción corta, badges de categoría,
  indicador "NUEVO" si aplica, precio en USD, botones "Preview" y "Agregar"
- Las mejoras ya compradas y activas muestran badge "✓ Activa" en vez de precio
- Las mejoras en proceso muestran badge "⚙ En proceso"

### 5.2 Modal de previsualización

Toggle "Antes / Después" con dos imágenes:
- ANTES: captura real de cómo está la web hoy
- DESPUÉS: captura real de cómo quedaría con la mejora

El Developer sube estas imágenes al cargar la mejora.
El modal muestra las imágenes con transición suave al togglear.

Además del toggle de imágenes, el modal muestra:
- Lista de diferencias/beneficios con dots de colores
- Precio con "pago único · publicado en 24 hs"
- Botón "Quiero esto →" que agrega al carrito

### 5.3 Sistema de carrito

- Carrito flotante sticky en el bottom que aparece al agregar la primera mejora
- Muestra: cantidad de mejoras, total en USD, botón "Ver pedido"
- Al hacer click abre el modal de resumen del pedido

### 5.4 Notas por mejora

Dentro del carrito, cada ítem tiene botón "✏ Agregar nota":
- Abre un textarea debajo del ítem con animación suave
- Placeholder: "Ej: me gustaría que esta función sea de forma X en vez de Y..."
- Máximo 400 caracteres con contador
- Al guardar, muestra preview resumida de la nota en azul debajo del ítem
- La nota viaja junto al pedido

### 5.5 Modal de resumen y pago

Al confirmar el carrito, modal con:
- Listado de mejoras seleccionadas con sus notas
- Total en USD
- Datos de transferencia de Bruno (hardcodeados o configurables)
- Upload de comprobante: input de imagen, se sube al storage de Laravel,
  genera URL pública en `/storage/comprobantes/`
- Botón principal: **"Enviar pedido por WhatsApp →"**
  Genera URL `wa.me/[NUMERO]?text=[MENSAJE_ENCODED]` con el pedido completo:
  nombre de cada mejora, notas, total USD, y link al comprobante subido
- Badge de garantía: "⚡ En menos de 24 hs queda publicado"

**Formato del mensaje de WhatsApp generado:**
```
🔬 Nuevo pedido — Laboratorio TCocina

📋 Mejoras solicitadas:
• [Nombre mejora] — USD [precio]
  ↳ "[nota si existe]"
• [Nombre mejora] — USD [precio]

💰 Total: USD [total]
📎 Comprobante: [link al storage]

✅ Transferencia realizada — esperando confirmación
```

### 5.6 Historial / Estado de pedidos

Sección dentro del Laboratorio donde Tomas ve:
- Sus pedidos anteriores con estado: Pendiente pago / Confirmado / En proceso / Activo
- Las mejoras ya activas con fecha de activación
- No puede editar nada, solo visualizar

### 5.7 Proponer una idea (Tomas → Bruno)

Botón secundario "💡 Proponer una idea" en la vista principal.
Abre un modal con:
- Campo de texto libre: "¿Qué te gustaría mejorar en tu web?"
- Envía por email a Bruno Y genera mensaje de WhatsApp pre-armado
- En fase 2: aparece en el panel de Bruno como solicitud pendiente de precio

---

## 6. VISTA DEL DEVELOPER (Bruno) — Carga de mejoras

Ruta: `/admin/laboratorio/gestionar`
Solo accesible por rol `developer`

### 6.1 Lista de mejoras cargadas

Tabla con: nombre, categoría, precio, estado (borrador/publicada), destacada sí/no,
fecha de carga, acciones (editar, archivar)

### 6.2 Formulario de nueva mejora

Campos:
- `nombre` — string, visible para Tomas
- `descripcion_corta` — 1 línea, visible en la card
- `descripcion_larga` — visible en el modal de preview
- `categoria` — enum: visual, ux, performance, admin
- `precio_usd` — decimal
- `icono` — emoji o selector
- `es_destacada` — boolean (solo una puede ser destacada a la vez)
- `estado` — enum: borrador, publicada, archivada
- `imagen_antes` — upload, guardada en storage
- `imagen_despues` — upload, guardada en storage
- `diferencias` — JSON array de bullets con color y texto
  (ej: [{color: "#3ecf8e", texto: "Combo incluido bien visible"}])
- `cliente_id` — FK a users (para cuando sea multi-cliente)
- `es_nueva` — auto: true si fue creada hace menos de 7 días

### 6.3 Tabla en BD

```
lab_improvements
  id
  cliente_id (FK users)
  nombre
  descripcion_corta
  descripcion_larga
  categoria (enum)
  precio_usd
  icono
  es_destacada (boolean)
  estado (enum: borrador, publicada, archivada)
  imagen_antes (path)
  imagen_despues (path)
  diferencias (JSON)
  created_at
  updated_at

lab_orders
  id
  cliente_id (FK users)
  estado (enum: pendiente_pago, confirmado, en_proceso, activo, cancelado)
  total_usd
  comprobante_path
  whatsapp_enviado_at
  confirmado_at
  activado_at
  created_at
  updated_at

lab_order_items
  id
  lab_order_id (FK)
  lab_improvement_id (FK)
  precio_usd (snapshot del precio al momento de compra)
  nota (text, nullable)
  created_at
```

---

## 7. NOTIFICACIONES

### 7.1 Email a Bruno cuando llega un pedido

Usar el sistema de mail ya configurado en tcocina (SMTP vía .env).
Mail simple con: cliente, mejoras pedidas, notas, total, link al comprobante.

### 7.2 Notificación en el panel de admin

Cuando llega un nuevo pedido de lab, aparece en la campanita de
notificaciones del admin (sistema `UserNotification` ya existe en el proyecto).
El mensaje debe ser llamativo y diferenciado visualmente.

### 7.3 WhatsApp (sin API)

Ver sección 5.5 — el cliente genera y envía el mensaje, no hay automatización
del lado del servidor. Cero dependencias externas.

---

## 8. RUTAS A CREAR

```php
// Laboratorio — cliente (Admin ve esto)
Route::prefix('admin/laboratorio')->middleware(['auth', 'role:developer,admin,cajero'])->group(function () {
    Route::get('/', [LaboratorioController::class, 'index']);           // catálogo
    Route::get('/historial', [LaboratorioController::class, 'historial']); // pedidos de Tomas
    Route::post('/orden', [LaboratorioController::class, 'crearOrden']); // confirmar pedido
    Route::post('/comprobante', [LaboratorioController::class, 'subirComprobante']); // upload
    Route::post('/idea', [LaboratorioController::class, 'proponerIdea']); // proponer mejora
});

// Laboratorio — developer (solo Bruno)
Route::prefix('admin/laboratorio/gestionar')->middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/', [LaboratorioAdminController::class, 'index']);
    Route::get('/nueva', [LaboratorioAdminController::class, 'create']);
    Route::post('/', [LaboratorioAdminController::class, 'store']);
    Route::get('/{id}/editar', [LaboratorioAdminController::class, 'edit']);
    Route::put('/{id}', [LaboratorioAdminController::class, 'update']);
    Route::patch('/{id}/estado', [LaboratorioAdminController::class, 'toggleEstado']);
});

// Gestión de usuarios y roles
Route::prefix('admin/users')->middleware(['auth', 'role:developer,admin'])->group(function () {
    Route::get('/', [UserManagementController::class, 'index']);
    Route::get('/nuevo', [UserManagementController::class, 'create']);
    Route::post('/', [UserManagementController::class, 'store']);
    Route::get('/{id}/editar', [UserManagementController::class, 'edit']);
    Route::put('/{id}', [UserManagementController::class, 'update']);
    Route::delete('/{id}', [UserManagementController::class, 'destroy']);
});
```

---

## 9. CONTROLADORES A CREAR

```
app/Http/Controllers/
  LaboratorioController.php        ← vista cliente (Tomas)
  LaboratorioAdminController.php   ← carga de mejoras (Bruno/Developer)
  UserManagementController.php     ← gestión de usuarios y roles
```

---

## 10. MODELOS A CREAR

```
app/Models/
  LabImprovement.php    ← mejoras del laboratorio
  LabOrder.php          ← pedidos de mejoras
  LabOrderItem.php      ← ítems de cada pedido con nota
```

---

## 11. MIDDLEWARE A CREAR

```
app/Http/Middleware/
  CheckRole.php         ← verifica rol del usuario
  CheckPermission.php   ← verifica permiso granular
```

Registrar en `bootstrap/app.php` (Laravel 12).

---

## 12. NÚMERO DE WHATSAPP

Configurable vía `BusinessSetting` con key `developer_whatsapp`.
Valor por defecto a definir al momento de implementar.

---

## 13. PROMPT DE CIERRE DE SESIÓN (para usar en Claude)

Al terminar una sesión de trabajo en Windsurf/Claude, correr este prompt
para generar automáticamente las mejoras listas para cargar al Laboratorio:

```
Sos el asistente de BLStudio, agencia de desarrollo web.

Acabo de terminar una sesión de trabajo. Esto es lo que hice:
[PEGAR RESUMEN LIBRE DE LA SESIÓN O GIT DIFF]

Para cada cosa que hice, analizá y respondé SOLO con un JSON array con este formato:
[
  {
    "nombre": "nombre claro para el cliente (no técnico)",
    "descripcion_corta": "una línea, el valor para el cliente",
    "descripcion_larga": "2-3 líneas explicando qué cambia y por qué es mejor",
    "categoria": "visual|ux|performance|admin",
    "precio_usd": número sugerido,
    "icono": "emoji representativo",
    "es_cobrable": true|false,
    "razon": "por qué es cobrable o por qué no (fix interno, prometido, etc)",
    "diferencias": [
      {"color": "#3ecf8e", "texto": "beneficio concreto 1"},
      {"color": "#38b6ff", "texto": "beneficio concreto 2"}
    ],
    "tipo_preview": "antes-despues|simulacion|metrica"
  }
]

Criterios de precio:
- Simple (UI, texto, config): USD 15-30
- Media (nueva sección, flujo): USD 40-70  
- Compleja (sistema nuevo, integración): USD 80-150

Solo incluir las que son mejoras reales para el cliente.
Fixes de bugs que vos rompiste = no cobrable.
Fixes de cosas prometidas y no entregadas = no cobrable.
```

---

## 14. FLUJO COMPLETO RESUMIDO

```
BRUNO                                    TOMAS
──────────────────────────────────────────────────────

Trabaja en mejoras
↓
Corre prompt de cierre en Claude
↓
Claude genera JSON clasificado
↓
Bruno revisa y ajusta (2 min)
↓
Carga mejoras en /admin/laboratorio/gestionar
con imágenes antes/después
↓
                              Tomas entra a su admin
                              ↓
                              Ve sección "Laboratorio" en sidebar
                              ↓
                              Ve catálogo de mejoras disponibles
                              ↓
                              Previsualiza con toggle antes/después
                              ↓
                              Agrega mejoras al carrito
                              ↓
                              Deja notas por mejora si quiere
                              ↓
                              Sube comprobante de transferencia
                              → se guarda en storage
                              → genera link público
                              ↓
                              Toca "Enviar por WhatsApp →"
                              → abre su WhatsApp con mensaje listo
                              → manda a Bruno
↓
Bruno recibe WhatsApp con
pedido completo + link al comprobante
↓
Bruno confirma pago en el panel
↓
Implementa con las notas de Tomas en mano
↓
Publica en producción
↓
Marca como "Activa" en el panel
↓
                              Tomas ve la mejora como "✓ Activa"
                              en su Laboratorio
```

---

## 15. LO QUE NO ENTRA EN FASE 1

- CRM externo en stgrandesligas.com (fase 2)
- Multi-tenant / múltiples clientes (fase 2)
- API entre dominios para sincronización de estados (fase 2)
- Mercado Pago o pago automático (fase 3)
- Métricas de facturación (fase 3)

---

## 16. NOTAS CRÍTICAS PARA NO ROMPER NADA

1. Todo es ADITIVO — no modificar controladores existentes
2. Las migraciones de roles deben tener default para usuarios existentes
   (Tomas = admin, usuario cocina = kitchen)
3. El middleware de roles debe respetar que `kitchen` ya existe y funciona
4. Los assets del Laboratorio (fuentes Google, estilos propios) solo cargan
   en las rutas del Laboratorio, no en todo el admin
5. El storage de comprobantes debe estar en .gitignore si no lo está ya
6. La columna `role` en users reemplaza el campo `is_admin` existente —
   migrar con cuidado para no romper el acceso de Tomas

---

*Brief generado en sesión de diseño — Mayo 2026*
*BLStudio × TCocina*
