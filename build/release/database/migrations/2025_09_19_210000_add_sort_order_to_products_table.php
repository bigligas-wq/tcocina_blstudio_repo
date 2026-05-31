<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                // Insertar la columna al final si columnas esperadas no existen en este esquema
                $table->integer('sort_order')->default(0);
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            });
        }
    }
};
