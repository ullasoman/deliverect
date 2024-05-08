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
        Schema::create('partner_order_delivery_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->string('job_id')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->string('company')->nullable();
            $table->string('name')->nullable();
            $table->string('street')->nullable();
            $table->string('street_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('delivery_remarks')->nullable();
            $table->string('phone')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();

            $table->foreign('partner_id')
            ->references('id')
            ->on('order_partners')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->foreign('order_id')
            ->references('id')
            ->on('partner_orders')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_order_delivery_locations');
    }
};
