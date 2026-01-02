<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerContactInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'secondary_phone',
        'email',
        'whatsapp',
        'address',
        'city',
        'social_media',
        'notes',
    ];

    protected $casts = [
        'social_media' => 'array',
    ];

    // Relationship with User (Seller)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
