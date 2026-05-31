<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Liberar el índice unique de 'code' y reemplazarlo por uno que incluya deleted_at
        // así el mismo código puede reutilizarse después de un soft-delete
        Schema::table('coupons', function (Blueprint $table) {
            try {
                $table->dropUnique(['code']);
            } catch (\Exception $e) {
                // Ya no existe o tiene otro nombre, ignorar
            }
        });

        // El unique lo manejamos a nivel aplicación (ignorando soft-deleted)
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->unique('code');
        });
    }
};
