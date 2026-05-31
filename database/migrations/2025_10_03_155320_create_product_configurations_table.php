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
        Schema::create('product_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Tipo de configuración: "Medallones", "Extras", "Aderezos", etc.
            $table->string('value');  // Valor específico: "Doble", "Carne", "Salsa de la casa", etc.
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Índices para optimización
            $table->unique(['name', 'value'], 'unique_config');
            $table->index(['name', 'is_available', 'sort_order'], 'idx_name_available_sort');
            $table->index(['is_available'], 'idx_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_configurations');
    }
};
