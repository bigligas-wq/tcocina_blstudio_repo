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
        // Eliminar columnas obsoletas de order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'selected_variants')) {
                $table->dropColumn('selected_variants');
            }
            if (Schema::hasColumn('order_items', 'selected_options')) {
                $table->dropColumn('selected_options');
            }
        });

        // Eliminar tablas obsoletas (en orden de dependencias)
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('sauces');
        Schema::dropIfExists('extras');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columnas en order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('selected_variants')->nullable();
            $table->json('selected_options')->nullable();
        });

        // Recrear tablas obsoletas (simplificado para rollback)
        Schema::create('sauces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('value');
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('value');
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
