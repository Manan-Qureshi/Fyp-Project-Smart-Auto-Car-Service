<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UpcomingServiceReminder extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $serviceName = $this->booking->service?->name ?? 'Your service';
        $bookingTime = $this->booking->appointment_time?->format('h:i A') ?? '-';

        return [
            'title'      => 'Next Service Alert',
            'message'    => "It is time to start your next booked service: \"$serviceName\" scheduled at $bookingTime. Please begin now!",
            'booking_id' => $this->booking->id,
            'icon'       => 'fa-clock',
            'color'      => 'warning',
        ];
    }
}
