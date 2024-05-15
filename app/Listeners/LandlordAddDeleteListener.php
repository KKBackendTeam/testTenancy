<?php

namespace App\Listeners;

use App\Models\TenancyEvents;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Events\LandlordAddDeleteEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LandlordAddDeleteListener
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
     * @param  LandlordAddDeleteEvent  $event
     * @return void
     */
    public function handle(LandlordAddDeleteEvent $event)
    {
        if (JWTAuth::getToken()) {
            $authenticatedUser = JWTAuth::parseToken()->authenticate();
            $creator = $authenticatedUser->name . " " . $authenticatedUser->l_name;
            $agencyId = $authenticatedUser->agency_id;
        } else {
            $creator = "Applicant";
            $agencyId = $event->landlordInformation['agency_id'];
        }
        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = $agencyId;
        $tenancy_event->tenancy_id = $event->landlordInformation['tenancy_id'];
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = $creator;

        if ($event->actionFor == 'add') {
            $items[] = 'Added a new landlord ' . $event->landlordInformation['email'] . ' to the tenancy.';
        } else {
            $items[] = 'An landlord ' . $event->landlordInformation['email'] . ' was removed from the tenancy';
        }
        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = Carbon::now();
        $tenancy_event->applicants = $event->applicantEmail;
        $tenancy_event->save();
    }
}
