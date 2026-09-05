<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UnassignedServiceReminder extends Notification
{
    use Queueable;

    public function __construct(public int $count)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Unassigned Services Alert',
            'message' => "You have {$this->count} upcoming service(s) that still need a worker assigned. Please assign workers before the service time!",
            'count'   => $this->count,
            'icon'    => 'fa-user-plus',
            'color'   => 'warning',
        ];
    }
}
