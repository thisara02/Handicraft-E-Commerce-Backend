<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'status',
        'category',
        'price',
        'images',
    ];

    // Cast images from JSON to array automatically
    protected $casts = [
        'images' => 'array',
    ];

    // Define the relationship to the Vendor model
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }
}
