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
        Schema::create('weekly_turno_configs', function (Blueprint $table) {
            $table->id();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('duracion_microturno_minutos');
            $table->integer('max_pedidos_por_microturno');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_enabled')->default(true);  // Si el día está habilitado para pedidos
            $table->timestamps();

            $table->unique('day_of_week');
            $table->index(['day_of_week', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_turno_configs');
    }
};
