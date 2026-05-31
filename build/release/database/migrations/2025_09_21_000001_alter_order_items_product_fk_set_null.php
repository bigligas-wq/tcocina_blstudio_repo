<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function ($table) {
            // Drop existing FK if present
            try {
                $table->dropForeign(['product_id']);
            } catch (\Throwable $e) {
                // ignore if it doesn't exist
            }
        });

        // Make column nullable without requiring doctrine/dbal
        DB::statement('ALTER TABLE `order_items` MODIFY `product_id` BIGINT UNSIGNED NULL');

        Schema::table('order_items', function ($table) {
            // Recreate FK with ON DELETE SET NULL
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function ($table) {
            try {
                $table->dropForeign(['product_id']);
            } catch (\Throwable $e) {
                // ignore
            }
        });

        // Set NOT NULL again
        DB::statement('ALTER TABLE `order_items` MODIFY `product_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('order_items', function ($table) {
            // Restore previous behavior (restrict or cascade). We'll use cascade to match initial create.
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
