<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        \App\Models\User::create([
            'name' => 'Administrador',
            'email' => 'admin@tecocina.com',
            'password' => bcrypt('password'),
            'phone' => '+1 (555) 123-4567',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Crear usuario de cocina
        \App\Models\User::create([
            'name' => 'Chef Cocina',
            'email' => 'cocina@tecocina.com',
            'password' => bcrypt('password'),
            'phone' => '+1 (555) 123-4568',
            'role' => 'kitchen',
            'is_active' => true,
        ]);

        // Crear usuario de reparto
        \App\Models\User::create([
            'name' => 'Repartidor',
            'email' => 'reparto@tecocina.com',
            'password' => bcrypt('password'),
            'phone' => '+1 (555) 123-4569',
            'role' => 'delivery',
            'is_active' => true,
        ]);

        // Crear algunos clientes de ejemplo
        $clientes = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 111-1111',
                'role' => 'customer',
                'is_active' => true,
            ],
            [
                'name' => 'María García',
                'email' => 'maria@example.com',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 222-2222',
                'role' => 'customer',
                'is_active' => true,
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos@example.com',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 333-3333',
                'role' => 'customer',
                'is_active' => true,
            ],
        ];

        foreach ($clientes as $cliente) {
            \App\Models\User::create($cliente);
        }

        // Crear direcciones para los clientes
        $juan = \App\Models\User::where('email', 'juan@example.com')->first();
        $maria = \App\Models\User::where('email', 'maria@example.com')->first();

        // Direcciones de Juan
        \App\Models\Address::create([
            'user_id' => $juan->id,
            'name' => 'Casa',
            'street' => 'Calle Principal',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'postal_code' => '12345',
            'reference' => 'Frente al parque',
            'is_default' => true,
        ]);

        \App\Models\Address::create([
            'user_id' => $juan->id,
            'name' => 'Trabajo',
            'street' => 'Avenida Comercial',
            'number' => '456',
            'neighborhood' => 'Zona Industrial',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'postal_code' => '12346',
            'reference' => 'Edificio de oficinas',
            'is_default' => false,
        ]);

        // Direcciones de María
        \App\Models\Address::create([
            'user_id' => $maria->id,
            'name' => 'Casa',
            'street' => 'Calle Residencial',
            'number' => '789',
            'neighborhood' => 'Residencial Norte',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'postal_code' => '12347',
            'reference' => 'Casa azul con portón blanco',
            'is_default' => true,
        ]);
    }
}
