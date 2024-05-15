<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\PersonalInfoRequest;
use App\Http\Requests\Profile\SelfieRequest;
use App\Http\Requests\Profile\MediaLogoRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile, App\Traits\LastStaffActionTrait;
use App\Models\Agency;
use Illuminate\Auth\Access\AuthorizationException;


class ProfileController extends Controller
{
    use AllPermissions, WorkWithFile, LastStaffActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProfileInfo()
    {
        $agency = Agency::where('id', authUserData()->agency_id)->first();
        return response()->json(['saved' => true, 'info' => authUserData(), 'agency' => $agency]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\SelfieRequest  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
    */
    public function postUpdateProfile(SelfieRequest $request)
    {
        $user = User::where('id', authUserId())->firstOrFail();

        if (!empty($request['selfie_pic'])) {

            $image_name = $this->file_upload($request['selfie_pic'], "selfie_pic", null);
            if ($image_name == 'virus_file') {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The avatar is a virus file'
                ]);
            } else {
                $this->deleteFile('selfie_pic', $user->selfie_pic);
                $user->selfie_pic = $image_name;
            }
        }

        $user->save();
        $this->lastStaffAction('Edit new avatar');
        return response()->json(['saved' => true, 'user_info' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\MediaLogoRequest  $request
     * @param  \App\Agency  $agency
     * @return \Illuminate\Http\Response
    */
    public function postUpdateMediaLogo(MediaLogoRequest $request)
    {
        $agent = Agency::where('id', $request->id)->firstOrFail();
        if (!empty($request['media_logo'])) {

            $image_name = $this->file_upload($request['media_logo'], "media_logo", null);
            if ($image_name == 'virus_file') {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The avatar is a virus file'
                ]);
            } else {
                $this->deleteFile('media_logo', $agent->media_logo);
                $agent->media_logo = $image_name;
            }
        }

        $agent->save();
        $this->lastStaffAction('Edit new avatar');
        return response()->json(['saved' => true, 'agent_info' => $agent]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\PersonalInfoRequest  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
    */
    public function postUpdatePersonalInfo(PersonalInfoRequest $request)
    {
        $user_email = User::where('email', strtolower($request['email']))->first();
        $user_info = authUserData();

        if (!is_null($user_email) && $user_email->id == authUserId()) {
            $user_info->email = strtolower($request['email']);
        } else {
            $rules = ['email' => 'required|string|email|unique:users'];
            $validator = validator($request->only('email'), $rules);
            if ($validator->fails()) {
                return response()->json(['saved' => false, 'errors' => $validator->errors()]);
            }
            $user_info->email = strtolower($request['email']);
        }

        $user_info->name = $request['name'];
        $user_info->l_name = $request['l_name'];
        $user_info->mobile = $request['mobile'];
        $user_info->country_code = $request['country_code'];
        $user_info->save();

        $this->lastStaffAction('Edit profile information');
        return response()->json(['saved' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ChangePasswordRequest  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
    */
    public function postChangePassword(ChangePasswordRequest $request)
    {
        if (JWTAuth::attempt(['email' => authUser()->email, 'password' => $request['old_password']])) {
            User::where('id', authUserId())->update(['password' => bcrypt($request['new_password'])]);
            $this->lastStaffAction('Change password');
            return response()->json(['saved' => true]);
        } else {
            $validator = validator($request->all(), []);
            $validator->getMessageBag()->add('old_password', 'Your old password is wrong');
            return response()->json(['saved' => false, 'errors' => $validator->errors()]);
        }
    }
}
