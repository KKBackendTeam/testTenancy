<?php

namespace App\Notifications\Applicant;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationCompleteNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $notificationFor, $tenancyData;

    public function __construct($notificationFor, $tenancyData)
    {
        $this->notificationFor = $notificationFor;
        $this->tenancyData = $tenancyData;
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
        if ($this->notificationFor == 1) {   //creator
            $id = $this->tenancyData['id'];
            $type = "tenancy_review";
        } else {  //applicant
            $id = '';
            $type = "application_completed";
        }
        return [
            'heading' => $this->tenancyData['reference'],
            'message' => 'Congratulations - Your Tenancy Application is complete!',
            'id' => $id,
            'type' => $type,
            'applicant_id' => 0,
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 12
        ];
    }
}
