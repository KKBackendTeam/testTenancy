<?php

namespace App\Http\Controllers;

use App\Traits\AllPermissions;

class SingleApiController extends Controller
{
    use AllPermissions;

    public $agencyInfo, $staffInfo, $staffPermission;

    /**
     * Retrieve initial information for agency.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing role status and agency information.
     */
    public function getInitialInfoForAgency()
    {
        if (agencyAdmin()) {
            $this->agencyInfo = authUser();
        }

        return response()->json(['role' => $this->agencyInfo->roleStatus, 'agencyInfo' => $this->agencyInfo]);
    }

    /**
     * Retrieve initial information for staff.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing role status, staff information, and permissions.
     */
    public function getInitialInfoForStaff()
    {
        if (agencyStaff()) {
            $this->staffInfo = authUser();
            $this->staffPermission = authUser()->getAllPermissions();
        }

        return response()->json(['role' => $this->staffInfo->roleStatus, 'staffInfo' => $this->staffInfo, 'permission' => $this->staffPermission]);
    }
}
