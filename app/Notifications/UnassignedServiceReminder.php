<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UnassignedServiceReminder extends Notification
{
    use Queueable;

    public function __construct(public Booking|int $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->booking instanceof Booking) {
            $serviceName = $this->booking->service?->name ?? 'Service';
            $time        = $this->booking->appointment_time?->format('h:i A') ?? '-';
            return [
                'title'      => 'Unassigned Service Alert',
                'message'    => "Service \"$serviceName\" scheduled at $time (starts in 20 mins) has no worker assigned! Please assign a worker immediately.",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-user-plus',
                'color'      => 'danger',
            ];
        }

        return [
            'title'   => 'Unassigned Services Alert',
            'message' => "You have {$this->booking} upcoming service(s) that still need a worker assigned. Please assign workers before the service time!",
            'count'   => $this->booking,
            'icon'    => 'fa-user-plus',
            'color'   => 'warning',
        ];
    }
}
