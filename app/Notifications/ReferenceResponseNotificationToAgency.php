<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReferenceResponseNotificationToAgency extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $appData, $referenceInformation, $tenancyInformation, $responseType;

    public function __construct($tenancyInformation, $referenceInformation, $appData, $responseType)
    {
        $this->tenancyInformation = $tenancyInformation;
        $this->referenceInformation = $referenceInformation;
        $this->appData = $appData;
        $this->responseType = $responseType;
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
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if ($this->responseType == 'guarantorReferences') $responseType = 'Guarantor';
        elseif ($this->responseType == 'employmentReferences') $responseType = 'Employment';
        elseif ($this->responseType == 'landlordReferences') $responseType = 'Landlord';
        return [
            'heading' => $this->tenancyInformation['reference'] . ' - ' . $this->appData->applicantbasic['app_name'],
            'message' => 'Maximum chases sent to ' . $responseType . '_Reference',
            'id' => $this->appData['tenancy_id'],
            'type' => 'review_reference',
            'applicant_id' => $this->appData['id'],
            'reference_id' => $this->referenceInformation['id'],
            'reference_type' => $responseType,
            'icon_number' => 8
        ];
    }
}
