@extends('layouts.app')

@section('title', 'Política de Privacidad - TCocina')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-beach-primary text-white">
                        <h1 class="h3 mb-0">Política de Privacidad</h1>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Última actualización: {{ date('d/m/Y') }}</p>

                        <h2 class="h4 text-beach-dark mb-3">1. Información que Recopilamos</h2>
                        <p>En TCocina, recopilamos la siguiente información personal cuando realizas un pedido:</p>
                        <ul>
                            <li><strong>Información de contacto:</strong> Nombre, número de teléfono y dirección de correo
                                electrónico</li>
                            <li><strong>Información de entrega:</strong> Dirección completa para el delivery</li>
                            <li><strong>Información de pedidos:</strong> Productos solicitados, métodos de pago y
                                preferencias</li>
                            <li><strong>Información de navegación:</strong> Cookies y datos de uso del sitio web</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">2. Cómo Utilizamos tu Información</h2>
                        <p>Utilizamos tu información personal para:</p>
                        <ul>
                            <li>Procesar y entregar tus pedidos</li>
                            <li>Comunicarnos contigo sobre tu pedido</li>
                            <li>Mejorar nuestros servicios y productos</li>
                            <li>Enviar promociones y ofertas especiales (con tu consentimiento)</li>
                            <li>Cumplir con obligaciones legales</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">3. Compartir Información</h2>
                        <p>No vendemos, alquilamos ni compartimos tu información personal con terceros, excepto:</p>
                        <ul>
                            <li>Proveedores de servicios que nos ayudan a operar nuestro negocio (solo la información
                                necesaria)</li>
                            <li>Cuando sea requerido por ley</li>
                            <li>Para proteger nuestros derechos y la seguridad de nuestros clientes</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">4. Seguridad de Datos</h2>
                        <p>Implementamos medidas de seguridad técnicas y organizacionales para proteger tu información
                            personal contra acceso no autorizado, alteración, divulgación o destrucción.</p>

                        <h2 class="h4 text-beach-dark mb-3">5. Tus Derechos</h2>
                        <p>Tienes derecho a:</p>
                        <ul>
                            <li>Acceder a tu información personal</li>
                            <li>Corregir información inexacta</li>
                            <li>Solicitar la eliminación de tu información</li>
                            <li>Retirar tu consentimiento para comunicaciones de marketing</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">6. Cookies</h2>
                        <p>Utilizamos cookies para mejorar tu experiencia en nuestro sitio web. Puedes configurar tu
                            navegador para rechazar cookies, pero esto puede afectar la funcionalidad del sitio.</p>

                        <h2 class="h4 text-beach-dark mb-3">7. Contacto</h2>
                        <p>Si tienes preguntas sobre esta Política de Privacidad, puedes contactarnos:</p>
                        <ul>
                            <li><strong>Email:</strong>
                                {{ \App\Models\BusinessSetting::get('business_email', 'info@tcocina.com') }}</li>
                            <li><strong>Teléfono:</strong>
                                {{ \App\Models\BusinessSetting::get('business_phone', '+54 9 249 401-5745') }}</li>
                            <li><strong>Dirección:</strong>
                                {{ \App\Models\BusinessSetting::get('business_address', 'Av. Principal 123, Tandil, Buenos Aires') }}
                            </li>
                        </ul>

                        <div class="mt-4 pt-4 border-top">
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
