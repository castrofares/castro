<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id_1',
        'car_id_2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car1()
    {
        return $this->belongsTo(Car::class, 'car_id_1');
    }

    public function car2()
    {
        return $this->belongsTo(Car::class, 'car_id_2');
    }
}
