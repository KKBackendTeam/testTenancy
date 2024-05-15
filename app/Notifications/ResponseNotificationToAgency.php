<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResponseNotificationToAgency extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $appData, $tenancyInformation;

    public function __construct($tenancyInformation, $appData)
    {
        $this->tenancyInformation = $tenancyInformation;
        $this->appData = $appData;
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
        //notify agency to send maximum Responses to the Applicant
        return [
            'heading' => $this->tenancyInformation['reference'] . ' - ' . $this->appData->applicantbasic['app_name'],
            'message' => 'Maximum chases sent to Applicant',
            'id' => $this->appData['tenancy_id'],
            'type' => 'review_applicant',
            'applicant_id' => $this->appData['id'],
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 8
        ];
    }
}
