<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\CSVDataController;
use App\Http\Controllers\DefaultSettingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::get('tenancy_view/{id}', [AgencyController::class, 'tenancyViewFromId']);
    Route::get('applicants', [AgencyController::class, 'getAllApplicants']);
    Route::get('staff_details/{id}', [AgencyController::class, 'getStaffDetail']);
    Route::get('staff_info/{id}', [AgencyController::class, 'getStaffDetailWithPermission']);
    Route::get('agency_info', [AgencyController::class, 'getAgencyInfo']);
    Route::delete('delete_applicant/{id}', [AgencyController::class, 'deleteApplicantById']);
    Route::get('applicant/info/{id}', [AgencyController::class, 'getApplicantInfoById']);
    Route::get('agency_members', [AgencyController::class, 'getAllTheAgencyMembers']);
    Route::post('statistics', [StatisticsController::class, 'agencyStatistics']);
    Route::post('property/statistics', [StatisticsController::class, 'averageAgencyProperty']);
    Route::post('property/bedroom', [StatisticsController::class, 'agencypropertyBedroom']);
    Route::get('get_certificate/{type}/{fileName}', [AgencyController::class, 'getCertificate']);
    Route::post('applicant/generate_csv', [CSVDataController::class, 'applicantCsvDataEmail']);
    Route::post('help/contact-us', [AgencyController::class, 'helpAndContactUs']);
});

Route::group(['prefix' => 'agency', 'middleware' => ['jwt', 'mail_server'], 'roles' => 'Agency'], function () {
    Route::post('edit_staff_status', [AgencyController::class, 'isActiveStaffMember']);
    Route::post('edit_applicant', [AgencyController::class, 'postEditApplicant']);
});

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::post('default_setting', [DefaultSettingController::class, 'defaultSettings']);
});

