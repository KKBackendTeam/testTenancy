<?php

namespace App\Listeners;

use App\Events\TenancyEvent;
use App\Models\TenancyEvents;
use Carbon\Carbon;
use JWTAuth;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenancyEventListener implements ShouldQueue
{
    public function __construct()
    {
    }

    public function handle(TenancyEvent $event)
    {
        $attributeArray = [
            'pro_address' => 'property address',
            'reference' => 'reference',
            'parking' => 'parking status',
            'parking_cost' => 'parking cost',
            'parkingArray' => 'parking status',
            'restriction' => 'restriction',
            'rent_include' => 'rent include',
            'monthly_amount' => 'monthly amount',
            'total_rent' => 'total rent',
            'deposite_amount' => 'deposit amount',
            'holding_amount' => 'holding amount',
            't_start_date' => 'tenancy start date',
            't_end_date' => 'tenancy end date',
            'updated_at' => 'updated at',
            'status' => 'status',
            'deadline' => 'deadline',
            'notes' => 'notes',
            'notes_text' => 'notes',
            'type' => 'type',
            'signing_date' => 'signing date',
            'days_to_complete' => 'days to complete',
            'no_applicant' => 'no of applicant',
            'interism_inspection' => 'interim inspection'
        ];

        $tenancyStatus = [
            '0' => '',
            '1' => 'pending',
            '2' => 'hold',
            '3' => 'awaiting review',
            '4' => 'failed review',
            '5' => 'awaiting signing',
            '6' => 'let',
            '7' => 'rolling ',
            '8' => 'in progress',
            '9' => 'expired',
            '10' => 'cancelled',
            '11' => 'completed',
            '12' => 'stalled at pending',
            '13' => 'stalled at hold',
            '14' => 'stalled at awaiting review',
            '15' => 'stalled at failed review',
            '16' => 'stalled at awaiting signing'
        ];

        $parkingStatus = [
            '0' => '',
            '1' => 'secure',
            '2' => 'off-road',
            '3' => 'street',
            '4' => 'other'
        ];

        $parkingToggle = [
            '0' => '',
            '1' => 'yes',
            '2' => 'no'
        ];

        $items = [];

        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = JWTAuth::parseToken()->authenticate()->agency_id;
        $tenancy_event->tenancy_id = $event->t_id;
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = JWTAuth::parseToken()->authenticate()->name . " " . JWTAuth::parseToken()->authenticate()->l_name;
        foreach ($event->new as $key => $change) {

            if ($key != 'updated_at') {

                if ($key == 'status') {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($tenancyStatus[$event->old[$key]]) ? 'NUll' : $tenancyStatus[$event->old[$key]]) . ' to ' . (empty($tenancyStatus[$change]) ? 'NUll' : $tenancyStatus[$change]);
                } elseif ($key == 'parking') {
                    $items[] = 'The ' . $attributeArray[$key] .  ' updated from ' . (empty($parkingToggle[$event->old[$key]]) ? 'NUll' : $parkingToggle[$event->old[$key]]) . ' to ' . (empty($parkingToggle[$change]) ? 'NUll' : $parkingToggle[$change]);
                } elseif ($key == 'parkingArray') {
                    $items[] = 'The ' . $attributeArray[$key] .  ' updated from ' . (empty($parkingStatus[$event->old[$key]]) ? 'NUll' : $parkingStatus[$event->old[$key]]) . ' to ' . (empty($parkingStatus[$change]) ? 'NUll' : $parkingStatus[$change]);
                } elseif ($key == 't_start_date' || $key == 't_end_date') {
                    if (!(strpos($change, $event->old[$key]) !== false)) {
                        $items[] = 'The ' . $attributeArray[$key] .  ' updated from ' . (empty($event->old[$key]) ? 'NUll' : Carbon::parse($event->old[$key])->format('d-m-Y')) . ' to ' . (empty($change) ? 'NUll' : Carbon::parse($change)->format('d-m-Y'));
                    }
                } elseif (in_array($key, ['deadline', 'signing_date'])) {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($event->old[$key]) ? 'NUll' : Carbon::parse($event->old[$key])->format('d-m-Y')) . ' to ' . (empty($change) ? 'NUll' : Carbon::parse($change)->format('d-m-Y'));
                } elseif (in_array($key, ['monthly_amount', 'deposite_amount', 'total_rent', 'holding_amount'])) {
                    if (($event->old[$key] == $change)) {
                        continue;
                    }
                    $items[] = 'The ' . $attributeArray[$key] .  ' updated from ' . (empty($event->old[$key]) ? 'NUll' : $event->old[$key]) . ' to ' . (empty($change) ? 'NUll' : $change);
                } else {
                    $items[] = 'The ' . $attributeArray[$key] .  ' updated from ' . (empty($event->old[$key]) ? 'NUll' : $event->old[$key]) . ' to ' . (empty($change) ? 'NUll' : $change);
                }
            }
        }

        $tenancy_event->details = json_encode($items);
        $tenancy_event->applicants = 'N/A';
        $tenancy_event->date = Carbon::now();
        if (count($items) > 0) {
            $tenancy_event->save();
        }
    }
}
