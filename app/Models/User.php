<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'address',
        'cnic',
        'experience_years',
        'google_id',
        'avatar',
        'car_model_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function hasRole($role)
    {
        $myRole = $this->role === 'user' ? 'customer' : $this->role;
        return $myRole === $role;
    }

    public function isAdmin()    { return $this->role === 'admin'; }
    public function isProvider() { return $this->role === 'provider'; }
    public function isWorker()   { return $this->role === 'worker'; }
    public function isCustomer() { return in_array($this->role, ['customer', 'user']); }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class, 'user_id');
    }
}
