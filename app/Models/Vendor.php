<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Vendor extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'full_name', 'business_name', 'mobile_number', 'address', 'nic', 'email',
        'product_description', 'product_types', 'password', 'profile_picture','status','otp',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'product_types' => 'array',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

