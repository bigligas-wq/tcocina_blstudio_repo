<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop existing FKs first to allow altering columns
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['address_id']);
            } catch (\Throwable $e) {
            }

            // Make columns nullable (requires doctrine/dbal)
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('address_id')->nullable()->change();

            // Recreate foreign keys allowing nulls
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['address_id']);
            } catch (\Throwable $e) {
            }

            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unsignedBigInteger('address_id')->nullable(false)->change();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('address_id')->references('id')->on('addresses')->cascadeOnDelete();
        });
    }
};
