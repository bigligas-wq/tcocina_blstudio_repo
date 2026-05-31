<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_changelog_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['nueva_mejora', 'actualizacion', 'publicado', 'nota', 'promo'])->default('nota');
            $table->string('titulo', 160);
            $table->text('cuerpo')->nullable();
            $table->string('icono', 16)->nullable();
            $table->string('color', 16)->nullable();
            $table->foreignId('lab_improvement_id')->nullable()->constrained('lab_improvements')->nullOnDelete();
            $table->boolean('visible')->default(true);
            $table->timestamp('publicado_en')->useCurrent();
            $table->timestamps();

            $table->index(['visible', 'publicado_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_changelog_entries');
    }
};
