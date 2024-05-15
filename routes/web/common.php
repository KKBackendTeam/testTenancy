<?php

use App\Http\Controllers\CommonController;
use App\Http\Controllers\FilterRecordsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferenceController;
use Illuminate\Support\Facades\Route;

$subjects = ['admin', 'agency'];

foreach ($subjects as $subject) {
    Route::group(['prefix' => $subject, 'middleware' => ['jwt']], function () use ($subject) {
        Route::get('notifications', [CommonController::class, 'allNotifications']);
        Route::get('mark_all_read', [CommonController::class, 'markAllRead']);
        Route::get('view_all_notifications', [CommonController::class, 'paginationNotifications']);
        Route::get('make_read_notification/{id}', [CommonController::class, 'makeReadNotification']);
        Route::delete('delete_notification/{id}', [CommonController::class, 'deleteNotification']);
        Route::get('my_info', [ProfileController::class, 'getProfileInfo']);
        Route::post('update_selfie', [ProfileController::class, 'postUpdateProfile']);
        Route::post('update_personal_info', [ProfileController::class, 'postUpdatePersonalInfo']);
        Route::post('change_password', [ProfileController::class, 'postChangePassword']);
        Route::post('update_media_logo', [ProfileController::class, 'postUpdateMediaLogo']);

        Route::post('property_filter', [FilterRecordsController::class, 'propertyFilterRecords']);
        Route::post('landlord_filter', [FilterRecordsController::class, 'landlordFilterRecords']);
        Route::post('tenancy_filter', [FilterRecordsController::class, 'tenancyFilterRecords']);
        Route::post('applicant_filter', [FilterRecordsController::class, 'applicantFilterRecords']);
        Route::post('agency_filter', [FilterRecordsController::class, 'agencyFilterRecords']);
        Route::post('staff_filter', [FilterRecordsController::class, 'staffFilterRecords']);
        Route::post('tenancy_event_filter', [FilterRecordsController::class, 'tenancyEventFilterRecords']);
        Route::post('inspection_filter_current_month', [FilterRecordsController::class, 'filterCurrentMonthInterimInspections']);
        Route::post('inspection_filter_past_month', [FilterRecordsController::class, 'filterPastInterimInspections']);
        Route::post('inspection_filter_completed_month', [FilterRecordsController::class, 'filterCompletedInterimInspections']);
        Route::post('inspection_filter_current_month/{id}', [FilterRecordsController::class, 'filterTenancyCurrentMonthInterimInspections']);
        Route::post('inspection_filter_past_month/{id}', [FilterRecordsController::class, 'filterTenancyPastInterimInspections']);
        Route::post('inspection_filter_completed_month/{id}', [FilterRecordsController::class, 'filterTenancyCompletedInterimInspections']);
    });
}

Route::group(['prefix' => 'applicant', 'middleware' => ['applicant.config']], function () {
    Route::get('notifications', [CommonController::class, 'allNotifications']);
    Route::get('view_all_notifications', [CommonController::class, 'paginationNotifications']);
    Route::get('mark_all_read', [CommonController::class, 'markAllRead']);
    Route::get('make_read_notification/{id}', [CommonController::class, 'makeReadNotification']);
    Route::delete('delete_notification/{id}', [CommonController::class, 'deleteNotification']);
});

Route::post('guarantor/guarantor_info/pdf', [ReferenceController::class, 'getGuarantorInfoPdf']);
Route::post('landlord/landlord_info/pdf', [ReferenceController::class, 'getLandlordInfoPdf']);
Route::post('employment/employment_info/pdf', [ReferenceController::class, 'getEmploymentInfoPdf']);



