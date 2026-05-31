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
                'value' => 'TecoCina',
                'type' => 'string',
                'description' => 'Nombre del negocio',
            ],
            [
                'key' => 'business_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'string',
                'description' => 'Teléfono del negocio',
            ],
            [
                'key' => 'business_email',
                'value' => 'info@tecocina.com',
                'type' => 'string',
                'description' => 'Email del negocio',
            ],
            [
                'key' => 'business_address',
                'value' => '123 Calle Principal, Ciudad, Estado 12345',
                'type' => 'string',
                'description' => 'Dirección del negocio',
            ],
            [
                'key' => 'delivery_fee',
                'value' => '2.99',
                'type' => 'float',
                'description' => 'Costo de entrega',
            ],
            [
                'key' => 'minimum_order_amount',
                'value' => '15.00',
                'type' => 'float',
                'description' => 'Monto mínimo de pedido',
            ],
            [
                'key' => 'estimated_delivery_time',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Tiempo estimado de entrega en minutos',
            ],
            [
                'key' => 'business_hours',
                'value' => json_encode([
                    'monday' => ['open' => '10:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '10:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '10:00', 'close' => '22:00'],
                    'thursday' => ['open' => '10:00', 'close' => '22:00'],
                    'friday' => ['open' => '10:00', 'close' => '23:00'],
                    'saturday' => ['open' => '10:00', 'close' => '23:00'],
                    'sunday' => ['open' => '11:00', 'close' => '21:00'],
                ]),
                'type' => 'json',
                'description' => 'Horarios de atención',
            ],
            [
                'key' => 'payment_methods',
                'value' => json_encode(['cash', 'card', 'online']),
                'type' => 'json',
                'description' => 'Métodos de pago disponibles',
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
                'key' => 'tax_rate',
                'value' => '8.5',
                'type' => 'float',
                'description' => 'Tasa de impuestos en porcentaje',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\BusinessSetting::create($setting);
        }
    }
}
