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
        Schema::create('product_review_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->enum('change_type', ['created', 'edited', 'status_changed']);
            $table->string('changed_by')->nullable(); // 'user' or 'admin'
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users');
            $table->text('change_notes')->nullable();
            $table->timestamps();

            $table->index('product_review_id');
            $table->index('changed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_history');
    }
};
