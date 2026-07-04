# 📋 CONTEXTO — Rediseño Landing T Cocina (handoff)

> **Última actualización: 03/07/2026** — aplicada la tanda completa de correcciones del cliente (hero, header, reseñas, menú, textos, historia, acompañamientos, ranking, álbum).

## 1. Qué es el proyecto
- **T Cocina / TCO BURGER** = hamburguesería **smash artesanal** en **Olavarría**. App **Laravel 12 + Blade + Tailwind v4 + Vite + JS vanilla**, EN PRODUCCIÓN (pedidos online, admin/CRM, cocina, lealtad "figuritas", cupones, reseñas, tracking en tiempo real).
- **Repo actual:** `C:\Users\usuario\OneDrive\Documents\tcocina\tcocina_blstudio_repo`
- **Objetivo:** LANDING nueva para `/`. Hoy `/` y `/catalog` muestran el catálogo.
- **REGLA DE ORO:** el **catálogo `/catalog` NO se toca**. La landing es la vidriera y **linkea a `/catalog`**.

## 2. Cómo se trabaja — ⚠️ HAY DOS COPIAS
- **Prototipo (fuente de trabajo):** `_landing_preview/index.html` (CSS+JS inline, un solo archivo).
- **Copia servida (la que ve el cliente):** `public/landing/index.html` → `http://127.0.0.1:8000/landing/index.html`.
- **Ambas deben sincronizarse a mano** tras cada cambio: `cp _landing_preview/index.html public/landing/index.html` (los `assets/` ya existen duplicados en ambas carpetas y están completos).
- ⚠️ **OneDrive truncó el archivo 2 veces durante ediciones** (se perdió el final del HTML). Tras editar, verificar SIEMPRE que el archivo termine en `</html>` y que el `<script>` esté completo. Ideal: pausar la sincronización de OneDrive mientras se edita.
- Correcciones del cliente: `_landing_preview/correccioneslanding.txt` (tanda 1, aplicada parcialmente) + txt "hola" del 03/07/2026 (tanda 2, **aplicada completa**).

## 3. Marca (LOCKED)
- **Colores:** navy `#0a0e1a` / `#0f1628`, **ámbar `#E8A020`** y `#F2B133`, blanco, glow azul `#284497`.
- **Fuentes:** **Bangers** (display), **Anton** (condensada, H1 hero), **Archivo Black** (nombres/stats), **Archivo** (body), **DM Sans** (solo logo blstudio).
- **Vibe:** futbolera/argentina, sol de mayo, "Copa Hamburguesa".
- **Descuento efectivo: 10%.**
- **Datos:** Av. Pringles 3768, Olavarría · Miér a dom 19:30–22:30 · Delivery + retiro · IG @t_cocina_ · tcocina.org · Tel 2284 647634.
- **HISTORIA OFICIAL (actualizada por el cliente, reemplaza al "2024 desde el quincho"):** arrancó en **2021 en Mar del Plata** buscando aprender, haciendo todo desde cero (hasta los panes); después planchero en otro emprendimiento, luego en **Hamburgo** y **La Hamburguesería**; **3 años de aprendizaje** hasta arrancar **T Cocina · TCO BURGER**. (El footer aún dice "desde 2024" — revisar con el cliente.)
- **El pan:** elaborado en el día por **Panadería Boston** (productor local), sin conservantes.
- **La carne:** doble medallón 120 g, solo sal, sin condimentos agregados.
- **Todas las burgers vienen con papas + dip** (destacado en menú y sección acompañamientos).

## 4. Secciones — ESTADO ACTUAL (`_landing_preview/index.html`, ~897 líneas)
Orden real en la página:
1. **HEADER** fijo glass — logo + nav + CTA "Pedí ahora". **El CTA está oculto sobre el hero y aparece recién al scrollear a la 2ª sección** (IntersectionObserver + clase `.show-cta`).
2. **HERO** — burger central **centrada y grande** (l-hero left:14% top:12% w:72%; mobile w:78%), **sin efecto flotante** (se eliminó `floaty` de toda la escena; queda entrada `layerIn` + parallax mouse). En mobile se ven **los 3 dips** (mayo/cheddar/bbq) y se ocultan las 2 burgers traseras. **Un solo CTA: "Pedí Ahora"** (se borró "Ver el menú"). Espacio título→botones: 44px.
3. **MARQUEE** ámbar con nombres (sin tocar — todavía nombra Playita, Solo Cheese, etc.).
4. **RESEÑAS** (crema) — encabezado compacto: título + **una sola línea** con pill "★★★★★ 5.0 · +60 reseñas · 100% en 5 estrellas" + badges "Verificadas en la web" y "Google" (`.resenas__bar`). **Carrusel center mode de a UNA card** en todas las resoluciones: card centrada resaltada (`.is-center`, borde ámbar + halo), laterales atenuadas (opacity .55, scale .95). Cards de hasta 560px, texto 17px. **Drag con mouse en PC** con snap a la más cercana. Dots de a 1. CTA "Ver más reseñas en Google" con **G oficial multicolor** (`.btn-google`, blanco estilo Google).
5. **MENÚ** — **solo 6 cards en este orden: Rayito, La Joya, Cheese Bacon, LIBRA, 4 Quesos Azul, Piruco** (se quitó Playita). Pill "🍟 Todas vienen con papas + dip incluidos" bajo el título. Drag con mouse en PC (`#menuGrid`). ⚠️ **LIBRA: sin foto (placeholder "( falta imagen )"), sin precio y descripción genérica** — pedir datos al cliente.
6. **CHEESE-PULL banner** — kicker ahora dice **"100% calidad"** (antes "Queso que se estira" → "Ingredientes de primera calidad" → "100% calidad").
7. **LA POSTA** — ahora **4 pasos**: 1) Doble medallón 120 g + "solo con sal, sin condimentos agregados" · 2) Blend propio · 3) **NUEVO: "El mejor pan de la ciudad" (Panadería Boston, del día, local, sin conservantes)** · 4) Armada al momento.
8. **HISTORIA** — título "Tres años de aprendizaje, una obsesión", relato nuevo 2021/Mar del Plata/planchero/Hamburgo/La Hamburguesería/TCO BURGER + **tira de 3 fotos del proceso** (`smasheo2.jpg`, `abierta.jpg`, 1 placeholder). La mascota sol-burger sigue (con floaty, intencional).
9. **ACOMPAÑAMIENTOS** (crema) — **badge ámbar grande "🍟 TODAS las hamburguesas vienen con papas + dip incluidos"** (resuelve la duda del cliente) + lista.
10. **COPA HAMBURGUESA** — ya NO es podio de 3: **carrusel de 9 puestos** (`#copaTrack`) con **autoplay hacia la derecha cada 3,5 s** (pausa en hover/touch/drag, loop al inicio). Ranking: **1 Olavarría · 2 Libra · 3 La Joya · 4 Rayito · 5 Cheese Bacon · 6 4 Quesos Azul · 7 Cheese Gold · 8 4 Quesos Amarilla · 9 Piruco**. ⚠️ Olavarría, Libra y Cheese Gold con placeholder "( falta imagen )".
11. **STATS band** (sin tocar).
12. **FIDELIZACIÓN "Tu álbum T Cocina"** — la imagen se reemplazó por **placeholder "( falta imagen )"** (el cliente quiere una captura real del álbum).
13. **LOCAL** + **FOOTER** + **barra BLSTUDIO** (sin tocar).

## 5. JS actual (inline al final del HTML)
- Header scrolled + **show-cta por IntersectionObserver del hero**.
- `dragScroll(el)`: drag con mouse + snap a card más cercana, aplicado a `#resGrid`, `#menuGrid`, `#copaTrack` (clase `.dragging` desactiva snap y pointer-events de cards).
- Autoplay del ranking Copa (setInterval 3,5 s, scrollBy +268px, loop).
- Parallax escena hero (mouse) + parallax cheese-pull.
- Dots de reseñas (1 por card) + **detección de card centrada → `.is-center`**.
- Reveal on scroll + puntito verde blstudio.

## 6. Assets
- Ambas copias (`_landing_preview/assets/` y `public/landing/assets/`) tienen: `scene/` (8 recortes + mascot), `foto/` (7 fotos proceso/producto), `burgers/` (11 fotos fondo azul).
- **Fotos que FALTAN (pedir al cliente):** burger **Olavarría**, burger **Libra**, burger **Cheese Gold**, **captura real del álbum** de figuritas, **1 foto más del proceso** para historia.

## 7. PRÓXIMOS PASOS
1. **Conseguir del cliente:** fotos faltantes (§6), **precio + descripción de LIBRA**, confirmar orden exacto del ranking (la numeración del txt era ambigua; se aplicó orden secuencial 1-9) y qué hacer con "desde 2024" del footer.
2. **De la tanda 1 pendiente (`correccioneslanding.txt`):** plan de **votación web** (usuarios logueados con Google, guardar registro, reseñas individuales por producto para el catálogo) y **ordenar el menú automáticamente según ranking** — requiere backend, no es solo landing.
3. **Reemplazar emojis 🍟/🔥 por SVG** en tags/pills (regla ui-ux del pro