<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Asegurar FK con cascade para order_id
            // Algunos motores requieren nombre exacto; intentamos soltar si existe
            try {
                $table->dropForeign(['order_id']);
            } catch (\Throwable $e) {
                // Ignorar si no existe
            }

            // Volver a crear con ON DELETE CASCADE
            $table
                ->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            // Asegurar integridad de product_id (sin cascada de borrado de producto por seguridad)
            try {
                $table->dropForeign(['product_id']);
            } catch (\Throwable $e) {
                // Ignorar si no existe
            }
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Revertir a FKs sin cascada (seguro si ya no existen)
            try {
                $table->dropForeign(['order_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['product_id']);
            } catch (\Throwable $e) {
            }

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }
};
