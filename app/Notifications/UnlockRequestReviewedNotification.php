<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UnlockRequestReviewedNotification extends Notification
{

    public function __construct(
        public bool $approved,
        public string $monthLabel,
        public string $monthYmd
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->approved
            ? "Požiadavka na odomknutie výkazu za {$this->monthLabel} bola schválená. Môžete znova upravovať záznamy."
            : "Požiadavka na odomknutie výkazu za {$this->monthLabel} bola zamietnutá.";

        return [
            'type' => 'unlock_request_reviewed',
            'approved' => $this->approved,
            'month_label' => $this->monthLabel,
            'month_ymd' => $this->monthYmd,
            'message' => $message,
        ];
    }
}
