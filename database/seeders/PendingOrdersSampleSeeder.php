<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PendingOrdersSampleSeeder extends Seeder
{
    public function run(): void
    {
        // Productos disponibles para asociar a los ítems
        $products = DB::table('products')->take(12)->get();
        if ($products->count() === 0) {
            $this->command?->warn('No hay productos en la tabla products. Crea productos antes de correr este seeder.');
            return;
        }

        DB::transaction(function () use ($products) {
            for ($i = 1; $i <= 10; $i++) {
                $contactName = 'Cliente Prueba ' . $i;
                $contactPhone = '+54 11 ' . rand(4000, 9999) . '-' . rand(1000, 9999);
                $now = date('Y-m-d H:i:s', time() - rand(60, 5400));

                // Insertar pedido base (pending)
                $orderId = DB::table('orders')->insertGetId([
                    'order_number' => 'ORD-' . strtoupper(bin2hex(random_bytes(4))),
                    'user_id' => null,  // dejamos null para no depender de usuarios
                    'address_id' => null,  // opcional
                    'status' => 'pending',
                    'payment_method' => ['cash', 'card', 'online'][array_rand([0, 1, 2])],
                    'payment_status' => 'pending',
                    'subtotal' => 0,
                    'delivery_fee' => rand(0, 1) ? 500 : 0,
                    'discount_amount' => rand(0, 1) ? rand(200, 1000) : 0,
                    'total_amount' => 0,
                    'contact_name' => $contactName,
                    'contact_phone' => $contactPhone,
                    'notes' => rand(0, 1) ? 'Sin cebolla y extra cheddar, por favor.' : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Ítems del pedido: 1 a 3 productos distintos
                $numItems = rand(1, 3);
                $itemsSubtotal = 0.0;
                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products[rand(0, $products->count() - 1)];
                    $quantity = rand(1, 3);
                    $unitPrice = (float) $product->base_price;
                    $totalPrice = $unitPrice * $quantity;
                    $itemsSubtotal += $totalPrice;

                    // Variantes y opciones ficticias para dar variedad
                    $variants = [];
                    if (rand(0, 1)) {
                        $variants[] = ['name' => 'Medallones', 'value' => ['Simple', 'Doble', 'Triple'][rand(0, 2)]];
                    }
                    if (rand(0, 1)) {
                        $variants[] = ['name' => 'Tipo de Medallón', 'value' => ['Carne', 'Veggie'][rand(0, 1)]];
                    }
                    $possibleOptions = [
                        ['name' => 'Extras', 'value' => 'Bacon + Carne + Cheddar'],
                        ['name' => 'Extras', 'value' => 'Provoleta'],
                        ['name' => 'Extras', 'value' => 'Queso Azul'],
                        ['name' => 'Extras', 'value' => 'Huevo a la plancha'],
                        ['name' => 'Aderezos', 'value' => 'Mayonesa ahumada'],
                        ['name' => 'Aderezos', 'value' => 'Mostaza y miel'],
                    ];
                    shuffle($possibleOptions);
                    $options = array_slice($possibleOptions, 0, rand(0, 3));

                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'selected_variants' => json_encode($variants),
                        'selected_options' => json_encode($options),
                        'special_instructions' => rand(0, 1) ? 'Bien cocida' : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                // Recalcular totales del pedido
                $delivery = (float) DB::table('orders')->where('id', $orderId)->value('delivery_fee');
                $discount = (float) DB::table('orders')->where('id', $orderId)->value('discount_amount');
                DB::table('orders')->where('id', $orderId)->update([
                    'subtotal' => $itemsSubtotal,
                    'total_amount' => max(0, $itemsSubtotal - $discount + $delivery),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        });
    }
}
