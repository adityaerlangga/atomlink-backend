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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('customer_address_code', 255)->unique()->index();
            $table->string('customer_code', 255)->index(); // FROM CUSTOMER TABLE
            $table->string('customer_full_address', 255);

            $table->string('customer_address_latitude', 255)->nullable();
            $table->string('customer_address_longitude', 255)->nullable();
            $table->string('customer_address_location_name', 255)->nullable(); // REV GEOLOCATION
            $table->string('customer_address_label', 255)->nullable();

            $table->boolean('is_customer_address_primary')->default(false);

            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
