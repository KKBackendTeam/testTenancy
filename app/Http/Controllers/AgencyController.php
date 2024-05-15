<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Landloard;
use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\ContactUsMail;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile;
use App\Traits\LastStaffActionTrait;
use App\Traits\ConverFileToBase64;
use App\Http\Requests\Staff\IsActiveStaffRequest;
use App\Traits\SortingActionTrait;
use Illuminate\Database\Eloquent\Collection;

class AgencyController extends Controller
{
    use AllPermissions, WorkWithFile, LastStaffActionTrait, ConverFileToBase64, SortingActionTrait;

    public function __construct()
    {
        $this->middleware('jwt')->except('authorizeAgency');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Agency  $token
     * @return \Illuminate\Http\Response
     */
    public function authorizeAgency($token)
    {
        if ($agency = Agency::where('agency_confirm_link', config('global.frontSiteUrl') . ("/agency_link/" . $token))->firstOrFail()) {
            $agency->update(['status' => 1, 'agency_confirm_link' => null]);
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBasicInformation()
    {
        $agency = Agency::where('id', authAgencyId())->firstOrFail();
        $remainingCredit = $agency->total_credit - $agency->used_credit;

        if ($remainingCredit > 20 && $agency->mailServer()->exists()) {
            return response()->json(['saved' => false]);
        }
        $remainingCredit = ($remainingCredit > 20) ? 21 : $remainingCredit;
        $emailServerSetting = $agency->mailServer()->exists() ? 1 : 0;

        return response()->json(['saved' => true, 'remaining_credit' => $remainingCredit, 'email_server_setting' => $emailServerSetting]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAgencyInfo()
    {
        return response()->json(['saved' => true, 'agency_info' => Agency::where('id', authAgencyId())->firstOrFail()]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllTheAgencyMembers()
    {
        return response()->json(["saved" => true, "agencyMembers" => User::where('agency_id', authAgencyId())->latest()->get(['id', 'name', 'l_name'])]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStaffDetail($id)
    {
        return response()->json(['saved' => true, "staff_info" => User::where('id', $id)->where('agency_id', authAgencyId())->first()]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStaffDetailWithPermission($id)
    {
        $staff_info = User::where('id', $id)->where('agency_id', authAgencyId())->first();
        $permission = $staff_info->getAllPermissions();
        return response()->json(['staff_info' => $staff_info, 'permission' => $permission]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\IsActiveStaffRequest  $request
     * @param  \App\User  $id
     * @return \Illuminate\Http\Response
     */
    public function isActiveStaffMember(IsActiveStaffRequest $request)
    {
        if (agencyAdmin()) {
            User::where('agency_id', authAgencyId())->where('id', $request['staff_id'])->firstOrFail()
                ->update(['is_active' => $request['is_active']]);
            $this->lastStaffAction('Activate/deactivate staff member');
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Tenancy $id
     * @return \Illuminate\Http\Response
     */
    public function tenancyViewFromId($id)
    {
        return response()
            ->json([
                "tenancy_info" => $tenancy_info = Tenancy::where('id', $id)->first(),
                "landlord_name" => Landloard::where('id', $tenancy_info->landlord_id)->value('name'),
                "applicant_info" => Applicant::where('tenancy_id', $tenancy_info->tenancy_id)->latest()->get()
            ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllApplicants()
    {
        $applicants = Applicant::where('agency_id', authAgencyId())
            ->with('tenancies:id,reference,pro_address')
            ->with('applicantbasic')
            ->with('users:id,name,l_name')
            ->get();

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        if (request('sort_by')  == 'status') {
            $i = 0;
            $new_collection = new Collection();
            $sortAccordingArray = request('sort_action') == 'desc' ? $this->applicantDescStatusArray : $this->applicantAscStatusArray;
            while ($i < $this->applicantStatusArrayCount) {
                foreach ($applicants as $key => $ti) {
                    if ($ti->status ==  $sortAccordingArray[$i]) {
                        $new_collection->push($ti);
                        unset($applicants[$key]);
                    }
                }
                $i++;
            }
            $data = $new_collection->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
            $applicants = $new_collection;
        } else {
            $applicants = $applicants->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingApplicantVariables[request('sort_by')]) ? $this->sortingApplicantVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
            $data = $applicants->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();
        }

        return response()->json(['saved' => true, 'applicants' => ['data' => $data, 'total' => $applicants->count()]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Applicant  $id
     * @return \Illuminate\Http\Response
     */
    public function postEditApplicant(Request $request)
    {
        $validation = validator($request['applicantData'], [
            'app_name' => 'required',
            'l_name' => 'required',
            'status' => 'required',
            'email' => 'required|email'
        ]);

        if ($validation->fails()) return response()->json(['saved' => false, 'errors' => $validation->errors()]);

        if ((agencyAdmin() || $this->editApplicant()) &&
            !empty($new_applicant = Applicant::where('id', $request['applicantData']['applicant_id'])
                ->where('agency_id', authAgencyId())->first()->applicantbasic)
        ) {

            $applicantData = [
                'app_name' => $request['applicantData']['app_name'],
                'm_name' => isset($request['applicantData']['m_name']) ? $request['applicantData']['m_name'] : '',
                'l_name' => $request['applicantData']['l_name'],
                'status' => $request['applicantData']['status'],
                'email' => strtolower($request['applicantData']['email']),
                'app_mobile' => $request['applicantData']['app_mobile']
            ];

            unset($applicantData['status']);

            if ($new_applicant->email == strtolower($request['applicantData']['email'])) {

                $new_applicant->update($applicantData);
                return response()->json(['save' => true]);
            } else {

                $email_validate = validator($request['applicantData'], ['email' => 'required|email|unique:applicantbasics']);
                if ($email_validate->fails()) return response()->json(['saved' => false, 'errors' => $email_validate->errors()]);
                $new_applicant->update($applicantData);

                $this->lastStaffAction('Edit applicant information');
                return response()->json(['saved' => true]);
            }
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Applicant  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteApplicantById($id)
    {
        if ((agencyAdmin() || $this->deleteApplicant()) && ($applicant = Applicant::where('agency_id', authAgencyId())->where('id', $id)->first())) {
            $applicant->tenancies()->decrement('no_applicant');
            $this->deleteSingleApplicant($applicant);
            //$applicant->delete();

            $this->lastStaffAction('Delete an applicant');
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Applicant  $id
     * @return \Illuminate\Http\Response
     */
    public function getApplicantInfoById($id)
    {
        if ($applicant = Applicant::where('id', $id)->where('agency_id', authAgencyId())->with('applicantbasic')->first()) {
            return response()->json(['saved' => true, 'app_info' => $applicant]);
        } else {
            return response()->json(['save' => false]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCertificate($type, $fileName)
    {
        return $this->base64Converter($type, $fileName);
    }


    /**
     * Handles the request to help and contact us.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function helpAndContactUs(Request $request)
    {
        $superAdmin = Agency::where('status', 2)->first();
        $superAdminEmail = $superAdmin['email'];
        $agencyData = agencyData();
        $data = $request->all();

        Mail::to($superAdminEmail)->send(new ContactUsMail($data, $agencyData));
        return response()->json(['saved' => true]);
    }
}
