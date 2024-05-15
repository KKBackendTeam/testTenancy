<?php

namespace App\Listeners;

use App\Models\TenancyEvents;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class SendEmailEventListener
{

    public function __construct()
    {
        #..
    }

    public function handle($event)
    {
        $items = [];
        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = $event->agencyId;
        $tenancy_event->tenancy_id = $event->t_id;
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->applicants = $event->applicant_email;
        if ($event->event_type == 'Custom email') {
            $tenancy_event->creator = JWTAuth::parseToken()->authenticate()->name . " " . JWTAuth::parseToken()->authenticate()->l_name;
        } else {
            $tenancy_event->creator = 'Sent by System';
        }
        $items[] = $event->emailMessage;
        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = Carbon::now();
        $tenancy_event->save();
    }
}
