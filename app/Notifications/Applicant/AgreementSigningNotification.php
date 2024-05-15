<?php

namespace App\Notifications\Applicant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AgreementSigningNotification extends Notification
{
    use Queueable;

    protected $app_info, $tenancyInformation;

    public function __construct($tenancyInformation, $data)
    {
        $this->tenancyInformation = $tenancyInformation;
        $this->app_info = $data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'heading' => $this->tenancyInformation['reference'] . ' - ' . $this->app_info->applicantbasic['app_name'],
            'message' => 'Reference Complete - Please sign the Tenancy Agreement',
            'id' => 0,
            'type' => 'agreement_signing',
            'applicant_id' => 0,
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 11
        ];
    }
}
