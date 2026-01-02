<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'delivery_company_id',
        'pickup_address',
        'delivery_address',
        'status',
        'pickup_date',
        'delivery_date',
        'proof_of_delivery',
        'delivery_cost',
        'notes',
    ];

    protected $casts = [
        'pickup_date' => 'datetime',
        'delivery_date' => 'datetime',
    ];

    // العلاقات
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function deliveryCompany()
    {
        return $this->belongsTo(User::class, 'delivery_company_id');
    }
}
