<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_improvements', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion_corta');
            $table->text('descripcion_larga')->nullable();
            $table->enum('categoria', ['visual', 'ux', 'performance', 'admin'])->default('visual');
            $table->decimal('precio_usd', 8, 2)->default(0);
            $table->string('icono', 16)->nullable();
            $table->boolean('es_destacada')->default(false);
            $table->enum('estado', ['borrador', 'publicada', 'archivada'])->default('borrador');
            $table->string('imagen_antes')->nullable();
            $table->string('imagen_despues')->nullable();
            $table->json('diferencias')->nullable();
            $table->timestamps();

            $table->index(['estado', 'categoria']);
            $table->index('es_destacada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_improvements');
    }
};
