<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('coupons') || Schema::hasColumn('coupons', 'allow_cash_discount')) {
            return;
        }

        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('allow_cash_discount')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('coupons') || !Schema::hasColumn('coupons', 'allow_cash_discount')) {
            return;
        }

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('allow_cash_discount');
        });
    }
};
