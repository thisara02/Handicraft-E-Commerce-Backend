<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    public function up()
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id'); // Or user_id if applicable
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Optional: Add a unique constraint to prevent duplicate entries
            $table->unique(['customer_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart');
    }
}