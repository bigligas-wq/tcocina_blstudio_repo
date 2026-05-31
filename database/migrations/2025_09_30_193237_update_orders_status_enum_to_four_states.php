<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar estados existentes que ya no serán válidos
        DB::table('orders')
            ->where('status', 'ready')
            ->update(['status' => 'preparing']);

        DB::table('orders')
            ->where('status', 'out_for_delivery')
            ->update(['status' => 'preparing']);

        DB::table('orders')
            ->whereIn('status', ['cancelled'])
            ->update(['status' => 'delivered']);

        // Modificar la columna para usar solo 4 estados
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'delivered') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar los estados anteriores
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
