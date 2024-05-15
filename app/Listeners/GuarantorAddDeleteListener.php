<?php

namespace App\Listeners;

use App\Models\TenancyEvents;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Events\GuarantorAddDeleteEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GuarantorAddDeleteListener
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
     * @param  GuarantorAddDeleteEvent  $event
     * @return void
     */
    public function handle(GuarantorAddDeleteEvent $event)
    {
        if (JWTAuth::getToken()) {
            $authenticatedUser = JWTAuth::parseToken()->authenticate();
            $creator = $authenticatedUser->name . " " . $authenticatedUser->l_name;
            $agencyId = $authenticatedUser->agency_id;
        } else {
            $creator = "Applicant";
            $agencyId = $event->guarantorInformation['agency_id'];
        }
        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = $agencyId;
        $tenancy_event->tenancy_id = $event->guarantorInformation['tenancy_id'];
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = $creator;

        if ($event->actionFor == 'add') {
            $items[] = 'Added a new guarantor ' . $event->guarantorInformation['email'] . ' to the tenancy.';
        } else {
            $items[] = 'A guarantor ' . $event->guarantorInformation['email'] . ' was removed from the tenancy';
        }
        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = Carbon::now();
        $tenancy_event->applicants = $event->applicantEmail;
        $tenancy_event->save();
    }
}
