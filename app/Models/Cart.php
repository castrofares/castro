<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    protected $fillable = [
        'user_id', // 🔥 السماح بتسجيل user_id تلقائيًا
        'car_id',  // 🔥 السماح بتسجيل car_id تلقائيًا
    ];
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
}
