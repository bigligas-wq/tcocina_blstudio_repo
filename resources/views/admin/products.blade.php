@extends('layouts.admin')

@section('title', 'Productos - Admin')

@section('page-title', 'Productos')

@php
    // Mapeo de iconos por slug de categoría (mismo que el catálogo público)
    $adminCategoryIcons = [
        'hamburguesas' => ['fa' => 'fas fa-hamburger', 'lordicon' => null],
        'acompanamientos' => ['fa' => 'fas fa-french-fries', 'lordicon' => null],
        'bebidas' => ['fa' => 'fas fa-mug-hot', 'lordicon' => 'bebidas.json'],
        'postres' => ['fa' => 'fas fa-ice-cream', 'lordicon' => 'postres.json'],
        'combos' => ['fa' => 'fas fa-utensils', 'lordicon' => null],
    ];
    $hamburguesasCategoryId = optional($categories->firstWhere('slug', 'hamburguesas'))->id;
@endphp

@push('styles')
    <style>
        /* Inputs más visibles dentro de los modales de productos */
        #modalCreateProduct .form-control,
        #modalCreateProduct .form-select,
        [id^="modalEditProduct"] .form-control,
        [id^="modalEditProduct"] .form-select {
            border: 1.5px solid #4b5563 !important;
            background-color: #fff;
        }

        #modalCreateProduct .form-control:hover,
        #modalCreateProduct .form-select:hover,
        [id^="modalEditProduct"] .form-control:hover,
        [id^="modalEditProduct"] .form-select:hover {
            border-color: #1f2937 !important;
        }

        #modalCreateProduct .form-control:focus,
        #modalCreateProduct .form-select:focus,
        [id^="modalEditProduct"] .form-control:focus,
        [id^="modalEditProduct"] .form-select:focus {
            border-color: #111827 !important;
            box-shadow: 0 0 0 0.2rem rgba(17, 24, 39, 0.15) !important;
        }

        #modalCreateProduct .form-label,
        [id^="modalEditProduct"] .form-label {
            font-weight: 600;
            color: #1f2937;
        }
    </style>
@endpush

@section('content')
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Listado de Productos</h5>
                <small class="text-muted">
                    @if ($selectedCategory)
                        Mostrando <strong>{{ $selectedCategory->name }}</strong>
                        ({{ $countsByCategory[$selectedCategory->id] ?? 0 }})
                    @else
                        Mostrando todas las categorías ({{ $totalProducts }} productos)
                    @endif
                </small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDefaultView" title="Vista por defecto del catálogo">
                    <i class="fas fa-desktop me-1"></i>
                    Vista inicial:
                    <span class="badge bg-primary ms-1" id="currentDefaultViewBadge">
                        @if($defaultCatalogView === 'list') <i class="fas fa-list me-1"></i>Lista
                        @elseif($defaultCatalogView === 'carousel') <i class="fas fa-images me-1"></i>Carrusel
                        @else <i class="fas fa-border-all me-1"></i>Grid
                        @endif
                    </span>
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateProduct"
                    data-default-category="{{ $selectedCategory->id ?? '' }}">
                    <i class="fas fa-plus me-2"></i>Nuevo producto
                </button>
            </div>
        </div>

        {{-- Pestañas de categorías --}}
        <ul class="nav nav-pills flex-wrap gap-2 mb-3" id="adminProductCategoryTabs">
            <li class="nav-item">
                <a class="nav-link {{ !$selectedCategory ? 'active' : '' }}"
                    href="{{ route('admin.products', ['category' => 'all']) }}">
                    <i class="fas fa-th-large me-1"></i>
                    Todas
                    <span class="badge bg-light text-dark ms-1">{{ $totalProducts }}</span>
                </a>
            </li>
            @foreach ($categories as $cat)
                @php
                    $iconData = $adminCategoryIcons[$cat->slug] ?? ['fa' => 'fas fa-tag', 'lordicon' => null];
                    $iconClass = $iconData['fa'];
                    $lordiconFile = $iconData['lordicon'];
                    $count = $countsByCategory[$cat->id] ?? 0;
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $selectedCategory && $selectedCategory->id === $cat->id ? 'active' : '' }}"
                        href="{{ route('admin.products', ['category' => $cat->slug]) }}">
                        @if($lordiconFile)
                            <lord-icon
                                src="{{ asset('lordicons/' . $lordiconFile) }}"
                                colors="primary:#0a2540,secondary:#0a2540"
                                trigger="hover"
                                style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:4px;">
                            </lord-icon>
                        @else
                            <i class="{{ $iconClass }} me-1"></i>
                        @endif
                        {{ $cat->name }}
                        <span class="badge bg-light text-dark ms-1">{{ $count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Disponible</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td style="width: 70px;">
                                @if ($product->image)
                                    <img src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}"
                                        class="rounded" style="width: 56px; height: 56px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px;">N/A</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <div class="text-muted small">{{ Str::limit($product->description, 80) }}</div>
                            </td>
                            <td>
                                @if ($product->category)
                                    @php $catIconData = $adminCategoryIcons[$product->category->slug] ?? ['fa' => 'fas fa-tag', 'lordicon' => null]; @endphp
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        @if($catIconData['lordicon'])
                                            <lord-icon
                                                src="{{ asset('lordicons/' . $catIconData['lordicon']) }}"
                                                colors="primary:#0a2540,secondary:#0a2540"
                                                trigger="hover"
                                                style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:4px;">
                                            </lord-icon>
                                        @else
                                            <i class="{{ $catIconData['fa'] }} me-1"></i>
                                        @endif
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">${{ number_format($product->base_price, 2) }}</td>
                            <td class="text-center">
                                <span
                                    class="badge {{ $product->is_available ? 'bg-success' : 'bg-secondary' }}">{{ $product->is_available ? 'Sí' : 'No' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#modalEditProduct{{ $product->id }}">
                                                <i class="fas fa-edit me-2"></i>Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="dropdown-item text-danger" data-bs-toggle="modal"
                                                data-bs-target="#modalDeleteProduct{{ $product->id }}">
                                                <i class="fas fa-trash me-2"></i>Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay productos para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal: Crear producto -->
    <div class="modal fade" id="modalCreateProduct" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('admin.products.store') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Crear producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select name="category_id" class="form-select category-select" data-target-sauce="sauceFieldCreate" required>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" data-slug="{{ $cat->slug }}"
                                        {{ $selectedCategory && $selectedCategory->id === $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio base</label>
                            <input type="number" step="0.01" name="base_price" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="image" class="form-control" accept="image/*"
                                onchange="previewImage(event, 'createPreview')">
                            <div class="mt-2">
                                <img id="createPreview" src="#" alt="Preview"
                                    style="display:none; height:80px; object-fit:cover;" class="rounded" />
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 sauce-field-wrapper" id="sauceFieldCreate">
                            <label class="form-label">Aderezo de Carta</label>
                            <select name="default_sauce_configuration_id" class="form-select">
                                <option value="">Ninguno (mostrar todos los aderezos)</option>
                                @foreach ($availableSauces as $sauce)
                                    <option value="{{ $sauce->id }}">{{ $sauce->value }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Solo aplica a Hamburguesas. Si seleccionas un aderezo, solo se mostrará "Sin aderezo" y este aderezo en la personalización.</small>
                        </div>
                        <div class="col-12 form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="is_available" id="createAvailable"
                                checked>
                            <label for="createAvailable" class="form-check-label">Disponible</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modales por producto: Editar y Eliminar -->
    @foreach ($products as $product)
        <div class="modal fade" id="modalEditProduct{{ $product->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('admin.products.update', $product->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ $product->name }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría</label>
                                <select name="category_id" class="form-select category-select"
                                    data-target-sauce="sauceFieldEdit{{ $product->id }}" required>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" data-slug="{{ $cat->slug }}"
                                            {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Precio base</label>
                                <input type="number" step="0.01" name="base_price" class="form-control"
                                    value="{{ $product->base_price }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Imagen</label>
                                <input type="file" name="image" class="form-control" accept="image/*"
                                    onchange="previewImage(event, 'editPreview{{ $product->id }}')">
                                <div class="mt-2">
                                    @if ($product->image)
                                        <img id="editPreview{{ $product->id }}"
                                            src="{{ asset('images/' . $product->image) }}" alt="Preview"
                                            style="height:80px; object-fit:cover;" class="rounded" />
                                    @else
                                        <img id="editPreview{{ $product->id }}" src="#" alt="Preview"
                                            style="display:none; height:80px; object-fit:cover;" class="rounded" />
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                            </div>
                            <div class="col-12 sauce-field-wrapper" id="sauceFieldEdit{{ $product->id }}">
                                <label class="form-label">Aderezo de Carta</label>
                                <select name="default_sauce_configuration_id" class="form-select">
                                    <option value="">Ninguno (mostrar todos los aderezos)</option>
                                    @foreach ($availableSauces as $sauce)
                                        <option value="{{ $sauce->id }}" {{ $product->default_sauce_configuration_id == $sauce->id ? 'selected' : '' }}>
                                            {{ $sauce->value }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Solo aplica a Hamburguesas. Si seleccionas un aderezo, solo se mostrará "Sin aderezo" y este aderezo en la personalización.</small>
                            </div>
                            <div class="col-12 form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="is_available"
                                    id="editAvailable{{ $product->id }}" {{ $product->is_available ? 'checked' : '' }}>
                                <label for="editAvailable{{ $product->id }}" class="form-check-label">Disponible</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalDeleteProduct{{ $product->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.products.destroy', $product->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ¿Seguro que deseas eliminar "{{ $product->name }}"?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
    @push('scripts')
        <script>
            function previewImage(event, imgId) {
                const input = event.target;
                const img = document.getElementById(imgId);
                if (!img) return;
                if (input.files && input.files[0]) {
                    img.src = URL.createObjectURL(input.files[0]);
                    img.style.display = 'block';
                }
            }

            // Mostrar/ocultar el campo "Aderezo de Carta" según la categoría seleccionada.
            // Sólo aplica a Hamburguesas.
            (function () {
                function syncSauceVisibility(select) {
                    const targetId = select.dataset.targetSauce;
                    if (!targetId) return;
                    const wrapper = document.getElementById(targetId);
                    if (!wrapper) return;
                    const opt = select.options[select.selectedIndex];
                    const slug = opt ? opt.dataset.slug : '';
                    const isHamburguesa = slug === 'hamburguesas';
                    wrapper.style.display = isHamburguesa ? '' : 'none';
                    if (!isHamburguesa) {
                        const innerSelect = wrapper.querySelector('select');
                        if (innerSelect) innerSelect.value = '';
                    }
                }

                document.querySelectorAll('select.category-select').forEach(function (select) {
                    syncSauceVisibility(select);
                    select.addEventListener('change', function () {
                        syncSauceVisibility(select);
                    });
                });
            })();
        </script>
    @endpush

    {{-- Modal: Vista por defecto del catálogo --}}
    <div class="modal fade" id="modalDefaultView" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-desktop me-2"></i>Vista inicial del catálogo</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Seleccioná cómo se visualizan los productos cuando el cliente abre el menú.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn {{ $defaultCatalogView === 'grid' ? 'btn-primary' : 'btn-outline-primary' }} default-view-btn" data-view="grid">
                            <i class="fas fa-border-all me-2"></i>Grid
                            @if($defaultCatalogView === 'grid') <i class="fas fa-check ms-2"></i> @endif
                        </button>
                        <button type="button" class="btn {{ $defaultCatalogView === 'list' ? 'btn-primary' : 'btn-outline-primary' }} default-view-btn" data-view="list">
                            <i class="fas fa-list me-2"></i>Lista
                            @if($defaultCatalogView === 'list') <i class="fas fa-check ms-2"></i> @endif
                        </button>
                        <button type="button" class="btn {{ $defaultCatalogView === 'carousel' ? 'btn-primary' : 'btn-outline-primary' }} default-view-btn" data-view="carousel">
                            <i class="fas fa-images me-2"></i>Carrusel
                            @if($defaultCatalogView === 'carousel') <i class="fas fa-check ms-2"></i> @endif
                        </button>
                    </div>
                    <div id="defaultViewFeedback" class="mt-2 small text-success d-none">
                        <i class="fas fa-check-circle me-1"></i>Guardado correctamente
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.default-view-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const view = this.dataset.view;
                    fetch('{{ route('admin.products.default-category') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ default_view: view })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('.default-view-btn').forEach(b => {
                                b.classList.remove('btn-primary');
                                b.classList.add('btn-outline-primary');
                                b.querySelector('.fa-check')?.remove();
                            });
                            this.classList.remove('btn-outline-primary');
                            this.classList.add('btn-primary');
                            const icon = document.createElement('i');
                            icon.className = 'fas fa-check ms-2';
                            this.appendChild(icon);
                            const labels = { grid: '<i class="fas fa-border-all me-1"></i>Grid', list: '<i class="fas fa-list me-1"></i>Lista', carousel: '<i class="fas fa-images me-1"></i>Carrusel' };
                            const badge = document.getElementById('currentDefaultViewBadge');
                            if (badge) badge.innerHTML = labels[view] || view;
                            const fb = document.getElementById('defaultViewFeedback');
                            if (fb) { fb.classList.remove('d-none'); setTimeout(() => fb.classList.add('d-none'), 2500); }
                        }
                    });
                });
            });
        </script>
    @endpush

    @push('styles')
    <style>
        /* Mobile Responsive Styles for Products */
        @media (max-width: 767.98px) {
            /* Header compacto */
            .card.p-4 {
                padding: 0.75rem !important;
            }
            
            .card.p-4 h5 {
                font-size: 1rem;
                margin-bottom: 0.25rem !important;
            }
            
            .card.p-4 small.text-muted {
                font-size: 0.75rem;
            }
            
            /* Botones más compactos */
            .card.p-4 .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .card.p-4 .btn i {
                margin-right: 0.25rem !important;
            }
            
            .card.p-4 .badge {
                font-size: 0.7rem;
            }
            
            /* Pestañas de categoría en scroll horizontal */
            #adminProductCategoryTabs {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 0.5rem;
                gap: 0.5rem !important;
            }
            
            #adminProductCategoryTabs .nav-item {
                flex-shrink: 0;
            }
            
            #adminProductCategoryTabs .nav-link {
                padding: 0.4rem 0.75rem;
                font-size: 0.8rem;
                white-space: nowrap;
            }
            
            #adminProductCategoryTabs .nav-link i {
                margin-right: 0.25rem !important;
            }
            
            #adminProductCategoryTabs .badge {
                font-size: 0.7rem;
                margin-left: 0.25rem !important;
            }
            
            /* Tabla más compacta */
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table thead th {
                font-size: 0.75rem;
                padding: 0.5rem 0.25rem;
                white-space: nowrap;
            }
            
            .table tbody td {
                padding: 0.5rem 0.25rem;
            }
            
            /* Imagen de producto más pequeña */
            .table td[style*="width: 70px"] img,
            .table td[style*="width: 70px"] div {
                width: 40px !important;
                height: 40px !important;
            }
            
            /* Nombre de producto más compacto */
            .table .fw-semibold {
                font-size: 0.85rem;
                line-height: 1.2;
            }
            
            .table .text-muted.small {
                font-size: 0.7rem;
                line-height: 1.2;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            
            /* Badges de categoría compactos */
            .table .badge {
                font-size: 0.7rem;
                padding: 0.25em 0.4em;
            }
            
            /* Precio más compacto */
            .table td.text-end {
                font-size: 0.8rem;
                font-weight: 600;
            }
            
            /* Toggle switch más pequeño */
            .form-check-input {
                width: 2em;
                height: 1em;
            }
            
            /* Botones de acción más pequeños */
            .table .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.75rem;
            }
            
            /* Ocultar descripción truncada en pantallas muy pequeñas */
            @media (max-width: 375px) {
                .table .text-muted.small {
                    display: none;
                }
            }
        }
        
        /* Tablet adjustments */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .card.p-4 {
                padding: 1rem !important;
            }
            
            #adminProductCategoryTabs .nav-link {
                padding: 0.5rem 0.875rem;
                font-size: 0.85rem;
            }
        }
    </style>
    @endpush
@endsection
