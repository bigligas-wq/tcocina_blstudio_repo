@extends('layouts.app')

@section('title', 'Envíos y Devoluciones - TCocina')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-beach-primary text-white">
                        <h1 class="h3 mb-0">Envíos y Devoluciones</h1>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Última actualización: {{ date('d/m/Y') }}</p>

                        <h2 class="h4 text-beach-dark mb-3">🚚 Servicio de Delivery</h2>

                        <h3 class="h5 text-beach-dark mb-2">Zona de Cobertura</h3>
                        <p>Nuestro servicio de delivery está disponible en:</p>
                        <ul>
                            <li><strong>Ciudad de Tandil:</strong> Centro y zonas aledañas</li>
                            <li><strong>Radio de cobertura:</strong> Hasta 5 km del local</li>
                            <li><strong>Consultas de cobertura:</strong> Contactanos para confirmar disponibilidad</li>
                        </ul>

                        <h3 class="h5 text-beach-dark mb-2">Tiempos de Entrega</h3>
                        <ul>
                            <li><strong>Delivery:</strong>
                                {{ \App\Models\BusinessSetting::get('estimated_delivery_time', 30) }}-{{ \App\Models\BusinessSetting::get('estimated_delivery_time', 30) + 15 }}
                                minutos</li>
                            <li><strong>Retiro en local:</strong> 15-20 minutos</li>
                            <li><strong>Horarios de entrega:</strong> Según horarios de atención del local</li>
                        </ul>

                        <h3 class="h5 text-beach-dark mb-2">Costos de Delivery</h3>
                        <ul>
                            <li><strong>Costo fijo:</strong>
                                ${{ number_format(\App\Models\BusinessSetting::get('delivery_fee', 1500) / 100, 2) }}</li>
                            <li><strong>Pedido mínimo:</strong> No aplica</li>
                            <li><strong>Promociones:</strong> Consulta nuestras ofertas especiales</li>
                        </ul>

                        <h3 class="h5 text-beach-dark mb-2">Proceso de Delivery</h3>
                        <ol>
                            <li><strong>Confirmación:</strong> Recibirás confirmación del pedido</li>
                            <li><strong>Preparación:</strong> Tiempo estimado de preparación</li>
                            <li><strong>En camino:</strong> Te notificaremos cuando salga el delivery</li>
                            <li><strong>Entrega:</strong> El repartidor te contactará al llegar</li>
                        </ol>

                        <h2 class="h4 text-beach-dark mb-3">📦 Retiro en Local</h2>
                        <ul>
                            <li><strong>Dirección:</strong>
                                {{ \App\Models\BusinessSetting::get('business_address', 'Av. Principal 123, Tandil, Buenos Aires') }}
                            </li>
                            <li><strong>Tiempo de espera:</strong> Máximo 2 horas después de la confirmación</li>
                            <li><strong>Identificación:</strong> Debes presentar DNI al retirar</li>
                            <li><strong>Estacionamiento:</strong> Disponible en la zona</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">❌ Política de Devoluciones</h2>

                        <h3 class="h5 text-beach-dark mb-2">Casos Aceptados</h3>
                        <ul>
                            <li><strong>Error en el pedido:</strong> Si recibiste productos diferentes a los solicitados
                            </li>
                            <li><strong>Producto en mal estado:</strong> Si el producto no cumple con nuestros estándares de
                                calidad</li>
                            <li><strong>Pedido incompleto:</strong> Si faltan productos de tu pedido</li>
                        </ul>

                        <h3 class="h5 text-beach-dark mb-2">Casos No Aceptados</h3>
                        <ul>
                            <li>Cambio de opinión después de recibir el pedido</li>
                            <li>Productos consumidos parcialmente</li>
                            <li>Demoras por causas externas (tráfico, clima)</li>
                            <li>Problemas con el método de pago del cliente</li>
                        </ul>

                        <h3 class="h5 text-beach-dark mb-2">Proceso de Devolución</h3>
                        <ol>
                            <li><strong>Contacto:</strong> Llama inmediatamente al
                                {{ \App\Models\BusinessSetting::get('business_phone', '+54 9 249 401-5745') }}</li>
                            <li><strong>Verificación:</strong> Revisaremos el caso y te daremos una respuesta</li>
                            <li><strong>Resolución:</strong> Según el caso, haremos un reembolso o reemplazo</li>
                            <li><strong>Reembolso:</strong> Se procesará en 3-5 días hábiles</li>
                        </ol>

                        <h2 class="h4 text-beach-dark mb-3">⚠️ Condiciones Especiales</h2>
                        <ul>
                            <li><strong>Productos perecederos:</strong> No aceptamos devoluciones por productos que ya no
                                están frescos</li>
                            <li><strong>Clima adverso:</strong> Los tiempos pueden extenderse por condiciones climáticas
                            </li>
                            <li><strong>Eventos especiales:</strong> Los tiempos pueden variar en fechas especiales</li>
                            <li><strong>Disponibilidad:</strong> Algunos productos pueden no estar disponibles</li>
                        </ul>

                        <h2 class="h4 text-beach-dark mb-3">📞 Contacto y Soporte</h2>
                        <p>Para consultas sobre envíos y devoluciones:</p>
                        <ul>
                            <li><strong>Teléfono:</strong>
                                {{ \App\Models\BusinessSetting::get('business_phone', '+54 9 249 401-5745') }}</li>
                            <li><strong>WhatsApp:</strong>
                                {{ \App\Models\BusinessSetting::get('whatsapp_number', '5492494015745') }}</li>
                            <li><strong>Email:</strong>
                                {{ \App\Models\BusinessSetting::get('business_email', 'info@tcocina.com') }}</li>
                            <li><strong>Horarios de atención:</strong> Consulta nuestros horarios de atención al cliente
                            </li>
                        </ul>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> Siempre revisa tu pedido al recibirlo. Las devoluciones deben
                            reportarse dentro de las primeras 2 horas de recibido el pedido.
                        </div>

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
