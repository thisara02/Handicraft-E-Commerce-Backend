<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('business_name')->nullable();
            $table->string('mobile_number');
            $table->string('address');
            $table->string('nic');
            $table->string('email')->unique();
            $table->text('product_description');
            $table->json('product_types');
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};

