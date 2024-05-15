<?php

namespace App\Listeners;

use App\Models\TenancyEvents;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReferenceEventListener
{
    public function __construct()
    {
        //
    }

    public function handle($event)
    {
        $attributeArray = $this->attributeArray();
        $items = [];
        $tenancy_event = new TenancyEvents();
        $tenancy_event->agency_id = JWTAuth::parseToken()->authenticate()->agency_id;
        $tenancy_event->tenancy_id = $event->t_id;
        $tenancy_event->event_type = $event->event_type;
        $tenancy_event->description = $event->description;
        $tenancy_event->creator = JWTAuth::parseToken()->authenticate()->name . " " . JWTAuth::parseToken()->authenticate()->l_name;
        foreach ($event->new as $key => $change) {
            if ($key != 'updated_at') {
                if (in_array($key, ['address_proof', 'id_proof', 'financial_proof', 'qu_doc'])) {
                    $items[] = $attributeArray[$key] . ' updated';
                } elseif (in_array($key, ['t_s_date', 't_e_date'])) {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($event->old[$key]) ? 'NUll' : Carbon::parse($event->old[$key])->format('d-m-Y')) . ' to ' . (empty($change) ? 'NUll' : Carbon::parse($change)->format('d-m-Y'));
                } else {
                    $items[] = 'The ' . $attributeArray[$key] . ' updated from ' . (empty($event->old[$key]) ? 'NUll' : $event->old[$key]) . ' to ' . (empty($change) ? 'NUll' : $change);
                }
            }
        }

        $tenancy_event->details = json_encode($items);
        $tenancy_event->date = Carbon::now();
        $tenancy_event->applicants = $event->applicantEmail;
        $tenancy_event->save();
    }

    public function attributeArray()
    {
        return $attributeArray = [
            'name' => 'name',
            'email' => 'email',
            'address' => 'address',
            'phone' => 'mobile',
            'country_code' => 'country code',
            'notes' => 'notes',
            'notes_text' => 'notes',
            'guarantor_income' => 'guarantor income',
            'addresses_text' => 'addresses notes',
            'owner' => 'owner',
            'relationship' => 'relationship',
            'occupation' => 'occupation',
            'employment_status' => 'employment status',
            'hr_email' => 'hr email',
            'least_income' => 'least income',
            'address_proof' => 'Address proof',
            'id_proof' => 'Id proof',
            'financial_proof' => 'Financial proof',
            'contract_type' => 'contract type',
            'probation_period' => 'probation period',
            'annual_salary' => 'annual salary',
            'annual_bonus' => 'annual bonus',
            'position' => 'position',
            'job_title' => 'job title',
            'company_name' => 'company name',
            'company_email' => 'company email',
            'company_phone' => 'company phone',
            'company_address' => 'company address',
            't_s_date' => 'tenancy start date',
            't_e_date' => 'tenancy end date',
            'damage_status' => 'damage status',
            'damage_detail' => 'damage detail',
            'paid_arrears_value' => 'paid arrears',
            'arrears_status' => 'arrears status',
            'frequent_status' => 'frequent status',
            'paid_status' => 'paid status',
            'rent_price_value' => 'rent price',
            'updated_at' => 'updated at',
            'moveout_status' => 'moveout status',
            'tenant_status' => 'tenant status',
            'qu_doc' => 'quarterly document',
            'close_bal' => 'closing balance',
            'other_document' => 'other document'
        ];
    }
}
