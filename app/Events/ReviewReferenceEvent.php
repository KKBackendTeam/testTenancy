<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReviewReferenceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $t_id, $event_type, $staff, $description, $referenceStatus, $referenceDetail, $applicantEmail;

    public function __construct($t_id, $event_type, $description, $referenceStatus, $referenceDetail, $applicantEmail)
    {
        $this->t_id = $t_id;
        $this->event_type = $event_type;
        $this->description = $description;
        $this->referenceStatus = $referenceStatus;
        $this->referenceDetail = $referenceDetail;
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
