<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_settings', 'reward_description')) {
                $table->text('reward_description')->nullable()->after('reward_value');
            }

            if (!Schema::hasColumn('loyalty_settings', 'reward_image')) {
                $table->string('reward_image')->nullable()->after('reward_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_settings', 'reward_image')) {
                $table->dropColumn('reward_image');
            }

            if (Schema::hasColumn('loyalty_settings', 'reward_description')) {
                $table->dropColumn('reward_description');
            }
        });
    }
};
