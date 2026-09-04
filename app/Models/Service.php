<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'category',
        'base_price',
        'duration_minutes',
        'description',
        'image',
        'car_type_id',
        'car_model_id',
    ];

    public function carType()
    {
        return $this->belongsTo(CarType::class);
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function providerServices()
    {
        return $this->hasMany(ProviderService::class);
    }

    public function providers()
    {
        return $this->belongsToMany(ServiceProvider::class, 'provider_services')
                    ->withPivot('is_available')
                    ->withTimestamps();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
