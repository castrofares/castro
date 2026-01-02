<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'seller_id',
        'buyer_id',
        'sale_price',
        'status',
        'sale_date',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'sale_price' => 'decimal:2',
    ];

    // Relationship with Car
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // Relationship with Seller
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Relationship with Buyer
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
