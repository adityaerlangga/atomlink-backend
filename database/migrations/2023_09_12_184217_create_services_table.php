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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code', 255)->unique()->index();
            $table->string('outlet_code', 255)->index(); // FROM OUTLET TABLE

            $table->string('service_name', 255);
            $table->string('service_price', 255);
            $table->string('unit_code', 255)->index(); // FROM VARIABLE_UNITS TABLE
            $table->string('service_duration_days', 255);
            $table->string('service_duration_hours', 255);

            // ======== PENGATURAN LANJUTAN ======== //

            // KATEGORI LAYANAN
            $table->string('service_category_code', 255)->index()->nullable(); // FROM VARIABLE_SERVICE_CATEGORIES TABLE

            // MINIMUM ORDER QUANTITY
            $table->boolean('is_minimum_order_quantity_active')->default(false);
            $table->string('minimum_order_quantity_regular', 255)->nullable();
            $table->string('minimum_order_quantity_deposit', 255)->nullable();

            // BORONGAN KARYAWAN (FEE PER TRX)
            $table->boolean('is_employees_bonus_fee_active')->default(false);
            $table->string('bonus_fee_labeling', 255)->nullable();
            $table->string('bonus_fee_sorting', 255)->nullable();
            $table->string('bonus_fee_cleaning', 255)->nullable();
            $table->string('bonus_fee_spotting', 255)->nullable();
            $table->string('bonus_fee_detailing', 255)->nullable();
            $table->string('bonus_fee_washing', 255)->nullable();
            $table->string('bonus_fee_drying', 255)->nullable();
            $table->string('bonus_fee_ironing', 255)->nullable();
            $table->string('bonus_fee_extra_ironing', 255)->nullable();
            $table->string('bonus_fee_folding', 255)->nullable();
            $table->string('bonus_fee_packaging', 255)->nullable();

            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
