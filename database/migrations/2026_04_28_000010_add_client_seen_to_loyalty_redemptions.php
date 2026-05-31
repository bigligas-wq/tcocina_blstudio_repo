<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_redemptions', 'client_seen_approved_at')) {
                $table->timestamp('client_seen_approved_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('loyalty_redemptions', 'client_seen_delivered_at')) {
                $table->timestamp('client_seen_delivered_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('loyalty_redemptions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('client_seen_delivered_at');
            }
            if (!Schema::hasColumn('loyalty_redemptions', 'cancelled_reason')) {
                $table->string('cancelled_reason', 255)->nullable()->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            $cols = ['client_seen_approved_at', 'client_seen_delivered_at', 'cancelled_at', 'cancelled_reason'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('loyalty_redemptions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
