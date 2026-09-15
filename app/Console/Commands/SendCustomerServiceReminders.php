<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Notifications\CustomerServiceReminder;
use Carbon\Carbon;

class SendCustomerServiceReminders extends Command
{
    protected $signature   = 'notify:customer-service-reminders';
    protected $description = 'Send reminder notifications to customers 20 minutes before their service starts.';

    public function handle(): void
    {
        $now    = Carbon::now();
        $target = $now->copy()->addMinutes(20);

        // Find confirmed/assigned/accepted bookings starting within 20 minutes
        $bookings = Booking::with(['user', 'service'])
            ->whereIn('status', ['confirmed', 'assigned', 'accepted'])
            ->whereBetween('appointment_time', [$now, $target])
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->user) {
                $booking->user->notify(new CustomerServiceReminder($booking));
                $this->info("Notified customer for booking #{$booking->id} starting in 20 minutes.");
            }
        }
    }
}
