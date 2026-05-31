// TCocina App JavaScript
document.addEventListener("DOMContentLoaded", function () {
    // Initialize cart
    initializeCart();

    // Initialize search functionality
    initializeSearch();

    // Initialize product interactions
    initializeProductInteractions();

    // Initialize modal event listeners
    initializeModalEventListeners();
});

// Cart functionality
function isMobileCartSheetView() {
    return !!document.getElementById("mobileCartBottomSheet");
}

const MOBILE_CART_COUPON_STORAGE_KEY = "tcocina_mobile_sheet_coupon";
const MOBILE_CART_COUPON_LENGTH = 8;
let mobileSheetAppliedCoupon = loadStoredMobileCartCoupon();

function loadStoredMobileCartCoupon() {
    try {
        const raw = localStorage.getItem(MOBILE_CART_COUPON_STORAGE_KEY);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== "object" || !parsed.code) return null;
        return parsed;
    } catch (e) {
        return null;
    }
}

function saveStoredMobileCartCoupon(coupon) {
    try {
        if (!coupon) {
            localStorage.removeItem(MOBILE_CART_COUPON_STORAGE_KEY);
            return;
        }
        localStorage.setItem(MOBILE_CART_COUPON_STORAGE_KEY, JSON.stringify(coupon));
    } catch (e) {
    }
}

function getCartSubtotalValue(cart) {
    return Math.round(
        (cart || []).reduce((sum, item) => {
            const basePrice = Number(item.unitPrice || item.price || 0);
            const variantsTotal = Array.isArray(item.variants)
                ? item.variants.reduce(
                      (acc, v) => acc + Number(v.priceModifier || 0),
                      0
                  )
                : 0;
            const optionsTotal = Array.isArray(item.options)
                ? item.options.reduce(
                      (acc, o) => acc + Number(o.priceModifier || 0),
                      0
                  )
                : 0;
            const unitPrice = basePrice + variantsTotal + optionsTotal;
            return sum + unitPrice * Number(item.quantity || 1);
        }, 0)
    );
}

function calculateCouponDiscountAmount(subtotal, coupon) {
    if (!coupon || subtotal <= 0) return 0;
    const percentage = Number(coupon.discount_percentage || 0);
    const directAmount = Number(coupon.discount_amount || 0);
    const discount = percentage > 0 ? Math.round(subtotal * (percentage / 100)) : Math.round(directAmount);
    return Math.max(0, Math.min(subtotal, discount));
}

function getMobileCartCouponSlots() {
    return Array.from(
        document.querySelectorAll("#mobileCartCouponSlots .mobile-cart-coupon-slot")
    );
}

function setMobileCartCouponError(message) {
    const errorEl = document.getElementById("mobileCartCouponError");
    if (!errorEl) return;
    if (message) {
        errorEl.textContent = message;
        errorEl.classList.remove("d-none");
    } else {
        errorEl.textContent = "";
        errorEl.classList.add("d-none");
    }
}

function markMobileCartCouponSlotsError(enable) {
    getMobileCartCouponSlots().forEach((slot) => {
        slot.classList.toggle("error", !!enable);
    });
}

function getMobileCartCouponCode() {
    return getMobileCartCouponSlots()
        .map((slot) => (slot.value || "").trim().toUpperCase())
        .join("");
}

function fillMobileCartCouponCode(code) {
    const clean = String(code || "")
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, "")
        .slice(0, MOBILE_CART_COUPON_LENGTH);
    const slots = getMobileCartCouponSlots();
    slots.forEach((slot, index) => {
        slot.value = clean[index] || "";
        slot.classList.toggle("filled", !!slot.value);
        slot.classList.remove("error");
    });
}

function updateMobileCouponAppliedText(subtotal, discount) {
    const body    = document.getElementById("mobileCartCouponBody");
    const toggle  = document.getElementById("mobileCartCouponToggle");
    if (!body) return;

    if (mobileSheetAppliedCoupon) {
        const couponName = (mobileSheetAppliedCoupon.name || mobileSheetAppliedCoupon.code || "").toUpperCase();
        const discountStr = discount > 0
            ? (typeof formatPrice === "function" ? formatPrice(discount) : `$${discount.toFixed(2)}`)
            : null;

        // Ocultar header toggle (ya no hace falta navegar a los inputs)
        if (toggle) toggle.style.display = "none";

        // Reemplazar body con el estado "aplicado"
        body.classList.remove("d-none");
        body.innerHTML = `
            <div class="mobile-cart-coupon-applied-state">
                <div class="mobile-cart-coupon-applied-badge">
                    <span class="mobile-cart-coupon-applied-code">${couponName}</span>
                    ${discountStr ? `<span class="mobile-cart-coupon-applied-discount">-${discountStr}</span>` : ""}
                </div>
                <div class="mobile-cart-coupon-applied-label">Cupón aplicado ✓</div>
                <button type="button" class="mobile-cart-coupon-remove-btn" id="mobileCartCouponRemoveBtn">Quitar cupón</button>
            </div>
        `;

        const removeBtn = document.getElementById("mobileCartCouponRemoveBtn");
        if (removeBtn) {
            removeBtn.addEventListener("click", function () {
                mobileSheetAppliedCoupon = null;
                saveStoredMobileCartCoupon(null);
                // Restaurar header y body a estado normal
                if (toggle) toggle.style.display = "";
                body.innerHTML = `
                    <div class="mobile-cart-coupon__row d-flex align-items-center justify-content-between">
                        <div id="mobileCartCouponSlots" class="mobile-cart-coupon__slots d-flex" aria-label="Ingresá tu cupón"></div>
                        <button type="button" id="mobileCartCouponApplyBtn" class="mobile-cart-coupon__apply-btn btn btn-sm btn-outline-danger mobile-cart-sheet__remove-btn" aria-label="Validar cupón">
                            <i class="fas fa-check" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="mobileCartCouponAppliedText" class="mobile-cart-coupon__applied d-none mt-1 text-success small text-center"></div>
                    <div id="mobileCartCouponError" class="mobile-cart-coupon__error d-none mt-1 text-danger small text-center"></div>
                `;
                initMobileCartCouponInput();
                // Reset button to default state (remove any state classes)
                const btn = document.getElementById("mobileCartCouponApplyBtn");
                if (btn) {
                    btn.classList.remove("state-loading", "state-success", "state-error");
                    btn.disabled = false;
                    const icon = btn.querySelector("i");
                    if (icon) icon.className = "fas fa-check";
                }
                // Clear any errors
                setMobileCartCouponError("");
                markMobileCartCouponSlotsError(false);
                updateCartOffcanvas();
            });
        }
        return;
    }

    // Sin cupón: restaurar header y asegurar inputs visibles
    if (toggle) toggle.style.display = "";
    // Solo restaurar si el body tiene el estado aplicado (no los inputs normales)
    if (body.querySelector(".mobile-cart-coupon-applied-state")) {
        body.innerHTML = `
            <div class="mobile-cart-coupon__row d-flex align-items-center justify-content-between">
                <div id="mobileCartCouponSlots" class="mobile-cart-coupon__slots d-flex" aria-label="Ingresá tu cupón"></div>
                <button type="button" id="mobileCartCouponApplyBtn" class="mobile-cart-coupon__apply-btn btn btn-sm btn-outline-danger mobile-cart-sheet__remove-btn" aria-label="Validar cupón">
                    <i class="fas fa-check" aria-hidden="true"></i>
                </button>
            </div>
            <div id="mobileCartCouponAppliedText" class="mobile-cart-coupon__applied d-none mt-1 text-success small text-center"></div>
            <div id="mobileCartCouponError" class="mobile-cart-coupon__error d-none mt-1 text-danger small text-center"></div>
        `;
        initMobileCartCouponInput();
    }
}

function playMobileCouponSuccessEffects() {
    try {
        const audio = new Audio("/audio/success.mp3");
        audio.volume = 0.5;
        audio.play().catch(() => {});
    } catch (e) {
    }

    // Crear overlay con blur
    const existingOverlay = document.getElementById('coupon-celebration-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }

    const overlay = document.createElement('div');
    overlay.id = 'coupon-celebration-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.3);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:1.5rem;padding:2rem;';
    
    overlay.innerHTML = `
        <lord-icon
            src="/lordicons/cupon.json"
            colors="primary:#ffffff,secondary:#ffffff"
            trigger="loop"
            style="width:160px;height:160px;">
        </lord-icon>
        <h2 style="color:#fff;font-size:2rem;font-weight:700;text-align:center;margin:0;text-shadow:0 2px 8px rgba(0,0,0,0.3);">Cupon Válido <span style="color:#22c55e;">✓</span></h2>
        <button id="coupon-confirm-btn" style="background:#0d6efd;color:#fff;border:none;border-radius:8px;padding:.65rem 2rem;font-size:1rem;font-weight:600;cursor:pointer;box-shadow:0 4px 14px rgba(13,110,253,.4);margin-top:.5rem;animation:lbBtnPulse 2s ease-in-out infinite;">
            Confirmar
        </button>
        <style>@keyframes lbBtnPulse{0%,100%{box-shadow:0 4px 14px rgba(13,110,253,.4),0 0 0 0 rgba(13,110,253,.0)}50%{box-shadow:0 4px 20px rgba(13,110,253,.6),0 0 18px 6px rgba(13,110,253,.25)}}</style>
    `;
    
    document.body.appendChild(overlay);

    // Cerrar overlay al hacer click en el botón
    document.getElementById('coupon-confirm-btn').addEventListener('click', function() {
        overlay.remove();
    });

    const existingConfetti = document.getElementById("confetti-container");
    if (existingConfetti) existingConfetti.remove();

    const confettiCount = 100;
    const colors = ["#ef4444", "#3b82f6", "#22c55e", "#eab308", "#8b5cf6", "#f97316"];
    const container = document.createElement("div");
    container.id = "confetti-container";
    container.className = "confetti-container";
    container.style.cssText = 'position:fixed;inset:0;z-index:100000;pointer-events:none;';
    document.body.appendChild(container);

    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement("div");
        confetti.className = "confetti-piece";
        confetti.style.left = `${Math.random() * 100}%`;
        confetti.style.top = `${-20 + Math.random() * 10}%`;
        confetti.style.backgroundColor = colors[i % colors.length];
        confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
        confetti.style.animation = `confetti-fall ${2.5 + Math.random() * 2.5}s ${
            Math.random() * 2
        }s linear forwards`;
        container.appendChild(confetti);
    }

    setTimeout(() => {
        if (container.parentNode) container.remove();
    }, 6000);
}

function setCouponBtnState(state) {
    const btn = document.getElementById("mobileCartCouponApplyBtn");
    if (!btn) return;
    const icon = btn.querySelector("i");
    btn.classList.remove("state-loading", "state-success", "state-error");
    btn.disabled = false;
    if (state === "loading") {
        btn.classList.add("state-loading");
        btn.disabled = true;
        if (icon) { icon.className = "fas fa-circle-notch"; }
    } else if (state === "success") {
        btn.classList.add("state-success");
        if (icon) { icon.className = "fas fa-check"; }
    } else if (state === "error") {
        btn.classList.add("state-error");
        if (icon) { icon.className = "fas fa-times"; }
    } else {
        if (icon) { icon.className = "fas fa-check"; }
    }
}

async function applyMobileCartCoupon() {
    const code = getMobileCartCouponCode();
    if (code.length !== MOBILE_CART_COUPON_LENGTH) {
        setMobileCartCouponError("El código debe tener 8 caracteres");
        markMobileCartCouponSlotsError(true);
        setCouponBtnState("error");
        return;
    }

    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const subtotal = getCartSubtotalValue(cart);
    if (subtotal <= 0) {
        setMobileCartCouponError("Agregá productos para aplicar un cupón");
        setCouponBtnState("error");
        return;
    }

    setCouponBtnState("loading");

    try {
        const response = await fetch("/api/coupons/validate", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document.querySelector('meta[name="csrf-token"]')?.content || "",
            },
            body: JSON.stringify({ code, subtotal }),
        });

        const data = await response.json();
        if (data.success && data.coupon) {
            mobileSheetAppliedCoupon = data.coupon;
            saveStoredMobileCartCoupon(mobileSheetAppliedCoupon);
            setMobileCartCouponError("");
            markMobileCartCouponSlotsError(false);
            fillMobileCartCouponCode(code);
            setCouponBtnState("success");
            updateCartOffcanvas();
            showNotification("¡Cupón aplicado exitosamente!", "success");
            playMobileCouponSuccessEffects();
        } else {
            setMobileCartCouponError(data.message || "Cupón no válido");
            markMobileCartCouponSlotsError(true);
            setCouponBtnState("error");
        }
    } catch (error) {
        setMobileCartCouponError("Error al validar el cupón. Intenta nuevamente.");
        markMobileCartCouponSlotsError(true);
        setCouponBtnState("error");
    }
}

function initMobileCartCouponInput() {
    const slotsWrap = document.getElementById("mobileCartCouponSlots");
    if (!slotsWrap || slotsWrap.dataset.initialized === "true") return;

    slotsWrap.dataset.initialized = "true";
    slotsWrap.innerHTML = "";

    for (let i = 0; i < MOBILE_CART_COUPON_LENGTH; i++) {
        const slot = document.createElement("input");
        slot.type = "text";
        slot.maxLength = 1;
        slot.className = "mobile-cart-coupon-slot";
        slot.dataset.index = String(i);
        slot.setAttribute("inputmode", "text");
        slot.setAttribute("autocomplete", "off");

        slot.addEventListener("input", function (e) {
            const raw = (e.data || slot.value || "").toUpperCase();
            const value = raw.replace(/[^A-Z0-9]/g, "").slice(0, 1);
            slot.value = value;
            slot.classList.toggle("filled", !!value);
            slot.classList.remove("error");
            setMobileCartCouponError("");
            setCouponBtnState("idle");

            const index = Number(slot.dataset.index || 0);
            if (value && index < MOBILE_CART_COUPON_LENGTH - 1) {
                const next = slotsWrap.querySelector(`[data-index="${index + 1}"]`);
                if (next) next.focus();
            } else if (value && index === MOBILE_CART_COUPON_LENGTH - 1) {
                // Último slot completado: validar automáticamente
                setTimeout(applyMobileCartCoupon, 80);
            }
        });

        slot.addEventListener("keydown", function (e) {
            const index = Number(slot.dataset.index || 0);
            if (e.key === "Backspace" && !slot.value && index > 0) {
                e.preventDefault();
                const prev = slotsWrap.querySelector(`[data-index="${index - 1}"]`);
                if (prev) {
                    prev.focus();
                    prev.value = "";
                    prev.classList.remove("filled", "error");
                }
            }
            if (e.key === "Enter") {
                e.preventDefault();
                applyMobileCartCoupon();
            }
        });

        slot.addEventListener("paste", function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData?.getData("text") || "")
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, "");
            if (!pasted) return;
            const startIndex = Number(slot.dataset.index || 0);
            const slots = getMobileCartCouponSlots();
            for (
                let i = 0;
                i < pasted.length && startIndex + i < MOBILE_CART_COUPON_LENGTH;
                i++
            ) {
                const target = slots[startIndex + i];
                if (!target) continue;
                target.value = pasted[i];
                target.classList.add("filled");
                target.classList.remove("error");
            }
            setMobileCartCouponError("");
            // Si el pegado completó todos los slots, validar automáticamente
            const allFilled = getMobileCartCouponSlots().every(s => s.value.length === 1);
            if (allFilled) setTimeout(applyMobileCartCoupon, 80);
        });

        slotsWrap.appendChild(slot);
    }

    if (mobileSheetAppliedCoupon?.code) {
        fillMobileCartCouponCode(mobileSheetAppliedCoupon.code);
    }

    const applyBtn = document.getElementById("mobileCartCouponApplyBtn");
    if (applyBtn && applyBtn.dataset.bound !== "true") {
        applyBtn.dataset.bound = "true";
        applyBtn.addEventListener("click", function() {
            // If button is in error state (red cross), clear the slots instead of verifying
            if (applyBtn.classList.contains("state-error")) {
                // Clear all slots
                const slots = getMobileCartCouponSlots();
                slots.forEach(slot => {
                    slot.value = "";
                    slot.classList.remove("filled", "error");
                });
                // Reset button to default state
                setCouponBtnState("idle");
                setMobileCartCouponError("");
                markMobileCartCouponSlotsError(false);
                // Focus first slot
                const firstSlot = slots[0];
                if (firstSlot) firstSlot.focus();
            } else {
                // Normal state: verify the coupon
                applyMobileCartCoupon();
            }
        });
    }
}

function getCartItemDetailsText(item) {
    let detailsText = "";
    if (item.configuration) {
        const configParts = [];
        if (item.configuration.medallones) {
            configParts.push(`Medallones: ${item.configuration.medallones}`);
        }
        if (item.configuration.tipo_medallon) {
            configParts.push(`Tipo: ${item.configuration.tipo_medallon}`);
        }
        if (
            item.configuration.aderezos &&
            item.configuration.aderezos.length > 0
        ) {
            configParts.push(
                `Aderezos: ${item.configuration.aderezos.join(", ")}`
            );
        }
        if (
            item.configuration.extras &&
            item.configuration.extras.length > 0
        ) {
            configParts.push(`Extras: ${item.configuration.extras.join(", ")}`);
        }
        if (item.configuration.dips && item.configuration.dips.length > 0) {
            configParts.push(`Dips: ${item.configuration.dips.join(", ")}`);
        }
        if (
            item.configuration.dip_extra &&
            item.configuration.dip_extra.length > 0
        ) {
            configParts.push(
                `Dip Extra: ${item.configuration.dip_extra.join(", ")}`
            );
        }
        detailsText = configParts.join(" | ");
    } else {
        const variantText =
            Array.isArray(item.variants) && item.variants.length
                ? item.variants.map((v) => v.value).join(", ")
                : "";
        const optionText =
            Array.isArray(item.options) && item.options.length
                ? item.options.map((o) => o.value).join(", ")
                : "";
        detailsText = [variantText, optionText].filter(Boolean).join(" | ");
    }
    return detailsText;
}

function renderMobileSheetCartItemsHtml(cart) {
    const placeholderImg =
        "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";

    if (!cart.length) {
        return {
            html: '<div class="text-center py-5 px-2"><i class="fas fa-shopping-cart fa-3x text-beach-primary opacity-50 mb-3 d-block"></i><p class="text-beach-brown mb-0">Tu carrito está vacío</p></div>',
            total: 0,
        };
    }

    let total = 0;
    const html = cart
        .map((item, index) => {
            const unitPrice = Number(item.price ?? 0);
            const itemTotal = unitPrice * item.quantity;
            total += itemTotal;
            const detailsText = getCartItemDetailsText(item);
            const meta = detailsText.trim() || "Sin opciones adicionales";
            const imgSrc = item.image ? "/images/" + item.image : placeholderImg;

            // Check if item is customizable (hamburguesas or acompañamientos)
            const categoryName = (item.categoryName || item.category || '').toLowerCase();
            const lineType = (item.lineType || '').toLowerCase();
            console.log('Mobile cart item:', item.name, 'Category:', categoryName, 'LineType:', lineType, 'Item:', item);
            const isHamburguesa = categoryName.includes('hamburguesa') || categoryName.includes('hamburguesas') || lineType.includes('hamburger');
            const isAcompanamiento = categoryName.includes('acompanamiento') || categoryName.includes('acompanamientos') || lineType.includes('side') || lineType.includes('acompanamiento');
            const isCustomizable = isHamburguesa || isAcompanamiento;
            console.log('Is customizable:', isCustomizable, 'Hamburguesa:', isHamburguesa, 'Acompanamiento:', isAcompanamiento);
            const editIconHtml = isCustomizable ? `
                <button type="button" class="edit-cart-item btn mobile-cart-sheet__edit-btn" data-index="${index}" title="Editar" style="padding: 0; background: none; border: none; margin-left: auto;">
                    <lord-icon src="/lordicons/edit.json" colors="primary:#0a2540,secondary:#0a2540" trigger="hover" style="width:20px;height:20px;"></lord-icon>
                </button>` : '';

            return `
            <div class="mobile-cart-sheet__line d-flex gap-2 py-2 border-bottom align-items-stretch" data-product-id="${
                item.productId
            }" data-cart-index="${index}" data-category="${categoryName}" data-quantity="${item.quantity}">
                <img class="mobile-cart-sheet__line-img rounded flex-shrink-0" src="${imgSrc}" alt="${
                item.name
            }" onerror="this.onerror=null;this.src='${placeholderImg}'">
                <div class="flex-grow-1 min-w-0 ${isCustomizable ? 'cursor-pointer' : ''}" ${isCustomizable ? `data-edit-trigger="${index}"` : ''}>
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="fw-semibold text-beach-dark small">${item.name}</div>
                        ${editIconHtml}
                    </div>
                    <div class="mobile-cart-sheet__line-meta text-muted small">${meta}</div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn mobile-cart-sheet__qty-btn" data-action="decrease" data-index="${index}">−</button>
                            <span class="quantity-display small fw-semibold px-2" style="min-width: 1.5rem; text-align: center;">${Number(
                                item.quantity
                            )}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn mobile-cart-sheet__qty-btn" data-action="increase" data-index="${index}">+</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-cart-item mobile-cart-sheet__remove-btn" title="Eliminar">✕</button>
                    </div>
                    <div class="text-end mt-1">
                        <span class="fw-semibold text-beach-primary small">${formatPrice(
                            Math.round(itemTotal)
                        )}</span>
                    </div>
                </div>
            </div>`;
        })
        .join("");

    return { html, total };
}

function initMobileCartSheet() {
    const sheet = document.getElementById("mobileCartBottomSheet");
    if (!sheet || sheet.dataset.mobileCartSheetInit === "true") return;
    sheet.dataset.mobileCartSheetInit = "true";

    sheet.addEventListener("click", function (e) {
        if (e.target.closest("[data-mobile-cart-sheet-dismiss]")) {
            closeMobileCartSheet();
        }
    });

    const chev = document.getElementById("mobileCartSheetCollapseBtn");
    if (chev) {
        chev.addEventListener("click", function () {
            closeMobileCartSheet();
        });
    }

    const closeDesktop = document.getElementById("mobileCartSheetCloseDesktop");
    if (closeDesktop) {
        closeDesktop.addEventListener("click", function () {
            closeMobileCartSheet();
        });
    }

    const mobileProceed = document.getElementById("mobileProceedToCart");
    if (mobileProceed) {
        mobileProceed.addEventListener("click", function () {
            window.location.href = "/checkout";
        });
    }

    initMobileCartCouponInput();
    initMobileCartCouponToggle();
    initMobileCartSwipe();
}

function initMobileCartSwipe() {
    const sheet = document.getElementById("mobileCartBottomSheet");
    const panel = sheet ? sheet.querySelector(".mobile-cart-sheet__panel") : null;
    if (!panel) return;

    var startY = 0;
    var currentY = 0;
    var dragging = false;
    var THRESHOLD = 80; // px para cerrar

    function onTouchStart(e) {
        // No iniciar drag si el toque es en la zona scrollable de items
        if (e.target.closest(".mobile-cart-sheet__items")) return;
        startY = e.touches[0].clientY;
        currentY = startY;
        dragging = true;
        panel.style.transition = "none";
    }

    function onTouchMove(e) {
        if (!dragging) return;
        currentY = e.touches[0].clientY;
        var delta = currentY - startY;
        if (delta < 0) delta = 0; // solo hacia abajo
        panel.style.transform = "translateY(" + delta + "px)";
    }

    function onTouchEnd() {
        if (!dragging) return;
        dragging = false;
        panel.style.transition = "";
        var delta = currentY - startY;
        if (delta > THRESHOLD) {
            closeMobileCartSheet();
        } else {
            panel.style.transform = "";
        }
    }

    panel.addEventListener("touchstart", onTouchStart, { passive: true });
    panel.addEventListener("touchmove",  onTouchMove,  { passive: true });
    panel.addEventListener("touchend",   onTouchEnd);
    panel.addEventListener("touchcancel", onTouchEnd);
}

function initMobileCartCouponToggle() {
    const toggle = document.getElementById("mobileCartCouponToggle");
    const body   = document.getElementById("mobileCartCouponBody");
    const chevron = toggle ? toggle.querySelector(".mobile-cart-coupon__chevron") : null;
    if (!toggle || !body || toggle.dataset.toggleBound === "true") return;
    toggle.dataset.toggleBound = "true";
    toggle.addEventListener("click", function () {
        const isOpen = !body.classList.contains("d-none");
        if (isOpen) {
            body.classList.add("d-none");
            if (chevron) chevron.classList.remove("open");
        } else {
            body.classList.remove("d-none");
            if (chevron) chevron.classList.add("open");
        }
    });
}

function openMobileCartSheet(opts) {
    opts = opts || {};
    const origin = opts.origin || "header";
    const sheet = document.getElementById("mobileCartBottomSheet");
    if (!sheet) return false;
    if (sheet.classList.contains("is-open")) return true;

    sheet.dataset.openOrigin = origin;
    updateCartOffcanvas();
    document.dispatchEvent(
        new CustomEvent("tcocina:mobile-cart-sheet-open", {
            detail: { origin: origin },
        })
    );

    sheet.setAttribute("aria-hidden", "false");
    document.body.classList.add("mobile-cart-sheet-open");

    const cartToggle = document.getElementById("cartToggle");
    if (cartToggle) cartToggle.setAttribute("aria-expanded", "true");
    const mobileCta = document.getElementById("mobileViewOrderCta");
    if (mobileCta) mobileCta.setAttribute("aria-expanded", "true");

    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            sheet.classList.add("is-open");
        });
    });
    return true;
}

function closeMobileCartSheet() {
    const sheet = document.getElementById("mobileCartBottomSheet");
    if (!sheet || !sheet.classList.contains("is-open")) return;

    const panel = sheet.querySelector(".mobile-cart-sheet__panel");
    if (panel) { panel.style.transform = ""; panel.style.transition = ""; }
    sheet.classList.remove("is-open");
    document.dispatchEvent(new CustomEvent("tcocina:mobile-cart-sheet-close"));
    document.body.classList.remove("mobile-cart-sheet-open");

    const cartToggle = document.getElementById("cartToggle");
    if (cartToggle) cartToggle.setAttribute("aria-expanded", "false");
    const mobileCta = document.getElementById("mobileViewOrderCta");
    if (mobileCta) mobileCta.setAttribute("aria-expanded", "false");

    window.setTimeout(function () {
        sheet.setAttribute("aria-hidden", "true");
    }, 420);
}

function initializeCart() {
    const cartToggle = document.getElementById("cartToggle");
    const cartBadge = document.getElementById("cartBadge");

    if (cartToggle && cartBadge) {
        updateCartBadge();

        cartToggle.addEventListener("click", function (e) {
            if (isMobileCartSheetView()) {
                e.preventDefault();
                const sheet = document.getElementById("mobileCartBottomSheet");
                if (sheet) {
                    if (sheet.classList.contains("is-open")) {
                        closeMobileCartSheet();
                    } else {
                        openMobileCartSheet({ origin: "header" });
                    }
                    return;
                }
            }

            const cartOffcanvas = document.getElementById("cartOffcanvas");
            if (cartOffcanvas && typeof bootstrap !== "undefined" && bootstrap.Offcanvas) {
                const offcanvas = new bootstrap.Offcanvas(cartOffcanvas);
                offcanvas.show();
            } else {
                window.location.href = "/cart";
            }
        });
    }

    // Initialize cart offcanvas if it exists
    initializeCartOffcanvas();
    initMobileCartSheet();

    // Delegación de eventos para botones del carrito
    document.addEventListener("click", function (e) {
        console.log('Click detected on:', e.target, 'closest .quantity-btn:', e.target.closest('.quantity-btn'), 'closest .remove-cart-item:', e.target.closest('.remove-cart-item'));
        
        // Handle quantity buttons first (before edit trigger)
        console.log('Checking quantity-btn...');
        if (e.target.closest(".quantity-btn")) {
            console.log('Inside quantity-btn block');
            e.preventDefault();
            e.stopPropagation();

            const button = e.target.closest(".quantity-btn");
            const action = button.dataset.action;
            const index = parseInt(button.dataset.index);

            console.log('Quantity button clicked:', action, 'index:', index);

            if (action === "increase") {
                console.log('Increasing quantity for index:', index);
                updateCartQuantity(index, 1);
            } else if (action === "decrease") {
                console.log('Decreasing quantity for index:', index);
                // Get current quantity from cart
                let cart = JSON.parse(localStorage.getItem("cart")) || [];
                const currentQuantity = cart[index] ? cart[index].quantity : 1;
                console.log('Current quantity:', currentQuantity);

                // If quantity is 1, show confirmation before removing
                if (currentQuantity === 1) {
                    const cartItem = button.closest("[data-product-id]");
                    if (!cartItem) { console.error("No se encontró el elemento del carrito"); return; }

                    // Guardar estado original del botón
                    const originalHTML = button.innerHTML;
                    const originalClass = button.className;

                    // Poner en modo confirm
                    button.dataset.confirming = "true";
                    button.innerHTML = `<span style="display:flex;align-items:center;gap:4px;font-size:11px;white-space:nowrap;">
                        ¿Eliminar?
                        <span class="qty-confirm-yes" style="cursor:pointer;background:#22c55e;color:#fff;border-radius:4px;padding:1px 5px;">✓</span>
                        <span class="qty-confirm-no" style="cursor:pointer;background:#ef4444;color:#fff;border-radius:4px;padding:1px 5px;">✕</span>
                    </span>`;
                    button.classList.remove("btn-outline-secondary");
                    button.classList.add("btn-outline-danger");

                    // Auto-cancelar después de 3 segundos si el usuario no decide
                    const autoCancel = setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.className = originalClass;
                        delete button.dataset.confirming;
                    }, 3000);

                    // Confirmar eliminación
                    button.querySelector(".qty-confirm-yes").addEventListener("click", function (ev) {
                        ev.stopPropagation();
                        clearTimeout(autoCancel);
                        button.innerHTML = originalHTML;
                        button.className = originalClass;
                        delete button.dataset.confirming;
                        console.log('Confirmed removal for index:', index);
                        removeFromCartByIndex(index);
                    });

                    // Cancelar
                    button.querySelector(".qty-confirm-no").addEventListener("click", function (ev) {
                        ev.stopPropagation();
                        clearTimeout(autoCancel);
                        button.innerHTML = originalHTML;
                        button.className = originalClass;
                        delete button.dataset.confirming;
                    });
                } else {
                    updateCartQuantity(index, -1);
                }
            }
            return;
        }

        // Handle remove button click (before edit trigger)
        if (e.target.closest(".remove-cart-item")) {
            e.preventDefault();
            e.stopPropagation();

            const button = e.target.closest(".remove-cart-item");

            // Si ya está en modo confirmación, ignorar (los sub-botones manejan esto)
            if (button.dataset.confirming === "true") return;

            const cartItem = button.closest("[data-product-id]");
            if (!cartItem) { console.error("No se encontró el elemento del carrito"); return; }

            // Get cartIndex from button data-index (mobile cart) or cartItem data-cartIndex (desktop cart)
            const cartIndex = button.dataset.index ? parseInt(button.dataset.index) : parseInt(cartItem.dataset.cartIndex);
            console.log('Remove button clicked for index:', cartIndex);

            // Guardar estado original del botón
            const originalHTML = button.innerHTML;
            const originalClass = button.className;

            // Poner en modo confirm
            button.dataset.confirming = "true";
            button.innerHTML = `<span style="display:flex;align-items:center;gap:4px;font-size:11px;white-space:nowrap;">
                ¿Eliminar?
                <span class="rm-confirm-yes" style="cursor:pointer;background:#22c55e;color:#fff;border-radius:4px;padding:1px 5px;">✓</span>
                <span class="rm-confirm-no" style="cursor:pointer;background:#ef4444;color:#fff;border-radius:4px;padding:1px 5px;">✕</span>
            </span>`;
            button.classList.remove("btn-outline-danger");
            button.classList.add("btn-outline-secondary");

            // Auto-cancelar después de 3 segundos si el usuario no decide
            const autoCancel = setTimeout(() => {
                button.innerHTML = originalHTML;
                button.className = originalClass;
                delete button.dataset.confirming;
            }, 3000);

            // Confirmar eliminación
            button.querySelector(".rm-confirm-yes").addEventListener("click", function (ev) {
                ev.stopPropagation();
                clearTimeout(autoCancel);
                button.innerHTML = originalHTML;
                button.className = originalClass;
                delete button.dataset.confirming;
                removeFromCartByIndex(cartIndex);
            });

            // Cancelar
            button.querySelector(".rm-confirm-no").addEventListener("click", function (ev) {
                ev.stopPropagation();
                clearTimeout(autoCancel);
                button.innerHTML = originalHTML;
                button.className = originalClass;
                delete button.dataset.confirming;
            });
            return;
        }

        // Handle edit button click
        if (e.target.closest(".edit-cart-item")) {
            e.preventDefault();
            e.stopPropagation();

            const button = e.target.closest(".edit-cart-item");
            const cartItem = button.closest("[data-product-id]");
            if (!cartItem) { console.error("No se encontró el elemento del carrito"); return; }

            const cartIndex = parseInt(button.dataset.index);
            console.log('Edit button clicked for index:', cartIndex);

            // Get the full cart and save the item data
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            if (cartIndex >= 0 && cartIndex < cart.length) {
                const item = cart[cartIndex];
                console.log('Opening edit modal for item:', item);
                localStorage.setItem('tcocina_edit_cart_item_index', cartIndex);
                
                // Open modal directly
                openEditModalForItem(item, cartIndex);
            }
            return;
        }

        // Handle clicking on product description to edit
        const editTrigger = e.target.closest("[data-edit-trigger]");
        if (editTrigger) {
            e.preventDefault();
            e.stopPropagation();

            const cartIndex = parseInt(editTrigger.dataset.editTrigger);
            console.log('Edit trigger clicked for index:', cartIndex);

            // Get the full cart and save the item data
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            if (cartIndex >= 0 && cartIndex < cart.length) {
                const item = cart[cartIndex];
                console.log('Opening edit modal for item:', item);
                localStorage.setItem('tcocina_edit_cart_item_index', cartIndex);
                
                // Open modal directly
                openEditModalForItem(item, cartIndex);
            }
            return;
        }
    });
}

// Function to open edit modal directly for a cart item
function openEditModalForItem(item, cartIndex) {
    console.log('Opening edit modal for item:', item, 'index:', cartIndex);
    
    // Determine if it's a hamburger (customizable) or accompaniment (dip)
    const categoryName = (item.categoryName || item.category || '').toLowerCase();
    const lineType = (item.lineType || '').toLowerCase();
    const isHamburguesa = categoryName.includes('hamburguesa') || categoryName.includes('hamburguesas') || lineType.includes('hamburger');
    const isAcompanamiento = categoryName.includes('acompanamiento') || categoryName.includes('acompanamientos') || lineType.includes('acompanamiento') || lineType.includes('side');
    
    let modalId = null;
    
    if (isHamburguesa) {
        modalId = 'hamburgerModal';
    } else if (isAcompanamiento) {
        modalId = 'dipModal';
    } else {
        console.error('Product is not customizable:', categoryName, lineType);
        return;
    }
    
    console.log('Opening modal:', modalId);

    // Set currentProductData for the modal
    window.currentProductData = {
        id: item.productId,
        name: item.name,
        price: item.price,
        image: item.image,
        description: item.description || '',
        defaultSauceId: item.defaultSauceId,
        defaultSauceValue: item.defaultSauceValue
    };

    // Update modal content for hamburger modal
    if (modalId === 'hamburgerModal') {
        const modalTitle = document.getElementById('hamburgerModalTitle');
        const modalImage = document.getElementById('hamburgerModalImage');
        const modalProductName = document.getElementById('hamburgerModalProductName');
        const modalDescription = document.getElementById('hamburgerModalDescription');
        const modalPrice = document.getElementById('hamburgerModalPrice');
        
        if (modalTitle) modalTitle.textContent = 'Personalizar';
        if (modalImage) modalImage.src = item.image ? '/images/' + item.image : '';
        if (modalProductName) modalProductName.textContent = item.name;
        if (modalDescription) modalDescription.textContent = item.description || '';
        if (modalPrice) modalPrice.textContent = '$' + item.price.toFixed(2);
    }

    // Update modal title for dip modal
    if (modalId === 'dipModal') {
        const modalTitle = document.getElementById('dipModalTitle');
        if (modalTitle) modalTitle.textContent = item.name + ' - Elegir Dip';
    }

    // Show modal directly using Bootstrap API
    setTimeout(() => {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error('Modal element not found:', modalId);
            return;
        }
        
        let modal = bootstrap.Modal.getInstance(modalElement);
        if (!modal) {
            modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
        }
        modal.show();
        
        console.log('Modal shown, populating configuration');
        
        // Populate modal with item's configuration
        setTimeout(() => {
            populateModalWithItemConfiguration(item, cartIndex);
        }, 500);
    }, 100);
}

// Function to populate modal with item configuration
function populateModalWithItemConfiguration(item, cartIndex) {
    const config = item.configuration || {};
    console.log('Populating modal with configuration:', config);

    // Set medallones (without triggering change event)
    if (config.medallones) {
        const medallonSelect = document.querySelector('[data-config-type="medallones"]');
        if (medallonSelect) {
            medallonSelect.value = config.medallones;
        }
    }

    // Set tipo medallon (without triggering change event)
    if (config.tipo_medallon) {
        const tipoSelect = document.querySelector('[data-config-type="tipo_medallon"]');
        if (tipoSelect) {
            tipoSelect.value = config.tipo_medallon;
        }
    }

    // Set dips (select)
    if (config.dips && Array.isArray(config.dips) && config.dips.length > 0) {
        const dipSelect = document.querySelector('[data-config-type="dips"]');
        if (dipSelect) {
            dipSelect.value = config.dips[0];
        }
    }

    // Set aderezos (select)
    if (config.aderezos && Array.isArray(config.aderezos) && config.aderezos.length > 0) {
        const aderezosSelect = document.querySelector('[data-config-type="aderezos"]');
        if (aderezosSelect) {
            aderezosSelect.value = config.aderezos[0];
        }
    }

    // Set extras (select)
    if (config.extras && Array.isArray(config.extras) && config.extras.length > 0) {
        const extrasSelect = document.querySelector('.extras-select');
        if (extrasSelect) {
            extrasSelect.value = config.extras[0];
        }
    }

    // Set dip extra (select)
    if (config.dip_extra && Array.isArray(config.dip_extra) && config.dip_extra.length > 0) {
        const dipExtraSelect = document.querySelector('.dip-extra-select');
        if (dipExtraSelect) {
            dipExtraSelect.value = config.dip_extra[0];
        }
    }

    // Update the price manually
    updateModalPrice(item.price);

    // Clean up localStorage after populating
    localStorage.removeItem('tcocina_edit_cart_item');
    localStorage.removeItem('tcocina_edit_cart_item_index');
}

// Function to update modal price
function updateModalPrice(basePrice) {
    const priceElement = document.getElementById('hamburgerModalPrice');
    if (!priceElement) return;

    let totalPrice = basePrice;

    // Add variant modifiers
    const variantSelects = document.querySelectorAll('.variant-select');
    variantSelects.forEach(select => {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            const modifier = parseFloat(selectedOption.dataset.priceModifier) || 0;
            totalPrice += modifier;
        }
    });

    // Add option modifiers
    const optionSelects = document.querySelectorAll('.option-select');
    optionSelects.forEach(select => {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            const modifier = parseFloat(selectedOption.dataset.priceModifier) || 0;
            totalPrice += modifier;
        }
    });

    priceElement.textContent = '$' + totalPrice.toFixed(2);
}

// Initialize modal event listeners
function initializeModalEventListeners() {
    // Add event listeners to all selects in the hamburger modal to update price
    const hamburgerModal = document.getElementById('hamburgerModal');
    if (hamburgerModal) {
        const selects = hamburgerModal.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                if (window.currentProductData) {
                    updateModalPrice(window.currentProductData.price);
                }
            });
        });

        // Add event listener to "add to cart" button
        const addToCartBtn = document.getElementById('hamburgerAddToCart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                handleHamburgerModalAddToCart();
            });
        }
    }

    // Add event listener to dip modal "add to cart" button
    const dipModal = document.getElementById('dipModal');
    if (dipModal) {
        const dipAddToCartBtn = document.getElementById('dipAddToCart');
        if (dipAddToCartBtn) {
            dipAddToCartBtn.addEventListener('click', function() {
                handleDipModalAddToCart();
            });
        }
    }
}

// Handle add to cart from hamburger modal
function handleHamburgerModalAddToCart() {
    console.log('Hamburger add to cart clicked');
    
    if (!window.currentProductData) {
        console.error('No current product data');
        return;
    }

    // Get configuration from modal
    const configuration = {};
    
    const medallonSelect = document.querySelector('[data-config-type="medallones"]');
    if (medallonSelect) configuration.medallones = medallonSelect.value;
    
    const tipoSelect = document.querySelector('[data-config-type="tipo_medallon"]');
    if (tipoSelect) configuration.tipo_medallon = tipoSelect.value;
    
    const dipSelect = document.querySelector('[data-config-type="dips"]');
    if (dipSelect && dipSelect.value) configuration.dips = [dipSelect.value];
    
    const aderezosSelect = document.querySelector('[data-config-type="aderezos"]');
    if (aderezosSelect && aderezosSelect.value) configuration.aderezos = [aderezosSelect.value];
    
    const extrasSelect = document.querySelector('.extras-select');
    if (extrasSelect && extrasSelect.value) configuration.extras = [extrasSelect.value];
    
    const dipExtraSelect = document.querySelector('.dip-extra-select');
    if (dipExtraSelect && dipExtraSelect.value) configuration.dip_extra = [dipExtraSelect.value];

    // Calculate final price
    let totalPrice = window.currentProductData.price;
    const variantSelects = document.querySelectorAll('.variant-select');
    variantSelects.forEach(select => {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            totalPrice += parseFloat(selectedOption.dataset.priceModifier) || 0;
        }
    });
    const optionSelects = document.querySelectorAll('.option-select');
    optionSelects.forEach(select => {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            totalPrice += parseFloat(selectedOption.dataset.priceModifier) || 0;
        }
    });

    // Check if we're editing an existing item
    const editIndex = localStorage.getItem('tcocina_edit_cart_item_index');
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (editIndex !== null) {
        const index = parseInt(editIndex);
        if (index >= 0 && index < cart.length) {
            // Replace existing item
            const configKey = `${window.currentProductData.id}_${JSON.stringify(configuration)}`;
            cart[index] = {
                productId: window.currentProductData.id,
                name: window.currentProductData.name,
                price: totalPrice,
                image: window.currentProductData.image,
                configuration: configuration,
                quantity: cart[index].quantity,
                configKey: configKey,
                lineType: 'hamburger'
            };
            localStorage.removeItem('tcocina_edit_cart_item_index');
            console.log('Updated cart item at index:', index);
        }
    } else {
        // Add new item
        const configKey = `${window.currentProductData.id}_${JSON.stringify(configuration)}`;
        const existingItem = cart.find(item => item.configKey === configKey);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                productId: window.currentProductData.id,
                name: window.currentProductData.name,
                price: totalPrice,
                image: window.currentProductData.image,
                configuration: configuration,
                quantity: 1,
                configKey: configKey,
                lineType: 'hamburger'
            });
        }
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartBadge();
    updateCartOffcanvas();

    // Close modal
    const modalElement = document.getElementById('hamburgerModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) modal.hide();
}

// Handle add to cart from dip modal
function handleDipModalAddToCart() {
    console.log('Dip add to cart clicked');
    
    if (!window.currentProductData) {
        console.error('No current product data');
        return;
    }

    // Get dip selection
    const dipSelect = document.getElementById('dipSelect');
    const dip = dipSelect ? dipSelect.value : '';

    // Calculate final price
    let totalPrice = window.currentProductData.price;
    if (dipSelect) {
        const selectedOption = dipSelect.options[dipSelect.selectedIndex];
        if (selectedOption) {
            totalPrice += parseFloat(selectedOption.dataset.priceModifier) || 0;
        }
    }

    // Configuration
    const configuration = { dip: dip };

    // Check if we're editing an existing item
    const editIndex = localStorage.getItem('tcocina_edit_cart_item_index');
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (editIndex !== null) {
        const index = parseInt(editIndex);
        if (index >= 0 && index < cart.length) {
            // Replace existing item
            const configKey = `${window.currentProductData.id}_${JSON.stringify(configuration)}`;
            cart[index] = {
                productId: window.currentProductData.id,
                name: window.currentProductData.name,
                price: totalPrice,
                image: window.currentProductData.image,
                configuration: configuration,
                quantity: cart[index].quantity,
                configKey: configKey,
                lineType: 'acompanamiento'
            };
            localStorage.removeItem('tcocina_edit_cart_item_index');
            console.log('Updated cart item at index:', index);
        }
    } else {
        // Add new item
        const configKey = `${window.currentProductData.id}_${JSON.stringify(configuration)}`;
        const existingItem = cart.find(item => item.configKey === configKey);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                productId: window.currentProductData.id,
                name: window.currentProductData.name,
                price: totalPrice,
                image: window.currentProductData.image,
                configuration: configuration,
                quantity: 1,
                configKey: configKey,
                lineType: 'acompanamiento'
            });
        }
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartBadge();
    updateCartOffcanvas();

    // Close modal
    const modalElement = document.getElementById('dipModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) modal.hide();
}

function initializeCartOffcanvas() {
    const proceedToCart = document.getElementById("proceedToCart");

    if (proceedToCart) {
        proceedToCart.addEventListener("click", function () {
            // Redirigir a la página del carrito completo
            window.location.href = "/cart";
        });
    }

    updateCartOffcanvas();
}

function updateCartOffcanvas() {
    const cartItems = document.getElementById("cartItems");
    const mobileCartSheetItems = document.getElementById("mobileCartSheetItems");
    const cartTotal = document.getElementById("cartTotal");
    const mobileCartSheetTotal = document.getElementById("mobileCartSheetTotal");
    const mobileCartSheetSubtotal = document.getElementById(
        "mobileCartSheetSubtotal"
    );
    const mobileCouponDiscountRow = document.getElementById(
        "mobileCartSheetCouponDiscountRow"
    );
    const mobileCouponDiscountLabel = document.getElementById(
        "mobileCartSheetCouponDiscountLabel"
    );
    const mobileCouponDiscountAmount = document.getElementById(
        "mobileCartSheetCouponDiscountAmount"
    );

    if (!cartItems && !mobileCartSheetItems) return;

    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const mobile = renderMobileSheetCartItemsHtml(cart);
    const subtotal = Math.round(mobile.total);

    if (!cart.length && mobileSheetAppliedCoupon) {
        mobileSheetAppliedCoupon = null;
        saveStoredMobileCartCoupon(null);
        fillMobileCartCouponCode("");
        setMobileCartCouponError("");
        markMobileCartCouponSlotsError(false);
    }

    const couponDiscount = calculateCouponDiscountAmount(
        subtotal,
        mobileSheetAppliedCoupon
    );
    const mobileTotalWithCoupon = Math.max(0, subtotal - couponDiscount);

    if (cartItems) {
        if (cart.length === 0) {
            cartItems.innerHTML =
                '<div class="text-center py-4"><i class="fas fa-shopping-cart fa-2x text-beach-brown mb-3"></i><p class="text-beach-brown">Tu carrito está vacío</p></div>';
            if (cartTotal) cartTotal.textContent = "$0";
        } else {
        let total = 0;

        cartItems.innerHTML = cart
            .map((item, index) => {
                // Use the price directly from item (already calculated with modifiers)
                const unitPrice = Number(item.price ?? 0);
                const itemTotal = unitPrice * item.quantity;
                total += itemTotal;
                const detailsText = getCartItemDetailsText(item);

                return `
            <div class="d-flex align-items-center gap-3 mb-3" data-product-id="${
                item.productId
            }" data-cart-index="${index}">
                <img src="${
                    item.image
                        ? "/images/" + item.image
                        : "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                }" 
                     alt="${item.name}" 
                     class="rounded" 
                     style="width: 60px; height: 60px; object-fit: cover;"
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'">
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-beach-dark">${item.name}</h6>
                    ${
                        detailsText
                            ? `<p class="mb-1 small text-beach-brown">${detailsText}</p>`
                            : ""
                    }
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary quantity-btn" data-action="decrease" data-index="${index}" style="font-size: 12px; padding: 2px 6px;">-</button>
                            <span class="quantity-display" style="min-width: 20px; text-align: center;">${
                                item.quantity
                            }</span>
                            <button class="btn btn-sm btn-outline-secondary quantity-btn" data-action="increase" data-index="${index}" style="font-size: 12px; padding: 2px 6px;">+</button>
                        </div>
                        <button class="btn btn-sm btn-outline-danger remove-cart-item" title="Eliminar" style="font-size: 12px;">
                            ✕
                        </button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="mb-0 fw-bold text-beach-primary">$${itemTotal.toFixed(
                            2
                        )}</p>
                    </div>
                </div>
            </div>
        `;
            })
            .join("");

        if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;
        }
    }

    if (mobileCartSheetItems) {
        mobileCartSheetItems.innerHTML = mobile.html;
    }
    const mobileSubtotalMoney =
        typeof formatPrice === "function"
            ? formatPrice(subtotal)
            : `$${subtotal.toFixed(2)}`;
    const mobileTotalMoney =
        typeof formatPrice === "function"
            ? formatPrice(mobileTotalWithCoupon)
            : `$${mobileTotalWithCoupon.toFixed(2)}`;
    if (mobileCartSheetSubtotal) mobileCartSheetSubtotal.textContent = mobileSubtotalMoney;
    if (mobileCartSheetTotal) mobileCartSheetTotal.textContent = mobileTotalMoney;

    if (mobileCouponDiscountRow && mobileCouponDiscountAmount) {
        if (couponDiscount > 0) {
            mobileCouponDiscountRow.classList.remove("d-none");
            mobileCouponDiscountAmount.textContent =
                "-" +
                (typeof formatPrice === "function"
                    ? formatPrice(couponDiscount)
                    : `$${couponDiscount.toFixed(2)}`);
            if (mobileCouponDiscountLabel) {
                const couponName =
                    mobileSheetAppliedCoupon?.name || mobileSheetAppliedCoupon?.code;
                mobileCouponDiscountLabel.textContent = couponName
                    ? `Descuento cupón (${couponName})`
                    : "Descuento cupón";
            }
        } else {
            mobileCouponDiscountRow.classList.add("d-none");
            if (mobileCouponDiscountLabel) {
                mobileCouponDiscountLabel.textContent = "Descuento cupón";
            }
        }
    }

    updateMobileCouponAppliedText(subtotal, couponDiscount);

    // Banner de figuritas de lealtad
    const loyaltyBanner = document.getElementById("mobileCartLoyaltyBanner");
    if (loyaltyBanner) {
        const totalStickers = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);

        // Guest: mostrar invitación a loguearse con Google
        if (totalStickers > 0 && window.__isGuest && window.__googleLoginUrl) {
            const label = totalStickers === 1 ? "figurita" : "figuritas";
            loyaltyBanner.innerHTML = `
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:nowrap;">
                    <span style="font-size:.76rem;font-weight:600;color:#166534;line-height:1.3;flex:1;min-width:0;">
                        Sumás <strong>${totalStickers} ${label}</strong> al Álbum — ingresá con Google
                    </span>
                    <span class="mobile-cart-loyalty-cancel" style="cursor:pointer;font-size:.72rem;color:#6b7280;text-decoration:underline;white-space:nowrap;flex-shrink:0;">No, gracias</span>
                </div>
                <div style="margin-top:7px;">
                    <a href="${window.__googleLoginUrl}" class="google-brand-btn google-brand-btn-sm" style="width:100%;justify-content:center;">
                        <svg viewBox="0 0 24 24" style="width:15px;height:15px;flex-shrink:0;" aria-hidden="true">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Ingresar con Google</span>
                    </a>
                </div>`;
            loyaltyBanner.style.cursor = "default";
            loyaltyBanner.classList.remove("d-none");

            loyaltyBanner.onclick = function (e) {
                if (!e.target.closest(".mobile-cart-loyalty-cancel")) return;
                e.stopPropagation();
                loyaltyBanner.classList.add("d-none");
            };
        } else if (totalStickers > 0) {
            const label = totalStickers === 1 ? "figurita" : "figuritas";
            const logoUrl = "/images/Tsinfondo.png";
            const dots = Array.from({ length: totalStickers }, () =>
                `<div class="mobile-cart-loyalty-dot"><img src="${logoUrl}" alt="figurita" aria-hidden="true"></div>`
            ).join("");
            // Calcular progreso con figuritas del carrito sumadas
            const ld = window.__loyaltyData || null;
            let progressHtml = "";
            if (ld && ld.target > 0) {
                const afterCart   = ld.current + totalStickers;
                const pct         = Math.min(100, Math.round((afterCart / ld.target) * 100));
                const remaining   = Math.max(0, ld.target - afterCart);
                const progressLabel = remaining > 0
                    ? `Y quedás a <strong>${remaining}</strong> figurita${remaining === 1 ? "" : "s"} de completar el álbum`
                    : `¡Con este pedido completás el álbum! 🎉`;
                progressHtml = `
                <div class="mobile-cart-loyalty-progress-wrap">
                    <div class="mobile-cart-loyalty-progress-label">${progressLabel}</div>
                    <div class="mobile-cart-loyalty-progress-track">
                        <div class="mobile-cart-loyalty-progress-bar" style="width:${pct}%"></div>
                    </div>
                    <div class="mobile-cart-loyalty-progress-pct">${ld.current} + ${totalStickers} = ${afterCart} / ${ld.target}</div>
                </div>`;
            }

            loyaltyBanner.innerHTML = `
                <div class="mobile-cart-loyalty-banner__text">
                    Con éste pedido sumás <strong>${totalStickers} ${label}</strong> a tu Álbum
                    <span class="mobile-cart-loyalty-arrow" aria-hidden="true" style="margin-left:auto;opacity:.5;font-size:.75rem;">›</span>
                </div>
                <div class="mobile-cart-loyalty-dots">${dots}</div>
                <div class="mobile-cart-loyalty-confirm d-none" style="flex-direction:column;gap:6px;margin-top:7px;">
                    ${progressHtml}
                    <div style="display:flex;align-items:center;gap:8px;font-size:.75rem;color:#166534;">
                        <a href="/mi-progreso" class="btn btn-sm" style="padding:2px 10px;font-size:.73rem;background:#166534;color:#fff;border-radius:6px;line-height:1.6;">Abrir álbum</a>
                        <span class="mobile-cart-loyalty-cancel" style="cursor:pointer;color:#6b7280;text-decoration:underline;">Cerrar</span>
                    </div>
                </div>`;
            loyaltyBanner.style.cursor = "pointer";
            loyaltyBanner.classList.remove("d-none");

            // Toggle confirm section via arrow or banner click
            const _collapseConfirm = () => {
                const confirm = loyaltyBanner.querySelector(".mobile-cart-loyalty-confirm");
                const arrow   = loyaltyBanner.querySelector(".mobile-cart-loyalty-arrow");
                if (!confirm) return;
                confirm.classList.add("d-none");
                confirm.style.display = "";
                if (arrow) arrow.style.transform = "";
            };
            const _expandConfirm = () => {
                const confirm = loyaltyBanner.querySelector(".mobile-cart-loyalty-confirm");
                const arrow   = loyaltyBanner.querySelector(".mobile-cart-loyalty-arrow");
                if (!confirm) return;
                confirm.classList.remove("d-none");
                confirm.style.display = "flex";
                if (arrow) arrow.style.transform = "rotate(90deg)";
            };

            loyaltyBanner.onclick = function (e) {
                // Cancelar (No / No, gracias) colapsa
                if (e.target.closest(".mobile-cart-loyalty-cancel")) {
                    e.stopPropagation();
                    _collapseConfirm();
                    return;
                }
                // Click en "Sí, vamos" deja navegar
                if (e.target.closest("a[href='/mi-progreso']")) return;

                const confirm = loyaltyBanner.querySelector(".mobile-cart-loyalty-confirm");
                if (!confirm) return;
                const isOpen = !confirm.classList.contains("d-none");
                isOpen ? _collapseConfirm() : _expandConfirm();
            };
        } else {
            loyaltyBanner.classList.add("d-none");
            loyaltyBanner.innerHTML = "";
        }
    }

    const mobileProceed = document.getElementById("mobileProceedToCart");
    if (mobileProceed) {
        mobileProceed.disabled = cart.length === 0;
        mobileProceed.setAttribute(
            "aria-disabled",
            cart.length === 0 ? "true" : "false"
        );
    }
}

function removeFromCartByIndex(index) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    // Verificar que el índice sea válido
    if (index >= 0 && index < cart.length) {
        // Eliminar el elemento del array
        cart.splice(index, 1);

        // Actualizar localStorage
        localStorage.setItem("cart", JSON.stringify(cart));

        // Actualizar UI
        updateCartBadge();
        updateCartOffcanvas();

        // Actualizar otras vistas del carrito si existen
        updateCartViews();

        // Mostrar notificación
        showNotification("Producto eliminado del carrito", "info");
    } else {
        console.error(
            "Índice inválido:",
            index,
            "Carrito tiene",
            cart.length,
            "elementos"
        );
        showNotification("Error al eliminar el producto", "error");
    }
}

// Mantener la función anterior por compatibilidad (pero usar la nueva)
function removeFromCartOffcanvas(configKey) {
    console.log(
        "Función removeFromCartOffcanvas llamada con configKey:",
        configKey
    );
    console.log("Esta función está deprecada, usar removeFromCartByIndex");
}

function updateCartBadge() {
    const cartBadge = document.getElementById("cartBadge");
    if (cartBadge) {
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

        if (totalItems > 0) {
            cartBadge.textContent = totalItems;
            cartBadge.classList.remove("d-none");
        } else {
            cartBadge.classList.add("d-none");
        }
    }
}

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById("searchInput");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const query = this.value.toLowerCase();
            filterProducts(query);
        });
    }
}

function filterProducts(query) {
    const productCards = document.querySelectorAll(".product-card");

    productCards.forEach((card) => {
        const productName =
            card.querySelector(".product-name")?.textContent.toLowerCase() ||
            "";
        const productDescription =
            card
                .querySelector(".product-description")
                ?.textContent.toLowerCase() || "";

        if (productName.includes(query) || productDescription.includes(query)) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}

// Product interactions
function initializeProductInteractions() {
    // Evitar doble binding en la página de catálogo (ésta tiene su propio script)
    const isCatalogPage = !!document.getElementById("productsGrid");
    if (isCatalogPage) {
        return;
    }

    // Add to cart buttons (con guard para evitar duplicados si se llama dos veces)
    document.querySelectorAll(".add-to-cart-btn").forEach((button) => {
        if (button.dataset.cartInit === "true") return;
        button.dataset.cartInit = "true";
        button.addEventListener("click", function (event) {
            if (event && typeof event.stopImmediatePropagation === "function") {
                event.stopImmediatePropagation();
            }
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            const productImage = this.dataset.productImage;

            addToCart(productId, productName, productPrice, productImage);
        });
    });

    // Product variant/option selectors (con guard)
    document
        .querySelectorAll(".variant-select, .option-select")
        .forEach((select) => {
            if (select.dataset.priceInit === "true") return;
            select.dataset.priceInit = "true";
            select.addEventListener("change", function () {
                updateProductPrice(this);
            });
        });
}

function addToCart(
    productId,
    productName,
    productPrice,
    productImage,
    variants = [],
    options = []
) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    // Crear configKey único para esta configuración del producto
    const configKey = `${productId}_${JSON.stringify(
        variants
    )}_${JSON.stringify(options)}`;

    // Check if this exact configuration already exists in cart
    const existingItem = cart.find((item) => {
        const itemConfigKey =
            item.configKey ||
            `${item.productId}_${JSON.stringify(
                item.variants || []
            )}_${JSON.stringify(item.options || [])}`;
        return itemConfigKey === configKey;
    });

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            productId: productId,
            name: productName,
            price: productPrice,
            image: productImage,
            variants: variants,
            options: options,
            quantity: 1,
            configKey: configKey,
        });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartBadge();
    updateCartOffcanvas();

    // Actualizar otras vistas del carrito si existen
    updateCartViews();

    showNotification("Producto agregado al carrito", "success");
}

function updateProductPrice(selectElement) {
    const productCard = selectElement.closest(".product-card");
    const basePrice = parseFloat(
        productCard.querySelector(".add-to-cart-btn").dataset.productPrice
    );
    const selectedVariants = Array.from(
        productCard.querySelectorAll(".variant-select")
    ).map((select) => ({
        name: select.dataset.variantName,
        value: select.value,
        priceModifier: parseFloat(
            select.selectedOptions[0].dataset.priceModifier || 0
        ),
    }));

    const selectedOptions = Array.from(
        productCard.querySelectorAll(".option-select")
    ).map((select) => ({
        name: select.dataset.optionName,
        value: select.value,
        priceModifier: parseFloat(
            select.selectedOptions[0].dataset.priceModifier || 0
        ),
    }));

    let totalPrice = basePrice;
    selectedVariants.forEach(
        (variant) => (totalPrice += variant.priceModifier)
    );
    selectedOptions.forEach((option) => (totalPrice += option.priceModifier));

    const priceElement = productCard.querySelector(".product-price");
    if (priceElement) {
        priceElement.textContent = `$${totalPrice.toFixed(2)}`;
    }
}

// Notification system
function showNotification(message, type = "info") {
    const notification = document.createElement("div");
    notification.className = `alert alert-${
        type === "success" ? "success" : type === "error" ? "danger" : "info"
    } alert-dismissible fade show position-fixed`;
    notification.style.cssText =
        "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Utility functions
function formatPrice(price) {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 0,
    }).format(price);
}

function formatDate(date) {
    return new Intl.DateTimeFormat("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(date));
}

// Función para actualizar cantidad en el carrito
function updateCartQuantity(index, change) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    if (index >= 0 && index < cart.length) {
        cart[index].quantity += change;

        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
            showNotification("Producto eliminado del carrito", "info");
        } else {
            showNotification("Cantidad actualizada", "success");
        }

        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartBadge();
        updateCartOffcanvas();
        updateCartViews();
    }
}

// Función para actualizar todas las vistas del carrito
function updateCartViews() {
    // Actualizar página del carrito si existe
    if (typeof renderCart === "function") {
        renderCart();
    }
    if (typeof updateTotals === "function") {
        updateTotals();
    }

    // Actualizar checkout si existe
    if (typeof updateOrderSummary === "function") {
        updateOrderSummary();
    }

    window.dispatchEvent(new CustomEvent("tcocina:cart-updated"));
    document.dispatchEvent(new CustomEvent("tcocina:cart-updated"));
}

// Export functions for global use
window.addToCart = addToCart;
window.removeFromCartOffcanvas = removeFromCartOffcanvas;
window.removeFromCartByIndex = removeFromCartByIndex;
window.updateCartQuantity = updateCartQuantity;
window.showNotification = showNotification;
window.formatPrice = formatPrice;
window.formatDate = formatDate;
window.updateCartViews = updateCartViews;
window.openMobileCartSheet = openMobileCartSheet;
window.closeMobileCartSheet = closeMobileCartSheet;

/**
 * Sistema de notificaciones de álbum/canje
 * Polling al cargar la página para mostrar toasts cuando el canje es aprobado/entregado.
 */
(function initLoyaltyNotifications() {
    // Solo si el usuario está logueado (detectamos por la presencia de un elemento en el DOM)
    const isLoggedIn = !!document.querySelector('[data-auth-user]') || !!document.querySelector('a[href*="/mis-datos"]');
    if (!isLoggedIn) return;

    const alreadyPolled = sessionStorage.getItem('loyaltyNotificationsPolled');
    if (alreadyPolled) return; // Solo una vez por sesión de navegación

    fetch('/api/loyalty/notifications')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            sessionStorage.setItem('loyaltyNotificationsPolled', '1');

            const approved = (data.approved || [])[0];
            const delivered = (data.delivered || [])[0];

            if (approved) {
                showLoyaltyToast('approved', approved.id, approved.reward_snapshot?.reward_value || 'tu premio');
            } else if (delivered) {
                showLoyaltyToast('delivered', delivered.id, delivered.reward_snapshot?.reward_value || 'tu premio');
            }
        })
        .catch(() => {});
})();

function showLoyaltyToast(type, redemptionId, rewardValue) {
    const isApproved = type === 'approved';
    const title = isApproved ? 'Tu premio fue aprobado 🎉' : 'Disfrutá tu premio 🍔';
    const message = isApproved
        ? `Acercate al local a retirar: <strong>${escapeHtml(rewardValue)}</strong>`
        : `Esperamos que hayas disfrutado: <strong>${escapeHtml(rewardValue)}</strong>`;

    // Crear toast
    const toast = document.createElement('div');
    toast.id = 'loyaltyToast';
    toast.style.cssText = `
        position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;
        background:rgba(0,0,0,0.35);backdrop-filter:blur(4px);
    `;
    toast.innerHTML = `
        <div style="background:#fff;border-radius:16px;max-width:420px;width:90%;padding:28px 24px;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,0.25);animation:slideUpFade 0.35s ease;">
            <div style="font-size:44px;margin-bottom:10px;">${isApproved ? '🎉' : '🍔'}</div>
            <h2 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#111827;">${title}</h2>
            <p style="margin:0 0 20px;font-size:15px;color:#4b5563;line-height:1.5;">${message}</p>
            <button id="loyaltyToastClose" style="background:#f59e0b;border:none;border-radius:10px;padding:12px 22px;font-size:15px;font-weight:700;color:#111827;cursor:pointer;">
                ${isApproved ? 'Entendido, gracias' : '¡Gracias!'}
            </button>
        </div>
    `;
    document.body.appendChild(toast);

    // Confeti
    try { triggerConfetti(); } catch (e) {}

    // Cerrar y marcar como visto
    toast.addEventListener('click', function (e) {
        if (e.target.id === 'loyaltyToastClose' || e.target === toast) {
            closeAndMarkSeen();
        }
    });

    function closeAndMarkSeen() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity .25s';
        setTimeout(() => toast.remove(), 260);
        // Marcar como visto en el servidor
        fetch(`/api/loyalty/notifications/${redemptionId}/seen`, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() } })
            .catch(() => {});
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }
}
