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
     * @param string  $event  One of: started | assigned | completed | cancelled_by_customer | booking_received | assigned_to_worker | payment_received | payment_refunded
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
        $amount      = 'PKR ' . number_format($this->booking->final_price ?? 0);

        return match ($this->event) {
            'started' => [
                'title'      => 'Service Started',
                'message'    => "Your service \"$serviceName\" has now begun.",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-play-circle',
                'color'      => 'info',
            ],
            'assigned' => [
                'title'      => 'Worker Assigned',
                'message'    => "A worker has been assigned to your service \"$serviceName\" on $bookingDate.",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-user-check',
                'color'      => 'primary',
            ],
            'completed' => [
                'title'      => 'Service Completed',
                'message'    => "Your service \"$serviceName\" is complete!",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-check-circle',
                'color'      => 'success',
            ],
            'cancelled_by_customer' => [
                'title'      => 'Booking Cancelled',
                'message'    => "The booking for \"$serviceName\" on $bookingDate has been cancelled by the customer.",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-times-circle',
                'color'      => 'danger',
            ],
            'booking_received' => [
                'title'      => 'New Booking Received',
                'message'    => "A new booking has been made for \"$serviceName\" on $bookingDate.",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-calendar-plus',
                'color'      => 'primary',
            ],
            'assigned_to_worker' => [
                'title'      => 'New Task Assigned',
                'message'    => "You have been assigned to service \"$serviceName\" scheduled on $bookingDate.",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-hard-hat',
                'color'      => 'warning',
            ],
            'payment_received' => [
                'title'      => 'Payment Received',
                'message'    => "Payment of $amount received for booking #" . $this->booking->id . " (\"$serviceName\").",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-money-bill-wave',
                'color'      => 'success',
            ],
            'payment_refunded' => [
                'title'      => 'Payment Refunded',
                'message'    => "Refund of $amount issued for cancelled booking #" . $this->booking->id . " (\"$serviceName\").",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-undo',
                'color'      => 'warning',
            ],
            default => [
                'title'      => 'Booking Update',
                'message'    => "Your booking #" . $this->booking->id . " has been updated.",
                'booking_id' => $this->booking->id,
                'icon'       => 'fa-bell',
                'color'      => 'secondary',
            ],
        };
    }
}

