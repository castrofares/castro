<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'car_id',
        'title',
        'description',
        'discount_percentage',
        'discount_amount',
        'valid_from',
        'valid_until',
        'is_active',
        'banner_image',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
