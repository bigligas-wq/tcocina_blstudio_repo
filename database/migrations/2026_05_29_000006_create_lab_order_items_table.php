<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->foreignId('lab_improvement_id')->nullable()->constrained('lab_improvements')->nullOnDelete();
            $table->string('nombre_snapshot');
            $table->decimal('precio_usd_snapshot', 8, 2)->default(0);
            $table->text('nota')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'activo'])->default('pendiente');
            $table->timestamp('activado_at')->nullable();
            $table->timestamps();

            $table->index('lab_order_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_items');
    }
};
