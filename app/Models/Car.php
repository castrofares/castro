<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'price',
        'description',
        'status',
        'user_id',
        'year',
        'transmission',
        'fuel_type',
        'color',
        'location',
        'availability',
        'mileage',
        'doors',
        'seats',
        'engine_size',
        'rental_price_hourly',
        'rental_price_daily',
        'rental_price_weekly',
        'rental_price_monthly',
        'is_featured',
        'views_count',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'cart', 'car_id', 'user_id')
        ->withTimestamps();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
}
