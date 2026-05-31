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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('microturno_id')->nullable()->constrained('microturnos')->onDelete('set null');
            $table->index('microturno_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['microturno_id']);
            $table->dropIndex(['microturno_id']);
            $table->dropColumn('microturno_id');
        });
    }
};
