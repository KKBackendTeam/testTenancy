<?php

use App\Http\Controllers\RequirementController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {   // protect with agency and staff

    Route::get('landlord_requirement', [RequirementController::class, 'getLandlordRequirement']);
    Route::post('landlord_requirement', [RequirementController::class, 'postLandlordRequirement']);
    Route::get('employment_requirement', [RequirementController::class, 'getEmploymentRequirement']);
    Route::post('employment_requirement', [RequirementController::class, 'postEmploymentRequirement']);
    Route::get('guarantor_requirement', [RequirementController::class, 'getGuarantorRequirement']);
    Route::post('guarantor_requirement', [RequirementController::class, 'postGuarantorRequirement']);
    Route::get('quarterly_requirement', [RequirementController::class, 'getQuarterlyRequirement']);
    Route::post('quarterly_requirement', [RequirementController::class, 'postQuarterlyRequirement']);
    Route::get('tenancy_requirement', [RequirementController::class, 'getTenancyRequirement']);
    Route::post('tenancy_requirement', [RequirementController::class, 'postTenancyRequirement']);
    Route::get('applicant_requirement', [RequirementController::class, 'getApplicantRequirement']);
    Route::post('applicant_requirement', [RequirementController::class, 'postApplicantRequirement']);
    Route::get('requirements', [RequirementController::class, 'getRequirements']);
});
