<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'service_provider_id',
        'total_amount',
        'commission_rate',
        'commission_amount',
        'provider_earning',
        'stripe_transfer_id',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
