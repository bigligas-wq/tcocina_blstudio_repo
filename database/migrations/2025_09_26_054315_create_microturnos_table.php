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
        Schema::create('microturnos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('capacidad_maxima');
            $table->integer('pedidos_actuales')->default(0);
            $table->boolean('is_disponible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['fecha', 'hora_inicio']);
            $table->unique(['fecha', 'hora_inicio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('microturnos');
    }
};
