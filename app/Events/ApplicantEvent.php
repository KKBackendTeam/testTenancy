<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ApplicantEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $t_id, $event_type, $staff, $description, $applicants, $old, $new, $oldApp,$newApp, $applicantEmail;

    public function __construct($t_id, $event_type, $staff, $description, $old, $new,$oldApp,$newApp, $applicantEmail)
    {
        $this->t_id = $t_id;
        $this->event_type = $event_type;
        $this->description = $description;
        $this->old = $old;
        $this->new = $new;
        $this->oldApp = $oldApp;
        $this->newApp = $newApp;
        $this->applicantEmail = $applicantEmail;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
