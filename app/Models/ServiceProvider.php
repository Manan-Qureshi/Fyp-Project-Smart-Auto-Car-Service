<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'description',
        'phone',
        'address',
        'latitude',
        'longitude',
        'service_radius_km',
        'logo',
        'is_active',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }

    public function providerServices()
    {
        return $this->hasMany(ProviderService::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'provider_services')
                    ->withPivot('is_available')
                    ->withTimestamps();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * Average rating attribute.
     */
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * Calculate distance from given lat/lng using Haversine (in km).
     */
    public static function nearbyProviders($lat, $lng)
    {
        $haversine = "(6371 * ACOS(
            COS(RADIANS($lat)) * COS(RADIANS(latitude)) *
            COS(RADIANS(longitude) - RADIANS($lng)) +
            SIN(RADIANS($lat)) * SIN(RADIANS(latitude))
        ))";

        return static::selectRaw("*, {$haversine} AS distance")
            ->where('is_active', true)
            ->orderBy('distance', 'asc')
            ->get();
    }
}
