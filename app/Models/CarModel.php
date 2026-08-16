<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['car_type_id', 'name', 'price_modifier'];

    public function carType()
    {
        return $this->belongsTo(CarType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
