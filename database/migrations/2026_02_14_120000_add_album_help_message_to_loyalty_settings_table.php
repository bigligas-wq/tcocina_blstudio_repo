<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_settings', 'album_help_message')) {
                $table->text('album_help_message')->nullable()->after('reward_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_settings', 'album_help_message')) {
                $table->dropColumn('album_help_message');
            }
        });
    }
};
