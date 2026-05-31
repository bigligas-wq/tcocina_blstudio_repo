<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('estado', [
                'pendiente_pago',
                'confirmado',
                'en_proceso',
                'activo_parcial',
                'activo',
                'cancelado',
            ])->default('pendiente_pago');
            $table->decimal('total_usd', 10, 2)->default(0);
            $table->string('comprobante_path')->nullable();
            $table->timestamp('whatsapp_enviado_at')->nullable();
            $table->timestamp('confirmado_at')->nullable();
            $table->timestamp('activado_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'estado']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
