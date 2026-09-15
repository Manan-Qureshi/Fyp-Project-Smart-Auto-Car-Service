<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerServiceReminder extends Notification
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
        $serviceName = $this->booking->service?->name ?? 'Your car service';
        $bookingTime = $this->booking->appointment_time?->format('h:i A') ?? 'soon';

        return [
            'title'      => 'Service Reminder: 20 Minutes Ahead',
            'message'    => "Reminder: Your service \"$serviceName\" is scheduled for $bookingTime. Please be prepared!",
            'booking_id' => $this->booking->id,
            'icon'       => 'fa-bell',
            'color'      => 'info',
        ];
    }
}
