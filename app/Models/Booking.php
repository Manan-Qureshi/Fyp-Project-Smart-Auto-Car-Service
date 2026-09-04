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

    public function getAvailableWorkers()
    {
        if (!$this->service_provider_id) {
            return collect();
        }

        $allWorkers = Worker::where('service_provider_id', $this->service_provider_id)
            ->where('is_available', true)
            ->get();

        if (!$this->appointment_time) {
            return $allWorkers;
        }

        $duration = $this->duration_minutes ?? 60;
        $slotStart = \Carbon\Carbon::parse($this->appointment_time);
        $slotEnd   = $slotStart->copy()->addMinutes($duration);

        $activeBookings = self::where('service_provider_id', $this->service_provider_id)
            ->where('id', '!=', $this->id)
            ->whereNotNull('provider_worker_id')
            ->whereDate('appointment_time', $slotStart->toDateString())
            ->whereIn('status', ['confirmed', 'accepted', 'assigned', 'in_progress'])
            ->get(['id', 'provider_worker_id', 'appointment_time', 'duration_minutes']);

        $busyWorkerIds = [];
        foreach ($activeBookings as $b) {
            $bStart = \Carbon\Carbon::parse($b->appointment_time);
            $bEnd   = $bStart->copy()->addMinutes($b->duration_minutes ?? 60);

            if ($slotStart->lt($bEnd) && $slotEnd->gt($bStart)) {
                $busyWorkerIds[] = $b->provider_worker_id;
            }
        }

        return $allWorkers->filter(function ($worker) use ($busyWorkerIds) {
            return !in_array($worker->id, $busyWorkerIds) || $worker->id == $this->provider_worker_id;
        });
    }
}

