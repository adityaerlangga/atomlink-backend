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
        Schema::create('variable_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_code')->index();
            $table->string('bank_name');
            $table->string('bank_logo')->nullable();
            $table->string('bank_account_number');
            $table->string('bank_account_holder');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variable_banks');
    }
};
