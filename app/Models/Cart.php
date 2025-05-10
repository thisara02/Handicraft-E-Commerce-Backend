<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    // Disable timestamps if not needed
    // public $timestamps = false;

    // Define fillable fields

    protected $table = 'cart';

    protected $fillable = [
        'customer_id',
        'product_id',
        'quantity',
    ];

    // Relationship: Cart belongs to a User
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship: Cart belongs to a Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}