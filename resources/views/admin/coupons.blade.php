@extends('layouts.admin')

@section('title', 'Cupones - Admin')

@section('page-title', 'Cupones')

@section('content')
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Listado de Cupones</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateCoupon">
                <i class="fas fa-plus me-2"></i>Nuevo cupón
            </button>
        </div>

        <!-- Barra de acciones masivas -->
        <div id="bulkActionBar" class="d-none alert alert-warning d-flex align-items-center justify-content-between gap-2 mb-3 py-2 px-3">
            <span><strong id="bulkSelectedCount">0</strong> cupón(es) seleccionado(s)</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                    <i class="fas fa-trash me-1"></i>Eliminar seleccionados
                </button>
                <button type="button" class="btn btn-sm btn-secondary" id="bulkCancelBtn">
                    Cancelar
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width:36px;">
                            <input type="checkbox" id="selectAllCoupons" class="form-check-input" title="Seleccionar todos">
                        </th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th class="text-center">Descuento</th>
                        <th class="text-center">Acumula Efectivo</th>
                        <th class="text-center">Longitud</th>
                        <th class="text-center">Usos</th>
                        <th class="text-center">Estado</th>
                        <th>Válido hasta</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input coupon-checkbox" value="{{ $coupon->id }}">
                            </td>
                            <td>
                                <strong class="text-primary">{{ $coupon->code }}</strong>
                            </td>
                            <td>{{ $coupon->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $coupon->discount_percentage }}%</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $coupon->allow_cash_discount ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $coupon->allow_cash_discount ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $coupon->code_length }} caracteres</span>
                            </td>
                            <td class="text-center">
                                {{ $coupon->used_count }}
                                @if ($coupon->usage_limit)
                                    / {{ $coupon->usage_limit }}
                                @else
                                    / ∞
                                @endif
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge {{ $coupon->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $coupon->is_active ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td>
                                {{ $coupon->valid_until ? $coupon->valid_until->format('d/m/Y') : 'Sin límite' }}
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="#" class="dropdown-item" onclick="editCoupon({{ $coupon->id }})">
                                                <i class="fas fa-edit me-2"></i>Editar
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('¿Estás seguro de eliminar este cupón?')">
                                                    <i class="fas fa-trash me-2"></i>Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No hay cupones para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $coupons->links() }}
        </div>
    </div>

    <!-- Modal: Confirmar eliminación masiva -->
    <div class="modal fade" id="modalBulkDeleteConfirm" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">¿Estás seguro que querés eliminar <strong id="bulkDeleteCountText">0</strong> cupón(es)?</p>
                    <p class="text-danger small mb-0"><i class="fas fa-info-circle me-1"></i>Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.coupons.bulk-delete') }}">
                        @csrf
                        <div id="bulkDeleteInputs"></div>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Sí, eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Crear cupón -->
    <div class="modal fade" id="modalCreateCoupon" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('admin.coupons.store') }}" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Crear cupón</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Revisa los campos marcados en rojo.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="hidden" name="code" id="create_code" value="{{ old('code') }}">
                        <div id="create_code_otp_container" class="coupon-otp-wrapper d-flex justify-content-center gap-2 mb-2 {{ $errors->has('code') ? 'code-otp-invalid' : (old('code') && strlen(old('code')) === 8 ? 'code-otp-valid' : '') }}"></div>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="coupon-help">8 caracteres. Puede ser solo letras, solo números o mezcla (alfanumérico). Se convierte a mayúsculas.</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="create_name"
                                class="form-control {{ $errors->has('name') ? 'is-invalid' : (old('name') ? 'is-valid' : '') }}"
                                required maxlength="255" placeholder="(nombre interno del cupón)" value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Porcentaje de Descuento <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="discount_percentage" id="create_discount_percentage"
                                    class="form-control {{ $errors->has('discount_percentage') ? 'is-invalid' : (old('discount_percentage') !== null && old('discount_percentage') !== '' ? 'is-valid' : '') }}"
                                    required min="0" max="100" step="5" placeholder="5, 10, 15…"
                                    value="{{ old('discount_percentage') !== null && old('discount_percentage') !== '' ? round((float)old('discount_percentage') / 5) * 5 : '' }}">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('discount_percentage')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="coupon-help">De 5 en 5 hasta 100.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Límite de Usos</label>
                        <input type="number" name="usage_limit" class="form-control {{ $errors->has('usage_limit') ? 'is-invalid' : '' }}"
                            min="1" placeholder="(dejar vacío para ilimitado)" value="{{ old('usage_limit') }}">
                        @error('usage_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="coupon-help">Cantidad máxima de veces que se puede usar este cupón.</small>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="allow_cash_discount" value="0">
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-3">
                            <input class="form-check-input" type="checkbox" name="allow_cash_discount"
                                id="create_allow_cash_discount" value="1" {{ old('allow_cash_discount') ? 'checked' : '' }}>
                            <label class="form-check-label mb-0" for="create_allow_cash_discount">
                                Permitir acumular descuento en efectivo
                            </label>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label">Válido desde</label>
                            <input type="hidden" name="valid_from" id="create_valid_from" value="{{ old('valid_from') }}">
                            <input type="text" id="create_valid_from_display" class="form-control coupon-datetime-display {{ $errors->has('valid_from') ? 'is-invalid' : '' }}"
                                placeholder="DD/MM/AAAA HH:mm" autocomplete="off"
                                value="{{ old('valid_from') ? \Carbon\Carbon::parse(old('valid_from'))->format('d/m/Y H:i') : '' }}">
                            @error('valid_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Válido hasta</label>
                            <input type="hidden" name="valid_until" id="create_valid_until" value="{{ old('valid_until') }}">
                            <input type="text" id="create_valid_until_display" class="form-control coupon-datetime-display {{ $errors->has('valid_until') ? 'is-invalid' : '' }}"
                                placeholder="DD/MM/AAAA HH:mm" autocomplete="off"
                                value="{{ old('valid_until') ? \Carbon\Carbon::parse(old('valid_until'))->format('d/m/Y H:i') : '' }}">
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                            rows="3" maxlength="500" placeholder="(descripción opcional del cupón)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Editar cupón -->
    <div class="modal fade" id="modalEditCoupon" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" id="editCouponForm" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar cupón</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Revisa los campos marcados en rojo.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="hidden" name="code" id="edit_code" value="{{ old('code') }}">
                        <div id="edit_code_otp_container" class="coupon-otp-wrapper d-flex justify-content-center gap-2 mb-2 {{ $errors->has('code') ? 'code-otp-invalid' : (old('code') && strlen(old('code')) === 8 ? 'code-otp-valid' : '') }}"></div>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="coupon-help">8 caracteres. Puede ser solo letras, solo números o mezcla (alfanumérico). Se convierte a mayúsculas.</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name"
                                class="form-control {{ $errors->has('name') ? 'is-invalid' : (old('name') ? 'is-valid' : '') }}"
                                required maxlength="255" placeholder="(nombre interno del cupón)" value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Porcentaje de Descuento <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="discount_percentage" id="edit_discount_percentage"
                                    class="form-control {{ $errors->has('discount_percentage') ? 'is-invalid' : (old('discount_percentage') !== null && old('discount_percentage') !== '' ? 'is-valid' : '') }}"
                                    required min="0" max="100" step="5" placeholder="5, 10, 15…"
                                    value="{{ old('discount_percentage') }}">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('discount_percentage')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="coupon-help">De 5 en 5 hasta 100.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Límite de Usos</label>
                        <input type="number" name="usage_limit" id="edit_usage_limit" class="form-control {{ $errors->has('usage_limit') ? 'is-invalid' : '' }}"
                            min="1" placeholder="(dejar vacío para ilimitado)" value="{{ old('usage_limit') }}">
                        @error('usage_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="coupon-help">Cantidad máxima de veces que se puede usar este cupón.</small>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="allow_cash_discount" value="0">
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-3">
                            <input class="form-check-input" type="checkbox" name="allow_cash_discount"
                                id="edit_allow_cash_discount" value="1" {{ old('allow_cash_discount') ? 'checked' : '' }}>
                            <label class="form-check-label mb-0" for="edit_allow_cash_discount">
                                Permitir acumular descuento en efectivo
                            </label>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label">Válido desde</label>
                            <input type="hidden" name="valid_from" id="edit_valid_from" value="{{ old('valid_from') }}">
                            <input type="text" id="edit_valid_from_display" class="form-control coupon-datetime-display {{ $errors->has('valid_from') ? 'is-invalid' : '' }}"
                                placeholder="DD/MM/AAAA HH:mm" autocomplete="off"
                                value="{{ old('valid_from') ? \Carbon\Carbon::parse(old('valid_from'))->format('d/m/Y H:i') : '' }}">
                            @error('valid_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Válido hasta</label>
                            <input type="hidden" name="valid_until" id="edit_valid_until" value="{{ old('valid_until') }}">
                            <input type="text" id="edit_valid_until_display" class="form-control coupon-datetime-display {{ $errors->has('valid_until') ? 'is-invalid' : '' }}"
                                placeholder="DD/MM/AAAA HH:mm" autocomplete="off"
                                value="{{ old('valid_until') ? \Carbon\Carbon::parse(old('valid_until'))->format('d/m/Y H:i') : '' }}">
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" id="edit_description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                            rows="3" maxlength="500" placeholder="(descripción opcional del cupón)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active"
                                value="1" checked>
                            <label class="form-check-label" for="edit_is_active">
                                Cupón activo
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
        <style>
            .form-label .text-danger {
                color: #dc3545 !important;
            }
            /* Títulos de inputs en modales de cupones: mayúscula y fuente Google */
            #modalCreateCoupon .form-label,
            #modalEditCoupon .form-label {
                font-family: 'DM Sans', sans-serif;
                text-transform: uppercase;
                font-size: 0.7rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                color: #333;
            }
            #modalCreateCoupon .modal-body small.coupon-help,
            #modalEditCoupon .modal-body small.coupon-help {
                font-size: 0.7rem;
                color: #6c757d;
                display: block;
                margin-top: 0.25rem;
            }
            /* Bordes claros para validación en modales de cupones */
            #modalCreateCoupon .form-control.is-invalid,
            #modalEditCoupon .form-control.is-invalid {
                border-color: #dc3545;
                border-width: 2px;
            }
            #modalCreateCoupon .form-control.is-valid,
            #modalEditCoupon .form-control.is-valid {
                border-color: #198754;
                border-width: 2px;
            }
            /* OTP slots para código de cupón (igual que checkout) */
            .coupon-otp-wrapper .coupon-otp-slot {
                width: 42px;
                height: 42px;
                border: 2px solid #dee2e6;
                border-radius: 8px;
                text-align: center;
                font-size: 1.1rem;
                font-weight: 600;
                text-transform: uppercase;
                background: #fff;
                transition: all 0.2s ease;
            }
            .coupon-otp-wrapper .coupon-otp-slot:focus {
                outline: none;
                border-color: var(--brand-primary, #0d6efd);
                box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            }
            .coupon-otp-wrapper .coupon-otp-slot.filled {
                border-color: var(--brand-primary, #0d6efd);
                background: rgba(13, 110, 253, 0.05);
            }
            .coupon-otp-wrapper.code-otp-invalid .coupon-otp-slot {
                border-color: #dc3545;
                background: rgba(220, 53, 69, 0.05);
            }
            .coupon-otp-wrapper.code-otp-valid .coupon-otp-slot {
                border-color: #198754;
                background: rgba(25, 135, 84, 0.05);
            }
            /* Mobile Responsive Styles - Solo para mobile */
            @media (max-width: 768px) {
                /* ELIMINAR TEXTOS INNECESARIOS */
                .modal-body small.text-muted {
                    display: none !important;
                    /* Ocultar textos de ayuda en modales */
                }

                /* Card principal más compacta */
                .card.p-4 {
                    padding: 1rem !important;
                }

                .card .d-flex {
                    flex-direction: column;
                    gap: 0.75rem;
                }

                .card .btn {
                    width: 100%;
                    font-size: 0.85rem !important;
                    padding: 0.5rem !important;
                }

                /* Tabla más compacta */
                .table-responsive {
                    font-size: 0.8rem;
                }

                .table th,
                .table td {
                    padding: 0.5rem 0.4rem !important;
                    font-size: 0.8rem !important;
                }

                .table th {
                    font-size: 0.7rem !important;
                    white-space: nowrap;
                }

                .table .btn-sm {
                    font-size: 0.7rem !important;
                    padding: 0.25rem 0.4rem !important;
                }

                .table .badge {
                    font-size: 0.65rem !important;
                    padding: 0.25rem 0.5rem !important;
                }

                .table .dropdown-toggle {
                    font-size: 0.7rem !important;
                }

                /* Modales más compactos */
                .modal-dialog {
                    max-width: 95% !important;
                    margin: 0.5rem auto !important;
                }

                .modal-body {
                    padding: 1rem !important;
                }

                .modal-body .mb-3 {
                    margin-bottom: 0.75rem !important;
                }

                .modal-body .form-label {
                    font-size: 0.85rem !important;
                    margin-bottom: 0.4rem !important;
                }

                .modal-body .form-control,
                .modal-body .form-select {
                    font-size: 0.85rem !important;
                    padding: 0.5rem !important;
                }

                .modal-body .row .col-md-6 {
                    flex: 0 0 100% !important;
                    max-width: 100% !important;
                    margin-bottom: 0.75rem;
                }

                /* Ajustes generales */
                .main-content {
                    padding: 0.5rem !important;
                }

                .mb-4 {
                    margin-bottom: 1rem !important;
                }
            }

            /* LANDSCAPE MODE - Mobile horizontal - Coupons */
            @media (max-width: 1024px) and (orientation: landscape) {
                .main-content {
                    padding: 0.75rem !important;
                    max-width: 100vw !important;
                    overflow-x: hidden !important;
                }

                /* Tabla más legible */
                .table {
                    font-size: 0.85rem !important;
                }

                .table th,
                .table td {
                    padding: 0.5rem 0.4rem !important;
                    font-size: 0.85rem !important;
                }

                /* Modales más grandes */
                .modal-dialog {
                    max-width: 70% !important;
                }

                /* Asegurar que no haya overflow */
                * {
                    max-width: 100% !important;
                }

                .container-fluid,
                .row {
                    max-width: 100% !important;
                    overflow-x: hidden !important;
                }
            }
            /* Modal bulk delete con backdrop blur */
            #modalBulkDeleteConfirm.show ~ .modal-backdrop,
            body.modal-open .modal-backdrop {
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
            }
            #bulkActionBar {
                border-left: 4px solid #dc3545;
                background: #fff8e1;
            }
            .coupon-checkbox { cursor: pointer; }
            tr.table-active-selected { background: rgba(220, 53, 69, 0.06) !important; }
        </style>
    @endpush

    <script>
        // Formato para servidor: YYYY-MM-DDTHH:mm en hora LOCAL
        function toLocalDateTime(isoString) {
            if (!isoString) return '';
            var d = new Date(isoString);
            if (isNaN(d.getTime())) return '';
            var y = d.getFullYear();
            var m = String(d.getMonth() + 1).padStart(2, '0');
            var day = String(d.getDate()).padStart(2, '0');
            var h = String(d.getHours()).padStart(2, '0');
            var min = String(d.getMinutes()).padStart(2, '0');
            return y + '-' + m + '-' + day + 'T' + h + ':' + min;
        }
        // Formato para mostrar: DD/MM/AAAA HH:mm
        function toDDMMYYYYHHmm(isoString) {
            if (!isoString) return '';
            var d = new Date(isoString);
            if (isNaN(d.getTime())) return '';
            var day = String(d.getDate()).padStart(2, '0');
            var m = String(d.getMonth() + 1).padStart(2, '0');
            var y = d.getFullYear();
            var h = String(d.getHours()).padStart(2, '0');
            var min = String(d.getMinutes()).padStart(2, '0');
            return day + '/' + m + '/' + y + ' ' + h + ':' + min;
        }
        // Parsear DD/MM/AAAA HH:mm o D/M/AA H:m y devolver YYYY-MM-DDTHH:mm para el servidor
        function parseDDMMYYYYToServer(str) {
            if (!str || !str.trim()) return '';
            var s = str.trim();
            var parts = s.split(/\s+/);
            var datePart = parts[0];
            var timePart = parts[1] || '00:00';
            var dParts = datePart.split(/[\/\-\.]/);
            if (dParts.length !== 3) return '';
            var day = parseInt(dParts[0], 10);
            var month = parseInt(dParts[1], 10) - 1;
            var year = parseInt(dParts[2], 10);
            if (year < 100) year += 2000;
            var tParts = timePart.split(':');
            var hour = parseInt(tParts[0], 10) || 0;
            var min = parseInt(tParts[1], 10) || 0;
            var date = new Date(year, month, day, hour, min);
            if (isNaN(date.getTime())) return '';
            var y = date.getFullYear();
            var m = String(date.getMonth() + 1).padStart(2, '0');
            var d = String(date.getDate()).padStart(2, '0');
            var h = String(date.getHours()).padStart(2, '0');
            var mi = String(date.getMinutes()).padStart(2, '0');
            return y + '-' + m + '-' + d + 'T' + h + ':' + mi;
        }

        function editCoupon(id) {
            fetch(`/admin/coupons/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('editCouponForm').action = `/admin/coupons/${id}`;
                    var codeStr = (data.code || '').toUpperCase();
                    document.getElementById('edit_code').value = codeStr;
                    setAdminCodeSlots('edit_code_otp_container', codeStr);
                    updateAdminCodeOtpWrapperClass('edit_code_otp_container', 'edit_code');
                    document.getElementById('edit_name').value = data.name;
                    var pct = data.discount_percentage != null && data.discount_percentage !== '' ? parseFloat(data.discount_percentage) : '';
                    document.getElementById('edit_discount_percentage').value = pct !== '' ? Math.round(pct / 5) * 5 : '';
                    document.getElementById('edit_usage_limit').value = data.usage_limit || '';
                    document.getElementById('edit_allow_cash_discount').checked = !!data.allow_cash_discount;
                    var validFrom = toLocalDateTime(data.valid_from);
                    var validUntil = toLocalDateTime(data.valid_until);
                    document.getElementById('edit_valid_from').value = validFrom;
                    document.getElementById('edit_valid_until').value = validUntil;
                    document.getElementById('edit_valid_from_display').value = toDDMMYYYYHHmm(data.valid_from);
                    document.getElementById('edit_valid_until_display').value = toDDMMYYYYHHmm(data.valid_until);
                    document.getElementById('edit_description').value = data.description || '';
                    document.getElementById('edit_is_active').checked = data.is_active;
                    ['edit_code', 'edit_name', 'edit_discount_percentage'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el && typeof validateCouponField === 'function') validateCouponField(el);
                    });

                    new bootstrap.Modal(document.getElementById('modalEditCoupon')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar el cupón');
                });
        }

        // ========== OTP slots para código de cupón (como en checkout) ==========
        var adminOtpLength = 8;

        function initAdminCodeSlots(containerId, hiddenId) {
            var container = document.getElementById(containerId);
            var hidden = document.getElementById(hiddenId);
            if (!container || !hidden) return;
            container.innerHTML = '';
            for (var i = 0; i < adminOtpLength; i++) {
                var slot = document.createElement('input');
                slot.type = 'text';
                slot.className = 'coupon-otp-slot form-control';
                slot.maxLength = 1;
                slot.dataset.index = i;
                slot.setAttribute('autocomplete', 'off');
                slot.addEventListener('input', function() { handleAdminOTPInput(containerId, hiddenId); });
                slot.addEventListener('keydown', function(e) { handleAdminOTPKeydown(e, containerId, hiddenId); });
                slot.addEventListener('paste', function(e) { handleAdminOTPPaste(e, containerId, hiddenId); });
                container.appendChild(slot);
            }
            var initialCode = (hidden.value || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, adminOtpLength);
            if (initialCode.length === adminOtpLength) {
                setAdminCodeSlots(containerId, initialCode);
                hidden.value = initialCode;
            }
            updateAdminCodeOtpWrapperClass(containerId, hiddenId);
        }

        function setAdminCodeSlots(containerId, code) {
            var container = document.getElementById(containerId);
            if (!container) return;
            var slots = container.querySelectorAll('.coupon-otp-slot');
            var str = (code || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, adminOtpLength);
            for (var i = 0; i < slots.length; i++) {
                slots[i].value = str[i] || '';
                slots[i].classList.toggle('filled', !!(str[i]));
            }
        }

        function getAdminCodeFromSlots(containerId) {
            var container = document.getElementById(containerId);
            if (!container) return '';
            var slots = container.querySelectorAll('.coupon-otp-slot');
            return Array.from(slots).map(function(s) { return s.value; }).join('');
        }

        function handleAdminOTPInput(containerId, hiddenId) {
            var container = document.getElementById(containerId);
            var hidden = document.getElementById(hiddenId);
            var slot = container.querySelector(':focus') || container.querySelector('.coupon-otp-slot');
            if (!slot || !hidden) return;
            var value = slot.value;
            if (value && !/^[A-Z0-9]$/i.test(value)) {
                slot.value = '';
                value = '';
            } else if (value) {
                slot.value = value.toUpperCase();
                var index = parseInt(slot.dataset.index, 10);
                if (index < adminOtpLength - 1) {
                    var next = container.querySelector('[data-index="' + (index + 1) + '"]');
                    if (next) next.focus();
                }
            }
            slot.classList.toggle('filled', !!slot.value);
            hidden.value = getAdminCodeFromSlots(containerId);
            updateAdminCodeOtpWrapperClass(containerId, hiddenId);
            var hiddenEl = document.getElementById(hiddenId);
            if (hiddenEl && typeof validateCouponField === 'function') validateCouponField(hiddenEl, false);
        }

        function handleAdminOTPKeydown(e, containerId, hiddenId) {
            var container = document.getElementById(containerId);
            var slot = e.target;
            var index = parseInt(slot.dataset.index, 10);
            if (e.key === 'Backspace' && !slot.value && index > 0) {
                e.preventDefault();
                var prev = container.querySelector('[data-index="' + (index - 1) + '"]');
                if (prev) { prev.value = ''; prev.classList.remove('filled'); prev.focus(); }
            } else if (e.key === 'ArrowLeft' && index > 0) {
                container.querySelector('[data-index="' + (index - 1) + '"]').focus();
            } else if (e.key === 'ArrowRight' && index < adminOtpLength - 1) {
                container.querySelector('[data-index="' + (index + 1) + '"]').focus();
            }
            if (e.key === 'Backspace' || e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                setTimeout(function() {
                    var hidden = document.getElementById(hiddenId);
                    if (hidden) hidden.value = getAdminCodeFromSlots(containerId);
                    updateAdminCodeOtpWrapperClass(containerId, hiddenId);
                }, 0);
            }
        }

        function handleAdminOTPPaste(e, containerId, hiddenId) {
            e.preventDefault();
            var container = document.getElementById(containerId);
            var hidden = document.getElementById(hiddenId);
            var pasted = (e.clipboardData.getData('text') || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, adminOtpLength);
            var slots = container.querySelectorAll('.coupon-otp-slot');
            var startIndex = parseInt(e.target.dataset.index, 10);
            for (var i = 0; i < pasted.length && (startIndex + i) < adminOtpLength; i++) {
                var s = slots[startIndex + i];
                s.value = pasted[i];
                s.classList.add('filled');
            }
            var nextIdx = Math.min(startIndex + pasted.length, adminOtpLength - 1);
            slots[nextIdx].focus();
            hidden.value = getAdminCodeFromSlots(containerId);
            updateAdminCodeOtpWrapperClass(containerId, hiddenId);
            if (typeof validateCouponField === 'function') validateCouponField(hidden, false);
        }

        function updateAdminCodeOtpWrapperClass(containerId, hiddenId) {
            var container = document.getElementById(containerId);
            var hidden = document.getElementById(hiddenId);
            if (!container || !hidden) return;
            var code = getAdminCodeFromSlots(containerId);
            container.classList.remove('code-otp-invalid', 'code-otp-valid');
            if (code.length === adminOtpLength && /^[A-Z0-9]{8}$/.test(code)) {
                container.classList.add('code-otp-valid');
            } else if (code.length > 0 && (code.length !== adminOtpLength || !/^[A-Z0-9]{8}$/.test(code))) {
                container.classList.add('code-otp-invalid');
            }
        }

        // Validación en vivo: válido = verde, inválido = rojo (strict = true en blur para marcar vacío como inválido)
        function validateCouponField(input, strict) {
            var id = input.id || input.name;
            var val = (input.value || '').trim();
            var valid = false;
            if (id === 'create_code' || id === 'edit_code') {
                var code = val.toUpperCase();
                valid = /^[A-Z0-9]{8}$/.test(code);
            } else if (id === 'create_name' || id === 'edit_name') {
                valid = val.length >= 1 && val.length <= 255;
            } else if (id === 'create_discount_percentage' || id === 'edit_discount_percentage') {
                if (val === '') valid = false;
                else {
                    var n = parseFloat(val);
                    valid = !isNaN(n) && n >= 0 && n <= 100;
                }
            } else return;
            input.classList.remove('is-valid', 'is-invalid');
            if (valid) {
                input.classList.add('is-valid');
            } else {
                if (strict || val.length > 0) input.classList.add('is-invalid');
            }
        }

        function setupLiveValidation(modalEl) {
            var ids = ['create_code', 'create_name', 'create_discount_percentage', 'edit_code', 'edit_name', 'edit_discount_percentage'];
            ids.forEach(function(id) {
                var input = modalEl.querySelector('#' + id);
                if (!input) return;
                input.addEventListener('input', function() { validateCouponField(input, false); });
                input.addEventListener('blur', function() { validateCouponField(input, true); });
            });
        }

        // Evitar que Enter en un input envíe el formulario (evita recarga al terminar de escribir)
        function preventEnterSubmit(formEl) {
            if (!formEl) return;
            formEl.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') && e.target.type !== 'submit') {
                    e.preventDefault();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initAdminCodeSlots('create_code_otp_container', 'create_code');
            initAdminCodeSlots('edit_code_otp_container', 'edit_code');
            setupLiveValidation(document.getElementById('modalCreateCoupon'));
            setupLiveValidation(document.getElementById('modalEditCoupon'));
            preventEnterSubmit(document.querySelector('#modalCreateCoupon form'));
            preventEnterSubmit(document.getElementById('editCouponForm'));
            document.querySelector('#modalCreateCoupon form').addEventListener('submit', function() {
                document.getElementById('create_code').value = getAdminCodeFromSlots('create_code_otp_container');
            });
            document.getElementById('editCouponForm').addEventListener('submit', function() {
                document.getElementById('edit_code').value = getAdminCodeFromSlots('edit_code_otp_container');
            });

            var datePairs = [
                { display: 'create_valid_from_display', hidden: 'create_valid_from' },
                { display: 'create_valid_until_display', hidden: 'create_valid_until' },
                { display: 'edit_valid_from_display', hidden: 'edit_valid_from' },
                { display: 'edit_valid_until_display', hidden: 'edit_valid_until' }
            ];
            datePairs.forEach(function(pair) {
                var disp = document.getElementById(pair.display);
                var hid = document.getElementById(pair.hidden);
                if (disp && hid) {
                    function sync() {
                        var serverVal = parseDDMMYYYYToServer(disp.value);
                        hid.value = serverVal;
                    }
                    disp.addEventListener('input', sync);
                    disp.addEventListener('blur', sync);
                }
            });
        });

        // Si hay errores de validación, abrir el modal y enfocar el primer campo con error
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                var openEdit = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                var modalId = openEdit ? 'modalEditCoupon' : 'modalCreateCoupon';
                var modalEl = document.getElementById(modalId);
                var modal = new bootstrap.Modal(modalEl);
                modalEl.addEventListener('shown.bs.modal', function focusFirstInvalid() {
                    modalEl.removeEventListener('shown.bs.modal', focusFirstInvalid);
                    var firstInvalid = modalEl.querySelector('.form-control.is-invalid');
                    var codeOtpInvalid = modalEl.querySelector('.coupon-otp-wrapper.code-otp-invalid');
                    if (codeOtpInvalid) {
                        var firstSlot = codeOtpInvalid.querySelector('.coupon-otp-slot');
                        if (firstSlot) { firstSlot.focus(); firstSlot.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); return; }
                    }
                    if (firstInvalid) {
                        if (firstInvalid.id === 'create_code' || firstInvalid.id === 'edit_code') {
                            var containerId = firstInvalid.id === 'create_code' ? 'create_code_otp_container' : 'edit_code_otp_container';
                            var firstSlot = document.querySelector('#' + containerId + ' .coupon-otp-slot');
                            if (firstSlot) { firstSlot.focus(); firstSlot.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
                        } else {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }
                }, { once: true });
                modal.show();
            @endif
        });
    </script>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var datePairs = [
                { display: 'create_valid_from_display', hidden: 'create_valid_from' },
                { display: 'create_valid_until_display', hidden: 'create_valid_until' },
                { display: 'edit_valid_from_display', hidden: 'edit_valid_from' },
                { display: 'edit_valid_until_display', hidden: 'edit_valid_until' }
            ];
            if (window.flatpickr && typeof parseDDMMYYYYToServer === 'function') {
                datePairs.forEach(function(pair) {
                    var disp = document.getElementById(pair.display);
                    var hid = document.getElementById(pair.hidden);
                    if (disp && hid) {
                        flatpickr(disp, {
                            enableTime: true,
                            dateFormat: 'd/m/Y H:i',
                            allowInput: true,
                            locale: { firstDayOfWeek: 1 },
                            onChange: function(sel, datestr) {
                                hid.value = parseDDMMYYYYToServer(datestr);
                            }
                        });
                    }
                });
            }
        });
    </script>

    <script>
        (function () {
            var selectAll   = document.getElementById('selectAllCoupons');
            var bulkBar     = document.getElementById('bulkActionBar');
            var bulkCount   = document.getElementById('bulkSelectedCount');
            var bulkDelBtn  = document.getElementById('bulkDeleteBtn');
            var bulkCancel  = document.getElementById('bulkCancelBtn');

            function getChecked() {
                return Array.from(document.querySelectorAll('.coupon-checkbox:checked'));
            }

            function updateBar() {
                var checked = getChecked();
                var n = checked.length;
                bulkCount.textContent = n;
                if (n > 0) {
                    bulkBar.classList.remove('d-none');
                    bulkBar.classList.add('d-flex');
                } else {
                    bulkBar.classList.add('d-none');
                    bulkBar.classList.remove('d-flex');
                }
                document.querySelectorAll('.coupon-checkbox').forEach(function(cb) {
                    cb.closest('tr').classList.toggle('table-active-selected', cb.checked);
                });
                selectAll.indeterminate = n > 0 && n < document.querySelectorAll('.coupon-checkbox').length;
                selectAll.checked = n > 0 && n === document.querySelectorAll('.coupon-checkbox').length;
            }

            document.querySelectorAll('.coupon-checkbox').forEach(function(cb) {
                cb.addEventListener('change', updateBar);
            });

            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.coupon-checkbox').forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
                updateBar();
            });

            bulkCancel.addEventListener('click', function() {
                document.querySelectorAll('.coupon-checkbox').forEach(function(cb) { cb.checked = false; });
                selectAll.checked = false;
                updateBar();
            });

            bulkDelBtn.addEventListener('click', function() {
                var checked = getChecked();
                if (!checked.length) return;
                document.getElementById('bulkDeleteCountText').textContent = checked.length;
                var container = document.getElementById('bulkDeleteInputs');
                container.innerHTML = '';
                checked.forEach(function(cb) {
                    var inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'ids[]';
                    inp.value = cb.value;
                    container.appendChild(inp);
                });
                new bootstrap.Modal(document.getElementById('modalBulkDeleteConfirm')).show();
            });
        })();
    </script>
    @endpush
@endsection
