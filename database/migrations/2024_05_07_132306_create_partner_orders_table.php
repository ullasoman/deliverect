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
        Schema::create('partner_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->string('order_id');
            $table->string('job_id');
            $table->string('account');
            $table->string('pickup_time');
            $table->string('transport_type');
            $table->string('channel_order_display_id');
            $table->string('delivery_time');
            $table->string('package_size');
            $table->string('order_description');
            $table->boolean('order_is_already_paid');
            $table->decimal('driver_tip');
            $table->decimal('amount');
            $table->tinyinteger('payment_type');
            $table->timestamps();

            $table->foreign('partner_id')
            ->references('id')
            ->on('order_partners')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_orders');
    }
};
