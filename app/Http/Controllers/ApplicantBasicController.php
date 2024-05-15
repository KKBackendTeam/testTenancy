<?php

namespace App\Http\Controllers;

use App\Models\DefaultDocuments;
use App\Mail\PasswordReset;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\ReferencesAddressesTrait;
use App\Traits\TextForSpecificAreaTrait;
use App\Traits\RunTimeEmailConfigrationTrait;
use App\Traits\WorkWithFile;
use App\Traits\ConverFileToBase64;
use App\Models\Agency;
use Illuminate\Support\Str;
use App\Http\Requests\Applicant\ForgotPasswordRequest as AppForgotPasswordRequest;
use App\Models\Applicantbasic;
use App\Models\Applicant;

class ApplicantBasicController extends Controller
{
    use ReferencesAddressesTrait, TextForSpecificAreaTrait, RunTimeEmailConfigrationTrait, WorkWithFile;
    use ConverFileToBase64;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function applicantDashboard()
    {
        $applicantData = Applicantbasic::where('id', authUserId())->first();
        $applicant = Applicant::where('applicant_id', $applicantData->id)->first();

        $wholeInformation = Tenancy::whereIn('id', $applicantData->applicants()->pluck('tenancy_id'))
            ->with('agencies:id,name,email,phone,opening_time,closing_time,address')
            ->with('applicants.employmentReferences:id,agency_id,applicant_id,company_name,company_email,country_code,company_phone,company_address,addresses,job_title,probation_period,contract_type,annual_salary,annual_bonus,name,position,signature,fill_date,fill_status,status,agency_status,notes,response_status,response_value,last_response_time,timezone,decision_text,notes_text,addresses_text,tenancy_id,reference_action')
            ->with('applicants.guarantorReferences:id,agency_id,applicant_id,name,email,country_code,phone,post_code,street,town,country,addresses,owner,relationship,occupation,employment_status,company_name,company_address,hr_email,least_income,address_proof,id_proof,financial_proof,signature,fill_date,fill_status,status,agency_status,notes,response_status,response_value,last_response_time,is_living_uk,timezone,is_eighteen,decision_income_text,decision_id_text,decision_address_text,notes_text,addresses_text,guarantor_income,created_at,updated_at,tenancy_id,decision_income_action,decision_id_action,decision_address_action')
            ->with('applicants.landlordReferences:id,name,applicant_id,post_code,street,town,country,addresses,email,country_code,phone,rent_price,paid_status,frequent_status,arrears_status,paid_arrears,damage_status,damage_detail,t_s_date,t_e_date,moveout_status,tenant_status,why_not,company_name,position,signature,fill_date,fill_status,status,agency_status,notes,response_status,response_value,last_response_time,timezone,decision_text,notes_text,addresses_text,rent_price_value,paid_arrears_value,created_at,updated_at,tenancy_id,reference_action')
            ->with('applicants.quarterlyReferences')
            ->with('applicants.applicantbasic')
            ->with('applicants:id,tenancy_id,applicant_id,agency_id,creator_id,country_code,doc_type,selfie_pic,signature,agreement_signature,type,log_status,ref_status,ref_agency_status,agreement,status,ta_status,review_status,response_status,response_value,last_response_time,notes,ip_address,is_complete,signing_time,addresses_text,addresses,total_references,fill_references,reference_tracker,review_agreement,notes_text,right_to_rent,passport_document,selfie_passport_document,step,created_at,updated_at,renew_status')
            ->with('properties')
            ->with(['tenancyHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->latest()->get();

        $agencyData = agencyDataFormId($applicantData->agency_id);

        return response()
            ->json([
                'saved' => true,
                'agency' => $agencyData,
                'applicant' => $applicantData,
                'app' => $wholeInformation,
                'documents' => DefaultDocuments::where('agency_id', $applicantData->agency_id)->get(),
                'text_for_specific_area' => $this->textForSpecificArea('ADPS', $applicant, null, $agencyData, null, null, null, null, null),
            ]);
    }

    /**
     * Handle forgot password request.
     *
     * @param  \App\Http\Requests\AppForgotPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(AppForgotPasswordRequest $request)
    {
        $user = Applicantbasic::where('email', strtolower($request->get('email')))->first();

        if (!empty($user)) {

            $user->update(['password_link' => config('global.frontSiteUrl') . ("/applicant/forgot_password/" . Str::random(12))]);

            $this->runTimeEmailConfiguration($user->agency_id);

            $user['name'] = $user['app_name'];
            $agencyData = Agency::where('id', $user->agency_id)->firstOrFail();
            $data = $this->emailTemplateData('PRE', $user, null, $agencyData, $user, null, null, null, null, null, null);
            Mail::to($user->email)->send(new PasswordReset($data, $agencyData, $user));

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Check if the password reset token is valid.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPasswordForm($token)
    {
        if (Applicantbasic::where('password_link', config('global.frontSiteUrl') . ("/applicant/forgot_password/" . $token))->first()) {
            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Reset user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetYourPassword(Request $request)
    {
        if ($user = Applicantbasic::where('password_link', config('global.frontSiteUrl') . ("/applicant/forgot_password/" . $request->get('code')))->first()) {

            $validator = validator($request->all(), [
                'password' => 'required|min:6',
                'password_confirmation' => 'required_with:new_password|same:password|min:6'
            ]);

            if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

            $user->password = bcrypt($request->get('password'));
            $user->password_link = null;
            $user->save();

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }


    public function downloadTheDefaultDocument($id)
    {
        return response()->download(storage_path('app/public/agency/default_documents/' . DefaultDocuments::where('agency_id', authAgencyId())->where('id', $id)->value('doc')));
    }

    /**
     * Update personal information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdatePersonalInfo(Request $request)
    {
        $validator = validator(
            $request['applicantPersonalInfo'],
            [
                'app_name' => 'required',
                'l_name' => 'required',
                'email' => 'required|email',
                'app_mobile' => 'required|numeric|digits_between:10,12',
            ]
        );

        if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

        $authUserId = authUserId();

        $app_email = Applicantbasic::where('email', strtolower($request['applicantPersonalInfo']['email']))->first();
        $app_info = Applicantbasic::where('id', $authUserId)->first();

        if (!is_null($app_email)) {
            if ($app_email->id == $authUserId) {
                $app_info->email = strtolower($request['applicantPersonalInfo']['email']);
            } else {
                $validator->getMessageBag()->add('email', 'This Email already exist.');
                return response()->json(['saved' => false, 'errors' => $validator->errors()]);
            }
        } else {
            $app_info->email = strtolower($request['applicantPersonalInfo']['email']);
        }

        $app_info->app_name = $request['applicantPersonalInfo']['app_name'];
        if (isset($request['applicantPersonalInfo']['m_name']) && !is_null($request['applicantPersonalInfo']['m_name'])) $app_info->m_name = $request['applicantPersonalInfo']['m_name'];
        $app_info->l_name = $request['applicantPersonalInfo']['l_name'];
        $app_info->app_mobile = $request['applicantPersonalInfo']['app_mobile'];
        $app_info->save();

        return response()->json(['saved' => true]);
    }

    /**
     * Change user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postChangePassword(Request $request)
    {
        $validator = validator($request['applicantPassword'], [
            'old_password' => 'required|min:6',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required_with:new_password|same:new_password|min:6'
        ]);

        if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

        $authUserData = authUser();
        if (JWTAuth::attempt(['email' => strtolower($authUserData->email), 'password' => $request['applicantPassword']['old_password']])) {
            Applicantbasic::where('id',  $authUserData->id)->update(['password' => bcrypt($request['applicantPassword']['new_password'])]);
            return response()->json(['saved' => true]);
        } else {
            $validator->getMessageBag()->add('old_password', 'Your old password is wrong!.');
            return response()->json(['saved' => false, 'errors' => $validator->errors()]);
        }
    }

    /**
     * Update user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateProfile(Request $request)
    {
        $validator = validator($request['applicantProfile'], ['selfie_pic' => 'required|only_image']);

        if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

        $applicant = Applicantbasic::where('id', authUserId())->first();

        if (!empty($request['applicantProfile']['selfie_pic'])) {
            $image_name = $this->file_upload($request['applicantProfile']['selfie_pic'], "app_selfie_pic", null);
            if ($image_name == 'virus_file') {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The avatar is a virus file'
                ]);
            } else {
                $this->deleteFile('app_selfie_pic', $applicant->selfie_pic);
                $applicant->selfie_pic = $image_name;
            }

            $applicant->save();
        }
        return response()->json(['saved' => true]);
    }

    /**
     * Get certificate.
     *
     * @param  string  $type
     * @param  string  $fileName
     * @return mixed
     */
    public function getCertificate($type, $fileName)
    {
        return $this->base64Converter($type, $fileName);
    }
}
