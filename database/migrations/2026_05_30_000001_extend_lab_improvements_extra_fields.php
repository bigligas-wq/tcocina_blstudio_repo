<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lab_improvements', function (Blueprint $table) {
            $table->decimal('precio_descuento_usd', 8, 2)->nullable()->after('precio_usd');
            $table->timestamp('descuento_hasta')->nullable()->after('precio_descuento_usd');
            $table->boolean('es_popular')->default(false)->after('es_destacada');
            $table->unsignedSmallInteger('tiempo_estimado_horas')->nullable()->after('es_popular');
            $table->string('roi_estimado', 120)->nullable()->after('tiempo_estimado_horas');
        });
    }

    public function down(): void
    {
        Schema::table('lab_improvements', function (Blueprint $table) {
            $table->dropColumn([
                'precio_descuento_usd',
                'descuento_hasta',
                'es_popular',
                'tiempo_estimado_horas',
                'roi_estimado',
            ]);
        });
    }
};
