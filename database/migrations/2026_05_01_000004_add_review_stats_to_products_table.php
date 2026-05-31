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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('avg_rating', 3, 2)->default(0)->after('is_featured');
            $table->integer('review_count')->default(0)->after('avg_rating');

            $table->index(['avg_rating', 'review_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['avg_rating', 'review_count']);
            $table->dropColumn(['avg_rating', 'review_count']);
        });
    }
};
