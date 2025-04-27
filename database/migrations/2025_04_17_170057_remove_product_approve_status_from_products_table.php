<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveProductApproveStatusFromProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_approve_status'); // Drop the column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_approve_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status'); // Re-add the column if rolling back
        });
    }
}