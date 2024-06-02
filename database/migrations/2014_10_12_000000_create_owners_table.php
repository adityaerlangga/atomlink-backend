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
        Schema::create('owners', function (Blueprint $table) {
            $table->id();

            $table->string('owner_code', 255)->unique()->index();
            $table->string('owner_name', 255)->nullable();
            $table->bigInteger('owner_balance')->default(0);
            $table->string('city_code', 255)->index()->nullable(); // FROM VARIABLE_CITIES TABLE
            $table->string('owner_whatsapp_number', 255)->unique();
            $table->string('owner_otp', 255)->nullable();
            $table->timestamp('owner_otp_expired_at')->nullable();
            $table->string('owner_email', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
