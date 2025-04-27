<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropWishlistsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('wishlists');
    }

    public function down()
    {
        // Recreate the table if needed (optional)
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unique(['customer_id', 'product_id']);
        });
    }
}