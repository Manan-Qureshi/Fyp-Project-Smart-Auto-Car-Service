<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Notifications\UpcomingServiceReminder;
use Carbon\Carbon;

class SendUpcomingServiceReminders extends Command
{
    protected $signature   = 'notify:upcoming-services';
    protected $description = 'Send reminder notifications to workers whose next service is starting now.';

    public function handle(): void
    {
        $now    = Carbon::now();
        $window = $now->copy()->addMinutes(5);

        // Find all 'assigned' bookings starting within the next 5 minutes
        $bookings = Booking::with(['worker.user'])
            ->where('status', 'assigned')
            ->whereBetween('appointment_time', [$now, $window])
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->worker && $booking->worker->user) {
                $booking->worker->user->notify(new UpcomingServiceReminder($booking));
                $this->info("Notified worker for booking #{$booking->id}");
            }
        }
    }
}
