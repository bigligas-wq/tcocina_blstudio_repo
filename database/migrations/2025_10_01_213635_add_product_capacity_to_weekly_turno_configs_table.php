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
        Schema::table('weekly_turno_configs', function (Blueprint $table) {
            $table->integer('max_hamburguesas')->default(6)->after('max_pedidos_por_microturno');
            $table->integer('max_acompañamientos')->default(6)->after('max_hamburguesas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_turno_configs', function (Blueprint $table) {
            $table->dropColumn(['max_hamburguesas', 'max_acompañamientos']);
        });
    }
};
