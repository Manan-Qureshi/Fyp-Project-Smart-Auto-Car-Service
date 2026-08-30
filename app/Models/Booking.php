<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'service_ids',
        'service_provider_id',
        'car_model_id',
        'provider_worker_id',
        'appointment_time',
        'duration_minutes',
        'notes',
        'status',
        'payment_status',
        'final_price',
    ];

    protected $casts = [
        'appointment_time' => 'datetime',
        'service_ids'      => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'provider_worker_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }
}
