<?php

namespace App\Notifications\Agency;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicantPrivacyFormCompletedNotification extends Notification
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
            'message' => 'Applicant has completed their Tenancy Application',
            'id' => $this->app_info['tenancy_id'],
            'type' => 'review_applicant',
            'applicant_id' => $this->app_info['id'],
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 5
        ];
    }
}
