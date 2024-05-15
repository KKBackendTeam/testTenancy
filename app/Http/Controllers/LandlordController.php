<?php

namespace App\Http\Controllers;

use App\Models\Landloard;
use Illuminate\Http\Request;
use App\Traits\AllPermissions, App\Traits\LastStaffActionTrait;
use App\Traits\SortingActionTrait;
use App\Traits\ConfigrationTrait;

class LandlordController extends Controller
{
    use AllPermissions, LastStaffActionTrait, SortingActionTrait, ConfigrationTrait;

    /**
     * Display the specified resource.
     *
     * @param  \App\Landloard  $id
     * @return \Illuminate\Http\Response
     */
    public function viewLandlordById($id)
    {
        $landlord_info = Landloard::where('agency_id', authAgencyId())->where('id', $id)->first();
        (agencyAdmin() || $this->editLandlord()) ? $show = 1 : $show = 0;

        return response()->json(['landlord_info' => $landlord_info, 'show' => $show]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function createLandlordHelper($request)
    {
        if ($request['landlordData']['status'] == "new") {
            $new_landlords = new Landloard(['agency_id' => authAgencyId()]);
        } else {
            $new_landlords = Landloard::where('id', $request['landlordData']['landlord_id'])->first();
        }
        $new_landlords->f_name = $request['landlordData']['f_name'];
        $new_landlords->l_name = $request['landlordData']['l_name'];
        $new_landlords->display_name = $request['landlordData']['display_name'];
        $new_landlords->post_code = $request['landlordData']['post_code'];
        $new_landlords->street = $request['landlordData']['street'];
        $new_landlords->town = $request['landlordData']['town'];
        $new_landlords->country_code = $request['landlordData']['country_code'];
        $new_landlords->country = $request['landlordData']['country'];
        $new_landlords->mobile = $request['landlordData']['mobile'];
        $new_landlords->email = strtolower($request['landlordData']['email']);
        $new_landlords->creator_id = authUserId();

        $new_landlords->save();

        return response()->json(['saved' => true]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function postCreateLandlord(Request $request)
    {
        $rules = [
            'f_name' => 'required',
            'l_name' => 'required',
            'street' => 'required',
            'town' => 'required',
            'country' => 'required',
            'mobile' => 'required',
        ];

        $rules +=  $request['landlordData']["status"] == "new" ? ['email' => 'required|email|unique:landloards'] : ['email' => 'required|email'];
        $validator = validator($request['landlordData'], $rules);

        if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

        if ($request['landlordData']['status'] == "new" && Landloard::whereRaw("lower(concat(f_name,' ',l_name)) like ?", ['%' . (strtolower($request['landlordData']['f_name']) . ' ' . strtolower($request['landlordData']['l_name'])) . '%'])->first() && $request['landlordData']['continue'] == 0) {
            return response()->json(['saved' => false, 'statusCode' => 409, 'reason' => 'Landlord name have already exist']);
        };
        $per = false;
        if ($request['landlordData']['status'] == "new" && $this->createLandlord()) {
            $per = true;
        }
        if ($request['landlordData']['status'] == "edit" && $this->editLandlord()) {
            $per = true;
        }

        if (agencyAdmin() || $per) {
            if ($request['landlordData']['status'] == "new") {

                $email_validate = validator($request['landlordData'], [
                    'email' => 'required|email|unique:landloards',
                ]);

                if ($email_validate->fails()) {
                    return response()->json(['saved' => false, 'errors' => $email_validate->errors()]);
                }

                $this->createLandlordHelper($request);
                $this->lastStaffAction('Create new landlord');
                return response()->json(['saved' => true]);
            } else {

                $land = Landloard::where('agency_id', $request['landlordData']['agency_id'])->find($request['landlordData']['landlord_id']);
                if ($land->email == strtolower($request['landlordData']['email'])) {
                    $this->createLandlordHelper($request);
                } else {
                    $email_validate = validator($request['landlordData'], [
                        'email' => 'required|email|unique:landloards',
                    ]);
                    if ($email_validate->fails()) {
                        return response()->json(['saved' => false, 'errors' => $email_validate->errors()]);
                    }
                    $this->createLandlordHelper($request);
                }
                $this->lastStaffAction('Edit landlord');
                return response()->json(['saved' => true]);
            }
        } else {
            return response()->json(['saved' => false, 'errors' => $validator->errors()]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getLandlordsInfo()
    {
        return response()->json(['landlord_info' => Landloard::where('agency_id', authAgencyId())->latest()->get()]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getAllLandlords()
    {
        $all_landlord = Landloard::where('agency_id', authAgencyId())->get();

        foreach ($all_landlord as $i => $landlord) {
            $all_landlord[$i]->total = $landlord->properties()->count();
            $all_landlord[$i]->available = $landlord->properties()->whereIn('status', [1, 3])->count();
            $all_landlord[$i]->processing = $landlord->properties()->where('status', 4)->count();
            $all_landlord[$i]->let = $landlord->properties()->where('status', 5)->count();
        }

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $all_landlord = $all_landlord->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingLandlordVariables[request('sort_by')]) ? $this->sortingLandlordVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
        $data = $all_landlord->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'landlords' => ['data' => $data, 'total' => $all_landlord->count()], 'financial_configuration' => $this->financialConfiguration()]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Landloard  $id
     * @return \Illuminate\Http\Response
    */
    public function deleteLandlordById($id)
    {
        if (agencyAdmin() && ($landlord = Landloard::where('agency_id', authAgencyId())->where('id', $id)->first())) {

            $landlord->delete();
            $this->lastStaffAction('Delete landlord');
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }
}
