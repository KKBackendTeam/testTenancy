<?php

namespace App\Listeners;

use App\Models\TenancyEvents;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApplicantAddDeleteEventListener
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
        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = JWTAuth::parseToken()->authenticate()->agency_id;
        $tenancy_event->tenancy_id = $event->applicantInformation['tenancy_id'];
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = JWTAuth::parseToken()->authenticate()->name . " " . JWTAuth::parseToken()->authenticate()->l_name;

        if ($event->actionFor == 'add') {
            $items[] = 'Added a new applicant ' . $event->applicantInformation['email'] . ' to the tenancy.';
        } else {
            $items[] = 'An applicant ' . $event->applicantInformation['email'] . ' was removed from the tenancy';
        }
        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = Carbon::now();
        $tenancy_event->applicants = $event->applicantEmail;
        $tenancy_event->save();
    }
}
