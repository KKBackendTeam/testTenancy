<?php

use App\Http\Controllers\ActionRequiredController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardFilterRecordsController;
use App\Http\Controllers\AgencyController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::get('dashboard', [DashboardController::class, 'agencyDashboard']);
    Route::get('staff_list', [DashboardController::class, 'staffList']);
    Route::get('fetch_staff_dashboard/{id}', [DashboardController::class, 'agencyFetchStaffDashboard']);
    Route::get('basic_information', [AgencyController::class,'getBasicInformation']);
    Route::get('interim_inspection', [DashboardController::class, 'intrimInspection']);
});

$subjects = ['agency', 'agency/staff'];

foreach ($subjects as $subject) {
    Route::group(['prefix' => $subject, 'middleware' => ['jwt']], function () use ($subject) {
        Route::get('problematic_application/{id}', [DashboardController::class, 'problematicApplicantData']);
        Route::get('awaiting_employment_review/{id}', [DashboardController::class, 'awaitingEmploymentReview']);
        Route::get('awaiting_guarantor_review/{id}', [DashboardController::class, 'awaitingGuarantorReview']);
        Route::get('awaiting_landlord_review/{id}', [DashboardController::class, 'awaitingLandlordReview']);
        Route::get('awaiting_quarterly_review/{id}', [DashboardController::class, 'awaitingQuarterlyReview']);
        Route::get('awaiting_ta_review/{id}', [DashboardController::class, 'awaitingTAReview']);
        Route::get('awaiting_ta_sending/{id}', [DashboardController::class, 'awaitingTASending']);
        Route::get('failed_employment_review/{id}', [DashboardController::class, 'failedEmploymentReview']);
        Route::get('failed_guarantor_review/{id}', [DashboardController::class, 'failedGuarantorReview']);
        Route::get('failed_landlord_review/{id}', [DashboardController::class, 'failedLandlordReview']);
        Route::get('failed_quarterly_review/{id}', [DashboardController::class, 'failedQuarterlyReview']);
        Route::get('accelerated_application/{id}', [DashboardController::class, 'progressButStartingSoon']);
        Route::get('awaiting_applications/{id}', [DashboardController::class, 'awaitingApplicantForm']);
        Route::get('awaiting_references/{id}', [DashboardController::class, 'awaitingReference']);
        Route::get('awaiting_signing/{id}', [DashboardController::class, 'awaitingSigning']);
        Route::get('recently_finalised/{id}', [DashboardController::class, 'recently_finalized']);
        Route::get('tenancy_complete_new/{id}', [DashboardController::class, 'tenancyCompleteNew']);
        Route::get('tenancy_complete_renew/{id}', [DashboardController::class, 'tenancyCompleteRenew']);
        Route::get('tenancy_complete_partial_new/{id}', [DashboardController::class, 'tenancyCompletePartialRenew']);
        Route::get('applicant_right_to_rent_expired/{id}', [DashboardController::class, 'applicantRightToExpired']);
        Route::get('applicant_right_to_rent/{id}', [DashboardController::class, 'applicantRightToExpiredWithInThirtyDays']);
        Route::get('epc_certificate_property/{id}', [DashboardController::class, 'epcCertificateExpiryWithInThirtyDays']);
        Route::get('gas_certificate_property/{id}', [DashboardController::class, 'gasCertificateExpiryWithInThirtyDays']);
        Route::get('eicr_certificate_property/{id}', [DashboardController::class, 'eicrCertificateExpiryWithInThirtyDays']);
        Route::get('hmo_certificate_property/{id}', [DashboardController::class, 'hmoCertificateExpiryWithInThirtyDays']);
        Route::get('fire_alarm_certificate_property/{id}', [DashboardController::class, 'fireAlarmCertificateExpiryWithInThirtyDays']);
        Route::get('inspection_current_month', [DashboardController::class, 'getAllInterimInspection']);
        Route::get('inspection_past_month', [DashboardController::class, 'getAllPastInterimInspection']);
        Route::get('inspection_done', [DashboardController::class, 'getAllDoneInterimInspection']);

        Route::post('problematic_application', [DashboardFilterRecordsController::class, 'problematicApplicantData']);
        Route::post('awaiting_employment_review', [DashboardFilterRecordsController::class, 'awaitingEmploymentReview']);
        Route::post('awaiting_guarantor_review', [DashboardFilterRecordsController::class, 'awaitingGuarantorReview']);
        Route::post('awaiting_landlord_review', [DashboardFilterRecordsController::class, 'awaitingLandlordReview']);
        Route::post('awaiting_quarterly_review', [DashboardFilterRecordsController::class, 'awaitingQuarterlyReview']);
        Route::post('awaiting_ta_review', [DashboardFilterRecordsController::class, 'awaitingTAReview']);
        Route::post('awaiting_ta_sending', [DashboardFilterRecordsController::class, 'awaitingTASending']);
        Route::post('failed_employment_review', [DashboardFilterRecordsController::class, 'failedEmploymentReview']);
        Route::post('failed_guarantor_review', [DashboardFilterRecordsController::class, 'failedGuarantorReview']);
        Route::post('failed_landlord_review', [DashboardFilterRecordsController::class, 'failedLandlordReview']);
        Route::post('failed_quarterly_review', [DashboardFilterRecordsController::class, 'failedQuarterlyReview']);
        Route::post('accelerated_application', [DashboardFilterRecordsController::class, 'progressButStartingSoon']);
        Route::post('awaiting_applications', [DashboardFilterRecordsController::class, 'awaitingApplicantForm']);
        Route::post('awaiting_references', [DashboardFilterRecordsController::class, 'awaitingReference']);
        Route::post('awaiting_signing', [DashboardFilterRecordsController::class, 'awaitingSigning']);
        Route::post('recently_finalised', [DashboardFilterRecordsController::class, 'recently_finalized']);
        Route::post('tenancy_complete_new', [DashboardFilterRecordsController::class, 'tenancyCompleteNew']);
        Route::post('tenancy_complete_renew', [DashboardFilterRecordsController::class, 'tenancyCompleteRenew']);
        Route::post('tenancy_complete_partial_new', [DashboardFilterRecordsController::class, 'tenancyCompletePartialRenew']);
        Route::post('applicant_right_to_rent_expired', [DashboardFilterRecordsController::class, 'rightToRentExpired']);
        Route::post('applicant_right_to_rent', [DashboardFilterRecordsController::class, 'rightToRentExpiredWithInThirtyDays']);
        Route::post('epc_certificate_property', [DashboardFilterRecordsController::class, 'epcCertificateExpiryWithInThirtyDays']);
        Route::post('gas_certificate_property', [DashboardFilterRecordsController::class, 'gasCertificateExpiryWithInThirtyDays']);
        Route::post('eicr_certificate_property', [DashboardFilterRecordsController::class, 'eicrCertificateExpiryWithInThirtyDays']);
        Route::post('hmo_certificate_property', [DashboardFilterRecordsController::class, 'hmoCertificateExpiryWithInThirtyDays']);
        Route::post('fire_alarm_certificate_property', [DashboardFilterRecordsController::class, 'fireAlarmCertificateExpiryWithInThirtyDays']);
    });
}

Route::group(['prefix' => 'agency', 'middleware' => ['jwt', 'mail_server'], 'roles' => 'Agency'], function () {
    Route::post('tenancy_cancel', [ActionRequiredController::class, 'getTenancyCancelStatus']);
    Route::post('extend_deadline', [ActionRequiredController::class, 'postExtendDeadline']);
    Route::post('send_custom_email', [ActionRequiredController::class, 'postSendCustomEmail']);
    Route::post('send_interim_inspection_email', [ActionRequiredController::class, 'postSendCustomInterimInspectionEmail']);
});
