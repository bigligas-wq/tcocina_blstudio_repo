<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_credit_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('balance_usd', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('lab_credit_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_credit_wallet_id')->constrained('lab_credit_wallets')->cascadeOnDelete();
            $table->enum('tipo', ['credito', 'debito'])->default('credito');
            $table->decimal('monto_usd', 10, 2);
            $table->string('descripcion', 200);
            $table->foreignId('lab_order_id')->nullable()->constrained('lab_orders')->nullOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('lab_credit_wallet_id');
        });

        Schema::table('lab_orders', function (Blueprint $table) {
            $table->decimal('credits_aplicados_usd', 10, 2)->default(0)->after('total_usd');
        });
    }

    public function down(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->dropColumn('credits_aplicados_usd');
        });
        Schema::dropIfExists('lab_credit_movements');
        Schema::dropIfExists('lab_credit_wallets');
    }
};
