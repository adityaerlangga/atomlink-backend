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
        // BELUM FIX DATABASE INI, DITINGGAL AJA DULU

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 255)->unique()->index();
            $table->string('outlet_code', 255)->nullable();

            // $table->string('inventory_code', 255)->nullable();



            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
