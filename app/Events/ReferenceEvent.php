<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReferenceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $t_id, $event_type, $staff, $description, $applicants, $old, $new, $applicantEmail;

    public function __construct($t_id, $event_type, $staff, $description, $old, $new, $applicantEmail)
    {
        $this->t_id = $t_id;
        $this->event_type = $event_type;
        $this->description = $description;
        $this->old = $old;
        $this->new = $new;
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
