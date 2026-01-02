<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'period_from',
        'period_to',
        'data',
        'status',
        'sent_to',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_to' => 'array',
        'sent_at' => 'datetime',
        'period_from' => 'date',
        'period_to' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
