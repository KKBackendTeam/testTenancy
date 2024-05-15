<?php

namespace App\Events;

use App\TenancyEvents;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TenancyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $t_id, $event_type, $staff, $description, $applicants, $old, $new;

    public function __construct($t_id, $event_type, $staff, $description, $old, $new)
    {
        $this->t_id = $t_id;
        $this->event_type = $event_type;
        $this->description = $description;
        $this->old = $old;
        $this->new = $new;
    }

    public function broadcastOn()
    {
        return [];
    }
}
