<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\TenancyEvents;

class ReviewReferenceEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $actionArray = ['3' => 'Declined', '4' => 'Accepted', '5' => 'Need more detail'];
        $items = [];
        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = JWTAuth::parseToken()->authenticate()->agency_id;
        $tenancy_event->tenancy_id = $event->t_id;
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = JWTAuth::parseToken()->authenticate()->name . " " . JWTAuth::parseToken()->authenticate()->l_name;
        $tenancy_event->applicants = $event->applicantEmail;
        $items[] = '<p>Action: ' . $actionArray[$event->referenceStatus] . ',</p><p>Message: ' . $event->referenceDetail . '</p>';
        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = now();
        $tenancy_event->save();
    }
}
