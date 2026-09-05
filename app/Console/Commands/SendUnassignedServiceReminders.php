<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\ServiceProvider;
use App\Notifications\UnassignedServiceReminder;
use Carbon\Carbon;

class SendUnassignedServiceReminders extends Command
{
    protected $signature   = 'notify:unassigned-services';
    protected $description = 'Remind providers hourly about today\'s bookings that still have no worker assigned.';

    public function handle(): void
    {
        $today = Carbon::today();

        // Find all providers with confirmed upcoming bookings that have no worker yet
        $providers = ServiceProvider::with(['user', 'bookings' => function ($q) use ($today) {
            $q->whereDate('appointment_time', '>=', $today)
              ->whereIn('status', ['confirmed', 'accepted'])
              ->whereNull('provider_worker_id');
        }])->get();

        foreach ($providers as $provider) {
            $count = $provider->bookings->count();
            if ($count > 0 && $provider->user) {
                $recentNotifExists = $provider->user->unreadNotifications()
                    ->where('type', 'App\Notifications\UnassignedServiceReminder')
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->exists();
                if (!$recentNotifExists) {
                    $provider->user->notify(new UnassignedServiceReminder($count));
                    $this->info("Notified provider #{$provider->id} about {$count} unassigned booking(s).");
                }
            }
        }
    }
}
