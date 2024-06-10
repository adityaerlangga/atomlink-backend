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
        Schema::create('system_invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->longText('system_invoice_template_transaction');
            $table->longText('system_invoice_template_deposit_purchase');
            $table->longText('system_invoice_template_e_payment');
            $table->longText('system_invoice_template_pick_up_customer');
            $table->longText('system_invoice_template_delivery_customer');
            $table->longText('system_invoice_template_paylink_purchase');
            $table->longText('system_invoice_template_membership');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_invoice_templates');
    }
};
