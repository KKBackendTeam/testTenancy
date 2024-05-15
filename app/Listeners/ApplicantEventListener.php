<?php

namespace App\Listeners;

use App\Models\TenancyEvents;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApplicantEventListener
{

    public function __construct()
    {
        //
    }

    public function handle($event)
    {
        $attributeArray = [
            'app_name' => 'first name',
            'm_name'   => 'middle name',
            'l_name' => 'last name',
            'email' => 'email',
            'app_mobile' => 'mobile',
            'app_ni_number' => 'applicant insurance number',
            'dob' => 'DOB',
            'type' => 'UK/EU/International',
            'status' => 'status',
            'notes' => 'notes',
            'notes_text' => 'notes',
            'country_code' => 'country code',
            'selfie_pic' => 'Selfie picture',
            'front_doc' => 'Front document',
            'back_doc' => 'Back document',
            'selfie_resident_card' => 'Selfie resident cart',
            'passport_document' => 'Passport document',
            'right_to_rent' => 'Right to rent',
            'selfie_passport_document' => 'Selfie passport document',
            'updated_at' => 'Updated at',
            'payment_schedule' => 'Payment Schedule'
        ];

        $typeArray = ['', 'UK', 'EU/International'];
        $items = [];

        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = JWTAuth::parseToken()->authenticate()->agency_id;
        $tenancy_event->tenancy_id = $event->t_id;
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = JWTAuth::parseToken()->authenticate()->name . " " . JWTAuth::parseToken()->authenticate()->l_name;
        foreach ($event->new as $key => $change) {
            if ($key != 'updated_at') {
                if (in_array($key, ['selfie_pic', 'front_doc', 'back_doc', 'selfie_resident_card', 'passport_document', 'selfie_passport_document'])) {
                    $items[] = $attributeArray[$key] . ' updated';
                } elseif (in_array($key, ['dob'])) {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($event->old[$key]) ? 'NUll' : Carbon::parse($event->old[$key])->format('d-m-Y')) . ' to ' . (empty($change) ? 'NUll' : Carbon::parse($change)->format('d-m-Y'));
                } else {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($event->old[$key]) ? 'NUll' : $event->old[$key]) . ' to ' . (empty($change) ? 'NUll' : $change);
                }
            }
        }

        foreach ($event->newApp as $key => $change) {
            if ($key != 'updated_at') {
                if (in_array($key, ['right_to_rent'])) {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($event->oldApp[$key]) ? 'NUll' : Carbon::parse($event->oldApp[$key])->format('d-m-Y')) . ' to ' . (empty($change) ? 'NUll' : Carbon::parse($change)->format('d-m-Y'));
                } elseif (in_array($key, ['type'])) {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($typeArray[$event->oldApp[$key]]) ? 'NUll' : $typeArray[$event->oldApp[$key]]) . ' to ' . (empty($change) ? 'NUll' : $typeArray[$change]);
                } else {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($event->oldApp[$key]) ? 'NUll' : $event->oldApp[$key]) . ' to ' . (empty($change) ? 'NUll' : $change);
                }
            }
        }

        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = Carbon::now();
        $tenancy_event->applicants = $event->applicantEmail;
        $tenancy_event->save();
    }
}
