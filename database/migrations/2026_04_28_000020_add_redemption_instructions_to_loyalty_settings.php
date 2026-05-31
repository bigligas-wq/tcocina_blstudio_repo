<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            $table->text('redemption_instructions')->nullable()->after('album_help_message')->comment('Instrucciones para el cliente sobre como canjear el premio (aparece en el mail de aprobacion)');
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_settings', function (Blueprint $table) {
            $table->dropColumn('redemption_instructions');
        });
    }
};
