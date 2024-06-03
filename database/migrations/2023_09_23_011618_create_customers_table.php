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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 255)->unique()->index();
            $table->string('outlet_code', 255); // FROM OUTLET TABLE
            $table->string('customer_name', 255);
            $table->string('customer_gender', 255);
            $table->string('customer_title', 255); // SAPAAN (KAK, GAN, SAUDARI, MBAK, BU, SOBAT, SAHABAT, NONA, NYONYA, YTH, IBU)
            $table->string('customer_whatsapp_number', 255)->nullable();

            $table->boolean('is_customer_have_addresses')->default(false); // GET FROM CUSTOMER_ADDRESSES TABLE

            // instansi, tanggal_lahir, agama, email
            $table->string('customer_institution', 255)->nullable();
            $table->string('customer_birth_date', 255)->nullable();
            $table->string('customer_religion', 255)->nullable(); // untuk promo hari-hari agama (ISLAM, KRISTEN, KATOLIK, HINDU, BUDDHA, KONGHUCU, DLL)
            $table->string('customer_email', 255)->nullable();

            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
