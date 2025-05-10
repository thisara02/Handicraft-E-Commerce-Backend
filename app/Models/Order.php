<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'vendor_id',
        'full_name',
        'email_address',
        'phone_number',
        'country',
        'street_address',
        'town_city',
        'total_amount',
        'shipping_status',
        'payment_status',
        'order_summary',
    ];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    // Cast order_summary as JSON
    protected $casts = [
        'order_summary' => 'array',
    ];
}