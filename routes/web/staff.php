<?php

use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {

    Route::get('staff', [StaffController::class, 'getAllStaffMembers']);     // get all the staff agency staff member
    Route::get('staff_info/{id}', [StaffController::class, 'getSingleStaff']);    // get single staff member via id
    Route::post('staff/get_data', [CSVDataController::class, 'getStaffCsvData']);   // get all in CSV format
    Route::post('staff/generate_csv', [CSVDataController::class, 'staffCsvDataEmail']);
});

Route::group(['prefix' => 'agency', 'middleware' => ['jwt', 'mail_server'], 'roles' => 'Agency'], function () {

    Route::post('new_staff', [StaffController::class, 'postCreateNewStaff']);   // create new staff member in the agency
    Route::post('staff_update', [StaffController::class, 'patchStaffInfoUpdate']);   // update staff information
    Route::post('staff_permission', [StaffController::class, 'postStaffPermission']);  // give permission to the staff member
    Route::post('staff_delete', [StaffController::class, 'deleteStaffMember']);   // delete the staff member
});
