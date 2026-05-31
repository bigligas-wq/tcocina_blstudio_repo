@extends('layouts.admin')

@section('title', 'Album de Tcocina - Admin')

@section('content')
    <div class="container-fluid py-3">
        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Album de Tcocina</h5>
                    </div>
                    <div class="card-body">
                        <form class="loyalty-settings-form" method="POST" action="{{ route('admin.loyalty.settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Objetivo de Combos</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 " style="color: #1e3a8a;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Cantidad de figuritas que el cliente necesita juntar para canjear el premio del día. Por ejemplo: si configurás 10, el usuario necesita 10 figuritas para reclamar el premio.">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <input type="number" min="1" max="500" class="form-control" name="target_stickers"
                                    value="{{ old('target_stickers', $setting->target_stickers) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Titulo del premio vigente</label>
                                <input type="text" class="form-control" name="reward_value"
                                    value="{{ old('reward_value', $setting->reward_value) }}" required>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Descripcion del premio</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 " style="color: #1e3a8a;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Descripción detallada del premio que se muestra en el álbum del cliente. Ejemplo: 'Hoy el premio vigente es un Combo Rayito con papas y dip incluido'.">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <textarea class="form-control" name="reward_description" rows="3"
                                    placeholder="Ej: Hoy el premio vigente es un Combo Rayito con papas y dip.">{{ old('reward_description', $setting->reward_description) }}</textarea>
                            </div>
                            @php
                                $defaultAlbumHelpMessage = 'Con {target_stickers} figuritas canjeás el premio del día (ver arriba en esta vista). Retiro en local o envío a tu cargo.';
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Mensaje del modal "Como funciona el album"</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 " style="color: #1e3a8a;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Texto explicativo que aparece cuando el cliente hace clic en '¿Cómo funciona?'. Usá {target_stickers} para que se reemplace automáticamente con la cantidad configurada. Ejemplo: 'Con {target_stickers} figuritas canjeás el premio del día. Retiro en local o envío a tu cargo.'">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <textarea class="form-control" name="album_help_message" rows="4"
                                    placeholder="Ej: Con {target_stickers} figuritas canjeas el premio del dia.">{{ old('album_help_message', $setting->album_help_message ?: $defaultAlbumHelpMessage) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Instrucciones de canje</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 " style="color: #1e3a8a;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Este texto se incluye en el email que recibe el cliente cuando apruebas su canje. Podés poner códigos de cupón, instrucciones de retiro, dirección del local, horarios de atención, etc. Ejemplo: 'Mostrá este mail en el local para retirar tu premio. Si es un cupón de descuento, usá el código CUPON2024 al finalizar tu compra.'">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <textarea class="form-control" name="redemption_instructions" rows="5"
                                    placeholder="Ej: Mostrá este mail en el local para retirar tu premio. Si es un cupón de descuento, usá el código CUPON2024 al finalizar tu compra.">{{ old('redemption_instructions', $setting->redemption_instructions) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Tipo de premio</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 " style="color: #1e3a8a;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Determina cómo se muestra al cliente el premio: Cupón de descuento muestra el código con botón para copiar, Premio físico muestra botón de contacto por WhatsApp para coordinar retiro, Otro/Personalizado permite mensaje libre.">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <select class="form-select" name="reward_category" id="rewardCategory" required>
                                    <option value="coupon" {{ old('reward_category', $setting->reward_category) === 'coupon' ? 'selected' : '' }}>
                                        Cupón de descuento
                                    </option>
                                    <option value="physical" {{ old('reward_category', $setting->reward_category) === 'physical' ? 'selected' : '' }}>
                                        Premio físico (retiro)
                                    </option>
                                    <option value="other" {{ old('reward_category', $setting->reward_category) === 'other' ? 'selected' : '' }}>
                                        Otro / personalizado
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3" id="couponCodeField">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Código de cupón</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 " style="color: #1e3a8a;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Código que se mostrará al cliente en su dashboard con botón para copiar. El cliente deberá usar este código al momento de pagar. Ejemplos: CANJE15, VERANO2024, PROMO25.">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <input type="text" class="form-control" name="coupon_code"
                                    value="{{ old('coupon_code', $setting->coupon_code) }}"
                                    placeholder="Ej: CANJE15, VERANO2024, etc.">
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Imagen del premio</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2 " style="color: #1e3a8a;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Imagen que se muestra en el álbum del cliente para ilustrar el premio del día. Si no subes una nueva imagen, se conserva la actual. Formatos permitidos: JPG, JPEG, PNG, WEBP.">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <input type="file" class="form-control" name="reward_image" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                            @if (!empty($setting->reward_image))
                                <div class="mb-3" style="background:#0f172a;border-radius:10px;padding:8px;text-align:center;">
                                    <img src="{{ asset('images/' . $setting->reward_image) }}" alt="Premio actual"
                                        style="width:100%;max-height:220px;object-fit:contain;border-radius:6px;"
                                        onerror="this.onerror=null; this.src='{{ asset('images/log.png') }}';">
                                </div>
                            @endif
                            <button id="floatingLoyaltySaveBtn" class="btn btn-primary btn-lg loyalty-save-floating" type="submit">
                                <i class="fas fa-save me-2"></i>Guardar cambios
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Canjes pendientes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Soles</th>
                                        <th>Premio</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingRedemptions as $redemption)
                                        <tr>
                                            <td>{{ $redemption->user->name }}</td>
                                            <td>{{ $redemption->stickers_spent }}</td>
                                            <td>{{ $redemption->reward_snapshot['reward_value'] ?? '-' }}</td>
                                            <td><span class="badge text-bg-secondary">{{ $redemption->status }}</span></td>
                                            <td class="text-end">
                                                @if ($redemption->status === 'pending')
                                                    <form action="{{ route('admin.loyalty.redemptions.approve', $redemption->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-primary">Aprobar</button>
                                                    </form>
                                                @endif
                                                @if (in_array($redemption->status, ['pending', 'approved'], true))
                                                    <form action="{{ route('admin.loyalty.redemptions.deliver', $redemption->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success">Entregar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No hay canjes para revisar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Movimientos recientes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Motivo</th>
                                        <th>Delta</th>
                                        <th>Pedido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMovements as $movement)
                                        <tr>
                                            <td>
                                                @if($movement->user && $movement->user->email === 'guest@tecocina.local' && $movement->order?->contact_name)
                                                    {{ $movement->order->contact_name }}
                                                @else
                                                    {{ $movement->user->name ?? 'Invitado' }}
                                                @endif
                                            </td>
                                            <td>{{ $movement->reason }}</td>
                                            <td class="{{ $movement->delta >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $movement->delta >= 0 ? '+' : '' }}{{ $movement->delta }}
                                            </td>
                                            <td>{{ $movement->order->order_number ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Sin movimientos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            const rewardCategory = document.getElementById('rewardCategory');
            const couponCodeField = document.getElementById('couponCodeField');

            function toggleCouponField() {
                if (rewardCategory.value === 'coupon') {
                    couponCodeField.style.display = 'block';
                } else {
                    couponCodeField.style.display = 'none';
                }
            }

            rewardCategory.addEventListener('change', toggleCouponField);
            toggleCouponField(); // Ejecutar al cargar
        });
    </script>
@endpush

@push('styles')
    <style>
        #floatingLoyaltySaveBtn.loyalty-save-floating {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1055;
            border-radius: 999px;
            padding: 10px 18px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.15);
        }

        .loyalty-settings-form {
            padding-bottom: 96px;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 767.98px) {
            /* Container compacto */
            .container-fluid.py-3 {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }
            
            /* Cards más compactas */
            .card {
                margin-bottom: 0.75rem !important;
            }
            
            .card-header {
                padding: 0.75rem !important;
            }
            
            .card-header h5 {
                font-size: 0.95rem;
                margin-bottom: 0 !important;
            }
            
            .card-body {
                padding: 0.75rem !important;
            }
            
            /* Formularios compactos */
            .loyalty-settings-form .mb-3 {
                margin-bottom: 0.75rem !important;
            }
            
            .form-label {
                font-size: 0.8rem;
                margin-bottom: 0.25rem;
            }
            
            .form-control, .form-select {
                padding: 0.375rem 0.5rem;
                font-size: 0.9rem;
            }
            
            textarea.form-control {
                rows: 2;
                min-height: 60px;
            }
            
            /* Help text más pequeño */
            .form-text, .text-muted.small {
                font-size: 0.7rem;
                line-height: 1.3;
            }
            
            /* Tablas simplificadas */
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
            
            /* Botones de tabla más pequeños */
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            /* Badges compactos */
            .badge {
                font-size: 0.7rem;
                padding: 0.25em 0.4em;
            }
            
            /* Imagen del premio más pequeña */
            img[alt="Premio actual"] {
                max-height: 120px !important;
            }
            
            /* Grid en mobile: apilar columnas */
            .row.g-3 > .col-12.col-lg-5,
            .row.g-3 > .col-12.col-lg-7 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            /* Reduce gap entre cards */
            .row.g-3 {
                --bs-gutter-y: 0.75rem;
                --bs-gutter-x: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            #floatingLoyaltySaveBtn.loyalty-save-floating {
                right: 16px;
                bottom: 16px;
                padding: 8px 14px;
                font-size: 0.85rem;
            }

            .loyalty-settings-form {
                padding-bottom: 80px;
            }
            
            /* Tablas aún más compactas en pantallas muy pequeñas */
            .table-responsive table {
                font-size: 0.75rem;
            }
            
            /* Ocultar columnas menos importantes */
            .table-responsive thead th:nth-child(3),  /* Premio */
            .table-responsive tbody td:nth-child(3) {
                display: none;
            }
            
            /* FORMULARIO SUPER COMPACTO EN MOBILE */
            /* Textareas sin scroll - muestran todo el contenido */
            .loyalty-settings-form textarea.form-control {
                min-height: 80px;
                height: auto;
                overflow: hidden;
                resize: none;
            }
            
            /* Labels más compactos y prominentes */
            .loyalty-settings-form .form-label {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
                font-weight: 700;
                color: #1f2937;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: calc(100% - 30px);
                display: inline-block;
                letter-spacing: -0.01em;
            }
            
            /* Labels específicos más cortos en mobile */
            .loyalty-settings-form label[for="album_help_message"],
            .loyalty-settings-form label:contains("Mensaje del modal") {
                font-size: 0.75rem;
            }
            
            /* Reducir espaciado entre campos */
            .loyalty-settings-form .mb-3 {
                margin-bottom: 0.5rem !important;
            }
            
            /* Inputs más compactos */
            .loyalty-settings-form .form-control,
            .loyalty-settings-form .form-select {
                padding: 0.375rem 0.5rem;
                font-size: 0.85rem;
                min-height: 36px;
            }
            
            /* Iconos de info más pequeños */
            .loyalty-settings-form .btn-link[style*="color: #1e3a8a"] {
                font-size: 0.85rem;
            }
            
            /* Card body con menos padding */
            .card:has(.loyalty-settings-form) .card-body {
                padding: 0.75rem !important;
            }
            
            /* Input file más compacto */
            .loyalty-settings-form input[type="file"].form-control {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
            
            /* Imagen preview más pequeña */
            .loyalty-settings-form img {
                max-height: 120px !important;
            }
        }
        
        /* Tablet adjustments */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .card-body {
                padding: 1rem !important;
            }
            
            .form-control, .form-select {
                font-size: 0.95rem;
            }
        }
        
        /* Tooltips anchos para textos largos */
        .tooltip-wide {
            max-width: 350px !important;
        }
        
        .tooltip-wide .tooltip-inner {
            max-width: 350px !important;
            text-align: left;
            padding: 0.75rem;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        
        /* Botón info más visible en mobile */
        @media (max-width: 767.98px) {
            .btn-link[style*="color: #1e3a8a"] {
                font-size: 1rem;
            }
            
            .tooltip-wide .tooltip-inner {
                max-width: 280px !important;
                font-size: 0.8rem;
            }
        }
    </style>
@endpush
