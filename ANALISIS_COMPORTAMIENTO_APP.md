# Análisis Completo del Comportamiento de la Aplicación

## 📋 Resumen Ejecutivo

Esta es una aplicación Laravel para un restaurante de hamburguesas (TECOCINA) que permite:
- **Pedidos online** con sistema de turnos/microturnos
- **Panel de administración** para gestión de pedidos, productos y configuración
- **Vista de cocina** para seguimiento de pedidos en preparación
- **Sistema de capacidad** basado en productos (hamburguesas y acompañamientos)

---

## 🏗️ Arquitectura General

### Stack Tecnológico
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade templates + Vite + TailwindCSS + Bootstrap
- **Base de datos**: SQLite (desarrollo)
- **Autenticación**: Laravel UI (sistema de roles: admin, kitchen, customer)

### Estructura de Directorios Clave
```
app/
├── Http/Controllers/     # Lógica de negocio
├── Models/               # Modelos Eloquent
└── Providers/            # Service Providers

resources/views/          # Vistas Blade
routes/web.php           # Definición de rutas
```

---

## 🔄 Flujos Principales de la Aplicación

### 1. FLUJO DE PEDIDO DEL CLIENTE

#### 1.1. Catálogo de Productos (`ProductController`)
- **Ruta**: `/` o `/catalog`
- **Comportamiento**:
  - Muestra categorías activas ordenadas
  - Lista productos disponibles ordenados por `sort_order`
  - Filtrado por categoría disponible (`/category/{slug}`)
  - Detalles de producto individual (`/product/{slug}`)

#### 1.2. Carrito de Compras (`OrderController::cart`)
- **Ruta**: `/cart`
- **Comportamiento**:
  - Verifica si el día actual está habilitado (`WeeklyTurnoConfig`)
  - Verifica si se debe saltar la selección de turno (`skip_turno_selection`)
  - Muestra el carrito con productos seleccionados

#### 1.3. Selección de Turnos (`OrderController::turnos`)
- **Ruta**: `/turnos`
- **Comportamiento**:
  - **Si `skip_turno_selection = true`**: Redirige directamente a checkout
  - **Si `skip_turno_selection = false`**:
    - Verifica que el día esté habilitado
    - Genera microturnos dinámicos para hoy (`DynamicMicroturno::generarParaFecha`)
    - Muestra todos los microturnos (disponibles y no disponibles)
    - El cliente selecciona un microturno disponible

#### 1.4. Checkout (`OrderController::checkout`)
- **Ruta**: `/checkout`
- **Comportamiento**:
  - Muestra formulario de datos de contacto
  - Opciones de entrega: `delivery` o `pickup`
  - Métodos de pago: `cash`, `card`, `transfer`
  - Si es delivery: solicita dirección
  - Si es pickup: no requiere dirección
  - Calcula descuento por pago en efectivo (`cash_discount_percentage`)

#### 1.5. Procesamiento del Pedido (`OrderController::store`)
- **Ruta**: `POST /orders`
- **Validaciones**:
  - Items del carrito (productos, cantidades)
  - Datos de contacto (nombre, teléfono, email opcional)
  - Método de pago y entrega
  - Microturno (obligatorio solo si `skip_turno_selection = false`)
- **Proceso**:
  1. Valida que el día esté habilitado
  2. Valida disponibilidad del microturno seleccionado
  3. Calcula precios:
     - Precio base del producto
     - Modificadores de configuración (extras, aderezos, dips, medallones)
     - Subtotal
     - Descuento por efectivo (si aplica)
     - Total final
  4. Crea usuario invitado si no hay sesión
  5. Crea dirección solo si es delivery
  6. Genera número de pedido único (`ORD-XXXXXXXX`)
  7. Crea pedido con estado `pending`
  8. Crea items del pedido con configuración guardada en JSON
  9. Asigna microturno (`microturno_sort_order`)
- **Respuesta**: JSON con pedido creado

#### 1.6. Confirmación (`OrderController::confirmation`)
- **Ruta**: `/order/{orderNumber}/confirmation`
- **Comportamiento**: Muestra resumen del pedido creado

---

### 2. SISTEMA DE TURNOS Y MICROTURNOS

#### 2.1. Configuración Semanal (`WeeklyTurnoConfig`)
- **Modelo**: `app/Models/WeeklyTurnoConfig.php`
- **Configuración por día de la semana**:
  - `day_of_week`: monday, tuesday, wednesday, etc.
  - `hora_inicio`: Hora de inicio del servicio
  - `hora_fin`: Hora de fin del servicio
  - `duracion_microturno_minutos`: Duración de cada microturno (5-60 min)
  - `max_hamburguesas`: Capacidad máxima de hamburguesas por microturno
  - `max_acompañamientos`: Capacidad máxima de acompañamientos por microturno
  - `is_enabled`: Si el día está habilitado
  - `is_active`: Si la configuración está activa

#### 2.2. Generación Dinámica de Microturnos (`DynamicMicroturno`)
- **Clase**: `app/Models/DynamicMicroturno.php` (NO es modelo Eloquent, es clase PHP)
- **Método clave**: `generarParaFecha($fecha)`
- **Proceso**:
  1. Obtiene configuración del día de la semana
  2. Divide el rango horario en microturnos según `duracion_microturno_minutos`
  3. Cada microturno tiene:
     - `sort_order`: Identificador único (1, 2, 3...)
     - `hora_inicio` y `hora_fin`: Rango horario
     - Capacidad calculada dinámicamente
  4. **NO se guardan en BD**, se calculan on-the-fly

#### 2.3. Cálculo de Capacidad
- **Método**: `getProductosActivos()`
- **Lógica**:
  - Busca pedidos con `microturno_sort_order` coincidente
  - Solo cuenta pedidos en estados **activos**: `confirmed`, `preparing`
  - Suma hamburguesas y acompañamientos por categoría
  - Compara con `max_hamburguesas` y `max_acompañamientos`
- **Disponibilidad**: Un microturno está disponible si:
  - `hamburguesas_activas < max_hamburguesas` Y
  - `acompañamientos_activos < max_acompañamientos`

#### 2.4. Estados de Pedido y Capacidad
- **Estados activos** (cuentan para capacidad):
  - `confirmed`: Pedido confirmado
  - `preparing`: En preparación
- **Estados inactivos** (NO cuentan):
  - `pending`: Pendiente de confirmación
  - `delivered`: Entregado
  - `cancelled`: Cancelado

---

### 3. PANEL DE ADMINISTRACIÓN

#### 3.1. Dashboard (`AdminController::dashboard`)
- **Ruta**: `/admin`
- **Funcionalidades**:
  - Estadísticas de pedidos (últimos 30 días por defecto)
  - Gráficos de pedidos e ingresos por día
  - Pedidos recientes
  - Filtro de tiempo: semana o mes

#### 3.2. Gestión de Pedidos (`AdminController::orders`)
- **Ruta**: `/admin/orders`
- **Vista dividida**:
  - **Pedidos de hoy**: Lista completa sin paginación
  - **Pedidos históricos**: Paginados (20 por página)
- **Acciones disponibles**:
  - Ver detalles
  - Imprimir ticket (`/admin/orders/{id}/print`)
  - Cambiar estado (`updateOrderStatus`)
  - Cambiar microturno (`updateOrderMicroturno`)
  - Editar detalles (nombre, teléfono, dirección, notas)
  - Eliminar pedido
  - Eliminación masiva

#### 3.3. Cambio de Estado de Pedido (`AdminController::updateOrderStatus`)
- **Validación de capacidad**:
  - Si el pedido pasa de estado inactivo → activo:
    - Calcula productos del pedido (hamburguesas y acompañamientos)
    - Suma productos ya activos en el microturno
    - Verifica si excede capacidad máxima
    - Si excede: **bloquea el cambio** (a menos que `force_exceed_capacity = true`)
- **Estados posibles**:
  - `pending` → `confirmed` → `preparing` → `delivered`
  - `cancelled` (en cualquier momento)

#### 3.4. Gestión de Productos (`AdminController::products`)
- **CRUD completo**:
  - Crear, editar, eliminar productos
  - Subir imágenes
  - Activar/desactivar disponibilidad
  - Asignar categoría

#### 3.5. Configuración del Negocio (`AdminController::settings`)
- **Configuraciones básicas**:
  - Datos del negocio (nombre, teléfono, email, dirección)
  - Redes sociales
  - Métodos de pago habilitados
  - Descuento por efectivo
  - Modo offline (`site_offline`)
- **Configuraciones de productos**:
  - **Aderezos y Dips**: Sin costo adicional
  - **Extras**: Con precio adicional
  - **Tipos de Medallón**: Configuración especial
  - **Medallones**: Cantidad de medallones
- **Sistema de turnos**:
  - Toggle para saltar selección de turno (`skip_turno_selection`)

#### 3.6. Gestión de Turnos (`AdminController::turnos`)
- **Ruta**: `/admin/turnos`
- **Funcionalidades**:
  - Configurar horarios por día de la semana
  - Ver microturnos generados para hoy
  - Ver estadísticas de ocupación
  - Ver días con turnos (últimos 7 días + próximos 30 días)
  - Toggle para activar/desactivar sistema de turnos

---

### 4. VISTA DE COCINA

#### 4.1. Vista Principal (`KitchenController::index`)
- **Ruta**: `/kitchen`
- **Comportamiento**:
  - Solo muestra pedidos de **hoy**
  - Solo pedidos en estados: `confirmed`, `preparing`
  - Agrupa pedidos por microturno
  - Muestra configuración de cada item (aderezos, extras, etc.)
  - Ordenados por hora de creación (más antiguos primero)

#### 4.2. Acciones de Cocina
- **Iniciar preparación** (`startPreparation`):
  - Cambia estado: `confirmed` → `preparing`
  - Registra `preparing_at`
- **Marcar como listo** (`markReady`):
  - Cambia estado: `preparing` → `delivered`
  - Registra `delivered_at`
  - **Libera capacidad** del microturno automáticamente

#### 4.3. Pantalla de Visualización (`KitchenController::display`)
- **Ruta**: `/kitchen/display`
- **Propósito**: Pantalla completa para mostrar en cocina
- **Actualización**: Vía AJAX periódica

---

### 5. SISTEMA DE PRODUCTOS Y CONFIGURACIONES

#### 5.1. Estructura de Productos
- **Modelo**: `Product`
- **Campos clave**:
  - `category_id`: Relación con categoría
  - `base_price`: Precio base
  - `is_available`: Disponibilidad
  - `is_featured`: Producto destacado
  - `sort_order`: Orden de visualización

#### 5.2. Configuraciones Globales (`ProductConfiguration`)
- **Modelo**: `ProductConfiguration`
- **Tipos de configuración**:
  - `Aderezos`: Sin costo
  - `Dips`: Sin costo
  - `Extras`: Con precio adicional (`price_modifier`)
  - `Medallones`: Cantidad
  - `Tipo de Medallón`: Variante (Carne, Veggie, etc.)
- **Almacenamiento en pedido**:
  - Se guarda en `OrderItem.configuration_data` como JSON
  - Ejemplo:
    ```json
    {
      "aderezos": ["Mayonesa", "Ketchup"],
      "extras": ["Bacon"],
      "medallones": 2,
      "tipo_medallon": "Carne"
    }
    ```

#### 5.3. Cálculo de Precios
- **Proceso**:
  1. Precio base del producto
  2. Suma modificadores de configuración:
     - Extras: `price_modifier` de cada extra
     - Aderezos/Dips: $0
     - Medallones: Se calcula según cantidad
  3. Multiplica por cantidad
  4. Aplica descuento por efectivo (si aplica)

---

### 6. SISTEMA DE USUARIOS Y AUTENTICACIÓN

#### 6.1. Roles
- **admin**: Acceso completo al panel
- **kitchen**: Solo vista de cocina
- **customer**: Cliente (no requiere autenticación para pedidos)

#### 6.2. Usuarios Invitados
- Si un cliente hace pedido sin sesión:
  - Se crea usuario automático: `guest@tecocina.local`
  - Rol: `customer`
  - Nombre: "Invitado"

---

### 7. API ENDPOINTS

#### 7.1. Productos
- `GET /api/products/{id}`: Obtener producto por ID
- `POST /api/products/batch`: Obtener múltiples productos
- `GET /api/products/category/{categoryId}`: Por categoría
- `GET /api/products/search?q=`: Búsqueda
- `GET /api/products/featured`: Productos destacados

#### 7.2. Turnos
- `GET /api/turnos/config`: Configuración actual
- `GET /api/turnos/disponibles?fecha=`: Microturnos disponibles
- `POST /api/turnos/disponibles`: Con contenido del carrito
- `POST /api/turnos/verificar`: Verificar disponibilidad específica
- `GET /api/turnos/estadisticas?fecha=`: Estadísticas

#### 7.3. Pedidos
- `GET /api/orders`: Pedidos del usuario autenticado

---

## 🔐 Middleware y Seguridad

### Middleware Personalizados
- **`auth`**: Requiere autenticación
- **`admin`**: Requiere rol admin
- **`kitchen`**: Requiere rol admin o kitchen

### Validaciones Clave
- Validación de disponibilidad de microturno antes de crear pedido
- Validación de capacidad antes de confirmar pedido
- Validación de día habilitado antes de procesar pedido

---

## 📊 Flujo de Datos Crítico

### Creación de Pedido → Asignación de Microturno
1. Cliente selecciona productos y configuración
2. Si `skip_turno_selection = false`: Cliente elige microturno
3. Si `skip_turno_selection = true`: Se auto-asigna primer microturno disponible
4. Se valida disponibilidad del microturno
5. Se crea pedido con `microturno_sort_order`
6. Estado inicial: `pending` (NO cuenta para capacidad)

### Confirmación de Pedido → Validación de Capacidad
1. Admin cambia estado: `pending` → `confirmed`
2. Sistema calcula productos del pedido
3. Suma productos activos en el microturno
4. Verifica si excede capacidad
5. Si excede: Bloquea cambio (a menos que se fuerce)
6. Si no excede: Confirma y cuenta para capacidad

### Entrega de Pedido → Liberación de Capacidad
1. Cocina marca: `preparing` → `delivered`
2. Estado cambia a inactivo
3. Automáticamente deja de contar para capacidad
4. Microturno puede aceptar nuevos pedidos

---

## 🎯 Puntos Críticos para Cambios

### 1. Sistema de Microturnos Dinámicos
- **Ubicación**: `DynamicMicroturno` (clase, no modelo)
- **Dependencias**: `WeeklyTurnoConfig`
- **Impacto**: Cualquier cambio afecta disponibilidad y capacidad

### 2. Cálculo de Capacidad
- **Ubicación**: `DynamicMicroturno::getProductosActivos()`
- **Dependencias**: Estados de pedido, categorías de productos
- **Impacto**: Cambios en estados o categorías afectan disponibilidad

### 3. Configuración de Productos
- **Ubicación**: `ProductConfiguration` (global)
- **Almacenamiento**: JSON en `OrderItem.configuration_data`
- **Impacto**: Cambios en estructura afectan pedidos existentes

### 4. Estados de Pedido
- **Definición**: `Order::ACTIVE_STATUSES`
- **Impacto**: Cambios afectan qué pedidos cuentan para capacidad

### 5. Validación de Capacidad
- **Ubicación**: `AdminController::updateOrderStatus`
- **Impacto**: Cambios en lógica afectan confirmación de pedidos

---

## 📝 Notas Importantes

1. **Microturnos NO se guardan en BD**: Se calculan dinámicamente
2. **Capacidad se calcula on-the-fly**: Basada en pedidos activos
3. **Configuraciones globales**: Aderezos, extras, etc. son compartidos entre productos
4. **Pedidos sin sesión**: Se crean usuarios invitados automáticamente
5. **Sistema de turnos opcional**: Puede desactivarse con `skip_turno_selection`
6. **Validación estricta**: No se puede confirmar pedido si excede capacidad (a menos que se fuerce)

---

## 🔍 Archivos Clave para Modificaciones

### Controladores
- `OrderController.php`: Lógica de pedidos
- `AdminController.php`: Panel de administración
- `KitchenController.php`: Vista de cocina
- `TurnoController.php`: API de turnos

### Modelos
- `Order.php`: Pedidos y estados
- `Product.php`: Productos
- `DynamicMicroturno.php`: Cálculo de microturnos
- `WeeklyTurnoConfig.php`: Configuración semanal
- `ProductConfiguration.php`: Configuraciones globales

### Vistas Principales
- `resources/views/cart.blade.php`: Carrito
- `resources/views/turnos.blade.php`: Selección de turnos
- `resources/views/checkout.blade.php`: Checkout
- `resources/views/admin/orders.blade.php`: Gestión de pedidos
- `resources/views/kitchen/index.blade.php`: Vista de cocina

---

## ⚠️ Consideraciones para Cambios

1. **Compatibilidad con datos existentes**: Los pedidos guardan configuración en JSON
2. **Cálculo dinámico**: Los microturnos se recalculan cada vez
3. **Estados activos**: Solo `confirmed` y `preparing` cuentan para capacidad
4. **Validación de capacidad**: Bloquea confirmación si excede
5. **Sistema de turnos opcional**: Puede estar desactivado

---

*Documento generado para análisis previo a cambios en la aplicación*

