<?php

namespace App\Notifications\Agency;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReferenceCompletedFormNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $app_info, $tenancyInformation, $ref_notifyData, $ref_type;

    public function __construct($tenancyInformation, $app, $ref, $ref_type)
    {
        $this->tenancyInformation = $tenancyInformation;
        $this->app_info = $app;
        $this->ref_notifyData = $ref;
        $this->ref_type = $ref_type;
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
        return [
            'heading' => $this->tenancyInformation['reference'] . ' - ' . $this->app_info->applicantbasic['app_name'],
            'message' => $this->ref_type . '_Reference completed',
            'id' => $this->app_info['tenancy_id'],
            'type' => 'review_reference',
            'applicant_id' => $this->app_info['id'],
            'reference_id' => $this->ref_notifyData['id'],
            'reference_type' => $this->ref_type,
            'icon_number' => 7
        ];
    }
}
