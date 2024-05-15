<?php

namespace App\Notifications\Applicant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AgencyActionOnReferenceNotification extends Notification
{
    use Queueable;

    protected $app_info, $tenancyInformation, $data, $referenceData, $referenceType;

    public function __construct($tenancyInformation, $referenceData, $applicantInformation, $data, $referenceType)
    {
        $this->tenancyInformation = $tenancyInformation;
        $this->referenceData = $referenceData;
        $this->app_info = $applicantInformation;
        $this->data = $data;
        $this->referenceType = $referenceType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'heading' => $this->tenancyInformation['reference'] . ' - ' . $this->app_info->applicantbasic['app_name'],
            'message' => $this->data,
            'id' => 0,
            'type' => 'admin_action_on_reference',
            'applicant_id' => $this->app_info['id'],
            'reference_id' => $this->referenceData['id'],
            'reference_type' => $this->referenceType,
            'icon_number' => 10
        ];
    }
}
