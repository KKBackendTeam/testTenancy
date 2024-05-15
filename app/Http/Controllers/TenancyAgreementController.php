<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Chasing;
use App\Events\AgreementEvent;
use App\Mail\TenancyAgreementEmail;
use App\Notifications\Applicant\AgreementSigningNotification;
use App\Notifications\Applicant\ApplicationCompleteNotification;
use App\Models\Tenancy;
use Mail;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile, App\Traits\LastStaffActionTrait, App\Traits\TextForSpecificAreaTrait;
use App\Http\Requests\TenancyAgreement\TenancyAgreementGenerateRequest;
use Carbon\Carbon;
use App\Notifications\TenancyNotification;
use App\Http\Requests\TenancyAgreementSigningRequest;
use App\Traits\AgreementHelperTrait;

class TenancyAgreementController extends Controller
{
    use AllPermissions, WorkWithFile, LastStaffActionTrait, TextForSpecificAreaTrait, AgreementHelperTrait;

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\TenancyAgreementGenerateRequest  $request
     * @param  \App\Tenancy  $tenancy
     * @return \Illuminate\Http\Response
     */
    public function postTenancyAgreementPdfGeneratorSaveToDatabase(TenancyAgreementGenerateRequest $request)
    {
        $tenancy = Tenancy::where('agency_id', authAgencyId())->findOrFail($request['tenancy_id']);

        $tenancy->update([
            'signing_date' => $request['signing_date'],
            'generated_date' => now(),
            'terminated_date' => $request['terminated_date'] ?? null
        ]);
        $tenancy->update(['status' => 5, 'tc_status' => 0, 'review_agreement' => 0]);

        $chasingSetting = Chasing::where('agency_id', $tenancy->agency_id)->firstOrFail();
        foreach ($tenancy->applicants as $applicant) {
            $at = timeChangeAccordingToTimezoneForChasing(!empty($applicant->applicantbasic->timezone)
                ? $applicant->applicantbasic->timezone : "UTC", $chasingSetting->response_time, $chasingSetting->stalling_time);

            $applicant->update([
                'status' => 5, 'ta_status' => 0, 'review_agreement' => 0, 'agreement_signature' => null,
                'signing_time' => null, 'ip_address' => null,
                'response_value' => 0, 'response_status' => 0, 'last_response_time' => $at
            ]);
        }
        $this->deleteFile('agreement', $tenancy->agreement);
        $agencyData = agencyData();
        $paymentSchedules = [];
        foreach ($tenancy->applicants as $applicant) {
            $applicantName = $applicant->applicantbasic->app_name . ' ' . $applicant->applicantbasic->l_name ?? '';
            $paymentSchedules[$applicantName] = $applicant->paymentSchedule()->get()->toArray();
        }
        $textCode = $request['text_code'];
        $this->agreementCreateHelper($this->textForSpecificAreaForAgreement($textCode, $tenancy, $agencyData, null, null, null, null, $request, $paymentSchedules), $request, 'All', null);
        $tenancy->creator->notify(new TenancyNotification($tenancy, 5));
        foreach ($tenancy->applicants as $applicant) {
            $data = $this->emailTemplateData('TAE', $applicant->applicantbasic, $tenancy, $agencyData, null, null, null, null, null, null, null);
            Mail::to($applicant->applicantbasic->email)->send(new TenancyAgreementEmail($data, $agencyData));
            $applicant->applicantbasic->notify(new AgreementSigningNotification($applicant->tenancies, $applicant));
        }

        $this->lastStaffAction('Tenancy agreement generate');

        foreach ($tenancy->applicants as $applicant) {
            event(new AgreementEvent(
                $tenancy->id,
                'Agreement generate',
                'Agreement generated for this tenancy',
                $applicant->applicantbasic->email
            ));
        }

        $timezone = $tenancy->applicants->first()->applicantbasic->timezone;
        touchTenancy($request['tenancy_id'], $timezone);

        return response()->json(['saved' => true, 'tenancy' => 'Tenancy agreement generated successfully']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function tenancyAgreementFullData($id)
    {
        return response()->json(['saved' => true, 'tenancy' => $this->tenancyAgreementFullDataHelperFunction($id, 'Whole', null)]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTenancyAgreement($id)
    {
        $fileName = Tenancy::findOrFail($id)->agreement;
        if (!$this->checkFileExistOrNot('agreement', $fileName)) {
            return response()->json(['saved' => false]);
        }
        return response()->json(['saved' => true, 'agreement' => Tenancy::findOrFail($id)->agreement]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTheTenancyAgreement($id)
    {
        return response()->download(storage_path('app/public/agency/agreement/' . $fileName = Tenancy::findOrFail($id)->agreement));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\TenancyAgreementSigningRequest  $request
     * @param  \App\Tenancy  $tenancy
     * @return \Illuminate\Http\Response
     */
    public function agreementSigningAndCheckTenancyStatus(TenancyAgreementSigningRequest $request)
    {
        $applicant = Applicant::where('id', $request['applicant_id'])->firstOrFail();
        $tenancy = Tenancy::where('id', $request['tenancy_id'])->firstOrFail();

        $this->deleteFile('agreement_signature', $applicant->agreement_signature);
        $image_name = $this->file_upload($request['agreement_signature'], "agreement_signature", null);

        $applicant->update(['ta_status' => 1, 'status' => 7, 'agreement_signature' => $image_name, 'signing_time' => timeChangeAccordingToTimezone($request['timezone']), 'ip_address' => $_SERVER['REMOTE_ADDR']]);
        $this->deleteFile('agreement', $applicant->tenancies->agreement);
        $request['signing_date'] = $tenancy->signing_date;
        $request['generated_date'] = $tenancy->generated_date;
        $textCode = $request['text_code'];

        $paymentSchedules = [];
        foreach ($tenancy->applicants as $applicant) {
            $applicantName = $applicant->applicantbasic->app_name . ' ' . $applicant->applicantbasic->l_name ?? '';
            $paymentSchedules[$applicantName] = $applicant->paymentSchedule()->get()->toArray();
        }

        $this->agreementCreateHelperForApplicant($this->textForSpecificAreaForAgreement($textCode, $applicant->tenancies, $applicant->agencies, null, null, null, null, $request, $paymentSchedules), $request, 'Single', $applicant);
        unset($tenancy['signing_date']);
        unset($tenancy['generated_date']);
        if ($applicant->tenancy_id == $request['tenancy_id']) {
            if ($tenancy->applicants()->where('ta_status', 1)->count() == $tenancy->applicants()->count()) {
                $tenancy->applicants()->update(['status' => 7]);
                $tenancy->update(['tc_status' => 1, 'status' => 18, 'days_to_complete' => Carbon::now()->diffInDays($tenancy->created_at)]);
                $tenancy->creator->notify(new TenancyNotification($tenancy, 20));
                $tenancy->creator->notify(new ApplicationCompleteNotification(1, $tenancy));
            }

            if ($tenancy->type == 3) {
                $applicant->update(['status' => 7]);
            }
        }

        touchTenancy($request['tenancy_id'], $applicant->applicantbasic->timezone);

        return response()->json(['saved' => true]);
    }
}
