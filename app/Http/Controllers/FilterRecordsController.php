<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agency;
use App\Models\Tenancy;
use App\Models\Property;
use App\Models\Applicant;
use App\Models\Landloard;
use App\Models\InterimInspection;
use Illuminate\Http\Request;
use App\Traits\AllPermissions;
use App\Traits\FilterHelperTrait;
use App\TenancyEvents;
use App\Traits\SortingActionTrait;
use Illuminate\Support\Collection;
use App\Traits\StatusTrait;

class FilterRecordsController extends Controller
{
    use AllPermissions, FilterHelperTrait, SortingActionTrait, StatusTrait;

    /**
     * Filter records for staff members based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function staffFilterRecords(Request $request)
    {
        $staffMember = User::where('agency_id', authAgencyId())
            ->where('roleStatus', '!=', 1)->where(function ($query) use ($request) {
                if (!empty($request['name'])) $this->filterWithStringAttributes($request['name'], $query, 'name', 'l_name', null);
                if (!empty($request['email'])) $this->filterWithStringAttributes($request['email'], $query, 'email', null, null);
                if (!empty($request['mobile'])) $this->filterWithStringAttributes($request['mobile'], $query, 'mobile', null, null);
                if (!empty($request['last_action']))  $this->filterWithStringAttributes($request['last_action'], $query, 'last_action', null, null);
                if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'is_active', $this->activeDeactiveArray);
                if (!empty($request['last_action_date'])) $this->filterWithDateAttributes($request['last_action_date'], $query, 'last_action_date', null, null);
            })
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingStaffVariables[request('sort_by')]) ? $this->sortingStaffVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $data = $staffMember->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        return response()->json(['saved' => true, 'staff_member' => ['data' => $data, 'count' => $staffMember->count(), 'total' => $staffMember->count()]]);
    }

    /**
     * Filter records for agencies based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
                if ($request['total_credit'] === '0') {
                    $query->where('total_credit', 0);
                }
                if (!empty($request['total_credit'])) $this->filterWithNumberAttributes($request['total_credit'], $query, 'total_credit', null, null);
                if ($request['used_credit'] === '0') {
                    $query->where('used_credit', 0);
                }
                if (!empty($request['used_credit'])) $this->filterWithNumberAttributes($request['used_credit'], $query, 'used_credit', null, null);
            })
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingAgencyVariables[request('sort_by')]) ? $this->sortingAgencyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $data = $agencies->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'count' => $agencies->count(), 'agencies' => ['data' => $data, 'total' => $agencies->count()]]);
    }

    /**
     * Filter records for properties based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function propertyFilterRecords(Request $request)
    {
        $landlordName = function ($query) use ($request) {
            $this->filterWithStringAttributes($request['landlord_name'], $query, 'f_name', 'l_name', null);
        };
        $properties = Property::where('agency_id', authAgencyId());

        $properties = $properties
            ->where(function ($query) use ($request) {
                if (!empty($request['address'])) $this->filterWithStringAttributes($request['address'], $query, 'street', 'town', 'country');
                if (!empty($request['reference'])) $this->filterWithStringAttributes($request['reference'], $query, 'property_ref', null, null);
                if (!empty($request['post_code'])) $this->filterWithStringAttributes($request['post_code'], $query, 'post_code', null, null);
                if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->propertyStatus);
                if (!empty($request['monthly_amount'])) $this->filterWithNumberAttributes($request['monthly_amount'], $query, 'monthly_rent', null, null);
                if (!empty($request['total_rent'])) $this->filterWithNumberAttributes($request['total_rent'], $query, 'total_rent', null, null);
                if ($request['deposite_amount'] === '0') {
                    $query->where('deposite_amount', 0);
                }
                if (!empty($request['deposite_amount'])) $this->filterWithNumberAttributes($request['deposite_amount'], $query, 'deposite_amount', null, null);
                if (!empty($request['holding_amount'])) $this->filterWithNumberAttributes($request['holding_amount'], $query, 'holding_fee_amount', null, null);
                if (!empty($request['bedroom'])) $this->filterWithNumberAttributes($request['bedroom'], $query, 'bedroom', null, null);
                if ($request['parking_cost'] === '0') {
                    $query->where('parking_cost', 0);
                }
                if (!empty($request['parking_cost'])) $this->filterWithNumberAttributes($request['parking_cost'], $query, 'parking_cost', null, null);
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

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $newcollection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->propertyDescStatusArray : $this->propertyAscStatusArray;
            while ($i < $this->propertyStatusArrayCount) {
                foreach ($properties as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $newcollection->push($ti);
                        unset($properties[$key]);
                    }
                }
                $i++;
            }
            $data = $newcollection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $properties = $newcollection;
        } else {
            $properties = $properties->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingPropertyVariables[request('sort_by')]) ? $this->sortingPropertyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $properties->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return response()->json(['saved' => true, 'properties' => ['data' => $data, 'total' => $properties->count()]]);
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
    private function getInterimInspections($query, $comparisonOperator = null, $comparisonValue = null)
    {
        if ($comparisonOperator && $comparisonValue) {
            $query->whereRaw("TO_DATE(inspection_month, 'Month YYYY') $comparisonOperator TO_DATE(?, 'Month YYYY')", [$comparisonValue]);
        }

        $inspections = $this->applyFilters($query);
        $inspectionData = $inspections->map(function ($inspection) {
            $tenancy = Tenancy::where('id', $inspection->tenancy_id)->with(['properties', 'landlords:id,f_name,l_name,email'])->firstOrFail();
            $inspectionArray = $inspection->toArray();
            $inspectionArray['landlord_email'] = $tenancy->landlords->email;
            return $inspectionArray;
        });
        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $inspectionData->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'inspection' => ['data' => $data, 'total' => $inspectionData->count()]]);
    }

    /**
     * Apply additional filters based on the request parameters.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function applyFilters($query)
    {
        $request = request();
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
                $this->filterWithDateAttributes($request['inspection_date'], $query, 'inspection_date', null, null);
            }
            if (!empty($request['email_date'])) {
                $this->filterWithDateAttributes($request['email_date'], $query, 'email_date', null, null);
            }
        })->get();
    }

    /**
     * Retrieve all interim inspections for the current month.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterCurrentMonthInterimInspections()
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', false);
        return $this->getInterimInspections($query, '=', now()->format('F Y'));
    }

    /**
     * Retrieve all past interim inspections.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterPastInterimInspections()
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', false);
        return $this->getInterimInspections($query, '<', now()->subMonth()->format('F Y'));
    }

    /**
     * Retrieve all completed interim inspections.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterCompletedInterimInspections()
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('is_done', true);
        return $this->getInterimInspections($query);
    }

    /**
     * Retrieve all interim inspections for the current month of Tenancy.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterTenancyCurrentMonthInterimInspections($id)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('tenancy_id', $id)
            ->where('is_done', false);
        return $this->getInterimInspections($query, '=', now()->format('F Y'));
    }

    /**
     * Retrieve all past interim inspections of Tenancy.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterTenancyPastInterimInspections($id)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('tenancy_id', $id)
            ->where('is_done', false);
        return $this->getInterimInspections($query, '<', now()->subMonth()->format('F Y'));
    }

    /**
     * Retrieve all completed interim inspections of Tenancy.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterTenancyCompletedInterimInspections($id)
    {
        $query = InterimInspection::where('agency_id', authAgencyId())
            ->where('tenancy_id', $id)
            ->where('is_done', true);
        return $this->getInterimInspections($query);
    }

    /**
     * Filter records for applicants based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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

        $applicants = Applicant::where('agency_id', authAgencyId());
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

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $newcollection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i <  $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $newcollection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $newcollection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $newcollection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }
        return response()->json(['saved' => true, 'count' => $applicants->count(), 'applicants' => ['data' => $data, 'total' => $applicants->count()]]);
    }

    /**
     * Filter records for landlords based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function landlordFilterRecords(Request $request)
    {
        $all_landlords = Landloard::where('agency_id', authAgencyId())->with('properties');

        $all_landlords = $all_landlords->where(function ($query) use ($request) {

            if (!empty($request['landlord_name'])) $this->filterWithStringAttributes($request['landlord_name'], $query, 'f_name', 'l_name', null);
            if (!empty($request['company_name'])) $this->filterWithStringAttributes($request['company_name'], $query, 'display_name', null, null);

            if ($request['no_of_prop'] === '0') {
                $query->whereDoesntHave('properties');
            } elseif (!empty($request['no_of_prop'])) {

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
            if ($request['no_of_available'] === '0') {
                $query->whereDoesntHave('properties', function ($propertyQuery) {
                    $propertyQuery->whereIn('status', [1, 3]);
                });
            } elseif (!empty($request['no_of_available'])) {
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
            if ($request['no_of_processing'] === '0') {
                $query->whereDoesntHave('properties', function ($propertyQuery) {
                    $propertyQuery->where('status', 4);
                });
            } elseif (!empty($request['no_of_processing'])) {

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
            if ($request['no_of_let'] === '0') {
                $query->whereDoesntHave('properties', function ($propertyQuery) {
                    $propertyQuery->where('status', 5);
                });
            } elseif (!empty($request['no_of_let'])) {

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

        foreach ($all_landlords as $i => $landlord) {
            $all_landlords[$i]->total = $landlord->properties()->count();
            $all_landlords[$i]->available = $landlord->properties()->whereIn('status', [1, 3])->count();
            $all_landlords[$i]->processing = $landlord->properties()->where('status', 4)->count();
            $all_landlords[$i]->let = $landlord->properties()->where('status', 5)->count();
        }
        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $all_landlords = $all_landlords->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordVariables[request('sort_by')]) ? $this->sortingLandlordVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
        $data = $all_landlords->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'landlords' => ['data' => $data, 'total' => $all_landlords->count()]]);
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

        $tenancies = Tenancy::where('agency_id', authAgencyId())->with(['users', 'properties']);

        $tenancies = $tenancies->where(function ($query) use ($request) {
            if (!empty($request['reference']))  $this->filterWithStringAttributes($request['reference'], $query, 'reference', null, null);
            if (!empty($request['tenancy_address']))  $this->filterWithStringAttributes($request['tenancy_address'], $query, 'pro_address', null, null);
            if (!empty($request['status'])) $this->filterWithStatusAttributes($request['status'], $query, 'status', $this->tenancyStatus);
            if (!empty($request['monthly_amount'])) $this->filterWithNumberAttributes($request['monthly_amount'], $query, 'monthly_amount', null, null);
            if (!empty($request['total_rent'])) $this->filterWithNumberAttributes($request['total_rent'], $query, 'total_rent', null, null);
            if ($request['deposite_amount'] === '0') {
                $query->where('deposite_amount', 0);
            }
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

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status' || request('sort_by') == 'type') {
            $i = 0;
            $newcollection = new Collection();
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
                        $newcollection->push($ti);
                        unset($tenancies[$key]);
                    }
                }
                $i++;
            }
            $data = $newcollection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $tenancies = $newcollection;
        } else {
            $tenancies = $tenancies->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingTenancyVariables[request('sort_by')]) ? $this->sortingTenancyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $tenancies->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return response()->json(['saved' => true, 'tenancies' => ['data' => $data, 'total' => $tenancies->count()]]);
    }

    /**
     * Filter records for tenancy events based on the provided request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tenancyEventFilterRecords(Request $request)
    {
        $tenancyEvents = TenancyEvents::where('agency_id', authAgencyId())
            ->where('tenancy_id', $request['tenancy_id'])
            ->where(function ($query) use ($request) {

                if (!empty($request['event_type']))  $this->filterWithStringAttributes($request['event_type'], $query, 'event_type', null, null);
                if (!empty($request['creator_name']))  $this->filterWithStringAttributes($request['creator_name'], $query, 'creator', null, null);
                if (!empty($request['description']))  $this->filterWithStringAttributes($request['description'], $query, 'description', null, null);
                if (!empty($request['date'])) $this->filterWithDateAttributes($request['date'], $query, 'date', null, null);
            })
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingTenancyEventVariables[request('sort_by')]) ? $this->sortingTenancyEventVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $data = $tenancyEvents->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'tenancy_events' => ['data' => $data, 'total' => $tenancyEvents->count()]]);
    }
}
