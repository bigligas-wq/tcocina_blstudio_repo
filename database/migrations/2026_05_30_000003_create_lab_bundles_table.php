<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('descripcion_corta', 200)->nullable();
            $table->string('icono', 16)->nullable();
            $table->decimal('precio_bundle_usd', 10, 2);
            $table->enum('estado', ['borrador', 'publicado', 'archivado'])->default('borrador');
            $table->timestamps();
        });

        Schema::create('lab_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_bundle_id')->constrained('lab_bundles')->cascadeOnDelete();
            $table->foreignId('lab_improvement_id')->constrained('lab_improvements')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lab_bundle_id', 'lab_improvement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_bundle_items');
        Schema::dropIfExists('lab_bundles');
    }
};
