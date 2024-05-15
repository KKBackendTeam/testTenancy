<?php

namespace App\Traits;

use App\Models\LandlordReference;
use App\Models\EmploymentReference;
use App\Models\GuarantorReference;
use App\Models\QuarterlyReference;

trait ReferencesAddressesTrait
{
    public function referencesAddresses($request)
    {
        $address = [];
        $i = 0;
        foreach ($request as $key => $add) {
            $address[$i]['postcode'] = $add['postcode'] ?? null;
            $address[$i]['street'] = $add['street'] ?? null;
            $address[$i]['town'] = $add['town'] ?? null;
            $address[$i]['country'] = $add['country'] ?? null;
            $i++;
        }
        return json_encode($address);
    }

    public function references($ref_type, $ref_id)
    {
        switch ($ref_type) {
            case 'L':
                return LandlordReference::where('id', $ref_id)->first();
                break;
            case 'E':
                return EmploymentReference::where('id', $ref_id)->first();
                break;
            case 'G':
                return GuarantorReference::where('id', $ref_id)->first();
                break;
            case 'Q':
                return QuarterlyReference::where('id', $ref_id)->first();
                break;
            default:
                return null;
                break;
        }
    }
}
