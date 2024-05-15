<?php

use App\Http\Controllers\TenancyController;
use App\Http\Controllers\CSVDataController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {

    Route::get('get_tenancies', [TenancyController::class, 'getTenancies']);
    Route::post('tenancy/get_data', [CSVDataController::class, 'getTenancyCsvData']);
    Route::post('applicant/get_data', [CSVDataController::class, 'getApplicantCsvData']);
    Route::post('current_inspection/get_data', [CSVDataController::class, 'currentInspectionsCsvData']);
    Route::post('past_inspection/get_data', [CSVDataController::class, 'pastInspectionsCsvData']);
    Route::post('done_inspection/get_data', [CSVDataController::class, 'completedInspectionsCsvData']);
    Route::post('current_inspection/email', [CSVDataController::class, 'currentInspectionsCsvDataEmail']);
    Route::post('past_inspection/email', [CSVDataController::class, 'pastInspectionsCsvDataEmail']);
    Route::post('done_inspection/email', [CSVDataController::class, 'completedInspectionsCsvDataEmail']);
});

Route::group(['prefix' => 'agency', 'middleware' => ['jwt', 'mail_server'], 'roles' => 'Agency'], function () {

    Route::delete('tenancy_delete/{id}', [TenancyController::class, 'deleteTenancyById']);
    Route::get('tenancy_edit_info/{id}', [TenancyController::class, 'getTenancyInfo']);
    Route::post('add_tenancy', [TenancyController::class, 'postAddTenancy']);
    Route::post('check_first_step', [TenancyController::class, 'createTenancyFirstStepCheck']);
    Route::post('check_second_step', [TenancyController::class, 'createTenancySecondStepCheck']);
    Route::post('tenancy/generate_pdf', [CSVDataController::class, 'tenancyCsvDataEmail']);
});

Route::get('agency/make_renew/{id}', [TenancyController::class, 'renewStatus']);

