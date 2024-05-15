<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AgreementEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tenancyId, $event_type, $description, $actionFor, $applicantEmail;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($tenancyId, $event_type, $description, $applicantEmail)
    {
        $this->tenancyId = $tenancyId;
        $this->event_type = $event_type;
        $this->description = $description;
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
