<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('coupons')) {
            return;
        }

        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->nullable()->after('value');
            }

            if (!Schema::hasColumn('coupons', 'code_length')) {
                $table->integer('code_length')->default(8)->after('discount_percentage');
            }

            if (!Schema::hasColumn('coupons', 'used_count')) {
                $table->integer('used_count')->default(0)->after('usage_count');
            }
        });

        if (Schema::hasColumn('coupons', 'type') && Schema::hasColumn('coupons', 'value') && Schema::hasColumn('coupons', 'discount_percentage')) {
            DB::statement("UPDATE coupons SET discount_percentage = value WHERE type = 'percentage' AND discount_percentage IS NULL");
        }

        if (Schema::hasColumn('coupons', 'code') && Schema::hasColumn('coupons', 'code_length')) {
            DB::statement("UPDATE coupons SET code_length = LENGTH(code) WHERE code_length IS NULL OR code_length = 0");
        }

        if (Schema::hasColumn('coupons', 'usage_count') && Schema::hasColumn('coupons', 'used_count')) {
            DB::statement("UPDATE coupons SET used_count = usage_count WHERE used_count = 0 AND usage_count > 0");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('coupons')) {
            return;
        }

        Schema::table('coupons', function (Blueprint $table) {
            if (Schema::hasColumn('coupons', 'discount_percentage')) {
                $table->dropColumn('discount_percentage');
            }
            if (Schema::hasColumn('coupons', 'code_length')) {
                $table->dropColumn('code_length');
            }
            if (Schema::hasColumn('coupons', 'used_count')) {
                $table->dropColumn('used_count');
            }
        });
    }
};
