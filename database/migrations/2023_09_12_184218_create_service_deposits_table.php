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
        Schema::create('service_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('service_deposit_code', 255)->unique()->index();
            $table->string('outlet_code', 255)->index(); // FROM OUTLETS TABLE
            $table->string('service_code', 255)->index(); // FROM SERVICES TABLE
            $table->string('service_deposit_name', 255);
            $table->integer('service_deposit_quota');
            $table->integer('service_deposit_discount_percentage')->default(0);
            $table->integer('service_deposit_price');
            $table->enum('service_deposit_period_type', ['UNLIMITED', 'ACTIVE_PERIOD'])->default('UNLIMITED');
            $table->integer('service_deposit_active_period_days')->nullable();
            $table->enum('service_deposit_active_period_type', ['ACCUMULATION', 'OLDEST', 'NEWEST'])->nullable();
            $table->enum('service_deposit_expired_action', ['BURN', 'ROLL_UP'])->nullable();
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
        Schema::dropIfExists('service_deposits');
    }
};
