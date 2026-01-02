<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class CarImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'image_path',
    ];

    // Append URL to JSON
    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        if ($this->image_path) {
            // استخدام BACKEND_URL من .env أو القيمة الافتراضية
            $backendUrl = env('BACKEND_URL', 'http://localhost:8000');
            return $backendUrl . '/storage/' . $this->image_path;
        }
        return null;
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}

