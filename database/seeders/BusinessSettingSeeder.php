<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'business_name',
                'value' => 'TCocina',
                'type' => 'string',
                'description' => 'Nombre del negocio',
            ],
            [
                'key' => 'business_phone',
                'value' => '+54 9 249 401-5745',
                'type' => 'string',
                'description' => 'Teléfono del negocio',
            ],
            [
                'key' => 'business_email',
                'value' => 'info@tcocina.com',
                'type' => 'string',
                'description' => 'Email del negocio',
            ],
            [
                'key' => 'business_address',
                'value' => 'Av. Principal 123, Tandil, Buenos Aires',
                'type' => 'string',
                'description' => 'Dirección del negocio',
            ],
            [
                'key' => 'footer_description',
                'value' => 'Hamburguesas artesanales, ingredientes frescos y combos para todos los gustos.',
                'type' => 'string',
                'description' => 'Descripción que aparece en el footer del sitio',
            ],
            [
                'key' => 'delivery_fee',
                'value' => '500',
                'type' => 'float',
                'description' => 'Costo de entrega',
            ],
            [
                'key' => 'estimated_delivery_time',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Tiempo estimado de entrega en minutos',
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '5492494015745',
                'type' => 'string',
                'description' => 'Número de WhatsApp para pedidos',
            ],
            [
                'key' => 'site_offline_message',
                'value' => 'Por el momento no estamos tomando pedidos.',
                'type' => 'string',
                'description' => 'Mensaje visible para clientes cuando el sitio está apagado',
            ],
            [
                'key' => 'site_offline_title',
                'value' => 'T cocina',
                'type' => 'string',
                'description' => 'Título visible para clientes cuando el sitio está apagado',
            ],
            [
                'key' => 'business_hours',
                'value' => json_encode([
                    'monday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                    'tuesday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                    'wednesday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                    'thursday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
                    'friday' => ['open' => '09:00', 'close' => '23:00', 'closed' => false],
                    'saturday' => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
                    'sunday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
                ]),
                'type' => 'json',
                'description' => 'Horarios de atención por día',
            ],
            [
                'key' => 'payment_methods',
                'value' => json_encode(['cash', 'card', 'transfer']),
                'type' => 'json',
                'description' => 'Métodos de pago habilitados',
            ],
            [
                'key' => 'is_delivery_available',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Si el servicio de entrega está disponible',
            ],
            [
                'key' => 'is_pickup_available',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Si el servicio de recogida está disponible',
            ],
            [
                'key' => 'facebook_url',
                'value' => '',
                'type' => 'string',
                'description' => 'URL de Facebook',
            ],
            [
                'key' => 'instagram_url',
                'value' => '',
                'type' => 'string',
                'description' => 'URL de Instagram',
            ],
            [
                'key' => 'linkedin_url',
                'value' => '',
                'type' => 'string',
                'description' => 'URL de LinkedIn',
            ],
            [
                'key' => 'whatsapp_url',
                'value' => '',
                'type' => 'string',
                'description' => 'URL de WhatsApp',
            ],
            [
                'key' => 'skip_turno_selection',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Saltar selección de turno y asignar automáticamente el primer microturno disponible',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\BusinessSetting::create($setting);
        }
    }
}
