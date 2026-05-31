@extends('layouts.admin')

@section('title', 'Productos - Admin')

@section('page-title', 'Productos')

@section('content')
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Listado de Productos</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateProduct">
                <i class="fas fa-plus me-2"></i>Nuevo producto
            </button>
        </div>

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
                                    <img src="{{ \Illuminate\Support\Str::startsWith($product->image, 'products/') ? \Illuminate\Support\Facades\Storage::url($product->image) : asset('images/products/' . $product->image) }}"
                                        alt="{{ $product->name }}" class="rounded"
                                        style="width: 56px; height: 56px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px;">N/A</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <div class="text-muted small">{{ Str::limit($product->description, 80) }}</div>
                            </td>
                            <td>{{ $product->category->name ?? '-' }}</td>
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
                            <td colspan="5" class="text-center text-muted py-4">No hay productos para mostrar.</td>
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
                            <select name="category_id" class="form-select" required>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
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
                                <select name="category_id" class="form-select" required>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
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
                                            src="{{ \Illuminate\Support\Str::startsWith($product->image, 'products/') ? \Illuminate\Support\Facades\Storage::url($product->image) : asset('images/products/' . $product->image) }}"
                                            alt="Preview" style="height:80px; object-fit:cover;" class="rounded" />
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
        </script>
    @endpush
@endsection
