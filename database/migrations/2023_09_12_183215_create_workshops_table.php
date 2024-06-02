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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('workshop_code', 255)->unique()->index();
            $table->string('owner_code', 255)->index(); // FROM OWNERS TABLE
            $table->string('workshop_name', 255);
            $table->string('workshop_phone_number', 255);
            $table->string('city_code', 255)->index(); // FROM VARIABLE_CITIES TABLE
            $table->string('workshop_address', 255);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
