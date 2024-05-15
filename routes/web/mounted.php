<?php

use App\Http\Controllers\SingleApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt','mail_server'], 'roles' => 'Agency'], function () {
    Route::get('initial_api', [SingleApiController::class, 'getInitialInfoForAgency']); // Get all the permission and role of agency staff member
    Route::get('staff/initial_api', [SingleApiController::class, 'getInitialInfoForStaff']); // Get all the permission and role of agency staff member
});
