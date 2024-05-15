<?php

namespace App\Notifications\Agency;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreditNotification extends Notification
{
    use Queueable;

    public $requestData, $agencyData, $notifyTo;

    public function __construct($requestData, $agencyData, $notifyTo)
    {
        $this->requestData = $requestData;
        $this->agencyData = $agencyData;
        $this->notifyTo = $notifyTo;
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
        if ($this->notifyTo == 1) { //notify admin to agency
            $heading = "Credit Added";
            $message = "Added " . $this->requestData['credit'] . " credits";
        } else {
            $heading = $this->agencyData['name'];  //notify agency to admin
            $message = 'Agency has requested ' . $this->requestData['credit'] . ' more credits';
        }
        return [
            'heading' => $heading,
            'message' => $message,
            'id' => $this->agencyData['id'],
            'type' => 'buy_credit',
            'applicant_id' => 0,
            'reference_id' => 0,
            'reference_type' => null,
            'icon_number' => 2
        ];
    }
}
