// TecoCina App JavaScript
document.addEventListener("DOMContentLoaded", function () {
    // Initialize cart
    initializeCart();

    // Initialize search functionality
    initializeSearch();

    // Initialize mobile menu
    initializeMobileMenu();

    // Initialize product interactions
    initializeProductInteractions();
});

// Cart functionality
function initializeCart() {
    const cartToggle = document.getElementById("cartToggle");
    const cartBadge = document.getElementById("cartBadge");

    if (cartToggle && cartBadge) {
        updateCartBadge();

        cartToggle.addEventListener("click", function () {
            // Open cart offcanvas if it exists, otherwise redirect to cart page
            const cartOffcanvas = document.getElementById("cartOffcanvas");
            if (cartOffcanvas) {
                const offcanvas = new bootstrap.Offcanvas(cartOffcanvas);
                offcanvas.show();
            } else {
                window.location.href = "/cart";
            }
        });
    }

    // Initialize cart offcanvas if it exists
    initializeCartOffcanvas();

    // Delegación de eventos para botones de eliminar del carrito
    document.addEventListener("click", function (e) {
        if (e.target.closest(".remove-cart-item")) {
            e.preventDefault();
            e.stopPropagation();

            const button = e.target.closest(".remove-cart-item");
            const cartItem = button.closest("[data-product-id]");
            const configKey = cartItem.dataset.configKey;

            removeFromCartOffcanvas(configKey);
        }
    });
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
    const cartTotal = document.getElementById("cartTotal");
    const stickyCartCount = document.getElementById("stickyCartCount");
    const stickyCartTotal = document.getElementById("stickyCartTotal");
    const stickyCartCTA = document.getElementById("stickyCartCTA");

    if (!cartItems) return; // Exit if cart offcanvas doesn't exist

    const cart = JSON.parse(localStorage.getItem("cart")) || [];

    if (cart.length === 0) {
        cartItems.innerHTML =
            '<div class="text-center py-4"><i class="fas fa-shopping-cart fa-2x text-beach-brown mb-3"></i><p class="text-beach-brown">Tu carrito está vacío</p></div>';
        if (cartTotal) cartTotal.textContent = "$0";
        if (stickyCartCTA) stickyCartCTA.classList.add("d-none");
    } else {
        let total = 0;
        let totalItems = 0;

        cartItems.innerHTML = cart
            .map((item) => {
                const basePrice = Number(item.unitPrice ?? item.price ?? 0);
                const variantsTotal = Array.isArray(item.variants)
                    ? item.variants.reduce(
                          (sum, v) => sum + Number(v.priceModifier || 0),
                          0
                      )
                    : 0;
                const optionsTotal = Array.isArray(item.options)
                    ? item.options.reduce(
                          (sum, o) => sum + Number(o.priceModifier || 0),
                          0
                      )
                    : 0;
                const unitPrice = basePrice + variantsTotal + optionsTotal;
                const itemTotal = unitPrice * item.quantity;
                total += itemTotal;
                totalItems += item.quantity;

                const variantText =
                    Array.isArray(item.variants) && item.variants.length
                        ? item.variants.map((v) => v.value).join(", ")
                        : "";
                const optionText =
                    Array.isArray(item.options) && item.options.length
                        ? item.options.map((o) => o.value).join(", ")
                        : "";
                const detailsText = [variantText, optionText]
                    .filter(Boolean)
                    .join(" | ");

                return `
            <div class="d-flex align-items-center gap-3 mb-3" data-product-id="${
                item.productId
            }" data-config-key="${item.configKey || item.productId}">
                <img src="${
                    item.image
                        ? String(item.image).startsWith("products/")
                            ? "/storage/" + item.image
                            : "/images/products/" + item.image
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
                    <p class="mb-1 small text-beach-brown">Cantidad: ${
                        item.quantity
                    }</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="mb-0 fw-bold text-beach-primary">$${itemTotal.toFixed(
                            2
                        )}</p>
                        <button class="btn btn-sm btn-outline-danger remove-cart-item" title="Eliminar" style="font-size: 12px;">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
        `;
            })
            .join("");

        if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;
        if (stickyCartCount) stickyCartCount.textContent = totalItems;
        if (stickyCartTotal)
            stickyCartTotal.textContent = `$${total.toFixed(2)}`;
        if (stickyCartCTA) stickyCartCTA.classList.remove("d-none");
    }
}

function removeFromCartOffcanvas(configKey) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    // Filtrar el producto a eliminar
    const targetKey = String(configKey);
    cart = cart.filter((item) => {
        const itemConfigKey = String(item.configKey || item.productId);
        return itemConfigKey !== targetKey;
    });

    // Actualizar localStorage
    localStorage.setItem("cart", JSON.stringify(cart));

    // Actualizar UI
    updateCartBadge();
    updateCartOffcanvas();

    // Mostrar notificación
    showNotification("Producto eliminado del carrito", "info");
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

// Mobile menu functionality
function initializeMobileMenu() {
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");
    const mobileMenu = document.getElementById("mobileMenu");

    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener("click", function () {
            mobileMenu.classList.toggle("show");
        });
    }
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

function addToCart(productId, productName, productPrice, productImage) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    // Check if product already exists in cart
    const existingItem = cart.find((item) => item.productId === productId);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            productId: productId,
            name: productName,
            price: productPrice,
            image: productImage,
            quantity: 1,
        });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartBadge();
    updateCartOffcanvas();
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

// Export functions for global use
window.addToCart = addToCart;
window.removeFromCartOffcanvas = removeFromCartOffcanvas;
window.showNotification = showNotification;
window.formatPrice = formatPrice;
window.formatDate = formatDate;
