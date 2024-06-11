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
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->string('rack_code', 255)->unique()->index();
            $table->string('outlet_code', 255)->index(); // FROM OUTLET TABLE
            $table->string('rack_category_code', 255)->index()->nullable(); // FROM RACK_CATEGORIES TABLE
            $table->string('rack_name', 255);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racks');
    }
};
