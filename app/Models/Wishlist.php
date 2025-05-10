<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlist';
    // Disable timestamps if not needed
    public $timestamps = true;

    // Define fillable fields
    protected $fillable = [
        'customer_id',
        'product_id',
    ];

    // Relationship: Wishlist belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship: Wishlist belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}