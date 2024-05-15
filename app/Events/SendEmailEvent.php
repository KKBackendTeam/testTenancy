<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendEmailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $t_id, $event_type, $description, $applicants, $applicant_email, $emailMessage, $agencyId;

    public function __construct($t_id, $event_type, $description, $applicant_email, $emailMessage, $agencyId)
    {
        $this->t_id = $t_id;
        $this->event_type = $event_type;
        $this->description = $description;
        $this->applicant_email = $applicant_email;
        $this->emailMessage = $emailMessage;
        $this->agencyId = $agencyId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
