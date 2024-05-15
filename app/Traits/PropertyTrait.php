<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait PropertyTrait
{
    public function statusCheckBeforePropertyEdit($property, $newStatus)
    {
        if ($property->status == $newStatus || in_array($newStatus, [6])) {
            return true;
        } elseif (in_array($property->status, [5, 6]) && in_array($newStatus, [1, 3])) {
            return true;
        } elseif (in_array($property->status, [2]) && in_array($newStatus, [1, 3, 5])) {
            return true;
        } elseif (in_array($property->status, [1, 3]) && in_array($newStatus, [1, 3])) {
            return true;
        } elseif (in_array($property->status, [5]) && in_array($newStatus, [1, 2, 3])) {
            return true;
        } else {
            return false;
        }
    }

    // public function propertyUniqueRefernceChecker(Request $request)
    // {
    //     $validator = validator($request['propertyData'], [
    //         'property_ref' => 'required|unique:properties'
    //     ]);

    //     if ($validator->fails()) return response()->json(['saved' => false]);
    //     return response()->json(['saved' => true]);
    // }

    public function propertyValidation($request, $actionFor)    //property validation Helper function
    {
        $rules =  [
            'street' => 'required',
            'town' => 'required',
            'country' => 'required',
            'monthly_rent' => 'required',
            'deposite_amount' => 'required',
            'holding_fee_amount' => 'required',
            'landlord_id' => 'required',
            'bedroom' => 'required|integer|between:1,10'
        ];

        $rules +=  $actionFor === 'edit' ? ['property_ref' => 'required|exists:properties'] : ['property_ref' => 'required|unique:properties'];
        $validator = validator($request, $rules);

        return $validator;
    }
}
