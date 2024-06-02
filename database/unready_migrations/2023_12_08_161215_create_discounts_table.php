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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('discount_code', 255)->unique();
            $table->string('outlet_code', 255);
            $table->string('discount_type', 255); // PERCENTAGE, FIXED_NOMINAL
            $table->bigInteger('discount_amount'); // Jika percentage maka 0-100
            $table->string('discount_name', 255);
            $table->string('discount_description', 255);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
