<?php

namespace App\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;

trait AllPermissions
{
    public function createLandlord()
    {
        return JWTAuth::parseToken()->authenticate()->can('create landlord');
    }

    public function editLandlord()
    {
        return JWTAuth::parseToken()->authenticate()->can('edit landlord');
    }

    public function deleteLandlord()
    {
        return JWTAuth::parseToken()->authenticate()->can('delete landlord');
    }

    public function createProperty()
    {
        return JWTAuth::parseToken()->authenticate()->can('create property');
    }

    public function editProperty()
    {
        return JWTAuth::parseToken()->authenticate()->can('edit property');
    }

    public function deleteProperty()
    {
        return JWTAuth::parseToken()->authenticate()->can('delete property');
    }

    public function createTenancy()
    {
        return JWTAuth::parseToken()->authenticate()->can('create tenancy');
    }

    public function editTenancy()
    {
        return JWTAuth::parseToken()->authenticate()->can('edit tenancy');
    }

    public function deleteTenancy()
    {
        return JWTAuth::parseToken()->authenticate()->can('delete tenancy');
    }

    public function editApplicant()
    {
        return JWTAuth::parseToken()->authenticate()->can('edit applicant');
    }

    public function deleteApplicant()
    {
        return JWTAuth::parseToken()->authenticate()->can('delete applicant');
    }

    public function reviewTenancy()
    {
        return JWTAuth::parseToken()->authenticate()->can('review tenancy');
    }

    public function tenancyNegotiator()
    {
        return JWTAuth::parseToken()->authenticate()->can('tenancy negotiator');
    }

    public function manuallyTenancyStatus()
    {
        return JWTAuth::parseToken()->authenticate()->can('manually tenancy status');
    }

    public function manuallyPropertyStatus()
    {
        return JWTAuth::parseToken()->authenticate()->can('manually property status');
    }

    public function configurationEdit()
    {
        return JWTAuth::parseToken()->authenticate()->can('configuration edit');
    }

    public function accessCustomization()
    {
        return JWTAuth::parseToken()->authenticate()->can('access customisation');
    }

    public function accessConfiguration()
    {
        return JWTAuth::parseToken()->authenticate()->can('access configuration');
    }

    public function createOrEditStaffUser()
    {
        return JWTAuth::parseToken()->authenticate()->can('create edit staff user');
    }
}
