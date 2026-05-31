@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <form id="settingsForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="site_offline_title_hidden" name="site_offline_title"
                value="{{ old('site_offline_title', optional($settings['site_offline_title'] ?? null)->value ?? 'T cocina') }}">
            <textarea id="site_offline_message_hidden" name="site_offline_message" class="d-none">{{ old('site_offline_message', optional($settings['site_offline_message'] ?? null)->value ?? 'Por el momento no estamos tomando pedidos.') }}</textarea>
            <textarea id="loyalty_offline_message_hidden" name="loyalty_offline_message" class="d-none">{{ old('loyalty_offline_message', optional($settings['loyalty_offline_message'] ?? null)->value ?? 'Por el momento Mi álbum no está disponible. Intenta nuevamente más tarde.') }}</textarea>

            <div class="card mb-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:52px;height:52px;border-radius:14px;background:#0ea5e9;display:flex;align-items:center;justify-content:center;box-shadow:0 0 18px rgba(14,165,233,.35)">
                            <i class="fas fa-power-off" style="color:#081420;font-size:22px"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Abri o cerra la web para tomar pedidos</div>
                        </div>
                    </div>
                    <div>
                        <button type="button" id="siteToggle" data-off="{{ ($businessSettings['site_offline'] ?? false) ? '1' : '0' }}" class="btn" style="border-radius:999px;padding:8px 18px;background: {{ ($businessSettings['site_offline'] ?? false) ? '#111827' : '#10b981' }}; color:#fff; display:flex; align-items:center; gap:10px">
                            <span class="badge {{ ($businessSettings['site_offline'] ?? false) ? 'bg-danger' : 'bg-success' }}" style="min-width:46px">{{ ($businessSettings['site_offline'] ?? false) ? 'OFF' : 'ON' }}</span>
                            <span class="siteToggleLabel">{{ ($businessSettings['site_offline'] ?? false) ? 'Sitio apagado' : 'Sitio encendido' }}</span>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-2">
                        <div class="col-12">
                            <label for="site_offline_title" class="form-label mb-1">Título cuando el sitio está apagado</label>
                            <input type="text" class="form-control" id="site_offline_title"
                                value="{{ old('site_offline_title', optional($settings['site_offline_title'] ?? null)->value ?? 'T cocina') }}"
                                placeholder="Ej: T cocina">
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between">
                                <label for="site_offline_message" class="form-label mb-1">Mensaje</label>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Este texto se muestra en el cartel público cuando el sitio está en modo OFF. Los clientes verán este mensaje cuando intenten acceder a la web estando apagada." style="color: #4e8df5;">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                            <textarea class="form-control" id="site_offline_message" rows="2"
                                placeholder="Mensaje visible para clientes cuando el sitio está apagado">{{ old('site_offline_message', optional($settings['site_offline_message'] ?? null)->value ?? 'Por el momento no estamos tomando pedidos.') }}</textarea>
                        </div>
                    </div>
                    <div class="row g-2 mt-3 pt-3 border-top">
                        <div class="col-12 mb-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center">
                                    <label class="form-label mb-0">Sistema Mi álbum (soles/cupones)</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Si está en OFF, los clientes no podrán loguearse con Google. En caso de que ya estén logueados, verán el mensaje configurado acá debajo indicando que el sistema de figuritas está temporalmente desactivado." style="color: #4e8df5;">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                <button type="button" id="loyaltyToggle" data-off="{{ ($businessSettings['loyalty_offline'] ?? false) ? '1' : '0' }}" class="btn" style="border-radius:999px;padding:8px 18px;background: {{ ($businessSettings['loyalty_offline'] ?? false) ? '#111827' : '#10b981' }}; color:#fff; display:flex; align-items:center; gap:10px">
                                    <span class="badge {{ ($businessSettings['loyalty_offline'] ?? false) ? 'bg-danger' : 'bg-success' }}" style="min-width:46px">{{ ($businessSettings['loyalty_offline'] ?? false) ? 'OFF' : 'ON' }}</span>
                                    <span class="loyaltyToggleLabel">{{ ($businessSettings['loyalty_offline'] ?? false) ? 'Mi álbum apagado' : 'Mi álbum encendido' }}</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="loyalty_offline_message" class="form-label mb-1">Mensaje cuando Mi álbum está apagado</label>
                            <textarea class="form-control" id="loyalty_offline_message" rows="2"
                                placeholder="Mensaje visible para clientes en Mi álbum cuando está apagado">{{ old('loyalty_offline_message', optional($settings['loyalty_offline_message'] ?? null)->value ?? 'Por el momento Mi álbum no está disponible. Intenta nuevamente más tarde.') }}</textarea>
                        </div>
                    </div>

                    <div class="row g-2 mt-3 pt-3 border-top">
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-2">
                                <label class="form-label mb-0">Días en que se toman pedidos</label>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="Marcá los días en que el negocio acepta pedidos. Si un día no está marcado, los clientes verán 'Hoy no estamos tomando pedidos' en el carrito y checkout. Esto permite controlar automáticamente la disponibilidad de la tienda según tu calendario laboral." style="color: #4e8df5;">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($weeklyConfigs as $config)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="order_days[]"
                                            value="{{ $config->day_of_week }}" id="order_day_{{ $config->day_of_week }}"
                                            {{ $config->is_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="order_day_{{ $config->day_of_week }}">
                                            {{ $config->day_name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-3 pb-3" style="border-bottom: 2px solid #0ea5e9;">
                        <div style="width:42px;height:42px;border-radius:12px;background:#0ea5e9;display:flex;align-items:center;justify-content:center;box-shadow:0 0 14px rgba(14,165,233,.3)">
                            <i class="fas fa-store" style="color:#081420;font-size:18px"></i>
                        </div>
                        <h2 class="mb-0 fw-bold" style="font-size:1.4rem;">Configuración del negocio</h2>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="section-general" class="row g-3 settings-section">
                        <div class="col-12">
                            <h5 class="settings-section-title">Información general</h5>
                        </div>
                        <div class="col-12">
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#generalInfoCollapse" aria-expanded="false"
                                        aria-controls="generalInfoCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Información general
                                    </button>
                                </div>
                                <div id="generalInfoCollapse" class="collapse">
                                    <div class="card-body row g-3">
                                        <div class="col-md-6">
                                            <label for="business_name" class="form-label">Nombre del negocio</label>
                                            <input type="text" class="form-control" id="business_name" name="business_name"
                                                value="{{ old('business_name', optional($settings['business_name'] ?? null)->value) }}"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="business_phone" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="business_phone" name="business_phone"
                                                value="{{ old('business_phone', optional($settings['business_phone'] ?? null)->value) }}"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="business_email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="business_email" name="business_email"
                                                value="{{ old('business_email', optional($settings['business_email'] ?? null)->value) }}"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="business_address" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="business_address" name="business_address"
                                                value="{{ old('business_address', optional($settings['business_address'] ?? null)->value) }}"
                                                required>
                                        </div>

                                        <div class="col-12">
                                            <label for="footer_description" class="form-label">Descripción del Footer</label>
                                            <textarea class="form-control" id="footer_description" name="footer_description" rows="3"
                                                placeholder="Descripción que aparecerá en el footer del sitio">{{ old('footer_description', optional($settings['footer_description'] ?? null)->value) }}</textarea>
                                            <div class="form-text">Esta descripción aparecerá debajo del logo en el footer del sitio web.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- WhatsApp Configuration -->
                    <div id="section-whatsapp" class="row g-3 mt-3 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Configuración de WhatsApp</h5>
                        </div>
                        <div class="col-md-6">
                            <label for="whatsapp_number" class="form-label">Número de WhatsApp</label>
                            <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number"
                                value="{{ old('whatsapp_number', optional($settings['whatsapp_number'] ?? null)->value) }}"
                                placeholder="5492494015745" required>
                            <small class="form-text text-muted">Formato: código país + número sin espacios ni
                                símbolos</small>
                        </div>
                    </div>



                    <!-- Payment Methods -->
                    <div id="section-payment" class="row g-3 mt-3 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Métodos de Pago Habilitados</h5>
                        </div>
                        @php
                            // Obtener el valor de payment_methods y decodificarlo según su tipo
                            $paymentMethodSetting = $settings['payment_methods'] ?? null;
                            
                            // Log para debug
                            \Log::info('settings.blade.php - Procesando payment_methods', [
                                'paymentMethodSetting_exists' => !is_null($paymentMethodSetting),
                                'paymentMethodSetting_type' => $paymentMethodSetting ? get_class($paymentMethodSetting) : null,
                                'paymentMethodSetting_value_raw' => $paymentMethodSetting ? $paymentMethodSetting->value : null,
                                'paymentMethodSetting_type_field' => $paymentMethodSetting ? $paymentMethodSetting->type : null,
                            ]);
                            
                            if ($paymentMethodSetting) {
                                // Usar castValue para decodificar correctamente según el tipo (JSON, string, etc.)
                                $paymentMethods = \App\Models\BusinessSetting::castValue(
                                    $paymentMethodSetting->value,
                                    $paymentMethodSetting->type
                                );
                                
                                // Si después de decodificar sigue siendo un string (doble encoding), decodificar nuevamente
                                if (is_string($paymentMethods)) {
                                    $decoded = json_decode($paymentMethods, true);
                                    if (is_array($decoded)) {
                                        $paymentMethods = $decoded;
                                    }
                                }
                                
                                \Log::info('settings.blade.php - payment_methods decodificado', [
                                    'paymentMethods' => $paymentMethods,
                                    'is_array' => is_array($paymentMethods),
                                    'count' => is_array($paymentMethods) ? count($paymentMethods) : 0,
                                ]);
                            } else {
                                $paymentMethods = [];
                                \Log::warning('settings.blade.php - paymentMethodSetting es null, usando array vacío');
                            }
                            // Asegurar que sea un array
                            $paymentMethods = is_array($paymentMethods) ? $paymentMethods : [];
                            
                            \Log::info('settings.blade.php - paymentMethods final', [
                                'paymentMethods' => $paymentMethods,
                                'cash_in_array' => in_array('cash', $paymentMethods),
                                'card_in_array' => in_array('card', $paymentMethods),
                                'transfer_in_array' => in_array('transfer', $paymentMethods),
                            ]);

                            $availableMethods = [
                                'cash' => 'Efectivo',
                                'card' => 'Tarjeta débito/crédito',
                                'transfer' => 'Transferencia (Mercado Pago, Ualá, Brubank)',
                            ];
                        @endphp
                        @foreach ($availableMethods as $methodKey => $methodName)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="payment_methods[]"
                                        value="{{ $methodKey }}" id="payment_{{ $methodKey }}"
                                        {{ in_array($methodKey, $paymentMethods) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payment_{{ $methodKey }}">
                                        {{ $methodName }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Descuento por Pago en Efectivo -->
                    <div id="section-cash-discount" class="row g-3 mt-3 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Descuento por Pago en Efectivo</h5>
                        </div>
                        <div class="col-md-6">
                            <label for="cash_discount_percentage" class="form-label">Porcentaje de Descuento (%)</label>
                            <input type="number" class="form-control" id="cash_discount_percentage"
                                name="cash_discount_percentage" min="0" max="100" step="0.01"
                                value="{{ old('cash_discount_percentage', optional($settings['cash_discount_percentage'] ?? null)->value ?? 0) }}"
                                placeholder="0">
                            <div class="form-text">Porcentaje de descuento que se aplicará automáticamente cuando el
                                cliente seleccione pago en efectivo.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Información:</strong> Este descuento se aplicará automáticamente en el checkout
                                cuando el cliente seleccione "Pago en efectivo".
                            </div>
                        </div>
                    </div>

                    <!-- Marca: Logo y Colores -->
                    <div id="section-brand" class="row g-3 mt-4 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Marca</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo central del header</label>
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <input id="brand_logo" type="file" class="form-control" name="brand_logo" accept="image/*">
                                    <div class="form-text mt-1">Logo que aparece en el centro del header. Formatos: PNG o SVG. Tamaño sugerido: 256x256.</div>
                                </div>
                                <div class="flex-shrink-0">
                                    @php 
                                        $brandLogo = optional($settings['brand_logo_url'] ?? null)->value;
                                        // Normalizar la URL para que funcione tanto con rutas antiguas como nuevas
                                        if ($brandLogo) {
                                            if (str_starts_with($brandLogo, '/storage/') || str_starts_with($brandLogo, '/branding/') || str_starts_with($brandLogo, '/images/')) {
                                                $brandLogo = asset($brandLogo);
                                            } else {
                                                $brandLogo = asset($brandLogo);
                                            }
                                        } else {
                                            $brandLogo = asset('images/log.png');
                                        }
                                    @endphp
                                    <img id="brand_logo_preview" src="{{ $brandLogo }}" alt="Logo central"
                                         class="logo-preview-wrap"
                                         style="height:80px; width:80px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 4px; padding: 4px; background: #f8f9fa;"
                                         onerror="this.src='{{ asset('images/log.png') }}';">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo izquierdo del header</label>
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <input id="brand_logo_left" type="file" class="form-control" name="brand_logo_left" accept="image/*">
                                    <div class="form-text mt-1">Logo que aparece a la izquierda del header. Formatos: PNG o SVG. Tamaño sugerido: 200x40.</div>
                                </div>
                                <div class="flex-shrink-0">
                                    @php
                                        $brandLogoLeft = optional($settings['brand_logo_left_url'] ?? null)->value;
                                        // Normalizar la URL para que funcione tanto con rutas antiguas como nuevas
                                        if ($brandLogoLeft) {
                                            if (str_starts_with($brandLogoLeft, '/storage/') || str_starts_with($brandLogoLeft, '/branding/') || str_starts_with($brandLogoLeft, '/images/')) {
                                                $brandLogoLeft = asset($brandLogoLeft);
                                            } else {
                                                $brandLogoLeft = asset($brandLogoLeft);
                                            }
                                        } else {
                                            $brandLogoLeft = asset('images/TcocinaLogo.png');
                                        }
                                    @endphp
                                    <img id="brand_logo_left_preview" src="{{ $brandLogoLeft }}" alt="Logo izquierdo"
                                         class="logo-preview-wrap"
                                         style="height:80px; width:80px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 4px; padding: 4px; background: #f8f9fa;"
                                         onerror="this.src='{{ asset('images/TcocinaLogo.png') }}';">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Color primario</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="brand_primary_color_picker"
                                    name="brand_primary_color"
                                    value="{{ old('brand_primary_color', optional($settings['brand_primary_color'] ?? null)->value ?? '#00b4d8') }}"
                                    style="width: 3rem; padding: 0;" />
                                <input type="text" class="form-control" id="brand_primary_color_text"
                                    placeholder="#284497" pattern="^#([0-9a-fA-F]{3}){1,2}$">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Color acento</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="brand_accent_color_picker"
                                    name="brand_accent_color"
                                    value="{{ old('brand_accent_color', optional($settings['brand_accent_color'] ?? null)->value ?? '#ff6b35') }}"
                                    style="width: 3rem; padding: 0;" />
                                <input type="text" class="form-control" id="brand_accent_color_text"
                                    placeholder="#284497" pattern="^#([0-9a-fA-F]{3}){1,2}$">
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Turnos
                         Reubicada a la vista de Gestión de Turnos -->

                    <!-- Gestión de Configuraciones de Productos -->
                    <div id="section-product-config" class="row g-3 mt-4 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Gestión de Configuraciones de Productos</h5>
                        </div>

                        <!-- Aderezos -->
                        <div class="col-12">
                            <div class="card mb-2">
                                <div class="card-body py-2">
                                    <label for="aderezo_de_carta_descripcion" class="form-label small mb-1">Descripción del aderezo "De carta"</label>
                                    <input type="text" class="form-control form-control-sm" id="aderezo_de_carta_descripcion"
                                        name="aderezo_de_carta_descripcion" maxlength="255"
                                        value="{{ old('aderezo_de_carta_descripcion', optional($settings['aderezo_de_carta_descripcion'] ?? null)->value) }}"
                                        placeholder="Ej: mayonesa de ajo">
                                    <div class="form-text small">Si completás esto, en el catálogo el cliente verá "De carta (tu texto)" en lugar de solo "De carta".</div>
                                </div>
                            </div>
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#aderezosCollapse" aria-expanded="false"
                                        aria-controls="aderezosCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Aderezos
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addProductConfig('Aderezos')">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="aderezosCollapse" class="collapse">
                                    <div class="card-body">
                                        <div id="aderezos-list">
                                            @if (isset($configurations['Aderezos']) && $configurations['Aderezos']->count() > 0)
                                                @foreach ($configurations['Aderezos'] as $config)
                                                    <div class="d-flex justify-content-between align-items-center mb-2 config-item"
                                                        data-id="{{ $config->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check me-2">
                                                                <input class="form-check-input config-enabled-checkbox"
                                                                    type="checkbox"
                                                                    {{ $config->is_available ? 'checked' : '' }}
                                                                    data-config-id="{{ $config->id }}"
                                                                    name="config_available[{{ $config->id }}]"
                                                                    value="1" autocomplete="off">
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="fw-medium {{ $config->is_available ? '' : 'text-muted' }}">{{ $config->value }}</span>
                                                                <small
                                                                    class="text-muted ms-2">${{ number_format($config->price_modifier, 2) }}</small>
                                                                @if (!$config->is_available)
                                                                    <span class="badge bg-secondary ms-1">Deshabilitado</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary me-1"
                                                                onclick="editProductConfig({{ $config->id }}, '{{ $config->value }}', 'Aderezos', {{ $config->price_modifier }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteProductConfig({{ $config->id }}, 'Aderezos')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">No hay aderezos configurados</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dips -->
                        <div class="col-12">
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#dipsCollapse" aria-expanded="false"
                                        aria-controls="dipsCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Dips
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addProductConfig('Dip')">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="dipsCollapse" class="collapse">
                                    <div class="card-body">
                                        <div id="dips-list">
                                            @if (isset($configurations['Dip']) && $configurations['Dip']->count() > 0)
                                                @foreach ($configurations['Dip'] as $config)
                                                    <div class="d-flex justify-content-between align-items-center mb-2 config-item"
                                                        data-id="{{ $config->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check me-2">
                                                                <input class="form-check-input config-enabled-checkbox"
                                                                    type="checkbox"
                                                                    {{ $config->is_available ? 'checked' : '' }}
                                                                    data-config-id="{{ $config->id }}"
                                                                    name="config_available[{{ $config->id }}]"
                                                                    value="1" autocomplete="off">
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="fw-medium {{ $config->is_available ? '' : 'text-muted' }}">{{ $config->value }}</span>
                                                                <small
                                                                    class="text-muted ms-2">${{ number_format($config->price_modifier, 2) }}</small>
                                                                @if (!$config->is_available)
                                                                    <span class="badge bg-secondary ms-1">Deshabilitado</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary me-1"
                                                                onclick="editProductConfig({{ $config->id }}, '{{ $config->value }}', 'Dip', {{ $config->price_modifier }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteProductConfig({{ $config->id }}, 'Dip')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">No hay dips configurados</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     
                       <!-- Gestión de Medallones de Carne Extra -->
                    <div id="section-medallones-extra" class="row g-3 mt-4 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Gestión de Medallones de Carne Extra</h5>
                        </div>

                        <!-- Medallones - Columna completa -->
                        <div class="col-12">
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#medallonesCollapse" aria-expanded="false"
                                        aria-controls="medallonesCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Medallones de Carne Extra
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addProductConfig('Medallones')">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="medallonesCollapse" class="collapse">
                                    <div class="card-body">
                                        <div id="medallones-list">
                                            @if (isset($configurations['Medallones']) && $configurations['Medallones']->count() > 0)
                                                @foreach ($configurations['Medallones'] as $config)
                                                    <div class="d-flex justify-content-between align-items-center mb-2 config-item"
                                                        data-id="{{ $config->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check me-2">
                                                                <input class="form-check-input config-enabled-checkbox"
                                                                    type="checkbox"
                                                                    {{ $config->is_available ? 'checked' : '' }}
                                                                    data-config-id="{{ $config->id }}"
                                                                    name="config_available[{{ $config->id }}]"
                                                                    value="1" autocomplete="off">
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="fw-medium {{ $config->is_available ? '' : 'text-muted' }}">{{ $config->value }}</span>
                                                                <small
                                                                    class="text-muted ms-2">${{ number_format($config->price_modifier, 2) }}</small>
                                                                @if (!$config->is_available)
                                                                    <span class="badge bg-secondary ms-1">Deshabilitado</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary me-1"
                                                                onclick="editProductConfig({{ $config->id }}, '{{ $config->value }}', 'Medallones', {{ $config->price_modifier }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteProductConfig({{ $config->id }}, 'Medallones')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">No hay medallones configurados</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#tipoMedallonCollapse" aria-expanded="false"
                                        aria-controls="tipoMedallonCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Tipos de Medallón
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addProductConfig('Tipo de Medallón')">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="tipoMedallonCollapse" class="collapse">
                                    <div class="card-body">
                                        <div id="tipo-medallon-list">
                                            @if (isset($configurations['Tipo de Medallón']) && $configurations['Tipo de Medallón']->count() > 0)
                                                @foreach ($configurations['Tipo de Medallón'] as $config)
                                                    <div class="d-flex justify-content-between align-items-center mb-2 config-item"
                                                        data-id="{{ $config->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check me-2">
                                                                <input class="form-check-input config-enabled-checkbox"
                                                                    type="checkbox"
                                                                    {{ $config->is_available ? 'checked' : '' }}
                                                                    data-config-id="{{ $config->id }}"
                                                                    name="config_available[{{ $config->id }}]"
                                                                    value="1" autocomplete="off">
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="fw-medium {{ $config->is_available ? '' : 'text-muted' }}">{{ $config->value }}</span>
                                                                <small
                                                                    class="text-muted ms-2">${{ number_format($config->price_modifier, 2) }}</small>
                                                                @if (!$config->is_available)
                                                                    <span class="badge bg-secondary ms-1">Deshabilitado</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary me-1"
                                                                onclick="editProductConfig({{ $config->id }}, '{{ $config->value }}', 'Tipo de Medallón', {{ $config->price_modifier }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteProductConfig({{ $config->id }}, 'Tipo de Medallón')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">No hay tipos de medallón configurados</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de Dip Extra -->
                    <div id="section-dip-extra" class="row g-3 mt-4 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Gestion de Extras</h5>
                        </div>

                        <!-- Dip Extra -->
                        <div class="col-12">
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#dipExtraCollapse" aria-expanded="false"
                                        aria-controls="dipExtraCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Dip Extra
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addProductConfig('Dip Extra')">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="dipExtraCollapse" class="collapse">
                                    <div class="card-body">
                                        <div id="dip-extra-list">
                                            @if (isset($configurations['Dip Extra']) && $configurations['Dip Extra']->count() > 0)
                                                @foreach ($configurations['Dip Extra'] as $config)
                                                    <div class="d-flex justify-content-between align-items-center mb-2 config-item"
                                                        data-id="{{ $config->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check me-2">
                                                                <input class="form-check-input config-enabled-checkbox"
                                                                    type="checkbox"
                                                                    {{ $config->is_available ? 'checked' : '' }}
                                                                    data-config-id="{{ $config->id }}"
                                                                    name="config_available[{{ $config->id }}]"
                                                                    value="1" autocomplete="off">
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="fw-medium {{ $config->is_available ? '' : 'text-muted' }}">{{ $config->value }}</span>
                                                                <small
                                                                    class="text-muted ms-2">${{ number_format($config->price_modifier, 2) }}</small>
                                                                @if (!$config->is_available)
                                                                    <span class="badge bg-secondary ms-1">Deshabilitado</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary me-1"
                                                                onclick="editProductConfig({{ $config->id }}, '{{ $config->value }}', 'Dip Extra', {{ $config->price_modifier }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteProductConfig({{ $config->id }}, 'Dip Extra')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">No hay dips extra configurados</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#extrasCollapse" aria-expanded="false"
                                        aria-controls="extrasCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Extras
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addProductConfig('Extras')">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                                <div id="extrasCollapse" class="collapse">
                                    <div class="card-body">
                                        <div id="extras-list">
                                            @if (isset($configurations['Extras']) && $configurations['Extras']->count() > 0)
                                                @foreach ($configurations['Extras'] as $config)
                                                    <div class="d-flex justify-content-between align-items-center mb-2 config-item"
                                                        data-id="{{ $config->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check me-2">
                                                                <input class="form-check-input config-enabled-checkbox"
                                                                    type="checkbox"
                                                                    {{ $config->is_available ? 'checked' : '' }}
                                                                    data-config-id="{{ $config->id }}"
                                                                    name="config_available[{{ $config->id }}]"
                                                                    value="1" autocomplete="off">
                                                            </div>
                                                            <div>
                                                                <span
                                                                    class="fw-medium {{ $config->is_available ? '' : 'text-muted' }}">{{ $config->value }}</span>
                                                                <small
                                                                    class="{{ $config->is_available ? 'text-muted' : 'text-secondary' }} ms-2">${{ number_format($config->price_modifier, 2) }}</small>
                                                                @if (!$config->is_available)
                                                                    <small
                                                                        class="badge bg-secondary ms-1">Deshabilitado</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary me-1"
                                                                onclick="editProductConfig({{ $config->id }}, '{{ $config->value }}', 'Extras', {{ $config->price_modifier }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteProductConfig({{ $config->id }}, 'Extras')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">No hay extras configurados</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Redes Sociales -->
                    <div id="section-social" class="row g-3 mt-4 settings-section">
                        <div class="col-12">
                            <h5 class="text-primary settings-section-title">Redes Sociales</h5>
                        </div>
                        <div class="col-12">
                            <div class="card settings-collapsible-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm settings-collapse-toggle collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#socialCollapse" aria-expanded="false"
                                        aria-controls="socialCollapse">
                                        <i class="fas fa-chevron-down me-2"></i>Redes sociales
                                    </button>
                                </div>
                                <div id="socialCollapse" class="collapse">
                                    <div class="card-body row g-3">
                                        <div class="col-md-6">
                                            <label for="facebook_url" class="form-label">Facebook</label>
                                            <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                                                value="{{ old('facebook_url', optional($settings['facebook_url'] ?? null)->value) }}"
                                                placeholder="https://facebook.com/tu-pagina">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="instagram_url" class="form-label">Instagram</label>
                                            <input type="url" class="form-control" id="instagram_url" name="instagram_url"
                                                value="{{ old('instagram_url', optional($settings['instagram_url'] ?? null)->value) }}"
                                                placeholder="https://instagram.com/tu-pagina">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="linkedin_url" class="form-label">LinkedIn</label>
                                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url"
                                                value="{{ old('linkedin_url', optional($settings['linkedin_url'] ?? null)->value) }}"
                                                placeholder="https://linkedin.com/company/tu-empresa">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="whatsapp_url" class="form-label">WhatsApp</label>
                                            <input type="url" class="form-control" id="whatsapp_url" name="whatsapp_url"
                                                value="{{ old('whatsapp_url', optional($settings['whatsapp_url'] ?? null)->value) }}"
                                                placeholder="https://wa.me/5492494015745">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <button type="submit" id="floatingSaveBtn" class="btn btn-primary btn-lg save-floating">
                        <i class="fas fa-save me-2"></i>Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* FilePond styles (CDN) */
        @import url('https://unpkg.com/filepond@4/dist/filepond.min.css');
        @import url('https://unpkg.com/filepond-plugin-image-preview@4/dist/filepond-plugin-image-preview.min.css');
        /* Toastify */
        @import url('https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css');
        /* Botón de guardado fijo para Settings */
        #floatingSaveBtn.save-floating{
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1055;
            border-radius: 999px;
            padding: 10px 18px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.15);
        }
        #settingsForm{
            display: grid;
            gap: 14px;
        }
        #settingsForm > .row{
            margin-top: 0 !important;
        }
        .settings-section{
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
            padding: 12px 8px;
        }
        .settings-section-title{
            margin-bottom: .35rem;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a !important;
        }
        .settings-collapsible-card .card-header{
            background: #f8fafc;
        }
        .settings-collapse-toggle{
            border: none;
            background: transparent;
            font-weight: 700;
            color: #0f172a;
            padding-left: 0;
        }
        .settings-collapse-toggle i{
            transition: transform .2s ease;
        }
        .settings-collapse-toggle.collapsed i{
            transform: rotate(-90deg);
        }
        .config-item{
            gap: 10px;
            align-items: flex-start !important;
        }
        .config-item > .d-flex.align-items-center{
            min-width: 0;
            flex: 1;
        }
        .config-item > div:last-child{
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }
        /* Dejar espacio al final del formulario para que no quede tapado */
        form[action*="admin.settings.update"]{ padding-bottom: 96px; }
        @media (max-width: 576px){
            #floatingSaveBtn.save-floating{ right: 16px; bottom: 16px; }
            form[action*="admin.settings.update"]{ padding-bottom: 88px; }
        }
        /* Overlay de carga al enviar */
        .saving-overlay{
            position: fixed; inset: 0; z-index: 2000;
            background: rgba(0,0,0,.5); backdrop-filter: blur(3px);
            display: none; align-items: center; justify-content: center;
        }
        .saving-card{
            background: #0b1020; color: #e6ecff; border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px; padding: 18px 18px; min-width: 280px;
            box-shadow: 0 12px 40px rgba(0,0,0,.35);
            display: flex; align-items: center; gap: 12px;
        }
        .saving-spinner{
            width: 28px; height: 28px; border-radius: 50%;
            border: 3px solid rgba(255,255,255,.15);
            border-top-color: var(--brand-primary, #00b4d8);
            animation: spin 1s linear infinite;
        }
        @keyframes spin{ to { transform: rotate(360deg); } }

        /* ── Dark mode overrides ── */
        html[data-theme="dark"] .settings-section {
            background: #0f1626 !important;
            border-color: #1a2840 !important;
        }
        html[data-theme="dark"] .settings-section-title {
            color: #e8edf5 !important;
        }
        html[data-theme="dark"] .settings-collapsible-card .card-header {
            background: #111c2e !important;
            border-color: #1a2840 !important;
        }
        html[data-theme="dark"] .settings-collapse-toggle {
            color: #c8d6e8 !important;
        }
        html[data-theme="dark"] .d-flex.flex-wrap.gap-3 .form-check {
            background: #111c2e !important;
            border-color: #1a2840 !important;
        }
        html[data-theme="dark"] .alert-info {
            background: rgba(14,165,233,.1) !important;
            border-color: rgba(14,165,233,.3) !important;
            color: #7dd3fc !important;
        }
        html[data-theme="dark"] .logo-preview-wrap {
            background: #111c2e !important;
            border-color: #1a2840 !important;
        }

        /* Estilo del área de drop de FilePond y acción como botón */
        .filepond--drop-label{
            color:#6b7280;
            min-height:64px;
        }
        .filepond--label-action{
            display:inline-flex;
            align-items:center;
            gap:6px;
            background: var(--brand-primary, #00b4d8);
            color: #081420 !important;
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 600;
            text-decoration: none !important;
            box-shadow: 0 6px 16px rgba(0,0,0,.08);
            transition: transform .06s ease, box-shadow .2s ease, background-color .2s ease;
        }
        .filepond--label-action:hover{
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(0,0,0,.12);
            background: color-mix(in srgb, var(--brand-primary, #00b4d8), #000 10%);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Sincronizar inputs de color (picker <-> texto HEX)
        (function (){
            function bindColorPair(colorId, textId){
                const $color = document.getElementById(colorId);
                const $text  = document.getElementById(textId);
                if(!$color || !$text) return;
                const isHex = v => /^#([0-9a-fA-F]{3}){1,2}$/.test((v||'').trim());
                const norm  = v => (v||'').trim().toLowerCase();

                const syncFromColor = () => { $text.value = norm($color.value); $text.classList.remove('is-invalid'); };
                const syncFromText  = () => {
                    const v = norm($text.value);
                    if(isHex(v)){ $color.value = v; $text.classList.remove('is-invalid'); }
                    else { $text.classList.add('is-invalid'); }
                };

                // Inicial
                syncFromColor();
                // Eventos
                $color.addEventListener('input', syncFromColor);
                $text.addEventListener('input', syncFromText);
            }

            document.addEventListener('DOMContentLoaded', function(){
                // Inicializar tooltips de Bootstrap
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                bindColorPair('brand_primary_color_picker','brand_primary_color_text');
                bindColorPair('brand_accent_color_picker','brand_accent_color_text');

                // Asegurar que los campos de estado (sitio + Mi album) se envíen con el form principal
                const form = document.getElementById('settingsForm');
                const titleInput = document.getElementById('site_offline_title');
                const messageInput = document.getElementById('site_offline_message');
                const titleHidden = document.getElementById('site_offline_title_hidden');
                const messageHidden = document.getElementById('site_offline_message_hidden');
                const loyaltyMessageInput = document.getElementById('loyalty_offline_message');
                const loyaltyMessageHidden = document.getElementById('loyalty_offline_message_hidden');
                if (form && titleInput && messageInput && titleHidden && messageHidden && loyaltyMessageInput && loyaltyMessageHidden) {
                    const syncStatusFields = () => {
                        titleHidden.value = titleInput.value || '';
                        messageHidden.value = messageInput.value || '';
                        loyaltyMessageHidden.value = loyaltyMessageInput.value || '';
                    };
                    titleInput.addEventListener('input', syncStatusFields);
                    messageInput.addEventListener('input', syncStatusFields);
                    loyaltyMessageInput.addEventListener('input', syncStatusFields);
                    form.addEventListener('submit', syncStatusFields);
                    syncStatusFields();
                }
            });
        })();

        // Toggle sitio ON/OFF con confirmación
        (function(){
            const btn = document.getElementById('siteToggle');
            if(!btn) return;
            btn.addEventListener('click', function(){
                const currentOff = btn.getAttribute('data-off') === '1';
                const nextOff = !currentOff;
                const title = nextOff ? 'Apagar sitio' : 'Encender sitio';
                const html = nextOff
                    ? '<div style="text-align:left">• Los usuarios podrán navegar el menú pero <strong>no podrán hacer pedidos ni ir al checkout</strong>.<br>• Se mostrará un banner informativo en la parte superior.<br>• El panel admin seguirá accesible para vos.</div>'
                    : '<div style="text-align:left">• Los usuarios podrán navegar y hacer pedidos normalmente.</div>';
                const confirmText = nextOff ? 'Sí, apagar ahora' : 'Sí, encender ahora';

                // Usar Bootstrap modal simple si no hay SweetAlert
                const proceed = () => {
                    fetch('{{ route('admin.settings.toggle-site') }}',{
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                        body: JSON.stringify({ enabled: nextOff })
                    }).then(r=>r.json()).then(data=>{
                        if(data.success){ location.reload(); }
                    }).catch(()=>{});
                };

                if (window.Swal && Swal.fire) {
                    Swal.fire({
                        title,
                        html,
                        icon: nextOff ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonText: confirmText,
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        customClass: { popup: 'rounded-4' }
                    }).then(res => { if(res.isConfirmed) proceed(); });
                } else {
                    if (confirm(`${title}\n\n${nextOff ? 'Los usuarios podrán navegar el menú pero no podrán hacer pedidos.' : 'Los usuarios podrán navegar y hacer pedidos normalmente.'}`)) {
                        proceed();
                    }
                }
            });
        })();

        // Toggle Mi Album ON/OFF con confirmación
        (function(){
            const btn = document.getElementById('loyaltyToggle');
            if(!btn) return;
            btn.addEventListener('click', function(){
                const currentOff = btn.getAttribute('data-off') === '1';
                const nextOff = !currentOff;
                const title = nextOff ? 'Apagar Mi álbum' : 'Encender Mi álbum';
                const html = nextOff
                    ? '<div style="text-align:left">• Los clientes no podrán usar Mi álbum ni solicitar canjes.<br>• Verán el mensaje configurado en esta sección.</div>'
                    : '<div style="text-align:left">• Mi álbum volverá a funcionar normalmente para los clientes.</div>';
                const confirmText = nextOff ? 'Sí, apagar ahora' : 'Sí, encender ahora';

                const proceed = () => {
                    fetch('{{ route('admin.settings.toggle-loyalty') }}',{
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                        body: JSON.stringify({ enabled: nextOff })
                    }).then(r=>r.json()).then(data=>{
                        if(data.success){ location.reload(); }
                    }).catch(()=>{});
                };

                if (window.Swal && Swal.fire) {
                    Swal.fire({
                        title,
                        html,
                        icon: nextOff ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonText: confirmText,
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        customClass: { popup: 'rounded-4' }
                    }).then(res => { if(res.isConfirmed) proceed(); });
                } else {
                    if (confirm(`${title}\n\n${nextOff ? 'Los clientes no podrán usar Mi álbum.' : 'Los clientes podrán usar Mi álbum normalmente.'}`)) {
                        proceed();
                    }
                }
            });
        })();
        // Preview del logo central al seleccionar archivo
        (function(){
            const input = document.getElementById('brand_logo');
            const preview = document.getElementById('brand_logo_preview');
            
            if(!input || !preview) return;
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validar que sea una imagen
                    if (!file.type.match('image.*')) {
                        alert('Por favor selecciona un archivo de imagen válido.');
                        e.target.value = '';
                        return;
                    }
                    
                    // Mostrar preview local
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        })();
        
        // Preview del logo izquierdo al seleccionar archivo
        (function(){
            const input = document.getElementById('brand_logo_left');
            const preview = document.getElementById('brand_logo_left_preview');
            
            if(!input || !preview) return;
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validar que sea una imagen
                    if (!file.type.match('image.*')) {
                        alert('Por favor selecciona un archivo de imagen válido.');
                        e.target.value = '';
                        return;
                    }
                    
                    // Mostrar preview local
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        })();
        // Preview de logo + overlay de guardado
        (function(){
            const form = document.getElementById('settingsForm');
            if(!form) return;
            const fileInput = form.querySelector('input[name="brand_logo"]');
            // Preview en vivo debajo del input
            if(fileInput){
                fileInput.addEventListener('change', function(){
                    const wrap = document.createElement('div');
                    wrap.className = 'mt-2 live-logo-preview';
                    let img = document.createElement('img');
                    img.style.height = '56px';
                    img.style.borderRadius = '10px';
                    wrap.appendChild(img);
                    // Reemplazar preview previa si existía
                    const prev = fileInput.parentElement.querySelector('.live-logo-preview');
                    if(prev){ prev.remove(); }
                    fileInput.parentElement.appendChild(wrap);
                    const file = this.files && this.files[0];
                    if(file){
                        img.src = URL.createObjectURL(file);
                    }
                });
            }
            // Overlay de carga al enviar
            const overlay = document.createElement('div');
            overlay.className = 'saving-overlay';
            overlay.innerHTML = '<div class="saving-card"><div class="saving-spinner"></div><div><div class="fw-bold">Guardando cambios…</div><div class="small" style="opacity:.85">Esto puede tardar unos segundos</div></div></div>';
            document.body.appendChild(overlay);
            form.addEventListener('submit', function(){
                overlay.style.display = 'flex';
            });
        })();
        // Variables globales para manejar configuraciones
        let editingConfigId = null;
        let editingConfigType = null;

        function addProductConfig(type) {
            openConfigModal({ mode: 'create', type });
        }

        function editProductConfig(id, currentName, type, currentPrice) {
            openConfigModal({ mode: 'edit', id, name: currentName, type, price: currentPrice });
        }

        // Modal futurista reutilizable para crear/editar configs
        function openConfigModal(opts) {
            try {
            const { mode, id, name = '', type, price = 0 } = opts;
            const isEdit = mode === 'edit';
            const safeName = name.replace(/"/g, '&quot;');

            const backdrop = document.createElement('div');
            backdrop.className = 'config-panel-overlay';
            backdrop.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:2050;display:flex;align-items:center;justify-content:center;';

            const modal = document.createElement('div');
            modal.style.cssText = 'width:min(520px,94%);border-radius:16px;padding:20px;background:linear-gradient(180deg,#0b1020 0%, #121a2c 100%);color:#e6ecff;box-shadow:0 10px 40px rgba(0,0,0,.35), inset 0 0 1px rgba(255,255,255,.08)';
            // Mostrar exactamente el número guardado como pesos (sin convertir a centavos)
            const toPesos = (value) => Number(value || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            modal.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <div style="width:34px;height:34px;border-radius:10px;background:#6366f1;display:flex;align-items:center;justify-content:center;box-shadow:0 0 12px rgba(99,102,241,.5)">
                        <i class="fas fa-sliders-h" style="color:#081420"></i>
                    </div>
                    <h5 style="margin:0;font-weight:700;letter-spacing:.3px;">${isEdit ? 'Editar' : 'Agregar'} ${type}</h5>
                </div>
                <div style="display:grid;gap:12px;">
                    <div>
                        <label class="form-label text-light">Nombre</label>
                        <input id="cfgName" type="text" class="form-control" value="${safeName}" />
                    </div>
                    <div>
                        <label class="form-label text-light">Precio</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-light btn-sm" id="cfgPriceToggle" style="background:#0e162a;color:#e6ecff;border-color:#22304a;min-width:45px;" title="Cambiar entre positivo y negativo">
                                <i class="fas fa-plus-minus"></i>
                            </button>
                            <span class="input-group-text" style="background:#0e162a;color:#e6ecff;border-color:#22304a">$</span>
                            <input id="cfgPrice" type="text" class="form-control" value="${toPesos(price)}" placeholder="0,00" />
                        </div>
                        <small class="text-muted">Moneda: ARS. Puedes usar el botón +/- o escribir el signo "-" para valores negativos.</small>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end gap-2">
                    <button type="button" id="cfgCancel" class="btn btn-outline-light btn-sm">Cancelar</button>
                    <button type="button" id="cfgSave" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Guardar</button>
                </div>
            `;

            backdrop.appendChild(modal);
            document.body.appendChild(backdrop);

            // Reglas: en Dip Extra antes sólo mostraba fijo; ahora se puede editar precio también
            // Para casos como Aderezos/Dip/Tipo de Medallón donde no quieras precio, se puede dejar 0

            document.getElementById('cfgCancel').addEventListener('click', () => {
                document.body.removeChild(backdrop);
            });

            const priceInput = modal.querySelector('#cfgPrice');
            const priceToggleBtn = modal.querySelector('#cfgPriceToggle');
            let isNegative = Number(price) < 0;
            
            // Función para formatear el precio con signo
            const formatPrice = (val) => {
                const absVal = Math.abs(val);
                if (absVal === 0) return '';
                const formatted = new Intl.NumberFormat('en-US').format(absVal);
                return isNegative ? `-${formatted}.-` : `${formatted}.-`;
            };
            
            // Prefill en formato 1,530.- o -1,530.- (enteros de pesos)
            const initialValue = Math.abs(Math.round(Number(price)));
            priceInput.value = initialValue ? formatPrice(price) : '';
            updateToggleButton();
            
            // Función para actualizar el botón de toggle
            function updateToggleButton() {
                if (isNegative) {
                    priceToggleBtn.innerHTML = '<i class="fas fa-minus"></i>';
                    priceToggleBtn.title = 'Cambiar a positivo';
                    priceToggleBtn.style.color = '#ef4444';
                } else {
                    priceToggleBtn.innerHTML = '<i class="fas fa-plus"></i>';
                    priceToggleBtn.title = 'Cambiar a negativo';
                    priceToggleBtn.style.color = '#e6ecff';
                }
            }
            
            // Toggle positivo/negativo
            priceToggleBtn.addEventListener('click', () => {
                isNegative = !isNegative;
                const currentValue = priceInput.value.replace(/[^\d]/g, '');
                if (currentValue) {
                    priceInput.value = formatPrice(Number(currentValue) * (isNegative ? -1 : 1));
                }
                updateToggleButton();
            });

            // Formato en vivo: permite escribir números y signo "-" al inicio
            priceInput.addEventListener('input', () => {
                let inputValue = priceInput.value;
                // Detectar si empieza con "-"
                const startsWithMinus = inputValue.startsWith('-');
                // Extraer solo dígitos
                const digits = inputValue.replace(/[^\d]/g, '');
                
                if (!digits) { 
                    priceInput.value = '';
                    isNegative = false;
                    updateToggleButton();
                    return;
                }
                
                isNegative = startsWithMinus;
                const num = Number(digits);
                priceInput.value = formatPrice(num);
                updateToggleButton();
            });

            document.getElementById('cfgSave').addEventListener('click', () => {
                const value = document.getElementById('cfgName').value.trim();
                // Convertir representación 1,530.- o -1,530.- al número exacto (pesos enteros con signo)
                const inputValue = (priceInput.value || '').toString();
                const isNegativeValue = inputValue.startsWith('-');
                const digits = inputValue.replace(/[^\d]/g, '');
                const pesosEnteros = digits ? Number(digits) : 0;
                const priceVal = isNegativeValue ? -pesosEnteros : pesosEnteros; // guardar con signo
                if (!value) { alert('El nombre es requerido'); return; }

                const payload = { name: type, value, price_modifier: priceVal };
                const url = isEdit ? `/admin/product-configurations/${id}` : '/admin/product-configurations';
                const method = isEdit ? 'PUT' : 'POST';

                fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
                    .then(data => {
                        if (data.success) {
                            document.body.removeChild(backdrop);
                            // Recargar manteniendo la sección abierta via hash
                            const sectionMap = {
                                'Aderezos': 'aderezosCollapse',
                                'Dip': 'dipsCollapse',
                                'Medallones': 'medallonesCollapse',
                                'Dip Extra': 'dipExtraCollapse',
                                'Extras': 'extrasCollapse',
                            };
                            const collapseId = sectionMap[type];
                            location.href = location.pathname + (collapseId ? '#open=' + collapseId : '');
                        } else {
                            alert(data.message || 'Error al guardar');
                        }
                    })
                    .catch(err => alert('Error: ' + err.message));
            });

            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) {
                    document.body.removeChild(backdrop);
                }
            });
            } catch (err) {
                console.error('Error opening config modal:', err);
                alert('Error al abrir el panel: ' + err.message);
            }
        }

        // Al cargar, abrir la sección indicada por hash #open=collapseId
        (function() {
            const hash = location.hash;
            const match = hash.match(/^#open=(.+)$/);
            if (match) {
                const collapseId = match[1];
                const el = document.getElementById(collapseId);
                if (el) {
                    const bsCollapse = new bootstrap.Collapse(el, { toggle: false });
                    bsCollapse.show();
                    history.replaceState(null, '', location.pathname);
                    setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
                }
            }
        })();

        function deleteProductConfig(id, type) {
            if (confirm(`¿Está seguro de que desea eliminar este ${type}?`)) {
                fetch(`/admin/product-configurations/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const sectionMap = {
                                'Aderezos': 'aderezosCollapse', 'Dip': 'dipsCollapse',
                                'Medallones': 'medallonesCollapse', 'Dip Extra': 'dipExtraCollapse', 'Extras': 'extrasCollapse',
                            };
                            const collapseId = sectionMap[type];
                            location.href = location.pathname + (collapseId ? '#open=' + collapseId : '');
                        } else {
                            alert('Error al eliminar: ' + (data.message || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        alert('Error al eliminar: ' + error.message);
                    });
            }
        }

        // Event listeners para checkboxes de configuración
        document.addEventListener('DOMContentLoaded', function() {
            // Checkboxes de configuraciones
            document.querySelectorAll('.config-enabled-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function(e) {
                    const id = this.dataset.configId;
                    const enabled = this.checked;

                    // Hacer petición AJAX para actualizar el estado
                    fetch(`/admin/product-configurations/${id}/toggle`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                is_available: enabled
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Actualizar visualmente solo si la petición fue exitosa
                                const item = document.querySelector(`[data-id="${id}"]`);
                                const span = item.querySelector('span.fw-medium');
                                const small = item.querySelector('small');

                                if (enabled) {
                                    span.classList.remove('text-muted');
                                    small.classList.remove('text-secondary');
                                    small.classList.add('text-muted');

                                    // Remover badge de deshabilitado si existe
                                    const disabledBadge = item.querySelector(
                                        '.badge.bg-secondary');
                                    if (disabledBadge) {
                                        disabledBadge.remove();
                                    }
                                } else {
                                    span.classList.add('text-muted');
                                    small.classList.remove('text-muted');
                                    small.classList.add('text-secondary');

                                    // Agregar badge de deshabilitado si no existe
                                    if (!item.querySelector('.badge.bg-secondary')) {
                                        const newBadge = document.createElement('small');
                                        newBadge.className = 'badge bg-secondary ms-1';
                                        newBadge.textContent = 'Deshabilitado';
                                        span.parentNode.appendChild(newBadge);
                                    }
                                }
                            } else {
                                // Revertir el checkbox si hubo error
                                this.checked = !enabled;
                                alert('Error al actualizar: ' + (data.message ||
                                    'Error desconocido'));
                            }
                        })
                        .catch(error => {
                            // Revertir el checkbox si hubo error
                            this.checked = !enabled;
                            console.error('Error:', error);
                            alert('Error al actualizar el estado: ' + error.message);
                        });
                });
            });
        });

    </script>

    <!-- Mobile Responsive Styles for Settings -->
    <style>
        /* Mobile: Compact settings cards */
        @media (max-width: 767.98px) {
            /* Card principal compacta */
            .card.mb-4 .card-body {
                padding: 0.75rem !important;
            }
            
            /* Header con toggle más compacto */
            .card.mb-4 .card-body.d-flex.align-items-center.justify-content-between {
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start !important;
            }
            
            .card.mb-4 .card-body.d-flex.align-items-center.justify-content-between > div:first-child {
                gap: 0.75rem !important;
            }
            
            .card.mb-4 .card-body.d-flex.align-items-center.justify-content-between > div:first-child > div:first-child {
                width: 40px !important;
                height: 40px !important;
                border-radius: 10px !important;
            }
            
            .card.mb-4 .card-body.d-flex.align-items-center.justify-content-between > div:first-child > div:first-child i {
                font-size: 16px !important;
            }
            
            .card.mb-4 .fw-bold {
                font-size: 0.9rem;
            }
            
            /* Toggle buttons más compactos */
            #siteToggle, #loyaltyToggle {
                padding: 6px 12px !important;
                font-size: 0.8rem;
                gap: 6px !important;
            }
            
            #siteToggle .badge, #loyaltyToggle .badge {
                min-width: 36px !important;
                font-size: 0.7rem;
                padding: 0.25em 0.4em;
            }
            
            .siteToggleLabel, .loyaltyToggleLabel {
                font-size: 0.75rem;
            }
            
            /* Labels más pequeños */
            .form-label {
                font-size: 0.8rem;
                margin-bottom: 0.25rem;
            }
            
            /* Inputs más compactos */
            .form-control, .form-select {
                padding: 0.375rem 0.5rem;
                font-size: 0.9rem;
            }
            
            /* Form text más pequeño */
            .form-text {
                font-size: 0.7rem;
            }
            
            /* Días de semana en scroll horizontal */
            .d-flex.flex-wrap.gap-3 {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                gap: 0.5rem !important;
                -webkit-overflow-scrolling: touch;
            }
            
            .d-flex.flex-wrap.gap-3 .form-check {
                min-width: 90px;
                margin-bottom: 0;
                padding: 0.25rem 0.5rem;
                background: #f8f9fa;
                border-radius: 6px;
                border: 1px solid #dee2e6;
            }
            html[data-theme="dark"] .d-flex.flex-wrap.gap-3 .form-check {
                background: #111c2e !important;
                border-color: #1a2840 !important;
            }
            
            .d-flex.flex-wrap.gap-3 .form-check .form-check-input {
                margin-top: 0;
                margin-right: 0.25rem;
            }
            
            .d-flex.flex-wrap.gap-3 .form-check-label {
                font-size: 0.8rem;
                white-space: nowrap;
            }
            
            /* Textos descriptivos más compactos */
            .text-muted.small, .text-muted.mb-0 {
                font-size: 0.7rem;
                line-height: 1.3;
            }
            
            /* Acordeones más compactos */
            .settings-collapse-toggle {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
            }
            
            .settings-collapse-toggle i {
                font-size: 0.75rem;
            }
            
            /* Cards colapsables */
            .settings-collapsible-card .card-body {
                padding: 0.75rem !important;
            }
            
            /* Botón guardar más pequeño */
            #floatingSaveBtn, .loyalty-save-floating {
                padding: 0.5rem 1rem !important;
                font-size: 0.85rem;
            }
            
            /* Separadores más sutiles */
            .border-top {
                margin-top: 0.75rem !important;
                padding-top: 0.75rem !important;
            }
            
            /* Título de sección más compacto */
            h2 {
                font-size: 1.1rem;
                margin-bottom: 0.75rem;
            }
            
            h5.settings-section-title {
                font-size: 0.95rem;
            }
        }
        
        /* Tablet adjustments */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .card.mb-4 .card-body {
                padding: 1rem !important;
            }
            
            #siteToggle, #loyaltyToggle {
                padding: 7px 14px !important;
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
