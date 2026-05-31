// ========================================================================
// MENÚ PÚBLICO - FUNCIONES PRINCIPALES
// Sistema de carrito con localStorage y integración con Laravel
// ========================================================================

import Swal from "sweetalert2";

// Variables globales
let menuData = [];
let filteredMenu = [];
let cart = JSON.parse(localStorage.getItem("restaurantCart")) || [];
let currentMesa = null;
let currentMesaId = null;
let cartOffcanvas;
let dishModal;
let currentModalDish = null;
let menuSwiper = null;
let currentView = "grid"; // 'grid' or 'carousel'

// ========================================================================
// INICIALIZACIÓN
// ========================================================================

document.addEventListener("DOMContentLoaded", function () {
    // Obtener información de la mesa desde la URL o variable global
    const urlPath = window.location.pathname;
    const mesaMatch = urlPath.match(/\/menu\/(\d+)/);

    if (mesaMatch) {
        currentMesa = mesaMatch[1];
    } else if (typeof mesaNumber !== "undefined") {
        currentMesa = mesaNumber;
    }

    // Inicializar componentes
    cartOffcanvas = new bootstrap.Offcanvas(
        document.getElementById("cartOffcanvas")
    );
    dishModal = new bootstrap.Modal(document.getElementById("dishModal"));

    // Cargar datos
    loadMesaInfo();
    loadMenuData();
    loadCategorias();

    // Inicializar UI
    initSwiper();
    updateCartDisplay();
    updateViewButtons();
});

// ========================================================================
// CARGA DE DATOS
// ========================================================================

function loadMesaInfo() {
    if (!currentMesa) return;

    fetch(`/api/menu/mesa/${currentMesa}`)
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                currentMesaId = data.data.id;
            }
        })
        .catch((error) => {
            console.error("Error al cargar información de mesa:", error);
        });
}

function loadMenuData() {
    showLoading();

    fetch("/api/menu/platos")
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                menuData = data.data.map((plato) => ({
                    id: plato.id,
                    name: plato.nombre,
                    description: plato.descripcion || "Sin descripción",
                    price: parseFloat(plato.precio),
                    category: plato.categoria?.nombre?.toLowerCase() || "otros",
                    image: plato.imagen_url,
                    popular: plato.destacado || false,
                    chef_recommended: plato.destacado || false,
                    allergens: plato.alergenos || [], // Ya viene como array de objetos desde la API
                    ingredients: parseIngredients(plato.ingredientes),
                    tiempo_preparacion: plato.tiempo_preparacion || 0,
                }));

                filteredMenu = [...menuData];
                renderMenu(menuData);
                hideLoading();
            }
        })
        .catch((error) => {
            hideLoading();
            showNoResults();
            console.error("Error al cargar platos:", error);
        });
}

function loadCategorias() {
    fetch("/api/menu/categorias")
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                populateCategoryFilter(data.data);
            }
        })
        .catch((error) => {
            console.error("Error al cargar categorías:", error);
        });
}

// ========================================================================
// UTILIDADES
// ========================================================================

function parseAllergens(alergenos) {
    if (!alergenos) return [];
    try {
        return JSON.parse(alergenos);
    } catch {
        return alergenos.split(",").filter((a) => a.trim());
    }
}

function parseIngredients(ingredientes) {
    if (!ingredientes) return [];
    try {
        return JSON.parse(ingredientes);
    } catch {
        return ingredientes.split(",").filter((i) => i.trim());
    }
}

// ========================================================================
// RENDERIZADO DE UI
// ========================================================================

function showLoading() {
    document.getElementById("loading-state").classList.remove("d-none");
    document.getElementById("menu-grid").style.display = "none";
    document.getElementById("menu-carousel").style.display = "none";
    document.getElementById("no-results").classList.add("d-none");
}

function hideLoading() {
    document.getElementById("loading-state").classList.add("d-none");
    if (currentView === "grid") {
        document.getElementById("menu-grid").style.display = "";
    } else {
        document.getElementById("menu-carousel").style.display = "block";
    }
}

function showNoResults() {
    document.getElementById("no-results").classList.remove("d-none");
    document.getElementById("menu-grid").style.display = "none";
    document.getElementById("menu-carousel").style.display = "none";
    document.getElementById("loading-state").classList.add("d-none");
}

function renderMenu(dishes) {
    if (currentView === "grid") {
        renderGridView(dishes);
    } else {
        renderCarouselView(dishes);
    }
}

function renderGridView(dishes) {
    const menuGrid = document.getElementById("menu-grid");
    const noResults = document.getElementById("no-results");

    if (dishes.length === 0) {
        menuGrid.innerHTML = "";
        noResults.classList.remove("d-none");
        return;
    }

    noResults.classList.add("d-none");
    menuGrid.innerHTML = "";

    dishes.forEach((dish) => {
        const dishCard = createDishCard(dish);
        menuGrid.appendChild(dishCard);
    });
}

function renderCarouselView(dishes) {
    const carouselWrapper = document.querySelector(
        "#menu-carousel .swiper-wrapper"
    );
    const noResults = document.getElementById("no-results");

    if (dishes.length === 0) {
        carouselWrapper.innerHTML = "";
        noResults.classList.remove("d-none");
        return;
    }

    noResults.classList.add("d-none");
    carouselWrapper.innerHTML = "";

    dishes.forEach((dish) => {
        const slide = createCarouselSlide(dish);
        carouselWrapper.appendChild(slide);
    });

    // Update swiper after adding slides
    if (menuSwiper) {
        menuSwiper.update();
        menuSwiper.slideTo(0);
    }
}

function createCarouselSlide(dish) {
    const slide = document.createElement("div");
    slide.className = "swiper-slide";

    const dishCard = createDishCard(dish);
    slide.appendChild(dishCard.firstElementChild);

    return slide;
}

function createDishCard(dish) {
    const col = document.createElement("div");
    col.className = "col animate-fade-in";

    col.innerHTML = `
        <div class="card card-interactive h-100 position-relative p-0 overflow-hidden">
            ${
                dish.popular
                    ? '<div class="position-absolute top-0 start-0 m-3" style="z-index: 2;"><span class="badge bg-danger">Popular</span></div>'
                    : ""
            }
            ${
                dish.chef_recommended
                    ? '<div class="position-absolute top-0 end-0 m-3" style="z-index: 2;"><span class="badge badge-gold"><i class="bi bi-star-fill me-1"></i>Chef</span></div>'
                    : ""
            }
            
            <div class="img-hover-zoom position-relative" style="height: 160px; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
                <img 
                    src="${dish.image}" 
                    alt="${dish.name}"
                    class="card-img-top"
                    style="height: 100%; object-fit: cover;"
                    loading="lazy"
                    onerror="this.src='/storage/defaults/default_articulo.png'; this.onerror=null;"
                >
            </div>
            
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-light text-dark border">
                        ${getCategoryName(dish.category)}
                    </span>
                    <button 
                        onclick="quickAdd(${dish.id})" 
                        class="btn btn-sm btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;"
                        title="Agregar al carrito"
                    >
                        <i class="bi bi-plus" style="font-size: 0.9rem;"></i>
                    </button>
                </div>
                
                <h6 class="card-title font-playfair fw-bold mb-2" style="cursor: pointer; font-size: 1rem;" onclick="openDishModal(${
                    dish.id
                })">
                    ${dish.name}
                </h6>
                
                <p class="card-text text-secondary mb-3" style="line-height: 1.4; font-size: 0.85rem;">
                    ${dish.description.substring(0, 80)}${
        dish.description.length > 80 ? "..." : ""
    }
                </p>
                
                ${
                    dish.allergens && dish.allergens.length > 0
                        ? `
                <div class="d-flex gap-1 mb-3 flex-wrap">
                    ${dish.allergens
                        .map(
                            (allergen) => `
                        <img src="/images/alergenos/${allergen.icono}" 
                             alt="${allergen.nombre}" 
                             title="${allergen.nombre}"
                             class="alergeno-badge"
                             style="width: 24px; height: 24px; object-fit: contain;"
                             data-bs-toggle="tooltip">
                    `
                        )
                        .join("")}
                </div>
                `
                        : ""
                }
                
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="price-display" style="font-size: 1.1rem;">$${dish.price.toFixed(
                        2
                    )}</span>
                    <button 
                        onclick="openDishModal(${dish.id})" 
                        class="btn btn-sm btn-outline"
                        style="font-size: 0.8rem; padding: 0.25rem 0.5rem;"
                    >
                        Ver detalles <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    return col;
}

function getCategoryName(category) {
    const categories = {
        entradas: "Entradas",
        principales: "Principales",
        postres: "Postres",
        bebidas: "Bebidas",
        otros: "Otros",
    };
    return categories[category] || category;
}

function populateCategoryFilter(categorias) {
    const select = document.getElementById("category-filter");

    categorias.forEach((categoria) => {
        const option = document.createElement("option");
        option.value = categoria.nombre.toLowerCase();
        option.textContent = categoria.nombre;
        select.appendChild(option);
    });
}

// ========================================================================
// SWIPER
// ========================================================================

function initSwiper() {
    menuSwiper = new Swiper(".menuSwiper", {
        effect: "coverflow",
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: "auto",
        initialSlide: 0,
        coverflowEffect: {
            rotate: 20,
            stretch: 0,
            depth: 200,
            modifier: 1.5,
            slideShadows: true,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        keyboard: {
            enabled: true,
        },
        loop: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: true,
        },
    });
}

// ========================================================================
// VISTAS
// ========================================================================

function switchToGridView() {
    currentView = "grid";
    document.getElementById("menu-grid").style.display = "";
    document.getElementById("menu-carousel").style.display = "none";
    renderMenu(filteredMenu);
    updateViewButtons();
}

function switchToCarouselView() {
    currentView = "carousel";
    document.getElementById("menu-grid").style.display = "none";
    document.getElementById("menu-carousel").style.display = "block";
    renderMenu(filteredMenu);
    updateViewButtons();
}

// ==================== FUNCIONES DE ÓRDENES ====================

function showOrdersModal() {
    const ordersModal = new bootstrap.Modal(
        document.getElementById("ordersModal")
    );
    document.getElementById("modal-mesa-number").textContent = currentMesa;

    // Mostrar loading
    document.getElementById("orders-loading").style.display = "block";
    document.getElementById("orders-empty").style.display = "none";
    document.getElementById("orders-content").style.display = "none";

    ordersModal.show();

    // Cargar órdenes
    loadOrdersForMesa();
}

function loadOrdersForMesa() {
    if (!currentMesaId) {
        showEmptyOrders();
        return;
    }

    fetch(`/api/menu/mesa/${currentMesaId}/ordenes`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success && data.data.length > 0) {
                showOrders(data.data);
            } else {
                showEmptyOrders();
            }
        })
        .catch((error) => {
            console.error("Error al cargar órdenes:", error);
            showEmptyOrders();
        });
}

function showEmptyOrders() {
    document.getElementById("orders-loading").style.display = "none";
    document.getElementById("orders-empty").style.display = "block";
    document.getElementById("orders-content").style.display = "none";
}

function showOrders(orders) {
    document.getElementById("orders-loading").style.display = "none";
    document.getElementById("orders-empty").style.display = "none";
    document.getElementById("orders-content").style.display = "block";

    const container = document.getElementById("orders-container");
    container.innerHTML = "";

    orders.forEach((orden) => {
        const orderCard = createOrderCard(orden);
        container.appendChild(orderCard);
    });
}

function createOrderCard(orden) {
    const div = document.createElement("div");
    div.className = "col-md-6 col-lg-4 mb-4";

    const estadoClass = getEstadoClass(orden.estado);
    const estadoIcon = getEstadoIcon(orden.estado);

    let detallesHTML = "";
    orden.detalles.forEach((detalle) => {
        detallesHTML += `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <img src="${detalle.plato.imagen_url}" alt="${
            detalle.plato.nombre
        }" 
                         class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                    <div>
                        <h6 class="mb-0 fw-semibold">${
                            detalle.plato.nombre
                        }</h6>
                        <small class="text-muted">x${detalle.cantidad}</small>
                        ${
                            detalle.notas_especiales
                                ? `<small class="text-info d-block">${detalle.notas_especiales}</small>`
                                : ""
                        }
                    </div>
                </div>
                <span class="fw-bold">€${(
                    detalle.cantidad * detalle.precio_unitario
                ).toFixed(2)}</span>
            </div>
        `;
    });

    div.innerHTML = `
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Orden #${orden.numero}</h5>
                        <small class="text-muted">${formatDateTime(
                            orden.created_at
                        )}</small>
                    </div>
                    <span class="badge ${estadoClass}">
                        <i class="${estadoIcon} me-1"></i>${getEstadoText(
        orden.estado
    )}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    ${detallesHTML}
                </div>
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Total:</span>
                        <span class="price-display fs-5 fw-bold">€${parseFloat(
                            orden.total
                        ).toFixed(2)}</span>
                    </div>
                    ${
                        orden.notas
                            ? `<div class="alert alert-light border mb-3"><small><strong>Notas:</strong> ${orden.notas}</small></div>`
                            : ""
                    }
                    <div class="d-grid gap-2">
                        ${getEstadoButtons(orden)}
                    </div>
                </div>
            </div>
        </div>
    `;

    return div;
}

function getEstadoClass(estado) {
    const classes = {
        pendiente: "bg-warning text-dark",
        en_proceso: "bg-info text-white",
        lista: "bg-success text-white",
        entregada: "bg-secondary text-white",
        cancelada: "bg-danger text-white",
    };
    return classes[estado] || "bg-secondary text-white";
}

function getEstadoIcon(estado) {
    const icons = {
        pendiente: "bi bi-clock",
        en_proceso: "bi bi-gear",
        lista: "bi bi-check-circle",
        entregada: "bi bi-check2-all",
        cancelada: "bi bi-x-circle",
    };
    return icons[estado] || "bi bi-question-circle";
}

function getEstadoText(estado) {
    const texts = {
        pendiente: "Pendiente",
        en_proceso: "En Proceso",
        lista: "Lista",
        entregada: "Entregada",
        cancelada: "Cancelada",
    };
    return texts[estado] || estado;
}

function getEstadoButtons(orden) {
    let buttons = "";

    // Solo mostrar botón de cancelar para órdenes pendientes
    if (orden.estado === "pendiente") {
        buttons += `<button class="btn btn-sm btn-outline-danger w-100" onclick="updateOrderStatus(${orden.id}, 'cancelada')">
            <i class="bi bi-x-circle me-1"></i>Cancelar Orden
        </button>`;
    }

    // Para órdenes en proceso o listas, no mostrar botones (solo información)
    if (orden.estado === "en_proceso" || orden.estado === "lista") {
        buttons += `<div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>Tu orden está siendo preparada
        </div>`;
    }

    return buttons;
}

function updateOrderStatus(ordenId, nuevoEstado) {
    // Configuración especial para cancelación
    if (nuevoEstado === "cancelada") {
        Swal.fire({
            title: "¿Cancelar esta orden?",
            text: "Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, cancelar orden",
            cancelButtonText: "No, mantener",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                executeStatusUpdate(ordenId, nuevoEstado);
            }
        });
    } else {
        // Para otros estados, actualizar directamente
        executeStatusUpdate(ordenId, nuevoEstado);
    }
}

function executeStatusUpdate(ordenId, nuevoEstado) {
    fetch(`/api/menu/ordenes/${ordenId}/estado`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
        body: JSON.stringify({ estado: nuevoEstado }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Estado actualizado!",
                    text: `Orden marcada como "${getEstadoText(nuevoEstado)}"`,
                    timer: 2000,
                    showConfirmButton: false,
                });
                loadOrdersForMesa(); // Recargar órdenes
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error al actualizar el estado: " + data.message,
                });
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error al actualizar el estado",
            });
        });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

// Exponer funciones globalmente para uso en onclick
window.switchToGridView = switchToGridView;
window.switchToCarouselView = switchToCarouselView;
window.openDishModal = openDishModal;
window.quickAdd = quickAdd;
window.toggleCart = toggleCart;
window.removeFromCart = removeFromCart;
window.showConfirmOrderModal = showConfirmOrderModal;
window.confirmOrder = confirmOrder;
window.modalIncreaseQuantity = modalIncreaseQuantity;
window.modalDecreaseQuantity = modalDecreaseQuantity;
window.modalUpdateTotalPrice = modalUpdateTotalPrice;
window.modalAddToCart = modalAddToCart;
window.hideToast = hideToast;
window.filterMenu = filterMenu;
window.resetFilters = resetFilters;
window.showOrdersModal = showOrdersModal;
window.loadOrdersForMesa = loadOrdersForMesa;
window.updateOrderStatus = updateOrderStatus;
window.executeStatusUpdate = executeStatusUpdate;

function updateViewButtons() {
    const gridBtn = document.getElementById("grid-view-btn");
    const carouselBtn = document.getElementById("carousel-view-btn");

    if (currentView === "grid") {
        gridBtn.classList.add("active");
        carouselBtn.classList.remove("active");
    } else {
        gridBtn.classList.remove("active");
        carouselBtn.classList.add("active");
    }
}

// ========================================================================
// FILTROS Y BÚSQUEDA
// ========================================================================

function filterMenu() {
    const searchTerm = document
        .getElementById("search-input")
        .value.toLowerCase()
        .trim();
    const categoryFilter = document.getElementById("category-filter").value;
    const priceSort = document.getElementById("price-sort").value;

    let filtered = menuData.filter((dish) => {
        const matchesSearch =
            !searchTerm ||
            dish.name.toLowerCase().includes(searchTerm) ||
            dish.description.toLowerCase().includes(searchTerm);
        const matchesCategory =
            !categoryFilter || dish.category === categoryFilter;

        return matchesSearch && matchesCategory;
    });

    // Sort by price if selected
    if (priceSort === "asc") {
        filtered.sort((a, b) => a.price - b.price);
    } else if (priceSort === "desc") {
        filtered.sort((a, b) => b.price - a.price);
    }

    filteredMenu = filtered;
    renderMenu(filtered);
    updateActiveFiltersCount();
}

function resetFilters() {
    document.getElementById("search-input").value = "";
    document.getElementById("category-filter").value = "";
    document.getElementById("price-sort").value = "";
    filteredMenu = [...menuData];
    renderMenu(menuData);
    updateActiveFiltersCount();
}

function updateActiveFiltersCount() {
    const searchTerm = document.getElementById("search-input").value.trim();
    const categoryFilter = document.getElementById("category-filter").value;
    const priceSort = document.getElementById("price-sort").value;

    let count = 0;
    if (searchTerm) count++;
    if (categoryFilter) count++;
    if (priceSort) count++;

    const badge = document.getElementById("active-filters-count");
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = "inline-block";
    } else {
        badge.style.display = "none";
    }
}

// ========================================================================
// MODAL DE DETALLES
// ========================================================================

function openDishModal(dishId) {
    const dish = menuData.find((d) => d.id === dishId);
    if (!dish) return;

    currentModalDish = dish;

    // Update modal content
    document.getElementById("modal-dish-name").textContent = dish.name;
    document.getElementById("modal-dish-description").textContent =
        dish.description;
    document.getElementById(
        "modal-dish-price"
    ).textContent = `$${dish.price.toFixed(2)}`;
    document.getElementById("modal-dish-category").textContent =
        getCategoryName(dish.category);
    document.getElementById("modal-main-image").src = dish.image;
    document.getElementById("modal-main-image").alt = dish.name;

    // Show/hide chef badge
    const chefBadge = document.getElementById("modal-chef-badge");
    if (dish.chef_recommended) {
        chefBadge.style.display = "inline-block";
    } else {
        chefBadge.style.display = "none";
    }

    // Update allergens
    const allergensContainer = document.getElementById("modal-allergens");
    const allergensSection = document.getElementById("modal-allergens-section");

    if (dish.allergens && dish.allergens.length > 0) {
        allergensSection.style.display = "block";
        allergensContainer.innerHTML = dish.allergens
            .map(
                (allergen) =>
                    `<img src="/images/alergenos/${allergen.icono}" 
                          alt="${allergen.nombre}" 
                          title="${allergen.nombre}"
                          style="width: 32px; height: 32px; object-fit: contain; margin: 2px;"
                          data-bs-toggle="tooltip">`
            )
            .join("");
    } else {
        allergensSection.style.display = "none";
    }

    // Update ingredients
    const ingredientsContainer = document.getElementById("modal-ingredients");
    if (dish.ingredients && dish.ingredients.length > 0) {
        ingredientsContainer.innerHTML = dish.ingredients
            .map(
                (ingredient) => `
            <div class="col-6">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-warning">•</span>
                    <small>${ingredient}</small>
                </div>
            </div>
        `
            )
            .join("");
    } else {
        ingredientsContainer.innerHTML =
            '<div class="col-12"><small class="text-muted">No se especificaron ingredientes</small></div>';
    }

    // Reset form
    document.getElementById("modal-quantity").value = 1;
    document.getElementById("modal-notes").value = "";
    modalUpdateTotalPrice();

    // Show modal
    dishModal.show();
}

// Modal quantity functions
function modalIncreaseQuantity() {
    const input = document.getElementById("modal-quantity");
    input.value = parseInt(input.value) + 1;
    modalUpdateTotalPrice();
}

function modalDecreaseQuantity() {
    const input = document.getElementById("modal-quantity");
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        modalUpdateTotalPrice();
    }
}

function modalUpdateTotalPrice() {
    if (!currentModalDish) return;
    const quantity =
        parseInt(document.getElementById("modal-quantity").value) || 1;
    const total = currentModalDish.price * quantity;
    document.getElementById(
        "modal-total-price"
    ).textContent = `$${total.toFixed(2)}`;
}

function modalAddToCart() {
    if (!currentModalDish) return;

    const quantity = parseInt(document.getElementById("modal-quantity").value);
    const notes = document.getElementById("modal-notes").value.trim();

    const existingItem = cart.find(
        (item) => item.dishId === currentModalDish.id && item.notes === notes
    );

    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        const cartItem = {
            id: Date.now(),
            dishId: currentModalDish.id,
            name: currentModalDish.name,
            price: currentModalDish.price,
            quantity: quantity,
            notes: notes,
            image: currentModalDish.image,
        };
        cart.push(cartItem);
    }

    updateCartDisplay();
    saveCartToStorage();
    showToast(`${currentModalDish.name} agregado al pedido (${quantity}x)`);

    // Close modal
    dishModal.hide();
}

// ========================================================================
// CARRITO
// ========================================================================

function quickAdd(dishId) {
    const dish = menuData.find((d) => d.id === dishId);
    if (!dish) return;

    const existingItem = cart.find((item) => item.dishId === dishId);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        const cartItem = {
            id: Date.now(),
            dishId: dish.id,
            name: dish.name,
            price: dish.price,
            quantity: 1,
            notes: "",
            image: dish.image,
        };
        cart.push(cartItem);
    }

    updateCartDisplay();
    saveCartToStorage();
    showToast(`${dish.name} agregado al carrito`);
}

function toggleCart() {
    cartOffcanvas.toggle();
}

function updateCartDisplay() {
    const cartItems = document.getElementById("cart-items");
    const cartCount = document.getElementById("cart-count");
    const cartTotal = document.getElementById("cart-total");
    const checkoutBtn = document.getElementById("checkout-btn");

    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
    cartCount.style.display = totalItems > 0 ? "inline-block" : "none";

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-bag-x text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                <p class="text-secondary mt-3 mb-1 fw-semibold">Tu pedido está vacío</p>
                <small class="text-muted">Agrega algunos platos deliciosos</small>
            </div>
        `;
        cartTotal.textContent = "$0.00";
        checkoutBtn.disabled = true;
        return;
    }

    let total = 0;
    cartItems.innerHTML = "";

    cart.forEach((item) => {
        total += item.price * item.quantity;

        const cartItem = document.createElement("div");
        cartItem.className = "card mb-3 border-0 shadow-sm";
        cartItem.innerHTML = `
            <div class="card-body p-3">
                <div class="d-flex gap-3">
                    <img src="${item.image}" alt="${
            item.name
        }" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-1">${item.name}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-secondary">Cantidad: ${
                                item.quantity
                            }</small>
                            <span class="price-display small">$${(
                                item.price * item.quantity
                            ).toFixed(2)}</span>
                        </div>
                        ${
                            item.notes
                                ? `<small class="text-muted d-block mt-1">${item.notes}</small>`
                                : ""
                        }
                    </div>
                    <button 
                        onclick="removeFromCart(${item.id})" 
                        class="btn btn-sm btn-link text-danger p-0 align-self-start"
                        title="Eliminar"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        cartItems.appendChild(cartItem);
    });

    cartTotal.textContent = `$${total.toFixed(2)}`;
    checkoutBtn.disabled = false;
}

function removeFromCart(itemId) {
    cart = cart.filter((item) => item.id !== itemId);
    updateCartDisplay();
    saveCartToStorage();
    showToast("Plato eliminado del carrito", "warning");
}

function showConfirmOrderModal() {
    if (cart.length === 0) return;

    // Llenar el resumen del pedido
    const orderSummary = document.getElementById("order-summary");
    const modalOrderTotal = document.getElementById("modal-order-total");

    // Generar HTML del resumen
    let summaryHTML = '<div class="list-group list-group-flush">';
    cart.forEach((item) => {
        const totalPrice = (
            parseFloat(item.price) * parseInt(item.quantity)
        ).toFixed(2);
        summaryHTML += `
            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <div class="d-flex align-items-center">
                    <img src="${item.image}" alt="${item.name}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                    <div>
                        <h6 class="mb-0 fw-semibold">${item.name}</h6>
                        <small class="text-muted">Cantidad: ${item.quantity}</small>
                    </div>
                </div>
                <span class="fw-bold">€${totalPrice}</span>
            </div>
        `;
    });
    summaryHTML += "</div>";

    orderSummary.innerHTML = summaryHTML;

    // Actualizar total
    const total = cart.reduce(
        (sum, item) => sum + parseFloat(item.price) * parseInt(item.quantity),
        0
    );
    modalOrderTotal.textContent = `€${total.toFixed(2)}`;

    // Mostrar modal
    const confirmModal = new bootstrap.Modal(
        document.getElementById("confirmOrderModal")
    );
    confirmModal.show();
}

function confirmOrder() {
    if (cart.length === 0) return;

    // Mostrar loading
    const confirmBtn = document.querySelector(
        "#confirmOrderModal .btn-primary"
    );
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML =
        '<i class="bi bi-hourglass-split me-2"></i>Creando orden...';
    confirmBtn.disabled = true;

    // Preparar datos de la orden
    const total = cart.reduce(
        (sum, item) => sum + parseFloat(item.price) * parseInt(item.quantity),
        0
    );
    const orderData = {
        mesa_id: currentMesaId,
        numero_mesa: currentMesa,
        tipo: "local", // Por defecto local para pedidos desde mesa
        notas: document.getElementById("order-notes").value || null,
        subtotal: total,
        descuento: 0,
        impuesto: 0,
        total: total,
        detalles: cart.map((item) => ({
            plato_id: item.dishId,
            cantidad: parseInt(item.quantity),
            precio_unitario: parseFloat(item.price),
            notas_especiales: item.notes || null,
        })),
    };

    // Enviar orden al backend
    fetch("/api/menu/ordenes", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
        body: JSON.stringify(orderData),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Limpiar carrito
                cart = [];
                saveCartToStorage();
                updateCartDisplay();

                // Cerrar modal
                const confirmModal = bootstrap.Modal.getInstance(
                    document.getElementById("confirmOrderModal")
                );
                confirmModal.hide();

                // Mostrar toast de éxito
                showToast("¡Orden creada exitosamente!", "success");

                // Redirigir a órdenes después de un breve delay
                setTimeout(() => {
                    window.location.href = "/menu/orders";
                }, 2000);
            } else {
                throw new Error(data.message || "Error al crear la orden");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showToast("Error al crear la orden: " + error.message, "error");

            // Restaurar botón
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        });
}

function saveCartToStorage() {
    localStorage.setItem("restaurantCart", JSON.stringify(cart));
}

// ========================================================================
// NOTIFICACIONES
// ========================================================================

function showToast(message, type = "success") {
    const toastEl = document.getElementById("toast");
    const toastMessage = document.getElementById("toast-message");

    toastMessage.textContent = message;
    toastEl.style.display = "block";

    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
}

function hideToast() {
    const toastEl = document.getElementById("toast");
    const toast = bootstrap.Toast.getInstance(toastEl);
    if (toast) toast.hide();
}
