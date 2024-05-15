<?php

use App\Http\Controllers\PredefinedFilterController;
use App\Http\Controllers\PreDefinedFilterRecordsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency/tenancy', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::get('lapsed_status/{id}', [PredefinedFilterController::class, 'lapsedApplicationStatus']);
    Route::get('lapsed_start/{id}', [PredefinedFilterController::class, 'lapsedApplicationStartDate']);
    Route::get('incomplete_status/{id}', [PredefinedFilterController::class, 'incompleteButStartingSoonStatus']);
    Route::get('incomplete_start/{id}', [PredefinedFilterController::class, 'incompleteButStartingSoonStartDate']);
    Route::get('complete_status/{id}', [PredefinedFilterController::class, 'completeButStartingSoonStatus']);
    Route::get('complete_start/{id}', [PredefinedFilterController::class, 'completeButStartingSoonStartDate']);
    Route::get('awaiting_app_status/{id}', [PredefinedFilterController::class, 'awaitingApplicationFormStatus']);
    Route::get('awaiting_app_start/{id}', [PredefinedFilterController::class, 'awaitingApplicationFormStartDate']);
    Route::get('awaiting_ref_status/{id}', [PredefinedFilterController::class, 'awaitingReferenceStatus']);
    Route::get('awaiting_ref_start/{id}', [PredefinedFilterController::class, 'awaitingReferenceStartDate']);
    Route::get('awaiting_sign_status/{id}', [PredefinedFilterController::class, 'awaitingSigningStatus']);
    Route::get('awaiting_sign_start/{id}', [PredefinedFilterController::class, 'awaitingSigningStartDate']);
    Route::get('stalled_status/{id}', [PredefinedFilterController::class, 'stalledApplicationStatus']);
    Route::get('stalled_start/{id}', [PredefinedFilterController::class, 'stalledApplicationStartDate']);
    Route::get('awaiting_review_status/{id}', [PredefinedFilterController::class, 'awaitingReviewStatus']);
    Route::get('awaiting_review_start/{id}', [PredefinedFilterController::class, 'awaitingReviewStartDate']);
    Route::get('failed_review_status/{id}', [PredefinedFilterController::class, 'failedReviewStatus']);
    Route::get('failed_review_start/{id}', [PredefinedFilterController::class, 'failedReviewStartDate']);
    Route::get('complete_recently_status/{id}', [PredefinedFilterController::class, 'completeRecentlyStatus']);
    Route::get('complete_recently_start/{id}', [PredefinedFilterController::class, 'completeRecentlyStartDate']);
    Route::get('complete_not_start_status/{id}', [PredefinedFilterController::class, 'completeNotStartedStatus']);
    Route::get('complete_not_start_start/{id}', [PredefinedFilterController::class, 'completeNotStartedStartDate']);
});

Route::group(['prefix' => 'agency/tenancy', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::post('lapsed_status', [PreDefinedFilterRecordsController::class, 'lapsedApplicationStatus']);
    Route::post('lapsed_start', [PreDefinedFilterRecordsController::class, 'lapsedApplicationStartDate']);
    Route::post('incomplete_status', [PreDefinedFilterRecordsController::class, 'incompleteButStartingSoonStatus']);
    Route::post('incomplete_start', [PreDefinedFilterRecordsController::class, 'incompleteButStartingSoonStartDate']);
    Route::post('complete_status', [PreDefinedFilterRecordsController::class, 'completeButStartingSoonStatus']);
    Route::post('complete_start', [PreDefinedFilterRecordsController::class, 'completeButStartingSoonStartDate']);
    Route::post('awaiting_app_status', [PreDefinedFilterRecordsController::class, 'awaitingApplicationFormStatus']);
    Route::post('awaiting_app_start', [PreDefinedFilterRecordsController::class, 'awaitingApplicationFormStartDate']);
    Route::post('awaiting_ref_status', [PreDefinedFilterRecordsController::class, 'awaitingReferenceStatus']);
    Route::post('awaiting_ref_start', [PreDefinedFilterRecordsController::class, 'awaitingReferenceStartDate']);
    Route::post('awaiting_sign_status', [PreDefinedFilterRecordsController::class, 'awaitingSigningStatus']);
    Route::post('awaiting_sign_start', [PreDefinedFilterRecordsController::class, 'awaitingSigningStartDate']);
    Route::post('stalled_status', [PreDefinedFilterRecordsController::class, 'stalledApplicationStatus']);
    Route::post('stalled_start', [PreDefinedFilterRecordsController::class, 'stalledApplicationStartDate']);
    Route::post('awaiting_review_status', [PreDefinedFilterRecordsController::class, 'awaitingReviewStatus']);
    Route::post('awaiting_review_start', [PreDefinedFilterRecordsController::class, 'awaitingReviewStartDate']);
    Route::post('failed_review_status', [PreDefinedFilterRecordsController::class, 'failedReviewStatus']);
    Route::post('failed_review_start', [PreDefinedFilterRecordsController::class, 'failedReviewStartDate']);
    Route::post('complete_recently_status', [PreDefinedFilterRecordsController::class, 'completeRecentlyStatus']);
    Route::post('complete_recently_start', [PreDefinedFilterRecordsController::class, 'completeRecentlyStartDate']);
    Route::post('complete_not_start_status', [PreDefinedFilterRecordsController::class, 'completeNotStartedStatus']);
    Route::post('complete_not_start_start', [PreDefinedFilterRecordsController::class, 'completeNotStartedStartDate']);
});


