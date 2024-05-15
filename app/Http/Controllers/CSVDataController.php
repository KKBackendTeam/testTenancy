<?php

namespace App\Http\Controllers;

use App\Models\Landloard;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\Applicant;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Agency;
use App\Models\InterimInspection;
use App\Traits\StatusTrait;
use App\Mail\ApplicantCsvMail;
use App\Mail\TenancyCsvMail;
use App\Mail\LandlordCsvMail;
use App\Mail\PropertyCsvMail;
use App\Mail\AgencyCsvMail;
use App\Mail\StaffCsvMail;
use Illuminate\Http\Request;
use App\Mail\InterimInspectionCsvMail;
use App\Traits\SortingActionTrait;
use App\Traits\FilterHelperTrait;
use Illuminate\Support\Facades\Mail;
use App\Traits\AllPermissions;

class CSVDataController extends Controller
{
    use AllPermissions, FilterHelperTrait, SortingActionTrait, StatusTrait;

    public $tenancyType = ['', 'new', 'Renewal', 'Part renewal'], $parkingStatus  = ['', 'Yes', 'No'], $hasGasStatus  = ['', 'Yes', 'No'];
    public $isActive  = ['Deactivate', 'Active'], $parkingArray = ['', 'Secure', 'Off-road', 'Street', 'Other'];
    public $isAuthorized  = ['Unauthorized', 'Authorized'];
    public $referenceForm = ['L' => 'Landlord reference', 'G' => 'Guarantor reference', 'E' => 'Employment reference', 'Q' => 'Quarterly reference', null => ''];

    /**
     * Retrieve CSV data for applicants and provide as a downloadable file.
     */
    public function getApplicantCsvData(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Reference', 'Creator', 'First name', 'Middle name', 'Last name', 'Email', 'DOB', 'Mobile', 'National insurance number', 'Document type',
            'Status', 'Reference form', 'Tenancy address', 'Created at'
        );

        $filename = $request->filled(['reference', 'name', 'email', 'mobile', 'status', 'tenancy_address', 'post_code', 'creator_name']) ? 'Applicant.csv' : 'Applicant.csv';
        $applicants = $this->applicantFilterRecords($request);

        if ($applicants->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($applicants, $filename, $variableArray);
        return response()->download($csvDataWithFile[0], 'Applicant.csv', $csvDataWithFile[1]);
    }

    /**
     * Send applicant CSV data via email.
     */
    public function applicantCsvDataEmail(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Reference', 'Creator', 'First name', 'Middle name', 'Last name', 'Email', 'DOB', 'Mobile', 'National insurance number', 'Document type',
            'Status', 'Reference form', 'Tenancy address', 'Created at'
        );
        $agencyData = Agency::where('id', authAgencyId())->firstOrFail();
        $filename = $request->filled(['reference', 'name', 'email', 'mobile', 'status', 'tenancy_address', 'post_code', 'creator_name']) ? 'Applicant.csv' : 'Applicant.csv';
        $applicants = $this->applicantFilterRecords($request);

        if ($applicants->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($applicants, $filename, $variableArray);
        foreach ($request->emails as $email) {
            Mail::to($email)->send(new ApplicantCsvMail($agencyData, $csvDataWithFile[0]));
        }
        return response()->json(['saved' => true]);
    }
    /**
     * Filter applicants based on the provided request parameters.
     *
     * @param Request $request The request containing filter parameters.
     * @return \Illuminate\Database\Eloquent\Collection The filtered agencies.
     */
    public function applicantFilterRecords(Request $request)
    {
        $tenancyAddress = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['tenancy_address'], $query, 'pro_address', null, null);
        };

        $tenacnyReference = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['reference'], $query, 'reference', null, null);
        };

        $userName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['creator_name'], $query, 'name', 'l_name', null);
        };

        $appName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['name'], $query, 'app_name', 'l_name', null);
        };

        $applicantEmail = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['email'], $query, 'email', null, null);
        };

        $appMobile = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['mobile'], $query, 'app_mobile', null, null);
        };

        $applicants = Applicant::where('agency_id', authAgencyId())->with(['users', 'applicantbasic']);
        $applicants = $applicants
            ->where(function ($query) use ($request) {
                if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->applicantStatus);
            })
            ->whereHas('applicantbasic', $appName)->with(['applicantbasic' => $appName])
            ->whereHas('applicantbasic', $applicantEmail)->with(['applicantbasic' => $applicantEmail])
            ->whereHas('applicantbasic', $appMobile)->with(['applicantbasic' => $appMobile])
            ->whereHas('tenancies', $tenancyAddress)->with(['tenancies' => $tenancyAddress])
            ->whereHas('tenancies', $tenacnyReference)->with(['tenancies' => $tenacnyReference])
            ->whereHas('users', $userName)->with(['users' => $userName])->get();

        return $applicants;
    }

    /**
     * Send current month CSV data via email.
     */
    public function getInterimInspectionCsvData($query, $request, $comparisonOperator = null, $comparisonValue = null)
    {
        $variableArray = array(
            'Sr. no', 'Reference', 'Address', 'Inspection Month', 'Scheduled Inspection Date', 'Email Date', 'Comment', 'Created at'
        );
        $filename = $request->filled(['reference', 'address', 'inspection_month', 'inspection_date', 'email_date']) ? 'InterimInspection.csv' : 'InterimInspection.csv';

        if ($comparisonOperator && $comparisonValue) {
            $query->whereRaw("TO_DATE(inspection_month, 'Month YYYY') $comparisonOperator TO_DATE(?, 'Month YYYY')", [$comparisonValue]);
        }

        $inspections = $this->applyFilters($query, $request); // Pass request to applyFilters

        if ($inspections->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }

        $csvDataWithFile = $this->csvCreatorHelperFunction($inspections, $filename, $variableArray);

        if (!empty($request->emails)) {
            $agencyData = Agency::where('id', authAgencyId())->firstOrFail();
            foreach ($request->emails as $email) {
                Mail::to($email)->send(new InterimInspectionCsvMail($agencyData, $csvDataWithFile[0]));
            }
            return response()->json(['saved' => true]);
        }

        return response()->download($csvDataWithFile[0], $filename, $csvDataWithFile[1]);
    }

    /**
     * Apply additional filters based on the request parameters.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function applyFilters($query, $request)
    {
        return $query->where(function ($query) use ($request) {
            if (!empty($request['reference'])) {
                $this->filterWithStringAttributes($request['reference'], $query, 'reference', null, null);
            }
            if (!empty($request['address'])) {
                $this->filterWithStringAttributes($request['address'], $query, 'address', null, null);
            }
            if (!empty($request['inspection_month'])) {
                $this->filterWithStringAttributes($request['inspection_month'], $query, 'inspection_month', null, null);
            }
            if (!empty($request['inspection_date'])) {
                $this->filterWithStringAttributes($request['inspection_date'], $query, 'inspection_date', null, null);
            }
            if (!empty($request['email_date'])) {
                $this->filterWithStringAttributes($request['email_date'], $query, 'email_date', null, null);
            }
        })->get();
    }

    /**
     * Retrieve all interim inspections for the current month.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function currentInspectionsCsvData(Request $request)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', false);
        return $this->getInterimInspectionCsvData($query, $request, '=', now()->format('F Y'));
    }

    /**
     * Retrieve all past interim inspections.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function pastInspectionsCsvData(Request $request)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', false);
        return $this->getInterimInspectionCsvData($query, $request, '<', now()->subMonth()->format('F Y'));
    }

    /**
     * Retrieve all completed interim inspections.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function completedInspectionsCsvData(Request $request)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', true);
        return $this->getInterimInspectionCsvData($query, $request);
    }

    /**
     * Retrieve all interim inspections for the current month.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function currentInspectionsCsvDataEmail(Request $request)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', false);
        return $this->getInterimInspectionCsvData($query, $request, '=', now()->format('F Y'));
    }

    /**
     * Retrieve all past interim inspections.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function pastInspectionsCsvDataEmail(Request $request)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', false);
        return $this->getInterimInspectionCsvData($query, $request, '<', now()->subMonth()->format('F Y'));
    }

    /**
     * Retrieve all completed interim inspections.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function completedInspectionsCsvDataEmail(Request $request)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', true);
        return $this->getInterimInspectionCsvData($query, $request);
    }
    /**
     * Retrieve CSV data for tenancies and provide as a downloadable file.
     */
    public function getTenancyCsvData(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Reference', 'Creator', 'Landlord name', 'Property address', 'Parking status', 'Parking cost',
            'Restriction', 'Rent include', 'Monthly rent', 'Total rent', 'Deposit amount', 'Holding fee amount',
            'Start date', 'End date', 'No of applicant', 'Deadline', 'Status', 'Type', 'Created at'
        );

        $filename = $request->filled(['landlord_name', 'reference', 'status', 'monthly_amount', 'deposite_amount', 'total_rent', 'holding_amount', 'no_applicant', 'type', 'no_beds', 'post_code', 'tenancy_address', 'start_date', 'end_date', 'created_by', 'updated_at']) ? 'Tenancy.csv' : 'Tenancy.csv';
        $tenancies = $this->tenancyFilterRecords($request);
        if ($tenancies->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($tenancies, $filename, $variableArray);
        return response()->download($csvDataWithFile[0], 'Tenancy.csv', $csvDataWithFile[1]);
    }
    /**
     * Send tenancy CSV data via email.
     */
    public function tenancyCsvDataEmail(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Reference', 'Creator', 'Landlord name', 'Property address', 'Parking status', 'Parking cost',
            'Restriction', 'Rent include', 'Monthly rent', 'Total rent', 'Deposit amount', 'Holding fee amount',
            'Start date', 'End date', 'No of applicant', 'Deadline', 'Status', 'Type', 'Created at'
        );
        $agencyData = Agency::where('id', authAgencyId())->firstOrFail();
        $filename = $request->filled(['landlord_name', 'reference', 'status', 'monthly_amount', 'deposite_amount', 'total_rent', 'holding_amount', 'no_applicant', 'type', 'no_beds', 'post_code', 'tenancy_address', 'start_date', 'end_date', 'created_by', 'updated_at']) ? 'Tenancy.csv' : 'Tenancy.csv';
        $tenancies = $this->tenancyFilterRecords($request);
        if ($tenancies->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($tenancies, $filename, $variableArray);
        foreach ($request->emails as $email) {
            Mail::to($email)->send(new TenancyCsvMail($agencyData, $csvDataWithFile[0]));
        }
        return response()->json(['saved' => true]);
    }

    /**
     * Filter records for tenancies based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tenancyFilterRecords(Request $request)
    {
        $landlordName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['landlord_name'], $query, 'f_name', 'l_name', null);
        };

        $creatorName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['created_by'], $query, 'name', 'l_name', null);
        };

        $bed_number = function ($query) use ($request) {
            if (!empty($request['no_beds']))  $this->filterWithNumberAttributes($request['no_beds'], $query, 'bedroom', null, null);
        };

        $tenancies = Tenancy::where('agency_id', authAgencyId())->with(['users', 'properties', 'landlords']);

        $tenancies = $tenancies->where(function ($query) use ($request) {
            if (!empty($request['reference']))  $this->filterWithStringAttributes($request['reference'], $query, 'reference', null, null);
            if (!empty($request['tenancy_address']))  $this->filterWithStringAttributes($request['tenancy_address'], $query, 'pro_address', null, null);
            if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->tenancyStatus);
            if (!empty($request['monthly_amount'])) $this->filterWithNumberAttributes($request['monthly_amount'], $query, 'monthly_amount', null, null);
            if (!empty($request['total_rent'])) $this->filterWithNumberAttributes($request['total_rent'], $query, 'total_rent', null, null);
            if (!empty($request['deposite_amount'])) $this->filterWithNumberAttributes($request['deposite_amount'], $query, 'deposite_amount', null, null);
            if (!empty($request['holding_amount'])) $this->filterWithNumberAttributes($request['holding_amount'], $query, 'holding_amount', null, null);
            if (!empty($request['no_applicant'])) $this->filterWithNumberAttributes($request['no_applicant'], $query, 'no_applicant', null, null);
            if (!empty($request['type'])) $this->filterWithStatusAttributes($request['type'], $query, 'type', $this->tenancyTypeStatus);
            if (!empty($request['start_date'])) $this->filterWithDateAttributes($request['start_date'], $query, 't_start_date', null, null);
            if (!empty($request['end_date'])) $this->filterWithDateAttributes($request['end_date'], $query, 't_end_date', null, null);
            if (!empty($request['create_date'])) $this->filterWithDateAttributes($request['create_date'], $query, 'created_at', null, null);
            if (!empty($request['updated_at'])) $this->filterWithDateAttributes($request['updated_at'], $query, 'updated_at', null, null);
        })
            ->whereHas('landlords', $landlordName)->with(['landlords' => $landlordName])
            ->whereHas('properties', $bed_number)->with(['properties' => $bed_number])
            ->whereHas('users', $creatorName)->with(['users' => $creatorName])
            ->with('latest_update:tenancy_id,event_type')->get();
        return $tenancies;
    }

    /**
     * Retrieve CSV data for staff and provide as a downloadable file.
     */
    public function getStaffCsvData(Request $request)
    {
        $variableArray = ['Sr. no', 'First name', 'Last name', 'Email', 'Active/De-active'];
        $filename = $request->filled(['name', 'email', 'mobile', 'last_action', 'status', 'last_action_date']) ? 'Staff.csv' : 'Staff.csv';
        $staffMembers = $this->getFilteredStaffMembers($request);

        if ($staffMembers->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }

        $csvDataWithFile = $this->csvCreatorHelperFunction($staffMembers, $filename, $variableArray);
        return response()->download($csvDataWithFile[0], $filename, $csvDataWithFile[1]);
    }

    /**
     * Send staff CSV data via email.
     */
    public function staffCsvDataEmail(Request $request)
    {
        $variableArray = ['Sr. no', 'First name', 'Last name', 'Email', 'Active/De-active'];
        $agencyData = Agency::where('id', authAgencyId())->firstOrFail();
        $filename = $request->filled(['name', 'email', 'mobile', 'last_action', 'status', 'last_action_date']) ? 'Staff.csv' : 'Staff.csv';
        $staffMembers = $this->getFilteredStaffMembers($request);
        $csvDataWithFile = $this->csvCreatorHelperFunction($staffMembers, $filename, $variableArray);
        foreach ($request->emails as $email) {
            Mail::to($email)->send(new StaffCsvMail($agencyData, $csvDataWithFile[0]));
        }

        return response()->json(['saved' => true]);
    }

    /**
     * Retrieve filtered staff members based on request parameters.
     *
     * @param Request $request The HTTP request object containing filter parameters.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getFilteredStaffMembers(Request $request)
    {
        return User::where('agency_id', authAgencyId())
            ->where('roleStatus', '!=', 1)
            ->when($request->filled('name'), function ($query) use ($request) {
                $this->filterWithStringAttributes($request->name, $query, 'name', 'l_name', null);
            })
            ->when($request->filled('email'), function ($query) use ($request) {
                $this->filterWithStringAttributes($request->email, $query, 'email', null, null);
            })
            ->when($request->filled('mobile'), function ($query) use ($request) {
                $this->filterWithStringAttributes($request->mobile, $query, 'mobile', null, null);
            })
            ->when($request->filled('last_action'), function ($query) use ($request) {
                $this->filterWithStringAttributes($request->last_action, $query, 'last_action', null, null);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $this->filterWithStatusAttributes($request->status, $query, 'is_active', $this->activeDeactiveArray);
            })
            ->when($request->filled('last_action_date'), function ($query) use ($request) {
                $this->filterWithDateAttributes($request->last_action_date, $query, 'last_action_date', null, null);
            })
            ->get();
    }

    /**
     * Retrieve CSV data for landlord and provide as a downloadable file.
     */
    public function getLandlordCsvData(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Creator', 'First name', 'Last name', 'Company/Display name', 'Email', 'Mobile', 'Postcode', 'Street', 'Town', 'Country', 'Number of properties',
            'Number of available', 'Number of processing', 'Number of let', 'Created at'
        );
        $filename = $request->filled(['landlord_name', 'company_name', 'no_of_prop', 'no_of_available', 'no_of_processing', 'no_of_let']) ? 'Landlord.csv' : 'Landlord.csv';
        $landlords = $this->getFilteredLandlord($request);

        if ($landlords->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }

        $csvDataWithFile = $this->csvCreatorHelperFunction($landlords, 'Landlord.csv', $variableArray);
        return response()->download($csvDataWithFile[0], 'Landlord.csv', $csvDataWithFile[1]);
    }

    /**
     * Retrieve filtered landlord records based on the provided request parameters.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing filter parameters.
     *
     * @return \Illuminate\Database\Eloquent\Collection The filtered landlord records.
     */
    protected function getFilteredLandlord(Request $request)
    {
        $all_landlords = Landloard::where('agency_id', authAgencyId())->with('properties')->with('users');

        $all_landlords = $all_landlords->where(function ($query) use ($request) {

            if (!empty($request['landlord_name'])) $this->filterWithStringAttributes($request['landlord_name'], $query, 'f_name', 'l_name', null);
            if (!empty($request['company_name'])) $this->filterWithStringAttributes($request['company_name'], $query, 'display_name', null, null);

            if (!empty($request['no_of_prop'])) {

                if (($request['no_of_prop'] === '[empty]') || ($request['no_of_prop'] === '[nonempty]')) {
                } else if (strpos($request['no_of_prop'], '||') !== false || strpos($request['no_of_prop'], '&&') !== false) {

                    $operatorValue = $this->workWithMultipleNumberAttributes($request['no_of_prop']);
                    $query->has('properties', $operatorValue[1]['first_0'], $operatorValue[1]['second_0'])
                        ->{$operatorValue[0] == '||' ? 'orHas' : 'has'}('properties', $operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
                } elseif (substr($request['no_of_prop'], 0, 4) === 'rgx:') {
                } else {

                    $operatorValue = $this->workWithSingleNumberAttribute($request['no_of_prop'], 0);
                    $query->has('properties', $operatorValue['first_0'], $operatorValue['second_0']);
                }
            }
            if (!empty($request['no_of_available'])) {

                if (($request['no_of_available'] === '[empty]') || ($request['no_of_available'] === '[nonempty]')) {
                } else if (strpos($request['no_of_available'], '||') !== false || strpos($request['no_of_available'], '&&') !== false) {

                    $operatorValue = $this->workWithMultipleNumberAttributes($request['no_of_available']);
                    $query
                        ->whereHas('properties', function ($child) {
                            $child->whereIn('status', [1, 3]);
                        }, $operatorValue[1]['first_0'], $operatorValue[1]['second_0'])
                        ->{$operatorValue[0] == '||' ? 'orWhereHas' : 'whereHas'}('properties', function ($child) {
                            $child->whereIn('status', [1, 3]);
                        }, $operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
                } elseif (substr($request['no_of_available'], 0, 4) === 'rgx:') {
                } else {

                    $operatorValue = $this->workWithSingleNumberAttribute($request['no_of_available'], 0);
                    $query->whereHas('properties', function ($child) {
                        $child->whereIn('status', [1, 3]);
                    }, $operatorValue['first_0'], $operatorValue['second_0']);
                }
            }
            if (!empty($request['no_of_processing'])) {

                if (($request['no_of_processing'] === '[empty]') || ($request['no_of_processing'] === '[nonempty]')) {
                } else if (strpos($request['no_of_processing'], '||') !== false || strpos($request['no_of_processing'], '&&') !== false) {
                    $operatorValue = $this->workWithMultipleNumberAttributes($request['no_of_processing']);

                    $query
                        ->whereHas('properties', function ($child) {
                            $child->where('status', 4);
                        }, $operatorValue[1]['first_0'], $operatorValue[1]['second_0'])
                        ->{$operatorValue[0] == '||' ? 'orWhereHas' : 'whereHas'}('properties', function ($child) {
                            $child->where('status', 4);
                        }, $operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
                } elseif (substr($request['no_of_processing'], 0, 4) === 'rgx:') {
                } else {

                    $operatorValue = $this->workWithSingleNumberAttribute($request['no_of_processing'], 0);
                    $query->whereHas('properties', function ($child) {
                        $child->where('status', 4);
                    }, $operatorValue['first_0'], $operatorValue['second_0']);
                }
            }
            if (!empty($request['no_of_let'])) {

                if (($request['no_of_let'] === '[empty]') || ($request['no_of_let'] === '[nonempty]')) {
                } else if (strpos($request['no_of_let'], '||') !== false || strpos($request['no_of_let'], '&&') !== false) {
                    $operatorValue = $this->workWithMultipleNumberAttributes($request['no_of_let']);

                    $query
                        ->whereHas('properties', function ($child) {
                            $child->where('status', 5);
                        }, $operatorValue[1]['first_0'], $operatorValue[1]['second_0'])
                        ->{$operatorValue[0] == '||' ? 'orWhereHas' : 'whereHas'}('properties', function ($child) {
                            $child->where('status', 5);
                        }, $operatorValue[1]['first_1'], $operatorValue[1]['second_1']);
                } elseif (substr($request['no_of_let'], 0, 4) === 'rgx:') {
                } else {

                    $operatorValue = $this->workWithSingleNumberAttribute($request['no_of_let'], 0);
                    $query->whereHas('properties', function ($child) {
                        $child->where('status', 5);
                    }, $operatorValue['first_0'], $operatorValue['second_0']);
                }
            }
        })->get();

        return $all_landlords;
    }

    /**
     * Send landlord CSV data via email.
     */
    public function landlordCsvDataEmail(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Creator', 'First name', 'Last name', 'Company/Display name', 'Email', 'Mobile', 'Postcode', 'Street', 'Town', 'Country', 'Number of properties',
            'Number of available', 'Number of processing', 'Number of let', 'Created at'
        );
        $agencyData = Agency::where('id', authAgencyId())->firstOrFail();
        $filename = $request->filled(['landlord_name', 'company_name', 'no_of_prop', 'no_of_available', 'no_of_processing', 'no_of_let']) ? 'Landlord.csv' : 'Landlord.csv';
        $landlords = $this->getFilteredLandlord($request);
        $csvDataWithFile = $this->csvCreatorHelperFunction($landlords, 'Landlord.csv', $variableArray);

        foreach ($request->emails as $email) {
            Mail::to($email)->send(new LandlordCsvMail($agencyData, $csvDataWithFile[0]));
        }

        return response()->json(['saved' => true]);
    }

    /**
     * Retrieve CSV data for property and provide as a downloadable file.
     */
    public function getPropertyCsvData(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Reference', 'Creator', 'Landlord name', 'Postcode', 'Street', 'Town', 'Country', 'Status', 'Parking', 'Parking cost', 'Parking status',
            'Bedroom', 'Restriction', 'Rent include', 'Has gas', 'Gas expiry date', 'Epc expiry date', 'Electric expiry date', 'Monthly rent',
            'Total rent', 'Deposit amount', 'Holding fee amount', 'Available from', 'Created at'
        );
        $filename = $request->filled(['reference', 'landlord_name', 'address', 'post_code', 'total_rent', 'status', 'monthly_amount', 'holding_amount', 'bedroom', 'available_from']) ? 'Filtered_Property.csv' : 'Property.csv';
        $filteredProperties = $this->propertyFilterRecords($request);
        if ($filteredProperties->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($filteredProperties, $filename, $variableArray);
        return response()->download($csvDataWithFile[0], $filename, $csvDataWithFile[1]);
    }

    /**
     * Send property CSV data via email.
     */
    public function propertyCsvDataEmail(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Reference', 'Creator', 'Landlord name', 'Postcode', 'Street', 'Town', 'Country', 'Status', 'Parking', 'Parking cost', 'Parking status',
            'Bedroom', 'Restriction', 'Rent include', 'Has gas', 'Gas expiry date', 'Epc expiry date', 'Electric expiry date', 'Monthly rent',
            'Total rent', 'Deposit amount', 'Holding fee amount', 'Available from', 'Created at'
        );
        $agencyData = Agency::where('id', authAgencyId())->firstOrFail();
        $filename = $request->filled(['reference', 'landlord_name', 'address', 'post_code', 'total_rent', 'status', 'monthly_amount', 'holding_amount', 'bedroom', 'available_from']) ? 'Property.csv' : 'Property.csv';
        $filteredProperties = $this->propertyFilterRecords($request);
        if ($filteredProperties->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($filteredProperties, $filename, $variableArray);
        foreach ($request->emails as $email) {
            Mail::to($email)->send(new PropertyCsvMail($agencyData, $csvDataWithFile[0]));
        }
        return response()->json(['saved' => true]);
    }

    /**
     * Filter properties based on the provided request parameters.
     *
     * @param \Illuminate\Http\Request $request The request object containing filter parameters.
     * @return \Illuminate\Database\Eloquent\Collection Filtered properties collection.
     */
    public function propertyFilterRecords(Request $request)
    {
        $landlordName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['landlord_name'], $query, 'f_name', 'l_name', null);
        };
        $properties = Property::where('agency_id', authAgencyId())->with(['users', 'landlords']);

        $properties = $properties
            ->where(function ($query) use ($request) {
                if (!empty($request['address'])) $this->filterWithStringAttributes($request['address'], $query, 'street', 'town', 'country');
                if (!empty($request['reference'])) $this->filterWithStringAttributes($request['reference'], $query, 'property_ref', null, null);
                if (!empty($request['post_code'])) $this->filterWithStringAttributes($request['post_code'], $query, 'post_code', null, null);
                if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->propertyStatus);
                if (!empty($request['monthly_amount'])) $this->filterWithNumberAttributes($request['monthly_amount'], $query, 'monthly_rent', null, null);
                if (!empty($request['total_rent'])) $this->filterWithNumberAttributes($request['total_rent'], $query, 'total_rent', null, null);
                if (!empty($request['deposite_amount'])) $this->filterWithNumberAttributes($request['deposite_amount'], $query, 'deposite_amount', null, null);
                if (!empty($request['holding_amount'])) $this->filterWithNumberAttributes($request['holding_amount'], $query, 'holding_fee_amount', null, null);
                if (!empty($request['bedroom'])) $this->filterWithNumberAttributes($request['bedroom'], $query, 'bedroom', null, null);
                if (!empty($request['available_from'])) $this->filterWithDateAttributes($request['available_from'], $query, 'available_from', null, null);
            })
            ->whereHas('landlords', $landlordName)->with(['landlords' => $landlordName])
            ->with('landlords:id,f_name,l_name')
            ->get();

        $properties->map(function ($data) {
            $data['restrictionArray'] = explode(',', $data['restriction']);
            $data['rentIncludeArray'] = explode(',', $data['rent_include']);
            unset($data['restriction'], $data['rent_include']);
            return $data;
        });

        return  $properties;
    }

    /**
     * Retrieve CSV data for agency and provide as a downloadable file.
     */
    public function getAgencyCsvData(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Agency name', 'Email', 'Phone', 'Status', 'Opening time', 'Closing time', 'Address', 'Total credit', 'Used credit',
            'Last login', 'Facebook', 'Twitter', 'Google plus'
        );
        $filename = $request->filled(['name', 'email', 'phone', 'status', 'last_login', 'total_credit', 'used_credit']) ? 'Agency.csv' : 'Agency.csv';
        $filteredAgency = $this->agencyFilterRecords($request);
        if ($filteredAgency->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($filteredAgency, $filename, $variableArray);
        return response()->download($csvDataWithFile[0], 'Agency.csv', $csvDataWithFile[1]);
    }

    /**
     * Send agency CSV data via email.
     */
    public function agencyCsvDataEmail(Request $request)
    {
        $variableArray = array(
            'Sr. no', 'Agency name', 'Email', 'Phone', 'Status', 'Opening time', 'Closing time', 'Address', 'Total credit', 'Used credit',
            'Last login', 'Facebook', 'Twitter', 'Google plus'
        );
        $agencyData = Agency::where('id', authAgencyId())->firstOrFail();
        $filename = $request->filled(['name', 'email', 'phone', 'status', 'last_login', 'total_credit', 'used_credit']) ? 'Agency.csv' : 'Agency.csv';
        $filteredAgency = $this->agencyFilterRecords($request);
        if ($filteredAgency->isEmpty()) {
            return response()->json(['message' => 'No records found matching the specified filters.']);
        }
        $csvDataWithFile = $this->csvCreatorHelperFunction($filteredAgency, $filename, $variableArray);
        foreach ($request->emails as $email) {
            Mail::to($email)->send(new AgencyCsvMail($agencyData, $csvDataWithFile[0]));
        }
        return response()->json(['saved' => true]);
    }

    /**
     * Filter agencies based on the provided request parameters.
     *
     * @param Request $request The request containing filter parameters.
     * @return \Illuminate\Database\Eloquent\Collection The filtered agencies.
     */
    public function agencyFilterRecords(Request $request)
    {
        $agencies = Agency::where('status', '!=', 2)
            ->where(function ($query) use ($request) {
                if (!empty($request['name'])) $this->filterWithStringAttributes($request['name'], $query, 'name', null, null);
                if (!empty($request['email'])) $this->filterWithStringAttributes($request['email'], $query, 'email', null, null);
                if (!empty($request['phone'])) $this->filterWithStringAttributes($request['phone'], $query, 'phone', null, null);
                if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->authorizeUnauthorizeArray);
                if (!empty($request['last_login'])) $this->filterWithDateAttributes($request['last_login'], $query, 'last_login', null, null);
                if (!empty($request['total_credit'])) $this->filterWithNumberAttributes($request['total_credit'], $query, 'total_credit', null, null);
                if (!empty($request['used_credit'])) $this->filterWithNumberAttributes($request['used_credit'], $query, 'used_credit', null, null);
            })
            ->get();
        return $agencies;
    }

    /**
     * Helper function to create CSV file and return file name with headers.
     */
    public function csvCreatorHelperFunction($data, $fileName, $variableArray)
    {
        $handle = fopen($fileName, 'w+');
        fputcsv($handle, $variableArray);
        $i = 0;
        foreach ($data as $row) {
            if ($fileName == 'Staff.csv') fputcsv($handle, array(++$i, $row['name'], $row['l_name'], $row['email'], $this->isActive[$row['is_active']]));

            elseif ($fileName == 'Landlord.csv') fputcsv($handle, array(
                ++$i,  $row['users']['name'] . ' ' . $row['users']['l_name'], $row['f_name'], $row['l_name'], $row['display_name'], $row['email'], $row['mobile'], $row['post_code'],
                $row['street'], $row['town'], $row['country'], $row->properties()->count(), $row->properties()->whereIn('status', [1, 3])->count(),
                $row->properties()->where('status', 4)->count(), $row->properties()->where('status', 5)->count(), Carbon::parse($row['created_at'])->format('Y-m-d'),
            ));
            elseif ($fileName == 'InterimInspection.csv') fputcsv($handle, array(
                ++$i,  $row['reference'], $row['address'], $row['inspection_month'], $row['inspection_date'], $row['email_date'], $row['comment'], Carbon::parse($row['created_at'])->format('Y-m-d')
            ));
            elseif ($fileName == 'Tenancy.csv')
                fputcsv($handle, array(
                    ++$i, $row['reference'],  $row['users']['name'] . ' ' . $row['users']['l_name'], $row['landlords']['f_name'] . ' ' . $row['landlords']['l_name'], $row['pro_address'], $this->parkingStatus[$row['parking']], $row['parking_cost'],
                    $row['restriction'], $row['rent_include'], $row['monthly_amount'], $row['total_rent'], $row['deposite_amount'],  $row['holding_amount'],
                    $row['t_start_date'], $row['t_end_date'], $row['no_applicant'], $row['deadline'], isset($this->tenancyStatus[$row['status']]) ? $this->tenancyStatus[$row['status']] : '', isset($this->tenancyType[$row['type']]) ? $this->tenancyType[$row['type']] : '',
                    Carbon::parse($row['created_at'])->format('Y-m-d')
                ));

            elseif ($fileName == 'Applicant.csv')  fputcsv($handle, array(
                ++$i, $row->tenancies->reference, $row['users']['name'] . ' ' . $row['users']['l_name'], $row['applicantbasic']['app_name'], $row['applicantbasic']['m_name'], $row['applicantbasic']['l_name'], $row['applicantbasic']['email'], $row['applicantbasic']['dob'], $row['applicantbasic']['app_mobile'], $row['applicantbasic']['app_ni_number'], $row['doc_type'],
                $this->applicantStatus[$row['status']], $this->referenceForm[$row['ref_code']], $row->tenancies->pro_address, Carbon::parse($row['created_at'])->format('Y-m-d')
            ));
            elseif ($fileName == 'Property.csv') fputcsv($handle, array(
                ++$i, $row['property_ref'], $row['users']['name'] . ' ' . $row['users']['l_name'], $row['landlords']['f_name'] . ' ' . $row['landlords']['l_name'], $row['post_code'], $row['street'], $row['town'],
                $row['country'], $this->propertyStatus[$row['status']], $this->parkingStatus[$row['parkingToggle']], $row['parking_cost'], $this->parkingArray[$row['parkingArray']], $row['bedroom'],
                $row['restriction'], $row['rent_include'], $this->hasGasStatus[$row['hasGas']], $row['gas_expiry_date'], $row['epc_expiry_date'], $row['electric_expiry_date'],
                $row['monthly_rent'], $row['total_rent'], $row['deposite_amount'], $row['holding_fee_amount'], $row['available_from'], Carbon::parse($row['created_at'])->format('Y-m-d')
            ));
            else {
                fputcsv($handle, array(
                    ++$i, $row['name'], $row['email'], $row['phone'], $this->isAuthorized[$row['status']], $row['opening_time'], $row['closing_time'],
                    $row['address'], $row['total_credit'], $row['used_credit'], $row['last_login'], $row['facebook'], $row['twitter'], $row['google_plus']
                ));
            }
        }
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return [$fileName, $headers];
    }
}
