<?php

use App\Http\Controllers\ApplicantViewController;
use App\Http\Controllers\TenancyAgreementController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt', 'mail_server'], 'roles' => 'Agency'], function () {

    Route::get('review_tenancy/{id}', [ApplicantViewController::class, 'reviewTenancyApplicant']);
    Route::post('custom_email', [ApplicantViewController::class, 'postSendCustomEmail']);
    Route::post('tenancy_info', [ApplicantViewController::class, 'postTenancyUpdate']);
    Route::get('tenancy_events/{id}', [ApplicantViewController::class, 'getAllTenancyEvents']);
    Route::post('applicant_info', [ApplicantViewController::class, 'postApplicantInfoUpdate']);
    Route::post('landlord_info', [ApplicantViewController::class, 'postLandlordInfoUpdate']);
    Route::post('guarantor_info', [ApplicantViewController::class, 'postGuarantorInfoUpdate']);
    Route::post('employment_info', [ApplicantViewController::class, 'postEmploymentInfoUpdate']);
    Route::post('quarterly_info', [ApplicantViewController::class, 'postQuarterlyInfoUpdate']);
    Route::post('quarterly_reference_info', [ApplicantViewController::class, 'postQuarterlyReferenceInfo']);
    Route::post('landlord_reference_info', [ApplicantViewController::class, 'postLandlordReferenceInfo']);
    Route::post('employment_reference_info', [ApplicantViewController::class, 'postEmploymentReferenceInfo']);
    Route::post('guarantor_reference_info', [ApplicantViewController::class, 'postGuarantorReferenceInfo']);
    Route::post('add_applicant', [ApplicantViewController::class, 'postAddNewApplicantToTenancy']);
    Route::post('delete_applicant', [ApplicantViewController::class, 'postDeleteApplicantToTenancy']);
    Route::post('change_negotiator', [ApplicantViewController::class, 'changeTenancyNegotiator']);
    Route::post('update_addresses', [ApplicantViewController::class, 'postUpdateReferencesAddresses']);
    Route::post('review_agreement', [ApplicantViewController::class, 'reviewTenancyAgreement']);
    Route::get('tenancy_full_info/{id}', [TenancyAgreementController::class, 'tenancyAgreementFullData']);
    Route::post('tenancy_agreement_generate', [TenancyAgreementController::class, 'postTenancyAgreementPdfGeneratorSaveToDatabase']);
    Route::get('tenancy_agreement/{id}', [TenancyAgreementController::class, 'getTenancyAgreement']);
    Route::post('add_landlord', [ApplicantViewController::class, 'postAddNewLandlordToTenancy']);
    Route::post('delete_landlord', [ApplicantViewController::class, 'postDeleteLandlordToTenancy']);
    Route::post('add_employment', [ApplicantViewController::class, 'postAddNewEmploymentToTenancy']);
    Route::post('delete_employment', [ApplicantViewController::class, 'postDeleteEmploymentToTenancy']);
    Route::post('add_guarantor', [ApplicantViewController::class, 'postAddNewGuarantorToTenancy']);
    Route::post('delete_guarantor', [ApplicantViewController::class, 'postDeleteGuarantorToTenancy']);
    Route::post('add_addresses', [ApplicantViewController::class, 'postAddNewAddresses']);
    Route::delete('delete_address/{id}/{index}', [ApplicantViewController::class, 'postDeleteAddresses']);
    Route::post('applicants/pause/chasing/emails', [ApplicantViewController::class, 'pauseChasingEmails']);
    Route::post('applicants/resume/chasing/emails', [ApplicantViewController::class, 'resumeChasingEmails']);
    Route::post('resend/guarantor_email', [ApplicantViewController::class, 'resendGuarantorEmail']);
    Route::post('resend/employment_email', [ApplicantViewController::class, 'resendEmploymentEmail']);
    Route::post('resend/landlord_email', [ApplicantViewController::class, 'resendLandlordEmail']);
    Route::post('resend/applicant_email', [ApplicantViewController::class, 'resendApplicantEmail']);
    Route::get('interim_inspection/{id}', [ApplicantViewController::class, 'intrimInspection']);
    Route::get('inspection_current_month/{id}', [ApplicantViewController::class, 'getInterimInspection']);
    Route::get('inspection_past_month/{id}', [ApplicantViewController::class, 'getPastInterimInspection']);
    Route::get('inspection_done/{id}', [ApplicantViewController::class, 'getDoneInterimInspection']);
    Route::post('interim_inspection/action', [ApplicantViewController::class, 'actionInterimInspection']);
    Route::post('download_applicant_references/{id}', [ApplicantViewController::class, 'downloadApplicantRefrences']);
    Route::post('update_tenancy', [ApplicantViewController::class, 'addAgreementTypeInTenancyHistory']);
});

Route::group(['prefix' => 'applicant', 'middleware' => ['applicant.config', 'jwt', 'mail_server']], function () {
    Route::post('agreement_signing', [TenancyAgreementController::class, 'agreementSigningAndCheckTenancyStatus']);
    Route::get('tenancy_agreement/{id}', [TenancyAgreementController::class, 'getTenancyAgreement']);
});

