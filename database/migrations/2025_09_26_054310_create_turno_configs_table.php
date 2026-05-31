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
        Schema::create('turno_configs', function (Blueprint $table) {
            $table->id();
            $table->time('hora_inicio')->default('19:30:00');
            $table->time('hora_fin')->default('22:30:00');
            $table->integer('duracion_microturno_minutos')->default(18);
            $table->integer('max_pedidos_por_microturno')->default(6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_configs');
    }
};
