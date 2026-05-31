<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            $table->enum('reward_category', ['coupon', 'physical', 'other'])
                ->default('other')
                ->after('coupon_code')
                ->comment('Tipo de premio: cupon de descuento, premio fisico, u otro');
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            $table->dropColumn('reward_category');
        });
    }
};
