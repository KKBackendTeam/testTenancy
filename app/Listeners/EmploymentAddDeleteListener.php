<?php

namespace App\Listeners;

use App\Models\TenancyEvents;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Events\EmploymentAddDeleteEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmploymentAddDeleteListener
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
     * @param  EmploymentAddDeleteEvent  $event
     * @return void
     */
    public function handle(EmploymentAddDeleteEvent $event)
    {
        if (JWTAuth::getToken()) {
            $authenticatedUser = JWTAuth::parseToken()->authenticate();
            $creator = $authenticatedUser->name . " " . $authenticatedUser->l_name;
            $agencyId = $authenticatedUser->agency_id;
        } else {
            $creator = "Applicant"; // or any default value you prefer
            $agencyId = $event->employmentInformation['agency_id']; // or any default value you prefer
        }


        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = $agencyId;
        $tenancy_event->tenancy_id = $event->employmentInformation['tenancy_id'];
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = $creator;

        if ($event->actionFor == 'add') {
            $items[] = 'Added a new employment ' . $event->employmentInformation['email'] . ' to the tenancy.';
        } else {
            $items[] = 'An employment ' . $event->employmentInformation['email'] . ' was removed from the tenancy';
        }
        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = Carbon::now();
        $tenancy_event->applicants = $event->applicantEmail;
        $tenancy_event->save();
    }
}
