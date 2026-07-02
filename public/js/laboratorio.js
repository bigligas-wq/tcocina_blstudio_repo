/* BLStudio Laboratorio · Fábrica de actualizaciones
 * Portado del prototipo (lab/lab/*.jsx) a vanilla JS, conectado al backend Laravel.
 */
(function () {
    'use strict';

    const BOOT = window.LAB_BOOT || { improvements: [], featuredId: null, urls: {}, whatsapp: '', isDeveloper: false, userName: '', csrf: '' };
    const IMPS = BOOT.improvements || [];
    const IMP_BY_ID = Object.fromEntries(IMPS.map(i => [i.id, i]));

    /* ---------------- almacenamiento local ---------------- */
    const CART_KEY    = 'bl_lab_cart_v2';      // [{id, nota}]
    const RATINGS_KEY = 'bl_lab_signals_v1';   // {id: {stars, at}}
    const TWEAKS_KEY  = 'bl_lab_tweaks_v1';    // {greet, accion, burbujeo, tono, regalo}

    function load(key, fallback) {
        try { return JSON.parse(localStorage.getItem(key)) ?? fallback; }
        catch (e) { return fallback; }
    }
    function save(key, value) {
        try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) {}
    }

    let cart    = load(CART_KEY, []);
    let ratings = load(RATINGS_KEY, {});
    let tweaks  = Object.assign({
        greet:    BOOT.userName || '',
        accion:   '#ff4c0c',
        burbujeo: 'mix',
        tono:     'tranquilo',
        regalo:   true,
    }, load(TWEAKS_KEY, {}));

    const COPY = {
        tranquilo: {
            add: 'Me interesa', added: 'Agregada', featuredAdd: 'Me interesa', previewAdd: 'Me interesa →',
            pedido: 'Lo que elegiste', whats: 'Enviármelo por WhatsApp',
            eyebrow: 'fábrica',
        },
        entusiasta: {
            add: 'Me interesa', added: 'Agregada', featuredAdd: 'Me interesa', previewAdd: 'Me interesa →',
            pedido: 'Lo que elegiste', whats: 'Enviármelo por WhatsApp',
            eyebrow: 'la fábrica',
        },
    };

    /* ---------------- formatters ---------------- */
    const fmt = n => Math.round(Number(n) || 0).toString();

    /* ============================================================
       LOTTIES (recolor + mount)
       ============================================================ */
    const LOTTIE_SOURCES = {
        atom:    '/lottie/atom.json',
        factory: '/lottie/nuke.json',
    };
    const RECOLOR_PRESETS = {
        ink: [0.957, 0.937, 0.906, 1],
        mut: [0.604, 0.576, 0.533, 1],
    };
    const lottieCache = {};

    function recolorLottieData(data, target) {
        const walk = node => {
            if (Array.isArray(node)) { node.forEach(walk); return; }
            if (node && typeof node === 'object') {
                if (node.c && Array.isArray(node.c.k) && node.c.k.length >= 3 && typeof node.c.k[0] === 'number') {
                    const k = node.c.k.slice(0, 3).map(v => v.toFixed(3)).join(',');
                    if (k === '0.071,0.075,0.192') node.c.k = target;
                }
                for (const key in node) walk(node[key]);
            }
        };
        walk(data);
        return data;
    }
    function loadLottieJson(name) {
        if (lottieCache[name]) return lottieCache[name];
        lottieCache[name] = fetch(LOTTIE_SOURCES[name]).then(r => r.json()).catch(() => null);
        return lottieCache[name];
    }
    function mountLotties() {
        if (!window.lottie) return;
        document.querySelectorAll('[data-lab-lottie]').forEach(el => {
            if (el.dataset.lottieMounted) return;
            const name   = el.dataset.labLottie;
            const preset = el.dataset.recolor;
            const loop   = el.dataset.loop !== '0';
            if (!LOTTIE_SOURCES[name]) return;
            el.dataset.lottieMounted = '1';
            loadLottieJson(name).then(raw => {
                if (!raw) { delete el.dataset.lottieMounted; return; }
                const data = JSON.parse(JSON.stringify(raw));
                if (preset && RECOLOR_PRESETS[preset]) recolorLottieData(data, RECOLOR_PRESETS[preset]);
                window.lottie.loadAnimation({
                    container: el, renderer: 'svg',
                    loop, autoplay: true, animationData: data,
                });
            });
        });
    }
    function ensureLottieReady(cb) {
        if (window.lottie) return cb();
        let tries = 0;
        const iv = setInterval(() => {
            tries++;
            if (window.lottie) { clearInterval(iv); cb(); }
            else if (tries > 80) clearInterval(iv);
        }, 100);
    }

    /* ============================================================
       FONDO: inyectar canvas DENTRO de .lab-app como primer hijo.
       position:absolute en el CSS los contiene dentro de lab-app
       (que es position:relative), evitando TODOS los problemas de
       stacking-context que causaba position:fixed en el body.
       ============================================================ */
    function ensureBgCanvases() {
        const app = document.querySelector('.lab-app');
        const host = app || document.body;
        if (!document.getElementById('lab-matrix-canvas')) {
            const m = document.createElement('canvas');
            m.id = 'lab-matrix-canvas';
            m.className = 'lab-bg-canvas lab-bg-matrix';
            m.setAttribute('aria-hidden', 'true');
            host.insertBefore(m, host.firstChild);
        }
        if (!document.getElementById('lab-bubble-canvas')) {
            const b = document.createElement('canvas');
            b.id = 'lab-bubble-canvas';
            b.className = 'lab-bg-canvas lab-bg-bubble';
            b.setAttribute('aria-hidden', 'true');
            host.insertBefore(b, host.firstChild);
        }
    }
    function removeBgCanvases() {
        ['lab-matrix-canvas', 'lab-bubble-canvas'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el.parentNode) el.parentNode.removeChild(el);
        });
    }

    /* ============================================================
       BUBBLE CANVAS (ambient)
       ============================================================ */
    let bubbleRaf = null, bubbleRO = null;
    function startBubbles(intensity) {
        const cv = document.getElementById('lab-bubble-canvas');
        if (!cv) return;
        if (bubbleRaf) cancelAnimationFrame(bubbleRaf);
        if (bubbleRO) { bubbleRO.disconnect(); bubbleRO = null; }
        if (intensity === 'off') {
            const ctx = cv.getContext('2d');
            ctx && ctx.clearRect(0, 0, cv.width, cv.height);
            return;
        }
        const ctx = cv.getContext('2d');
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        const count = intensity === 'vivo' ? 70 : 42;
        // paleta brand: solo lima/blanco/grises — nada de rojo/azul/naranja
        const palette = [
            [139, 195, 74],   // lima brand
            [164, 214, 94],   // lima soft
            [255, 255, 255],  // blanco
            [207, 199, 187],  // gris cálido claro
            [154, 147, 136],  // gris cálido medio
        ];
        const weight  = [0.32, 0.26, 0.22, 0.12, 0.08];
        const pick = () => {
            let r = Math.random(), a = 0;
            for (let i = 0; i < weight.length; i++) { a += weight[i]; if (r <= a) return palette[i]; }
            return palette[0];
        };
        let W, H, bubbles = [];
        const resize = () => {
            const app = document.querySelector('.lab-app');
            W = (app ? app.offsetWidth  : cv.offsetWidth)  || window.innerWidth;
            H = (app ? app.offsetHeight : cv.offsetHeight) || window.innerHeight;
            cv.width = Math.round(W * dpr); cv.height = Math.round(H * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        };
        const mk = initial => {
            const r = 2 + Math.pow(Math.random(), 2) * 22;
            return {
                x: Math.random() * W,
                y: initial ? Math.random() * H : H + r + Math.random() * 40,
                r, c: pick(),
                sp: (intensity === 'vivo' ? 0.35 : 0.22) + Math.random() * 0.7 + r * 0.012,
                drift: (Math.random() - 0.5) * 0.4,
                ph: Math.random() * Math.PI * 2,
                wob: 0.3 + Math.random() * 0.7,
                a: 0.10 + Math.random() * 0.28,
            };
        };
        resize();
        bubbles = Array.from({ length: count }, () => mk(true));
        let t = 0;
        const frame = () => {
            t += 0.016;
            ctx.clearRect(0, 0, W, H);
            ctx.globalCompositeOperation = 'lighter';
            for (const b of bubbles) {
                b.y -= b.sp;
                b.x += b.drift + Math.sin(t * b.wob + b.ph) * 0.3;
                if (b.y < -b.r - 30) Object.assign(b, mk(false));
                const [r, g, bl] = b.c;
                const grd = ctx.createRadialGradient(b.x - b.r * 0.3, b.y - b.r * 0.3, b.r * 0.1, b.x, b.y, b.r);
                grd.addColorStop(0,    `rgba(${r},${g},${bl},${b.a * 1.1})`);
                grd.addColorStop(0.55, `rgba(${r},${g},${bl},${b.a * 0.4})`);
                grd.addColorStop(1,    `rgba(${r},${g},${bl},0)`);
                ctx.fillStyle = grd;
                ctx.beginPath(); ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2); ctx.fill();
                if (b.r > 7) {
                    ctx.strokeStyle = `rgba(255,255,255,${b.a * 0.5})`;
                    ctx.lineWidth = 0.8;
                    ctx.beginPath(); ctx.arc(b.x - b.r * 0.2, b.y - b.r * 0.2, b.r * 0.7, Math.PI * 0.9, Math.PI * 1.7); ctx.stroke();
                }
            }
            ctx.globalCompositeOperation = 'source-over';
            bubbleRaf = requestAnimationFrame(frame);
        };
        frame();
        bubbleRO = new ResizeObserver(resize);
        bubbleRO.observe(cv);
    }

    /* ============================================================
       MATRIX RAIN — reactivo a hover y clicks sobre el laptop
       ============================================================ */
    let matrixRaf = null, matrixRO = null;
    let matrixHoverX = -1;
    const matrixRipples = []; // [{x, t}] — spawned on click

    function setupMatrixReactivity() {
        const laptop = document.querySelector('.lab-laptop');
        if (!laptop) return;
        laptop.addEventListener('mousemove', e => {
            const cv = document.getElementById('lab-matrix-canvas');
            if (!cv) return;
            const rect = cv.getBoundingClientRect();
            matrixHoverX = e.clientX - rect.left;
        });
        laptop.addEventListener('mouseleave', () => { matrixHoverX = -1; });
        laptop.addEventListener('click', e => {
            const cv = document.getElementById('lab-matrix-canvas');
            if (!cv) return;
            const rect = cv.getBoundingClientRect();
            matrixRipples.push({ x: e.clientX - rect.left, t: performance.now() });
            // limpia ripples viejos (> 1s)
            const now = performance.now();
            for (let i = matrixRipples.length - 1; i >= 0; i--) {
                if (now - matrixRipples[i].t > 1000) matrixRipples.splice(i, 1);
            }
        });
    }

    function startMatrix() {
        const cv = document.getElementById('lab-matrix-canvas');
        if (!cv) return;
        if (matrixRaf) { cancelAnimationFrame(matrixRaf); matrixRaf = null; }
        if (matrixRO)  { matrixRO.disconnect(); matrixRO = null; }
        const ctx = cv.getContext('2d');
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        const fontSize = 14;
        let W, H, cols, drops, lastT = 0;

        const resize = () => {
            // El canvas vive dentro de .lab-app (position:absolute, inset:0).
            // offsetWidth/Height dan el tamaño real del contenedor.
            const app = document.querySelector('.lab-app');
            W = (app ? app.offsetWidth : 0)  || window.innerWidth;
            H = (app ? app.offsetHeight : 0) || window.innerHeight;
            cv.width  = Math.round(W * dpr);
            cv.height = Math.round(H * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            cols  = Math.floor(W / fontSize);
            // Drops arrancando en posiciones aleatorias DENTRO de la pantalla
            // para que los caracteres sean visibles de inmediato.
            drops = Array(cols).fill(0).map(() => Math.random() * (H / fontSize));
        };
        resize();

        const STEP_MS = 60;
        const frame = (t) => {
            if (t - lastT >= STEP_MS) {
                lastT = t;

                // Desvanecer el rastro borrando el alpha gradualmente (destination-out)
                ctx.globalCompositeOperation = 'destination-out';
                ctx.fillStyle = 'rgba(0,0,0,0.038)';
                ctx.fillRect(0, 0, W, H);
                ctx.globalCompositeOperation = 'source-over';

                ctx.font = `bold ${fontSize}px "DM Mono", ui-monospace, monospace`;
                ctx.textBaseline = 'top';

                const now = performance.now();

                for (let i = 0; i < cols; i++) {
                    const colX = i * fontSize + fontSize * 0.5;
                    let speed = 1;

                    if (matrixHoverX >= 0) {
                        const dist = Math.abs(colX - matrixHoverX);
                        if (dist < fontSize * 10) speed += (1 - dist / (fontSize * 10)) * 2.2;
                    }

                    for (const rip of matrixRipples) {
                        const age = now - rip.t;
                        if (age > 900) continue;
                        const life = 1 - age / 900;
                        const radius = fontSize * (3 + age / 55);
                        const dist = Math.abs(colX - rip.x);
                        if (dist < radius) speed += life * (1 - dist / radius) * 4;
                    }

                    // 92% binario clásico (0/1), 8% caracteres "BLS" como firma BLStudio
                    let ch;
                    const r = Math.random();
                    if (r < 0.04)      ch = 'B';
                    else if (r < 0.06) ch = 'L';
                    else if (r < 0.08) ch = 'S';
                    else               ch = (r < 0.54) ? '0' : '1';
                    const isBls = (ch === 'B' || ch === 'L' || ch === 'S');
                    const x    = i * fontSize;
                    const y    = drops[i] * fontSize;

                    if (isBls) {
                        // los BLS resaltan más para que la firma se note sin gritar
                        ctx.fillStyle = `rgba(164,214,94,${Math.min(1, 0.65 + speed * 0.15)})`;
                    } else if (speed > 1.5 && Math.random() > 0.6) {
                        ctx.fillStyle = `rgba(255,255,255,${Math.min(1, speed * 0.42)})`;
                    } else if (Math.random() > 0.985) {
                        ctx.fillStyle = 'rgba(255,255,255,1)';
                    } else {
                        ctx.fillStyle = 'rgba(139,195,74,0.92)';
                    }
                    ctx.fillText(ch, x, y);

                    if (y > H && Math.random() > 0.972) drops[i] = 0;
                    drops[i] += speed;
                }
            }
            matrixRaf = requestAnimationFrame(frame);
        };
        matrixRaf = requestAnimationFrame(frame);

        matrixRO = new ResizeObserver(resize);
        matrixRO.observe(cv);
    }

    /* ============================================================
       COPY / TWEAKS
       ============================================================ */
    function applyTweaks() {
        // color de acción
        document.documentElement.style.setProperty('--lab-accent', tweaks.accion);
        // greet
        document.querySelectorAll('[data-tw-greet]').forEach(el => el.textContent = tweaks.greet || '');
        // copy intercambiable según tono
        const c = COPY[tweaks.tono] || COPY.tranquilo;
        document.querySelectorAll('[data-tw-copy]').forEach(el => {
            const key = el.dataset.twCopy;
            if (c[key]) el.textContent = c[key];
        });
        document.querySelectorAll('[data-tw-eyebrow]').forEach(el => el.textContent = c.eyebrow);
        // free banner
        document.querySelectorAll('[data-tw-free]').forEach(el => {
            el.style.display = tweaks.regalo ? '' : 'none';
        });
        // burbujeo (ELIMINADO: rediseño sin canvas)
        // startBubbles(tweaks.burbujeo);
        // sincronizar UI del panel
        syncTweakControls();
    }
    function setTweak(key, value) {
        tweaks = { ...tweaks, [key]: value };
        save(TWEAKS_KEY, tweaks);
        applyTweaks();
        if (key === 'regalo') updateCartDock();
    }

    /* ─── badge helper (declarado antes de CART para uso temprano) ─── */
    function updateSidenavBadge(id, count) {
        const el = document.getElementById(id);
        if (!el) return;
        if (count > 0) { el.textContent = count; el.style.display = ''; }
        else { el.style.display = 'none'; }
    }

    /* ============================================================
       CART
       ============================================================ */
    function inCart(id) { return cart.some(c => c.id === id); }
    function getCartItem(id) { return cart.find(c => c.id === id); }
    function setCartNote(id, nota) {
        cart = cart.map(c => c.id === id ? { ...c, nota } : c);
        save(CART_KEY, cart);
    }
    function toggleCart(id) {
        if (inCart(id)) cart = cart.filter(c => c.id !== id);
        else cart.push({ id, nota: '' });
        save(CART_KEY, cart);
        updateAddButtons();
        updateCartDock();
    }

    function cartTotals() {
        const items = cart.map(c => IMP_BY_ID[c.id]).filter(Boolean);
        const subtotal = items.reduce((s, i) => s + (i.price || 0), 0);
        const freeOff = (tweaks.regalo && items.length > 0)
            ? Math.min(...items.map(i => i.price || 0))
            : 0;
        return { items, subtotal, freeOff, total: subtotal - freeOff };
    }

    function updateAddButtons() {
        document.querySelectorAll('[data-lab-add]').forEach(btn => {
            const id = parseInt(btn.dataset.labAdd, 10);
            const added = inCart(id);
            btn.classList.toggle('added', added);
            const labelEl = btn.querySelector('[data-tw-copy]');
            const c = COPY[tweaks.tono] || COPY.tranquilo;
            if (labelEl) {
                if (added) {
                    labelEl.textContent = c.added;
                    btn.firstChild && btn.firstChild.nodeType === 3 && (btn.firstChild.textContent = '✓ ');
                } else {
                    const key = labelEl.dataset.twCopy;
                    labelEl.textContent = c[key] || c.add;
                    btn.firstChild && btn.firstChild.nodeType === 3 && (btn.firstChild.textContent = '+ ');
                }
            }
        });
    }

    function updateCartDock() {
        const dock = document.getElementById('lab-cart-dock');
        const { items, freeOff, total } = cartTotals();

        // Badge sidebar
        updateSidenavBadge('snav-carrito-cnt', items.length);

        // Refrescar panel carrito si está activo
        const panelActive = document.getElementById('panel-carrito')?.classList.contains('active');
        if (panelActive) renderPanelCarrito();

        if (!dock) return;
        if (items.length === 0) { dock.innerHTML = ''; return; }
        dock.innerHTML = `
            <div class="lab-cart-dock vis">
                <div class="ci"><b>${items.length}</b> ${items.length === 1 ? 'mejora elegida' : 'mejoras elegidas'}
                    ${freeOff > 0 ? '<span style="color:var(--lab-lime-soft)"> · primera gratis</span>' : ''}
                </div>
                <div class="sep"></div>
                <div class="ctot"><span>USD </span>${fmt(total)}</div>
                <button class="btn btn-primary" data-lab-action="open-order">Ver lo que elegí →</button>
            </div>
        `;
    }

    /* ============================================================
       STARS
       ============================================================ */
    const RATE_WORDS = ['¿cuánto la puntuás?', 'no tanto', 'puede ser', 'me gusta', 'me gusta mucho', 'la quiero ya'];
    function renderStars(container, impId, opts) {
        opts = opts || {};
        const big = !!opts.big;
        const value = (ratings[impId] && ratings[impId].stars) || 0;
        container.innerHTML = `
            <div class="star-rate${big ? ' big' : ''}">
                <span class="star-label${value ? ' act' : ''}">${RATE_WORDS[value]}</span>
                <div class="stars" data-stars-for="${impId}">
                    ${[1,2,3,4,5].map(n => `
                        <button type="button" class="star${n <= value ? ' on' : ''}" data-star="${n}" aria-label="${n} estrellas">
                            <svg viewBox="0 0 24 24"><path d="M12 3.4l2.6 5.3 5.8.85-4.2 4.1.99 5.8L12 21.8 6.8 19.1l1-5.8-4.2-4.1 5.8-.85z"/></svg>
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
        const labelEl = container.querySelector('.star-label');
        const starsBox = container.querySelector('.stars');
        const updateHover = n => {
            labelEl.textContent = RATE_WORDS[n || value];
            labelEl.classList.toggle('act', (n || value) > 0);
            starsBox.querySelectorAll('.star').forEach(s => {
                const k = parseInt(s.dataset.star, 10);
                s.classList.toggle('on', k <= (n || value));
            });
        };
        starsBox.addEventListener('mouseleave', () => updateHover(0));
        starsBox.querySelectorAll('.star').forEach(s => {
            const n = parseInt(s.dataset.star, 10);
            s.addEventListener('mouseenter', () => updateHover(n));
            s.addEventListener('click', e => {
                e.stopPropagation();
                const cur = (ratings[impId] && ratings[impId].stars) || 0;
                if (n === cur) delete ratings[impId];
                else ratings[impId] = { stars: n, at: Date.now() };
                save(RATINGS_KEY, ratings);
                renderStars(container, impId, opts);
            });
        });
    }
    function mountAllStars() {
        document.querySelectorAll('[data-lab-stars]').forEach(el => {
            const id = parseInt(el.dataset.labStars, 10);
            if (id) renderStars(el, id);
        });
    }

    /* ============================================================
       TABS
       ============================================================ */
    function setupTabs() {
        const tabs = document.querySelectorAll('[data-lab-tab]');
        const cards = document.querySelectorAll('[data-lab-cat]');
        tabs.forEach(t => {
            t.addEventListener('click', () => {
                tabs.forEach(x => x.classList.remove('active'));
                t.classList.add('active');
                const cat = t.dataset.labTab;
                cards.forEach(c => {
                    c.style.display = (cat === 'todas' || c.dataset.labCat === cat) ? '' : 'none';
                });
            });
        });
    }

    /* ============================================================
       MODALES
       ============================================================ */
    function openModal(id)  { const el = document.getElementById(id); if (el) el.classList.add('open'); }
    function closeModal(id) { const el = document.getElementById(id); if (el) el.classList.remove('open'); }

    function fillPreview(imp) {
        const m = document.getElementById('lab-modal-preview');
        m.querySelector('[data-pv-icon]').textContent = imp.icon || '✨';
        m.querySelector('[data-pv-name]').textContent = imp.nombre;
        m.querySelector('[data-pv-cat]').textContent = imp.cat;
        m.querySelector('[data-pv-desc]').textContent = imp.long || imp.short || '';
        m.querySelector('[data-pv-price]').textContent = fmt(imp.price);

        const before = m.querySelector('[data-pv-before]');
        const after  = m.querySelector('[data-pv-after]');
        before.style.background = imp.before_url ? `url('${imp.before_url}') center/cover` : 'linear-gradient(135deg,#1a1a1e,#0f0f12)';
        after.style.background  = imp.after_url  ? `url('${imp.after_url}') center/cover`  : 'linear-gradient(135deg,#ff6a2b,#b5300a)';
        // reset al "después"
        m.querySelectorAll('[data-pv-view]').forEach(b => b.classList.toggle('on', b.dataset.pvView === 'despues'));
        before.style.opacity = 0; after.style.opacity = 1;

        const diffs = m.querySelector('[data-pv-diffs]');
        diffs.innerHTML = (imp.diffs || []).map(d => `
            <div class="lab-diff"><span class="d" style="background:${d.color || '#3ecf8e'}"></span>${d.texto || d.t || ''}</div>
        `).join('');

        const stars = m.querySelector('[data-pv-stars]');
        if (stars) renderStars(stars, imp.id, { big: true });

        const addBtn = m.querySelector('[data-pv-add]');
        const refreshAdd = () => {
            const inC = inCart(imp.id);
            addBtn.classList.toggle('added', inC);
            const span = addBtn.querySelector('[data-tw-copy]');
            const c = COPY[tweaks.tono] || COPY.tranquilo;
            if (span) span.textContent = inC ? c.added : c.previewAdd;
        };
        addBtn.onclick = () => { toggleCart(imp.id); refreshAdd(); };
        refreshAdd();

        // toggle antes/después
        m.querySelectorAll('[data-pv-view]').forEach(b => {
            b.onclick = () => {
                m.querySelectorAll('[data-pv-view]').forEach(x => x.classList.toggle('on', x === b));
                const isAntes = b.dataset.pvView === 'antes';
                before.style.opacity = isAntes ? 1 : 0;
                after.style.opacity  = isAntes ? 0 : 1;
            };
        });

        openModal('lab-modal-preview');
    }

    /* ----------- ORDER MODAL ------------- */
    function renderOrderItems() {
        const container = document.getElementById('lab-order-items');
        const { items, subtotal, freeOff, total } = cartTotals();
        const freeItemId = freeOff > 0
            ? items.slice().sort((a,b) => a.price - b.price)[0].id
            : null;

        container.innerHTML = items.map(it => {
            const isFree = freeItemId === it.id;
            const entry = getCartItem(it.id);
            const nota = entry ? (entry.nota || '') : '';
            return `
                <div class="lab-cart-row${nota ? ' note-open' : ''}" data-cart-row="${it.id}" style="${isFree ? 'border-color:rgba(139,195,74,.35);' : ''}">
                    <div class="lab-cart-line">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="cat-ic" style="width:38px; height:38px; font-size:18px;">${it.icon || '✨'}</span>
                            <div>
                                <div style="font-weight:600; font-size:14.5px;">${escapeHtml(it.nombre)}</div>
                                ${isFree ? '<div style="font-family:var(--lab-font-mono); font-size:11px; color:var(--lab-lime-soft); margin-top:3px;">tu primera gratis ✨</div>' : ''}
                            </div>
                        </div>
                        <div class="lab-price">
                            ${isFree
                                ? '<div class="amt" style="font-size:16px; color:var(--lab-lime-soft);">gratis</div>'
                                : `<div class="amt" style="font-size:18px;"><span>USD </span>${fmt(it.price)}</div>`}
                        </div>
                    </div>
                    <button class="note-toggle" data-toggle-note="${it.id}">${nota ? '✏ Editar nota' : '✏ Agregar nota'}</button>
                    <div class="note-field">
                        <textarea maxlength="400" data-note-input="${it.id}" placeholder="Ej: me gustaría que esta función sea de forma X en vez de Y…">${escapeHtml(nota)}</textarea>
                        ${nota ? `<div class="note-preview" data-note-preview="${it.id}">${escapeHtml(nota)}</div>` : ''}
                    </div>
                    <button class="note-toggle" data-remove-from-cart="${it.id}" style="color:var(--lab-mut-2);">× Quitar</button>
                </div>
            `;
        }).join('') || '<p style="color:var(--lab-mut); text-align:center; padding: 20px 0;">No agregaste ninguna mejora todavía.</p>';

        document.getElementById('lab-order-subtotal').textContent = fmt(subtotal);
        document.getElementById('lab-order-total').textContent    = fmt(total);
        const freeRow = document.getElementById('lab-order-free-row');
        if (freeOff > 0) { freeRow.style.display = 'flex'; document.getElementById('lab-order-free').textContent = fmt(freeOff); }
        else freeRow.style.display = 'none';

        // listeners por row
        container.querySelectorAll('[data-toggle-note]').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('.lab-cart-row');
                row.classList.toggle('note-open');
            });
        });
        container.querySelectorAll('[data-note-input]').forEach(ta => {
            ta.addEventListener('input', e => {
                const id = parseInt(ta.dataset.noteInput, 10);
                setCartNote(id, e.target.value);
                const prev = container.querySelector(`[data-note-preview="${id}"]`);
                if (prev) prev.textContent = e.target.value;
            });
        });
        container.querySelectorAll('[data-remove-from-cart]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.removeFromCart, 10);
                toggleCart(id);
                renderOrderItems();
                if (cart.length === 0) closeModal('lab-modal-order');
            });
        });
    }

    function submitOrder() {
        if (cart.length === 0) return;
        const btn = document.getElementById('lab-order-submit');
        btn.disabled = true;
        btn.style.opacity = .6;

        const form = new FormData();
        form.append('_token', BOOT.csrf);
        cart.forEach((c, i) => {
            form.append(`items[${i}][improvement_id]`, c.id);
            if (c.nota) form.append(`items[${i}][nota]`, c.nota);
        });

        fetch(BOOT.urls.crearOrden, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: form,
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            // abre WhatsApp y limpia el cart
            if (data.whatsapp_url) window.open(data.whatsapp_url, '_blank');
            cart = []; save(CART_KEY, cart);
            updateAddButtons(); updateCartDock();
            closeModal('lab-modal-order');
            // redirect opcional al historial
            if (data.redirect) setTimeout(() => { window.location.href = data.redirect; }, 800);
        })
        .catch(err => {
            alert('No pude enviar el pedido: ' + (err.message || 'error'));
        })
        .finally(() => {
            btn.disabled = false;
            btn.style.opacity = 1;
        });
    }

    /* ----------- CHAT IDEA (estilo Claude) ------------- */
    function setupIdea() {
        const ta      = document.getElementById('lab-chat-text');
        const count   = document.getElementById('lab-chat-count');
        const sendBtn = document.getElementById('lab-chat-send');
        const fileIn  = document.getElementById('lab-chat-file');
        const preview = document.getElementById('lab-chat-preview');
        const prevImg = document.getElementById('lab-chat-preview-img');
        const prevX   = document.getElementById('lab-chat-preview-x');
        const formBox = document.querySelector('[data-chat-form]');
        const sentBox = document.querySelector('[data-chat-sent]');
        if (!ta) return;

        let currentFile = null;
        const updateUI = () => {
            count.textContent = ta.value.length;
            const ok = ta.value.trim().length > 0 || currentFile !== null;
            sendBtn.disabled = !ok;
        };

        ta.addEventListener('input', updateUI);
        ta.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendBtn.click();
            }
        });

        // auto-resize textarea
        ta.addEventListener('input', () => {
            ta.style.height = 'auto';
            ta.style.height = Math.min(ta.scrollHeight, 160) + 'px';
        });

        // adjuntar imagen (input file o pegado con Ctrl+V)
        const attachImage = file => {
            if (!file || !file.type.startsWith('image/')) return;
            currentFile = file;
            const reader = new FileReader();
            reader.onload = e => {
                prevImg.src = e.target.result;
                preview.style.display = 'inline-block';
                updateUI();
            };
            reader.readAsDataURL(file);
        };

        fileIn && fileIn.addEventListener('change', () => attachImage(fileIn.files[0]));

        // pegar captura de pantalla con Ctrl+V
        ta.addEventListener('paste', e => {
            const items = (e.clipboardData || window.clipboardData || {}).items || [];
            for (const it of items) {
                if (it.type && it.type.startsWith('image/')) {
                    const file = it.getAsFile();
                    if (file) { attachImage(file); e.preventDefault(); }
                    break;
                }
            }
        });

        // quitar imagen
        prevX && prevX.addEventListener('click', () => {
            currentFile = null;
            fileIn.value = '';
            preview.style.display = 'none';
            prevImg.src = '';
            updateUI();
        });

        // enviar
        sendBtn.addEventListener('click', () => {
            const txt = ta.value.trim();
            if (!txt && !currentFile) return;

            sendBtn.disabled = true;
            formBox.classList.add('sending');
            sendBtn.classList.add('sending');

            const form = new FormData();
            form.append('_token', BOOT.csrf);
            if (txt) form.append('idea', txt);
            if (currentFile) form.append('imagen', currentFile);

            fetch(BOOT.urls.idea, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: form,
            })
            .then(r => r.json())
            .then(() => {
                // reset
                ta.value = '';
                ta.style.height = 'auto';
                currentFile = null;
                fileIn.value = '';
                preview.style.display = 'none';
                prevImg.src = '';
                updateUI();

                formBox.style.display = 'none';
                sentBox.style.display = 'flex';
                setTimeout(() => {
                    sentBox.style.display = 'none';
                    formBox.style.display = '';
                }, 3500);
            })
            .catch(() => {
                alert('No se pudo enviar la idea. Probá de nuevo.');
            })
            .finally(() => {
                formBox.classList.remove('sending');
                sendBtn.classList.remove('sending');
                updateUI();
            });
        });
    }

    /* ============================================================
       SIGNALS (dev)
       ============================================================ */
    function renderSignals() {
        const stats = document.getElementById('lab-sig-stats');
        const list  = document.getElementById('lab-sig-list');
        if (!stats || !list) return;
        const rows = IMPS.map(i => ({
            id: i.id, name: i.nombre, cat: i.cat,
            st: (ratings[i.id] && ratings[i.id].stars) || 0,
        })).sort((a, b) => b.st - a.st);
        const rated = rows.filter(r => r.st > 0);
        const avg = rated.length ? (rated.reduce((s, r) => s + r.st, 0) / rated.length).toFixed(1) : '—';
        stats.innerHTML = `
            <div class="sig-stat"><div class="n">${rated.length}<span>/${rows.length}</span></div><div class="l">calificadas</div></div>
            <div class="sig-stat"><div class="n">${avg}<span>★</span></div><div class="l">promedio</div></div>
            <div class="sig-stat"><div class="n">${rows.filter(r => r.st >= 4).length}</div><div class="l">con 4★ o más</div></div>
        `;
        const catColors = { visual:'#8bc34a', ux:'#38b6ff', performance:'#a78bfa', admin:'#f5a623' };
        list.innerHTML = rows.map(r => `
            <div class="sig-row" style="opacity:${r.st ? 1 : .45}">
                <span class="sig-dot" style="background:${catColors[r.cat] || '#9a9388'}"></span>
                <span class="sig-name">${escapeHtml(r.name)}</span>
                <div class="sig-bar"><i style="width:${r.st / 5 * 100}%"></i></div>
                <span class="sig-val">${r.st ? r.st + '★' : '—'}</span>
            </div>
        `).join('');
    }

    /* ============================================================
       TWEAKS PANEL (developer)
       ============================================================ */
    function syncTweakControls() {
        const panel = document.getElementById('lab-tweaks-panel');
        if (!panel) return;
        const greet = panel.querySelector('[data-twk="greet"]');
        if (greet && document.activeElement !== greet) greet.value = tweaks.greet || '';
        panel.querySelectorAll('[data-twk-swatches]').forEach(group => {
            const key = group.dataset.twkSwatches;
            group.querySelectorAll('button').forEach(b => {
                b.classList.toggle('on', b.dataset.val === tweaks[key]);
            });
        });
        panel.querySelectorAll('[data-twk-seg]').forEach(group => {
            const key = group.dataset.twkSeg;
            group.querySelectorAll('button').forEach(b => {
                b.classList.toggle('on', b.dataset.val === tweaks[key]);
            });
        });
        panel.querySelectorAll('[data-twk-toggle]').forEach(t => {
            const key = t.dataset.twkToggle;
            t.classList.toggle('on', !!tweaks[key]);
        });
    }
    function setupTweaks() {
        const fab   = document.getElementById('lab-tweaks-fab');
        const panel = document.getElementById('lab-tweaks-panel');
        if (!fab || !panel) return;
        fab.addEventListener('click', () => panel.classList.toggle('open'));

        const greet = panel.querySelector('[data-twk="greet"]');
        greet && greet.addEventListener('input', e => setTweak('greet', e.target.value));

        panel.querySelectorAll('[data-twk-swatches]').forEach(group => {
            const key = group.dataset.twkSwatches;
            group.querySelectorAll('button').forEach(b => {
                b.addEventListener('click', () => setTweak(key, b.dataset.val));
            });
        });
        panel.querySelectorAll('[data-twk-seg]').forEach(group => {
            const key = group.dataset.twkSeg;
            group.querySelectorAll('button').forEach(b => {
                b.addEventListener('click', () => setTweak(key, b.dataset.val));
            });
        });
        panel.querySelectorAll('[data-twk-toggle]').forEach(t => {
            t.addEventListener('click', () => setTweak(t.dataset.twkToggle, !tweaks[t.dataset.twkToggle]));
        });
    }

    /* ============================================================
       LOADER
       ============================================================ */
    function runLoaderTypewriter() {
        const el = document.getElementById('lab-loader-tw');
        if (!el) return Promise.resolve();
        // Soporta múltiples frases separadas por ' | ' en data-tw-text
        const text = (el.dataset.twText || 'iniciando reactor de mejoras…');
        const phrases = text.split('|').map(s => s.trim()).filter(Boolean);
        let phraseIdx = 0;

        return new Promise(resolve => {
            const typeNext = () => {
                const phrase = phrases[phraseIdx];
                el.textContent = '';
                let i = 0;
                const tick = () => {
                    if (i <= phrase.length) {
                        el.textContent = phrase.slice(0, i);
                        i++;
                        setTimeout(tick, 28 + Math.random() * 18);
                    } else {
                        phraseIdx++;
                        if (phraseIdx < phrases.length) {
                            setTimeout(typeNext, 500);
                        } else {
                            resolve();
                        }
                    }
                };
                tick();
            };
            typeNext();
        });
    }

    function hideLoader() {
        const ld = document.getElementById('lab-loader');
        if (!ld) return;
        runLoaderTypewriter();
        setTimeout(() => {
            ld.classList.add('gone');
            // Mostrar intro overlay después del preloader
            const intro = document.getElementById('lab-intro');
            if (intro) {
                intro.hidden = false;
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => intro.classList.add('visible'));
                });
            }
        }, 2600);
        setTimeout(() => ld.remove(), 3600);
    }

    function setupIntro() {
        const intro = document.getElementById('lab-intro');
        if (!intro) return;
        const dismiss = () => {
            intro.classList.add('exiting');
            setTimeout(() => { intro.hidden = true; intro.remove(); }, 400);
        };
        // Botón principal
        const enterBtn = document.getElementById('lab-intro-enter');
        if (enterBtn) enterBtn.addEventListener('click', dismiss);
        // Botón X
        const closeBtn = document.getElementById('lab-intro-close');
        if (closeBtn) closeBtn.addEventListener('click', dismiss);
        // Click en el fondo (fuera del panel interior)
        intro.addEventListener('click', e => {
            if (e.target === intro) dismiss();
        });
        // ESC
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !intro.hidden) dismiss();
        }, { once: true });
    }

    /* ============================================================
       UTILIDADES
       ============================================================ */
    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    }

    /* ============================================================
       SEÑALES: ME GUSTA / NO ME INTERESA
       ============================================================ */
    const PREFS_KEY = 'bl_lab_prefs_v1';
    let prefs = load(PREFS_KEY, { liked: [], skipped: [] });

    function savePrefs() { save(PREFS_KEY, prefs); }

    function applyCardState(id) {
        const wrap = document.querySelector(`.fade-up[data-lab-cat] > [data-improvement-id="${id}"]`)?.closest('.fade-up');
        if (!wrap) return;
        const card = wrap.querySelector('article');
        if (!card) return;
        const likeBtn = card.querySelector('.lab-sig-like');
        const isLiked   = prefs.liked.includes(id);
        const isSkipped = prefs.skipped.includes(id);
        card.classList.toggle('lab-card--liked', isLiked);
        card.classList.toggle('lab-card--skipped', isSkipped);
        if (likeBtn) likeBtn.classList.toggle('active', isLiked);
    }

    function rebuildSkippedSection() {
        const section  = document.getElementById('lab-skipped-section');
        const grid     = document.getElementById('lab-skipped-grid');
        const cntEl    = document.getElementById('lab-skipped-cnt');
        if (!section || !grid) return;

        const cnt = prefs.skipped.length;
        if (cnt === 0) { section.style.display = 'none'; return; }

        section.style.display = '';
        cntEl && (cntEl.textContent = cnt);

        // Mover cards skipped al grid oculto
        prefs.skipped.forEach(id => {
            const wrap = document.querySelector(`.fade-up[data-lab-cat] > [data-improvement-id="${id}"]`)?.closest('.fade-up');
            if (wrap && wrap.parentElement?.id !== 'lab-skipped-grid') {
                grid.appendChild(wrap);
            }
        });

        // Devolver al grid principal las que ya no son skipped
        grid.querySelectorAll('.fade-up').forEach(w => {
            const id = parseInt(w.querySelector('[data-improvement-id]')?.dataset?.improvementId);
            if (id && !prefs.skipped.includes(id)) {
                document.getElementById('lab-grid')?.appendChild(w);
            }
        });
    }

    function setupSignals() {
        // Aplicar estados guardados
        [...prefs.liked, ...prefs.skipped].forEach(id => applyCardState(id));
        rebuildSkippedSection();
        // Badges iniciales
        updateSidenavBadge('snav-megusta-cnt', prefs.liked.length);
        updateSidenavBadge('snav-guardadas-cnt', prefs.skipped.length);

        // Clicks en botones de señal (delegación global)
        document.addEventListener('click', e => {
            const likeBtn = e.target.closest('.lab-sig-like');
            const skipBtn = e.target.closest('.lab-sig-skip');

            if (likeBtn) {
                const id = parseInt(likeBtn.dataset.sigId);
                if (!id) return;
                if (prefs.liked.includes(id)) {
                    prefs.liked = prefs.liked.filter(x => x !== id);
                } else {
                    prefs.liked.push(id);
                    prefs.skipped = prefs.skipped.filter(x => x !== id);
                }
                savePrefs();
                applyCardState(id);
                rebuildSkippedSection();
                updateSidenavBadge('snav-megusta-cnt', prefs.liked.length);
                updateSidenavBadge('snav-guardadas-cnt', prefs.skipped.length);
                // Refrescar panel activo si corresponde
                const activePanel = document.querySelector('.lab-panel.active')?.dataset?.panel;
                if (activePanel === 'me-gusta' || activePanel === 'guardadas') populatePanel(activePanel);
            }

            if (skipBtn) {
                const id = parseInt(skipBtn.dataset.sigId);
                if (!id) return;
                if (!prefs.skipped.includes(id)) {
                    prefs.skipped.push(id);
                    prefs.liked = prefs.liked.filter(x => x !== id);
                }
                savePrefs();
                applyCardState(id);
                rebuildSkippedSection();
                updateSidenavBadge('snav-megusta-cnt', prefs.liked.length);
                updateSidenavBadge('snav-guardadas-cnt', prefs.skipped.length);
                const activePanel = document.querySelector('.lab-panel.active')?.dataset?.panel;
                if (activePanel === 'me-gusta' || activePanel === 'guardadas') populatePanel(activePanel);
            }
        });
    }

    /* ============================================================
       SIDENAV — panel switching
       ============================================================ */
    function setupSidenav() {
        const btns = document.querySelectorAll('.lab-sidenav-btn[data-nav]');
        const titleEl = document.getElementById('lab-panel-title');
        const TITLES = {
            actualizaciones: 'Actualizaciones',
            guardadas:        'Guardadas para después',
            'me-gusta':       'Me gusta',
            activas:          'Mis mejoras activas',
            carrito:          'Carrito',
            idea:             'Sugerir una idea',
        };

        function activatePanel(nav) {
            // Botones
            btns.forEach(b => b.classList.toggle('active', b.dataset.nav === nav));
            // Panels
            document.querySelectorAll('.lab-panel[data-panel]').forEach(p => {
                p.classList.toggle('active', p.dataset.panel === nav);
            });
            // Título topbar
            if (titleEl) titleEl.textContent = TITLES[nav] || nav;
            // Poblar panel
            populatePanel(nav);
        }

        btns.forEach(btn => {
            btn.addEventListener('click', () => activatePanel(btn.dataset.nav));
        });
    }

    /* ============================================================
       POPULATE PANELS — poblar paneles secundarios desde localStorage
       ============================================================ */
    function renderImpCard(imp) {
        const activa  = imp.activa;
        const proceso = imp.proceso;
        const bubbles = Array.from({ length: 8 }, (_, b) =>
            `<i style="left:${12+b*11}%;width:${6+(b%4)*3}px;height:${6+(b%4)*3}px;animation-delay:${b*0.18}s;animation-duration:${2.2+b*0.12}s;"></i>`
        ).join('');
        const badgeState = activa
            ? `<span class="lab-badge activa"><span class="bd"></span>Ya activo en tu web</span>`
            : proceso
                ? `<span class="lab-badge proceso"><span class="bd"></span>En camino</span>`
                : `<span class="lab-badge cat">${escapeHtml(imp.cat ? imp.cat.charAt(0).toUpperCase() + imp.cat.slice(1) : '')}</span>`;
        const foot = activa
            ? `<div class="lab-state-line activa"><span class="lab-state-dot"></span>REACTOR ACTIVO</div>`
            : proceso
                ? `<div class="lab-state-line proceso"><span class="lab-state-dot"></span>EN SÍNTESIS</div>`
                : `<div class="lab-price"><div class="amt"><span>USD </span>${Math.round(imp.price || 0)}</div></div>
                   <div class="lab-card-actions">
                     <button class="lab-icon-btn" title="Ver cómo queda" data-lab-action="preview" data-id="${imp.id}">
                       <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                     </button>
                     <button class="lab-add-btn${inCart(imp.id) ? ' added' : ''}" data-lab-action="add" data-id="${imp.id}" data-lab-add="${imp.id}">
                       <span data-tw-copy="add">${inCart(imp.id) ? '✓ Agregada' : 'Me interesa →'}</span>
                     </button>
                   </div>`;
        return `
        <div class="fade-up" data-lab-cat="${escapeHtml(imp.cat || '')}">
          <article class="lab-card cat-${escapeHtml(imp.cat || '')}" data-improvement-id="${imp.id}">
            <div class="meniscus"></div>
            <div class="cbubbles">${bubbles}</div>
            <div class="lab-card-top">
              <span class="cat-ic">${escapeHtml(imp.icon || '✨')}</span>
              <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                ${imp.nuevo ? '<span class="lab-badge nuevo">Nuevo</span>' : ''}
                ${badgeState}
              </div>
            </div>
            <h4>${escapeHtml(imp.nombre || '')}</h4>
            <p class="cdesc">${escapeHtml(imp.short || '')}</p>
            <div class="lab-card-foot">${foot}</div>
            <div class="lab-card-signals">
              <button class="lab-sig-like${prefs.liked.includes(imp.id) ? ' active' : ''}" data-sig-id="${imp.id}" title="Me gusta">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span>Me gusta</span>
              </button>
              <button class="lab-sig-skip" data-sig-id="${imp.id}" title="No me interesa ahora">No me interesa</button>
            </div>
          </article>
        </div>`;
    }

    function populatePanel(nav) {
        if (nav === 'guardadas') {
            const grid  = document.getElementById('guardadas-grid');
            const empty = document.getElementById('guardadas-empty');
            if (!grid) return;
            const items = prefs.skipped.map(id => IMP_BY_ID[id]).filter(Boolean);
            grid.innerHTML = items.map(renderImpCard).join('');
            if (empty) empty.style.display = items.length ? 'none' : '';
            mountAllStars();
        } else if (nav === 'me-gusta') {
            const grid  = document.getElementById('megusta-grid');
            const empty = document.getElementById('megusta-empty');
            if (!grid) return;
            const items = prefs.liked.map(id => IMP_BY_ID[id]).filter(Boolean);
            grid.innerHTML = items.map(renderImpCard).join('');
            if (empty) empty.style.display = items.length ? 'none' : '';
            mountAllStars();
        } else if (nav === 'activas') {
            const grid  = document.getElementById('activas-grid');
            const empty = document.getElementById('activas-empty');
            if (!grid) return;
            const items = IMPS.filter(i => i.activa);
            grid.innerHTML = items.map(renderImpCard).join('');
            if (empty) empty.style.display = items.length ? 'none' : '';
            mountAllStars();
        } else if (nav === 'carrito') {
            renderPanelCarrito();
        }
    }

    function renderPanelCarrito() {
        const itemsEl  = document.getElementById('panel-carrito-items');
        const emptyEl  = document.getElementById('panel-carrito-empty');
        const totalsEl = document.getElementById('panel-carrito-totals');
        if (!itemsEl) return;
        const { items, freeOff, subtotal, total } = cartTotals();

        if (items.length === 0) {
            itemsEl.innerHTML = '';
            if (emptyEl)  emptyEl.style.display  = '';
            if (totalsEl) totalsEl.style.display = 'none';
            return;
        }
        if (emptyEl)  emptyEl.style.display  = 'none';
        if (totalsEl) totalsEl.style.display = '';

        itemsEl.innerHTML = items.map(imp => `
            <div class="lab-pc-item">
                <div class="lab-pc-item-icon">${escapeHtml(imp.icon || '✨')}</div>
                <div class="lab-pc-item-info">
                    <div class="lab-pc-item-name">${escapeHtml(imp.nombre || '')}</div>
                    <div class="lab-pc-item-price">USD ${Math.round(imp.price || 0)}</div>
                </div>
                <button class="lab-pc-remove" data-lab-action="add" data-id="${imp.id}" title="Quitar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>`).join('');

        const subEl  = document.getElementById('panel-cart-subtotal');
        const freeEl = document.getElementById('panel-cart-free');
        const freeRow= document.getElementById('panel-cart-free-row');
        const totEl  = document.getElementById('panel-cart-total');
        if (subEl) subEl.textContent = fmt(subtotal);
        if (totEl) totEl.textContent = fmt(total);
        if (freeEl) freeEl.textContent = fmt(freeOff);
        if (freeRow) freeRow.style.display = freeOff > 0 ? 'flex' : 'none';
    }

    /* ============================================================
       SETUP IDEA PANEL
       ============================================================ */
    function setupIdeaPanel() {
        const ta      = document.getElementById('lab-idea-text');
        const countEl = document.getElementById('lab-idea-count');
        const sendBtn = document.getElementById('lab-idea-send');
        const fileIn  = document.getElementById('lab-idea-file');
        const imgWrap = document.getElementById('lab-idea-img-wrap');
        const imgPrev = document.getElementById('lab-idea-img-prev');
        const imgX    = document.getElementById('lab-idea-img-x');
        const formEl  = document.getElementById('lab-idea-form-panel');
        const sentEl  = document.getElementById('lab-idea-sent');
        if (!ta) return;

        let currentFile = null;

        const updateUI = () => {
            if (countEl) countEl.textContent = ta.value.length;
            sendBtn.disabled = !(ta.value.trim().length > 0 || currentFile);
        };

        ta.addEventListener('input', () => { updateUI(); ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 180) + 'px'; });

        const attachImage = file => {
            if (!file || !file.type.startsWith('image/')) return;
            currentFile = file;
            const reader = new FileReader();
            reader.onload = e => { imgPrev.src = e.target.result; imgWrap.style.display = ''; updateUI(); };
            reader.readAsDataURL(file);
        };

        fileIn && fileIn.addEventListener('change', () => attachImage(fileIn.files[0]));

        ta.addEventListener('paste', e => {
            const items = (e.clipboardData || window.clipboardData || {}).items || [];
            for (const it of items) {
                if (it.type && it.type.startsWith('image/')) { attachImage(it.getAsFile()); e.preventDefault(); break; }
            }
        });

        imgX && imgX.addEventListener('click', () => {
            currentFile = null; fileIn.value = ''; imgWrap.style.display = 'none'; imgPrev.src = ''; updateUI();
        });

        sendBtn.addEventListener('click', () => {
            const txt = ta.value.trim();
            if (!txt && !currentFile) return;
            sendBtn.disabled = true;
            const form = new FormData();
            form.append('_token', BOOT.csrf);
            if (txt) form.append('idea', txt);
            if (currentFile) form.append('imagen', currentFile);
            fetch(BOOT.urls.idea, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: form })
            .then(r => r.json())
            .then(() => {
                ta.value = ''; ta.style.height = 'auto'; currentFile = null; fileIn.value = ''; imgWrap.style.display = 'none'; imgPrev.src = '';
                if (formEl) formEl.style.display = 'none';
                if (sentEl) sentEl.style.display = 'flex';
                setTimeout(() => {
                    if (sentEl) sentEl.style.display = 'none';
                    if (formEl) formEl.style.display = '';
                    updateUI();
                }, 4000);
            })
            .catch(() => alert('No se pudo enviar la idea. Probá de nuevo.'))
            .finally(() => { sendBtn.disabled = false; updateUI(); });
        });
    }

    /* ============================================================
       SHADER BACKGROUND (WebGL · líneas plasma)
       ============================================================ */
    function setupShaderBg() {
        const canvas = document.getElementById('lab-shader-bg');
        if (!canvas) return;

        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (!gl) { canvas.style.display = 'none'; return; }

        const VS = `
            attribute vec4 aVertexPosition;
            void main() { gl_Position = aVertexPosition; }
        `;
        const FS = `
            precision highp float;
            uniform vec2 iResolution;
            uniform float iTime;

            const float overallSpeed = 0.2;
            const float gridSmoothWidth = 0.015;
            const float axisWidth = 0.05;
            const float majorLineWidth = 0.025;
            const float minorLineWidth = 0.0125;
            const float majorLineFrequency = 5.0;
            const float minorLineFrequency = 1.0;
            const float scale = 5.0;
            const vec4 lineColor = vec4(0.35, 0.45, 0.95, 1.0);
            const float minLineWidth = 0.01;
            const float maxLineWidth = 0.2;
            const float lineSpeed = 1.0 * overallSpeed;
            const float lineAmplitude = 1.0;
            const float lineFrequency = 0.2;
            const float warpSpeed = 0.2 * overallSpeed;
            const float warpFrequency = 0.5;
            const float warpAmplitude = 1.0;
            const float offsetFrequency = 0.5;
            const float offsetSpeed = 1.33 * overallSpeed;
            const float minOffsetSpread = 0.6;
            const float maxOffsetSpread = 2.0;
            const int linesPerGroup = 16;

            #define drawCircle(pos, radius, coord) smoothstep(radius + gridSmoothWidth, radius, length(coord - (pos)))
            #define drawSmoothLine(pos, halfWidth, t) smoothstep(halfWidth, 0.0, abs(pos - (t)))
            #define drawCrispLine(pos, halfWidth, t) smoothstep(halfWidth + gridSmoothWidth, halfWidth, abs(pos - (t)))
            #define drawPeriodicLine(freq, width, t) drawCrispLine(freq / 2.0, width, abs(mod(t, freq) - (freq) / 2.0))

            float random(float t) {
                return (cos(t) + cos(t * 1.3 + 1.3) + cos(t * 1.4 + 1.4)) / 3.0;
            }
            float getPlasmaY(float x, float horizontalFade, float offset) {
                return random(x * lineFrequency + iTime * lineSpeed) * horizontalFade * lineAmplitude + offset;
            }
            void main() {
                vec2 fragCoord = gl_FragCoord.xy;
                vec4 fragColor;
                vec2 uv = fragCoord.xy / iResolution.xy;
                vec2 space = (fragCoord - iResolution.xy / 2.0) / iResolution.x * 2.0 * scale;

                float horizontalFade = 1.0 - (cos(uv.x * 6.28) * 0.5 + 0.5);
                float verticalFade = 1.0 - (cos(uv.y * 6.28) * 0.5 + 0.5);

                space.y += random(space.x * warpFrequency + iTime * warpSpeed) * warpAmplitude * (0.5 + horizontalFade);
                space.x += random(space.y * warpFrequency + iTime * warpSpeed + 2.0) * warpAmplitude * horizontalFade;

                vec4 lines = vec4(0.0);
                vec4 bgColor1 = vec4(0.04, 0.07, 0.16, 1.0);
                vec4 bgColor2 = vec4(0.10, 0.08, 0.22, 1.0);

                for(int l = 0; l < linesPerGroup; l++) {
                    float normalizedLineIndex = float(l) / float(linesPerGroup);
                    float offsetTime = iTime * offsetSpeed;
                    float offsetPosition = float(l) + space.x * offsetFrequency;
                    float rand = random(offsetPosition + offsetTime) * 0.5 + 0.5;
                    float halfWidth = mix(minLineWidth, maxLineWidth, rand * horizontalFade) / 2.0;
                    float offset = random(offsetPosition + offsetTime * (1.0 + normalizedLineIndex)) * mix(minOffsetSpread, maxOffsetSpread, horizontalFade);
                    float linePosition = getPlasmaY(space.x, horizontalFade, offset);
                    float line = drawSmoothLine(linePosition, halfWidth, space.y) / 2.0 + drawCrispLine(linePosition, halfWidth * 0.15, space.y);

                    float circleX = mod(float(l) + iTime * lineSpeed, 25.0) - 12.0;
                    vec2 circlePosition = vec2(circleX, getPlasmaY(circleX, horizontalFade, offset));
                    float circle = drawCircle(circlePosition, 0.01, space) * 4.0;

                    line = line + circle;
                    lines += line * lineColor * rand;
                }

                fragColor = mix(bgColor1, bgColor2, uv.x);
                fragColor *= verticalFade;
                fragColor.a = 1.0;
                fragColor += lines;
                gl_FragColor = fragColor;
            }
        `;

        function compile(type, src) {
            const sh = gl.createShader(type);
            gl.shaderSource(sh, src);
            gl.compileShader(sh);
            if (!gl.getShaderParameter(sh, gl.COMPILE_STATUS)) {
                console.error('Shader compile:', gl.getShaderInfoLog(sh));
                gl.deleteShader(sh);
                return null;
            }
            return sh;
        }

        const prog = gl.createProgram();
        const vs = compile(gl.VERTEX_SHADER, VS);
        const fs = compile(gl.FRAGMENT_SHADER, FS);
        if (!vs || !fs) { canvas.style.display = 'none'; return; }
        gl.attachShader(prog, vs);
        gl.attachShader(prog, fs);
        gl.linkProgram(prog);
        if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
            console.error('Shader link:', gl.getProgramInfoLog(prog));
            canvas.style.display = 'none';
            return;
        }

        const buf = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buf);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 1,-1, -1,1, 1,1]), gl.STATIC_DRAW);

        const aPos = gl.getAttribLocation(prog, 'aVertexPosition');
        const uRes = gl.getUniformLocation(prog, 'iResolution');
        const uTime = gl.getUniformLocation(prog, 'iTime');

        function resize() {
            const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
            const w = canvas.clientWidth, h = canvas.clientHeight;
            canvas.width = Math.max(1, Math.floor(w * dpr));
            canvas.height = Math.max(1, Math.floor(h * dpr));
            gl.viewport(0, 0, canvas.width, canvas.height);
        }
        window.addEventListener('resize', resize);
        resize();

        const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const start = Date.now();
        function render() {
            const t = reduce ? 0 : (Date.now() - start) / 1000;
            gl.useProgram(prog);
            gl.uniform2f(uRes, canvas.width, canvas.height);
            gl.uniform1f(uTime, t);
            gl.bindBuffer(gl.ARRAY_BUFFER, buf);
            gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);
            gl.enableVertexAttribArray(aPos);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
            if (!reduce) requestAnimationFrame(render);
        }
        requestAnimationFrame(render);
    }

    /* ============================================================
       BOOT
       ============================================================ */
    document.addEventListener('DOMContentLoaded', () => {
        ensureLottieReady(mountLotties);
        setupShaderBg();

        // ELIMINADO: canvas matrix (rediseño minimalista sin matrix)
        // ensureBgCanvases();
        // startMatrix();
        // setupMatrixReactivity();
        applyTweaks();
        setupTabs();
        mountAllStars();
        updateAddButtons();
        updateCartDock();
        setupIdea();
        setupIdeaPanel();
        setupSidenav();
        setupIntro();
        setupSignals();
        if (BOOT.isDeveloper) setupTweaks();
        hideLoader();

        // delegación de acciones
        document.body.addEventListener('click', e => {
            const t = e.target.closest('[data-lab-action]');
            if (!t) return;
            const action = t.dataset.labAction;
            const id = parseInt(t.dataset.id || '0', 10);

            switch (action) {
                case 'add':
                    toggleCart(id);
                    break;
                case 'preview': {
                    const imp = IMP_BY_ID[id];
                    if (imp) fillPreview(imp);
                    break;
                }
                case 'open-order':
                    renderOrderItems();
                    openModal('lab-modal-order');
                    break;
                case 'open-signals':
                    renderSignals();
                    openModal('lab-modal-signals');
                    break;
                case 'close-modal':
                    closeModal(t.dataset.modal);
                    break;
                case 'close-tweaks':
                    document.getElementById('lab-tweaks-panel').classList.remove('open');
                    break;
            }
        });

        // submit order (modal)
        const orderBtn = document.getElementById('lab-order-submit');
        orderBtn && orderBtn.addEventListener('click', submitOrder);

        // submit order desde panel carrito
        const panelCartBtn = document.getElementById('panel-cart-submit');
        panelCartBtn && panelCartBtn.addEventListener('click', submitOrder);

        // signals clear
        const sigClear = document.getElementById('lab-sig-clear');
        sigClear && sigClear.addEventListener('click', () => {
            ratings = {};
            save(RATINGS_KEY, ratings);
            renderSignals();
            mountAllStars();
        });

        // backdrop click cierra
        document.querySelectorAll('.lab-scrim').forEach(bd => {
            bd.addEventListener('click', e => { if (e.target === bd) bd.classList.remove('open'); });
        });
        // escape cierra modales abiertos
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') document.querySelectorAll('.lab-scrim.open').forEach(s => s.classList.remove('open'));
        });

        // ===== SISTEMA OPERATIVO SIMULADO =====
        setupOS();

        // ===== BOTÓN FLOTANTE DE IDEAS =====
        setupFloatIdea();

        // limpiar canvas de fondo al navegar fuera del lab
        window.addEventListener('pagehide', removeBgCanvases);
    });
})();

function setupOS() {
    // Botón "Tengo una idea" del hero → abre el popup flotante (siempre, con o sin OS)
    const heroIdeaBtn = document.getElementById('lab-hero-idea-btn');
    if (heroIdeaBtn) heroIdeaBtn.addEventListener('click', () => {
        const floatTrigger = document.getElementById('lab-float-trigger');
        const floatPopup   = document.getElementById('lab-float-popup');
        if (floatPopup && floatPopup.hidden) floatTrigger && floatTrigger.click();
        setTimeout(() => { const ta = document.getElementById('lab-float-text'); ta && ta.focus(); }, 80);
    });

    const screen = document.getElementById('lab-laptop-screen');
    const icon = document.getElementById('lab-os-icon');
    const win = document.getElementById('lab-os-window');
    const minimizeBtn = document.getElementById('lab-window-minimize');
    const taskItem = document.getElementById('lab-os-task-item');
    const startBtn = document.getElementById('lab-os-start-btn');
    const startMenu = document.getElementById('lab-os-start-menu');
    const clock = document.getElementById('lab-os-clock');
    const menuIdeaBtn = document.getElementById('lab-os-idea-btn');

    if (!screen || !win) return;

    // Doble click en icono del escritorio abre la ventana
    if (icon) {
        icon.addEventListener('dblclick', () => openWindow());
    }

    // Minimizar
    if (minimizeBtn) {
        minimizeBtn.addEventListener('click', () => minimizeWindow());
    }

    // Click en task item togglea
    if (taskItem) {
        taskItem.addEventListener('click', () => {
            if (win.classList.contains('minimized')) openWindow();
            else minimizeWindow();
        });
    }

    // Start menu toggle
    if (startBtn && startMenu) {
        startBtn.addEventListener('click', e => {
            e.stopPropagation();
            startMenu.classList.toggle('open');
        });
        document.addEventListener('click', e => {
            if (!startMenu.contains(e.target) && e.target !== startBtn) {
                startMenu.classList.remove('open');
            }
        });
    }

    // Navegación del start menu (scroll dentro de .lab-window-body)
    document.querySelectorAll('[data-scroll-to]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.scrollTo;
            const target = document.getElementById(id);
            if (target) {
                openWindow();
                // esperar a que la ventana se abra para calcular posiciones correctas
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        const scrollContainer = document.querySelector('.lab-window-body');
                        if (scrollContainer) {
                            const top = target.getBoundingClientRect().top - scrollContainer.getBoundingClientRect().top + scrollContainer.scrollTop - 20;
                            scrollContainer.scrollTo({ top, behavior: 'smooth' });
                        }
                    });
                });
            }
            startMenu && startMenu.classList.remove('open');
        });
    });

    // Botones "Tengo una idea" → abren el popup flotante del átomo
    [heroIdeaBtn, menuIdeaBtn].forEach(btn => {
        if (btn) btn.addEventListener('click', () => {
            startMenu && startMenu.classList.remove('open');
            // Disparar apertura del popup flotante
            const floatTrigger = document.getElementById('lab-float-trigger');
            const floatPopup   = document.getElementById('lab-float-popup');
            if (floatPopup && floatPopup.hidden) floatTrigger && floatTrigger.click();
            // Foco en el textarea del popup
            setTimeout(() => {
                const ta = document.getElementById('lab-float-text');
                ta && ta.focus();
            }, 80);
        });
    });

    function openWindow() {
        win.classList.remove('minimized');
        screen.classList.remove('os-mode');
        taskItem && taskItem.classList.add('active');
    }

    function minimizeWindow() {
        win.classList.add('minimized');
        screen.classList.add('os-mode');
        taskItem && taskItem.classList.remove('active');
        // chat bar siempre visible — no se oculta al minimizar
    }

    // Reloj — actualiza hora (#lab-os-time) y fecha (#lab-os-date) separados
    const timeEl = document.getElementById('lab-os-time');
    const dateEl = document.getElementById('lab-os-date');
    const mbarClock = document.getElementById('lab-os-mbar-clock');
    function tick() {
        if (!clock && !mbarClock) return;
        const now = new Date();
        const timeStr = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
        const dateStr = now.toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'numeric' });
        if (timeEl) timeEl.textContent = timeStr;
        if (dateEl) dateEl.textContent = dateStr;
        if (!timeEl && clock) clock.textContent = timeStr;
        // menubar clock: "sáb 31/5 · 14:30"
        if (mbarClock) mbarClock.textContent = `${dateStr} · ${timeStr}`;
    }
    tick();
    setInterval(tick, 10000);
}

/* ============================================================
   BOTÓN FLOTANTE DE IDEAS
   ============================================================ */
function setupFloatIdea() {
    const wrap    = document.getElementById('lab-float-idea');
    const trigger = document.getElementById('lab-float-trigger');
    const popup   = document.getElementById('lab-float-popup');
    const closeBtn= document.getElementById('lab-float-popup-x');
    const hint    = document.getElementById('lab-float-hint');
    const ta      = document.getElementById('lab-float-text');
    const counter = document.getElementById('lab-float-count');
    const sendBtn = document.getElementById('lab-float-send');
    const fileIn  = document.getElementById('lab-float-file');
    const imgWrap = document.getElementById('lab-float-img-wrap');
    const imgPrev = document.getElementById('lab-float-img-prev');
    const imgX    = document.getElementById('lab-float-img-x');
    const sentDiv = document.getElementById('lab-float-popup-sent');
    const formDiv = document.getElementById('lab-float-popup-form');

    if (!wrap || !trigger) return;

    let currentFile = null;

    const updateUI = () => {
        if (counter) counter.textContent = ta ? ta.value.length : 0;
        if (sendBtn) sendBtn.disabled = !(ta && ta.value.trim().length > 0) && !currentFile;
    };

    function openPopup() {
        wrap.classList.add('open');
        popup.hidden = false;
        ta && setTimeout(() => ta.focus(), 40);
    }
    function closePopup() {
        popup.hidden = true;
        wrap.classList.remove('open');
    }
    function resetForm() {
        if (ta)      { ta.value = ''; ta.style.height = 'auto'; }
        currentFile = null;
        if (fileIn)  fileIn.value = '';
        if (imgWrap) imgWrap.style.display = 'none';
        if (imgPrev) imgPrev.src = '';
        if (formDiv) formDiv.style.display = '';
        if (sentDiv) sentDiv.style.display = 'none';
        updateUI();
    }

    trigger.addEventListener('click', () => popup.hidden ? openPopup() : closePopup());
    if (closeBtn) closeBtn.addEventListener('click', closePopup);

    // Ocultar hint al primer hover
    trigger.addEventListener('mouseenter', () => {
        if (hint) hint.style.transition = 'opacity .3s';
        if (hint) hint.style.opacity = '0';
        setTimeout(() => { if (hint) hint.style.display = 'none'; }, 300);
    }, { once: true });

    // Click fuera cierra
    document.addEventListener('click', e => {
        if (!popup.hidden && !wrap.contains(e.target)) closePopup();
    });

    // Textarea: counter + auto-resize + Enter para enviar
    if (ta) {
        ta.addEventListener('input', () => {
            updateUI();
            ta.style.height = 'auto';
            ta.style.height = Math.min(ta.scrollHeight, 160) + 'px';
        });
        ta.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendBtn && sendBtn.click(); }
        });
    }

    // Adjuntar imagen (input file o pegado con Ctrl+V)
    const attachImage = f => {
        if (!f || !f.type.startsWith('image/')) return;
        currentFile = f;
        const reader = new FileReader();
        reader.onload = ev => {
            if (imgPrev) imgPrev.src = ev.target.result;
            if (imgWrap) imgWrap.style.display = '';
            updateUI();
        };
        reader.readAsDataURL(f);
    };

    if (fileIn) {
        fileIn.addEventListener('change', () => attachImage(fileIn.files[0]));
    }

    // pegar captura de pantalla con Ctrl+V
    if (ta) {
        ta.addEventListener('paste', e => {
            const items = (e.clipboardData || window.clipboardData || {}).items || [];
            for (const it of items) {
                if (it.type && it.type.startsWith('image/')) {
                    const f = it.getAsFile();
                    if (f) { attachImage(f); e.preventDefault(); }
                    break;
                }
            }
        });
    }
    if (imgX) imgX.addEventListener('click', () => { currentFile = null; if (fileIn) fileIn.value = ''; if (imgWrap) imgWrap.style.display = 'none'; updateUI(); });

    // Envío
    if (sendBtn) {
        sendBtn.addEventListener('click', () => {
            const txt = ta ? ta.value.trim() : '';
            if (!txt && !currentFile) return;

            sendBtn.disabled = true;
            const fd = new FormData();
            fd.append('_token', BOOT.csrf);
            if (txt) fd.append('idea', txt);
            if (currentFile) fd.append('imagen', currentFile);

            fetch(BOOT.urls.idea, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: fd,
            })
            .then(r => r.json())
            .then(() => {
                if (formDiv) formDiv.style.display = 'none';
                if (sentDiv) sentDiv.style.display = '';
                setTimeout(() => { closePopup(); setTimeout(resetForm, 400); }, 2500);
            })
            .catch(() => { alert('No se pudo enviar. Intentá de nuevo.'); updateUI(); });
        });
    }
}
