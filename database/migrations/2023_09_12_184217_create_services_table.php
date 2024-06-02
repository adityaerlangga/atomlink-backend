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
            $table->string('service_duration', 255);

            // PENGATURAN LANJUTAN

            // INVENTORY
            $table->boolean('is_using_inventory')->default(false); // bikin table inventory_used
            // $table->string('inventory_codes', 1000)->nullable();

            // MINIMUM ORDER QUANTITY
            $table->boolean('is_minimum_order_quantity')->default(false);
            $table->string('minimum_order_quantity_regular', 255)->nullable();
            $table->string('minimum_order_quantity_deposits', 255)->nullable();

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
