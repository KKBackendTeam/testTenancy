<?php

namespace App\Traits;

trait SortingActionTrait
{
    public $defaultSortBy = 'created_at', $defaultSortingAction = 'sortByDesc', $sortingString = SORT_NATURAL | SORT_FLAG_CASE;

    public $sortingAction = ['asc' => 'sortBy', 'desc' => 'sortByDesc'];

    public $applicantAscStatusArray = [6, 7, 1, 2, 5, 4, 3, 10, 11, 12, 9, 8], $applicantDescStatusArray = [8, 9, 12, 11, 10, 3, 4, 5, 2, 1, 7, 6];

    public $propertyAscStatusArray = [3, 1, 2, 4, 5, 6], $propertyDescStatusArray = [6, 5, 4, 2, 1, 3];

    public $tenancyAscStatusArray = [3, 18, 17, 5, 10, 11, 9, 4, 2, 8, 6, 1, 14, 16, 15, 13, 12], $tenancyDescStatusArray = [12, 13, 15, 16, 14, 1, 6, 8, 2, 4, 9, 11, 10, 5, 17, 18, 3];

    public $propertyStatusArrayCount = 6, $tenancyStatusArrayCount = 18, $applicantStatusArrayCount = 12, $tenancyTypeArrayCount  = 3;

    public $tenancyAscTypeArray = [1, 3, 2], $tenancyDescTypeArray = [2, 3, 1];

    public $sortingStaffVariables = [
        'name' => 'name',
        'email' => 'email',
        'mobile' => 'mobile',
        'last_action' => 'last_action',
        'status' => 'is_active',
        'last_action_date' => 'last_action_date'
    ];

    public $sortingApplicantVariables = [
        'tenancy_address' => 'tenancies.pro_address',
        'reference' => 'tenancies.reference',
        'creator_name' => 'users.name',
        'name' => 'applicantBasic.app_name',
        'email' => 'applicantBasic.email',
        'post_code' => 'post_code',
        'mobile' => 'applicantBasic.app_mobile',
        'status' => 'status'
    ];

    public $sortingInterimInspectionVariables = [
        'reference' => 'reference',
        'address' => 'address',
        'inspection_month' => 'inspection_month',
        'inspection_date' => 'inspection_date',
        'email_date' => 'email_date'
    ];

    public $sortingLandlordVariables = [
        'landlord_name' => 'f_name',
        'company_name' => 'display_name',
        'no_of_prop' => 'total',
        'no_of_available' => 'available',
        'no_of_processing' => 'processing',
        'no_of_let' => 'let'
    ];

    public $sortingPropertyVariables = [
        'address' => 'street',
        'reference' => 'property_ref',
        'post_code' => 'post_code',
        'status' => 'status',
        'parking_cost' => 'parking_cost',
        'monthly_amount' => 'monthly_rent',
        'total_rent' => 'total_rent',
        'deposite_amount' => 'deposite_amount',
        'holding_amount' => 'holding_fee_amount',
        'bedroom' => 'bedroom',
        'available_from' => 'available_from',
        'landlord_name' => 'landlords.f_name',
        'hmo_expiry_date' => 'hmo_expiry_date',
        'fire_alarm_expiry_date' => 'fire_alarm_expiry_date'

    ];

    public $sortingTenancyVariables = [
        'reference' => 'reference',
        'tenancy_address' => 'pro_address',
        'status' => 'status',
        'monthly_amount' => 'monthly_amount',
        'total_rent' => 'total_rent',
        'deposite_amount' => 'deposite_amount',
        'holding_amount' => 'holding_amount',
        'parking_cost' => 'parking_cost',
        'no_applicant' => 'no_applicant',
        'type' => 'type',
        'start_date' => 't_start_date',
        'end_date' => 't_end_date',
        'create_date' => 'created_at',
        'updated_at' => 'updated_at',
        'landlord_name' => 'landlords.f_name',
        'created_by' => 'users.name',
        'no_beds' => 'properties.bedroom'
    ];

    public $sortingTenancyEventVariables = [
        'event_type' => 'event_type',
        'creator_name' => 'creator',
        'description' => 'description',
        'date' => 'date'
    ];

    public $sortingAgencyVariables = [
        'name' => 'name',
        'email' => 'email',
        'phone' => 'phone',
        'status' => 'status',
        'last_login' => 'last_login',
        'total_credit' => 'total_credit',
        'used_credit' => 'used_credit'
    ];

    public $sortingProblematicApplicantVariables = [
        'tenancy_reference' => 'tenancies.reference',
        'applicant_name' => 'applicantBasic.app_name',
        'email' => 'applicantBasic.email',
        'mobile' => 'applicantBasic.app_mobile'
    ];

    public $sortingEmploymentReviewVariables = [
        'name' => 'name',
        'company_email' => 'company_email',
        'company_name' => 'company_name',
        'company_phone' => 'company_phone',
        'job_title' => 'job_title',
        'applicant_name' => 'applicants.applicantBasic.app_name',
        'tenancy_reference' => 'applicants.tenancies.reference'
    ];

    public $sortingGuarantorReviewVariables = [
        'name' => 'name',
        'company_email' => 'company_email',
        'company_name' => 'company_name',
        'company_phone' => 'company_phone',
        'job_title' => 'job_title',
        'applicant_name' => 'applicants.applicantBasic.app_name',
        'tenancy_reference' => 'applicants.tenancies.reference'
    ];

    public $sortingLandlordReviewVariables = [
        'name' => 'name',
        'company_email' => 'company_email',
        'company_name' => 'company_name',
        'company_phone' => 'company_phone',
        'job_title' => 'job_title',
        'applicant_name' => 'applicants.applicantBasic.app_name',
        'tenancy_reference' => 'applicants.tenancies.reference',
        'created_at' => 'landlord_references.created_at'
    ];

    public function checkPageAndPagesize($page, $pageSize)
    {
        return ['page' => is_null($page) ? 1 : $page, 'pageSize' => (is_null($pageSize) || $pageSize < 10) ? 10 : $pageSize];
    }
}
