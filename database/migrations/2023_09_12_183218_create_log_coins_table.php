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
        Schema::create('log_coins', function (Blueprint $table) {
            $table->id();
            $table->string('log_coin_code', 255)->unique()->index();
            $table->string('owner_code', 255)->index(); // FROM OWNERS TABLE
            $table->string('log_coin_type', 255); // INCOME OR EXPENSE
            $table->string('log_coin_category', 255); // TOPUP, TRANSACTION, WHATSAPP_TRANSACTION, MASS_INVOICE
            $table->string('log_coin_amount', 255);
            $table->string('log_coin_new_balance', 255);
            $table->string('log_coin_description', 255);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_coins');
    }
};
