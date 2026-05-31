@extends('layouts.app')

@section('title', 'Catálogo de Productos - TCocina')

@php
    // Cargar configuraciones una sola vez para reutilizar en los modales
    $medallonesConfigs = \App\Models\ProductConfiguration::where('name', 'Medallones')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $tipoConfigs = \App\Models\ProductConfiguration::where('name', 'Tipo de Medallón')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $dipConfigs = \App\Models\ProductConfiguration::where('name', 'Dip')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $aderezosConfigs = \App\Models\ProductConfiguration::where('name', 'Aderezos')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $extrasConfigs = \App\Models\ProductConfiguration::where('name', 'Extras')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $dipConfigs = \App\Models\ProductConfiguration::where('name', 'Dip')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $dipExtraConfigs = \App\Models\ProductConfiguration::where('name', 'Dip Extra')
        ->where('is_available', true)
        ->orderBy('sort_order')
        ->orderBy('value')
        ->get();

    $aderezoDeCartaDescripcion = (string) \App\Models\BusinessSetting::get('aderezo_de_carta_descripcion', '');
@endphp

@section('content')
    <!-- Main Content -->
    <main class="container py-4">
        <!-- Page Header -->
        <div class="mb-4 text-center">
            <h1 class="display-6 fw-bold text-beach-dark mb-2 ultramono-title">Menú</h1>
        </div>

        

        <!-- Category Filter -->
        <div class="mb-4">

            <!-- Mobile: App bar inferior -->
            <div class="mobile-filter-appbar d-md-none p-2" id="filterContainerMobile">
                @php
                    $hamburguesasCount = $products->where('category_id', 1)->count();
                    $acompanamientosCount = $products->where('category_id', 2)->count();
                @endphp

                <!-- Botón Hamburguesas -->
                <button type="button" class="mobile-filter-btn category-filter {{ 'hamburguesas' == 'hamburguesas' ? 'active' : '' }}"
                    data-category="hamburguesas">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/burger.png') }}" alt="Hamburguesa" class="filter-burger-img">
                    </div>
                    <span class="filter-label">HAMBURGUESAS</span>

                </button>

                <!-- Botón Acompañamientos -->
                <button type="button" class="mobile-filter-btn category-filter {{ 'acompanamientos' == 'hamburguesas' ? 'active' : '' }}"
                    data-category="acompanamientos">
                    <div class="filter-icon">
                        <img src="{{ asset('productos/fondo/fries.png') }}" alt="Acompañamientos" class="filter-fries-img">
                    </div>
                    <span class="filter-label">ACOMPAÑAMIENTOS</span>

                </button>
            </div>
        </div>

        <!-- Toggle (Grid / Carrusel) - siempre encima del listado -->
        <div class="d-flex justify-content-center align-items-center mb-3">
            <div class="btn-group view-toggle" role="group" aria-label="Cambiar vista">
                <button id="gridBtn" type="button" class="btn btn-outline-secondary active" title="Vista en grid">
                    <i class="fas fa-border-all"></i>
                </button>
                <button id="carouselBtn" type="button" class="btn btn-outline-secondary" title="Vista en carrusel">
                    <i class="fas fa-images"></i>
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div id="productsGrid" class="row g-4">
            @foreach ($products as $product)
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card h-100 beach-card product-card"style="padding: 0px!important;"
                        data-category="{{ $product->category->slug ?? '' }}" data-product-id="{{ $product->id }}">
                        <span class="cash-discount-badge"><i class="fas fa-money-bill-wave"></i> 10% OFF efectivo</span>
                        <div class="position-relative overflow-hidden product-image-container">
                            @if ($product->image)
                                @php
                                    $imgUrl = asset('images/' . $product->image);
                                @endphp
                                <img class="card-img-top product-image"
                                    style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                    src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy"
                                    data-lightbox="{{ $imgUrl }}" data-lightbox-title="{{ $product->name }}"
                                    onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                            @else
                                <img class="card-img-top product-image"
                                    style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                    src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    alt="{{ $product->name }}" loading="lazy"
                                    data-lightbox="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    data-lightbox-title="{{ $product->name }}" />
                            @endif
                            <div class="lightbox-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title product-name mb-1 text-black"
                                style="font-size: 1.5rem; font-weight: 600;">{{ $product->name }}</h3>

                            @if ($product->category_id == 1)
                                <!-- Aclaración para hamburguesas -->
                                <div class="mb-2 text-center">
                                    <small class="text-success fw-medium">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Incluye porción de papas y dip de salsa
                                    </small>
                                </div>
                            @elseif ($product->category_id == 2)
                                <!-- Aclaración para acompañamientos -->
                                <div class="mb-2 text-center">
                                    <small class="text-success fw-medium">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Incluye dip de salsa
                                    </small>
                                </div>
                            @endif

                            <!-- Price between title and description -->
                            <div class="mb-2 text-center">
                                <span
                                    class="h5 fw-bold text-black product-price">${{ number_format($product->base_price, 2, ',', '.') }}</span>
                                <span
                                    class="text-decoration-line-through small text-beach-brown original-price d-none"></span>
                            </div>

                            <p class="card-text text-black small mb-3 product-description">
                                {{ $product->description }}
                            </p>

                            @if ($product->category_id == 1)
                                {{-- Solo mostrar accordion para categoría Hamburguesas --}}
                                <!-- Button for options modal -->
                                <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                    data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->base_price }}"
                                    data-product-image="{{ $product->image }}"
                                    data-product-description="{{ $product->description }}"
                                    data-product-default-sauce-id="{{ $product->default_sauce_configuration_id }}"
                                    data-product-default-sauce-value="{{ $product->defaultSauce ? $product->defaultSauce->value : '' }}">
                                    Personalizar
                                </button>
                            @elseif ($product->category_id == 2)
                                {{-- Para acompañamientos, solo mostrar opciones de dip --}}
                                <!-- Button for dip modal -->
                                <button class="btn btn-beach-primary btn-sm w-100 mb-3 dip-btn" type="button"
                                    data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->base_price }}"
                                    data-product-image="{{ $product->image }}">
                                    Elegir Dip
                                </button>
                            @else
                                {{-- Para productos que no son hamburguesas ni acompañamientos, solo mostrar el botón --}}
                                <!-- Add to Cart Button - Directo para productos sin opciones -->
                                <div class="d-grid">
                                    <button class="add-to-cart-btn btn btn-beach-primary cart-button"
                                        data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}"
                                        data-product-image="{{ $product->image }}">
                                        <span class="cart-button-text">
                                            <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                        </span>
                                        <span class="cart-button-cart">
                                            <i class="fas fa-shopping-cart"></i>
                                        </span>
                                        <span class="cart-button-tick">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Products Carousel (Swiper) -->
        <div id="productsCarousel" class="swiper menuSwiper d-none">
            <div class="swiper-wrapper">
                @foreach ($products as $product)
                    <div class="swiper-slide">
                        <div class="card h-100 beach-card product-card" style="padding: 0px!important; width: 100%; max-width: 320px; margin: 0 auto;"
                            data-category="{{ $product->category->slug ?? '' }}" data-product-id="{{ $product->id }}">
                            <span class="cash-discount-badge"><i class="fas fa-money-bill-wave"></i> 10% OFF efectivo</span>
                            <div class="position-relative overflow-hidden product-image-container">
                                @if ($product->image)
                                    @php $imgUrl = asset('images/' . $product->image); @endphp
                                    <img class="card-img-top product-image"
                                        style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                        src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy"
                                        data-lightbox="{{ $imgUrl }}" data-lightbox-title="{{ $product->name }}"
                                        onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;" />
                                @else
                                    <img class="card-img-top product-image"
                                        style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                        src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                        alt="{{ $product->name }}" loading="lazy"
                                        data-lightbox="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                        data-lightbox-title="{{ $product->name }}" />
                                @endif
                                <div class="lightbox-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h3 class="card-title product-name mb-1 text-black" style="font-size: 1.5rem; font-weight: 600;">{{ $product->name }}</h3>
                                @if ($product->category_id == 1)
                                    <div class="mb-2 text-center">
                                        <small class="text-success fw-medium"><i class="fas fa-check-circle me-1"></i>Incluye porción de papas y dip de salsa</small>
                                    </div>
                                @elseif ($product->category_id == 2)
                                    <div class="mb-2 text-center">
                                        <small class="text-success fw-medium"><i class="fas fa-check-circle me-1"></i>Incluye dip de salsa</small>
                                    </div>
                                @endif
                                <div class="mb-2 text-center">
                                    <span class="h5 fw-bold text-black product-price">${{ number_format($product->base_price, 2, ',', '.') }}</span>
                                    <span class="text-decoration-line-through small text-beach-brown original-price d-none"></span>
                                </div>
                                <p class="card-text text-black small mb-3 product-description">{{ $product->description }}</p>
                                @if ($product->category_id == 1)
                                    <button class="btn btn-beach-primary btn-sm w-100 mb-3 customize-btn" type="button"
                                        data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}">Personalizar</button>
                                @elseif ($product->category_id == 2)
                                    <button class="btn btn-beach-primary btn-sm w-100 mb-3 dip-btn" type="button"
                                        data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}">Elegir Dip</button>
                                @else
                                    <div class="d-grid">
                                        <button class="add-to-cart-btn btn btn-beach-primary cart-button"
                                            data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}"
                                            data-product-price="{{ $product->base_price }}" data-product-image="{{ $product->image }}">
                                            <span class="cart-button-text">
                                                <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                            </span>
                                            <span class="cart-button-cart">
                                                <i class="fas fa-shopping-cart"></i>
                                            </span>
                                            <span class="cart-button-tick">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="d-none text-center py-5">
            <div class="spinner-border text-beach-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-beach-brown">Cargando productos...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="d-none text-center py-5">
            <i class="fas fa-search display-1 text-beach-brown mb-4"></i>
            <h3 class="h5 fw-medium text-beach-dark mb-2">No se encontraron productos</h3>
            <p class="text-beach-brown">Intenta con otros términos de búsqueda o categorías</p>
        </div>

        <!-- Desktop: App bar fijo abajo -->
        <div class="desktop-filter-appbar d-none d-md-flex justify-content-center p-3" id="filterContainerDesktop">
            @php
                $hamburguesasCount = $products->where('category_id', 1)->count();
                $acompanamientosCount = $products->where('category_id', 2)->count();
            @endphp

            <!-- Botón Hamburguesas -->
            <button type="button" class="desktop-filter-btn category-filter {{ 'hamburguesas' == 'hamburguesas' ? 'active' : '' }}"
                data-category="hamburguesas">
                <div class="filter-icon">
                    <img src="{{ asset('productos/fondo/burger.png') }}" alt="Hamburguesa" class="filter-burger-img">
                </div>
                <span class="filter-label">HAMBURGUESAS</span>
            </button>

            <!-- Botón Acompañamientos -->
            <button type="button" class="desktop-filter-btn category-filter {{ 'acompanamientos' == 'hamburguesas' ? 'active' : '' }}"
                data-category="acompanamientos">
                <div class="filter-icon">
                    <img src="{{ asset('productos/fondo/fries.png') }}" alt="Acompañamientos" class="filter-fries-img">
                </div>
                <span class="filter-label">ACOMPAÑAMIENTOS</span>
            </button>
        </div>
    </main>



    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal">
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img id="lightboxImage" class="lightbox-img" src="" alt="">
            <div id="lightboxTitle" class="lightbox-title"></div>
        </div>
    </div>

    <!-- Global Modal for Hamburgers -->
    <div class="modal fade" id="hamburgerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hamburgerModalTitle">Personalizar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del producto -->
                    <div class="text-center mb-4">
                        <img id="hamburgerModalImage" src="" alt="" class="img-fluid rounded mb-3" style="max-height: 200px; object-fit: cover; width: 100%;">
                        <h5 class="fw-bold mb-2" id="hamburgerModalProductName"></h5>
                        <p class="text-muted small mb-3" id="hamburgerModalDescription"></p>
                        <!-- Precio dinámico -->
                        <div class="h2 text-beach-primary fw-bold" id="hamburgerModalPrice">$0</div>
                        <span class="cash-discount-badge mt-2" style="position:static; display:inline-flex;">
                            <i class="fas fa-money-bill-wave"></i> 10% OFF efectivo
                        </span>
                        <small class="text-muted d-block mt-1">Precio actualizado</small>
                    </div>

                    <div class="product-options mb-3">
                        <div class="row g-2">
                            <!-- Select 1: Medallones -->
                            <div class="col-6 mb-2">
                                <label class="form-label fw-medium small">1. Medallones:</label>
                                <select class="form-select form-select-sm variant-select" data-variant-name="Medallones"
                                    data-config-type="medallones">
                                    @foreach ($medallonesConfigs as $index => $config)
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            {{ $config->value === 'Doble' ? 'selected' : ($index === 0 ? 'selected' : '') }}>
                                            {{ $config->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select 2: Tipo de Medallón -->
                            <div class="col-6 mb-2">
                                <label class="form-label fw-medium small">2. Tipo de Medallón:</label>
                                <select class="form-select form-select-sm variant-select" data-variant-name="Tipo de Medallón"
                                    data-config-type="tipo_medallon">
                                    @foreach ($tipoConfigs as $index => $config)
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            {{ $config->value === 'Carne' ? 'selected' : ($index === 0 ? 'selected' : '') }}>
                                            {{ $config->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select 3: Dip -->
                            <div class="col-6 mb-2">
                                <label class="form-label fw-medium small d-inline-flex align-items-center gap-1">
                                    <span>3. Dip:</span>
                                    <span class="field-help-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Salsa en pote aparte.">
                                        ?
                                    </span>
                                </label>
                                <select class="form-select form-select-sm option-select" data-option-name="Dip"
                                    data-config-type="dips">
                                    @foreach ($dipConfigs as $index => $config)
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            {{ $index === 0 ? 'selected' : '' }}>
                                            {{ $config->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select 4: Aderezos -->
                            <div class="col-6 mb-2">
                                <label class="form-label fw-medium small d-inline-flex align-items-center gap-1">
                                    <span>4. Aderezos:</span>
                                    <span class="field-help-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Salsa que va dentro de la hamburguesa, en el pan.">
                                        ?
                                    </span>
                                </label>
                                <select class="form-select form-select-sm option-select" data-option-name="Aderezos"
                                    data-config-type="aderezos" id="aderezosSelect">
                                    @foreach ($aderezosConfigs as $index => $config)
                                        @php
                                            $aderezoLabel = $config->display_value;
                                            if (strcasecmp($config->value, 'De carta') === 0 && $aderezoDeCartaDescripcion !== '') {
                                                $aderezoLabel = 'De carta (' . e($aderezoDeCartaDescripcion) . ')' . $config->formatted_price_modifier;
                                            }
                                        @endphp
                                        <option value="{{ $config->value }}"
                                            data-price-modifier="{{ $config->price_modifier }}"
                                            data-config-id="{{ $config->id }}"
                                            {{ $index === 0 ? 'selected' : '' }}>
                                            {{ $aderezoLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select 5: Extras -->
                            <div class="col-6 mb-2">
                                <label class="form-label fw-medium small">5. Extras:</label>
                                <div id="extras-container">
                                    <div class="extras-row d-flex align-items-center">
                                        <select class="form-select form-select-sm option-select extras-select" 
                                                data-option-name="Extras" data-config-type="extras" style="flex: 1;">
                                            <option value="" data-price-modifier="0" selected>Sin extras</option>
                                            @foreach ($extrasConfigs as $config)
                                                <option value="{{ $config->value }}"
                                                    data-price-modifier="{{ $config->price_modifier }}">
                                                    {{ $config->display_value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-success btn-sm ms-1 add-extra-btn btn-icon-centered" 
                                                style="width: 35px; height: 31px; padding: 0; font-size: 0.75rem;" title="Agregar otro extra">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Select 6: Dip Extra -->
                            <div class="col-6 mb-2">
                                <label class="form-label fw-medium small">6. Dip Extra:</label>
                                <div id="dip-extra-container">
                                    <div class="dip-extra-row d-flex align-items-center">
                                        <select class="form-select form-select-sm option-select dip-extra-select" 
                                                data-option-name="Dip Extra" data-config-type="dip_extra" style="flex: 1;">
                                            <option value="" data-price-modifier="0" selected>Sin dip extra</option>
                                            @foreach ($dipExtraConfigs as $config)
                                                <option value="{{ $config->value }}"
                                                    data-price-modifier="{{ $config->price_modifier }}">
                                                    {{ $config->display_value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-success btn-sm ms-1 add-dip-extra-btn btn-icon-centered" 
                                                style="width: 35px; height: 31px; padding: 0; font-size: 0.75rem;" title="Agregar otro dip extra">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-beach-primary cart-button" id="hamburgerAddToCart">
                            <span class="cart-button-text">
                                <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                            </span>
                            <span class="cart-button-cart">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <span class="cart-button-tick">
                                <i class="fas fa-check"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Modal for Accompaniments -->
    <div class="modal fade" id="dipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dipModalTitle">Elegir Dip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="product-options mb-3">
                        <div class="mb-2">
                            <label class="form-label small text-black fw-medium d-inline-flex align-items-center gap-1">
                                <span>Dip:</span>
                                <span class="field-help-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Salsa en pote aparte.">
                                    ?
                                </span>
                            </label>
                            <select class="form-select form-select-sm" id="dipSelect" data-option-name="Dip">
                                @foreach ($dipConfigs as $index => $config)
                                    <option value="{{ $config->value }}"
                                        data-price-modifier="{{ $config->price_modifier }}"
                                        {{ $index === 0 ? 'selected' : '' }}>
                                        {{ $config->display_value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-beach-primary cart-button" id="dipAddToCart">
                            <span class="cart-button-text">
                                <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                            </span>
                            <span class="cart-button-cart">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <span class="cart-button-tick">
                                <i class="fas fa-check"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .product-card { position: relative; }
        .cash-discount-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(34,197,94,0.4);
        }

        /* Animación de botón de carrito - Basado en código proporcionado pero manteniendo colores actuales */
        button.btn.cart-button {
            --background: var(--beach-primary, #0c6568);
            --text: #fff;
            --cart: #fff;
            --tick: #fff;
            --scale: 1;
            position: relative !important;
            overflow: hidden !important;
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
            transform: scale(var(--scale)) !important;
            transition: transform 0.4s cubic-bezier(0.36, 1.01, 0.32, 1.27) !important;
        }
        
        /* Mantener el color de fondo durante la animación - IMPORTANTE: mantener azul */
        button.btn.cart-button.adding,
        button.btn.cart-button.added {
            background-color: var(--beach-primary, #0c6568) !important;
            border-color: var(--beach-primary, #0c6568) !important;
        }
        
        /* Estado active - blanco con borde azul y texto azul (igual que Personalizar) */
        button.btn.cart-button:active:not(.adding):not(.added),
        button.btn.cart-button:focus:active:not(.adding):not(.added) {
            background-color: #fff !important;
            border-color: var(--beach-primary, #0c6568) !important;
            color: var(--beach-primary, #0c6568) !important;
        }
        
        /* Durante active, los íconos y texto deben ser azules (igual que Personalizar) */
        button.btn.cart-button:active:not(.adding):not(.added) .cart-button-text,
        button.btn.cart-button:active:not(.adding):not(.added) .cart-button-cart i,
        button.btn.cart-button:active:not(.adding):not(.added) .cart-button-tick i,
        button.btn.cart-button:focus:active:not(.adding):not(.added) .cart-button-text,
        button.btn.cart-button:focus:active:not(.adding):not(.added) .cart-button-cart i,
        button.btn.cart-button:focus:active:not(.adding):not(.added) .cart-button-tick i {
            color: var(--beach-primary, #0c6568) !important;
        }
        
        /* Durante adding/added, todo debe ser blanco */
        button.btn.cart-button.adding .cart-button-cart i,
        button.btn.cart-button.added .cart-button-tick i {
            color: #fff !important;
        }

        button.btn.cart-button .cart-button-text {
            display: inline-block !important;
            position: relative !important;
            opacity: 1 !important;
            transition: opacity 0.3s ease !important;
            z-index: 1;
        }

        button.btn.cart-button .cart-button-cart,
        button.btn.cart-button .cart-button-tick {
            position: absolute !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            opacity: 0 !important;
            transition: opacity 0.3s ease !important;
            z-index: 10;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        button.btn.cart-button .cart-button-cart i,
        button.btn.cart-button .cart-button-tick i {
            font-size: 1.5em !important;
            color: #fff !important;
            margin: 0 !important;
        }

        /* Cuando se está agregando - mostrar carrito */
        button.btn.cart-button.adding .cart-button-text {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        button.btn.cart-button.adding .cart-button-cart {
            opacity: 1 !important;
            visibility: visible !important;
            animation: cartAnimation 0.6s ease-in-out !important;
        }

        /* Cuando se agregó - mostrar tick */
        button.btn.cart-button.added .cart-button-text {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        button.btn.cart-button.added .cart-button-cart {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        button.btn.cart-button.added .cart-button-tick {
            opacity: 1 !important;
            visibility: visible !important;
            animation: tickAnimation 0.6s ease-in-out !important;
        }

        @keyframes cartAnimation {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) rotate(-12deg);
            }
            50% {
                opacity: 1;
                transform: translate(-50%, -50%) rotate(5deg);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) rotate(0deg);
            }
        }

        @keyframes tickAnimation {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        button.btn.cart-button.adding {
            --scale: 0.95;
        }

        button.btn.cart-button.added {
            --scale: 1;
        }

        /* Evitar scroll horizontal en móviles */
        html, body { overflow-x: hidden; }
        /* Swiper basic styles */
        .swiper{padding: 24px 0 36px 0; overflow: hidden}
        .swiper-wrapper{align-items: stretch}
        .swiper-slide{display:flex;justify-content:center;transition:transform .35s ease, opacity .35s ease; width: auto; max-width: 88vw}
        @media (min-width: 576px){
            .swiper-slide{width: 340px; max-width: 340px}
        }
        .swiper .product-card{max-width:88vw; transition:transform .35s ease, box-shadow .35s ease; will-change: transform}
        @media (min-width: 576px){ .swiper .product-card{max-width:340px} }
        /* Más redondeo de las cards */
        .product-card,
        .product-card .card,
        .product-card .card-img-top,
        .product-card .product-image,
        .product-card .product-image-container{
            border-radius: 16px !important;
            overflow: hidden;
        }
        /* Bordes superiores también redondos en imagen */
        .product-image-container img{
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
        }
        /* Center mode scale - SAFE defaults */
        #productsCarousel .swiper-slide{opacity:.9}
        #productsCarousel .swiper-slide .product-card{box-shadow:0 10px 24px rgba(0,0,0,.12)}
        #productsCarousel .swiper-slide-active{transform:none;opacity:1}
        #productsCarousel .swiper-slide-active .product-card{transform:none; box-shadow:0 16px 36px rgba(0,0,0,.18)}
        /* Card pop for active */
        #productsCarousel .swiper-slide-active .product-card{transform:translateY(-6px); box-shadow:0 18px 40px rgba(0,0,0,.18)}
        /* Eliminar fondo verde al arrastrar slides y al hacer click - Máxima especificidad */
        #productsCarousel .swiper-slide,
        #productsCarousel .swiper-slide:active,
        #productsCarousel .swiper-slide:focus,
        #productsCarousel .swiper-slide-dragging {
            background: transparent !important;
            background-color: transparent !important;
        }
        #productsCarousel .swiper-slide .product-card,
        #productsCarousel .swiper-slide .beach-card,
        #productsCarousel .swiper-slide .card,
        #productsCarousel .swiper-slide .product-card:active,
        #productsCarousel .swiper-slide .product-card:focus,
        #productsCarousel .swiper-slide .product-card:hover,
        #productsCarousel .swiper-slide .beach-card:active,
        #productsCarousel .swiper-slide .beach-card:focus,
        #productsCarousel .swiper-slide .beach-card:hover,
        #productsCarousel .swiper-slide .card:active,
        #productsCarousel .swiper-slide .card:focus,
        #productsCarousel .swiper-slide .card:hover,
        #productsCarousel .swiper-slide-dragging .product-card,
        #productsCarousel .swiper-slide-dragging .beach-card,
        #productsCarousel .swiper-slide-dragging .card,
        #productsCarousel .swiper-slide-dragging .product-card:focus,
        #productsCarousel .swiper-slide-dragging .product-card:active,
        #productsCarousel .swiper-slide-dragging .product-card:hover,
        #productsCarousel .swiper-slide-dragging .beach-card:focus,
        #productsCarousel .swiper-slide-dragging .beach-card:active,
        #productsCarousel .swiper-slide-dragging .beach-card:hover {
            background-color: white !important;
            background: white !important;
        }
        /* Bullets del carrusel: usar color secundario de marca (mapeado desde settings) */
        .swiper-pagination-bullets .swiper-pagination-bullet{
            background: var(--beach-secondary);
            opacity:.5
        }
        .swiper-pagination-bullets .swiper-pagination-bullet-active{
            background: var(--beach-secondary);
            opacity:1
        }
        /* Navegación del carrusel (flechas) acorde a paleta: fondo secundario, flecha primaria */
        .swiper-button-next, .swiper-button-prev{
            width: 44px; height: 44px;
            border-radius: 9999px;
            background: var(--beach-secondary) !important;
            box-shadow: 0 6px 16px rgba(0,0,0,.18);
            transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .swiper-button-next:after, .swiper-button-prev:after{
            font-size: 18px;
            color: var(--beach-primary) !important;
            font-weight: 900;
        }
        .swiper-button-next:hover, .swiper-button-prev:hover{
            background: var(--beach-primary) !important;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(0,0,0,.22);
        }
        .swiper-button-next:hover:after, .swiper-button-prev:hover:after{
            color: var(--beach-secondary) !important;
        }
        /* Accesibilidad: foco visible */
        .swiper-button-next:focus, .swiper-button-prev:focus{
            outline: 2px solid var(--beach-primary);
            outline-offset: 2px;
        }
        .view-toggle .btn{border-color:#cfd8dc;color:#263238}
        .view-toggle .btn.active{background:var(--beach-primary, #00b4d8);color:#fff;border-color:var(--beach-primary, #00b4d8)}
        .beach-card {
            border: 1px solid var(--beach-border-light);
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Override hover effect from custom-beach-theme.css */
        .beach-card:hover {
            box-shadow: none !important;
            transform: none !important;
            border-color: var(--beach-border-light) !important;
        }

        /* Eliminar cambio de color al hacer clic en card-body */
        .product-card .card-body,
        .product-card .card-body:active,
        .product-card .card-body:focus,
        .product-card .card-body:hover,
        .product-card:active .card-body,
        .product-card:focus .card-body,
        .product-card:focus-within .card-body {
            background-color: transparent !important;
            background: transparent !important;
            color: inherit !important;
            outline: none !important;
            box-shadow: none !important;
        }
        
        /* Prevenir que el card completo cambie de color al hacer clic */
        .product-card:active,
        .product-card:focus {
            background-color: white !important;
            background: white !important;
        }

        /* Eliminar hover de botones beach-primary */

        .beach-scrollbar-hide {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .beach-scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .category-filter {
            transition: all 0.3s ease;
            border-radius: 25px;
            padding: 8px 16px;
            font-weight: 500;
            border: 2px solid transparent;
            flex-shrink: 0;
            white-space: nowrap;
            min-width: fit-content;
        }

        .category-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .category-filter.active {
            background-color: var(--beach-primary) !important;
            border-color: var(--beach-primary) !important;
            color: var(--beach-dark) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Ensure only one filter is active at a time */
        .category-filter:not(.active) {
            background-color: transparent !important;
            border-color: var(--beach-primary) !important;
            color: var(--beach-primary) !important;
        }

        .category-filter i {
            font-size: 0.9rem;
        }

        /* Scroll indicators */
        .scroll-indicator {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--beach-primary);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .scroll-indicator-left {
            left: 8px;
        }

        .scroll-indicator-right {
            right: 8px;
        }

        .scroll-indicator:hover {
            background: var(--beach-primary);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .scroll-indicator i {
            font-size: 0.8rem;
            color: var(--beach-primary);
        }

        .scroll-indicator:hover i {
            color: white;
        }

        /* Desktop App Bar - Fixed at bottom - Always fixed */
        .desktop-filter-appbar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0;
            right: 0;
            background: #ffffffed;
            border-top: 1px solid #e0e0e0;
            border-radius: 0;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            gap: 32px;
        }

        /* Add padding to main content to prevent content from being hidden behind fixed progress bar */
        main {
            padding-bottom: 120px;
        }
        
        /* Asegurar que el footer quede visible sobre la barra fija */
        footer.footer-themed {
            margin-bottom: 0 !important;
            position: relative;
            z-index: 999;
        }

        @media (max-width: 767px) {
            main {
                padding-bottom: 100px;
            }

            footer.footer-themed {
                margin-bottom: 0 !important;
            }
        }

        .desktop-filter-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 16px 24px;
            border-radius: 16px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 80px;
            min-width: 160px;
        }

        .desktop-filter-btn.active {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgba(12, 101, 104, 0.7);
            color: white !important;
        }

        .desktop-filter-btn.active .filter-label {
            color: white !important;
        }

        .desktop-filter-btn:not(.active) {
            color: #666;
        }

        .desktop-filter-btn .filter-icon {
            font-size: 32px;
            margin-bottom: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .desktop-filter-btn .filter-label {
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            line-height: 1.2;
        }

        .desktop-filter-btn.active .filter-burger-img {
            transform: scale(3.5) translateY(-25%);
        }

        .desktop-filter-btn.active .filter-fries-img {
            transform: scale(3.5) translateY(-25%);
        }

        /* Mobile App Bar Inferior - Always fixed */
        .mobile-filter-appbar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0;
            right: 0;
            background: #ffffffed;
            border-top: 1px solid #e0e0e0;
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .mobile-filter-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 8px 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 60px;
        }

        .mobile-filter-btn.active {
            background: url("/productos/fondo/fondo.png") center center / cover no-repeat,
                rgba(12, 101, 104, 0.7);
            color: white !important;
        }

        .mobile-filter-btn.active .filter-label {
            color: white !important;
        }

        .mobile-filter-btn:not(.active) {
            color: #666;
        }

        .mobile-filter-btn .filter-icon {
            font-size: 24px;
            margin-bottom: 4px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-burger-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .filter-fries-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .mobile-filter-btn.active .filter-burger-img {
            transform: scale(3.5) translateY(-25%);
        }

        .mobile-filter-btn.active .filter-fries-img {
            transform: scale(3.5) translateY(-25%);
        }

        .mobile-filter-btn .filter-label {
            font-size: 11px;
            font-weight: 500;
            text-align: center;
            line-height: 1.2;
        }

        .mobile-filter-btn .filter-badge {
            position: absolute;
            top: 4px;
            right: 8px;
            background: #ff4757;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-filter-btn.active .filter-badge {
            background: rgba(255, 255, 255, 0.9);
            color: var(--beach-primary);
        }


        /* Mobile optimizations */
        @media (max-width: 768px) {

            /* Añadir espacio inferior para mobile app bar */
            .container {
                padding-bottom: 100px;
            }

            .category-filter {
                padding: 6px 12px;
                font-size: 0.9rem;
                min-width: auto;
            }

            .category-filter .badge {
                font-size: 0.75rem;
                padding: 2px 6px;
            }

            .category-filter i {
                font-size: 0.8rem;
            }

            /* Ensure proper scrolling on mobile */
            .d-flex.gap-2.overflow-x-auto {
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .scroll-indicator {
                width: 28px;
                height: 28px;
            }

            .scroll-indicator i {
                font-size: 0.7rem;
            }
        }

        .field-help-icon {
            display: inline-flex;
            width: 16px;
            height: 16px;
            border-radius: 999px;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            line-height: 1;
            font-weight: 700;
            cursor: help;
            color: #0c6568;
            border: 1px solid rgba(12, 101, 104, 0.35);
            background: rgba(12, 101, 104, 0.08);
        }

        /* Accordion button styling to match cart buttons */
        .accordion-button.btn-outline-beach-primary {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            font-weight: 600;
            min-height: 44px;
            color: black !important;
            background-color: var(--beach-primary) !important;
            border-color: var(--beach-primary) !important;
        }

        .accordion-button.btn-outline-beach-primary:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000000'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        /* Ensure all text in menu is black */
        .product-card .card-title,
        .product-card .card-text,
        .product-card .product-description,
        .product-card .product-name,
        .product-card .product-price,
        .product-card .original-price,
        .product-card .form-label {
            color: black !important;
        }

        /* Botones con texto blanco por defecto */
        .product-card .btn-beach-primary { color: white !important; background-color: var(--beach-primary, #0ea5a2); border: 2px solid var(--beach-primary, #0ea5a2); }

        /* Ensure buttons maintain consistent padding */
        .btn-beach-primary,
        .btn-outline-beach-primary {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            font-weight: 600 !important;
            min-height: 44px !important;
        }

        /* Botones de productos con texto blanco */
        .product-card .btn-beach-primary {
            color: white !important;
        }

        .btn-outline-beach-primary:hover {
            color: black !important;
        }

        /* Hover: fondo blanco, borde del color primario, texto verde */
        .product-card .btn-beach-primary:hover,
        .product-card .btn-beach-primary:active,
        .product-card .btn-beach-primary:focus {
            background-color: #fff !important;
            color: var(--beach-primary, #0ea5a2) !important;
            border-color: var(--beach-primary, #0ea5a2) !important;
        }

        /* Hide original selects in product cards since we use global modals */
        .product-card .variant-select,
        .product-card .option-select {
            visibility: hidden;
            position: absolute;
            left: -9999px;
        }

        .product-card[data-category] {
            transition: opacity 0.3s ease;
        }

        .product-card.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* Hide entire columns when filtering */
        .col-sm-6.hidden,
        .col-lg-4.hidden,
        .col-xl-3.hidden {
            display: none !important;
        }

        /* Product Image Styles */
        .product-image-container {
            position: relative;
            overflow: hidden;
        }

        .product-image {
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .lightbox-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .product-image-container:hover .lightbox-overlay {
            opacity: 1;
        }

        .lightbox-overlay i {
            color: white;
            font-size: 2rem;
        }

        /* Modal z-index fix */
        .modal {
            z-index: 9999 !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
        }

        .modal-dialog {
            z-index: 10000 !important;
        }

        /* Tooltips por encima del modal (Bootstrap los pone en body con z-index menor) */
        .tooltip {
            z-index: 10010 !important;
        }

        /* Centrado del ícono en botones cuadrados (agregar extra / dip extra) */
        .btn-icon-centered {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Modales 100% responsivos en móvil */
        @media (max-width: 576px) {
            .modal-dialog {
                max-width: 94vw !important;
                width: 94vw !important;
                margin: 0 auto !important;
            }
            .modal-content { border-radius: 12px; width: 100% !important; }
            .modal { overflow-x: hidden !important; }
            body.modal-open { overflow-x: hidden !important; }
            .modal .card-img-top, .modal img { max-width: 100% !important; height: auto !important; }
        }

        /* Lightbox Modal Styles */
        .lightbox-modal {
            display: none;
            position: fixed;
            z-index: 9997;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .lightbox-modal.show {
            display: flex !important;
            align-items: center;
            justify-content: center;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            text-align: center;
            padding: 20px;
        }

        .lightbox-img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .lightbox-title {
            color: white;
            font-size: 1.5rem;
            margin-top: 1rem;
            font-weight: 600;
        }

        .lightbox-close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            cursor: pointer;
            z-index: 9996;
            transition: color 0.3s ease;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .lightbox-close:hover {
            color: #ff6b6b;
            background: rgba(0, 0, 0, 0.8);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .lightbox-title {
                font-size: 1.2rem;
            }

            .lightbox-close {
                font-size: 2rem;
                width: 40px;
                height: 40px;
                top: 5px;
                right: 10px;
            }

            .lightbox-content {
                padding: 10px;
            }
        }
    </style>
    <link id="swiperCss" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('context/main.css') }}" />
@endpush

@push('scripts')
    <script>
        // Esperar a que app.js esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño delay para asegurar que app.js esté completamente cargado
            setTimeout(function() {
                initializeCatalog();
                initializeScrollIndicators();
                // setupProgressBarPosition(); // Deshabilitado - appbars siempre fijos
                // Inicializar lightbox después de un delay adicional
                setTimeout(function() {
                    initializeLightbox();
                }, 500);
                initializeViewSwitcher();
                setupDataCacheForFiltering();
                // Render default category on load using data cache
                renderByCategory('hamburguesas');
                initializeFieldHelpTooltips();
                bindTooltipsOnModalShow();
            }, 100);
        });

        function initializeFieldHelpTooltips() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach((el) => {
                bootstrap.Tooltip.getOrCreateInstance(el, {
                    trigger: 'hover focus click'
                });
            });
        }

        /** Inicializa tooltips solo dentro de un contenedor (ej. modal). Útil cuando el contenido estaba oculto al cargar. */
        function initializeFieldHelpTooltipsInContainer(container) {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip || !container) return;
            const triggers = container.querySelectorAll('[data-bs-toggle="tooltip"]');
            triggers.forEach((el) => {
                const existing = bootstrap.Tooltip.getInstance(el);
                if (existing) existing.dispose();
                new bootstrap.Tooltip(el, { trigger: 'hover focus click' });
            });
        }

        /** Enlaza inicialización de tooltips al abrir los modales (hamburger y dip). */
        function bindTooltipsOnModalShow() {
            ['hamburgerModal', 'dipModal'].forEach(function (modalId) {
                const modalEl = document.getElementById(modalId);
                if (!modalEl) return;
                modalEl.addEventListener('shown.bs.modal', function () {
                    initializeFieldHelpTooltipsInContainer(modalEl);
                });
            });
        }

        // Función deshabilitada - Los appbars ahora siempre están fijos en bottom: 0
        // function setupProgressBarPosition() {
        //     // Esta función ha sido deshabilitada para mantener los appbars siempre fijos en la parte inferior
        // }

        function initializeCatalog() {
            // Initialize category filters
            initializeCategoryFilters();

            // Initialize product interactions
            initializeProductInteractions();

            // Initialize cart functionality
            initializeCartOffcanvas();

            // Initialize search
            initializeSearch();

            // Lightbox se inicializa por separado con delay

            // Actualizar el carrito lateral al cargar la página
            if (typeof updateCartOffcanvas === 'function') {
                updateCartOffcanvas();
            }
        }

        // --- Data cache for rebuilding views ---
        let allProductsData = null; // cached at first use
        let currentCategory = 'hamburguesas';

        function setupDataCacheForFiltering(){
            if (allProductsData) return;
            const cols = document.querySelectorAll('#productsGrid > div');
            allProductsData = Array.from(cols).map(col => {
                const card = col.querySelector('.product-card');
                const category = card ? (card.getAttribute('data-category') || '') : '';
                return {
                    category,
                    columnHtml: col.outerHTML,
                    cardHtml: card ? card.outerHTML : col.innerHTML
                };
            });
        }

        function isCarouselActive(){
            const carouselEl = document.getElementById('productsCarousel');
            return carouselEl && !carouselEl.classList.contains('d-none');
        }

        function renderGridFromData(category){
            const container = document.getElementById('productsGrid');
            if (!container || !allProductsData) return;
            const filtered = allProductsData.filter(p => p.category === category);
            container.innerHTML = filtered.map(p => p.columnHtml).join('');
            // re-bind interactions on new nodes
            container.querySelectorAll('.customize-btn, .dip-btn').forEach(btn => {
                btn.removeAttribute('data-customize-init');
                btn.removeAttribute('data-dip-init');
            });
            initializeProductInteractions();
        }

        function renderCarouselFromData(category){
            if (!window.menuSwiper || !allProductsData) return;
            const filtered = allProductsData.filter(p => p.category === category && p.cardHtml && p.cardHtml.trim() !== '');
            const slides = filtered.map(p => `<div class="swiper-slide">${p.cardHtml}</div>`);
            try {
                window.menuSwiper.removeAllSlides();
            } catch(e) { /* ignore */ }
            if (slides.length > 0) {
                window.menuSwiper.addSlide(0, slides);
            }
            window.menuSwiper.update();
            window.menuSwiper.slideTo(0);
            // Rebind interactions for dynamically injected buttons in slides
            const wrapper = document.querySelector('#productsCarousel .swiper-wrapper');
            if (wrapper) {
                wrapper.querySelectorAll('.customize-btn, .dip-btn').forEach(btn => {
                    btn.removeAttribute('data-customize-init');
                    btn.removeAttribute('data-dip-init');
                });
            }
            if (typeof initializeProductInteractions === 'function') {
                initializeProductInteractions();
            }
        }

        function renderByCategory(category){
            currentCategory = category;
            if (isCarouselActive()) {
                renderCarouselFromData(category);
            } else {
                renderGridFromData(category);
            }
        }

        function initializeCategoryFilters() {
            const categoryFilters = document.querySelectorAll('.category-filter');
            const filterContainer = document.getElementById('filterContainer');
            const filterContainerMobile = document.getElementById('filterContainerMobile');
            const leftIndicator = document.querySelector('.scroll-indicator-left');
            const rightIndicator = document.querySelector('.scroll-indicator-right');

            console.log('Inicializando filtros de categoría...');
            console.log('Filtros encontrados:', categoryFilters.length);
            const totalProducts = allProductsData ? allProductsData.length : document.querySelectorAll('.product-card').length;
            console.log('Productos encontrados:', totalProducts);

            // Asegurar que el filtro "Hamburguesas" esté activo por defecto
            const hamburguesasFilters = document.querySelectorAll('[data-category="hamburguesas"]');
            if (hamburguesasFilters.length > 0) {
                console.log('Activando filtro "Hamburguesas" por defecto...');
                // Remover active de todos los filtros
                categoryFilters.forEach(f => {
                    f.classList.remove('active');
                    f.classList.remove('btn-beach-primary');
                    f.classList.add('btn-light');
                });

                // Remover active de todos los botones mobile y desktop
                document.querySelectorAll('.mobile-filter-btn, .desktop-filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Activar todos los filtros de "Hamburguesas" (desktop y mobile)
                hamburguesasFilters.forEach(filter => {
                    if (filter.classList.contains('mobile-filter-btn') || filter.classList.contains(
                            'desktop-filter-btn')) {
                        filter.classList.add('active');
                    } else {
                        filter.classList.add('active');
                        filter.classList.remove('btn-light');
                        filter.classList.add('btn-beach-primary');
                    }
                });
                // Render default
                renderByCategory('hamburguesas');
            }

            categoryFilters.forEach(filter => {
                filter.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const selectedCategory = this.dataset.category;

                    // Update active state - ensure only one is active per category
                    categoryFilters.forEach(f => {
                        if (f.dataset.category === selectedCategory) {
                            // Activar botón seleccionado
                            if (f.classList.contains('mobile-filter-btn') || f.classList.contains(
                                    'desktop-filter-btn')) {
                                f.classList.add('active');
                            } else {
                                f.classList.add('active');
                                f.classList.remove('btn-light');
                                f.classList.add('btn-beach-primary');
                            }
                        } else {
                            // Desactivar otros botones
                            if (f.classList.contains('mobile-filter-btn') || f.classList.contains(
                                    'desktop-filter-btn')) {
                                f.classList.remove('active');
                            } else {
                                f.classList.remove('active');
                                f.classList.remove('btn-beach-primary');
                                f.classList.add('btn-light');
                            }
                        }
                    });

                    // Render depending on current view
                    renderByCategory(selectedCategory);
                    hideNoProductsMessage();
                });
            });
        }

        // Scroll indicators functionality
        function initializeScrollIndicators() {
            const filterContainer = document.getElementById('filterContainer');
            const leftIndicator = document.querySelector('.scroll-indicator-left');
            const rightIndicator = document.querySelector('.scroll-indicator-right');

            if (filterContainer && leftIndicator && rightIndicator) {
                function updateScrollIndicators() {
                    const scrollLeft = filterContainer.scrollLeft;
                    const maxScroll = filterContainer.scrollWidth - filterContainer.clientWidth;

                    if (scrollLeft <= 5) {
                        leftIndicator.classList.add('d-none');
                    } else {
                        leftIndicator.classList.remove('d-none');
                    }

                    if (scrollLeft >= maxScroll - 5) {
                        rightIndicator.classList.add('d-none');
                    } else {
                        rightIndicator.classList.remove('d-none');
                    }
                }

                // Initial check
                updateScrollIndicators();

                // Update on scroll
                filterContainer.addEventListener('scroll', updateScrollIndicators);

                // Scroll buttons
                leftIndicator.addEventListener('click', () => {
                    filterContainer.scrollBy({
                        left: -200,
                        behavior: 'smooth'
                    });
                });

                rightIndicator.addEventListener('click', () => {
                    filterContainer.scrollBy({
                        left: 200,
                        behavior: 'smooth'
                    });
                });

                // Update on resize
                window.addEventListener('resize', updateScrollIndicators);
            }
        }

        function showNoProductsMessage(category) {
            let message = document.getElementById('noProductsMessage');
            if (!message) {
                message = document.createElement('div');
                message.id = 'noProductsMessage';
                message.className = 'text-center py-5';
                message.innerHTML = `
                    <i class="fas fa-search display-1 text-beach-brown mb-4"></i>
                    <h3 class="h5 fw-medium text-beach-dark mb-2">No hay productos en esta categoría</h3>
                    <p class="text-beach-brown">Intenta con otra categoría</p>
                `;
                document.getElementById('productsGrid').appendChild(message);
            }
        }

        function hideNoProductsMessage() {
            const message = document.getElementById('noProductsMessage');
            if (message) {
                message.remove();
            }
        }

        // Global modal variables
        let currentProductData = null;

        function initializeProductInteractions() {
            // Customize buttons for hamburgers (evitar doble binding)
            document.querySelectorAll('.customize-btn').forEach(button => {
                if (button.dataset.customizeInit === 'true') return;
                button.dataset.customizeInit = 'true';
                button.addEventListener('click', function() {
                    currentProductData = {
                        id: this.dataset.productId,
                        name: this.dataset.productName,
                        price: parseFloat(this.dataset.productPrice),
                        image: this.dataset.productImage,
                        description: this.dataset.productDescription || '',
                        defaultSauceId: this.dataset.productDefaultSauceId || null,
                        defaultSauceValue: this.dataset.productDefaultSauceValue || ''
                    };

                    // Update modal title
                    document.getElementById('hamburgerModalTitle').textContent = 'Personalizar';
                    
                    // Update product info in modal
                    const modalImage = document.getElementById('hamburgerModalImage');
                    const modalProductName = document.getElementById('hamburgerModalProductName');
                    const modalDescription = document.getElementById('hamburgerModalDescription');
                    const modalPrice = document.getElementById('hamburgerModalPrice');
                    
                    if (currentProductData.image) {
                        modalImage.src = '/images/' + currentProductData.image;
                        modalImage.alt = currentProductData.name;
                        modalImage.style.display = 'block';
                    } else {
                        modalImage.style.display = 'none';
                    }
                    
                    modalProductName.textContent = currentProductData.name;
                    modalDescription.textContent = currentProductData.description || '';
                    modalPrice.textContent = '$' + currentProductData.price.toFixed(2);
                    
                    // Show modal
                    const modalElement = document.getElementById('hamburgerModal');
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }
                    modal.show();

                    // Add price update listeners to all selects in the modal
                    initializeHamburgerModalPriceUpdates(currentProductData.price);
                    
                    // Filtrar aderezos según el producto (después de initializeHamburgerModalPriceUpdates)
                    // Usar setTimeout para asegurar que el DOM esté actualizado después del clonado de selects
                    setTimeout(() => {
                        filterAderezosForProduct(currentProductData.defaultSauceId, currentProductData.defaultSauceValue);
                    }, 100);
                });
            });

            // Dip buttons for accompaniments (evitar doble binding)
            document.querySelectorAll('.dip-btn').forEach(button => {
                if (button.dataset.dipInit === 'true') return;
                button.dataset.dipInit = 'true';
                button.addEventListener('click', function() {
                    currentProductData = {
                        id: this.dataset.productId,
                        name: this.dataset.productName,
                        price: parseFloat(this.dataset.productPrice),
                        image: this.dataset.productImage
                    };

                    // Update modal title
                    document.getElementById('dipModalTitle').textContent = currentProductData.name +
                        ' - Elegir Dip';

                    // Show modal
                    const modalElement = document.getElementById('dipModal');
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }
                    modal.show();
                });
            });

            // Global add to cart buttons (evitar doble binding)
            const hamburgerBtn = document.getElementById('hamburgerAddToCart');
            const dipBtn = document.getElementById('dipAddToCart');

            if (hamburgerBtn && !hamburgerBtn.dataset.modalInit) {
                hamburgerBtn.dataset.modalInit = 'true';
                hamburgerBtn.addEventListener('click', function() {
                    if (currentProductData) {
                        addProductToCartFromModal(currentProductData, 'hamburgerModal');
                    }
                });
            }

            if (dipBtn && !dipBtn.dataset.modalInit) {
                dipBtn.dataset.modalInit = 'true';
                dipBtn.addEventListener('click', function() {
                    if (currentProductData) {
                        // Activar animación del botón antes de agregar al carrito
                        if (dipBtn && dipBtn.classList.contains('cart-button')) {
                            triggerCartButtonAnimationOnElement(dipBtn);
                        }
                        addProductToCartFromModal(currentProductData, 'dipModal');
                    }
                });
            }

            // Add to cart buttons (evitar doble binding)
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                if (button.dataset.cartInit === 'true') return;
                button.dataset.cartInit = 'true';
                button.addEventListener('click', function(event) {
                    if (event && typeof event.stopImmediatePropagation === 'function') {
                        event.stopImmediatePropagation();
                    }
                    event.stopPropagation();
                    const productCard = this.closest('.product-card');
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    const productPrice = parseFloat(this.dataset.productPrice);
                    const productImage = this.dataset.productImage;

                    // Get selected variants and options
                    const selectedVariants = getSelectedVariants(productCard);
                    const selectedOptions = getSelectedOptions(productCard);

                    // Activar animación del botón antes de agregar al carrito
                    const clickedButton = this;
                    if (clickedButton && clickedButton.classList.contains('cart-button')) {
                        triggerCartButtonAnimationOnElement(clickedButton);
                    }
                    
                    addToCartWithVariants(productId, productName, productPrice, productImage,
                        selectedVariants,
                        selectedOptions);
                });
            });


            // Variant/option changes (evitar doble binding)
            document.querySelectorAll('.variant-select, .option-select').forEach(select => {
                if (select.dataset.priceInit === 'true') return;
                select.dataset.priceInit = 'true';
                select.addEventListener('change', function() {
                    updateProductPrice(this.closest('.product-card'));
                });
            });
        }

        function getSelectedVariants(productCard) {
            const variants = [];
            productCard.querySelectorAll('.variant-select').forEach(select => {
                const selectedOption = select.selectedOptions[0];
                if (selectedOption) {
                    variants.push({
                        name: select.dataset.variantName,
                        value: selectedOption.value,
                        priceModifier: parseFloat(selectedOption.dataset.priceModifier || 0)
                    });
                }
            });
            return variants;
        }

        function getSelectedOptions(productCard) {
            const options = [];
            productCard.querySelectorAll('.option-select').forEach(select => {
                if (select.value) {
                    const selectedOption = select.selectedOptions[0];
                    options.push({
                        name: select.dataset.optionName,
                        value: selectedOption.value,
                        priceModifier: parseFloat(selectedOption.dataset.priceModifier || 0)
                    });
                }
            });
            return options;
        }

        function updateProductPrice(productCard) {
            const basePrice = parseFloat(productCard.querySelector('.add-to-cart-btn').dataset.productPrice);
            const selectedVariants = getSelectedVariants(productCard);
            const selectedOptions = getSelectedOptions(productCard);

            let totalPrice = basePrice;
            selectedVariants.forEach(variant => totalPrice += variant.priceModifier);
            selectedOptions.forEach(option => totalPrice += option.priceModifier);

            const priceElement = productCard.querySelector('.product-price');
            if (priceElement) {
                priceElement.textContent = '$' + totalPrice.toFixed(2);
            }
        }

        // Usar la función global addToCart, pero con soporte para variants y options
        function addToCartWithVariants(productId, productName, productPrice, productImage, variants = [], options = []) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Create unique key for this product configuration
            const configKey = `${productId}_${JSON.stringify(variants)}_${JSON.stringify(options)}`;

            // Check if this exact configuration already exists
            const existingItem = cart.find(item => item.configKey === configKey);

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
                    configKey: configKey
                });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            updateCartOffcanvas();
            showNotification('Producto agregado al carrito', 'success');
        }


        function initializeCartOffcanvas() {
            // Ya no necesitamos duplicar esta función, usar la global de app.js
            // Solo configurar el botón específico de esta página
            const proceedToCart = document.getElementById('proceedToCart');
            if (proceedToCart) {
                proceedToCart.addEventListener('click', function() {
                    window.location.href = '/cart';
                });
            }
        }

        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    filterProducts(query);
                });
            }
        }

        function filterProducts(query) {
            const productCards = document.querySelectorAll('.product-card');

            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productDescription = card.querySelector('.product-description').textContent.toLowerCase();

                if (productName.includes(query) || productDescription.includes(query)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }


        // Format Price function
        function formatPrice(price) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0
            }).format(price);
        }

        // Lightbox functionality - Versión ultra simplificada
        function initializeLightbox() {
            console.log('🚀 Inicializando lightbox...');

            // Crear modal si no existe
            let modal = document.getElementById('lightboxModal');
            if (!modal) {
                console.log('Creando modal...');
                modal = document.createElement('div');
                modal.id = 'lightboxModal';
                modal.className = 'lightbox-modal';
                modal.innerHTML = `
                    <div class="lightbox-content">
                        <span class="lightbox-close">&times;</span>
                        <img id="lightboxImage" class="lightbox-img" src="" alt="">
                        <div id="lightboxTitle" class="lightbox-title"></div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxTitle = document.getElementById('lightboxTitle');
            const lightboxClose = document.querySelector('.lightbox-close');

            console.log('Modal:', !!modal);
            console.log('Imagen:', !!lightboxImage);
            console.log('Título:', !!lightboxTitle);
            console.log('Cerrar:', !!lightboxClose);

            // Función para abrir lightbox
            window.openLightbox = function(imageSrc, imageTitle) {
                console.log('🔥 ABRIENDO LIGHTBOX:', imageSrc);

                if (lightboxImage) {
                    lightboxImage.src = imageSrc;
                    lightboxImage.alt = imageTitle;
                }

                if (lightboxTitle) {
                    lightboxTitle.textContent = imageTitle;
                }

                if (modal) {
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                    console.log('✅ Modal mostrado');
                }
            };

            // Función para cerrar lightbox
            window.closeLightbox = function() {
                console.log('🔥 CERRANDO LIGHTBOX');
                if (modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            };

            // Agregar listeners usando delegación de eventos
            document.addEventListener('click', function(e) {
                console.log('Click detectado en:', e.target);
                console.log('Clases del elemento:', e.target.className);
                console.log('Elemento padre:', e.target.parentElement);

                // Si es una imagen de producto (incluyendo el overlay)
                if (e.target.classList.contains('product-image') ||
                    e.target.classList.contains('lightbox-overlay') ||
                    e.target.closest('.product-image-container')) {

                    e.preventDefault();
                    e.stopPropagation();

                    // Buscar la imagen real dentro del contenedor
                    let productImage = e.target;
                    if (!productImage.classList.contains('product-image')) {
                        productImage = e.target.closest('.product-image-container')?.querySelector(
                            '.product-image');
                    }

                    if (productImage) {
                        const imageSrc = productImage.src;
                        const imageTitle = productImage.alt || 'Imagen del producto';

                        console.log('🎯 Click en imagen de producto:', imageSrc);
                        window.openLightbox(imageSrc, imageTitle);
                    }
                }

                // Si es el botón cerrar
                if (e.target.classList.contains('lightbox-close')) {
                    console.log('🎯 Click en cerrar');
                    window.closeLightbox();
                }

                // Si es el fondo del modal
                if (e.target === modal) {
                    console.log('🎯 Click en fondo del modal');
                    window.closeLightbox();
                }
            });

            // Navegación con teclado
            document.addEventListener('keydown', function(e) {
                if (modal && modal.classList.contains('show')) {
                    if (e.key === 'Escape') {
                        console.log('🎯 Tecla Escape presionada');
                        window.closeLightbox();
                    }
                }
            });

            // Método alternativo: agregar listeners directos a las imágenes
            setTimeout(function() {
                const productImages = document.querySelectorAll('.product-image');
                console.log('🔍 Encontradas', productImages.length, 'imágenes para listeners directos');

                productImages.forEach((img, index) => {
                    console.log('Agregando listener directo a imagen', index);
                    img.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('🎯 Click directo en imagen:', this.src);
                        window.openLightbox(this.src, this.alt || 'Imagen del producto');
                    });

                    // También agregar listener al contenedor
                    const container = img.closest('.product-image-container');
                    if (container) {
                        container.addEventListener('click', function(e) {
                            if (e.target === this || e.target.classList.contains(
                                    'lightbox-overlay')) {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log('🎯 Click en contenedor de imagen:', img.src);
                                window.openLightbox(img.src, img.alt || 'Imagen del producto');
                            }
                        });
                    }
                });
            }, 1000);

            console.log('✅ Lightbox inicializado correctamente');
        }


        function addProductToCartFromModal(productData, modalId) {
            const modal = document.getElementById(modalId);
            const configuration = getSelectedOptionsFromModal(modal);

            // Calculate total price with modifiers for both hamburger and dip modals
            let totalPrice = productData.price;
            modal.querySelectorAll('.variant-select, .option-select, #dipSelect').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier) {
                    const modifier = parseFloat(selectedOption.dataset.priceModifier);
                    if (!isNaN(modifier)) {
                        // El modifier puede ser negativo (ej: -2000 para "Simple"), así que simplemente sumamos
                        // (suma de negativo = resta automáticamente)
                        totalPrice += modifier;
                    }
                }
            });

            // Add to cart using the calculated total price
            addToCartWithConfigurationAndPrice(productData.id, productData.name, totalPrice, productData.image,
                configuration);

            // Activar animación del botón
            triggerCartButtonAnimation('hamburgerAddToCart');

            // Esperar a que termine la animación antes de cerrar el modal
            // La animación tiene 300ms (adding) + 2000ms (added) = 2300ms total
            // Cerramos después de que se vea el check (300ms + 1500ms = 1800ms)
            setTimeout(() => {
                // Close modal
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
                }

                // Show success message
                showToast('Producto agregado al carrito', 'success');
            }, 1800); // Cerrar después de 1.8 segundos para que se vea la animación completa
        }

        function getSelectedOptionsFromModal(modal) {
            const configuration = {};

            // Get variants (medallones, tipo de medallón)
            modal.querySelectorAll('.variant-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                const variantName = select.dataset.variantName.toLowerCase().replace(/\s+/g, '_');

                if (variantName === 'medallones') {
                    configuration.medallones = selectedOption.value;
                } else if (variantName === 'tipo_de_medallón') {
                    configuration.tipo_medallon = selectedOption.value;
                }
            });

            // Get options (aderezos, dips)
            modal.querySelectorAll('.option-select:not(.extras-select):not(.dip-extra-select)').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                const optionName = select.dataset.optionName.toLowerCase();

                if (optionName === 'aderezos') {
                    if (!configuration.aderezos) configuration.aderezos = [];
                    configuration.aderezos.push(selectedOption.value);
                } else if (optionName === 'dips') {
                    if (!configuration.dips) configuration.dips = [];
                    configuration.dips.push(selectedOption.value);
                } else if (optionName === 'dip') {
                    // Handle singular "dip" for hamburger modal
                    if (!configuration.dips) configuration.dips = [];
                    configuration.dips.push(selectedOption.value);
                }
            });

            // Get extras (multiple extras)
            if (!configuration.extras) configuration.extras = [];
            modal.querySelectorAll('.extras-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value !== '') {
                    configuration.extras.push(selectedOption.value);
                }
            });

            // Get dip extras (multiple dip extras)
            if (!configuration.dip_extra) configuration.dip_extra = [];
            modal.querySelectorAll('.dip-extra-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value !== '') {
                    configuration.dip_extra.push(selectedOption.value);
                }
            });

            // Handle dip modal specifically (single dip selection)
            const dipSelect = modal.querySelector('#dipSelect');
            if (dipSelect) {
                const selectedOption = dipSelect.options[dipSelect.selectedIndex];
                configuration.dips = [selectedOption.value];
            }

            return configuration;
        }

        // Function to initialize price updates for hamburger modal
        /**
         * Filtrar aderezos según el producto
         * Si el producto tiene un aderezo de carta, solo mostrar "Sin aderezo" y ese aderezo
         * Si no tiene, mostrar todos los aderezos disponibles
         */
        function filterAderezosForProduct(defaultSauceId, defaultSauceValue) {
            const aderezosSelect = document.getElementById('aderezosSelect');
            if (!aderezosSelect) return;
            
            // Guardar todas las opciones originales si no están guardadas
            if (!aderezosSelect.dataset.originalOptions) {
                const originalOptions = Array.from(aderezosSelect.options).map(opt => ({
                    value: opt.value,
                    text: opt.text,
                    priceModifier: opt.dataset.priceModifier || '0',
                    configId: opt.dataset.configId || ''
                }));
                aderezosSelect.dataset.originalOptions = JSON.stringify(originalOptions);
            }
            
            // Si no hay aderezo de carta, restaurar todas las opciones
            if (!defaultSauceId || !defaultSauceValue) {
                const originalOptions = JSON.parse(aderezosSelect.dataset.originalOptions);
                aderezosSelect.innerHTML = '';
                originalOptions.forEach((opt, index) => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    option.dataset.priceModifier = opt.priceModifier;
                    option.dataset.configId = opt.configId;
                    if (index === 0) option.selected = true;
                    aderezosSelect.appendChild(option);
                });
                return;
            }
            
            // Si hay aderezo de carta, mostrar solo "Sin aderezo" y el aderezo de carta
            const originalOptions = JSON.parse(aderezosSelect.dataset.originalOptions);
            aderezosSelect.innerHTML = '';
            
            // Agregar "Sin aderezo" (primera opción, que generalmente es la que tiene value vacío o "Sin aderezo")
            const sinAderezoOption = originalOptions.find(opt => 
                opt.value === '' || 
                opt.value.toLowerCase().includes('sin') || 
                opt.value.toLowerCase().includes('ninguno')
            ) || originalOptions[0];
            
            const sinAderezo = document.createElement('option');
            sinAderezo.value = sinAderezoOption.value;
            sinAderezo.textContent = sinAderezoOption.text;
            sinAderezo.dataset.priceModifier = sinAderezoOption.priceModifier;
            sinAderezo.dataset.configId = sinAderezoOption.configId;
            aderezosSelect.appendChild(sinAderezo);
            
            // Agregar el aderezo de carta (seleccionado por defecto)
            const defaultSauceOption = originalOptions.find(opt => {
                // Comparar por value (texto) o por configId (ID numérico)
                const valueMatch = opt.value && defaultSauceValue && opt.value.trim() === defaultSauceValue.trim();
                const idMatch = opt.configId && defaultSauceId && String(opt.configId) === String(defaultSauceId);
                return valueMatch || idMatch;
            });
            
            if (defaultSauceOption) {
                const defaultSauce = document.createElement('option');
                defaultSauce.value = defaultSauceOption.value;
                defaultSauce.textContent = defaultSauceOption.text;
                defaultSauce.dataset.priceModifier = defaultSauceOption.priceModifier;
                defaultSauce.dataset.configId = defaultSauceOption.configId;
                defaultSauce.selected = true;
                aderezosSelect.appendChild(defaultSauce);
            } else {
                // Si no se encuentra el aderezo en las opciones originales, crear una opción manualmente
                const defaultSauce = document.createElement('option');
                defaultSauce.value = defaultSauceValue;
                defaultSauce.textContent = defaultSauceValue;
                defaultSauce.dataset.priceModifier = '0';
                defaultSauce.dataset.configId = defaultSauceId;
                defaultSauce.selected = true;
                aderezosSelect.appendChild(defaultSauce);
            }
        }

        function initializeHamburgerModalPriceUpdates(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const priceElement = document.getElementById('hamburgerModalPrice');

            // Remove existing listeners to avoid duplicates
            modal.querySelectorAll('.variant-select, .option-select').forEach(select => {
                const newSelect = select.cloneNode(true);
                select.parentNode.replaceChild(newSelect, select);
            });

            // Add change listeners to all selects
            modal.querySelectorAll('.variant-select, .option-select').forEach(select => {
                select.addEventListener('change', function() {
                    updateHamburgerModalPrice(basePrice);
                });
            });

            // Initialize extras functionality
            initializeExtrasFunctionality(basePrice);
            
            // Initialize dip extra functionality
            initializeDipExtraFunctionality(basePrice);

            // Initial price calculation
            updateHamburgerModalPrice(basePrice);
        }

        // Function to initialize extras functionality
        function initializeExtrasFunctionality(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const container = modal.querySelector('#extras-container');
            if (!container) return;

            // Clear all added rows, keep only the first
            const rows = container.querySelectorAll('.extras-row');
            for (let i = 1; i < rows.length; i++) rows[i].remove();

            // Reset first row select and ensure its button is "+"
            const firstRow = container.querySelector('.extras-row');
            if (firstRow) {
                const sel = firstRow.querySelector('.extras-select');
                if (sel) sel.selectedIndex = 0;
                const btn = firstRow.querySelector('.add-extra-btn, .remove-extra-btn');
                if (btn && !btn.classList.contains('add-extra-btn')) {
                    btn.innerHTML = '<i class="fas fa-plus"></i>';
                    btn.classList.remove('btn-outline-danger', 'remove-extra-btn');
                    btn.classList.add('btn-outline-success', 'add-extra-btn');
                    btn.title = 'Agregar otro extra';
                }
            }

            // Event delegation — replace old handler to avoid duplicates
            if (container._extrasClickHandler) {
                container.removeEventListener('click', container._extrasClickHandler);
            }
            container._extrasClickHandler = function(e) {
                if (e.target.closest('.add-extra-btn')) {
                    addExtraRow(container, basePrice);
                } else if (e.target.closest('.remove-extra-btn')) {
                    const row = e.target.closest('.extras-row');
                    if (row) { row.remove(); updateHamburgerModalPrice(basePrice); }
                }
            };
            container.addEventListener('click', container._extrasClickHandler);

            // Change delegation for selects
            if (container._extrasChangeHandler) {
                container.removeEventListener('change', container._extrasChangeHandler);
            }
            container._extrasChangeHandler = function(e) {
                if (e.target.matches('.extras-select')) updateHamburgerModalPrice(basePrice);
            };
            container.addEventListener('change', container._extrasChangeHandler);
        }

        // Function to add a new extra row
        function addExtraRow(container, basePrice) {
            const firstRow = container.querySelector('.extras-row');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);
            newRow.style.marginTop = '4px';

            // Reset select
            const newSelect = newRow.querySelector('.extras-select');
            if (newSelect) newSelect.selectedIndex = 0;

            // Change "+" to "-"
            const btn = newRow.querySelector('.add-extra-btn');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-minus"></i>';
                btn.classList.remove('btn-outline-success', 'add-extra-btn');
                btn.classList.add('btn-outline-danger', 'remove-extra-btn');
                btn.title = 'Eliminar este extra';
            }

            container.appendChild(newRow);
            updateHamburgerModalPrice(basePrice);
        }

        // Function to initialize dip extra functionality
        function initializeDipExtraFunctionality(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const container = modal.querySelector('#dip-extra-container');
            if (!container) return;

            // Clear all added rows, keep only the first
            const rows = container.querySelectorAll('.dip-extra-row');
            for (let i = 1; i < rows.length; i++) rows[i].remove();

            // Reset first row select and ensure its button is "+"
            const firstRow = container.querySelector('.dip-extra-row');
            if (firstRow) {
                const sel = firstRow.querySelector('.dip-extra-select');
                if (sel) sel.selectedIndex = 0;
                const btn = firstRow.querySelector('.add-dip-extra-btn, .remove-dip-extra-btn');
                if (btn && !btn.classList.contains('add-dip-extra-btn')) {
                    btn.innerHTML = '<i class="fas fa-plus"></i>';
                    btn.classList.remove('btn-outline-danger', 'remove-dip-extra-btn');
                    btn.classList.add('btn-outline-success', 'add-dip-extra-btn');
                    btn.title = 'Agregar otro dip extra';
                }
            }

            // Event delegation — replace old handler to avoid duplicates
            if (container._dipExtraClickHandler) {
                container.removeEventListener('click', container._dipExtraClickHandler);
            }
            container._dipExtraClickHandler = function(e) {
                if (e.target.closest('.add-dip-extra-btn')) {
                    addDipExtraRow(container, basePrice);
                } else if (e.target.closest('.remove-dip-extra-btn')) {
                    const row = e.target.closest('.dip-extra-row');
                    if (row) { row.remove(); updateHamburgerModalPrice(basePrice); }
                }
            };
            container.addEventListener('click', container._dipExtraClickHandler);

            // Change delegation for selects
            if (container._dipExtraChangeHandler) {
                container.removeEventListener('change', container._dipExtraChangeHandler);
            }
            container._dipExtraChangeHandler = function(e) {
                if (e.target.matches('.dip-extra-select')) updateHamburgerModalPrice(basePrice);
            };
            container.addEventListener('change', container._dipExtraChangeHandler);
        }

        // Function to add a new dip extra row
        function addDipExtraRow(container, basePrice) {
            const firstRow = container.querySelector('.dip-extra-row');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);
            newRow.style.marginTop = '4px';

            // Reset select
            const newSelect = newRow.querySelector('.dip-extra-select');
            if (newSelect) newSelect.selectedIndex = 0;

            // Change "+" to "-"
            const btn = newRow.querySelector('.add-dip-extra-btn');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-minus"></i>';
                btn.classList.remove('btn-outline-success', 'add-dip-extra-btn');
                btn.classList.add('btn-outline-danger', 'remove-dip-extra-btn');
                btn.title = 'Eliminar este dip extra';
            }

            container.appendChild(newRow);
            updateHamburgerModalPrice(basePrice);
        }

        // Function to update the price in hamburger modal
        function updateHamburgerModalPrice(basePrice) {
            const modal = document.getElementById('hamburgerModal');
            const priceElement = document.getElementById('hamburgerModalPrice');

            let totalPrice = basePrice;

            // Calculate modifiers from variant selects (medallones, tipo de medallón)
            modal.querySelectorAll('.variant-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier) {
                    const modifier = parseFloat(selectedOption.dataset.priceModifier);
                    // Si el modifier es negativo o el valor es "Simple", restar (el modifier ya tiene el signo correcto)
                    // El modifier puede venir negativo desde la BD, así que simplemente sumamos (suma de negativo = resta)
                    totalPrice += modifier;
                }
            });

            // Calculate modifiers from option selects (aderezos, dips)
            modal.querySelectorAll('.option-select:not(.extras-select):not(.dip-extra-select)').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier) {
                    totalPrice += parseFloat(selectedOption.dataset.priceModifier);
                }
            });

            // Calculate modifiers from extras selects (multiple extras)
            modal.querySelectorAll('.extras-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier && selectedOption.value !== '') {
                    totalPrice += parseFloat(selectedOption.dataset.priceModifier);
                }
            });

            // Calculate modifiers from dip extra selects (multiple dip extras)
            modal.querySelectorAll('.dip-extra-select').forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.priceModifier && selectedOption.value !== '') {
                    totalPrice += parseFloat(selectedOption.dataset.priceModifier);
                }
            });

            // Update price display
            priceElement.textContent = '$' + totalPrice.toFixed(2);
        }

        // Function to add product to cart with configuration and pre-calculated price
        function addToCartWithConfigurationAndPrice(productId, productName, totalPrice, productImage, configuration) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Create unique key for this product configuration
            const configKey = `${productId}_${JSON.stringify(configuration)}`;

            // Check if this exact configuration already exists
            const existingItem = cart.find(item => item.configKey === configKey);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    productId: productId,
                    name: productName,
                    price: totalPrice,
                    image: productImage,
                    configuration: configuration,
                    quantity: 1,
                    configKey: configKey
                });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            updateCartOffcanvas();
        }
        
        // Show Toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                console.warn('Toast container not found');
                return;
            }
            
            const toast = document.createElement('div');
            toast.className = `bg-${type === 'success' ? 'success' : 'danger'} text-white px-4 py-3 rounded-lg shadow-lg mb-2`;
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    <span>${message}</span>
                </div>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // Función para activar la animación del botón de carrito (por ID)
        function triggerCartButtonAnimation(buttonId) {
            const button = document.getElementById(buttonId);
            if (button) {
                triggerCartButtonAnimationOnElement(button);
            }
        }
        
        // Función para activar la animación del botón de carrito (por elemento)
        function triggerCartButtonAnimationOnElement(button) {
            if (button && button.classList.contains('cart-button')) {
                button.classList.remove('added');
                button.classList.add('adding');
                
                setTimeout(() => {
                    button.classList.remove('adding');
                    button.classList.add('added');
                    
                    setTimeout(() => {
                        button.classList.remove('added');
                    }, 2000);
                }, 300);
            }
        }

        // Legacy function - kept for compatibility but now just passes the base price
        function addToCartWithConfiguration(productId, productName, basePrice, productImage, configuration) {
            addToCartWithConfigurationAndPrice(productId, productName, basePrice, productImage, configuration);
        }

        // Grid / Carousel switcher (Swiper)
        function initializeViewSwitcher(){
            const gridBtn = document.getElementById('gridBtn');
            const carouselBtn = document.getElementById('carouselBtn');
            const gridEl = document.getElementById('productsGrid');
            const carouselEl = document.getElementById('productsCarousel');

            if(!gridBtn || !carouselBtn || !gridEl || !carouselEl) return;

            function enableGrid(){
                gridEl.classList.remove('d-none');
                carouselEl.classList.add('d-none');
                gridBtn.classList.add('active');
                carouselBtn.classList.remove('active');
                // Re-render grid for current category
                if (typeof renderByCategory === 'function') {
                    renderByCategory(currentCategory || 'hamburguesas');
                }
            }

            function enableCarousel(){
                gridEl.classList.add('d-none');
                carouselEl.classList.remove('d-none');
                carouselBtn.classList.add('active');
                gridBtn.classList.remove('active');

                // Cargar Swiper de context si no está
                function initSwiper(){
                    if(window.menuSwiper && typeof window.menuSwiper.update === 'function'){
                        window.menuSwiper.update();
                        if (typeof renderByCategory === 'function') {
                            renderByCategory(currentCategory || 'hamburguesas');
                        }
                        return;
                    }
                    if(window.Swiper){
                        window.menuSwiper = new Swiper('#productsCarousel', {
                            effect: 'coverflow',
                            grabCursor: true,
                            centeredSlides: true,
                            slidesPerView: 'auto',
                            initialSlide: 0,
                            coverflowEffect: { rotate: 20, stretch: 0, depth: 200, modifier: 1.5, slideShadows: true },
                            pagination: { el: '.swiper-pagination', clickable: true, dynamicBullets: true },
                            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                            keyboard: { enabled: true },
                            loop: true,
                            autoplay: { delay: 3500, disableOnInteraction: true }
                        });
                        if (typeof renderByCategory === 'function') {
                            renderByCategory(currentCategory || 'hamburguesas');
                        }
                    }
                }

                if(!window.Swiper){
                    var existing = document.getElementById('swiperJs');
                    if(existing){ existing.addEventListener('load', initSwiper); }
                    else {
                        var s = document.createElement('script');
                        s.id = 'swiperJs';
                        s.src = 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js';
                        s.onload = initSwiper;
                        document.body.appendChild(s);
                    }
                } else { initSwiper(); }
            }

            // Eventos
            gridBtn.addEventListener('click', enableGrid);
            carouselBtn.addEventListener('click', enableCarousel);

            // Iniciar en grid por defecto
            enableGrid();
        }
    </script>
@endpush
