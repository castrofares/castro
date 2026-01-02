<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'name',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'target_audience_count',
        'reached_count',
        'budget',
        'spent',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // العلاقات
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
