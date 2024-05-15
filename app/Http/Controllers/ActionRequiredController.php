<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Chasing;
use App\Models\EmploymentReference;
use App\Models\GuarantorReference;
use App\Http\Requests\ActionRequire\SendCustomEmailRequest;
use App\Models\LandlordReference;
use App\Mail\CustomEmail;
use App\Models\Tenancy;
use Mail;
use App\Traits\AllPermissions;
use App\Traits\LastStaffActionTrait;
use App\Traits\WorkWithFile;
use App\Traits\TextForSpecificAreaTrait;
use App\Mail\DeadlineExtendEmail;
use App\Http\Requests\ActionRequire\ExtendDeadlineRequest;
use App\Http\Requests\ActionRequire\TenancyCancelRequest;
use App\Notifications\TenancyNotification;
use App\Events\SendEmailEvent;
use App\Traits\TenancyApplicantIdsHelperTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\InterimInspection;
use App\Models\EmailTemplate;
use App\Mail\InterimInspectionEmail;

class ActionRequiredController extends Controller
{
    use AllPermissions, LastStaffActionTrait, WorkWithFile, TextForSpecificAreaTrait, TenancyApplicantIdsHelperTrait;

    /**
     * Get tenancy cancel status.
     *
     * @param TenancyCancelRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTenancyCancelStatus(TenancyCancelRequest $request)
    {
        if ((agencyAdmin() || $this->manuallyTenancyStatus())
            && $tenancy = Tenancy::where('agency_id', authAgencyId())->where('id', Applicant::findOrFail($request['applicant_id'])->tenancy_id)->first()
        ) {
            $tenancy->applicants()->update(['status' => 6]);  //applicant status = 6 (Tenancy Application Cancelled)

            $agreementType = $request['agreement_type'] === 'extend' ? 'extend' : 'terminate';
            // $this->deleteTenancyRecords($tenancy);
            $tenancy->update(['status' => 10, 'agreement_type' => $agreementType]);
            $this->checkForPropertyStatus($tenancy);
            $tenancy->creator->notify(new TenancyNotification($tenancy, 10));

            $this->lastStaffAction('Cancel tenancy');
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Extend deadline for tenancy application.
     *
     * @param ExtendDeadlineRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postExtendDeadline(ExtendDeadlineRequest $request)
    {
        $agencyData = agencyData();

        if ($tenancy = Tenancy::where('agency_id', $agencyData->id)->where('id', Applicant::findOrFail($request['applicant_id'])->tenancy_id)->first()) {
            if (Carbon::parse($tenancy->deadline)->isPast(now())) {
                $extendDeadline = Carbon::parse(now())->addDays($request['days']);
            } else {
                $extendDeadline = Carbon::parse($tenancy->deadline)->addDays($request['days']);
            }

            $tenancy->update(['deadline' => $extendDeadline]);

            $chasingSetting = Chasing::where('agency_id', $tenancy->agency_id)->firstOrFail();

            foreach ($tenancy->applicants as $applicant) {

                $data = $this->emailTemplateData('DEE', $applicant->applicantbasic, $tenancy, $agencyData, null, null, null, null, null, null, $request);
                Mail::to($applicant->applicantbasic->email)->send(new DeadlineExtendEmail($data, $agencyData));

                $at = timeChangeAccordingToTimezoneForChasing(!empty($applicant->applicantbasic->timezone) ? $applicant->applicantbasic->timezone : "UTC", 0, $chasingSetting->stalling_time);
                $et = timeChangeAccordingToTimezoneForChasing(!empty($applicant->employmentReferences->timezone) ? $applicant->employmentReferences->timezone : "UTC", 0, $chasingSetting->stalling_time);
                $gt = timeChangeAccordingToTimezoneForChasing(!empty($applicant->guarantorReferences->timezone) ? $applicant->guarantorReferences->timezone : "UTC", 0, $chasingSetting->stalling_time);
                $lt = timeChangeAccordingToTimezoneForChasing(!empty($applicant->landlordReferences->timezone) ? $applicant->landlordReferences->timezone : "UTC", 0, $chasingSetting->stalling_time);

                $NewStatus = $applicant->status;
                if ($applicant->status == 10) {
                    $NewStatus = 1;
                } else if ($applicant->status == 11) {
                    $NewStatus = 2;
                } else if ($applicant->status == 12) {
                    $NewStatus = 5;
                }

                $applicant->update(['response_value' => 0, 'response_status' => 0, 'last_response_time' => $at, 'status' => $NewStatus]);
                $applicant->employmentReferences()->update(['response_value' => 0, 'response_status' => 0, 'last_response_time' => $et]);
                $applicant->guarantorReferences()->update(['response_value' => 0, 'response_status' => 0, 'last_response_time' => $gt]);
                $applicant->landlordReferences()->update(['response_value' => 0, 'response_status' => 0, 'last_response_time' => $lt]);
            }

            $this->lastStaffAction('Extend deadline of tenancy');
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Send custom email to applicants or references.
     *
     * @param SendCustomEmailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSendCustomEmail(SendCustomEmailRequest $request)
    {
        $agencyData = agencyData();
        $applicantInfo = Applicant::findOrFail($request['applicant_id']);
        if (!Tenancy::where('agency_id', $agencyData->id)->where('id', $applicantInfo->tenancy_id)->firstOrFail()) {
            return response()->json(['saved' => false]);
        }

        $documentDetails = [];
        foreach ($request['document'] as $singleDocument) {
            $documentDetails[] = ["name" => $singleDocument["name"], "file" => $this->file_upload($singleDocument["file"], "test", null)];
        }
        $employmentData = $guarantorData = $landlordData = null;
        if ($request['send_code'] == 'E' && !$employmentData = $this->employmentEmailIsExist($request, $applicantInfo)) {
            return response()->json(['saved' => false]);
        } else if ($request['send_code'] == 'G' && !$guarantorData = $this->guarantorEmailIsExist($request, $applicantInfo)) {
            return response()->json(['saved' => false]);
        } else if ($request['send_code'] == 'L' && !$landlordData = $this->landlordEmailIsExist($request, $applicantInfo)) {
            return response()->json(['saved' => false]);
        }

        $data = $this->textForSpecificAreaForCustomTemplate($request, $applicantInfo->applicantbasic, $applicantInfo->tenancies, $agencyData, null, optional($employmentData), optional($guarantorData), optional($landlordData), null, null);

        Mail::to($request['email'])->send(new CustomEmail($data, $agencyData, $documentDetails, $request));

        foreach ($documentDetails as $singleFile) $this->deleteFile("test", $singleFile["file"]);

        event(new SendEmailEvent($applicantInfo->tenancy_id, 'Custom Email', 'Custom email sent to applicants and references', $request['email'], $request['message'], $agencyData->id));

        $this->lastStaffAction('Send custom email to applicant');
        return response()->json(['saved' => true]);
    }

    /**
     * Check if employment email exists.
     *
     * @param Request $request
     * @param mixed $applicantInfo
     * @return mixed
     */
    public function employmentEmailIsExist($request, $applicantInfo)
    {
        return EmploymentReference::where('employment_references.agency_id', $applicantInfo->agency_id)
            ->where('employment_references.company_email', $request['email'])
            ->join('applicants', function ($query) use ($applicantInfo) {
                $query->on('applicants.id', '=', 'employment_references.applicant_id')
                    ->where('applicants.tenancy_id', $applicantInfo->tenancy_id);
            })->first();
    }

    /**
     * Check if guarantor email exists.
     *
     * @param Request $request
     * @param mixed $applicantInfo
     * @return mixed
     */
    public function guarantorEmailIsExist($request, $applicantInfo)
    {
        return GuarantorReference::where('guarantor_references.agency_id', $applicantInfo->agency_id)
            ->where('guarantor_references.email', $request['email'])
            ->join('applicants', function ($query) use ($applicantInfo) {
                $query->on('applicants.id', '=', 'guarantor_references.applicant_id')
                    ->where('applicants.tenancy_id', $applicantInfo->tenancy_id);
            })->first();
    }

    /**
     * Check if landlord email exists.
     *
     * @param Request $request
     * @param mixed $applicantInfo
     * @return mixed
     */
    public function landlordEmailIsExist($request, $applicantInfo)
    {
        return LandlordReference::where('landlord_references.agency_id', $applicantInfo->agency_id)
            ->where('landlord_references.email', $request['email'])
            ->join('applicants', function ($query) use ($applicantInfo) {
                $query->on('applicants.id', '=', 'landlord_references.applicant_id')
                    ->where('applicants.tenancy_id', $applicantInfo->tenancy_id);
            })->first();
    }

    /**
     * Send custom interim inspection email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSendCustomInterimInspectionEmail(Request $request)
    {
        $agencyData = agencyData();
        $authAgencyId = authAgencyId();
        $interimDetails = $request->input('interim_detail');
        foreach ($interimDetails as $detail) {
            $inspection = InterimInspection::where('agency_id', $authAgencyId)->where('id', $detail['inspection_id'])->firstOrFail();
            if (!$inspection) {
                return response()->json(['saved' => false, 'error' => 'Interim Inspection not found']);
            }
            $body = $detail['body'];
            if (!is_array($body)) {
                return response()->json(['saved' => false, 'error' => 'Invalid body']);
            }
            $email = $detail['email'];
            $tenancy = Tenancy::where('agency_id', $authAgencyId)->where('id', $inspection->tenancy_id)->firstOrFail();
            if (!$tenancy) {
                return response()->json(['saved' => false, 'error' => 'Tenancy not found']);
            }
            $inspection->update(['email_date' => now()]);
            $inspection['subject'] = $detail['subject'];
            $mm = EmailTemplate::where('agency_id', $authAgencyId)->where('mail_code', 'II')->firstOrFail();
            $mm->update(['data' => json_encode($body)]);
            $data = $this->emailTemplateData('II', null, $tenancy, $agencyData, null, null, null, null, null, null, null, $inspection);
            Mail::to($email)->send(new InterimInspectionEmail($data, $agencyData, $inspection, null));
            event(new SendEmailEvent($inspection->tenancy_id, 'Custom Email', 'Custom email sent to landlords', $email, $detail['subject'], $authAgencyId));
        }
        $this->lastStaffAction('Send email to landlord for interim inspection');
        return response()->json(['saved' => true]);
    }
}
