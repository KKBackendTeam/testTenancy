<?php

namespace App\Http\Controllers;

use App\Mail\AgencyCreateNewStaffEmail;
use Illuminate\Http\Request;
use App\Models\User;
use Mail;
use App\Traits\AllPermissions, App\Traits\LastStaffActionTrait, App\Traits\TextForSpecificAreaTrait;
use App\Http\Requests\Staff\DeleteStaffMemberRequest;
use App\Traits\SortingActionTrait;

class StaffController extends Controller
{
    use AllPermissions, LastStaffActionTrait, TextForSpecificAreaTrait, SortingActionTrait;

    public function getAllStaffMembers()
    {
        $staff = User::where('agency_id', authAgencyId())
            ->where('roleStatus', 0)
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingStaffVariables[request('sort_by')]) ? $this->sortingStaffVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $staff->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'staff_member' => ['data' => $data, 'total' => $staff->count()]]);
    }

    public function getSingleStaff($id)
    {
        $authAgencyId = authAgencyId();
        if ((!empty($staff = User::where('id', $id)->where('agency_id', $authAgencyId)->first())) && (!empty($s_p = User::where('id', $id)->where('agency_id', $authAgencyId)->first()))) {
            $res = ['saved' => true, 'staff' => $staff, 'permission' => $s_p->getAllPermissions()];
        } else {
            $res = ['saved' => false];
        }
        return response()->json($res);
    }

    public function patchStaffInfoUpdate(Request $request)
    {
        $validator = validator($request['staffInfo'], [
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email',

        ]);

        if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

        if (!empty($edit_staff = User::where('id', (int) $request['staffInfo']['s_id'])->where('agency_id', authAgencyId())->first())) {

            if ($edit_staff->email == strtolower($request['staffInfo']['email'])) {
                $this->updateStaffMemberHelper($edit_staff, $request);
            } else {
                $email_validate = validator($request['staffInfo'], [
                    'email' => 'required|email|unique:users',
                ]);
                if ($email_validate->fails()) {
                    return response()
                        ->json(['saved' => false, 'errors' => $email_validate->errors()]);
                }
                $this->updateStaffMemberHelper($edit_staff, $request);
            }

            $this->lastStaffAction('Edit staff member information');
            $res = ['saved' => true];
        } else {
            $res = ['saved' => false];
        }
        return response()->json($res);
    }

    public function updateStaffMemberHelper($edit_staff, $request)
    {
        $edit_staff->name = $request['staffInfo']['fname'];
        $edit_staff->l_name = $request['staffInfo']['lname'];
        $edit_staff->email = strtolower($request['staffInfo']['email']);
        $edit_staff->is_active = $request['staffInfo']['is_active'];
        $edit_staff->save();
        return true;
    }

    public function postStaffPermission(Request $request)
    {
        if (!empty($staff_info = User::where('id', $request['staffPermission']['s_id'])->where('agency_id', authAgencyId())->first())) {

            $this->createThePermission($request, $staff_info);
            $this->lastStaffAction('Edit staff member permission');

            $res = ['saved' => true];
        } else {
            $res = ['saved' => false];
        }
        return response()->json($res);
    }

    public function postCreateNewStaff(Request $request)
    {
        $validator = validator($request['staffData'], [
            'email' => 'required|email|unique:users',
            'f_name' => 'required',
            'l_name' => 'required',
            'password' => 'required|same:confirmation_password'
        ]);

        if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

        if (agencyAdmin() || $this->createOrEditStaffUser()) {

            $user = new User(['email_status' => 1, 'roleStatus' => 0, 'agency_id' => authAgencyId(), 'staff_status' => 1]);
            $user->name = $request['staffData']['f_name'];
            $user->l_name = $request['staffData']['l_name'];
            $user->email = strtolower($request['staffData']['email']);
            $user->password = bcrypt($request['staffData']['password']);
            $user->is_active = $request['staffData']['is_active'];
            $user->last_action = 'No action';
            $user->last_action_date = now();
            $user->save();

            $user->password =  $request['staffData']['password'];

            $agencyData = agencyData();
            $data = $this->emailTemplateData('SWE', null, null, $agencyData, $user, null, null, null, null, null, null);
            Mail::to($user['email'])->send(new AgencyCreateNewStaffEmail($data, $agencyData));

            $this->createThePermission($request, $user);
            $this->lastStaffAction('Create new staff member');
            $res = ['saved' => true];
        } else {
            $res = ['saved' => false, 'errors' => $validator->errors()];
        }
        return response()->json($res);
    }

    public function deleteStaffMember(DeleteStaffMemberRequest $request)
    {
        $user = User::where('agency_id', authAgencyId())->where('id', $request['id'])->first();

        if (agencyAdmin() && ($user) && $user->roleStatus != 1) {

            $user->applicants()->update(['creator_id' => $request['assignee_id']]);
            $user->tenancies()->update(['creator_id' => $request['assignee_id']]);
            $user->properties()->update(['creator_id' => $request['assignee_id']]);
            $user->landloards()->update(['creator_id' => $request['assignee_id']]);

            $user->delete();
            $this->lastStaffAction('Delete staff member');
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    public function activateDeactivateStaff($id, $what)
    {
        if (agencyAdmin() && !empty($user = User::where('id', $id)->where('agency_id', authAgencyId())->first())) {
            $user->staff_status = $what;
            $user->save();
            $this->lastStaffAction('Active/deactive staff member');
            $res = ['saved' => true];
        } else {
            $res = ['saved' => false];
        }
        return response()->json($res);
    }

    public function createThePermission($request, $staff)
    {
        $perm = array();
        $staff->revokePermissionTo([
            'create landlord', 'edit landlord',
            'delete landlord', 'create property', 'edit property', 'delete property', 'create tenancy',
            'edit tenancy', 'delete tenancy', 'edit applicant', 'delete applicant', 'review tenancy', 'tenancy negotiator',
            'manually tenancy status', 'manually property status', 'configuration edit', 'access customisation', 'access configuration', 'create edit staff user'
        ]);

        if ($request['staffPermission']['add_landlord'] > 0) {
            $perm[] = 1;
        }
        if ($request['staffPermission']['edit_landlord'] > 0) {
            $perm[] = 2;
        }
        if ($request['staffPermission']['del_landlord'] > 0) {
            $perm[] = 3;
        }
        if ($request['staffPermission']['add_property'] > 0) {
            $perm[] = 4;
        }
        if ($request['staffPermission']['edit_property'] > 0) {
            $perm[] = 5;
        }
        if ($request['staffPermission']['del_property'] > 0) {
            $perm[] = 6;
        }
        if ($request['staffPermission']['add_tenancy'] > 0) {
            $perm[] = 7;
        }
        if ($request['staffPermission']['edit_tenancy'] > 0) {
            $perm[] = 8;
        }
        if ($request['staffPermission']['del_tenancy'] > 0) {
            $perm[] = 9;
        }
        if ($request['staffPermission']['edit_applicant'] > 0) {
            $perm[] = 10;
        }
        if ($request['staffPermission']['del_applicant'] > 0) {
            $perm[] = 11;
        }
        if ($request['staffPermission']['review_tenancy'] > 0) {
            $perm[] = 12;
        }
        if ($request['staffPermission']['tenancy_negotiator'] > 0) {
            $perm[] = 13;
        }
        if ($request['staffPermission']['tenancy_status'] > 0) {
            $perm[] = 14;
        }
        if ($request['staffPermission']['property_status'] > 0) {
            $perm[] = 15;
        }
        if ($request['staffPermission']['docs_configuration_editing'] > 0) {
            $perm[] = 16;
        }
        if ($request['staffPermission']['access_agency_customisation'] > 0) {
            $perm[] = 17;
        }
        if ($request['staffPermission']['access_agency_configuration'] > 0) {
            $perm[] = 18;
        }
        if ($request['staffPermission']['create_or_edit_staff_user'] > 0) {
            $perm[] = 19;
        }

        $staff->givePermissionTo($perm);
        return true;
    }
}
