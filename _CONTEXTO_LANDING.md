# 📋 CONTEXTO — Rediseño Landing T Cocina (handoff)

## 1. Qué es el proyecto
- **T Cocina** = hamburguesería **smash artesanal** en **Olavarría**. App **Laravel 12 + Blade + Tailwind v4 + Vite + JS vanilla**, EN PRODUCCIÓN (pedidos online, admin/CRM, cocina, lealtad "figuritas", cupones, reseñas, tracking en tiempo real).
- **Ruta:** `C:\Users\BigLigas\Desktop\Proyectos\tcocina\13-5-26`
- **Objetivo:** crear una **LANDING nueva y espectacular** para `/`. Hoy `/` y `/catalog` muestran ambas el catálogo (no hay landing).
- **REGLA DE ORO:** el **catálogo `/catalog` NO se toca** (es el motor que vende). La landing es la vidriera y **linkea a `/catalog`**.

## 2. Cómo se trabaja
- Primero **PROTOTIPO ESTÁTICO** (un `index.html` con CSS+JS inline) para iterar rápido, luego se **porta a Blade** en `/inicio` (sin tocar la home), y al aprobar se repunta `/`.
- **Prototipo:** `tcocina\13-5-26\_landing_preview\` → `index.html`, `assets/`, `assets/burgers/`, `assets/foto/`, `assets/scene/`.
- **Ver en vivo:** abrir `file:///C:/Users/BigLigas/Desktop/Proyectos/tcocina/13-5-26/_landing_preview/index.html`. (Server preview python config "tcocina" puerto 8123 en `Cowork_claude/.claude/launch.json`.)
- ⚠️ El **screenshot del preview venía fallando** → verificar por `eval` (getBoundingClientRect, elementFromPoint, computed styles).
- Método: `C:\Users\BigLigas\Desktop\Proyectos\PLANTILLA-WEB-MAGICA.md`.

## 3. Marca (LOCKED)
- **Colores:** navy `#0a0e1a` / `#0f1628` (del login/tracking), **ámbar `#E8A020`** y `#F2B133`, blanco, glow azul `#284497`. (NO el celeste "beach" del catálogo actual.)
- **Fuentes (mezcladas):** **Bangers** (display, la del checkout), **Anton** (condensada), **Archivo Black** (nombres/stats), **Archivo** (body), **DM Sans** (solo logo blstudio).
- **Logo:** `public/branding/logo_left.png` (wordmark "Tcocina" blanco), `logo.png` (la T sola).
- **Vibe:** futbolera/argentina, sol de mayo, "Copa Hamburguesa", "estar adentro de una hamburguesería a pleno que dé hambre".
- **Mascota firma:** sol-hamburguesa boxeador con zapatillas (grabado) → `public/productos/fondo/burger.png`. Números: `1tcocina.png`, `2tcocina.png`.
- **Descuento efectivo: 10%** (no 15).
- **Datos:** Av. Pringles 3768, Olavarría · Miér a dom 19:30–22:30 · Delivery + retiro · IG @t_cocina_ · tcocina.org · Tel 2284 647634.
- **Copy real:** "la hamburguesería artesanal de Olavarría especializada en smash burgers… blend de carnes propio… las mejores de Olavarría". **Origen: 2024, desde el quincho de la casa.**
- **Menú (9):** Rayito $15.500 · BBQ Especial $15.500 · Solo Cheese $15.000 · La Joya $17.000 · Cheese Bacon $15.500 · 4 Quesos Azul $15.500 · 4 Quesos Amarilla $15.500 · Playita $16.500 · Piruco $15.500. (+veggie $15.500. "Tosca" = especial.) Todas con papas+dip.

## 4. Secciones YA construidas (`_landing_preview/index.html`)
1. Header fijo glass (logo + nav Menú/Cómo se hace/Historia/Local + CTA "Pedí ahora" → /catalog).
2. HERO (en upgrade a ESCENA, ver §5). Título Bangers "LAS MEJORES SMASH DE OLAVARRÍA", eyebrow "★ Hamburguesas artesanales · Olavarría", stats (+124 en una noche · ★4,9 · 2024 desde el quincho), 2 CTAs.
3. Marquee ámbar con nombres de burgers.
4. MENÚ "Nuestras estrellas" — 9 cards con fotos PRO fondo azul (`assets/burgers/`), nombre Archivo Black, precio Bangers ámbar, flag "★ La más pedida" en Rayito, hover, → /catalog. Nota "10% OFF".
5. Banner CHEESE-PULL full-bleed (`assets/foto/cheesepull.jpg`) "CADA BOCADO, UNA FIESTA" + parallax scroll.
6. "LA POSTA / Cómo se hace" — sección CREMA `#f4f1e8` texto navy, foto del smasheo en plancha (`assets/foto/smasheo.jpg`), 3 pasos. Bordes ámbar.
7. HISTORIA "Del quincho a la cancha" (navy) — mascota sol-burger flotando + relato 2024.
8. ACOMPAÑAMIENTOS — sección CREMA, papas y aros (`assets/foto/papasyaros.jpg`), lista.
9. STATS band (azul, bordes ámbar): +124 · ★4,9 · 4.8k IG · 10% OFF.
10. LOCAL — Pringles 3768, horarios, mapa Google embed, CTA.
11. FOOTER (navy) — logo, IG, dirección, horarios, pedidos.
12. Barrita BLSTUDIO (sello) — "¿Querés una web como ésta? · Ver planes" + logo blstudio animado → stgrandesligas.com.

**Ritmo:** navy → navy(menú) → banner oscuro → crema(posta) → navy(historia) → crema(acomp) → azul(stats) → navy(local) → navy(footer).

## 5. HERO SCENE — el corazón
**Concepto:** escena "hamburguesería a pleno" con recortes reales PNG en capas: smash central + 2 burgers atrás + papas + aros + 3 dips, + sol de rayos ámbar girando + halo.
**3 niveles anidados por capa (sin conflicto de transform):**
- `.layer`: entrada `layerIn` (.85s `cubic-bezier(.34,1.45,.5,1)` forwards, opacity+translateY(48px)+scale(.85)→none) con stagger (`animation-delay`).
- `.pll`: parallax mouse vía JS (`data-depth`).
- `img`: flote idle `floaty` infinito (duraciones distintas).
- Fondo: `.sunrays` (conic-gradient ámbar, `spin` 80s) + `.halo` (glow).

**Capas (desktop, scene ~496×560):** l-hero (burger-hero, left:18% top:22% w:60% z5 depth10) · l-lajoya (left:-5% top:1% w:42% z2 depth16) · l-tosca (right:-7% top:-3% w:44% z2 depth18) · l-papas (left:-9% bottom:3% w:31% z6 depth26) · l-aros (right:-9% bottom:7% w:32% z4 depth24) · l-dip1/mayo (left:7% bottom:-3% w:15% z8 depth36) · l-dip2/cheddar (left:41% bottom:-6% w:16% z9 depth40) · l-dip3/bbq (right:9% bottom:-3% w:15% z8 depth34).
**Mobile (≤880):** oculta l-lajoya/l-tosca/l-dip3; recentra hero; papas/aros/2 dips más grandes.
**ESTADO:** construido. **papas.png YA ESTÁ** (assets/scene/papas.png). **FALTA afinar visualmente** las posiciones (que no se pisen, márgenes).

## 6. Assets (recortes)
- Prototipo `assets/scene/`: burger-hero, burger-lajoya, burger-tosca, **papas**, aros, dip-mayo, dip-cheddar, dip-bbq, mascot. ✅ TODOS.
- `assets/foto/`: smasheo, smasheo2, cheesepull, abierta, papasyaros, lajoya-pro, cheesebacon-pro.
- `assets/burgers/`: 9 del menú (fondo azul) + bbq-especial + papas + aros.
- Originales en `public/images/fotografo/` y `public/images/products/`.

## 7. PRÓXIMOS PASOS (lo que falta hacer)
1. **Terminar/afinar la HERO SCENE:** tunear posiciones (overlaps/márgenes/tamaños), pulir coreografía; opcional vapor + papel cuadrillé azul "mostrador" + mascota asomando; test mobile.
2. **Sección COPA HAMBURGUESA:** podio/ranking con números ilustrados (`1tcocina.png`/`2tcocina.png`) → "campeona del mes" + 1°/2°/3° + CTA "votá en IG". Sin backend.
3. **Sección RESEÑAS + FIDELIZACIÓN:** tarjetas de reseñas Google (★4,9, pedir 4-6 textos) + teaser figuritas (diferencial) con `public/images/rewards/`.
4. **Pulido opcional:** contadores que cuentan, "ABIERTO AHORA" en vivo, WhatsApp flotante, barra sticky "Pedí ahora" mobile.
5. **PORTEO A LARAVEL:** vista Blade + ruta `GET /inicio` (CSS inline → portear casi tal cual); copiar `assets/` a `public/images/landing/` y ajustar paths; CTAs → `/catalog`; al aprobar, repuntar `/` (hoy `ProductController@index` sirve catálogo en `/` y `/catalog`). NO tocar `/catalog`.
6. **Bonus:** video con Remotion (intro .mp4) si hay footage. (Remotion = video, NO el hero interactivo.)

## 8. Notas técnicas
- Skill /ui-ux-pro-max valida: "Vibrant & Block-based" (bold/energético), tipografía grande, micro 200–300ms, solo transform/opacity, `prefers-reduced-motion`, responsive 375/768/1024/1440, cursor-pointer, **no emojis como íconos** (reemplazar 🔥 de tags por SVG en final).
- Prompts reutilizables: retoque de burgers (idéntica, limpiar, iluminar, 3/4 frente, transparente, una por vez) + dips (envase real de referencia, cambiar solo salsa) + papas (aislar caja y completar).
