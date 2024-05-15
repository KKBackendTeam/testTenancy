<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Traits\ConfigrationTrait;
use App\Traits\FilterHelperTrait;
use App\Models\EmploymentReference;
use App\Models\GuarantorReference;
use App\Models\LandlordReference;
use App\Models\Tenancy;
use App\Models\Property;
use Carbon\Carbon;
use App\Traits\SortingActionTrait;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\StatusTrait;
use App\Models\QuarterlyReference;

class DashboardFilterRecordsController extends Controller
{
    use ConfigrationTrait, FilterHelperTrait, SortingActionTrait, StatusTrait;

    /**
     * Retrieve data of problematic applicants based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function problematicApplicantData(Request $request)
    {
        $tenancyReference = $this->wfc($request['tenancy_reference'], 'reference', null, null);
        $appName = $this->wfc($request['applicant_name'], 'app_name', 'l_name', null);
        $applicantEmail = $this->wfc($request['email'], 'email', null, null);
        $applicantMobile = $this->wfc($request['mobile'], 'app_mobile', null, null);

        $problematicApplicant = Applicant::where('agency_id', authAgencyId())->whereIn('status', [10, 11, 12]);

        if ($request['id'] != 0) {
            $problematicApplicant = $problematicApplicant->where('creator_id', $request['id']);
        }

        $problematicApplicant = $problematicApplicant
            ->with('tenancies:id,reference')
            ->whereHas('tenancies', $tenancyReference)->with(['tenancies' => $tenancyReference])
            ->whereHas('applicantbasic', $appName)->with(['applicantbasic' => $appName])
            ->whereHas('applicantbasic', $applicantEmail)->with(['applicantbasic' => $applicantEmail])
            ->whereHas('applicantbasic', $applicantMobile)->with(['applicantbasic' => $applicantMobile])
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingProblematicApplicantVariables[request('sort_by')]) ? $this->sortingProblematicApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $data = $problematicApplicant->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'problematicApplicant' => ['data' => $data, 'total' => $problematicApplicant->count()]]);
    }

    /**
     * Retrieve data of failed employment reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function failedEmploymentReview(Request $request)
    {
        return response()->json(['saved' => true, 'failedEmploymentReview' => $this->employmentDataHelper($request, 'failedEmploymentReview')]);
    }

    /**
     * Retrieve data of applicants awaiting employment reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingEmploymentReview(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingEmploymentReview' => $this->employmentDataHelper($request, 'awaitingEmploymentReview')]);
    }

    /**
     * Retrieve data of applicants awaiting guarantor reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingGuarantorReview(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingGuarantorReview' => $this->guarantorDataHelper($request, 'awaitingGuarantortReview')]);
    }

    /**
     * Retrieve data of failed guarantor reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function failedGuarantorReview(Request $request)
    {
        return response()->json(['saved' => true, 'failedGuarantorReview' => $this->guarantorDataHelper($request, 'failedGuarantorReview')]);
    }

    /**
     * Retrieve data of applicants awaiting landlord reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingLandlordReview(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingLandlordReview' => $this->landlordDataHelper($request, 'awaitingLandlordReview')]);
    }

    /**
     * Retrieve data of failed landlord reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function failedLandlordReview(Request $request)
    {
        return response()->json(['saved' => true, 'failedLandlordReview' => $this->landlordDataHelper($request, 'failedLandlordReview')]);
    }

    /**
     * Retrieve data of applicants awaiting quaterly reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingQuarterlyReview(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingQuarterlyReview' => $this->quarterlyDataHelper($request, 'awaitingQuarterlyReview')]);
    }

    /**
     * Retrieve data of applicants awaiting TA reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingTAReview(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingTAReview' => $this->tenancyDataHelper($request, 'awaitingTAReview')]);
    }

    /**
     * Retrieve data of failed quaterly reviews.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function failedQuarterlyReview(Request $request)
    {
        return response()->json(['saved' => true, 'failedQuarterlyReview' => $this->quarterlyDataHelper($request, 'failedQuarterlyReview')]);
    }

    /**
     * Retrieve data for the "Awaiting Applicant Form" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingApplicantForm(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingApplicantForm' => $this->applicantsDataHelper($request, 'awaitingApplicantForm')]);
    }

    /**
     * Retrieve data for the "Right to rent expired" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function rightToRentExpired(Request $request)
    {
        return response()->json(['saved' => true, 'applicantRightToExpired' => $this->applicantsDataHelper($request, 'applicantRightToExpired')]);
    }

    /**
     * Retrieve data for the "Right to rent expired within 30days" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function rightToRentExpiredWithInThirtyDays(Request $request)
    {
        return response()->json(['saved' => true, 'applicantRightToExpiredWithInThirtyDays' => $this->applicantsDataHelper($request, 'applicantRightToExpiredWithInThirtyDays')]);
    }


    /**
     * Retrieve data for the "Awaiting Reference" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingReference(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingReference' => $this->applicantsDataHelper($request, 'awaitingReference')]);
    }

    /**
     * Retrieve data for the "Awaiting Signing" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingSigning(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingSigning' => $this->applicantsDataHelper($request, 'awaitingSigning')]);
    }

    /**
     * Retrieve data for the "Progress But Starting Soon" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function progressButStartingSoon(Request $request)
    {
        return response()->json(['saved' => true, 'progressButStartingSoon' => $this->tenancyDataHelper($request, 'progressButStartingSoon')]);
    }

    /**
     * Retrieve data for the "Recently Finalized" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recently_finalized(Request $request)
    {
        return response()->json(['saved' => true, 'recentlyFinalised' => $this->tenancyDataHelper($request, 'recentlyFinalised')]);
    }

    /**
     * Retrieve data for the "New Tenancy Complete" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tenancyCompleteNew(Request $request)
    {
        return response()->json(['saved' => true, 'tenancyCompleteNew' => $this->tenancyDataHelper($request, 'tenancyCompleteNew')]);
    }

    /**
     * Retrieve data for the "Renew Tenancy Complete" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tenancyCompleteRenew(Request $request)
    {
        return response()->json(['saved' => true, 'tenancyCompleteRenew' => $this->tenancyDataHelper($request, 'tenancyCompleteRenew')]);
    }

    /**
     * Retrieve data for the "Partial Renew Tenancy Complete" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tenancyCompletePartialRenew(Request $request)
    {
        return response()->json(['saved' => true, 'tenancyCompletePartialRenew' => $this->tenancyDataHelper($request, 'tenancyCompletePartialRenew')]);
    }

    /**
     * Retrieve data for the "Awaiting TA Sending" stage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awaitingTASending(Request $request)
    {
        return response()->json(['saved' => true, 'awaitingTASending' => $this->tenancyDataHelper($request, 'awaitingTASending')]);
    }

    /**
     * Retrieve data for the "Epc certificate expiry within 30 days".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function epcCertificateExpiryWithInThirtyDays(Request $request)
    {
        return response()->json(['saved' => true, 'epcCertificateExpiryWithInThirtyDays' => $this->propertiesDataHelper($request, 'epcCertificateExpiryWithInThirtyDays')]);
    }

    /**
     * Retrieve data for the "Epc certificate expiry within 30 days".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function gasCertificateExpiryWithInThirtyDays(Request $request)
    {
        return response()->json(['saved' => true, 'gasCertificateExpiryWithInThirtyDays' => $this->propertiesDataHelper($request, 'gasCertificateExpiryWithInThirtyDays')]);
    }

    /**
     * Retrieve data for the "Epc certificate expiry within 30 days".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function eicrCertificateExpiryWithInThirtyDays(Request $request)
    {
        return response()->json(['saved' => true, 'eicrCertificateExpiryWithInThirtyDays' => $this->propertiesDataHelper($request, 'eicrCertificateExpiryWithInThirtyDays')]);
    }

    /**
     * Retrieve data for the "Epc certificate expiry within 30 days".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function hmoCertificateExpiryWithInThirtyDays(Request $request)
    {
        return response()->json(['saved' => true, 'hmoCertificateExpiryWithInThirtyDays' => $this->propertiesDataHelper($request, 'hmoCertificateExpiryWithInThirtyDays')]);
    }

    /**
     * Retrieve data for the "Epc certificate expiry within 30 days".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fireAlarmCertificateExpiryWithInThirtyDays(Request $request)
    {
        return response()->json(['saved' => true, 'fireAlarmCertificateExpiryWithInThirtyDays' => $this->propertiesDataHelper($request, 'fireAlarmCertificateExpiryWithInThirtyDays')]);
    }

    /**
     * Helper function to retrieve applicants data based on provided parameters.
     *
     * @param  array  $request
     * @param  string  $dataFor
     * @return array
     */
    public function applicantsDataHelper($request, $dataFor)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);
        $tenancyAddress = $this->wfc($request['tenancy_address'], 'pro_address', null, null);
        $tenancyReference = $this->wfc($request['reference'], 'reference', null, null);
        $userName = $this->wfc($request['creator_name'], 'name', 'l_name', null);
        $appName = $this->wfc($request['name'], 'app_name', 'l_name', null);
        $applicantEmail = $this->wfc($request['email'], 'email', null, null);
        $applicantMobile = $this->wfc($request['mobile'], 'app_mobile', null, null);

        $applicants = $this->applicantsData($request);

        if ($dataFor == 'awaitingApplicantForm') {
            $applicants = $applicants->where('log_status', 0)->where('status', 1);
        } elseif ($dataFor == 'awaitingReference') {
            $applicants = $applicants->where('log_status', 1)->where('status', 2)->where('ref_status', 0);
        } elseif ($dataFor == 'applicantRightToExpired') {
            $applicants = $applicants->where('right_to_rent', '<', now());
        } elseif ($dataFor == 'applicantRightToExpiredWithInThirtyDays') {
            $applicants = $applicants->whereDate('right_to_rent', '>=', $today)->whereDate('right_to_rent', '<=', $thirtyDaysFromNow);
        } else {
            $applicants = $applicants->where('log_status', 1)->where('status', 5);
        }

        $applicants = $applicants
            ->where(function ($query) use ($request) {
                if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->applicantStatus);
            })
            ->whereHas('tenancies', $tenancyAddress)->with(['tenancies' => $tenancyAddress])
            ->whereHas('tenancies', $tenancyReference)->with(['tenancies' => $tenancyReference])
            ->whereHas('applicantbasic', $appName)->with(['applicantbasic' => $appName])
            ->whereHas('applicantbasic', $applicantEmail)->with(['applicantbasic' => $applicantEmail])
            ->whereHas('applicantbasic', $applicantMobile)->with(['applicantbasic' => $applicantMobile])
            ->whereHas('users', $userName)->with(['users' => $userName])
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status' || request('sort_by') == 'type') {
            $i = 0;
            $new_collection = new Collection();
            $actionVariable = request('sort_by');
            if (request('sort_by') == 'status') {
                $maxCount =  $this->tenancyStatusArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            } else {
                $maxCount =  $this->tenancyTypeArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->tenancyDescTypeArray : $this->tenancyAscTypeArray;
            }
            while ($i < $maxCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingTenancyVariables[request('sort_by')]) ? $this->sortingTenancyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }
        return ['data' => $data, 'total' => $applicants->count()];
    }

    /**
     * Helper function to retrieve properties data based on provided parameters.
     *
     * @param  array  $request
     * @param  string  $dataFor
     * @return array
     */
    public function propertiesDataHelper($request, $dataFor)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);
        $landlordName = $this->wfc($request['name'], 'f_name', 'l_name', null);
        $properties = $this->propertiesData($request);

        if ($dataFor == 'epcCertificateExpiryWithInThirtyDays') {
            $properties = $properties->where('epc_expiry_date', '>=', $today)->where('epc_expiry_date', '<=', $thirtyDaysFromNow);
        } elseif ($dataFor == 'gasCertificateExpiryWithInThirtyDays') {
            $properties = $properties->where('gas_expiry_date', '>=', $today)->where('gas_expiry_date', '<=', $thirtyDaysFromNow);
        } elseif ($dataFor == 'eicrCertificateExpiryWithInThirtyDays') {
            $properties = $properties->where('electric_expiry_date', '>=', $today)->where('electric_expiry_date', '<=', $thirtyDaysFromNow);
        } elseif ($dataFor == 'hmoCertificateExpiryWithInThirtyDays') {
            $properties = $properties->where('hmo_expiry_date', '>=', $today)->where('hmo_expiry_date', '<=', $thirtyDaysFromNow);
        } else {
            $properties = $properties->where('fire_alarm_expiry_date', '>=', $today)->where('fire_alarm_expiry_date', '<=', $thirtyDaysFromNow);
        }

        $properties = $properties->where(function ($query) use ($request) {

            if (!empty($request['property_ref']))  $this->filterWithStringAttributes($request['property_ref'], $query, 'property_ref', null, null);
            if (!empty($request['address']))  $this->filterWithStringAttributes($request['address'], $query, 'street', 'town', 'country');
        })
            ->whereHas('landlords', $landlordName)->with(['landlords' => $landlordName])->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status' || request('sort_by') == 'type') {
            $i = 0;
            $new_collection = new Collection();
            $actionVariable = request('sort_by');
            if (request('sort_by') == 'status') {
                $maxCount =  $this->tenancyStatusArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->tenancyDescStatusArray : $this->tenancyAscStatusArray;
            } else {
                $maxCount =  $this->tenancyTypeArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->tenancyDescTypeArray : $this->tenancyAscTypeArray;
            }
            while ($i < $maxCount) {
                foreach ($properties as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($properties[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $properties = $new_collection;
        } else {
            $properties = $properties->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingTenancyVariables[request('sort_by')]) ? $this->sortingTenancyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $properties->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return ['data' => $data, 'total' => $properties->count()];
    }

    /**
     * Helper function to retrieve tenancies data based on provided parameters.
     *
     * @param  array  $request
     * @param  string  $dataFor
     * @return array
     */
    public function tenancyDataHelper($request, $dataFor)
    {
        $tenancy_info = $this->tenanciesData($request);

        if ($dataFor == 'progressButStartingSoon') {
            $tenancy_info = $tenancy_info->whereNotIn('status', [7, 9, 10, 11])->where('t_start_date', '<=', now()->addDays(13));
        } elseif ($dataFor == 'recentlyFinalised') {
            $tenancy_info = $tenancy_info->where('status', 11)->where('updated_at', '>', now()->subDays(14));
        } elseif ($dataFor == 'awaitingTAReview') {
            $tenancy_info = $tenancy_info->where('status', 18);
        } elseif ($dataFor == 'awaitingTASending') {
            $tenancy_info = $tenancy_info->where('status', 17);
        } elseif ($dataFor == 'tenancyCompleteNew') {
            $tenancy_info = $tenancy_info->where('status', 11)->where('type', 1)->whereBetween('t_start_date', [now(), now()->addDays(8)]);
        } elseif ($dataFor == 'tenancyCompleteRenew') {
            $tenancy_info = $tenancy_info->where('status', 11)->where('type', 2)->whereBetween('t_start_date', [now(), now()->addDays(8)]);
        } else {
            $tenancy_info = $tenancy_info->where('status', 11)->where('type', 3)->whereBetween('t_start_date', [now(), now()->addDays(8)]);
        }

        $landlordName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['landlord_name'], $query, 'f_name', 'l_name', null);
        };

        $creatorName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['created_by'], $query, 'name', 'l_name', null);
        };

        $bed_number = function ($query) use ($request) {
            if (!empty($request['no_beds'])) $this->filterWithNumberAttributes($request['no_beds'], $query, 'bedroom', null, null);
        };

        $tenancy_info = $tenancy_info->where(function ($query) use ($request) {

            if (!empty($request['reference']))  $this->filterWithStringAttributes($request['reference'], $query, 'reference', null, null);
            if (!empty($request['tenancy_address']))  $this->filterWithStringAttributes($request['tenancy_address'], $query, 'pro_address', null, null);
            if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->tenancyStatus);
            if (!empty($request['monthly_amount'])) $this->filterWithNumberAttributes($request['monthly_amount'], $query, 'monthly_amount', null, null);
            if (!empty($request['total_rent'])) $this->filterWithNumberAttributes($request['total_rent'], $query, 'total_rent', null, null);
            if (!empty($request['deposite_amount'])) $this->filterWithNumberAttributes($request['deposite_amount'], $query, 'deposite_amount', null, null);
            if (!empty($request['holding_amount'])) $this->filterWithNumberAttributes($request['holding_amount'], $query, 'holding_amount', null, null);
            if (!empty($request['no_applicant'])) $this->filterWithNumberAttributes($request['no_applicant'], $query, 'no_applicant', null, null);
            if (!empty($request['type']))  $this->filterWithStringAttributes($request['type'], $query, 'type', null, null);
            if (!empty($request['start_date'])) $this->filterWithDateAttributes($request['start_date'], $query, 't_start_date', null, null);
            if (!empty($request['end_date'])) $this->filterWithDateAttributes($request['end_date'], $query, 't_end_date', null, null);
            if (!empty($request['create_date'])) $this->filterWithDateAttributes($request['create_date'], $query, 'created_at', null, null);
            if (!empty($request['updated_at'])) $this->filterWithDateAttributes($request['updated_at'], $query, 'updated_at', null, null);
        })
            ->whereHas('landlords', $landlordName)->with(['landlords' => $landlordName])
            ->whereHas('properties', $bed_number)->with(['properties' => $bed_number])
            ->whereHas('users', $creatorName)->with(['users' => $creatorName])
            ->with('landlords:id,f_name,l_name,street,town,country,post_code')
            ->with('properties:id,post_code,bedroom')
            ->with('latest_update:tenancy_id,event_type')
            ->with('users:id,name,l_name')
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status' || request('sort_by') == 'type') {
            $i = 0;
            $new_collection = new Collection();
            $actionVariable = request('sort_by');
            if (request('sort_by') == 'status') {
                $maxCount =  $this->tenancyStatusArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->tenancyDescStatusArray : $this->tenancyAscStatusArray;
            } else {
                $maxCount =  $this->tenancyTypeArrayCount;
                $sortAccordingArray = request('sort_action') == 'desc' ? $this->tenancyDescTypeArray : $this->tenancyAscTypeArray;
            }
            while ($i < $maxCount) {
                foreach ($tenancy_info as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($tenancy_info[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $tenancy_info = $new_collection;
        } else {
            $tenancy_info = $tenancy_info->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingTenancyVariables[request('sort_by')]) ? $this->sortingTenancyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $tenancy_info->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }
        return ['data' => $data, 'total' => $tenancy_info->count()];
    }

    /**
     * Helper function to retrieve employments data based on provided parameters.
     *
     * @param  array  $request
     * @param  string  $dataFor
     * @return array
     */
    public function employmentDataHelper($request, $dataFor)
    {
        $employments = $this->employmentsData($request);

        if ($dataFor == 'failedEmploymentReview') {
            $employments = $employments->whereIn('agency_status', [3, 5]);
        } else {
            $employments = $employments->where('status', 2)->whereIn('agency_status', [0, 1, 2]);
        }

        $employments = $employments
            ->where(function ($query) use ($request) {
                if (!empty($request['name'])) {
                    $this->filterWithStringAttributes($request['name'], $query, 'name', null, null);
                }
                if (!empty($request['company_email'])) {
                    $this->filterWithStringAttributes($request['company_email'], $query, 'company_email', null, null);
                }
                if (!empty($request['company_name'])) {
                    $this->filterWithStringAttributes($request['company_name'], $query, 'company_name', null, null);
                }
                if (!empty($request['company_phone'])) {
                    $this->filterWithStringAttributes($request['company_phone'], $query, 'company_phone', null, null);
                }
                if (!empty($request['job_title'])) {
                    $this->filterWithStringAttributes($request['job_title'], $query, 'job_title', null, null);
                }
            })
            ->get();

        $employments->transform(function ($item) {
            $item->timePassed = Carbon::parse($item->created_at)->diffInDays(Carbon::today());
            return $item;
        });

        if (!empty($request['timePassed'])) {
            $employments = $employments->where('timePassed', '=', $request['timePassed']);
        }

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $sortedEmployments = $employments->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingEmploymentReviewVariables[request('sort_by')]) ? $this->sortingEmploymentReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $data = $sortedEmployments->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $sortedEmployments->count()];
    }

    /**
     * Helper function to retrieve guarantor data based on provided parameters.
     *
     * @param  array  $request
     * @param  string  $dataFor
     * @return array
     */

    public function guarantorDataHelper($request, $dataFor)
    {
        $guarantor = $this->guarantorsData($request);

        if ($dataFor == 'failedGuarantorReview') {
            $guarantor = $guarantor->whereIn('agency_status', [3, 5]);
        } else {
            $guarantor = $guarantor->where('status', 2)->whereIn('agency_status', [0, 1, 2]);
        }

        $guarantor = $guarantor
            ->where(function ($query) use ($request) {
                if (!empty($request['name'])) {
                    $this->filterWithStringAttributes($request['name'], $query, 'name', null, null);
                }
                if (!empty($request['email'])) {
                    $this->filterWithStringAttributes($request['email'], $query, 'email', null, null);
                }
                if (!empty($request['phone'])) {
                    $this->filterWithStringAttributes($request['phone'], $query, 'phone', null, null);
                }
                if (!empty($request['company_name'])) {
                    $this->filterWithStringAttributes($request['company_name'], $query, 'company_name', null, null);
                }
                if (!empty($request['company_address'])) {
                    $this->filterWithStringAttributes($request['company_address'], $query, 'company_address', null, null);
                }
            })
            ->get();

        $guarantor->transform(function ($item) {
            $item->timePassed = Carbon::parse($item->created_at)->diffInDays(Carbon::today());
            return $item;
        });

        if (!empty($request['timePassed'])) {
            $guarantor = $guarantor->where('timePassed', '=', $request['timePassed']);
        }
        $pageAndPagesize = $this->checkPageAndPagesize($request['page'], $request['pagesize']);

        $sortedGuarantor = $guarantor->{isset($this->sortingAction[$request['sort_action']]) ? $this->sortingAction[$request['sort_action']] : $this->defaultSortingAction}(isset($this->sortingGuarantorReviewVariables[$request['sort_by']]) ? $this->sortingGuarantorReviewVariables[$request['sort_by']] : $this->defaultSortBy, $this->sortingString);

        $data = $sortedGuarantor->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $sortedGuarantor->count()];
    }

    /**
     * Helper function to retrieve landlord data based on provided parameters.
     *
     * @param  array  $request
     * @param  string  $dataFor
     * @return array
     */
    public function landlordDataHelper($request, $dataFor)
    {
        $landlords = $this->landlordsData($request);
        if ($dataFor == 'failedLandlordReview') {
            $landlords = $landlords->whereIn('agency_status', [3, 5]);
        } else {
            $landlords = $landlords->where('status', 2)->whereIn('agency_status', [0, 1, 2]);
        }
        $landlords = $landlords
            ->where(function ($query) use ($request) {

                if (!empty($request['name']))  $this->filterWithStringAttributes($request['name'], $query, 'name', null, null);
                if (!empty($request['email']))  $this->filterWithStringAttributes($request['email'], $query, 'email', null, null);
                if (!empty($request['phone']))  $this->filterWithStringAttributes($request['phone'], $query, 'phone', null, null);
            })
            ->get();

        $landlords->transform(function ($landlord) {
            $landlord->timePassed = Carbon::parse($landlord->created_at)->diffInDays(Carbon::today());
            return $landlord;
        });
        if (!empty($request['timePassed'])) {
            $landlords = $landlords->where('timePassed', '=', $request['timePassed']);
        }
        $sortedLandlords = $landlords->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordReviewVariables[request('sort_by')]) ? $this->sortingLandlordReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $data = $sortedLandlords->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $sortedLandlords->count()];
    }

    /**
     * Helper function to retrieve quaterly data based on provided parameters.
     *
     * @param  array  $request
     * @param  string  $dataFor
     * @return array
     */
    public function quarterlyDataHelper($request, $dataFor)
    {
        $quarterly = $this->quarterlyData($request);
        if ($dataFor == 'failedQuarterlyReview') {
            $quarterly = $quarterly->whereIn('agency_status', [3, 5]);
        } else {
            $quarterly = $quarterly->where('status', 2)->whereIn('agency_status', [0, 1, 2]);
        }
        $quarterly = $quarterly
            ->where(function ($query) use ($request) {

                if (!empty($request['close_bal'])) $this->filterWithNumberAttributes($request['close_bal'], $query, 'close_bal', null, null);
                if (!empty($request['type']))  $this->filterWithStringAttributes($request['type'], $query, 'type', null, null);
            })
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordReviewVariables[request('sort_by')]) ? $this->sortingLandlordReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $data = $quarterly->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        return ['data' => $data, 'total' => $quarterly->count()];
    }

    /**
     * Retrieves employments data based on the provided request parameters.
     *
     * @param  array  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function employmentsData($request)
    {
        $creatorName = $this->creatorNameCreator($request);
        $tenancyReferenceCreator = $this->tenancyReferenceCreator($request);
        $creatorId  = $this->creatorIdHelper($request['id']);

        $employments = EmploymentReference::where('agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')
            ->with('applicants.applicantbasic:id,app_name,l_name')->with('applicants:id,tenancy_id')
            ->whereHas('applicants.applicantbasic', $creatorName)->with(['applicants.applicantbasic' => $creatorName])
            ->whereHas('applicants', $tenancyReferenceCreator)->with(['applicants' => $tenancyReferenceCreator])
            ->when($request['id'] != 0, function ($query) use ($creatorId) {
                return $query->whereHas('applicants', $creatorId)->with(['applicants' => $creatorId]);
            });

        return $employments;
    }

    /**
     * Retrieves landlords data based on the provided request parameters.
     *
     * @param  array  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function landlordsData($request)
    {
        $creatorName = $this->creatorNameCreator($request);
        $tenancyReferenceCreator = $this->tenancyReferenceCreator($request);
        $creatorId  = $this->creatorIdHelper($request['id']);

        $landlords = LandlordReference::where('agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')
            ->with('applicants.applicantbasic:id,app_name,l_name')->with('applicants:id,tenancy_id')
            ->whereHas('applicants.applicantbasic', $creatorName)->with(['applicants.applicantbasic' => $creatorName])
            ->whereHas('applicants', $tenancyReferenceCreator)->with(['applicants' => $tenancyReferenceCreator])
            ->when($request['id'] != 0, function ($query) use ($creatorId) {
                return $query->whereHas('applicants', $creatorId)->with(['applicants' => $creatorId]);
            });

        return $landlords;
    }

    /**
     * Retrieves quaterly data based on the provided request parameters.
     *
     * @param  array  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function quarterlyData($request)
    {
        $creatorName = $this->creatorNameCreator($request);
        $tenancyReferenceCreator = $this->tenancyReferenceCreator($request);
        $creatorId  = $this->creatorIdHelper($request['id']);

        $quarterly = QuarterlyReference::where('agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')
            ->with('applicants.applicantbasic:id,app_name,l_name')->with('applicants:id,tenancy_id')
            ->whereHas('applicants.applicantbasic', $creatorName)->with(['applicants.applicantbasic' => $creatorName])
            ->whereHas('applicants', $tenancyReferenceCreator)->with(['applicants' => $tenancyReferenceCreator])
            ->when($request['id'] != 0, function ($query) use ($creatorId) {
                return $query->whereHas('applicants', $creatorId)->with(['applicants' => $creatorId]);
            });

        return $quarterly;
    }

    /**
     * Retrieves guarantor data based on the provided request parameters.
     *
     * @param  array  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function guarantorsData($request)
    {
        $creatorName = $this->creatorNameCreator($request);
        $tenancyReferenceCreator = $this->tenancyReferenceCreator($request);
        $creatorId  = $this->creatorIdHelper($request['id']);

        $guarantors = GuarantorReference::where('agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')->with('applicants:id,tenancy_id,app_name,l_name')
            ->whereHas('applicants.applicantbasic', $creatorName)->with(['applicants.applicantbasic' => $creatorName])
            ->whereHas('applicants', $tenancyReferenceCreator)->with(['applicants' => $tenancyReferenceCreator])
            ->when($request['id'] != 0, function ($query) use ($creatorId) {
                return $query->whereHas('applicants', $creatorId)->with(['applicants' => $creatorId]);
            });

        return $guarantors;
    }

    /**
     * Creates a dynamic query builder function for filtering records based on the creator's name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Closure
     */
    public function creatorNameCreator($request)
    {
        return  function ($query) use ($request) {

            if (($request['applicant_name'] === '[empty]') || ($request['applicant_name'] ===  '[nonempty]')) {

                $query->{$this->checkEmptyNotEmptyValue($request['applicant_name'])}('app_name');
            } else if (strpos($request['applicant_name'], '||')  !== false || strpos($request['applicant_name'], '&&')  !== false) {

                $operatorValue = $this->workWithMultipleAttributes($request['applicant_name'], 'app_name', 'l_name', null);
                $query->whereRaw($operatorValue[1]['first_0'], $operatorValue[1]['second_0'])
                    ->{$operatorValue[0] == '||' ? 'orWhereRaw' : 'whereRaw'}($operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
            } elseif (substr($request['applicant_name'], 0, 4) === 'rgx:') {
                $query->whereRaw("concat(app_name,' ',l_name) ~* ?", $this->takeRegexSubstring($request['applicant_name']));
            } else {

                $operatorValue = $this->workWithSingleAttribute($request['applicant_name'], 'app_name', 'l_name', null);
                $query->whereRaw($operatorValue['first_0'], $operatorValue['second_0']);
            }
        };
    }

    /**
     * Creates a dynamic query builder function for filtering records based on tenancy reference.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Closure
     */
    public function tenancyReferenceCreator($request)
    {
        return  function ($query) use ($request) {
            $query->whereIn('tenancy_id', $this->findTenanciesFromReferences($request));
        };
    }

    /**
     * Retrieves applicants data based on the provided request parameters.
     *
     * @param  array  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applicantsData($request)
    {
        $cancelledTenancyStatusForTenancy = $this->cancelledTenancyStatusForTenancy();

        $applicants = Applicant::where('agency_id', authAgencyId());
        if ((superAdmin() || agencyStaff()) && $request['id'] != 0) {
            $applicants = Applicant::where('agency_id', authAgencyId())->where('creator_id', $request['id']);
        }
        return $applicants
            ->whereHas('tenancies', $cancelledTenancyStatusForTenancy)->with(['tenancies' => $cancelledTenancyStatusForTenancy]);
    }

    /**
     * Retrieves tenancies data based on the provided request parameters.
     *
     * @param  array  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function tenanciesData($request)
    {
        if ((superAdmin() || agencyStaff()) && $request['id'] == 0) {
            $tenancies = Tenancy::where('agency_id', authAgencyId());
        } else {
            $tenancies = Tenancy::where('agency_id', authAgencyId())->where('creator_id', $request['id']);
        }
        return $tenancies;
    }

    /**
     * Retrieves properties data based on the provided request parameters.
     *
     * @param  array  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function propertiesData($request)
    {
        if ((superAdmin() || agencyStaff()) && $request['id'] == 0) {
            $properties = Property::where('agency_id', authAgencyId());
        } else {
            $properties = Property::where('agency_id', authAgencyId())->where('creator_id', $request['id']);
        }
        return $properties;
    }

    /**
     * Find tenancies based on the provided tenancy reference.
     *
     * @param  array  $request
     * @return \Illuminate\Support\Collection
     */
    public function findTenanciesFromReferences($request)
    {
        $tenancy = Tenancy::where('agency_id', authAgencyId());
        if (($request['tenancy_reference'] === '[empty]') || ($request['tenancy_reference'] ===  '[nonempty]')) {
            $tenancy->{$this->checkEmptyNotEmptyValue($request['tenancy_reference'])}('reference');
        } else if (strpos($request['tenancy_reference'], '||')  !== false || strpos($request['tenancy_reference'], '&&')  !== false) {

            $operatorValue = $this->workWithMultipleAttributes($request['tenancy_reference'], 'reference', null, null);
            $tenancy->whereRaw($operatorValue[1]['first_0'], $operatorValue[1]['second_0']);
            $tenancy->{$operatorValue[0] == '||' ? 'orWhereRaw' : 'whereRaw'}($operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
        } elseif (substr($request['tenancy_reference'], 0, 4) === 'rgx:') {
            $tenancy->whereRaw("concat(reference) ~* ?", $this->takeRegexSubstring($request['tenancy_reference']));
        } else {

            $operatorValue = $this->workWithSingleAttribute($request['tenancy_reference'], 'reference', null, null);
            $tenancy->whereRaw($operatorValue['first_0'], $operatorValue['second_0']);
        }

        return $tenancy->pluck('id');
    }

    /**
     * Get the query scope for cancelled tenancy status.
     *
     * @return \Closure
     */
    public function cancelledTenancyStatusForTenancy()
    {
        return function ($query) {
            $query->where('status', '!=', 10);
        };
    }

    /**
     * Get the query scope for creator ID.
     *
     * @param  int  $id
     * @return \Closure
     */
    public function creatorIdHelper($id)
    {
        return function ($query) use ($id) {
            $query->where('creator_id', $id);
        };
    }

    /**
     * Get the query scope for working with string attributes.
     *
     * @param  mixed  $rv
     * @param  string  $f
     * @param  string|null  $s
     * @param  string|null  $t
     * @return \Closure
     */
    public function wfc($rv, $f, $s, $t)
    {
        return function ($query) use ($rv, $f, $s, $t) {
            $this->filterWithStringAttributes($rv, $query, $f, $s, $t);
        };
    }
}
