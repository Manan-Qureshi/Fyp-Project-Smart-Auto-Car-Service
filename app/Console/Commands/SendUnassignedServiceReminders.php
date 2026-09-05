<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Notifications\UnassignedServiceReminder;
use Carbon\Carbon;

class SendUnassignedServiceReminders extends Command
{
    protected $signature   = 'notify:unassigned-services';
    protected $description = 'Remind providers 20 minutes before an unassigned service appointment starts.';

    public function handle(): void
    {
        $now = Carbon::now();
        $twentyMinsLater = $now->copy()->addMinutes(20);

        // Find bookings starting in the next 20 minutes with no worker assigned
        $unassignedBookings = Booking::with(['serviceProvider.user', 'service'])
            ->whereIn('status', ['confirmed', 'accepted'])
            ->whereNull('provider_worker_id')
            ->whereBetween('appointment_time', [$now, $twentyMinsLater])
            ->get();

        foreach ($unassignedBookings as $booking) {
            $providerUser = $booking->serviceProvider?->user;
            if ($providerUser) {
                // Check if provider has already received an alert for this specific booking
                $alreadyAlerted = $providerUser->notifications()
                    ->where('type', 'App\Notifications\UnassignedServiceReminder')
                    ->where('data->booking_id', $booking->id)
                    ->exists();

                if (!$alreadyAlerted) {
                    $providerUser->notify(new UnassignedServiceReminder($booking));
                    $this->info("Notified provider user #{$providerUser->id} about unassigned booking #{$booking->id}.");
                }
            }
        }
    }
}
