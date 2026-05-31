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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('last_modified_at')->nullable()->after('updated_at');
            $table->string('last_modified_by')->nullable()->after('last_modified_at');
            $table->json('change_log')->nullable()->after('last_modified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['last_modified_at', 'last_modified_by', 'change_log']);
        });
    }
};
