<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'primary_phone',
        'secondary_phone',
        'email',
        'whatsapp',
        'address',
        'city',
        'business_hours',
        'social_media',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'social_media' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
