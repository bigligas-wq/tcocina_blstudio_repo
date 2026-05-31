@extends('layouts.app')

@section('title', 'Términos y Condiciones - TCocina')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-beach-primary text-white">
                        <h1 class="h3 mb-0">Términos y Condiciones</h1>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Última actualización: {{ date('d/m/Y') }}</p>

                        <h2 class="h4 text-beach-dark mb-3">1. Aceptación de los Términos</h2>
                        <p>Al acceder y utilizar el servicio de TCocina, aceptas estar sujeto a estos Términos y
                            Condiciones. Si no estás de acuerdo con alguna parte de estos términos, no debes utilizar
                            nuestro servicio.</p>

                        <h2 class="h4 text-beach-dark mb-3">2. Descripción del Servicio</h2>
                        <p>TCocina ofrece:</p>
                        <ul>
                            <li>Hamburguesas artesanales y productos gastronómicos</li>
                            <li>Servicio de delivery y retiro en local</li>
                            <li>Pedidos a través de nuestra plataforma web</li>
                            <li>Atención al cliente y soporte</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">3. Pedidos y Pagos</h2>
                        <h3 class="h5 text-beach-dark mb-2">3.1 Realización de Pedidos</h3>
                        <ul>
                            <li>Los pedidos se realizan a través de nuestra plataforma web</li>
                            <li>Debes proporcionar información precisa y completa</li>
                            <li>Los precios están sujetos a cambios sin previo aviso</li>
                        </ul>

                        <h3 class="h5 text-beach-dark mb-2">3.2 Métodos de Pago</h3>
                        <ul>
                            <li>Aceptamos efectivo, tarjetas de crédito/débito y transferencias</li>
                            <li>Los pagos se procesan al momento de confirmar el pedido</li>
                            <li>TCocina se reserva el derecho de rechazar cualquier pago</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">4. Delivery y Retiro</h2>
                        <h3 class="h5 text-beach-dark mb-2">4.1 Delivery</h3>
                        <ul>
                            <li>Tiempo estimado:
                                {{ \App\Models\BusinessSetting::get('estimated_delivery_time', 30) }}-{{ \App\Models\BusinessSetting::get('estimated_delivery_time', 30) + 15 }}
                                minutos</li>
                            <li>Costo de delivery:
                                ${{ number_format(\App\Models\BusinessSetting::get('delivery_fee', 1500) / 100, 2) }}</li>
                            <li>Debes estar presente en la dirección indicada</li>
                            <li>TCocina no se responsabiliza por demoras por causas externas</li>
                        </ul>

                        <h3 class="h5 text-beach-dark mb-2">4.2 Retiro en Local</h3>
                        <ul>
                            <li>Tiempo estimado: 15-20 minutos</li>
                            <li>Debes presentar identificación al retirar</li>
                            <li>Los pedidos no retirados en 2 horas pueden ser cancelados</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">5. Cancelaciones y Reembolsos</h2>
                        <ul>
                            <li>Las cancelaciones deben realizarse antes de que comience la preparación</li>
                            <li>Los reembolsos se procesarán según el método de pago original</li>
                            <li>TCocina se reserva el derecho de rechazar cancelaciones por causas justificadas</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">6. Responsabilidad del Cliente</h2>
                        <p>El cliente se compromete a:</p>
                        <ul>
                            <li>Proporcionar información veraz y actualizada</li>
                            <li>Estar disponible para recibir el delivery</li>
                            <li>Respetar a nuestro personal y establecimiento</li>
                            <li>No realizar pedidos fraudulentos o maliciosos</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">7. Limitación de Responsabilidad</h2>
                        <p>TCocina no será responsable por:</p>
                        <ul>
                            <li>Daños indirectos o consecuenciales</li>
                            <li>Demoras por causas externas (tráfico, clima, etc.)</li>
                            <li>Problemas con métodos de pago de terceros</li>
                            <li>Cambios en ingredientes por disponibilidad</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">8. Modificaciones</h2>
                        <p>TCocina se reserva el derecho de modificar estos términos en cualquier momento. Los cambios
                            entrarán en vigor inmediatamente después de su publicación en el sitio web.</p>

                        <h2 class="h4 text-beach-dark mb-3">9. Ley Aplicable</h2>
                        <p>Estos términos se rigen por las leyes de la República Argentina. Cualquier disputa será resuelta
                            en los tribunales competentes de la ciudad de Tandil.</p>

                        <h2 class="h4 text-beach-dark mb-3">10. Contacto</h2>
                        <p>Para consultas sobre estos términos:</p>
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
