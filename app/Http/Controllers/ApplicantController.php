<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\Applicant;
use App\Models\ApplicantRequirement;
use App\Models\Chasing;
use App\Models\EmploymentReference;
use App\Models\GuarantorReference;
use App\Models\LandlordReference;
use App\Mail\EmploymentReferenceEmail;
use App\Mail\GuarantorReferenceEmail;
use App\Mail\LandlordReferenceEmail;
use App\Mail\WelcomeEmail;
use App\Models\QuarterlyReference;
use App\Models\Tenancy;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile;
use App\Traits\ConverFileToBase64;
use App\Traits\TextForSpecificAreaTrait;
use App\Traits\DeleteDuplicateReferencesTrait;
use App\Traits\RunTimeEmailConfigrationTrait;
use App\Traits\ReferencesAddressesTrait;
use App\Notifications\ApplicantPrivacyFormIncompleteFlagNotification;
use App\Http\Requests\Applicant\InitialLoginRequest;
use App\Models\Agency;
use App\Traits\PaymentScheduleTrait;
use Carbon\Carbon;
use App\Models\StudentReference;
use App\Traits\ApplicantTrait;
use App\Events\EmploymentAddDeleteEvent;
use App\Events\GuarantorAddDeleteEvent;
use App\Events\LandlordAddDeleteEvent;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Notifications\Agency\ApplicantPrivacyFormCompletedNotification;

class ApplicantController extends Controller
{
    use AllPermissions, WorkWithFile, ConverFileToBase64, TextForSpecificAreaTrait, DeleteDuplicateReferencesTrait;
    use RunTimeEmailConfigrationTrait, ReferencesAddressesTrait, ApplicantTrait, PaymentScheduleTrait;

    /**
     * Perform initial login check for the applicant.
     *
     * @param  \App\Http\Requests\InitialLoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postInitialLoginCheck(InitialLoginRequest $request)
    {
        $app_info = Applicant::where('app_url', config('global.frontSiteUrl') . ('/applicant/initial_login?email=' . $request->get('email') . '&code=' . $request->get('code')))
            ->with(['applicantbasic', 'employmentReferences', 'guarantorReferences', 'quarterlyReferences', 'landlordReferences', 'studentReferences'])
            ->first();
        if ($app_info !== null) {
            if ($app_info->tenancies->status == 10) {
                return response()->json(['saved' => false, 'statusCode' => 2323, 'reason' => 'Your tenancy application is cancelled']);
            }

            if ($app_info->log_status == 0) {
                return $this->fetchApplicantInformationHelperFunction($app_info);
            } else {
                return response()->json(['saved' => false, 'statusCode' => 404]);
            }
        } else {
            return response()->json(['saved' => false, 'statusCode' => 404]);
        }
    }

    /**
     * Retrieve and return saved applicant information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postApplicantSavedInformation(Request $request)
    {
        return $this->fetchApplicantInformationHelperFunction(Applicant::where('id', $request['applicantInfo']['applicant_id'])
            ->with(['applicantbasic', 'employmentReferences', 'guarantorReferences', 'quarterlyReferences', 'landlordReferences', 'studentReferences'])->firstOrFail());
    }

    /**
     * Get PDF of applicant information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getApplicantInfoPdf(Request $request)
    {
        $data = Applicant::where('id', $request->input('applicant_id'))
            ->with(['applicantbasic', 'employmentReferences', 'guarantorReferences', 'quarterlyReferences', 'landlordReferences', 'studentReferences', 'paymentSchedule'])
            ->firstOrFail();

        $tenancy_info = Tenancy::where('id', $data->tenancy_id)->first();
        $applicants = $tenancy_info->applicants()->with('applicantBasic')->get();
        $agency = agencyDataFormId($tenancy_info->agency_id);

        try {
            $pdf = PDF::loadView('Pdf.applicantInfo', [
                'agency' => $agency,
                'data' => $data,
                'tenancyInfo' => $tenancy_info,
                'applicants' => $applicants,
                'applicant_privacy_statement' => $this->textForSpecificArea('AILPS', $data, $tenancy_info, $agency, null, null, null, null, null),
            ]);

            $filename = 'document_' . uniqid() . '.pdf';
            $path = 'public/pdfs/' . $filename;

            Storage::put($path, $pdf->output());
            $url = config('global.backSiteUrl') . Storage::url($path);

            return Response::json(['pdf_url' => $url]);
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update applicant steps information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postApplicantStepsInformation(Request $request)
    {
        $requestData = $request->all();
        $step = $requestData['step'];
        if (in_array($step, [1, 3])) {
            $this->postApplicant134StepPrivacyInformation($request);
        } elseif ($step == 2) {
            $resp = $this->postApplicantSecondStepPrivacyInformation($request);
            if (isset($resp['virus_error']) && $resp['virus_error']) {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The ' . $resp['type'] . ' is a virus file'
                ]);
            }
        } elseif ($step == 4) {
            $this->postApplicantFourthStepPrivacyInformation($request);
        } else {
            $resp = $this->postApplicantFifthStepPrivacyInformation($request);
            if (isset($resp['virus_error']) && $resp['virus_error']) {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The ' . $resp['type'] . ' is a virus file'
                ]);
            }
        }

        return response()->json(['saved' => true]);
    }

    /**
     * Update the applicant's step to proceed to the next privacy information step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function postApplicant134StepPrivacyInformation(Request $request)
    {
        Applicant::where('id', $request['applicant_id'])->first()->update(['step' => ($request['step'] + 1)]);
    }

    /**
     * Update the applicant's privacy information for the second step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function postApplicantSecondStepPrivacyInformation(Request $request)
    {
        $requestData = $request->json()->all();
        $app_info = Applicant::where('id', $requestData['applicant_id'])->firstOrFail();
        $resp = $this->applicantBasicInformationHelper($app_info, $request, 'step');

        if (isset($resp['virus_error']) && $resp['virus_error']) {
            return $resp;
        }
        $applicantInformation = $resp;
        $applicantInformation->save();
    }

    /**
     * Update the applicant's privacy information for the fourth step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function postApplicantFourthStepPrivacyInformation(Request $request)
    {
        $requestData = $request->json()->all();
        $applicantInformation = Applicant::where('id', $requestData['applicant_id'])->first();

        $tracker = $applicantInformation->reference_tracker;
        if ($tracker != 0 && $tracker != $requestData['applicantInfo']['reference_tracker'])
            $this->checkReferenceIfExistsThenRemove($applicantInformation);

        $applicantInformation->update([
            'type' => $requestData['applicantInfo']['type'], 'step' => 5,
            'reference_tracker' => $requestData['applicantInfo']['reference_tracker'],
            'level_1' => $requestData['applicantInfo']['level_1'],
            'level_2' => $requestData['applicantInfo']['level_2'],
            'level_3' => $requestData['applicantInfo']['level_3'],
            'level_4' => $requestData['applicantInfo']['level_4']
        ]);
    }


    /**
     * Update the applicant's privacy information for the fifth step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function postApplicantFifthStepPrivacyInformation(Request $request)
    {
        $applicant_id = $request['applicant_id'];
        $app_info = Applicant::where('id', $applicant_id)->firstOrFail();
        $this->runTimeEmailConfiguration($app_info->agency_id);
        $app_info->addresses = $this->referencesAddresses($request['applicantInfo']['family_address']);
        $resp = $this->switchCaseBetweenReferences($request, $app_info, $applicant_id, 0, Chasing::where('agency_id', $app_info->agency_id)->firstOrFail(), 'UTC');
        if (isset($resp['virus_error']) && $resp['virus_error']) {
            return $resp;
        } else {
            $app_info = $resp;
        }
        $app_info->step = 6;
        $app_info->save();
    }

    /**
     * Process and save applicant privacy form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postApplicantPrivacy(Request $request)
    {
        $validator = $this->applicantValidationHelperFunction($request);

        if (sizeof($validator)) return response()->json(['saved' => false, 'errors' => $validator]);

        $applicant_id = $request['applicantInfo']['applicant_id'];
        $app_info = Applicant::where('id', $applicant_id)->firstOrFail();

        $this->runTimeEmailConfiguration($app_info->agency_id);

        if (!empty($app_info) && ($app_info->log_status == 0 || $app_info->is_complete == 1)) {

            $chasingSetting = Chasing::where('agency_id', $app_info->agency_id)->firstOrFail();

            if ($request['applicantInfo']['falg_to_agency'] == 0) {
                $resp = $this->switchCaseBetweenReferences($request, $app_info, $applicant_id, 1, $chasingSetting, $request['applicantInfo']['timezone']);
                if (isset($resp['virus_error']) && $resp['virus_error']) {
                    return response()->json([
                        'saved' => false,
                        'statusCode' => 4578,
                        'message' => 'The ' . $resp['type'] . ' is a virus file'
                    ]);
                } else {
                    $app_info = $resp;
                }
            }

            $app_info->ta_status = 0;
            $app_info->signature = $this->fileUploadHelperFunction("signature", null, $request['applicantInfo']['signature_image']);
            $app_info = $this->applicantBasicInformationHelper($app_info, $request, 'all');
            $app_info->addresses = $this->referencesAddresses($request['applicantInfo']['family_address']);
            $app_info->reference_tracker = $request['applicantInfo']['reference_tracker'];
            $app_info->is_complete = $request['applicantInfo']['falg_to_agency'] == 0 ? 0 : 1;
            $app_info->log_status = 1;
            $app_info->response_value = 0;
            $app_info->response_status = 0;
            $app_info->last_response_time = timeChangeAccordingToTimezoneForChasing(!empty($app_info->applicantbasic->timezone) ? $app_info->applicantbasic->timezone : "UTC", 0, null);
            $app_info->app_url = null;
            $app_info->save();

            $app_info['pass'] = $request['applicantInfo']['register_account']['password'];   //temporary password send to user for login
            $app_info->applicantbasic['pass'] = $request['applicantInfo']['register_account']['password'];
            $tenancy = Tenancy::where('id', $app_info->tenancy_id)->first();
            $tenancy = Tenancy::where('id', $app_info->tenancy_id)->first();
            if ($tenancy) {
                $allApplicantsHaveNullAppUrl = true;
                foreach ($tenancy->applicants as $applicant) {
                    if ($applicant->app_url != null) {
                        $allApplicantsHaveNullAppUrl = false;
                        break;
                    }
                }
                if ($allApplicantsHaveNullAppUrl) {
                    $tenancy->status = 2;
                    $tenancy->save();
                }
            }
            $start_date = Carbon::parse($tenancy->t_start_date);
            $end_date = Carbon::parse($tenancy->t_end_date);
            $this->createPaymentSchedules($app_info, $tenancy, $start_date, $end_date);
            if ($request['applicantInfo']['falg_to_agency'] == 0) {
                $app_info->tenancies->creator->notify(new ApplicantPrivacyFormCompletedNotification($tenancy, $app_info));
                $agencyData = Agency::where('id', $app_info->agency_id)->firstOrFail();
                $data = $this->emailTemplateData('WE', $app_info->applicantbasic, $tenancy, $agencyData, null, null, null, null, null, null, $request);
                Mail::to($app_info->applicantbasic->email)->send(new WelcomeEmail($data, $agencyData));
            } else {
                $app_info->tenancies->creator->notify(new ApplicantPrivacyFormIncompleteFlagNotification($tenancy, $app_info));
            }

            touchTenancy($tenancy->id, $app_info->applicantbasic->timezone);

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Process and save guarantor reference.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $appInfo
     * @param  mixed  $app_id
     * @param  mixed  $sendEmail
     * @param  mixed  $chasingSetting
     * @param  mixed  $timezone
     * @return \App\GuarantorReference
     */
    public function guarantorReference($request, $appInfo, $app_id, $sendEmail, $chasingSetting, $timezone)
    {
        if ($gu_info = GuarantorReference::where('agency_id', $appInfo->agency_id)->where('applicant_id', $app_id)->first()) {
        } else {
            $gu_info = new GuarantorReference(['agency_id' => $appInfo->agency_id, 'applicant_id' => $app_id]);
        }
        $gu_info->name = $request['g_name'];
        $gu_info->email = strtolower($request['g_email']);
        $gu_info->phone = $request['g_phone'];
        $gu_info->fill_status = 0;
        $gu_info->status = 1;
        $gu_info->agency_status = 1;
        $gu_info->country_code = $request['country_code'];
        $gu_info->timezone = $timezone;
        $gu_info->ref_link = config('global.frontSiteUrl') . ('/guarantor/' . Str::random(8) . '/' . Str::random(15));
        $gu_info->last_response_time = timeChangeAccordingToTimezoneForChasing($timezone, $chasingSetting->response_time, $chasingSetting->stalling_time);
        $gu_info->save();
        if ($sendEmail == 1) {
            $agencyData = Agency::where('id', $appInfo->agency_id)->firstOrFail();
            $data = $this->emailTemplateData('GRE', $appInfo->applicantbasic, $appInfo->tenancies, $agencyData, null, null, $gu_info, null, null, null, null);
            Mail::to($gu_info['email'])->send(new GuarantorReferenceEmail($data, $agencyData, $gu_info, $appInfo->applicantbasic));
        }
        event(new GuarantorAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => strtolower($request['g_email']), 'agency_id' => $appInfo->agency_id],
            'Add guarantor',
            'A guarantor added to the tenancy',
            'add',
            $appInfo->applicantbasic->email
        ));
        return $gu_info;
    }

    /**
     * Process and save student reference.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $appInfo
     * @param  mixed  $app_id
     * @param  mixed  $sendEmail
     * @param  mixed  $chasingSetting
     * @param  mixed  $timezone
     * @return \App\StudentReference
     */
    public function studentReference($request, $agency_id, $app_id)
    {
        if ($stu_info = StudentReference::where('agency_id', $agency_id)->where('applicant_id', $app_id)->first()) {
        } else {
            $stu_info = new StudentReference(['agency_id' => $agency_id, 'applicant_id' => $app_id]);
        }
        $stu_info->uni_name = $request['uni_name'];
        $stu_info->course_title = strtolower($request['course_title']);
        $stu_info->year_grad = $request['year_grad'];
        // $stu_info->timezone = $timezone;
        $stu_info->save();
        return $stu_info;
    }

    /**
     * Process and save quaterly reference.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $appInfo
     * @param  mixed  $app_id
     * @param  mixed  $sendEmail
     * @param  mixed  $chasingSetting
     * @param  mixed  $timezone
     * @return \App\QuarterlyReference
     */
    public function quarterlyReference($request, $agency_id, $app_id)
    {
        if ($qu_info = QuarterlyReference::where('agency_id', $agency_id)->where('applicant_id', $app_id)->first()) {
        } else {
            $qu_info = new QuarterlyReference(['agency_id' => $agency_id, 'applicant_id' => $app_id]);
        }
        $qu_info->close_bal = $request['close_bal'];
        $qu_info->fill_status = 1;
        $qu_info->status = 2;
        $qu_info->type = $request['type'];   //fullterm or quarterly
        $qu_info->agency_status = 1;
        $pd = $this->fileUploadArrayHelperFunction("document", null, $request['qu_doc']);
        if ($pd == 'virus_file') {
            return ['virus_error' => true, 'type' => 'quarterly document'];
        } else {
            $qu_info->qu_doc = $pd;
        }
        $qu_info->save();
        return $qu_info;
    }

    /**
     * Process and save employment reference.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $appInfo
     * @param  mixed  $app_id
     * @param  mixed  $sendEmail
     * @param  mixed  $chasingSetting
     * @param  mixed  $timezone
     * @return \App\EmploymentReference
     */
    public function employmentReference($request, $appInfo, $app_id, $sendEmail, $chasingSetting, $timezone)
    {
        if ($emp_info = EmploymentReference::where('agency_id', $appInfo->agency_id)->where('applicant_id', $app_id)->first()) {
        } else {
            $emp_info = new EmploymentReference(['agency_id' => $appInfo->agency_id, 'applicant_id' => $app_id]);
        }
        $emp_info->company_name = $request['company_name'];
        $emp_info->company_email = strtolower($request['manage_email']);
        $emp_info->company_phone = $request['manage_phone'];
        $emp_info->country_code = $request['country_code'];
        $emp_info->timezone = $timezone;
        $emp_info->ref_link = config('global.frontSiteUrl') . ('/employment/' . Str::random(8) . '/' . Str::random(15));
        $emp_info->last_response_time = timeChangeAccordingToTimezoneForChasing($timezone, $chasingSetting->response_time, $chasingSetting->stalling_time);
        $emp_info->fill_status = 0;
        $emp_info->status = 1;
        $emp_info->agency_status = 1;
        $emp_info->save();

        if ($sendEmail == 1) {

            $agencyData = Agency::where('id', $appInfo->agency_id)->firstOrFail();
            $data = $this->emailTemplateData('ERE', $appInfo->applicantbasic, $appInfo->tenancies, $agencyData, null, $emp_info, null, null, null, null, null);
            Mail::to($emp_info['company_email'])->send(new EmploymentReferenceEmail($data, $agencyData, $emp_info, $appInfo->applicantbasic));
        }
        event(new EmploymentAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => strtolower($request['manage_email']), 'agency_id' => $appInfo->agency_id],
            'Add employment',
            'A employment added to the tenancy',
            'add',
            $appInfo->applicantbasic->email
        ));
        return $emp_info;
    }

    /**
     * Process and save student reference.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $appInfo
     * @param  mixed  $app_id
     * @param  mixed  $sendEmail
     * @param  mixed  $chasingSetting
     * @param  mixed  $timezone
     * @return \App\LandlordReference
     */
    public function landlordReference($request, $appInfo, $app_id, $sendEmail, $chasingSetting, $timezone)
    {
        if ($landlord_info = LandlordReference::where('agency_id', $appInfo->agency_id)->where('applicant_id', $app_id)->first()) {
        } else {
            $landlord_info = new LandlordReference(['agency_id' => $appInfo->agency_id, 'applicant_id' => $app_id]);
        }

        $landlord_info->name = $request['ll_name'];
        $landlord_info->email = strtolower($request['ll_email']);
        $landlord_info->post_code = $request['post_code'];
        $landlord_info->town = $request['town'];
        $landlord_info->street = $request['street'];
        $landlord_info->country = $request['country'];
        $landlord_info->phone = $request['ll_phone'];
        $landlord_info->country_code = $request['country_code'];
        $landlord_info->timezone = $timezone;
        $landlord_info->t_s_date = $request['ll_start_date'];
        $landlord_info->t_e_date = $request['ll_end_date'];
        $landlord_info->ref_link = $appInfo->renew_status == 0 ? config('global.frontSiteUrl') . ('/landlord/' . Str::random(8) . '/' . Str::random(15)) : null;
        $landlord_info->last_response_time = timeChangeAccordingToTimezoneForChasing($timezone, $chasingSetting->response_time, $chasingSetting->stalling_time);
        $landlord_info->fill_status = $appInfo->renew_status == 0 ? 0 : 1;
        $landlord_info->status = $appInfo->renew_status == 0 ? 1 : 2;
        $landlord_info->agency_status = 1;

        $landlord_info->save();

        if ($sendEmail == 1 && $appInfo->renew_status == 0) {

            $agencyData = Agency::where('id', $appInfo->agency_id)->firstOrFail();
            $data = $this->emailTemplateData('LRE', $appInfo->applicantbasic, $appInfo->tenancies, $agencyData, null, null, null, $landlord_info, null, null, null);
            Mail::to($landlord_info['email'])->send(new LandlordReferenceEmail($data, $agencyData, $landlord_info, $appInfo->applicantbasic));
        }
        event(new LandlordAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => strtolower($request['ll_email']), 'agency_id' => $appInfo->agency_id],
            'Add landlord',
            'A landlord added to the tenancy',
            'add',
            $appInfo->applicantbasic->email
        ));
        return $landlord_info;
    }

    /**
     * Fetches applicant information along with related tenancy, requirements, and agency data.
     *
     * @param  Object $app_info The applicant information object
     * @return \Illuminate\Http\Response
     */
    public function fetchApplicantInformationHelperFunction($app_info)
    {
        $tenancy_info = Tenancy::where('id', $app_info->tenancy_id)->with(['landlords', 'properties'])->first();
        $app_req = ApplicantRequirement::where('agency_id', $app_info->agency_id)->first();

        $tenancy_info['available_applicants'] = $tenancy_info->applicants()->count();

        $applicants_name = $tenancy_info->applicants->map(function ($apps) {
            return $apps->applicantbasic['app_name'] . (is_null($apps->applicantbasic['m_name']) ? '' : ' ') . $apps['m_name'] . ' ' . $apps->applicantbasic['l_name'];
        });

        $agencyData = agencyDataFormId($app_info->agency_id);

        return response()
            ->json([
                'saved' => true,
                'applicant' => $app_info,
                'tenancy' => $tenancy_info,
                'applicant_req' => $app_req,
                'applicant_name' => $applicants_name,
                'agency_info' => $agencyData,
                'step' => $app_info->step,
                'applicant_privacy_statement' => $this->textForSpecificArea('AILPS', $app_info, $tenancy_info, $agencyData, null, null, null, null, null),
                'applicant_questionnaire' => $this->textForSpecificArea('AILAQ', $app_info, $tenancy_info, $agencyData, null, null, null, null, null),
                'tenancy_information' => $this->textForSpecificArea('ATI', $app_info, $tenancy_info, $agencyData, null, null, null, null, null),
            ]);
    }

    /**
     * Updates the basic information of an applicant.
     *
     * @param  Object $app_info The applicant information object
     * @param  \Illuminate\Http\Request  $request The HTTP request object
     * @param  string $dataFrom Data source ('all' or 'step')
     * @return \App\ApplicantBasic|null
     */
    public function updateApplicantBasicInformation($app_info, $request, $dataFrom)
    {
        $applicantBasic = '';
        if ($applicantBasic = $app_info->applicantbasic) {

            $applicantBasic->app_name = $request['applicantInfo']['register_account']['f_name'];
            $applicantBasic->m_name = isset($request['applicantInfo']['register_account']['m_name']) ? $request['applicantInfo']['register_account']['m_name'] : '';
            $applicantBasic->l_name = $request['applicantInfo']['register_account']['l_name'];
            $applicantBasic->country_code = $request['applicantInfo']['register_account']['country_code'];
            $applicantBasic->app_ni_number = $request['applicantInfo']['register_account']['ni_number'] == null ? null : $request['applicantInfo']['register_account']['ni_number'];
            $applicantBasic->dob = $request['applicantInfo']['register_account']['dob'];
            $applicantBasic->temporary_password = $request['applicantInfo']['register_account']['password'];
            if ($dataFrom === 'all') {
                $applicantBasic->password = bcrypt($request['applicantInfo']['register_account']['password']);
            }
            $applicantBasic->save();
        }

        return $applicantBasic;
    }

    /**
     * Helper function for updating applicant's basic information.
     *
     * @param  Object $app_info The applicant information object
     * @param  \Illuminate\Http\Request  $request The HTTP request object
     * @param  string $dataFrom Data source ('all' or 'step')
     * @return \App\Applicant|null|array
     */
    public function applicantBasicInformationHelper($app_info, $request, $dataFrom)
    {
        $requestData = $request->json()->all();
        $app_info->doc_type = $requestData['applicantInfo']['register_account']['doc_type'];
        $this->updateApplicantBasicInformation($app_info, $requestData, $dataFrom);
        $dataFrom === 'step' ? $app_info->step = 3 : '';

        if ($requestData['applicantInfo']['register_account']['doc_type'] === 'Passport') {
            $pd = $this->fileUploadHelperFunction("document", null, $requestData['applicantInfo']['register_account']['passport_document']);
            if ($pd == 'virus_file') {
                $this->deleteFile('document', $pd);
                return ['virus_error' => true, 'type' => 'passport document'];
            } else {
                $app_info->passport_document = $pd;
            }

            $spd =  $this->fileUploadHelperFunction("document", null, $requestData['applicantInfo']['register_account']['selfie_passport_document']);
            if ($spd == 'virus_file') {
                $this->deleteFile('document', $spd);
                return ['virus_error' => true, 'type' => 'selfie passport document'];
            } else {
                $app_info->selfie_passport_document = $spd;
            }
        }

        return $app_info;
    }

    /**
     * Validates applicant information and returns validation errors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\MessageBag
     */
    public function applicantValidationHelperFunction($request)
    {
        $rules = [
            'register_account.f_name' => 'required|string',
            'register_account.l_name' => 'required|string',
            'register_account.dob' => 'required|date',
            'register_account.doc_type' => 'required|string',
            'applicant_id' => 'required|integer'

        ];
        $rules += ($request['applicantInfo']['falg_to_agency'] == 0) ? ['signature_image' => 'required'] : [];

        $validator = validator($request['applicantInfo'], $rules);

        $doc_valid = validator($request['applicantInfo'], ['']);
        return $validator->errors();
    }

    /**
     * Performs actions based on different types of references provided.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $app_info
     * @param  int  $applicant_id
     * @param  bool  $sendEmail
     * @param  mixed  $chasingSetting
     * @param  mixed  $timezone
     * @return mixed
     */
    public function switchCaseBetweenReferences($request, $app_info, $applicant_id, $sendEmail, $chasingSetting, $timezone)
    {
        $totalReferences = $filledReferences = 0;
        foreach ($request['applicantInfo']['reference_form'] as $key => $reference) {
            switch ($key) {
                case "guarantor_form":
                    if (!empty($reference['g_email'])) {
                        $this->guarantorReference($reference, $app_info, $applicant_id, $sendEmail, $chasingSetting, $timezone);
                        $totalReferences++;
                    }
                    break;

                case "quarterly_form":
                    if (!empty($reference['qu_doc'])) {
                        $resp = $this->quarterlyReference($reference, $app_info->agency_id, $applicant_id);
                        if (isset($resp['virus_error']) && $resp['virus_error']) {
                            return $resp;
                        }
                        $filledReferences++;
                        $totalReferences++;
                    }
                    break;

                case "employment_form":
                    if (!empty($reference['manage_email'])) {
                        $this->employmentReference($reference, $app_info, $applicant_id, $sendEmail, $chasingSetting, $timezone);
                        $totalReferences++;
                    }
                    break;

                case "landlord_form":
                    if (!empty($reference['ll_email']) && $app_info->tenancies->type != 2 && $app_info->renew_status == 0) {
                        $this->landlordReference($reference, $app_info, $applicant_id, $sendEmail, $chasingSetting, $timezone);
                        $totalReferences++;
                    }
                    break;
                case "student_form":
                    if (!empty($reference['uni_name'])) {
                        $resp = $this->studentReference($reference, $app_info->agency_id, $applicant_id);
                        if (isset($resp['virus_error']) && $resp['virus_error']) {
                            return $resp;
                        }
                        $filledReferences++;
                        $totalReferences++;
                    }
                    break;
            }
        }
        $app_info->ref_status = $request['applicantInfo']['falg_to_agency'] == 0 ? 0 : 1;
        if ($sendEmail > 0) {

            if ($filledReferences > 0 && $totalReferences > 0 && $totalReferences == $filledReferences) {
                $app_info->status = 3;
            } else {
                $app_info->status = 2;
            }
            $app_info->total_references = $totalReferences;
            $app_info->fill_references = $filledReferences;
        }
        return $app_info;
    }
}
