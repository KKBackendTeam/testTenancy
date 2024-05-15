<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicantPrivacyFormIncompleteFlagNotification extends Notification
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
            'message' => 'Tenancy Application is in-complete',
            'id' => $this->app_info['tenancy_id'],
            'type' => 'review_applicant',
            'applicant_id' => $this->app_info['id'],
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 6
        ];
    }
}
