<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade'); // Vendor ID
            $table->string('full_name');
            $table->string('email_address');
            $table->string('phone_number');
            $table->string('country');
            $table->string('street_address');
            $table->string('town_city');
            $table->decimal('total_amount', 8, 2);
            $table->string('payment_status')->default('successful'); // pending, successful, unsuccessful
            $table->text('order_summary')->nullable(); // JSON-encoded string of items
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}