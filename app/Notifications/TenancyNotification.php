<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TenancyNotification extends Notification
{
    use Queueable;

    public $tenancyInformation, $tenancyStatus;

    public $tenancyStatusArray = [
        '', 'Pending', 'Hold', 'Awaiting Review', 'Failed Review',
        'Awaiting TA Signing', 'Let', 'Rolling', 'In progress', 'Expired', 'Cancelled', 'Completed',
        'Stalled at Pending', 'Stalled at Hold', 'Stalled at Awaiting Review', 'Stalled at Failed Review',
        'Stalled at Awaiting Signing', 'Awaiting TA Sending', 'Awaiting TA Review'
    ];

    public function __construct($tenancyInformation, $tenancyStatus)
    {
        $this->tenancyInformation = $tenancyInformation;
        $this->tenancyStatus = $tenancyStatus;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        if ($this->tenancyStatus == 20) {
            $message = 'All Applicants signed- Review Tenancy Agreement';
            $type = 'review_agreement';
        } else {
            $message = 'Status change: ' . $this->tenancyStatusArray[$this->tenancyStatus];
            $type = 'review_tenancy';
        }
        return [
            'heading' => $this->tenancyInformation['reference'],
            'message' => $message,
            'id' => $this->tenancyInformation['id'],
            'type' =>  $type,
            'applicant_id' => 0,
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 4
        ];
    }
}
