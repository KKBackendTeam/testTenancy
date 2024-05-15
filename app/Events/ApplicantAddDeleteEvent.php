<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ApplicantAddDeleteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $applicantInformation, $event_type, $description, $actionFor,$applicantEmail;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($applicantInformation, $event_type, $description, $actionFor, $applicantEmail)
    {
        $this->applicantInformation = $applicantInformation;
        $this->event_type = $event_type;
        $this->description = $description;
        $this->actionFor = $actionFor;
        $this->applicantEmail = $applicantEmail;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
