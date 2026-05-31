@extends('layouts.app')

@section('title', 'Preguntas Frecuentes - TCocina')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-beach-primary text-white">
                        <h1 class="h3 mb-0">Preguntas Frecuentes</h1>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Encuentra respuestas a las preguntas más comunes sobre nuestros servicios.
                        </p>

                        <div class="accordion" id="faqAccordion">
                            <!-- Pregunta 1 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq1">
                                        ¿Cuáles son los horarios de atención?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        @php
                                            $businessHours = \App\Models\BusinessSetting::get('business_hours', []);
                                            $diasSemana = [
                                                'monday' => 'Lunes',
                                                'tuesday' => 'Martes',
                                                'wednesday' => 'Miércoles',
                                                'thursday' => 'Jueves',
                                                'friday' => 'Viernes',
                                                'saturday' => 'Sábado',
                                                'sunday' => 'Domingo',
                                            ];
                                        @endphp
                                        <p><strong>Horarios de Atención:</strong></p>
                                        @foreach ($diasSemana as $dayKey => $dayName)
                                            @if (isset($businessHours[$dayKey]))
                                                <div class="mb-1">
                                                    <span class="fw-medium">{{ $dayName }}:</span>
                                                    @if ($businessHours[$dayKey]['closed'] ?? false)
                                                        <span class="text-danger">Cerrado</span>
                                                    @else
                                                        <span>{{ $businessHours[$dayKey]['open'] ?? '09:00' }} -
                                                            {{ $businessHours[$dayKey]['close'] ?? '22:00' }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 2 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq2">
                                        ¿Cuánto tiempo tarda el delivery?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Nuestro tiempo estimado de delivery es de
                                            <strong>{{ \App\Models\BusinessSetting::get('estimated_delivery_time', 30) }}-{{ \App\Models\BusinessSetting::get('estimated_delivery_time', 30) + 15 }}
                                                minutos</strong> desde que confirmamos tu pedido.</p>
                                        <p><strong>Factores que pueden afectar el tiempo:</strong></p>
                                        <ul>
                                            <li>Distancia del local</li>
                                            <li>Tráfico en la zona</li>
                                            <li>Complejidad del pedido</li>
                                            <li>Condiciones climáticas</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 3 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq3">
                                        ¿Cuál es el costo del delivery?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>El costo de delivery es de
                                            <strong>${{ number_format(\App\Models\BusinessSetting::get('delivery_fee', 1500) / 100, 2) }}</strong>
                                            fijo.</p>
                                        <p>Si prefieres retirar en local, no hay costo adicional y el tiempo de espera es de
                                            15-20 minutos.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 4 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq4">
                                        ¿Qué métodos de pago aceptan?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Aceptamos los siguientes métodos de pago:</p>
                                        <ul>
                                            <li><strong>Efectivo:</strong> Pago contra entrega</li>
                                            <li><strong>Tarjeta de Crédito/Débito:</strong> Visa, Mastercard, American
                                                Express</li>
                                            <li><strong>Transferencia Bancaria:</strong> Mercado Pago, Ualá, Brubank</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 5 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq5">
                                        ¿Puedo personalizar mis hamburguesas?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>¡Por supuesto! Nuestras hamburguesas se pueden personalizar con:</p>
                                        <ul>
                                            <li><strong>Medallones:</strong> Simple, Doble, Triple, Cuádruple, Quintuple
                                            </li>
                                            <li><strong>Tamaño:</strong> Regular, Grande</li>
                                            <li><strong>Aderezos:</strong> Para el pan</li>
                                            <li><strong>Dip:</strong> Para las papas</li>
                                        </ul>
                                        <p>Cada opción tiene un costo adicional que se muestra al seleccionarla.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 6 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq6">
                                        ¿Cómo puedo cancelar mi pedido?
                                    </button>
                                </h2>
                                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Para cancelar tu pedido:</p>
                                        <ol>
                                            <li>Llama inmediatamente al
                                                {{ \App\Models\BusinessSetting::get('business_phone', '+54 9 249 401-5745') }}
                                            </li>
                                            <li>Proporciona tu número de pedido</li>
                                            <li>Si aún no comenzamos la preparación, la cancelación será gratuita</li>
                                        </ol>
                                        <p><strong>Importante:</strong> No podemos cancelar pedidos que ya están en
                                            preparación.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 7 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq7">
                                        ¿Hacen delivery a toda la ciudad?
                                    </button>
                                </h2>
                                <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Nuestro servicio de delivery cubre:</p>
                                        <ul>
                                            <li><strong>Ciudad de Tandil:</strong> Centro y zonas aledañas</li>
                                            <li><strong>Radio de cobertura:</strong> Hasta 5 km del local</li>
                                        </ul>
                                        <p>Si no estás seguro si llegamos a tu zona, llámanos al
                                            {{ \App\Models\BusinessSetting::get('business_phone', '+54 9 249 401-5745') }} y
                                            te confirmamos.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 8 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq8">
                                        ¿Qué hago si hay un problema con mi pedido?
                                    </button>
                                </h2>
                                <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Si hay algún problema con tu pedido:</p>
                                        <ol>
                                            <li><strong>Contacta inmediatamente:</strong> Llama al
                                                {{ \App\Models\BusinessSetting::get('business_phone', '+54 9 249 401-5745') }}
                                            </li>
                                            <li><strong>Explica el problema:</strong> Detalla qué está mal con tu pedido
                                            </li>
                                            <li><strong>Tomamos acción:</strong> Te ofreceremos una solución (reemplazo,
                                                reembolso, etc.)</li>
                                        </ol>
                                        <p>Nuestro objetivo es que quedes 100% satisfecho con tu pedido.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 9 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq9">
                                        ¿Tienen opciones para vegetarianos?
                                    </button>
                                </h2>
                                <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Sí, ofrecemos opciones para diferentes preferencias alimentarias:</p>
                                        <ul>
                                            <li>Hamburguesas vegetarianas</li>
                                            <li>Opción de sin carne en algunos productos</li>
                                            <li>Aderezos y acompañamientos vegetarianos</li>
                                        </ul>
                                        <p>Consulta nuestro menú o pregunta al personal sobre las opciones disponibles.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregunta 10 -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq10">
                                        ¿Cómo puedo contactar con ustedes?
                                    </button>
                                </h2>
                                <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>Puedes contactarnos de las siguientes maneras:</p>
                                        <ul>
                                            <li><strong>Teléfono:</strong>
                                                {{ \App\Models\BusinessSetting::get('business_phone', '+54 9 249 401-5745') }}
                                            </li>
                                            <li><strong>WhatsApp:</strong>
                                                {{ \App\Models\BusinessSetting::get('whatsapp_number', '5492494015745') }}
                                            </li>
                                            <li><strong>Email:</strong>
                                                {{ \App\Models\BusinessSetting::get('business_email', 'info@tcocina.com') }}
                                            </li>
                                            <li><strong>Dirección:</strong>
                                                {{ \App\Models\BusinessSetting::get('business_address', 'Av. Principal 123, Tandil, Buenos Aires') }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-top">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>¿No encontraste tu pregunta?</strong> No dudes en contactarnos directamente. Estamos
                                aquí para ayudarte.
                            </div>

                            <a href="{{ route('catalog') }}" class="btn btn-beach-primary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Menú
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
