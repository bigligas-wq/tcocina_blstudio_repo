# 🧪 Catálogo del Laboratorio — Mejoras detectadas (Local vs Producción)

> Comparación: `TcocinaProduccion/tco_produ` (deploy actual) vs `tcocina/13-5-26` (local avanzado).
> Fecha análisis: 31/5/26. Criterio: **pocas mejoras, prolijas, que le hablen al dueño del negocio.**

---

## Criterio de curado

De todas las diferencias encontradas, separé en 3 grupos:

| Grupo | Qué incluye | ¿Se vende? |
|---|---|---|
| 🟢 **Mejoras vendibles** | Features nuevas con valor de negocio claro | **SÍ** |
| ⚪ **Infraestructura** | El Laboratorio mismo | No — se la das gratis, aparece sola |
| 🔧 **Fixes / pulido** | Arreglos de contraste, bugs, ajustes menores | No — van gratis (es lo "oficial") |

**Detectado como fix (gratis, no entra al catálogo):** contraste de badges en modo oscuro del panel
de turnos, ajustes de espaciado mobile en catálogo, correcciones varias de CSS.

---

## 🟢 Las 5 mejoras para vender

Cada una redactada para que **Emilio (dueño)** entienda el valor sin tecnicismos.

---

### 1. Control de equipo y accesos
**Categoría:** `admin` · **Precio:** 🔴 Alta · **Tiempo estimado:** 14 h · **ROI:** Seguridad + delegación

**Gancho:** Sumá a tu equipo con permisos a medida — cada uno ve solo lo suyo.

**Descripción larga:**
Hoy entrás solo vos con una cuenta. Esta mejora te deja crear usuarios para tu gente (cajero,
cocina, encargado) y darle a cada uno exactamente los permisos que necesita. Tu nombre y rol
aparecen siempre en el panel, y podés cerrar sesión con un clic desde cualquier pantalla.

**Antes → Después:**
- ❌ Una sola cuenta para todo → ✅ Un usuario por persona del equipo
- ❌ Todos verían todo → ✅ Permisos por rol (cajero, cocina, admin)
- ❌ No se podía cerrar sesión desde el panel → ✅ Tu nombre + cerrar sesión siempre visible

**Evidencia:** `app/Models/User.php`, `app/Http/Controllers/UserManagementController.php`,
`resources/views/layouts/admin.blade.php` (user pill), `config/permissions.php`, rutas `admin.users.*`

---

### 2. Checkout guiado paso a paso
**Categoría:** `ux` · **Precio:** 🔴 Alta · **Tiempo estimado:** 20 h · **ROI:** Más ventas (menos abandono)

**Gancho:** Un checkout que lleva al cliente de la mano y vende más.

**Descripción larga:**
Rediseño completo del proceso de compra en pasos claros: método de entrega → datos de contacto →
confirmación. Cada paso se completa, se resume y se puede editar sin perder lo anterior. Menos
confusión = menos carritos abandonados = más pedidos cerrados.

**Antes → Después:**
- ❌ Formulario largo de una sola pantalla → ✅ Pasos guiados con progreso visual
- ❌ Si te equivocabas, volvías a empezar → ✅ Editás cualquier paso sin perder datos
- ❌ Cliente perdido sin saber qué falta → ✅ Resumen claro en cada etapa

**Evidencia:** `resources/views/checkout.blade.php` (+1.325 líneas: secciones `step-card`,
`deliveryMethodSection`, `contactStep`, estados active/locked/done)

---

### 3. Pedidos grandes coordinados por WhatsApp
**Categoría:** `ux` · **Precio:** 🟡 Media · **Tiempo estimado:** 8 h · **ROI:** No perder pedidos grandes

**Gancho:** Los pedidos grandes ya no rompen tu agenda de turnos.

**Descripción larga:**
Cuando un pedido supera la capacidad de un turno normal, en lugar de bloquearlo, el sistema ofrece
coordinar la entrega directo por WhatsApp con la cocina. Captás el pedido grande (más facturación)
sin desarmar la logística de los demás.

**Antes → Después:**
- ❌ Pedido grande no entraba en ningún turno → ✅ Se deriva a coordinación por WhatsApp
- ❌ Riesgo de perder la venta → ✅ Card clara que guía al cliente y avisa a cocina

**Evidencia:** `resources/views/turnos.blade.php` (+168 líneas: `cart-too-large-card`, flujo
`__whatsapp__`, `coordinateByWhatsApp`)

---

### 4. Pantalla de cocina con filtros por turno
**Categoría:** `admin` · **Precio:** 🟡 Media · **Tiempo estimado:** 10 h · **ROI:** Menos errores en cocina

**Gancho:** La cocina ve solo lo que tiene que cocinar ahora.

**Descripción larga:**
La pantalla de cocina (KDS) ahora filtra los pedidos por microturno con un clic, muestra un
contador por turno y un diseño moderno con vidrio esmerilado. Botones directos para "Iniciar
preparación" y "Marcar entregado". Cocina más ordenada, menos pedidos traspapelados.

**Antes → Después:**
- ❌ Todos los pedidos mezclados → ✅ Filtro por microturno con contador
- ❌ Pantalla plana → ✅ Diseño moderno, legible a distancia
- ❌ Estados poco claros → ✅ Acciones directas iniciar/entregar

**Evidencia:** `resources/views/kitchen/display.blade.php` (+51 líneas: `filter-btn`,
`filter-count`, `renderMicroturnoFilters`, modal KD)

---

### 5. Panel de pedidos profesional
**Categoría:** `admin` · **Precio:** 🟡 Media · **Tiempo estimado:** 7 h · **ROI:** Mejor gestión diaria

**Gancho:** Toda la operación del día (y la de ayer) a la vista.

**Descripción larga:**
El panel de administración de pedidos suma la vista de "pedidos del día anterior" para no perder
trazabilidad, opción de ver todos los pedidos juntos y mejoras de legibilidad. Gestionás el día
con más control y menos clics.

**Antes → Después:**
- ❌ Solo veías el día actual → ✅ Acceso al histórico del día anterior
- ❌ Paginación rígida → ✅ Ver "Todos" o por cantidad
- ❌ Listado denso → ✅ Más legible y filtrable

**Evidencia:** `resources/views/admin/orders.blade.php` (histórico, `lengthMenu` con "Todos", badges)

---

## 📦 Bundles sugeridos (packs)

Usando el modelo `LabBundle` ya existente. Vender el pack a precio menor que la suma incentiva.

### Pack «Más Ventas» 🔴
> Todo para que el cliente compre más y más fácil.
- Checkout guiado paso a paso (#2)
- Pedidos grandes por WhatsApp (#3)

### Pack «Gestión Pro» 🔴
> El centro de control de tu local.
- Control de equipo y accesos (#1)
- Pantalla de cocina con filtros (#4)
- Panel de pedidos profesional (#5)

---

## Resumen ejecutivo

| # | Mejora | Categoría | Banda |
|---|---|---|---|
| 1 | Control de equipo y accesos | admin | 🔴 Alta |
| 2 | Checkout guiado paso a paso | ux | 🔴 Alta |
| 3 | Pedidos grandes por WhatsApp | ux | 🟡 Media |
| 4 | Pantalla de cocina con filtros | admin | 🟡 Media |
| 5 | Panel de pedidos profesional | admin | 🟡 Media |

**Destacada sugerida:** #2 Checkout guiado (la de mayor impacto en ventas).
**Bandas de precio:** 🟢 baja / 🟡 media / 🔴 alta — vos ponés el número final en USD.

---

## Próximo paso
1. Revisá/editá este catálogo (precios, textos, qué entra y qué no).
2. Cuando esté cerrado → genero `database/seeders/LabImprovementSeeder.php` con las aprobadas.
3. Las cargo en estado `borrador` o `publicada` (vos decidís) y aparecen en `/admin/laboratorio`.
