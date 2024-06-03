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
        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->string('topup_code', 255)->unique()->index();
            $table->string('owner_code', 255)->index(); // FROM OWNERS TABLE
            $table->string('bank_code', 255); // BANK CODE
            $table->string('topup_amount', 255);
            $table->string('topup_amount_unique_code', 255);
            $table->string('topup_status', 255)->default('PENDING');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};
