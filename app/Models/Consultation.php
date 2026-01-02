<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'consultant_id',
        'car_id',
        'question',
        'response',
        'status',
        'scheduled_at',
        'commission',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // العلاقات
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
