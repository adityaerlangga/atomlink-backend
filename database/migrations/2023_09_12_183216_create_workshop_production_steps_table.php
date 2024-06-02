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
        Schema::create('workshop_production_steps', function (Blueprint $table) {
            $table->id();
            $table->string('workshop_production_step_code', 255)->unique()->index();
            $table->string('workshop_code', 255)->index(); // FROM WORKSHOP TABLE
            $table->boolean('workshop_label')->default(false);
            $table->boolean('workshop_sorting')->default(false);
            $table->boolean('workshop_cleaning')->default(false);
            $table->boolean('workshop_spotting')->default(false);
            $table->boolean('workshop_detailing')->default(false);
            $table->boolean('workshop_washing')->default(false);
            $table->boolean('workshop_drying')->default(false);
            $table->boolean('workshop_ironing')->default(false);
            $table->boolean('workshop_extra_ironing')->default(false);
            $table->boolean('workshop_folding')->default(false);
            $table->boolean('workshop_packaging')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_production_steps');
    }
};
