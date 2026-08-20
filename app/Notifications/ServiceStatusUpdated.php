<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ServiceStatusUpdated extends Notification
{
    use Queueable;

    /**
     * @param Booking $booking
     * @param string  $event  One of: started | assigned | completed | cancelled_by_customer | worker_finished
     */
    public function __construct(public Booking $booking, public string $event)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $serviceName = $this->booking->service?->name ?? 'Service';
        $bookingDate = $this->booking->appointment_time?->format('d M Y, h:i A') ?? '-';

        return match ($this->event) {
            'started' => [
                'title'   => 'Service Started',
                'message' => "Your service \"$serviceName\" has now begun.",
                'booking_id' => $this->booking->id,
                'icon'    => 'fa-play-circle',
                'color'   => 'info',
            ],
            'assigned' => [
                'title'   => 'Worker Assigned',
                'message' => "A worker has been assigned to your service \"$serviceName\" on $bookingDate.",
                'booking_id' => $this->booking->id,
                'icon'    => 'fa-user-check',
                'color'   => 'primary',
            ],
            'completed' => [
                'title'   => 'Service Completed',
                'message' => "Your service \"$serviceName\" is complete!",
                'booking_id' => $this->booking->id,
                'icon'    => 'fa-check-circle',
                'color'   => 'success',
            ],
            'cancelled_by_customer' => [
                'title'   => 'Booking Cancelled',
                'message' => "The booking for \"$serviceName\" on $bookingDate has been cancelled by the customer.",
                'booking_id' => $this->booking->id,
                'icon'    => 'fa-times-circle',
                'color'   => 'danger',
            ],
            'worker_finished' => [
                'title'   => 'Worker Finished Job',
                'message' => "A worker has completed the service \"$serviceName\" for booking #" . $this->booking->id . ".",
                'booking_id' => $this->booking->id,
                'icon'    => 'fa-flag-checkered',
                'color'   => 'success',
            ],
            'booking_received' => [
                'title'   => 'New Booking Received',
                'message' => "A new booking has been made for \"$serviceName\" on $bookingDate.",
                'booking_id' => $this->booking->id,
                'icon'    => 'fa-calendar-plus',
                'color'   => 'primary',
            ],
            default => [
                'title'   => 'Booking Update',
                'message' => "Your booking #" . $this->booking->id . " has been updated.",
                'booking_id' => $this->booking->id,
                'icon'    => 'fa-bell',
                'color'   => 'secondary',
            ],
        };
    }
}
