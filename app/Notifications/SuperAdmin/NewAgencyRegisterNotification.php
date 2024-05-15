<?php

namespace App\Notifications\SuperAdmin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewAgencyRegisterNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $agencyData;

    public function __construct($agencyData)
    {
        $this->agencyData = $agencyData;
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
            'heading' => 'New Agency Registered',
            'message' => $this->agencyData['name'] . ' has registered',
            'id' => $this->agencyData['id'],
            'type' => 'new_agency_registered',
            'applicant_id' => 0,
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 1
        ];
    }
}
