<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Mail\RegistrationEmail;
use App\Mail\RenewTenantEmail;
use App\Models\Tenancy;
use App\Models\Property;
use App\Models\TenancyHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mail;
use DateTime;
use Illuminate\Support\Str;
use DateInterval;
use App\Traits\AllPermissions;
use App\Traits\ConfigrationTrait;
use Illuminate\Support\Facades\Validator;
use App\Traits\LastStaffActionTrait;
use App\Traits\TextForSpecificAreaTrait;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\SortingActionTrait;
use App\Traits\TenancyApplicantIdsHelperTrait;
use App\Events\ApplicantAddDeleteEvent;
use App\Models\Chasing;
use App\Models\Applicantbasic;
use App\Models\InterimInspection;
use App\Traits\WorkWithFile;
use Illuminate\Support\Facades\Log;


class TenancyController extends Controller
{
    use AllPermissions, ConfigrationTrait, LastStaffActionTrait, TextForSpecificAreaTrait, SortingActionTrait;
    use TenancyApplicantIdsHelperTrait, WorkWithFile;

    /**
     * Update the status of a tenancy to renew it.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function renewStatus($id)
    {
        $tenancy = Tenancy::find($id);
        $tenancy->properties()->update(['status' => 2]);
        $tenancy->update(['renew_tenancy' => 1]);
        return response()->json(['saved' => true]);
    }

    /**
     * Get tenancies with optional sorting and pagination.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTenancies()
    {
        $tenancies = Tenancy::where('agency_id', authAgencyId())
            ->with('landlords:id,f_name,l_name,street,town,country,post_code')
            ->with('properties:id,post_code,bedroom')
            ->with('latest_update:id,tenancy_id,event_type')
            ->with('users:id,name,l_name')
            ->with(['tenancyHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status' || request('sort_by') == 'type') {
            $new_collection = new Collection();
            $actionVariable = request('sort_by');
            if (request('sort_by') == 'status') {
                $maxCount =  $this->tenancyStatusArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->tenancyDescStatusArray : $this->tenancyAscStatusArray;
            } else {
                $maxCount =  $this->tenancyTypeArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->tenancyDescTypeArray : $this->tenancyAscTypeArray;
            }
            $maxCount = count($sortAccordingArray);
            $i = 0;
            while ($i < $maxCount) {
                foreach ($tenancies as $key => $ti) {
                    if (isset($sortAccordingArray[$i]) && $ti->{$actionVariable} == $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($tenancies[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $tenancies = $new_collection;
        } else {
            $tenancies = $tenancies->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingTenancyVariables[request('sort_by')]) ? $this->sortingTenancyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $tenancies->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }
        return response()->json(['saved' => true, 'tenancies' => ['data' => $data, 'total' => $tenancies->count()]]);
    }

    /**
     * Check if a tenancy can be created in the second step and handle email validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createTenancySecondStepCheck(Request $request)
    {
        $userEmailArray = $emailDuplicationArray = [];
        $i = 0;
        $authAgencyId = authAgencyId();

        if (Property::where('agency_id', $authAgencyId)->where('id', $request['tenancyData']['property_id'])->whereIn('status', [4])->exists()) {
            return response()->json(['saved' => false, 'statusCode' => 2322, 'reason' => 'You can not create a new tenancy from property where property status is Hold and Not available to let']);
        }

        foreach ($request['tenancyData']['applicants'] as $applicant) {
            $app_info = Applicantbasic::where('agency_id', $authAgencyId)->where('email', strtolower($applicant['app_email']))->first();
            if ($app_info) {
                $today = Carbon::today();
                $tenancies = Tenancy::where('agency_id', $authAgencyId)
                    ->whereHas('applicants.applicantbasic', function ($query) use ($applicant) {
                        $query->where('email', strtolower($applicant['app_email']));
                    })
                    ->whereNotIn('status', [9, 10])
                    ->get();
                if ($tenancies) {
                    foreach ($tenancies as $tenancy) {
                        // Check for overlap
                        if (
                            $tenancy->t_end_date >= $request['tenancyData']['t_start_date'] &&
                            $tenancy->t_start_date <= $request['tenancyData']['t_end_date']
                        ) {
                            $userEmailArray[$i]['app_email'] = strtolower($applicant['app_email']);
                            $userEmailArray[$i]['code'] = 1;
                            $userEmailArray[$i]['app_name'] = $app_info->app_name;
                            $i++;
                            // No need to continue checking other tenancies if overlap is found
                            break;
                        }
                    }
                }
            }
            $emailDuplicationArray[] = strtolower($applicant['app_email']);
        }

        if (!empty($userEmailArray)) return response()->json(['saved' => false, 'errors' => $userEmailArray]);

        if (count($duplicateArrayOfKeys = checkForUniqueValues($emailDuplicationArray)) > 0) {
            return response()->json(['saved' => false, 'statusCode' => 123, 'errors' =>  $duplicateArrayOfKeys, 'reason' => 'Duplicate email in the tenancy']);
        }

        return response()->json(['saved' => true]);
    }

    /**
     * Handles the addition of a new tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddTenancy(Request $request)
    {
        if (agencyAdmin() || $this->createTenancy()) {

            $agencyData = agencyData();

            if (Property::where('agency_id', $agencyData->id)->where('id', $request['tenancyData']['property_id'])->whereIn('status', [4])->exists()) {
                return response()->json(['saved' => false, 'statusCode' => 2322, 'reason' => 'You can not create a new tenancy from property where property status is Hold and Not available to let']);
            }

            if ($prop = Property::where('agency_id', $agencyData->id)->where('id', $request['tenancyData']['property_id'])->first()) {
                if ($prop->available_from > $request['tenancyData']['t_start_date']) {
                    return response()->json(['saved' => false, 'statusCode' => 2327, 'reason' => 'You can not create an tenancy where property is not available, please check property available from date']);
                }
            }

            if ($agencyData->total_credit <= ($agencyData->used_credit + $request['tenancyData']['no_applicant'])) {
                return response()->json(['saved' => false, 'statusCode' => 780, 'reason' => 'Your credit is not sufficient.']);
            }

            $validator = $this->tenancyValidation($request['tenancyData'], 'new', $agencyData->id, $this->tenancyRequirement($agencyData->id));

            if ($validator instanceof \Illuminate\Http\JsonResponse) {
                return $validator;
            }

            if ($validator->errors()->count() > 0) {
                return response()->json(['saved' => false, 'errors' => $validator->errors()]);
            }

            if ($this->checkTenancyOverlappingOrNot($request, 'add')) {
                return response()->json(['saved' => false, 'statusCode' => 782, 'reason' => 'Previous tenancy end date and this tenancies starting date is overlapped']);
            }

            $returnData = $this->checkAtAtimeOneTenancy($request['tenancyData']);
            if ($returnData['isValid'] ==  1) {
                return response()->json(['saved' => false, 'statusCode' => 2326, 'errors' =>  $returnData['array'], 'reason' => 'An applicant is available in more than 1 tenancy at a time.']);
            }

            $tenancy = new Tenancy(['agency_id' => $agencyData->id, 'creator_id' => authUserId()]);

            if ($this->createTenancyHelper($request, $tenancy)) {

                $this->lastStaffAction('Add new tenancy');
                return response()->json(['saved' => true]);
            }
            return response()->json(['saved' => false]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Performs preliminary checks before creating a tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createTenancyFirstStepCheck(Request $request)
    {
        $agencyData = agencyData();

        if (Property::where('agency_id', $agencyData->id)->where('id', $request['tenancyData']['property_id'])->whereNotIn('status', [1, 3])->exists()) {
            return response()->json(['saved' => false, 'statusCode' => 2322, 'reason' => 'You cannot create a new tenancy from a property with a status other than 4']);
        }
        if ($agencyData->total_credit <= ($agencyData->used_credit + $request['tenancyData']['no_applicant'])) {
            return response()->json(['saved' => false, 'statusCode' => 780, 'reason' => 'Your credit is not sufficient.']);
        }

        $validatorOrResponse = $this->checkOneMonthRequirements($request['tenancyData'], $agencyData->id);

        if ($validatorOrResponse instanceof \Illuminate\Http\JsonResponse) {
            return $validatorOrResponse;
        }

        if ($validatorOrResponse->fails()) {
            return response()->json(['saved' => false, 'errors' => $validatorOrResponse->errors()]);
        }

        if ($this->checkTenancyOverlappingOrNot($request, 'add')) {
            return response()->json(['saved' => false, 'statusCode' => 782, 'reason' => 'Previous tenancy end date and this tenancy starting date overlap']);
        }

        return response()->json(['saved' => true]);
    }

    /**
     * Delete a tenancy by its ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteTenancyById($id)
    {
        if ((agencyAdmin() || $this->deleteTenancy()) && ($tenancy = Tenancy::where('agency_id', authAgencyId())->where('id', $id)->first())) {

            $this->checkForPropertyStatus($tenancy);
            $this->deleteTenancyRecords($tenancy);
            $tenancy->tenancyHistory()->delete();
            $tenancy->tenancyInterimInspection()->delete();
            $tenancy->delete();
            $this->lastStaffAction('Delete tenancy');
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Get information about a tenancy by its ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getTenancyInfo($id)
    {
        $tenancy_info = Tenancy::where('id', $id)->where('agency_id', authAgencyId())->firstOrFail();
        $tenancy_info->landlord_name = $tenancy_info->landlords->f_name;
        $applicant_info = $tenancy_info->applicants;
        $tenancy_info->restrictionArray = (!empty($tenancy_info->restriction)) ?  explode(',', $tenancy_info->restriction) : [];
        $tenancy_info->rentIncludeArray = (!empty($tenancy_info->rent_include)) ?  explode(',', $tenancy_info->rent_include) : [];

        return response()->json(['tenancy_info' => $tenancy_info, 'applicant_info' => $applicant_info]);
    }

    /**
     * Check if there are overlapping tenancies for the given tenancy data.
     *
     * @param  array  $request
     * @param  string  $queryFor
     * @return bool
     */
    public function checkTenancyOverlappingOrNot($request, $queryFor)
    {
        $tenancyOverlapped = Tenancy::where('agency_id', authAgencyId())
            ->where('property_id', $request['tenancyData']['property_id'])
            ->where('t_end_date', '>=', $request['tenancyData']['t_start_date'])
            ->where('t_start_date', '<=', $request['tenancyData']['t_end_date'])
            ->where('status', '!=', 10);

        $queryFor == 'edit' ? $tenancyOverlapped->where('id', '!=', $request['tenancyData']['id']) : '';
        $tenancyOverlapped->get(['id']);

        return $tenancyOverlapped->count() > 0 ? true : false;
    }

    /**
     * Check if the given tenancy data meets the one month requirements.
     *
     * @param  array  $request
     * @param  int  $agencyId
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function checkOneMonthRequirements($requestData, $agencyId)
    {
        $tenancyRequirements = $this->tenancyRequirement($agencyId);

        if (!$tenancyRequirements) {
            return response()->json(['saved' => false, 'statusCode' => 783, 'reason' => 'Tenancy requirements not found for the agency']);
        }

        $first = $tenancyRequirements->start_month ? '|first_date_of_month' : '';
        $end = $tenancyRequirements->end_month ? '|end_date_of_month' : '';

        $startDate = $requestData['t_start_date'];
        $endDate = $requestData['t_end_date'];

        $dateMonthDifference = Carbon::parse($startDate)->diff(Carbon::parse($endDate)->addDay());  // month diff
        $totalMonths = $dateMonthDifference->y * 12 + $dateMonthDifference->m;

        $rules = [
            't_start_date' => 'required|before:t_end_date' . $first,
            't_end_date' => 'required|after:t_start_date' . $end . '|month_length:' . $tenancyRequirements->tenancy_max_length . ',' . $totalMonths
        ];

        if ($dateMonthDifference->days < Carbon::parse($startDate)->daysInMonth) {
            Validator::extend('minimum_days_length', function ($attribute, $value, $parameters, $validator) use ($startDate) {
                return Carbon::parse($value)->diffInDays($startDate) >= Carbon::parse($startDate)->daysInMonth;
            });
            $rules['t_start_date'] .= '|minimum_days_length:' . Carbon::parse($startDate)->daysInMonth;
        }
        $customMessages = [
            't_start_date.minimum_days_length' => 'The start date and end date duration must be ' . $tenancyRequirements->tenancy_max_length . ' months.',
        ];
        return validator($requestData, $rules, $customMessages);
    }

    /**
     * Create a new tenancy with the provided data.
     *
     * @param  array  $request
     * @param  \App\Tenancy  $tenancy
     * @return \App\Tenancy
     */
    public function createTenancyHelper($request, $tenancy)
    {
        $agencyData = agencyData();
        $tenancy->timestamps = true;
        $tenancy->landlord_id = $request['tenancyData']['landlord_id'];
        $tenancy->pro_address = $request['tenancyData']['pro_address'];
        $tenancy->property_id = $request['tenancyData']['property_id'];
        $tenancy->parking = ($request['tenancyData']['parking'] == 2) ? 2 : 1;
        $tenancy->parking_cost = is_null($request['tenancyData']['parking_cost']) ? 0 : $request['tenancyData']['parking_cost'];
        $tenancy->parkingArray = $request['tenancyData']['parkingArray'];
        $tenancy->restriction = implode(',', $request['tenancyData']['restrictionArray']);
        $tenancy->rent_include = implode(',', $request['tenancyData']['rentIncludeArray']);
        $tenancy->monthly_amount = $request['tenancyData']['monthly_amount'];
        $tenancy->total_rent = $request['tenancyData']['total_rent'];
        $tenancy->deposite_amount = $request['tenancyData']['deposite_amount'];
        $tenancy->holding_amount = $request['tenancyData']['holding_amount'];
        $tenancy->interism_inspection = $request['tenancyData']['interism_inspection'];
        $tenancy->t_start_date = dateFormat($request['tenancyData']['t_start_date']);
        $tenancy->t_end_date = dateFormat($request['tenancyData']['t_end_date']);
        $tenancy->signing_date = '1900-01-01';
        $tenancy->deadline = dateFormat(now()->addDays(14));
        $tenancy->tc_date = dateFormat(now());
        $tenancy->timezone = $request['tenancyData']['timezone'];

        $applicantNumber = $request['tenancyData']['no_applicant'];
        if ($request['tenancyData']['isNew'] == "new") {
            $tenancy->reference = getUniqueReference($tenancy->properties['property_ref'], $request['tenancyData']['t_start_date']);
            $tenancy->no_applicant = $applicantNumber;
        } else {
            $tenancy->no_applicant = ($applicantNumber > $tenancy->no_applicant) ? $applicantNumber : $tenancy->no_applicant;
        }

        $tenancy->type = isset($request['tenancyData']['type']) ? $request['tenancyData']['type'] : 1;   //tenancy_type = new  ["","new","Renewal","Part Renewal"]
        $tenancy->status = 1;
        $tenancy->agreement_type = $request['tenancyData']['agreement_type'];
        $tenancy->save();

        $tenancyHistory = new TenancyHistory();
        $tenancyHistory->tenancy_id = $tenancy->id;
        $tenancyHistory->agency_id = authAgencyId();
        $tenancyHistory->agreement_type = $request['tenancyData']['agreement_type'];
        $tenancyHistory->save();

        if (isset($request['tenancyData']['type']) && $request['tenancyData']['type'] == 2) {
            Tenancy::find($request['tenancyData']['id'])->update(['renew_tenancy' => 0]);
        }
        if (!empty($tenancy->interism_inspection)) {
            $date1 = new DateTime($tenancy->t_end_date);
            $date2 = new DateTime($tenancy->t_start_date);
            $interval = $date1->diff($date2);

            $months = $interval->format('%m');
            $years = $interval->format('%y');
            $totalMonths = $years * 12 + $months;
            $totalDays = $interval->days;
            if ($totalDays > 15) {
                $totalMonths++;
            }
            $numberOfInspection = $tenancy->interism_inspection;
            $intervals = floor($totalMonths / ($numberOfInspection + 1));
            for ($i = 0; $i < $numberOfInspection; $i++) {
                $date2->add(new DateInterval("P{$intervals}M"));
                $interimInspection = new InterimInspection();
                $interimInspection->tenancy_id = $tenancy->id;
                $interimInspection->agency_id = $agencyData->id;
                $interimInspection->timestamps = true;
                $interimInspection->reference = $tenancy->reference;
                $interimInspection->address = $tenancy->pro_address;
                $interimInspection->inspection_month = $date2->format('F Y');
                $interimInspection->save();
            }
        }

        $tenancy->properties()->update(['previous_status' => $tenancy->properties->status, 'status' => 4, 'available_from' => dateFormat(Carbon::parse($request['tenancyData']['t_end_date'])->addDay(1))]);    //property status = 4 HOLD and tenancy has created from this property!.

        if ($request['tenancyData']['isNew'] == "new") {

            $agencyData = agencyData();
            $chasingSetting = Chasing::where('agency_id', $tenancy->agency_id)->firstOrFail();
            $renewStatus = '';

            foreach ($request['tenancyData']['applicants'] as $applicant) {
                $app_info = Applicantbasic::where('agency_id', authAgencyId())->where('email', strtolower($applicant['app_email']))->first();
                if ($app_info) {
                    $today = Carbon::today();
                    $tenancies = Tenancy::where('agency_id', authAgencyId())
                        ->whereHas('applicants.applicantbasic', function ($query) use ($applicant) {
                            $query->where('email', strtolower($applicant['app_email']));
                        })
                        ->whereNotIn('status', [9, 10])
                        ->get();
                    if ($tenancies) {
                        $renewStatus = 1;
                    }
                }
                $applicantBasic = $this->createOrUpdateNewApplicant($applicant, $request, $agencyData, $pass = Str::random(15));
                $new_applicant = new Applicant();
                $new_applicant->tenancy_id = $tenancy->id;
                $new_applicant->applicant_id = $applicantBasic->id;
                $new_applicant->agency_id = authAgencyId();
                $new_applicant->creator_id = authUserId();
                $new_applicant->app_url = config('global.frontSiteUrl') . ('/applicant/initial_login?email=' . strtolower($applicant['app_email']) . '&code=' . $pass);
                $new_applicant->status = 1;
                $new_applicant->last_response_time = timeChangeAccordingToTimezoneForChasing($request['tenancyData']['timezone'], $chasingSetting->response_time, $chasingSetting->stalling_time);
                $new_applicant->renew_status = ($renewStatus || $request['tenancyData']['type'] == 2) ? 1 : 0;
                $new_applicant->save();

                event(new ApplicantAddDeleteEvent(
                    ['tenancy_id' => $tenancy->id, 'email' => strtolower($applicantBasic->email)],
                    'Add applicant',
                    'An applicant was added to the tenancy',
                    'add',
                    $applicantBasic->email
                ));

                $this->lastStaffAction('Add new applicant');

                if ($applicant['app_renew_tenant'] > 0 || $request['tenancyData']['type'] == 2) {

                    $data = $this->emailTemplateData('RTE', $applicantBasic, $new_applicant->tenancies, $agencyData, null, null, null, null, null, null, null);
                    Mail::to(strtolower($applicant['app_email']))->send(new RenewTenantEmail($data, $agencyData, $new_applicant));
                } else {

                    $data = $this->emailTemplateData('RE', $applicantBasic, $new_applicant->tenancies, $agencyData, null, null, null, null, null, null, null);
                    Mail::to(strtolower($applicant['app_email']))->send(new RegistrationEmail($data, $agencyData, $new_applicant));
                }
            }

            $tenancy->save();
            agencyData()->increment('used_credit', $tenancy->no_applicant);  //deduct the credit
        }

        return $tenancy;
    }

    /**
     * Check if there's only one tenancy at a time for each applicant.
     *
     * @param  array  $tenancyData
     * @return array
     */
    public function checkAtAtimeOneTenancy($tenancyData)
    {
        $array = [];
        $isValid = 0;
        foreach ($tenancyData['applicants'] as $key => $em) {
            $applicantBasic = Applicantbasic::whereEmail(strtolower($em['app_email']))->first();
            if ($applicantBasic) {
                if (count($applicantBasic->applicants) > 0) {
                    foreach ($applicantBasic->applicants as $app) {
                        if ($app->tenancies->t_end_date >= Carbon::parse($tenancyData['t_start_date'])->toDateString() &&  $app->tenancies->t_start_date <= Carbon::parse($tenancyData['t_end_date'])->toDateString()) {
                            $array[$key]['app_email'] = strtolower($em['app_email']);
                            $array[$key]['add_on_not'] = false;
                            $isValid = 1;
                            break;
                        }
                    }
                }
            } else {
                $array[$key]['app_email'] = strtolower($em['app_email']);
                $array[$key]['add_on_not'] = true;
            }
        }
        return ['isValid' => $isValid, 'array' => $array];
    }

    /**
     * Create or update an applicant with the provided data.
     *
     * @param  array  $applicant
     * @param  array  $request
     * @param  \App\Agency  $agencyData
     * @param  string  $password
     * @return \App\Applicantbasic
     */
    public function createOrUpdateNewApplicant($applicant, $request, $agencyData, $password)
    {
        if ($applicantBasic = Applicantbasic::where('email',  strtolower($applicant['app_email']))->first()) {
        } else {
            $applicantBasic = new Applicantbasic();
        }
        $applicantBasic->tenancy_id = 0;
        $applicantBasic->agency_id = $agencyData->id;
        $applicantBasic->app_name = $applicant['app_f_name'];
        $applicantBasic->m_name = isset($applicant['app_m_name']) ? $applicant['app_m_name'] : '';
        $applicantBasic->l_name = $applicant['app_l_name'];
        $applicantBasic->email = strtolower($applicant['app_email']);
        $applicantBasic->app_mobile = $applicant['app_mobile'];
        $applicantBasic->country_code = $applicant['country_code'];
        $applicantBasic->app_mobile = $applicant['app_mobile'];
        $applicantBasic->password = bcrypt($password);
        $applicantBasic->timezone = isset($request['tenancyData']['timezone']) ? $request['tenancyData']['timezone'] : "UTC";
        $applicantBasic->save();
        return $applicantBasic;
    }
}
