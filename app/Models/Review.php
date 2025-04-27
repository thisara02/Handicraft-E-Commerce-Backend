<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'review',
        'rating',
    ];

    // Relationship with Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}