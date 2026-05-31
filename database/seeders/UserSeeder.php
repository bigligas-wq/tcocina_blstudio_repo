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
        $admin = \App\Models\User::create([
            'name' => 'Administrador',
            'email' => 'admin@tecocina.com',
            'password' => bcrypt('password'),
            'phone' => '+1 (555) 123-4567',
            'is_active' => true,
        ]);
        $admin->role = 'admin';
        $admin->save();

        // Crear usuario de cocina
        $kitchen = \App\Models\User::create([
            'name' => 'Chef Cocina',
            'email' => 'cocina@tecocina.com',
            'password' => bcrypt('password'),
            'phone' => '+1 (555) 123-4568',
            'is_active' => true,
        ]);
        $kitchen->role = 'kitchen';
        $kitchen->save();

        // Crear usuario de reparto
        $delivery = \App\Models\User::create([
            'name' => 'Repartidor',
            'email' => 'reparto@tecocina.com',
            'password' => bcrypt('password'),
            'phone' => '+1 (555) 123-4569',
            'is_active' => true,
        ]);
        $delivery->role = 'delivery';
        $delivery->save();

        // Crear algunos clientes de ejemplo
        $clientes = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 111-1111',
                'is_active' => true,
            ],
            [
                'name' => 'María García',
                'email' => 'maria@example.com',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 222-2222',
                'is_active' => true,
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos@example.com',
                'password' => bcrypt('password'),
                'phone' => '+1 (555) 333-3333',
                'is_active' => true,
            ],
        ];

        foreach ($clientes as $cliente) {
            $user = \App\Models\User::create($cliente);
            $user->role = 'customer';
            $user->save();
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
