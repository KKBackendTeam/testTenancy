<?php

namespace App\Notifications\Applicant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicantNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $referenceData, $ref_type, $app_info, $tenancyInformation;

    public function __construct($tenancyInformation, $app_info, $referenceData, $ref_type)
    {
        $this->referenceData = $referenceData;
        $this->ref_type = $ref_type;
        $this->tenancyInformation = $tenancyInformation;
        $this->app_info = $app_info;
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
        //notify applicant to your reference fills their form
        return [
            'heading' => $this->tenancyInformation['reference'] . ' - ' . $this->app_info->applicantbasic['app_name'],
            'message' => $this->ref_type . '_Reference Completed',
            'id' => $this->referenceData['id'],
            'type' => 'reference_form_completed',
            'applicant_id' => $this->app_info['id'],
            'reference_id' => $this->referenceData['id'],
            'reference_type' => $this->ref_type,
            'icon_number' => 7
        ];
    }
}
