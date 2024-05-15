<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Traits\AllPermissions;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\WorkWithFile;
use App\Traits\LastStaffActionTrait;
use App\Traits\ConverFileToBase64;
use App\Traits\SortingActionTrait;
use App\Traits\ConfigrationTrait;
use App\Traits\PropertyTrait;

class PropertyController extends Controller
{
    use AllPermissions, WorkWithFile, LastStaffActionTrait, ConverFileToBase64, SortingActionTrait, ConfigrationTrait, PropertyTrait;

    /**
     * Retrieve properties along with their latest tenancies and landlord information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProperties()
    {
        $properties = Property::where('agency_id', authAgencyId())->with('landlords:id,f_name,l_name')->get();

        foreach ($properties as $key => $single) {
            $data = $single->latestTenancies()->first();
            $properties[$key]['new_tenancy_start_date'] = is_null($data) ?  Carbon::now()->toDateString() : Carbon::parse($data->t_end_date)->addDay()->toDateString();
        }
        $properties->map(function ($data) {
            $data['restrictionArray'] = explode(',', $data['restriction']);
            $data['rentIncludeArray'] = explode(',', $data['rent_include']);
            unset($data['restriction'], $data['rent_include']);
            unset($data['latest_tenancies']);
            return $data;
        });

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->propertyDescStatusArray : $this->propertyAscStatusArray;
            while ($i < $this->propertyStatusArrayCount) {
                foreach ($properties as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($properties[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $properties = $new_collection;
        } else {
            $properties = $properties->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingPropertyVariables[request('sort_by')]) ? $this->sortingPropertyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $properties->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return response()->json(['saved' => true, 'properties' => ['data' => $data, 'total' => $properties->count()], 'financial_configuration' => $this->financialConfiguration()]);
    }

    /**
     * Retrieve property information by its ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function propertyInfoById($id)
    {
        $prop = Property::where('id', $id)->where('agency_id', authAgencyId())
            ->with(array('tenancies' => function ($query) {
                $query->latest();
            }))->with('landlords:id,f_name,l_name')->first();

        $prop->restrictionArray = (!empty($prop->restriction)) ?  explode(',', $prop->restriction) : [];
        $prop->rentIncludeArray = (!empty($prop->rent_include)) ?  explode(',', $prop->rent_include) : [];

        foreach ($prop['tenancies']  as $tenancy) {

            $tenancy->restrictionArray = (!empty($tenancy->restriction)) ?  explode(',', $tenancy->restriction) : [];
            $tenancy->rentIncludeArray = (!empty($tenancy->rent_include)) ?  explode(',', $tenancy->rent_include) : [];
        }
        return response()->json(['saved' => true, 'prop_info' => $prop]);
    }

    /**
     * Check uniqueness of property reference and upload associated documents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function propertyUniqueRefernceChecker(Request $request)
    {
        $validator = validator($request['propertyData'], [
            'property_ref' => 'required|unique:properties'
        ]);

        if ($validator->fails()) return response()->json(['saved' => false]);

        $in1 = $this->fileUploadHelperFunction("document",  null, $request['propertyData']['epc_certificate']);
        if ($in1 == 'virus_file') {
            $this->deleteFile('document', $in1);
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The is a virus file'
            ]);
        }

        $in2 = $this->fileUploadHelperFunction("document",  null, $request['propertyData']['gas_certificate']);
        if ($in2 == 'virus_file') {
            $this->deleteFile('document', $in2);
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The gas certificate is a virus file'
            ]);
        }

        $in3 = $this->fileUploadHelperFunction("document",  null, $request['propertyData']['electric_certificate']);
        if ($in3 == 'virus_file') {
            $this->deleteFile('document', $in3);
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The electric certificate is a virus file'
            ]);
        }

        $in4 = $this->fileUploadHelperFunction("document",  null, $request['propertyData']['hmo_certificate']);
        if ($in4 == 'virus_file') {
            $this->deleteFile('document', $in4);
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The electric certificate is a virus file'
            ]);
        }

        $in5 = $this->fileUploadHelperFunction("document",  null, $request['propertyData']['fire_alarm_certificate']);
        if ($in5 == 'virus_file') {
            $this->deleteFile('document', $in5);
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The electric certificate is a virus file'
            ]);
        }
        return response()->json(['saved' => true]);
    }

    /**
     * Helper function to create a property.
     *
     * @param \App\Models\Property $property The property model instance.
     * @param array $request The request data.
     * @param string $onlyFor Specifies if the function is for adding or editing properties.
     * @return bool|array Returns true on success, or an array with virus error details.
     */
    public function createPropertyHelper($property, $request, $onlyFor)    //create property Helper function
    {
        if ($validate = $this->fileExistOnNotValidatorHelper('document', $request['epc_certificate'], $request, 'epc_certificate')) {
            return response()->json(['saved' => false, 'errors' => $validate->errors()]);
        }

        if ($validate = $this->fileExistOnNotValidatorHelper('document', $request['gas_certificate'], $request, 'gas_certificate')) {
            return response()->json(['saved' => false, 'errors' => $validate->errors()]);
        }

        if ($validate = $this->fileExistOnNotValidatorHelper('document', $request['electric_certificate'], $request, 'electric_certificate')) {
            return response()->json(['saved' => false, 'errors' => $validate->errors()]);
        }
        if ($validate = $this->fileExistOnNotValidatorHelper('document', $request['hmo_certificate'], $request, 'hmo_certificate')) {
            return response()->json(['saved' => false, 'errors' => $validate->errors()]);
        }
        if ($validate = $this->fileExistOnNotValidatorHelper('document', $request['fire_alarm_certificate'], $request, 'fire_alarm_certificate')) {
            return response()->json(['saved' => false, 'errors' => $validate->errors()]);
        }

        $property->agency_id = authAgencyId();

        if ($onlyFor == 'add') {
            $property->property_ref = $request['property_ref'];
        }
        $property->status =  $request['status'];
        $property->post_code = $request['post_code'];
        $property->street = $request['street'];
        $property->town = $request['town'];
        $property->country = $request['country'];
        $property->parkingToggle = ($request['parkingToggle'] == 1) ? 1 : 2;
        $property->parking_cost = is_null($request['parking_cost']) ? 0 : $request['parking_cost'];
        $property->parkingArray =  $request['parkingArray'];
        $property->bedroom = $request['bedroom'];
        $property->restriction = implode(',', $request['restrictionArray']);
        $property->rent_include = implode(',', $request['rentIncludeArray']);
        $property->hasGas = (isset($request['hasGas']) && !is_null($request['hasGas']) && $request['hasGas'] == 1) ? 1 : 2;
        $property->gas_expiry_date = optional($request)['gas_expiry_date'];
        $property->epc_expiry_date = optional($request)['epc_expiry_date'];
        $property->electric_expiry_date = optional($request)['electric_expiry_date'];

        $in1 = $this->fileUploadHelperFunction("document",  null, $request['epc_certificate']);
        if ($in1 == 'virus_file') {
            $this->deleteFile('document', $in1);
            return ['virus_error' => true, 'type' => 'epc certificate'];
        } else {
            $property->epc_certificate = $in1;
        }

        $in2 = $this->fileUploadHelperFunction("document",  null, $request['gas_certificate']);
        if ($in2 == 'virus_file') {
            $this->deleteFile('document', $in2);
            return ['virus_error' => true, 'type' => 'gas certificate'];
        } else {
            $property->gas_certificate = $in2;
        }

        $in3 = $this->fileUploadHelperFunction("document", null, $request['electric_certificate']);
        if ($in3 == 'virus_file') {
            $this->deleteFile('document', $in3);
            return ['virus_error' => true, 'type' => 'electric certificate'];
        } else {
            $property->electric_certificate = $in3;
        }

        $property->hmo = $request['hmo'];
        $property->hmo_expiry_date = optional($request)['hmo_expiry_date'];
        $in4 = $this->fileUploadHelperFunction("document",  null, $request['hmo_certificate']);
        if ($in4 == 'virus_file') {
            $this->deleteFile('document', $in4);
            return ['virus_error' => true, 'type' => 'hmo certificate'];
        } else {
            $property->hmo_certificate = $in4;
        }

        $property->fire_alarm = $request['fire_alarm'];
        $property->fire_alarm_expiry_date = optional($request)['fire_alarm_expiry_date'];
        $in5 = $this->fileUploadHelperFunction("document",  null, $request['fire_alarm_certificate']);
        if ($in5 == 'virus_file') {
            $this->deleteFile('document', $in5);
            return ['virus_error' => true, 'type' => 'fire alarm certificate'];
        } else {
            $property->fire_alarm_certificate = $in5;
        }


        $property->monthly_rent = $request['monthly_rent'];
        $property->total_rent = $request['total_rent'];
        $property->deposite_amount = $request['deposite_amount'];
        $property->holding_fee_amount = $request['holding_fee_amount'];
        $property->landlord_id = $request['landlord_id'];
        if (isset($request['available_from'])) $property->available_from = $request['available_from'];

        if ($onlyFor == 'edit') {
            if ($property->status != 2  && $request['status'] == 2) {
                $property->latestTenancy()->update(['renew_tenancy' => 1]);
            } else {
                $property->tenancies()->update(['renew_tenancy' => 0]);
            }
        }
        $property->save();
        return true;
    }

    /**
     * Add new property.
     *
     * @param \Illuminate\Http\Request $request The request data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function postAddProperty(Request $request)
    {
        if (agencyAdmin() || $this->createProperty()) {
            $success_count = 0;
            $total_count = count($request['propertyData']);

            $validateArrayString =  $propetyDuplicateArray = $referencesArray = $duplicateArrayOfKeys = [];
            $propertyLocation = '';

            for ($p = 0; $p < $total_count; $p++) {

                $validator = $this->propertyValidation($request['propertyData'][$p], 'add');

                if ($validator->fails()) {
                    $propetyDuplicateArray[] = ($p + 1);
                    $propertyLocation .= ($p + 1) . ', ';
                }
                if (($p == ($total_count - 1)) && trim($propertyLocation) !== '') {
                    $validateArrayString[] = 'The reference of property ' . substr_replace($propertyLocation, "", -2) . ' has already been taken.';
                }
                $referencesArray[] = $request['propertyData'][$p]['property_ref'];
            }

            if (count($validateArrayString) > 0) {
                return response()->json(['saved' => false, 'statusCode' => 124, 'errors' =>  $validateArrayString, 'propertyNumber' => $propetyDuplicateArray]);
            }

            if (count($duplicateArrayOfKeys = checkForUniqueValues($referencesArray)) > 0) {
                return response()->json(['saved' => false, 'statusCode' => 123, 'errors' =>  $duplicateArrayOfKeys, 'reason' => 'Duplicate references in the property']);
            }

            for ($p = 0; $p < $total_count; $p++) {
                $property = new Property(['creator_id' => authUserId()]);
                $th = $this->createPropertyHelper($property, $request['propertyData'][$p], 'add');

                if (isset($th['virus_error']) && $th['virus_error']) {
                    return response()->json([
                        'saved' => false,
                        'statusCode' => 4578,
                        'message' => 'The ' . $th['type'] . ' is a virus file'
                    ]);
                }

                if ($th == true) $success_count++;
            }
            $this->lastStaffAction('Create new property');
            return response()->json(['saved' => true, 'success' => $success_count, 'total' => $total_count]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Edit existing property.
     *
     * @param \Illuminate\Http\Request $request The request data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function postEditProperty(Request $request)
    {
        if (agencyAdmin() || $this->editProperty()) {
            $validator = $this->propertyValidation($request['propertyData'], 'edit');

            if ($validator->fails()) {
                return response()->json(['saved' => false, 'errors' => $validator->errors()]);
            }
            if (!empty($property = Property::where('id', $request['propertyData']['id'])->where('agency_id', authAgencyId())->first())) {

                if (!$this->statusCheckBeforePropertyEdit($property, $request['propertyData']['status'])) {
                    return response()->json(['saved' => false, 'statusCode' => 2315, 'reason' => 'You can not update the property status']);
                }

                if (isset($request['propertyData']['available_from'])) {
                    $d = Carbon::parse($request['propertyData']['available_from'])->toDateString();
                    if ($property->tenancies->count() > 0  && $d < $property->available_from) {
                        return response()->json(['saved' => false, 'statusCode' => 2325, 'reason' => 'You can not not update Propety availabel date']);
                    }
                }
                $validator = $this->createPropertyHelper($property, $request['propertyData'], 'edit');
                if (isset($validator['virus_error']) && $validator['virus_error']) {
                    return response()->json([
                        'saved' => false,
                        'statusCode' => 4578,
                        'message' => 'The ' . $validator['type'] . ' is a virus file'
                    ]);
                }
                if ($validator === true) {
                    $this->lastStaffAction('Edit property');
                    return response()->json(['saved' => true]);
                } else {
                    return response()->json(['saved' => false, 'errors' => $validator->errors()]);
                }
            }
            return response()->json(['saved' => false]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Delete existing property.
     *
     * @param \Illuminate\Http\Request $request The request data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function postDeleteProperty($id)
    {
        if (agencyAdmin() || $this->deleteProperty()) {
            if (!empty($property = Property::where('id', $id)->where('agency_id', authAgencyId())->first())) {
                if (($property->previous_status === 0 && $property->status === 3) ||
                    ($property->previous_status === 0 && $property->status === 1) ||
                    ($property->previous_status === 1 && $property->status === 1) ||
                    ($property->previous_status === 3 && $property->status === 1)
                ) {
                    $property->delete();
                    return response()->json(['saved' => true]);
                }
                return response()->json(['saved' => false]);
            }
            return response()->json(['saved' => false]);
        }
        return response()->json(['saved' => false]);
    }
}
