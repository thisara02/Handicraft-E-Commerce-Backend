<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWishlistTable extends Migration
{
    public function up()
    {
        Schema::create('wishlist', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('customer_id'); // Foreign key to customers table
            $table->unsignedBigInteger('product_id');  // Foreign key to products table
            $table->timestamps(); // Created_at and updated_at timestamps

            // Add foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Ensure unique combination of customer_id and product_id
            $table->unique(['customer_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wishlist');
    }
}
