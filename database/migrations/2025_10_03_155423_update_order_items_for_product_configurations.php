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
            // Agregar nueva columna para configuraciones
            $table->json('configuration_data')->nullable()->after('total_price');

            // Mantener las columnas existentes por ahora para compatibilidad
            // Las eliminaremos en una migración posterior
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('configuration_data');
        });
    }
};
