<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Applicant;
use App\Models\EmploymentReference;
use App\Models\GuarantorReference;
use App\Models\LandlordReference;
use App\Models\Tenancy;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Traits\AllPermissions;
use App\Traits\ConfigrationTrait;
use App\Traits\SortingActionTrait;
use Illuminate\Support\Collection;
use App\Models\QuarterlyReference;
use App\Models\InterimInspection;
use App\Models\Property;

class DashboardController extends Controller
{
    use AllPermissions, ConfigrationTrait, SortingActionTrait;

    /**
     * Fetches the dashboard data for the agency.
     *
     * @return \Illuminate\Http\Response
     */
    public function agencyDashboard()
    {
        return response()->json($this->dashboardDataFetchHelper(0));
    }

    /**
     * Fetches the dashboard data for a specific staff member in the agency.
     *
     * @param int $id The ID of the staff member
     * @return \Illuminate\Http\Response
     */
    public function agencyFetchStaffDashboard($id)
    {
        return response()->json($this->dashboardDataFetchHelper($id));
    }

    /**
     * Retrieves a list of staff members for the current agency.
     *
     * @return \Illuminate\Http\Response
     */
    public function staffList()
    {
        return response()
            ->json([
                "saved" => true,
                "staffList" => User::where('agency_id', authAgencyId())->latest()->get(['id', 'name', 'l_name'])
            ]);
    }

    /**
     * Retrieves a list of EPC certificate expiry withIn 30 days.
     *
     * @param int $id The ID of the property
     * @return array
     */
    public function epcCertificateExpiryWithInThirtyDays($id)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $properties = $this->propertiesData($id)->where('epc_expiry_date', '>=', $today)
            ->where('epc_expiry_date', '<=', $thirtyDaysFromNow)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->propertyDescStatusArray : $this->propertyAscStatusArray;
            while ($i <  $this->propertyStatusArrayCount) {
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

        return  ['data' => $data, 'total' => $properties->count()];
    }

    /**
     * Retrieves a list of Gas certificate expiry withIn 30 days.
     *
     * @param int $id The ID of the property
     * @return array
     */
    public function gasCertificateExpiryWithInThirtyDays($id)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $properties = $this->propertiesData($id)->where('gas_expiry_date', '>=', $today)
            ->where('gas_expiry_date', '<=', $thirtyDaysFromNow)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->propertyDescStatusArray : $this->propertyAscStatusArray;
            while ($i <  $this->propertyStatusArrayCount) {
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

        return  ['data' => $data, 'total' => $properties->count()];
    }

    /**
     * Retrieves a list of Electric certificate expiry withIn 30 days.
     *
     * @param int $id The ID of the property
     * @return array
     */
    public function eicrCertificateExpiryWithInThirtyDays($id)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $properties = $this->propertiesData($id)->where('electric_expiry_date', '>=', $today)
            ->where('electric_expiry_date', '<=', $thirtyDaysFromNow)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->propertyDescStatusArray : $this->propertyAscStatusArray;
            while ($i <  $this->propertyStatusArrayCount) {
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

        return  ['data' => $data, 'total' => $properties->count()];
    }

    /**
     * Retrieves a list of HMO certificate expiry withIn 30 days.
     *
     * @param int $id The ID of the property
     * @return array
     */
    public function hmoCertificateExpiryWithInThirtyDays($id)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $properties = $this->propertiesData($id)->where('hmo_expiry_date', '>=', $today)
            ->where('hmo_expiry_date', '<=', $thirtyDaysFromNow)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->propertyDescStatusArray : $this->propertyAscStatusArray;
            while ($i <  $this->propertyStatusArrayCount) {
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

        return  ['data' => $data, 'total' => $properties->count()];
    }

    /**
     * Retrieves a list of Fire Alarm certificate expiry withIn 30 days.
     *
     * @param int $id The ID of the property
     * @return array
     */
    public function fireAlarmCertificateExpiryWithInThirtyDays($id)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $properties = $this->propertiesData($id)->where('fire_alarm_expiry_date', '>=', $today)
            ->where('fire_alarm_expiry_date', '<=', $thirtyDaysFromNow)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->propertyDescStatusArray : $this->propertyAscStatusArray;
            while ($i <  $this->propertyStatusArrayCount) {
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

        return  ['data' => $data, 'total' => $properties->count()];
    }

    /**
     * Retrieves a list of applicants awaiting review.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function awaitingApplicantReview($id)
    {
        $applicants = $this->applicantsData($id)->where('log_status', 1)->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i <  $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return  ['data' => $data, 'total' => $applicants->count()];
    }

    /**
     * Retrieves a list of applicants right to rent expired.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function applicantRightToExpired($id)
    {
        $applicants = $this->applicantsData($id)->where('right_to_rent', '<', now())->get();
        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i <  $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return  ['data' => $data, 'total' => $applicants->count()];
    }

    /**
     * Retrieves a list of applicants right to rent expired withIn 30 Days.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function applicantRightToExpiredWithInThirtyDays($id)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $applicants = $this->applicantsData($id)
            ->whereDate('right_to_rent', '>=', $today)
            ->whereDate('right_to_rent', '<=', $thirtyDaysFromNow)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i <  $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return  ['data' => $data, 'total' => $applicants->count()];
    }

    /**
     * Retrieves a list of applicants problematic.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function problematicApplicantData($id)   #1
    {
        $problematicApplicant = Applicant::where('agency_id', authAgencyId())
            ->with('applicantbasic')->whereIn('status', [10, 11, 12]);

        if ($id != 0) {
            $problematicApplicant = $problematicApplicant->where('applicants.creator_id', $id);
        }

        $problematicApplicant = $problematicApplicant
            ->with('tenancies:id,reference')
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingProblematicApplicantVariables[request('sort_by')]) ? $this->sortingProblematicApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $problematicApplicant->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $problematicApplicant->count()];
    }

    /**
     * Retrieves a list of employment awaiting review.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function awaitingEmploymentReview($id)
    {
        $awaitingEmploymentReview = $this->employmentsData($id)
            ->where('employment_references.status', 2)
            ->whereIn('employment_references.agency_status', [0, 1, 2])
            ->get();

        $awaitingEmploymentReview->transform(function ($item) {
            $item->timePassed = Carbon::parse($item->created_at)->diffInDays(Carbon::today());
            return $item;
        });

        $sortedEmploymentReview = $awaitingEmploymentReview->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingEmploymentReviewVariables[request('sort_by')]) ? $this->sortingEmploymentReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $sortedEmploymentReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $sortedEmploymentReview->count()];
    }

    /**
     * Retrieves a list of guarantor awaiting review.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function awaitingGuarantorReview($id)
    {
        $awaitingGuarantorReview = $this->guarantorsData($id)
            ->where('guarantor_references.status', 2)
            ->whereIn('guarantor_references.agency_status', [0, 1, 2])
            ->get();

        // Calculate time passed for each guarantor reference
        $awaitingGuarantorReview->transform(function ($item) {
            $item->timePassed = Carbon::parse($item->created_at)->diffInDays(Carbon::today());
            return $item;
        });

        $sortedGuarantorReview = $awaitingGuarantorReview->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingGuarantorReviewVariables[request('sort_by')]) ? $this->sortingGuarantorReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $sortedGuarantorReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $sortedGuarantorReview->count()];
    }

    /**
     * Retrieves a list of landlord awaiting review.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function awaitingLandlordReview($id)
    {
        $awaitingLandlordReview = $this->landlordsData($id)
            ->where('landlord_references.status', 2)
            ->whereIn('landlord_references.agency_status', [0, 1, 2])
            ->get();

        // Calculate time passed for each landlord reference
        $awaitingLandlordReview->transform(function ($item) {
            $item->timePassed = Carbon::parse($item->created_at)->diffInDays(Carbon::today());
            return $item;
        });

        $sortedLandlordReview = $awaitingLandlordReview->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordReviewVariables[request('sort_by')]) ? $this->sortingLandlordReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $sortedLandlordReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $sortedLandlordReview->count()];
    }

    /**
     * Retrieves a list of quaterly awaiting review.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function awaitingQuarterlyReview($id)  #new
    {
        $awaitingQuarterlyReview = $this->quarterlyData($id)
            ->where('quarterly_references.status', 2)
            ->whereIn('quarterly_references.agency_status', [0, 1, 2])
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordReviewVariables[request('sort_by')]) ? $this->sortingLandlordReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $awaitingQuarterlyReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $awaitingQuarterlyReview->count()];
    }

    /**
     * Retrieves a list of awaiting TA review.
     *
     * @param int $id The ID of the applicant
     * @return array
     */
    public function awaitingTAReview($id)  #12
    {
        $tenancies = $this->tenanciesData($id)->where('status', 18)->get();

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
                foreach ($tenancies as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
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

        return ['data' => $data, 'total' => $tenancies->count()];
    }

    /**
     * Retrieve failed employment reviews for a specific user.
     *
     * @param  int  $id The ID of the employment
     * @return array
     */
    public function failedEmploymentReview($id)  #5
    {
        $failedEmploymentReview = $this->employmentsData($id)->whereIn('employment_references.agency_status', [3, 5])
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingEmploymentReviewVariables[request('sort_by')]) ? $this->sortingEmploymentReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $failedEmploymentReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $failedEmploymentReview->count()];
    }

    /**
     * Retrieve failed guarantor reviews for a specific user.
     *
     * @param  int  $id The ID of the employment
     * @return array
     */
    public function failedGuarantorReview($id)  #6
    {
        $failedGuarantorReview = $this->guarantorsData($id)->whereIn('guarantor_references.agency_status', [3, 5])
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingGuarantorReviewVariables[request('sort_by')]) ? $this->sortingGuarantorReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $failedGuarantorReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $failedGuarantorReview->count()];
    }

    /**
     * Retrieve failed landlord reviews for a specific landlord.
     *
     * @param  int  $id The ID of the employment
     * @return array
     */
    public function failedLandlordReview($id)  #7
    {
        $failedLandlordReview = $this->landlordsData($id)->whereIn('landlord_references.agency_status', [3, 5])
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordReviewVariables[request('sort_by')]) ? $this->sortingLandlordReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $failedLandlordReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $failedLandlordReview->count()];
    }

    /**
     * Retrieve failed  quaterly reviews for a specific quaterly.
     *
     * @param  int  $id The ID of the quaterly
     * @return array
     */
    public function failedQuarterlyReview($id)  #new
    {
        $failedLandlordReview = $this->quarterlyData($id)->whereIn('quarterly_references.agency_status', [3, 5])
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordReviewVariables[request('sort_by')]) ? $this->sortingLandlordReviewVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $failedLandlordReview->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $failedLandlordReview->count()];
    }

    /**
     * Retrieve progress for tenancies starting soon.
     *
     * @param  int  $id
     * @return array
     */
    public function progressButStartingSoon($id)  #8
    {
        $tenancies = $this->tenanciesData($id)
            ->whereDate('t_start_date', '<=', now()->addDays(13))
            ->whereNotIn('status', [7, 9, 10, 11])
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
                foreach ($tenancies as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
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

        return ['data' => $data, 'total' => $tenancies->count()];
    }

    /**
     * Retrieve applicants awaiting application form.
     *
     * @param  int  $id
     * @return array
     */
    public function awaitingApplicantForm($id)  #9
    {
        $applicants =  $this->applicantsData($id)->where('log_status', 0)->where('status', 1)->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i <  $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return  ['data' => $data, 'total' => $applicants->count()];
    }

    /**
     * Retrieve applicants awaiting reference.
     *
     * @param  int  $id
     * @return array
     */
    public function awaitingReference($id)  #10
    {
        $applicants =  $this->applicantsData($id)->where('log_status', 1)
            ->where('status', 2)->where('ref_status', 0)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i <  $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return  ['data' => $data, 'total' => $applicants->count()];
    }

    /**
     * Retrieve applicants awaiting signing.
     *
     * @param  int  $id
     * @return array
     */
    public function awaitingSigning($id) #11
    {
        $applicants =  $this->applicantsData($id)
            ->where('log_status', 1)->where('status', 5)
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i <  $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return  ['data' => $data, 'total' => $applicants->count()];
    }

    /**
     * Retrieves recently finalized tenancies within the last 14 days.
     *
     * @param  int  $id
     * @return array
     */
    public function recently_finalized($id)  #12
    {
        $tenancies = $this->tenanciesData($id)
            ->where('status', 11)
            ->where('updated_at', '>', now()->subDays(14))
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
                foreach ($tenancies as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
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

        return ['data' => $data, 'total' => $tenancies->count()];
    }

    /**
     * Retrieves tenancies awaiting tenancy agreements sending.
     *
     * @param  int  $id
     * @return array
     */
    public function awaitingTASending($id)  #9
    {
        $tenancies = $this->tenanciesData($id)->where('status', 17)->get();

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
                foreach ($tenancies as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
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

        return ['data' => $data, 'total' => $tenancies->count()];
    }

    /**
     * Retrieves tenancies marked as complete within the next 8 days.
     *
     * @param  int  $id
     * @return array
     */
    public function tenancyCompleteNew($id) #13
    {
        $tenancies = $this->tenanciesData($id)
            ->where('status', 11)
            ->where('type', 1)
            ->whereBetween('t_start_date', [now(), now()->addDays(8)])
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
                foreach ($tenancies as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
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

        return ['data' => $data, 'total' => $tenancies->count()];
    }

    /**
     * Retrieves renew tenancies marked as complete within the next 8 days.
     *
     * @param  int  $id
     * @return array
     */
    public function tenancyCompleteRenew($id) #13
    {
        $tenancies = $this->tenanciesData($id)
            ->where('type', 2)
            ->whereBetween('t_start_date', [now(), now()->addDays(8)])
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
                foreach ($tenancies as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
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

        return ['data' => $data, 'total' => $tenancies->count()];
    }

    /**
     * Retrieves partial renew tenancies marked as complete within the next 8 days.
     *
     * @param  int  $id
     * @return array
     */
    public function tenancyCompletePartialRenew($id) #13
    {
        $tenancies = $this->tenanciesData($id)
            ->where('status', 11)
            ->where('type', 3)
            ->whereBetween('t_start_date', [now(), now()->addDays(8)])
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
                foreach ($tenancies as $key => $ti) {
                    if ($ti->{$actionVariable} ==  $sortAccordingArray[$i]) {
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

        return ['data' => $data, 'total' => $tenancies->count()];
    }

    /**
     * Calculate the average time taken to complete a tenancy.
     *
     * @return float|null
     */
    public function tenancyAverageTime()
    {
        return round((float)Tenancy::where('agency_id', authAgencyId())->where('days_to_complete', '>', 0)->avg('days_to_complete'), 2);
    }

    /**
     * Retrieves the active tenancy.
     *
     * @return float|null
     */
    public function activeTenancies()
    {
        $today = Carbon::today();
        $tenancies = Tenancy::whereIn('status', [7, 11])
            ->whereDate('t_start_date', '<=', $today->toDateString())
            ->whereDate('t_end_date', '>=', $today->toDateString())
            ->count();

        return $tenancies;
    }
    /**
     * Retrieves the complete tenancy in last 2 weeks.
     *
     * @return float|null
     */
    public function completeTenanciesInLast2Weeks($id)
    {
        $twoWeeksAgo = Carbon::today()->subWeeks(2);
        $today = Carbon::today();

        $tenancies = Tenancy::where('agency_id', $id)->whereIn('status', [7, 11])
            ->whereBetween('updated_at', [$twoWeeksAgo, $today])
            ->count();

        return $tenancies;
    }
    /**
     * Retrieves dashboard response data for a specific user.
     *
     * @param  int  $id
     * @return array
     */
    public function dashboardResponse($id)
    {
        $failedLandlordReview = $this->failedLandlordReview($id);
        $failedGuarantorReview = $this->failedGuarantorReview($id);
        $failedEmploymentReview = $this->failedEmploymentReview($id);
        $failedQuarterlyReview = $this->failedQuarterlyReview($id);
        $awaitingLandlordReview = $this->awaitingLandlordReview($id);
        $awaitingGuarantorReview = $this->awaitingGuarantorReview($id);
        $awaitingEmploymentReview = $this->awaitingEmploymentReview($id);
        $awaitingQuarterlyReview = $this->awaitingQuarterlyReview($id);
        $awaitingTAReview = $this->awaitingTAReview($id);
        $awaitingTASending = $this->awaitingTASending($id);
        $problematicApplicant = $this->problematicApplicantData($id);
        $applicantRightToExpired = $this->applicantRightToExpired($id);
        $applicantRightToExpiredWithInThirtyDays = $this->applicantRightToExpiredWithInThirtyDays($id);

        $action_required = $this->countActionRequired(
            $failedLandlordReview['total'],
            $failedGuarantorReview['total'],
            $failedEmploymentReview['total'],
            $failedQuarterlyReview['total'],
            $awaitingLandlordReview['total'],
            $awaitingGuarantorReview['total'],
            $awaitingEmploymentReview['total'],
            $awaitingQuarterlyReview['total'],
            $awaitingTAReview['total'],
            $awaitingTASending['total'],
            $problematicApplicant['total'],
            $applicantRightToExpired['total'],
            $applicantRightToExpiredWithInThirtyDays['total']
        );

        $awaitingApplicantReview = $this->awaitingApplicantReview($id);
        $awaitingApplicantForm = $this->awaitingApplicantForm($id);
        $awaitingReference = $this->awaitingReference($id);
        $awaitingSigning = $this->awaitingSigning($id);
        $in_progress = $this->countInProgress($awaitingReference['total'], $awaitingSigning['total'], $awaitingApplicantForm['total']);

        $recently_finalized = $this->recently_finalized($id);
        $progressButStartingSoon = $this->progressButStartingSoon($id);
        $tenancyCompleteNew = $this->tenancyCompleteNew($id);
        $tenancyCompleteRenew = $this->tenancyCompleteRenew($id);
        $tenancyCompletePartialRenew = $this->tenancyCompletePartialRenew($id);
        $epcCertificateExpiryWithInThirtyDays = $this->epcCertificateExpiryWithInThirtyDays($id);
        $gasCertificateExpiryWithInThirtyDays = $this->gasCertificateExpiryWithInThirtyDays($id);
        $eicrCertificateExpiryWithInThirtyDays = $this->eicrCertificateExpiryWithInThirtyDays($id);
        $hmoCertificateExpiryWithInThirtyDays = $this->hmoCertificateExpiryWithInThirtyDays($id);
        $fireAlarmCertificateExpiryWithInThirtyDays = $this->fireAlarmCertificateExpiryWithInThirtyDays($id);
        $tenancyAverageTime = $this->tenancyAverageTime();
        $activeTenancies = $this->activeTenancies();
        $completeTenanciesInLast2Weeks = $this->completeTenanciesInLast2Weeks($id);

        return [
            "saved" => true,

            //Action required
            "action_required" => $action_required,
            "failedLandlordReview" => $failedLandlordReview,
            "failedGuarantorReview" => $failedGuarantorReview,
            "failedEmploymentReview" => $failedEmploymentReview,
            "failedQuarterlyReview" => $failedQuarterlyReview,
            "awaitingLandlordReview" => $awaitingLandlordReview,
            "awaitingGuarantorReview" => $awaitingGuarantorReview,
            "awaitingEmploymentReview" => $awaitingEmploymentReview,
            "awaitingQuarterlyReview" => $awaitingQuarterlyReview,
            "awaitingTAReview" => $awaitingTAReview,
            "awaitingTASending" => $awaitingTASending,
            "problematicApplicant" => $problematicApplicant,
            "applicantRightToExpired" => $applicantRightToExpired,
            "applicantRightToExpiredWithInThirtyDays" => $applicantRightToExpiredWithInThirtyDays,
            "tenancyAverageTime" => $tenancyAverageTime,
            "completeTenanciesInLast2Weeks" => $completeTenanciesInLast2Weeks,
            "activeTenancies" => $activeTenancies,
            //Accelerated Application
            "accelerated_application" => $progressButStartingSoon['total'],
            "progressButStartingSoon" => $progressButStartingSoon,

            //In progress
            "in_progress" => $in_progress,
            "awaitingApplicantForm" => $awaitingApplicantForm,
            "awaitingApplicantReview" => $awaitingApplicantReview,
            "awaitingReference" => $awaitingReference,
            "awaitingSigning" => $awaitingSigning,

            //Recently Finalized
            "recently_finalised" => $recently_finalized['total'],
            "recentlyFinalised" => $recently_finalized,

            //Tenancy complete
            "tenancy_complete_new" => $tenancyCompleteNew['total'],
            "tenancyCompleteNew" => $tenancyCompleteNew,
            "tenancy_complete_renew" => $tenancyCompleteRenew['total'],
            "tenancyCompleteRenew" => $tenancyCompleteRenew,
            "tenancy_complete_partial_renew" => $tenancyCompletePartialRenew['total'],
            "tenancyCompletePartialRenew" => $tenancyCompletePartialRenew,

            // Property Certificate
            "epcCertificateExpiryWithInThirtyDays" => $epcCertificateExpiryWithInThirtyDays['total'],
            "epcCertificateExpiryWithInThirtyDays" => $epcCertificateExpiryWithInThirtyDays,

            "gasCertificateExpiryWithInThirtyDays" => $gasCertificateExpiryWithInThirtyDays['total'],
            "gasCertificateExpiryWithInThirtyDays" => $gasCertificateExpiryWithInThirtyDays,

            "eicrCertificateExpiryWithInThirtyDays" => $eicrCertificateExpiryWithInThirtyDays['total'],
            "eicrCertificateExpiryWithInThirtyDays" => $eicrCertificateExpiryWithInThirtyDays,

            "hmoCertificateExpiryWithInThirtyDays" => $hmoCertificateExpiryWithInThirtyDays['total'],
            "hmoCertificateExpiryWithInThirtyDays" => $hmoCertificateExpiryWithInThirtyDays,

            "fireAlarmCertificateExpiryWithInThirtyDays" => $fireAlarmCertificateExpiryWithInThirtyDays['total'],
            "fireAlarmCertificateExpiryWithInThirtyDays" => $fireAlarmCertificateExpiryWithInThirtyDays
        ];
    }

    /**
     * Calculate the total count of action required.
     *
     * @param int $a
     * @param int $b
     * @param int $c
     * @param int $d
     * @param int $e
     * @param int $f
     * @param int $g
     * @param int $h
     * @param int $i
     * @param int $j
     * @param int $k
     * @return int
     */
    public function countActionRequired($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k)
    {
        return $a + $b + $c + $d + $e + $f + $g + $h + $i + $j + $k;
    }

    /**
     * Calculate the total count of tasks in progress.
     *
     * @param int $a
     * @param int $b
     * @param int $c
     * @return int
     */
    public function countInProgress($a, $b, $c)
    {
        return $a + $b + $c;
    }

    /**
     * Fetch dashboard data helper function.
     *
     * @param int $id
     * @return mixed
     */
    public function dashboardDataFetchHelper($id)
    {
        return $this->dashboardResponse($id);
    }

    /**
     * Retrieve property data.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function propertiesData($id)
    {
        $properties = Property::where('agency_id', authAgencyId())
            ->with('landlords:id,f_name,l_name,street,town,country,post_code')
            ->with('users:id,name,l_name');

        if ($id != 0) {
            $properties = $properties->where('creator_id', $id);
        }
        return $properties;
    }

    /**
     * Retrieve applicant data.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applicantsData($id)
    {
        $cancelledTenancyStatusForTenancy = $this->cancelledTenancyStatusForTenancy();

        $applicants = Applicant::where('agency_id', authAgencyId())
            ->whereHas('tenancies', $cancelledTenancyStatusForTenancy)->with(['tenancies' => $cancelledTenancyStatusForTenancy])
            ->with('tenancies:id,reference,pro_address')
            ->with('users:id,name,l_name')
            ->with('applicantbasic');

        if ($id != 0) {
            $applicants = $applicants->where('creator_id', $id);
        }

        return $applicants;
    }

    /**
     * Retrieve tenancy data.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function tenanciesData($id)
    {
        $tenancies = Tenancy::where('agency_id', authAgencyId())
            ->with('landlords:id,f_name,l_name,street,town,country,post_code')
            ->with('properties:id,post_code,bedroom')
            ->with('latest_update:tenancy_id,event_type')
            ->with('users:id,name,l_name');

        if ($id != 0) {
            $tenancies = $tenancies->where('creator_id', $id);
        }
        return $tenancies;
    }

    /**
     * Retrieve landlord data.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function landlordsData($id)
    {
        $landlords = LandlordReference::where('landlord_references.agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')->with('applicants.applicantbasic')->with('applicants:id,tenancy_id,status,applicant_id');

        if ($id != 0) {
            $landlords = $landlords->whereHas('applicants', $this->creatorIdToNameCreator($id))->with(['applicants' => $this->creatorIdToNameCreator($id)]);
        }

        return $landlords;
    }

    /**
     * Retrieve guarantor data.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function guarantorsData($id)
    {
        $guarantors = GuarantorReference::where('guarantor_references.agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')
            ->with('applicants.applicantbasic')->with('applicants:id,tenancy_id,applicant_id,status');

        if ($id != 0) {
            $guarantors = $guarantors->whereHas('applicants', $this->creatorIdToNameCreator($id))->with(['applicants' => $this->creatorIdToNameCreator($id)]);
        }
        return $guarantors;
    }

    /**
     * Retrieve employment data.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function employmentsData($id)
    {
        $employments = EmploymentReference::where('agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')
            ->with('applicants.applicantbasic')->with('applicants:id,tenancy_id,applicant_id,status');

        if ($id != 0) {
            $employments = $employments
                ->whereHas('applicants', $this->creatorIdToNameCreator($id))->with(['applicants' => $this->creatorIdToNameCreator($id)]);
        }
        return $employments;
    }

    /**
     * Retrieve quaterly data.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function quarterlyData($id)
    {
        $quarterly = QuarterlyReference::where('agency_id', authAgencyId())
            ->with('applicants.tenancies:id,reference')
            ->with('applicants.applicantbasic')
            ->with('applicants:id,tenancy_id,status,applicant_id');

        if ($id != 0) {
            $quarterly = $quarterly->whereHas('applicants', $this->creatorIdToNameCreator($id))->with(['applicants' => $this->creatorIdToNameCreator($id)]);
        }

        return $quarterly;
    }

    /**
     * Returns a closure to filter by creator id.
     *
     * @param  int  $id The ID of the creator.
     * @return \Closure
     */
    public function creatorIdToNameCreator($id)
    {
        return function ($query) use ($id) {
            $query->where('creator_id', $id);
        };
    }

    /**
     * Returns a closure to filter out cancelled tenancy status.
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
     * Retrieve interim inspections based on specified criteria.
     *
     * @param string $authAgencyId
     * @param string $status
     * @param string|null $comparisonOperator
     * @param string|null $comparisonValue
     * @return \Illuminate\Http\Response
     */
    private function getInterimInspections($authAgencyId, $status, $comparisonOperator = null, $comparisonValue = null)
    {
        $currentMonth = now()->format('F Y');
        $query = InterimInspection::where('agency_id', $authAgencyId)
            ->where('is_done', $status);

        if ($comparisonOperator && $comparisonValue) {
            $query->whereRaw("TO_DATE(inspection_month, 'Month YYYY') $comparisonOperator TO_DATE(?, 'Month YYYY')", [$comparisonValue]);
        }

        $inspections = $query->get();

        $inspectionData = $inspections->map(function ($inspection) {
            $tenancy = Tenancy::where('id', $inspection->tenancy_id)->with(['properties', 'landlords:id,f_name,l_name,email'])->firstOrFail();
            $inspectionArray = $inspection->toArray();
            $inspectionArray['landlord_email'] = $tenancy->landlords->email;
            return $inspectionArray;
        });

        return $inspectionData;
    }


    /**
     * Retrieve all interim inspections for the current month.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllInterimInspection()
    {
        $authAgencyId = authAgencyId();
        $mm = EmailTemplate::where('agency_id', $authAgencyId)->where('mail_code', 'II')->first();
        $allInspection = $this->getInterimInspections($authAgencyId, false, '=', now()->format('F Y'));

        // Sorting
        $allInspection = $allInspection
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingInterimInspectionVariables[request('sort_by')]) ? $this->sortingInterimInspectionVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $allInspection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $allInspection->count(), 'mail_template' => $mm];
    }

    /**
     * Retrieve all past interim inspections.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllPastInterimInspection()
    {
        $authAgencyId = authAgencyId();
        $mm = EmailTemplate::where('agency_id', $authAgencyId)->where('mail_code', 'II')->first();
        $pastInspection = $this->getInterimInspections($authAgencyId, false, '<', now()->subMonth()->format('F Y'));

        // Sorting
        $pastInspection = $pastInspection
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingInterimInspectionVariables[request('sort_by')]) ? $this->sortingInterimInspectionVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $pastInspection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $pastInspection->count(), 'mail_template' => $mm];
    }

    /**
     * Retrieve all completed interim inspections.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllDoneInterimInspection()
    {
        $authAgencyId = authAgencyId();
        $mm = EmailTemplate::where('agency_id', $authAgencyId)->where('mail_code', 'II')->first();
        $doneInspection = $this->getInterimInspections($authAgencyId, true);

        // Sorting
        $doneInspection = $doneInspection
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingInterimInspectionVariables[request('sort_by')]) ? $this->sortingInterimInspectionVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $doneInspection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $doneInspection->count(), 'mail_template' => $mm];
    }

    public function intrimInspection()
    {
        $current = $this->getAllInterimInspection();
        $past = $this->getAllPastInterimInspection();
        $done = $this->getAllDoneInterimInspection();
        return [
            "saved" => true,
            "current_month" => $current,
            "past_month" => $past,
            "done" => $done,
        ];
    }
}
