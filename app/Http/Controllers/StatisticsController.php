<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Landloard;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\AllPermissions;
use App\Models\Applicant;

class StatisticsController extends Controller
{
    use AllPermissions;

    /**
     * Get agency statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function agencyStatistics(Request $request)
    {
        return response()->json([
            'allMembers' => $this->allMembers(),
            'tenancies_count' => $this->agencyTenancyCount($request),
            'properties_count' => $this->agencyPropertyCount($request),
            'tenancyDateComparsion' => $this->tenancyDateComparison($request),
            'propertyDateComparision' => $this->propertyDateComparison($request),
            'average_time' => $this->tenancyAgencyAverageTime($request),
        ]);
    }

    /**
     * Get all members of the agency.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allMembers()
    {
        return User::where('agency_id', authAgencyId())->latest()->get();
    }

    /**
     * Get all agency.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allAgencies()
    {
        return Agency::where('status', 1)->latest()->get();
    }

    /**
     * Compare tenancy counts between two date ranges.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function tenancyDateComparison($request)
    {
        $startDate1 = $request->input('start_date_1');
        $endDate1 = $request->input('end_date_1');
        $startDate2 = $request->input('start_date_2');
        $endDate2 = $request->input('end_date_2');
        $creatorId = $request->input('creator_id');

        $response = [];

        if (!empty($startDate1) && !empty($endDate1)) {
            $count1 = $this->getTenancyCounts($startDate1, $endDate1, $creatorId);
            $response['tenancyFirstData'] = $this->formatCounts($count1);
        }

        if (!empty($startDate2) && !empty($endDate2)) {
            $count2 = $this->getTenancyCounts($startDate2, $endDate2, $creatorId);
            $response['tenancySecondData'] = $this->formatCounts($count2);
        }

        if (empty($response)) {
            return ['tenancyDateComparison' => $this->getAllMonthsTenancyData($creatorId)];
        }

        return $response;
    }

    /**
     * Get tenancy counts for all months of the current year.
     *
     * @return array
     */
    private function getAllMonthsTenancyData($creatorId)
    {
        $counts = [];
        $currentYear = Carbon::now()->year;
        $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();

        $query = Tenancy::where('agency_id', authAgencyId());

        if ($creatorId) {
            $query->where('creator_id', $creatorId);
        }

        $tenancyCounts = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUBSTRING(created_at::text, 6, 2) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        foreach ($months as $month) {
            $counts[$month] = 0;
        }
        foreach ($tenancyCounts as $tenancyCount) {
            $month = Carbon::createFromFormat('m', $tenancyCount->month)->format('M');
            $counts[$month] = $tenancyCount->count;
        }
        return $this->formatCounts($counts);
    }

    /**
     * Format counts array.
     *
     * @param  array  $counts
     * @return array
     */
    private function formatCounts($counts)
    {
        $formattedCounts = [];
        foreach ($counts as $month => $count) {
            $formattedCounts[] = [$month => $count];
        }
        return $formattedCounts;
    }

    /**
     * Get tenancy counts within a specified date range.
     *
     * @param  string  $startDate  The start date of the date range.
     * @param  string  $endDate  The end date of the date range.
     * @return array  An associative array with month names as keys and tenancy counts as values.
     */
    private function getTenancyCounts($startDate, $endDate,  $creatorId)
    {
        $counts = [];
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $query = Tenancy::where('agency_id', authAgencyId());

        if ($creatorId) {
            $query->where('creator_id', $creatorId);
        }

        $tenancyCounts = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUBSTRING(created_at::text, 6, 2) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get();

        foreach ($tenancyCounts as $tenancyCount) {
            $month = Carbon::createFromFormat('m', $tenancyCount->month)->format('M');
            $counts[$month] = $tenancyCount->count;
        }
        while ($startDate->lte($endDate)) {
            $month = $startDate->format('M');
            if (!isset($counts[$month])) {
                $counts[$month] = 0;
            }
            $startDate->addMonth();
        }
        return $counts;
    }

    /**
     * Compare property counts based on specified date ranges.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function propertyDateComparison($request)
    {
        $startDate1 = $request->input('pro_start_date_1');
        $endDate1 = $request->input('pro_end_date_1');
        $startDate2 = $request->input('pro_start_date_2');
        $endDate2 = $request->input('pro_end_date_2');
        $creatorId = $request->input('creator_id');

        $response = [];

        if (!empty($startDate1) && !empty($endDate1)) {
            $count1 = $this->getPropertyCounts($startDate1, $endDate1, $creatorId);
            $response['propertyFirstData'] = $this->formatCounts($count1);
        }

        if (!empty($startDate2) && !empty($endDate2)) {
            $count2 = $this->getPropertyCounts($startDate2, $endDate2, $creatorId);
            $response['propertySecondData'] = $this->formatCounts($count2);
        }

        if (empty($response)) {
            return ['tenancyDateComparison' => $this->getAllMonthPropertyData($creatorId)];
        }

        return $response;
    }

    /**
     * Get the counts of properties created within a specified date range.
     *
     * @param  string  $startDate  The start date of the date range.
     * @param  string  $endDate  The end date of the date range.
     * @return array  An associative array containing counts of properties for each month within the date range.
     */
    private function getPropertyCounts($startDate, $endDate, $creatorId)
    {
        $counts = [];
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $query = Property::where('agency_id', authAgencyId());

        if ($creatorId) {
            $query->where('creator_id', $creatorId);
        }

        $propertyCounts = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUBSTRING(created_at::text, 6, 2) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get();

        foreach ($propertyCounts as $propertyCount) {
            $month = Carbon::createFromFormat('m', $propertyCount->month)->format('M');
            $counts[$month] = $propertyCount->count;
        }
        while ($startDate->lte($endDate)) {
            $month = $startDate->format('M');
            if (!isset($counts[$month])) {
                $counts[$month] = 0;
            }
            $startDate->addMonth();
        }

        return $counts;
    }

    /**
     * Get counts of properties created for each month of the current year.
     *
     * @return array
     */
    private function getAllMonthPropertyData($creatorId)
    {
        $counts = [];
        $currentYear = Carbon::now()->year;
        $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();

        $query = Property::where('agency_id', authAgencyId());

        if ($creatorId) {
            $query->where('creator_id', $creatorId);
        }

        $propertyCounts = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUBSTRING(created_at::text, 6, 2) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        foreach ($months as $month) {
            $counts[$month] = 0;
        }
        foreach ($propertyCounts as $propertyCount) {
            $month = Carbon::createFromFormat('m', $propertyCount->month)->format('M');
            $counts[$month] = $propertyCount->count;
        }
        return $this->formatCounts($counts);
    }

    /**
     * Get the count of tenancies for the agency based on different time intervals.
     *
     * @return array
     */
    public function agencyTenancyCount($request)
    {
        $startDate = Carbon::parse($request->input('start_date_type'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date_type'))->endOfDay();
        $creatorId = $request->input('creator_id');

        $query = Tenancy::where('agency_id', authAgencyId());

        if (!empty($creatorId)) {
            $query->where('creator_id', $creatorId);
        }

        $todayCount = clone $query;
        $thisWeekCount = clone $query;
        $thisMonthCount = clone $query;
        $thisYearCount = clone $query;
        $totalCount = clone $query;
        $tenancyNewCount = clone $query;
        $tenancyRenewCount = clone $query;

        $todayCount = $todayCount->whereDate('created_at', Carbon::today())->count();

        $thisWeekCount = $thisWeekCount->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

        $thisMonthCount = $thisMonthCount->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $thisYearCount = $thisYearCount->whereYear('created_at', Carbon::now()->year)->count();

        $totalCount = $totalCount->count();

        $tenanciesCountStartingInMonth = 0;
        $selectedMonthTenancyStart = $request->input('selectedMonthtenancyStart');
        $tenanciesCountStartingInMonth = clone $query;
        if ($selectedMonthTenancyStart) {
            $startOfMonth = Carbon::create($selectedMonthTenancyStart['year'], $selectedMonthTenancyStart['month'], 1)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $tenanciesCountStartingInMonth = $tenanciesCountStartingInMonth->whereDate('t_start_date', '>=', $startOfMonth)
                ->whereDate('t_start_date', '<=', $endOfMonth)
                ->count();
        }

        $tenanciesCountEndingInMonth = 0;
        $selectedMonthTenancyEnd = $request->input('selectedMonthTenancyEnd');
        $tenanciesCountEndingInMonth = clone $query;
        if ($selectedMonthTenancyEnd) {
            $startOfMonth = Carbon::create($selectedMonthTenancyEnd['year'], $selectedMonthTenancyEnd['month'], 1)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $tenanciesCountEndingInMonth = $tenanciesCountEndingInMonth->whereDate('t_end_date', '>=', $startOfMonth)
                ->whereDate('t_end_date', '<=', $endOfMonth)
                ->count();
        }

        $tenancyNewCount = $tenancyNewCount->where('type', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $tenancyRenewCount = $tenancyRenewCount->whereIn('type', [2, 3])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $response = [
            'today_count' => $todayCount,
            'this_week_count' => $thisWeekCount,
            'this_month_count' => $thisMonthCount,
            'this_year_count' => $thisYearCount,
            'total_count' => $totalCount,
            'selected_month_count_tenancy_start' => $tenanciesCountStartingInMonth,
            'selected_month_count_tenancy_end' => $tenanciesCountEndingInMonth,
            'tenancy_new_count' => $tenancyNewCount,
            'tenancy_renew_count' => $tenancyRenewCount
        ];

        return $response;
    }

    /**
     * Get the bedroom of properties created by the agency.
     *
     * @return array
     */
    public function agencypropertyBedroom(Request $request)
    {
        $query = Property::where('agency_id', authAgencyId());
        $response = $this->calculatePropertyBedroom($request, $query);
        return response()->json([
            'property_bedroom' =>  $response
        ]);
    }

    /**
     * Get the bedroom of all properties.
     *
     * @return array
     */
    public function propertyBedroom(Request $request)
    {
        $query = Property::query();
        $response = $this->calculatePropertyBedroom($request, $query);
        return response()->json([
            'property_bedroom' =>  $response
        ]);
    }

    /**
     * Calculate the number of properties with each distinct number of bedrooms.
     *
     * @param  Request  $request
     * @param  Builder  $query
     * @return array
     */
    private function calculatePropertyBedroom($request, $query)
    {
        $postCode = $request->input('post_code');
        $distinctBedrooms = $query->where('post_code', $postCode)
            ->distinct()
            ->orderBy('bedroom')
            ->pluck('bedroom')
            ->toArray();

        return $distinctBedrooms;
    }

    /**
     * Get the average of properties created by the agency.
     *
     * @return array
     */
    public function averageAgencyProperty(Request $request)
    {
        $query = Property::where('agency_id', authAgencyId());
        $response = $this->calculatePropertyAverage($request, $query);
        return response()->json([
            'property_average' =>  $response
        ]);
    }

    /**
     * Get the average of all properties.
     *
     * @return array
     */
    public function averageProperty(Request $request)
    {
        $query = Property::query();
        $response = $this->calculatePropertyAverage($request, $query);
        return response()->json([
            'property_average' =>  $response
        ]);
    }

    /**
     * Calculate the average monthly rent for properties.
     *
     * @param  Request  $request
     * @param  Builder  $query
     * @return float
     */
    private function calculatePropertyAverage($request, $query)
    {
        $property = $query->where('post_code', $request->input('post_code'))->where('bedroom', $request->input('bedroom'));
        return round((float)$property->avg('monthly_rent'), 2);
    }

    /**
     * Get the count of properties created by the agency.
     *
     * @return array
     */
    public function agencyPropertyCount($request)
    {
        $creatorId = $request->input('creator_id');

        $query = Property::where('agency_id', authAgencyId());

        if (!empty($creatorId)) {
            $query->where('creator_id', $creatorId);
        }

        $todayCount = clone $query;
        $thisWeekCount = clone $query;
        $thisMonthCount = clone $query;
        $thisYearCount = clone $query;
        $totalCount = clone $query;
        $todayCount = $todayCount->whereDate('created_at', Carbon::today())->count();

        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $thisWeekCount = $thisWeekCount->whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])->count();

        $thisMonthCount = $thisMonthCount->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $thisYearCount = $thisYearCount->whereYear('created_at', Carbon::now()->year)->count();
        $totalCount = $totalCount->count();
        $response = [
            'today_count' => $todayCount,
            'this_week_count' => $thisWeekCount,
            'this_month_count' => $thisMonthCount,
            'this_year_count' => $thisYearCount,
            'total_count' => $totalCount,
        ];

        return $response;
    }

    /**
     * Get counts of tenancies based on different time periods.
     *
     * @return array
     */
    public function tenancyCount($request)
    {
        $agencyId = $request->input('agency_id');
        $baseQuery = Tenancy::query();
        $startDate = Carbon::parse($request->input('start_date_type'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date_type'))->endOfDay();

        if (!empty($agencyId)) {
            $query = clone $baseQuery;
            $query->where('agency_id', $agencyId);
        } else {
            $query = $baseQuery;
        }

        $response = $this->getCounts($query);
        $tenancyNewCount = clone $baseQuery;
        $tenancyRenewCount = clone $baseQuery;

        $response['tenancy_new_count'] = $tenancyNewCount->where('type', 1)->whereBetween('created_at', [$startDate, $endDate])->count();
        $response['tenancy_renew_count'] = $tenancyRenewCount->whereIn('type', [2, 3])->whereBetween('created_at', [$startDate, $endDate])->count();

        $tenanciesCountStartingInMonth = 0;
        $selectedMonthTenancyStart = $request->input('selectedMonthtenancyStart');
        $tenanciesCountStartingInMonth = clone $query;
        if ($selectedMonthTenancyStart) {
            $startOfMonth = Carbon::create($selectedMonthTenancyStart['year'], $selectedMonthTenancyStart['month'], 1)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $tenanciesCountStartingInMonth = $tenanciesCountStartingInMonth->whereDate('t_start_date', '>=', $startOfMonth)
                ->whereDate('t_start_date', '<=', $endOfMonth)
                ->count();
        }

        $tenanciesCountEndingInMonth = 0;
        $selectedMonthTenancyEnd = $request->input('selectedMonthTenancyEnd');
        $tenanciesCountEndingInMonth = clone $query;
        if ($selectedMonthTenancyEnd) {
            $startOfMonth = Carbon::create($selectedMonthTenancyEnd['year'], $selectedMonthTenancyEnd['month'], 1)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $tenanciesCountEndingInMonth = $tenanciesCountEndingInMonth->whereDate('t_end_date', '>=', $startOfMonth)
                ->whereDate('t_end_date', '<=', $endOfMonth)
                ->count();
        }
        $response['selected_month_count_tenancy_start'] = $tenanciesCountStartingInMonth;
        $response['selected_month_count_tenancy_end'] = $tenanciesCountEndingInMonth;

        return $response;
    }

    /**
     * Get the count of agencies created in the last five years.
     *
     * @return array
     */
    public function agencyLastFiveYearCount($request)
    {
        $selectedYear = $request->input('agencyYearFilter');
        $counts = [];
        if (!empty($selectedYear)) {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfDay();

            $query = Agency::whereIn('status', [0, 1]);

            $agencyCounts = $query->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('SUBSTRING(created_at::text, 6, 2) as month, COUNT(*) as count')
                ->groupBy('month')
                ->get();

            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $month) {
                $counts[$month] = 0;
            }
            foreach ($agencyCounts as $agencyCount) {
                $month = Carbon::createFromFormat('m', $agencyCount->month)->format('M');
                $counts[$month] = $agencyCount->count;
            }
            return $this->formatCounts($counts);
        } else {
            $currentYear = date('Y');
            $fiveYearsAgo = $currentYear - 5;

            for ($year = $currentYear; $year >= $fiveYearsAgo; $year--) {
                $query = Agency::whereIn('status', [0, 1])->whereYear('created_at', $year);

                if (!empty($agencyId)) {
                    $query->where('agency_id', $agencyId);
                }
                $count = $query->count();
                $counts[] = [
                    'year' => $year,
                    'count' => $count
                ];
            }
            return $counts;
        }
    }

    public function getCountsByYear($model, $request, $selectedYear, $agencyId, $dateFilterInputName)
    {
        $counts = [];

        if (!empty($selectedYear)) {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfDay();

            $query = $model::query();

            if ($agencyId) {
                $query->where('agency_id', $agencyId);
            }

            $countsData = $query->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('SUBSTRING(created_at::text, 6, 2) as month, COUNT(*) as count')
                ->groupBy('month')
                ->get();

            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $month) {
                $counts[$month] = 0;
            }
            foreach ($countsData as $countData) {
                $month = Carbon::createFromFormat('m', $countData->month)->format('M');
                $counts[$month] = $countData->count;
            }
            return $this->formatCounts($counts);
        } else {
            $currentYear = date('Y');
            $fiveYearsAgo = $currentYear - 5;

            for ($year = $currentYear; $year >= $fiveYearsAgo; $year--) {
                $query = $model::whereYear('created_at', $year);

                if (!empty($agencyId)) {
                    $query->where('agency_id', $agencyId);
                }
                $count = $query->count();
                $counts[] = [
                    'year' => $year,
                    'count' => $count
                ];
            }
            return $counts;
        }
    }

    public function landloardLastFiveYearCount($request)
    {
        $agencyId = $request->input('agency_id');
        $selectedYear = $request->input('landlordYearFilter');

        return $this->getCountsByYear(Landloard::class, $request, $selectedYear, $agencyId, 'landlordYearFilter');
    }

    public function propertyLastFiveYearCount($request)
    {
        $agencyId = $request->input('agency_id');
        $selectedYear = $request->input('propertyYearFilter');

        return $this->getCountsByYear(Property::class, $request, $selectedYear, $agencyId, 'propertyYearFilter');
    }

    public function tenanciesLastFiveYearCount($request)
    {
        $agencyId = $request->input('agency_id');
        $selectedYear = $request->input('tenancyFilterYear');

        return $this->getCountsByYear(Tenancy::class, $request, $selectedYear, $agencyId, 'tenancyFilterYear');
    }

    /**
     * Get count created today, this week, this month, this year, and total.
     *
     * @return array
     */
    private function getCounts($query)
    {
        $todayCount = clone $query;
        $yesterdayCount = clone $query;
        $thisWeekCount = clone $query;
        $prevWeekCount = clone $query;
        $thisMonthCount = clone $query;
        $prevMonthCount = clone $query;
        $thisYearCount = clone $query;
        $prevYearCount = clone $query;

        $todayCount = $todayCount->whereDate('created_at', Carbon::today())->count();
        $yesterdayCount = $yesterdayCount->whereDate('created_at', Carbon::yesterday())->count();
        $dayComparision = $todayCount - $yesterdayCount;
        $dayPercentChange = $yesterdayCount != 0 ? number_format(($dayComparision / $yesterdayCount) * 100, 2) : 0;

        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $thisWeekCount = $thisWeekCount->whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])->count();
        $prevWeekStart = Carbon::now()->startOfWeek()->subWeek();
        $prevWeekEnd = Carbon::now()->endOfWeek()->subWeek();
        $prevWeekCount = $prevWeekCount->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])->count();
        $weekDifference = $thisWeekCount - $prevWeekCount;
        $weekPercentageChange = $prevWeekCount != 0 ? number_format(($weekDifference / $prevWeekCount) * 100, 2) : 0;

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $thisMonthCount = $thisMonthCount->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count();
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $prevMonthCount = $prevMonthCount->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count();
        $monthDifference = $thisMonthCount - $prevMonthCount;
        $monthPercentageChange = $prevMonthCount != 0 ? number_format(($monthDifference / $prevMonthCount) * 100, 2) : 0;

        $thisYearStart = Carbon::now()->startOfYear();
        $thisYearEnd = Carbon::now()->endOfYear();
        $thisYearCount = $thisYearCount->whereBetween('created_at', [$thisYearStart, $thisYearEnd])->count();
        $prevYearStart = Carbon::now()->subYear()->startOfYear();
        $prevYearEnd = Carbon::now()->subYear()->endOfYear();
        $prevYearCount = $prevYearCount->whereBetween('created_at', [$prevYearStart, $prevYearEnd])->count();
        $yearDifference = $thisYearCount - $prevYearCount;
        $yearPercentageChange = $prevYearCount != 0 ? number_format(($yearDifference / $prevYearCount) * 100, 2) : 0;

        $totalCount = $query->count();

        return [
            'today_count' => $todayCount,
            'this_week_count' => $thisWeekCount,
            'this_month_count' => $thisMonthCount,
            'this_year_count' => $thisYearCount,
            'total_count' => $totalCount,
            'day_comparision' => $dayComparision,
            'day_percentage' => $dayPercentChange,
            'week_comparision' => $weekDifference,
            'week_percentage' => $weekPercentageChange,
            'month_difference' => $monthDifference,
            'month_percentage' => $monthPercentageChange,
            'year_difference' => $yearDifference,
            'year_percentage' => $yearPercentageChange,
        ];
    }

    /**
     * Get count of properties created today, this week, this month, this year, and total.
     *
     * @return array
     */
    public function propertyCount($request)
    {
        $agencyId = $request->input('agency_id');
        $baseQuery = Property::query();

        if (!empty($agencyId)) {
            $query = clone $baseQuery;
            $query->where('agency_id', $agencyId);
        } else {
            $query = $baseQuery;
        }

        return $this->getCounts($query);
    }

    /**
     * Get count of landlords created today, this week, this month, this year, and total.
     *
     * @return array
     */
    public function landloardCount($request)
    {
        $agencyId = $request->input('agency_id');
        $baseQuery = Landloard::query();

        if (!empty($agencyId)) {
            $query = clone $baseQuery;
            $query->where('agency_id', $agencyId);
        } else {
            $query = $baseQuery;
        }

        return $this->getCounts($query);
    }

    /**
     * Get count of agencies created today, this week, this month, this year, and total.
     *
     * @return array
     */
    public function agenciesCount()
    {
        $query = Agency::whereIn('status', [0, 1]);
        return $this->getCounts($query);
    }

    /**
     * Get statistics for super admin dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function superAdminStatistics(Request $request)
    {
        return response()
            ->json([
                'agencies_count' => $this->agenciesCount(),
                'tenancies_count' => $this->tenancyCount($request),
                'landloards_five_years_count' => $this->landloardLastFiveYearCount($request),
                'agency_five_years_count' => $this->agencyLastFiveYearCount($request),
                'landlords_count' => $this->landloardCount($request),
                'average_time' => $this->tenancyAverageTime($request),
                'allAgencies' => $this->allAgencies($request),
                'property_last_five_years_count' => $this->propertyLastFiveYearCount($request),
                'properties_count' => $this->propertyCount($request),
                'tenancies_last_five_years_count' => $this->tenanciesLastFiveYearCount($request),
                'property_average_amount' => $this->averageProperty($request)
            ]);
    }

    /**
     * Calculate the average time taken to complete a tenancy.
     *
     * @return float|null
     */
    public function tenancyAgencyAverageTime($request)
    {
        $creatorId = $request->input('creator_id');
        $query = Tenancy::where('agency_id', authAgencyId())->where('days_to_complete', '>', 0);

        if ($creatorId) {
            $query->where('creator_id', $creatorId);
        }

        return round((float)$query->avg('days_to_complete'), 2);
    }

    /**
     * Calculate the average time taken to complete a tenancy.
     *
     * @return float|null
     */
    public function tenancyAverageTime($request)
    {
        $agencyId = $request->input('agency_id');
        $query = Tenancy::where('days_to_complete', '>', 0);
        if ($agencyId) {
            $query->where('agency_id', $agencyId);
        }
        return round((float)$query->avg('days_to_complete'), 2);
    }
}
