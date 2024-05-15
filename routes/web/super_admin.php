<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CSVDataController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => ['jwt', 'admin'], 'onlyFor' => 'SuperAdmin'], function () {

    Route::get('tenancies', [SuperAdminController::class, 'getTenancies']);
    Route::get('agencies', [SuperAdminController::class, 'getAgencies']);
    Route::get('dashboard', [SuperAdminController::class, 'adminDashboard']);
    Route::post('agency/get_data', [CSVDataController::class, 'getAgencyCsvData']);
    Route::post('generate-pdf', [CSVDataController::class, 'agencyCsvDataEmail']);
    Route::get('agency/{id}', [SuperAdminController::class, 'getAgency']);
    Route::post('statistics', [StatisticsController::class, 'superAdminStatistics']);
    Route::post('property/statistics', [StatisticsController::class, 'averageProperty']);
    Route::post('property/bedroom', [StatisticsController::class, 'propertyBedroom']);
    Route::get('default_mail_template', [SuperAdminController::class, 'getEmailTemplate']);
    Route::get('default_text_for_specific_area', [SuperAdminController::class, 'getTextForSpecificArea']);
    Route::post('default_mail_template', [SuperAdminController::class, 'postEmailTemplate']);
    Route::post('default_text_for_specific_area', [SuperAdminController::class, 'postTextForSpecificArea']);
});

Route::group(['prefix' => 'admin', 'middleware' => ['jwt', 'admin', 'admin_mailServer'], 'onlyFor' => 'SuperAdmin'], function () {

    Route::post('add_agency', [SuperAdminController::class, 'postAddAgency']);
    Route::post('edit_agency', [SuperAdminController::class, 'postEditAgency']);
    Route::post('edit_agency_profile', [SuperAdminController::class, 'postUpdateAgencyProfile']);
    Route::delete('delete_agency/{id}', [SuperAdminController::class, 'postDeleteAgency']);
    Route::post('add_credit', [SuperAdminController::class, 'postAddCreditToAgency']);
});



