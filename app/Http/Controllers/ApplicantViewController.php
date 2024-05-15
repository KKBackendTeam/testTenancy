<?php

namespace App\Http\Controllers;

use Mail;
use PDF;
use App\Models\User;
use App\Models\Agency;
use DateTime;
use DateInterval;
use App\Models\Tenancy;
use App\Models\PaymentSchedule;
use App\Models\Applicant;
use App\Models\TenancyEvents;
use App\Mail\CustomEmail;
use Illuminate\Support\Str;
use App\Models\LandlordReference;
use App\Models\GuarantorReference;
use App\Models\GuarantorRefOtherDocument;
use App\Models\StudentReference;
use App\Models\TenancyHistory;
use App\Models\InterimInspection;
use App\Events\TenancyEvent;
use App\Models\EmploymentReference;
use App\Events\ApplicantEvent;
use App\Events\EmploymentAddDeleteEvent;
use App\Events\GuarantorAddDeleteEvent;
use App\Events\LandlordAddDeleteEvent;
use App\Events\ResendEmailEvent;
use App\Events\ReferenceEvent;
use App\Mail\RenewTenantEmail;
use App\Models\EmailTemplate;
use App\Mail\LandlordReferenceEmail;
use App\Http\Requests\ApplicantViewTenancy\ApplicantInfoRequest;
use App\Http\Requests\ApplicantViewTenancy\EmailSendRequest;
use App\Http\Requests\ApplicantViewTenancy\EmploymentInfoRequest;
use App\Http\Requests\ApplicantViewTenancy\GuarantorInfoRequest;
use App\Http\Requests\ApplicantViewTenancy\LandlordInfoRequest;
use App\Http\Requests\TenancyReview\ApplicantTenancyRequest;
use App\Http\Requests\ApplicantViewTenancy\AddNewApplicantRequest;
use App\Http\Requests\ApplicantViewTenancy\LandlordReferenceInfoRequest;
use App\Http\Requests\ApplicantViewTenancy\GuarantorReferenceInfoRequest;
use App\Http\Requests\ApplicantViewTenancy\EmploymentReferenceInfoRequest;
use App\Mail\EmploymentReferenceEmail;
use App\Mail\GuarantorReferenceEmail;
use App\Traits\AllPermissions;
use App\Traits\LastStaffActionTrait;
use App\Traits\PaymentScheduleTrait;
use App\Traits\WorkWithFile;
use App\Traits\TenancyApplicantIdsHelperTrait;
use App\Traits\TextForSpecificAreaTrait;
use App\Traits\ReferencesAddressesTrait;
use App\Traits\ConfigrationTrait;
use App\Http\Requests\ApplicantViewTenancy\ChangeTenancyNegotiatorRequest;
use App\Http\Requests\ApplicantViewTenancy\DeleteApplicantRequest;
use App\Events\SendEmailEvent;
use Illuminate\Http\Request;
use App\Events\ApplicantAddDeleteEvent;
use App\Traits\SortingActionTrait;
use App\Mail\RegistrationEmail;
use App\Models\QuarterlyReference;
use App\Notifications\TenancyNotification;
use App\Notifications\Applicant\ApplicationCompleteNotification;
use App\Mail\ApplicationFinilizeEmail;
use App\Http\Requests\ApplicantViewTenancy\ReviewAgreementRequest;
use App\Http\Requests\ApplicantViewTenancy\QuarterlyInfoRequest;
use Carbon\Carbon;
use App\Models\Chasing;
use App\Traits\AgreementHelperTrait;
use App\Models\Applicantbasic;
use App\Events\AgreementEvent;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\TenancyAgreement\TenancyAgreementGenerateRequest;

class ApplicantViewController extends Controller
{
    use AllPermissions, LastStaffActionTrait, WorkWithFile, TenancyApplicantIdsHelperTrait, TextForSpecificAreaTrait, ReferencesAddressesTrait;
    use ConfigrationTrait, SortingActionTrait, AgreementHelperTrait, PaymentScheduleTrait;


    /**
     * Retrieve and review tenancy applicant details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reviewTenancyApplicant($id)
    {
        $tenancy = Tenancy::where('agency_id', authAgencyId())->where('id', $id)
            ->with(['properties', 'landlords:id,f_name,l_name,email'])
            ->with('users:id,name,l_name')
            ->with(['tenancyHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();
        $tenancy->restrictionArray = (!empty($tenancy->restriction)) ?  explode(',', $tenancy->restriction) : [];
        $tenancy->rentIncludeArray = (!empty($tenancy->rent_include)) ?  explode(',', $tenancy->rent_include) : [];
        $tenancy['negotiator_name'] = $tenancy->creator->name . ' ' . $tenancy->creator->l_name;

        $apps = [];
        if (!empty($tenancy->landlords)) {
            $apps[] = [
                'email' => $tenancy->landlords['email'],
                'code' => 'Property Landlord',
                'unique' => Str::random(8),
            ];
        }
        $applicants = Applicant::where('tenancy_id', $tenancy->id)
            ->with([
                'applicantbasic', 'employmentReferences', 'paymentSchedule',
                'quarterlyReferences', 'landlordReferences', 'studentReferences',
                'guarantorReferences' => function ($query) {
                    $query->with('guarantorRefOtherDocument');
                },
            ])
            ->oldest()->get();
        $i = count($apps);
        foreach ($applicants as $app) {
            if ($app->tenancy_id == $id && !empty($app['applicantbasic']['email'])) {
                $apps[$i]['email'] = $app['applicantbasic']['email'];
                $apps[$i]['code'] = 'Applicant';
                $apps[$i]['unique'] = Str::random(8);

                if (!$app['employmentReferences']->isEmpty()) {
                    foreach ($app['employmentReferences'] as $employment) {
                        $i++;
                        $apps[$i]['email'] = $employment['company_email'];
                        $apps[$i]['code'] = 'Employment';
                        $apps[$i]['unique'] = Str::random(8);
                    }
                }
                if (!$app['guarantorReferences']->isEmpty()) {
                    foreach ($app['guarantorReferences'] as $guarantor) {
                        $i++;
                        $apps[$i]['email'] = $guarantor['email'];
                        $apps[$i]['code'] = 'Guarantor';
                        $apps[$i]['unique'] = Str::random(8);
                    }
                }
                if (!$app['landlordReferences']->isEmpty()) {
                    foreach ($app['landlordReferences'] as $landlord) {
                        $i++;
                        $apps[$i]['email'] = $landlord['email'];
                        $apps[$i]['code'] = 'Landlord';
                        $apps[$i]['unique'] = Str::random(8);
                    }
                }
                $i++;
            }
        }

        return response()
            ->json([
                'saved' => true,
                'total_app' => $tenancy->applicants()->count(),
                'tenancies' => $tenancy,
                'applicant' => $applicants,
                'email_array' => $apps,
                'financial_configuration' => $this->financialConfiguration()
            ]);
    }

    /**
     * Retrieve all tenancy events for a given tenancy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getAllTenancyEvents($id)
    {
        $tenancyEvents = TenancyEvents::where('agency_id', authAgencyId())
            ->where('tenancy_id', $id)
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingTenancyEventVariables[request('sort_by')]) ? $this->sortingTenancyEventVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));
        $data = $tenancyEvents->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'tenancy_events' => ['data' => $data, 'total' => $tenancyEvents->count()]]);
    }

    /**
     * Update tenancy details based on the provided request.
     *
     * @param  \App\Http\Requests\ApplicantTenancyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postTenancyUpdate(ApplicantTenancyRequest $request)
    {
        if ((agencyAdmin() || $this->editTenancy() || $this->reviewTenancy()) && $tenancy_edit = Tenancy::where('id', $request['id'])->firstOrFail()) {

            $authAgencyId = authAgencyId();

            $tenancyOverlapped = Tenancy::where('agency_id', $authAgencyId)
                ->where('property_id', $request['property_id'])
                ->where('t_end_date', '>=', $request['t_start_date'])
                ->where('t_start_date', '<=', $request['t_end_date'])
                ->where('id', '!=', $request['id'])
                ->where('status', '!=', 10)
                ->get(['id']);

            if ($tenancyOverlapped->count() > 0) {
                return response()->json(['saved' => false, 'statusCode' => 2314, 'reason' => 'Previous tenancy end date and this tenancies starting date is overlapped']);
            }

            $noOfApplicantStatus = $this->checkNoOfApplicants($tenancy_edit, $request['no_applicant']);

            if ($noOfApplicantStatus > 0) {
                if ($noOfApplicantStatus == 1) return response()->json(['saved' => false, 'statusCode' => 2312, 'reason' => "You can't increase the number of applicants"]);
                else return response()->json(['saved' => false, 'statusCode' => 2313, 'reason' => "You can't decrease the number of applicants"]);
            }

            $validator = $this->tenancyValidation($request->all(), 'edit', $authAgencyId, $this->tenancyRequirement($authAgencyId));

            if ($validator->errors()->count() > 0) {
                return response()->json(['saved' => false, 'errors' => $validator->errors()]);
            }

            if (!$this->statusCheckBeforeTenancyEdit($tenancy_edit, $request['status'])) {
                return response()->json(['saved' => false, 'statusCode' => 2315, 'reason' => "You can't update the tenancy status"]);
            }

            if ($tenancy_edit->status != 5 && $request['status'] == 5) {
                $requestOther = new TenancyAgreementGenerateRequest();
                $requestOther['tenancy_id'] = $request['id'];
                $requestOther['signing_date'] = now()->toDateString();
                app('App\Http\Controllers\TenancyAgreementController')->postTenancyAgreementPdfGeneratorSaveToDatabase($requestOther);
            }

            $tenancy_old = Tenancy::where('id', $request['id'])->first();   //creating the 2 same object for the update difference
            $updated = $this->tenancyUpdateHelper($request, $tenancy_edit);

            $updated->properties()->update(['available_from' => dateFormat(Carbon::parse($updated->t_end_date)->addDay(1))]);

            $this->changeApplicantStatus($request['status'], $tenancy_old);
            if ($request['status'] == 10) {
                $this->checkForPropertyStatus($tenancy_old);
            }
            if (!empty($updated->getChanges())) {
                $oldAttributes = $tenancy_old->getAttributes();
                $changes = $updated->getChanges();

                foreach ($changes as $key => $value) {
                    if ($value === null) {
                        $changes[$key] = 'Null';
                    }
                }

                foreach ($oldAttributes as $key => $value) {
                    if ($value === null) {
                        $oldAttributes[$key] = 'Null';
                    }
                }

                event(new TenancyEvent($request['id'], 'Edit tenancy', '', 'Edit the tenancy information', $oldAttributes, $changes));
            }

            if (isset($tenancy_edit->timezone)) {
                touchTenancy($request['id'], $tenancy_edit->timezone);
            } else {
                touchTenancy($request['id'], 'UTC');
            }

            if ($request['status'] == 10) {
                $this->deleteTenancyRecords($tenancy_old);
            }

            $this->lastStaffAction('Edit tenancy');
            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Helper function to update tenancy details.
     *
     * @param  \App\Http\Requests\ApplicantTenancyRequest  $request
     * @param  \App\Models\Tenancy  $tenancy
     * @return \App\Models\Tenancy
     */
    public function tenancyUpdateHelper($request, $tenancy)
    {
        $tenancy->timestamps = true;
        $tenancy->restriction = implode(',', $request['restrictionArray']);
        $tenancy->rent_include = implode(',', $request['rentIncludeArray']);
        $tenancy->pro_address = $request['pro_address'];
        $tenancy->monthly_amount = $request['monthly_amount'];
        $tenancy->total_rent = $request['total_rent'];
        $tenancy->deposite_amount = $request['deposite_amount'];
        $tenancy->holding_amount = $request['holding_amount'];
        $tenancy->t_start_date = $request['t_start_date'];
        $tenancy->t_end_date = $request['t_end_date'];
        $tenancy->deadline = $request['deadline'];
        $tenancy->type = $request['type'];
        $tenancy->signing_date = $request['signing_date'];
        $tenancy->days_to_complete = $request['days_to_complete'];
        $tenancy->status = $request['status'];
        $tenancy->parking = ($request['parking'] == 2) ? 2 : 1;
        $tenancy->parking_cost = $request['parking_cost'] ?? '0.00';
        $tenancy->parkingArray = $request['parkingArray'];
        $tenancy->notes_text = optional($request)['notes_text'];
        $tenancy->no_applicant = $request['no_applicant'];
        $tenancy->interism_inspection = $request['interism_inspection'];
        $tenancy->tenancyInterimInspection()->delete();
        if (!empty($request['interism_inspection'])) {
            $date1 = new DateTime($request['t_start_date']);
            $date2 = new DateTime($request['t_end_date']);
            $interval = $date1->diff($date2);
            $months = $interval->format('%m');
            $years = $interval->format('%y');
            if ($interval->d > 15) {
                $months += 1;
            }
            $years += floor($months / 12);
            $months %= 12;
            $lengthOfTenancy = $years * 12 + $months;
            $numberOfInspection = $request['interism_inspection'];
            $totalMonths = $lengthOfTenancy + 1;
            $intervalMonths = floor($totalMonths / ($numberOfInspection + 1));
            $currentDate = clone $date1;
            for ($i = 0; $i < $numberOfInspection; $i++) {
                $currentDate->add(new DateInterval("P{$intervalMonths}M"));
                if ($currentDate >= $date2) {
                    break;
                }
                $interimInspection = new InterimInspection();
                $interimInspection->tenancy_id = $tenancy->id;
                $interimInspection->agency_id = $tenancy->agency_id;
                $interimInspection->timestamps = true;
                $interimInspection->reference = $tenancy->reference;
                $interimInspection->address = $request['pro_address'];
                $interimInspection->inspection_month = $currentDate->format('F Y');
                $interimInspection->save();
            }
        }
        if (!empty($request['t_start_date'] || $request['t_end_date'])) {
            foreach ($tenancy->applicants as $applicant) {
                $applicant->paymentSchedule()->delete();
                $start_date = !empty($request['t_start_date']) ? Carbon::parse($request['t_start_date']) : $tenancy->t_start_date;
                $end_date = !empty($request['t_end_date']) ? Carbon::parse($request['t_end_date']) : $tenancy->t_start_date;
                $this->createPaymentSchedules($applicant, $tenancy, $start_date, $end_date);
            }
        }

        $tenancy->save();
        return $tenancy;
    }

    /**
     * Update the applicant information.
     *
     * @param  \Illuminate\Http\ApplicantInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postApplicantInfoUpdate(ApplicantInfoRequest $request)
    {
        $authAgencyId = authAgencyId();

        if (!empty(Applicantbasic::where('agency_id', '!=', $authAgencyId)->where('email', strtolower($request['email']))->first())) {
            $validator = validator($request->only("email"), [""]);
            $validator->getMessageBag()->add('email', 'This Email already exist.');
            return response()->json(['saved' => false, 'errors' => $validator->errors()]);
        }

        $oldApplicantBasic = Applicantbasic::where('agency_id', $authAgencyId)->where('id', $request['applicant_basic_id'])->first();
        $newApplicantBasic = Applicantbasic::where('agency_id', $authAgencyId)->where('id', $request['applicant_basic_id'])->first();

        if (!is_null($applicantBasic = Applicantbasic::where('agency_id', $authAgencyId)->where('email', strtolower($request['applicantbasic']['email']))->first())) {
            if ($applicantBasic->id == $request['applicant_basic_id']) {
                $newApplicantBasic->email = strtolower($request['email']);
            } else {
                $validator = validator($request->only("email"), [""]);
                $validator->getMessageBag()->add('email', 'This email already exist');
                return response()->json(['saved' => false, 'errors' => $validator->errors()]);
            }
        } else {
            $newApplicantBasic->email = strtolower($request['email']);
        }

        $newApplicantBasic->app_name = $request['app_name'];
        $newApplicantBasic->m_name = $request['m_name'];
        $newApplicantBasic->l_name = $request['l_name'];
        $newApplicantBasic->dob = $request['dob'];
        $newApplicantBasic->country_code = $request['country_code'];
        $newApplicantBasic->app_ni_number = $request['app_ni_number'];
        $newApplicantBasic->app_mobile = $request['app_mobile'];
        $newApplicantBasic->save();
        $oldApplicant = Applicant::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();
        $newApplicant = Applicant::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();
        $newApplicant->type = $request['type'];
        $newApplicant->right_to_rent = $request['right_to_rent'];
        $newApplicant->notes_text = optional($request)['notes_text'];
        $newApplicant->save();
        if (!empty($request['student_references'][0]['uni_name'])) {
            $stu_info = StudentReference::where('agency_id', $authAgencyId)->where('applicant_id', $request['id'])->first();
            if ($stu_info) {
                $stu_info->uni_name = $request['student_references'][0]['uni_name'];
                $stu_info->course_title = $request['student_references'][0]['course_title'];
                $stu_info->year_grad = $request['student_references'][0]['year_grad'];
                $stu_info->save();
            }
        }
        if (!empty($request['payment_schedule'])) {
            foreach ($request['payment_schedule'] as $paymentData) {
                $paymentSchedule = PaymentSchedule::find($paymentData['id']);
                if ($paymentSchedule) {
                    $paymentSchedule->date = $paymentData['date'];
                    $paymentSchedule->amount = $paymentData['amount'];
                    $paymentSchedule->save();
                }
            }
        }
        if (!empty($newApplicantBasic->getChanges()) || !empty($newApplicant->getChanges())) {

            event(new ApplicantEvent(
                $request['tenancy_id'],
                'Edit applicant',
                '',
                'Edit the applicant information',
                $oldApplicantBasic->getAttributes(),
                $newApplicantBasic->getChanges(),
                $oldApplicant->getAttributes(),
                $newApplicant->getChanges(),
                $newApplicantBasic->email
            ));
        }

        touchTenancy($newApplicant->tenancy_id, $oldApplicantBasic->timezone);
        $this->lastStaffAction('Edit the applicant information');

        return response()->json(['saved' => true]);
    }

    /**
     * Update the landlord information.
     *
     * @param  \Illuminate\Http\LandlordInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postLandlordInfoUpdate(LandlordInfoRequest $request)
    {
        $authAgencyId = authAgencyId();
        $old_landlord_info = LandlordReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();
        $landlord_info = LandlordReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();
        $landlord_info->email = strtolower($request['email']);
        $landlord_info->name = $request['name'];
        if (isset($request['phone']) && $request['phone']) {
            $landlord_info->phone = $request['phone'];
        } else {
            $landlord_info->phone = ' ';
        }
        $landlord_info->country_code = $request['country_code'];
        $landlord_info->rent_price_value = $request['rent_price_value'];
        $landlord_info->paid_status = $request['paid_status'];
        $landlord_info->frequent_status = $request['frequent_status'];
        $landlord_info->arrears_status = $request['arrears_status'];
        if ($request['arrears_status'] == 'Yes') {
            $landlord_info->paid_arrears = intval($request['paid_arrears_value']);
            $landlord_info->paid_arrears_value = $request['paid_arrears_value'];
        }
        $landlord_info->post_code = $request['post_code'];
        $landlord_info->town = $request['town'];
        $landlord_info->street = $request['street'];
        $landlord_info->country = $request['country'];
        $landlord_info->damage_status = $request['damage_status'];
        $landlord_info->damage_detail = $request['damage_detail'];
        $landlord_info->tenant_status = $request['tenant_status'];
        $landlord_info->why_not = $request['why_not'];
        $landlord_info->moveout_status = $request['moveout_status'];
        $landlord_info->free_move_out_reason = $request['free_move_out_reason'];
        $landlord_info->tenant_status = $request['tenant_status'];
        $landlord_info->t_s_date = $request['t_s_date'];
        $landlord_info->t_e_date = $request['t_e_date'];
        $landlord_info->company_name = $request['company_name'];
        $landlord_info->position = $request['position'];
        $landlord_info->notes_text = optional($request)['notes_text'];
        $landlord_info->save();

        if (!empty($landlord_info->getChanges())) {
            event(new ReferenceEvent(
                $request['tenancy_id'],
                'Edit landlord',
                '',
                'Edit the landlord information',
                $old_landlord_info->getAttributes(),
                $landlord_info->getChanges(),
                $landlord_info->applicants->email
            ));
        }

        $timezone = Applicantbasic::find($landlord_info->applicants->applicant_id)->timezone;
        touchTenancy($request['tenancy_id'], $timezone);

        $this->lastStaffAction('Edit landlord information');
        return response()->json(['saved' => true]);
    }

    /**
     * Update the guarantor information.
     *
     * @param  \Illuminate\Http\GuarantorInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postGuarantorInfoUpdate(GuarantorInfoRequest $request)
    {
        $authAgencyId = authAgencyId();

        $old_guarantor_info = GuarantorReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();
        $guarantor_info = GuarantorReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();

        $guarantor_info->email = strtolower($request['email']);
        $guarantor_info->name = $request['name'];
        $guarantor_info->post_code = $request['post_code'];
        $guarantor_info->town = $request['town'];
        $guarantor_info->street = $request['street'];
        $guarantor_info->country = $request['country'];
        $guarantor_info->owner = $request['owner'];
        $guarantor_info->phone = (isset($request['phone']) && $request['phone']) ? $request['phone'] : ' ';
        $guarantor_info->guarantor_income = $request['guarantor_income'];
        $guarantor_info->country_code = $request['country_code'];
        $guarantor_info->owner = $request['owner'];
        $guarantor_info->relationship = $request['relationship'];
        $guarantor_info->occupation = $request['occupation'];
        $guarantor_info->employment_status = $request['employment_status'];
        $guarantor_info->company_name = $request['company_name'];
        $guarantor_info->company_address = $request['company_address'];
        $guarantor_info->hr_email = strtolower($request['hr_email']);
        $guarantor_info->least_income = $request['least_income'];
        $guarantor_info->notes_text = optional($request)['notes_text'];
        $guarantor_info->id_proof = $this->fileUploadHelperFunction("document",  null, $request['id_proof']);
        $guarantor_info->address_proof = $this->fileUploadHelperFunction("document",  null, $request['address_proof']);
        $guarantor_info->financial_proof = $this->fileUploadHelperFunction("document",  null, $request['financial_proof']);
        // $guarantor_info->other_document = $this->fileUploadArrayHelperFunction("document", null, $request['other_document']);
        $guarantor_info->signature = $this->fileUploadHelperFunction("signature",  null, $request['signaturePad']);
        $guarantor_info->save();
        $docData = $request['other_document'];
        if (empty($docData)) {
            $guarantor_info->guarantorRefOtherDocument()->delete();
        } else {
            foreach ($docData as $doc) {
                if (!empty($doc['id'])) {
                    $guarantorRefOtherDocument = GuarantorRefOtherDocument::findOrFail($doc['id']);
                    $guarantorRefOtherDocument->update([
                        'doc' => $this->fileUploadHelperFunction("document",  null, $doc['doc']),
                    ]);
                } else {
                    $guarantor_info->guarantorRefOtherDocument()->create([
                        'doc' => $this->fileUploadHelperFunction("document",  null, $doc['doc'])
                    ]);
                }
            }
        }
        if (!empty($guarantor_info->getChanges())) {
            event(new ReferenceEvent(
                $request['tenancy_id'],
                'Edit guarantor',
                '',
                'Edit the guarantor information',
                $old_guarantor_info->getAttributes(),
                $guarantor_info->getChanges(),
                $guarantor_info->applicants->email
            ));
        }

        $timezone = Applicantbasic::find($guarantor_info->applicants->applicant_id)->timezone;
        touchTenancy($request['tenancy_id'], $timezone);

        $this->lastStaffAction('Edit guarantor information');

        return response()->json(['saved' => true]);
    }

    /**
     * Update the employment information.
     *
     * @param  \Illuminate\Http\EmploymentInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmploymentInfoUpdate(EmploymentInfoRequest $request)
    {
        $authAgencyId = authAgencyId();
        $old_employment_info = EmploymentReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();
        $employment_info = EmploymentReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();

        if (isset($request['company_name']) && $request['company_name']) {
            $employment_info->company_name = $request['company_name'];
        } else {
            $employment_info->company_name = ' ';
        }

        if (isset($request['company_phone']) && $request['company_phone']) {
            $employment_info->company_phone = $request['company_phone'];
        } else {
            $employment_info->company_phone = ' ';
        }

        $employment_info->company_email =  strtolower($request['company_email']);
        $employment_info->country_code = $request['country_code'];
        $employment_info->company_address = $request['company_address'];
        $employment_info->job_title = $request['job_title'];
        $employment_info->contract_type = $request['contract_type'];
        $employment_info->probation_period = $request['probation_period'];
        $employment_info->annual_salary = (isset($request['annual_salary']) && $request['annual_salary']) ? $request['annual_salary'] : 0;
        $employment_info->annual_bonus = (isset($request['annual_bonus']) && $request['annual_bonus']) ? $request['annual_bonus'] : 0;
        $employment_info->name = $request['name'];
        $employment_info->position = $request['position'];
        $employment_info->notes_text = optional($request)['notes_text'];
        $employment_info->save();

        if (!empty($employment_info->getChanges())) {
            event(new ReferenceEvent(
                $request['tenancy_id'],
                'Edit employment',
                '',
                'Edit the employment information',
                $old_employment_info->getAttributes(),
                $employment_info->getChanges(),
                $employment_info->applicants->email
            ));
        }

        $timezone = Applicantbasic::find($employment_info->applicants->applicant_id)->timezone;
        touchTenancy($request['tenancy_id'], $timezone);

        $this->lastStaffAction('Edit employment information');

        return response()->json(['saved' => true]);
    }

    /**
     * Update the quaterly information.
     *
     * @param  \Illuminate\Http\QuarterlyInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postQuarterlyInfoUpdate(QuarterlyInfoRequest $request)
    {
        $authAgencyId = authAgencyId();

        $old_qu_info = QuarterlyReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();
        $qu_info = QuarterlyReference::where('agency_id', $authAgencyId)->where('id', $request['id'])->first();

        $qu_info->close_bal = $request['close_bal'];
        $qu_info->notes = optional($request)['notes'];
        $pd = $this->fileUploadArrayHelperFunction("document", null, $request['qu_doc'] ?? '');

        if ($pd == 'virus_file') {
            return ['virus_error' => true, 'type' => 'quarterly document'];
        } else {
            $qu_info->qu_doc = is_array($pd) ? $pd : [];
        }
        $qu_info->save();

        $type  = ($qu_info->type == 'fullterm') ? 'fullterm' : 'quarterly';

        if (!empty($qu_info->getChanges())) {
            event(new ReferenceEvent(
                $request['tenancy_id'],
                'Edit ' . ucfirst($type),
                '',
                'Edit the ' . $type . ' information',
                $old_qu_info->getAttributes(),
                $qu_info->getChanges(),
                $qu_info->applicants->email
            ));
        }

        $timezone = Applicantbasic::find($qu_info->applicants->applicant_id)->timezone;
        touchTenancy($request['tenancy_id'], $timezone);

        $this->lastStaffAction('Edit ' . $type . ' information');

        return response()->json(['saved' => true]);
    }

    /**
     * Update the reference address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postUpdateReferencesAddresses(Request $request)
    {
        $applicantInformation = Applicant::where('agency_id', authAgencyId())->findOrFail($request['applicant_id']);
        $address = json_decode($applicantInformation->addresses, true);
        $i =  $request['index'];
        if (array_key_exists($i, $address)) {
            $address[$i] = $request['addresses'];
            $applicantInformation->update(['addresses' => json_encode($address), 'addresses_text' => $request['addresses_text']]);
            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Add new reference address for an applicant by agency.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddNewAddresses(Request $request)
    {
        $app_info = Applicant::where('agency_id', authAgencyId())->findOrFail($request['applicant_id']);
        $existingJsonData = json_decode($app_info->addresses, true);
        $newAddress = $request['addresses'];
        $existingJsonData[] = $newAddress;
        $newJsonData = json_encode($existingJsonData);
        $app_info->addresses = $newJsonData;
        $app_info->addresses_text = $request['addresses_text'];
        $app_info->save();
        return response()->json(['saved' => true]);
    }

    /**
     * Delete reference address for an applicant by agency.
     *
     * @param  int $id
     * @param  int $index
     * @return \Illuminate\Http\Response
     */
    public function postDeleteAddresses($id, $index)
    {
        $app_info = Applicant::where('agency_id', authAgencyId())->findOrFail($id);
        $address = json_decode($app_info->addresses, true);
        if (array_key_exists($index, $address)) {
            unset($address[$index]);
            $app_info->update(['addresses' => json_encode($address)]);
            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Send custom email to applicant, landlord, guarantor, employment.
     *
     * @param  \Illuminate\Http\EmailSendRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postSendCustomEmail(EmailSendRequest $request)
    {
        $documentDetails = [];
        foreach ($request['document'] as $singleDocument) {
            $image_name = $this->file_upload($singleDocument["file"], "test", null);
            $documentDetails[] = ["name" => $singleDocument["name"], "file" => $image_name];
        }
        $emailString = "";
        $numItems = count($request['email']);
        $i = 0;
        foreach ($request['email'] as $key => $email) {

            $agencyData = agencyData();
            if ($email['code'] == 'Applicant' && $applicant = Applicantbasic::where('email', strtolower($email['email']))->first()) {

                $data = $this->textForSpecificAreaForCustomTemplate($request, $applicant, $applicant->tenancies, $agencyData, null, null, null, null, null, null);
            } else {
                $data = $this->textForSpecificAreaForCustomTemplate($request, null, null, $agencyData, null, null, null, null, null, null);
            }

            Mail::to($email['email'])->send(new CustomEmail($data, $agencyData, $documentDetails, $request));

            if ($i == ($numItems - 1) && $numItems > 1) {
                $emailString .= ' and ' . $email['email'];
            } elseif ($i == ($numItems - 2) || $numItems == 1) {
                $emailString .= $email['email'];
            } else {
                $emailString .= $email['email'] . ', ';
            }
            $i++;
        }

        foreach ($documentDetails as $key => $singleFile) $this->deleteFile("test", $singleFile["file"]);
        event(new SendEmailEvent($request['tenancy_id'], 'Custom email', 'Custom email sent to applicants, references and property landlord', $emailString, $request['message'], authAgencyId()));

        $timezone = Tenancy::find($request['tenancy_id'])->applicants->first()->applicantbasic->timezone;
        touchTenancy($request['tenancy_id'], $timezone);

        $this->lastStaffAction('Custom email send to the applicants, references and property landlord');
        return response()->json(['saved' => true]);
    }

    /**
     * Update quarterly reference information documents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postQuarterlyReferenceInfo(Request $request)
    {
        $re_status = $request['status'];
        $agencyData = agencyData();
        $app_info = Applicant::where('agency_id', $agencyData->id)
            ->where('id', $request['applicant_id'])
            ->with('applicantBasic')
            ->first();
        $tenancy = Tenancy::where('id', $app_info->tenancy_id)->first();

        $ref_info = QuarterlyReference::where('agency_id', $agencyData->id)->where('applicant_id', $request['applicant_id'])->firstOrFail();

        $type  = ($ref_info->type == 'fullterm') ? 'fullterm' : 'quarterly';

        if ($re_status == 3) {
            $actionData['message'] = 'The ' . $type . ' income proof has been declined';
        } elseif ($re_status == 4) {
            $actionData['message'] = 'The ' . $type . ' income proof has been accepted';
        } else {
            $actionData['message'] = 'We require further information about ' . $type . ' your income proof';
        }

        $this->reviewReferenceEvent($app_info, ucfirst($type), 'Review ' . $type . ' reference information', $re_status, $request['ref_text'], $app_info['applicantBasic']['email']);

        if ($ref_info->agency_status < 3) {
            $app_info->update(['ref_agency_status' => ($app_info->ref_agency_status + 1)]);
        }
        $ref_info->update([
            'agency_status' => $re_status,
            'decision_text' => $request['ref_text'],
            'reference_action' => $re_status
        ]);

        if ($re_status == 3 || $re_status == 5) {
            $tenancy->update(['status' => 4]);
        }

        if ($re_status == 4) {
            $tenancy->update(['status' => 3]);
            $this->checkAllTheReferencesStatusAndNotifyToClinet($app_info, $ref_info, $actionData, $re_status, ucfirst($type));
        }

        $timezone = Applicantbasic::find($app_info->applicant_id)->timezone;
        touchTenancy($app_info->tenancy_id, $timezone);

        $this->lastStaffAction('Review ' . $type . ' reference information');
        return response()->json(['saved' => true]);
    }

    /**
     * Update landlord reference information documents.
     *
     * @param  \Illuminate\Http\LandlordReferenceInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postLandlordReferenceInfo(LandlordReferenceInfoRequest $request)
    {
        $re_status = $request['status'];
        $agencyData = agencyData();
        $app_info = Applicant::where('agency_id', $agencyData->id)
            ->where('id', $request['applicant_id'])
            ->with('applicantBasic')
            ->first();

        $ref_info = LandlordReference::where('agency_id', $agencyData->id)->where('applicant_id', $request['applicant_id'])->where('id', $request['id'])->firstOrFail();
        $tenancy = Tenancy::where('id', $app_info->tenancy_id)->first();
        if ($re_status == 3) {
            $actionData['message'] = 'The landlord reference has been declined';
        } elseif ($re_status == 4) {
            $actionData['message'] = 'The landlord reference has been accepted';
        } else {
            $actionData['message'] = 'We require further information from your landlord';
        }
        $this->reviewReferenceEvent($app_info, 'Landlord', 'Review landlord reference information', $re_status, $request['ref_text'], $app_info['applicantBasic']['email']);
        if ($ref_info->agency_status < 3) {
            $app_info->update(['ref_agency_status' => ($app_info->ref_agency_status + 1)]);
        }
        $ref_info->update([
            'agency_status' => $re_status,
            'decision_text' => $request['ref_text'],
            'reference_action' => $re_status
        ]);

        if ($re_status == 3 || $re_status == 5) {
            $tenancy->update(['status' => 4]);
        }

        if ($re_status == 4) {
            $tenancy->update(['status' => 3]);
            $this->checkAllTheReferencesStatusAndNotifyToClinet($app_info, $ref_info, $actionData, $re_status, 'Landlord');
        }

        $timezone = Applicantbasic::find($app_info->applicant_id)->timezone;
        touchTenancy($app_info->tenancy_id, $timezone);

        $this->lastStaffAction('Review landlord reference information');
        return response()->json(['saved' => true]);
    }

    /**
     * Update employment reference information documents.
     *
     * @param  \Illuminate\Http\EmploymentReferenceInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmploymentReferenceInfo(EmploymentReferenceInfoRequest $request)
    {
        $agencyData = agencyData();
        $app_info = Applicant::where('agency_id', $agencyData->id)
            ->where('id', $request['applicant_id'])
            ->with('applicantBasic')
            ->first();

        $ref_info = EmploymentReference::where('agency_id', $agencyData->id)->where('applicant_id', $request['applicant_id'])->where('id', $request['id'])->firstOrFail();
        $tenancy = Tenancy::where('id', $app_info->tenancy_id)->first();

        $re_status = $request['status'];

        if ($re_status == 3) {
            $actionData['message'] = 'The employment reference has been declined';
        } elseif ($re_status == 4) {
            $actionData['message'] = 'The employment reference has been accepted';
        } else {
            $actionData['message'] = 'We require further information from your employer';
        }

        $this->reviewReferenceEvent($app_info, 'Employment', 'Review employment reference information', $re_status, $request['ref_text'], $app_info['applicantBasic']['email']);

        if ($ref_info->agency_status < 3) {
            $app_info->update(['ref_agency_status' => ($app_info->ref_agency_status + 1)]);
        }
        $ref_info->update([
            'agency_status' => $re_status,
            'decision_text' => $request['ref_text'],
            'reference_action' => $re_status
        ]);

        if ($re_status == 3 || $re_status == 5) {
            $tenancy->update(['status' => 4]);
        }

        if ($re_status == 4) {
            $tenancy->update(['status' => 3]);
            $this->checkAllTheReferencesStatusAndNotifyToClinet($app_info, $ref_info, $actionData, $re_status, 'Employment');
        }

        $timezone = Applicantbasic::find($app_info->applicant_id)->timezone;
        touchTenancy($app_info->tenancy_id, $timezone);

        $this->lastStaffAction('Review employment reference information');
        return response()->json(['saved' => true]);
    }

    /**
     * Update guarantor reference information documents.
     *
     * @param  \Illuminate\Http\GuarantorReferenceInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postGuarantorReferenceInfo(GuarantorReferenceInfoRequest $request)
    {
        $reason = '<p>' . $request['income_proof_text'] . '</p><p></p><p>' . $request['id_proof_text'] . '</p><p></p><p>' . $request['address_proof_text'] . '</p>';

        $agencyData = agencyData();
        $app_info = Applicant::where('agency_id', $agencyData->id)
            ->where('id', $request['applicant_id'])
            ->with('applicantBasic')
            ->first();
        $ref_info = GuarantorReference::where('agency_id', $agencyData->id)->where('applicant_id', $request['applicant_id'])->where('id', $request['id'])->with('guarantorRefOtherDocument')->firstOrFail();

        $tenancy = Tenancy::where('id', $app_info->tenancy_id)->first();

        $income_status = $request['income_proof_status'];
        $id_status = $request['id_proof_status'];
        $address_status = $request['address_proof_status'];
        $doc_status = [];
        foreach ($request['guarantor_ref_other_document'] as $doc) {
            $doc_status[]  = $doc['decision_action'];
        }
        if ($income_status == 5 || $id_status == 5 || $address_status == 5 || in_array(5, $doc_status)) {
            $ref_status = 5;
            $actionData['message'] = 'We require further information from your guarantor';
        } elseif ($income_status == 3 || $id_status == 3 || $address_status == 3 || in_array(3, $doc_status)) {
            $ref_status = 3;
            $actionData['message'] = 'The guarantor reference has been declined';
        } else {
            $ref_status = 4;
            $actionData['message'] = 'The guarantor reference has been accepted';
        }

        $this->reviewReferenceEvent($app_info, 'Guarantor', 'Review guarantor reference information', $ref_status, $reason, $app_info['applicantBasic']['email']);

        if ($ref_info->agency_status < 3) {
            $app_info->update(['ref_agency_status' => ($app_info->ref_agency_status + 1)]);
        }

        $ref_info->update([
            'agency_status' => $ref_status,
            'decision_income_text' => $request['income_proof_text'],
            'decision_id_text' => $request['id_proof_text'],
            'decision_address_text' => $request['address_proof_text'],
            'decision_income_action' => $income_status,
            'decision_id_action' => $id_status,
            'decision_address_action' => $address_status,
        ]);
        $docData = $request['guarantor_ref_other_document'];
        foreach ($docData as $doc) {
            $guarantorRefOtherDocument = GuarantorRefOtherDocument::findOrFail($doc['id']);
            $guarantorRefOtherDocument->update([
                'decision_action' => $doc['decision_action'],
                'decision_text' => $doc['decision_text']
            ]);
        }

        if ($ref_status == 3 || $ref_status == 5) {
            $tenancy->update(['status' => 4]);
        }

        if ($ref_status == 4) {
            $tenancy->update(['status' => 3]);
            $this->checkAllTheReferencesStatusAndNotifyToClinet($app_info, $ref_info, $actionData, $ref_status, 'Guarantor');
        }

        $timezone = Applicantbasic::find($app_info->applicant_id)->timezone;
        touchTenancy($app_info->tenancy_id, $timezone);

        $this->lastStaffAction('Review guarantor reference information');
        return response()->json(['saved' => true]);
    }

    /**
     * Add a new applicant to a tenancy.
     *
     * @param  \App\Http\Requests\AddNewApplicantRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddNewApplicantToTenancy(AddNewApplicantRequest $request)
    {
        $agencyData = agencyData();

        if ($agencyData->total_credit <= ($agencyData->used_credit + 1)) {
            return response()->json(['saved' => false, 'statusCode' => 2317, 'reason' => 'Your credit is less then']);
        }

        $app_info = Applicantbasic::where('agency_id',  $agencyData->id)->where('email', $request['applicant']['app_email'])->first();
        if ($app_info) {
            $today = Carbon::today();
            $tenancies = Tenancy::where('agency_id',  $agencyData->id)
                ->whereHas('applicants.applicantbasic', function ($query) use ($request) {
                    $query->where('email', strtolower($request['applicant']['app_email']));
                })
                ->whereNotIn('status', [9, 10])
                ->get();
            if ($tenancies) {
                foreach ($tenancies as $tenancy) {
                    if (
                        $tenancy->t_end_date >= $request['tenancyData']['t_start_date'] &&
                        $tenancy->t_start_date <= $request['tenancyData']['t_end_date']
                    ) {
                        return response()->json(['saved' => false, 'statusCode' => 2318, 'reason' => 'Application cannot proceed! ' . $app_info->app_name . ' has a Tenancy that Ends after the Start Date of this Tenancy.']);
                    }
                }
            }
        }

        $tenancy = Tenancy::where('id', $request['tenancy_id'])->where('agency_id', $agencyData->id)->firstOrFail();

        if (Applicant::where('agency_id', $agencyData->id)->where('tenancy_id', $request['tenancy_id'])->where('applicant_id', Applicantbasic::where('email', $request['applicant']['app_email'])->value('id'))->exists()) {
            return response()->json(['saved' => false, 'statusCode' => 2311, 'reason' => 'You are not smarter than me. You can not renew the existing applicant of this tenancy.']);
        }

        if ($tenancy->no_applicant < ($tenancy->applicants()->count() + 1)) {
            return response()->json(['saved' => false, 'statusCode' => 2318, 'reason' => 'You cant not add new applicant because tenancy has already full']);
        }

        if ($tenancy->type == 2) {
            $tenancy->update(['type' => 3]); //Part Renewal
        }
        $agencyData->increment('used_credit');

        $chasingSetting = Chasing::where('agency_id', $agencyData->id)->firstOrFail();

        $applicantBasic = app('App\Http\Controllers\TenancyController')->createOrUpdateNewApplicant($request['applicant'], $request, $agencyData, $pass = Str::random(15));

        $new_applicant = new Applicant();
        $new_applicant->tenancy_id = $tenancy->id;
        $new_applicant->applicant_id = $applicantBasic->id;
        $new_applicant->agency_id = authAgencyId();
        $new_applicant->creator_id = $tenancy->creator_id;
        $new_applicant->app_url = config('global.frontSiteUrl') . ('/applicant/initial_login?email=' . strtolower($request['applicant']['app_email']) . '&code=' . $pass);
        $new_applicant->status = 1;
        $new_applicant->last_response_time = timeChangeAccordingToTimezoneForChasing($request['tenancyData']['timezone'], $chasingSetting->response_time, $chasingSetting->stalling_time);
        $new_applicant->renew_status = $request['applicant']['app_renew_tenant'] > 0 ? 1 : 0;
        $new_applicant->save();

        event(new ApplicantAddDeleteEvent(
            ['tenancy_id' => $tenancy->id, 'email' => strtolower($request['applicant']['app_email'])],
            'Add applicant',
            'A new applicant was added to the tenancy',
            'add',
            $request['applicant']['app_email']
        ));

        $this->lastStaffAction('Add new applicant');

        if ($request['applicant']['app_renew_tenant'] > 0) {

            $data = $this->emailTemplateData('RTE', $applicantBasic, $new_applicant->tenancies, $agencyData, null, null, null, null, null, null, null);
            Mail::to(strtolower($request['applicant']['app_email']))->send(new RenewTenantEmail($data, $agencyData, $new_applicant));
            return response()->json(['saved' => true]);
        } else {

            $data = $this->emailTemplateData('RE', $applicantBasic, $new_applicant->tenancies, $agencyData, null, null, null, null, null, null, null);
            Mail::to(strtolower($request['applicant']['app_email']))->send(new RegistrationEmail($data, $agencyData, $new_applicant));

            return $this->reviewTenancyApplicant($request['tenancy_id']);
        }
    }

    /**
     * Add a new landlord to a tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddNewLandlordToTenancy(Request $request)
    {
        $app_id = $request['applicant_id'];
        $appInfo = Applicant::where('id', $app_id)->firstOrFail();
        $chasingSetting = Chasing::where('agency_id', $appInfo->agency_id)->firstOrFail();
        $timezone = 'UTC';
        $landlord_info = new LandlordReference(['agency_id' => $appInfo->agency_id, 'applicant_id' => $app_id]);

        $landlord_info->agency_id = authAgencyId();
        $landlord_info->email = strtolower($request['email']);
        $landlord_info->name = $request['name'];
        if (isset($request['phone']) && $request['phone']) {
            $landlord_info->phone = $request['phone'];
        } else {
            $landlord_info->phone = ' ';
        }
        $landlord_info->post_code = $request['post_code'];
        $landlord_info->town = $request['town'];
        $landlord_info->street = $request['street'];
        $landlord_info->country = $request['country'];
        $landlord_info->country_code = $request['country_code'];
        $landlord_info->rent_price_value = $request['rent_price_value'];
        $landlord_info->paid_status = $request['paid_status'];
        $landlord_info->frequent_status = $request['frequent_status'];
        $landlord_info->moveout_status = $request['moveout_status'];
        $landlord_info->tenant_status = $request['tenant_status'];
        $landlord_info->arrears_status = $request['arrears_status'];
        if ($request['arrears_status'] == 'Yes') {
            $landlord_info->paid_arrears = intval($request['paid_arrears_value']);
            $landlord_info->paid_arrears_value = $request['paid_arrears_value'];
        }
        $landlord_info->damage_status = $request['damage_status'];
        $landlord_info->damage_detail = $request['damage_detail'];
        $landlord_info->t_s_date = $request['t_s_date'];
        $landlord_info->t_e_date = $request['t_e_date'];
        $landlord_info->company_name = $request['company_name'];
        $landlord_info->position = $request['position'];
        $landlord_info->notes_text = optional($request)['notes_text'];
        $landlord_info->timezone = $timezone;
        $landlord_info->ref_link = $appInfo->renew_status == 0 ? config('global.frontSiteUrl') . ('/landlord/' . Str::random(8) . '/' . Str::random(15)) : null;
        $landlord_info->last_response_time = timeChangeAccordingToTimezoneForChasing($timezone, $chasingSetting->response_time, $chasingSetting->stalling_time);
        $landlord_info->fill_status = $appInfo->renew_status == 0 ? 0 : 1;
        $landlord_info->status = $appInfo->renew_status == 0 ? 1 : 2;
        $landlord_info->agency_status = 1;
        $landlord_info->save();
        $referenceCount = $appInfo->total_references;
        $appInfo->total_references = $referenceCount + 1;
        $appInfo->save();
        event(new LandlordAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => strtolower($request['email'])],
            'Add landlord',
            'A new landlord was added to the tenancy',
            'add',
            $appInfo->applicantbasic->email
        ));

        $this->lastStaffAction('Add new landlord');
        $agencyData = Agency::where('id', $appInfo->agency_id)->firstOrFail();
        $data = $this->emailTemplateData('LRE', $appInfo->applicantbasic, $appInfo->tenancies, $agencyData, null, null, null, $landlord_info, null, null, null);
        Mail::to($landlord_info['email'])->send(new LandlordReferenceEmail($data, $agencyData, $landlord_info, $appInfo));

        return response()->json(['saved' => true]);
    }

    /**
     * Delete a landlord from a tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postDeleteLandlordToTenancy(Request $request)
    {
        $authAgencyId = authAgencyId();
        $app_id = $request['applicant_id'];
        $appInfo = Applicant::where('id', $app_id)->firstOrFail();
        $landlord = LandlordReference::where('id', $request['landlord_id'])->where('agency_id', $authAgencyId)->where('applicant_id', $request['applicant_id'])->firstOrFail();
        event(new LandlordAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => $landlord->email],
            'Remove landlord',
            'Remove an landlord from the tenancy',
            'delete',
            $appInfo->applicantbasic->email
        ));
        $landlord->delete();
        $referenceCount = $appInfo->total_references;
        $appInfo->total_references = $referenceCount - 1;
        $appInfo->save();
        return response()->json(['saved' => true]);
    }

    /**
     * Add a new employment to a tenancy.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddNewEmploymentToTenancy(Request $request)
    {
        $app_id = $request['applicant_id'];
        $appInfo = Applicant::where('id', $app_id)->firstOrFail();
        $chasingSetting = Chasing::where('agency_id', $appInfo->agency_id)->firstOrFail();
        $timezone = 'UTC';
        $employment_info = new EmploymentReference(['agency_id' => $appInfo->agency_id, 'applicant_id' => $app_id]);

        if (isset($request['company_name']) && $request['company_name']) {
            $employment_info->company_name = $request['company_name'];
        } else {
            $employment_info->company_name = ' ';
        }

        if (isset($request['company_phone']) && $request['company_phone']) {
            $employment_info->company_phone = $request['company_phone'];
        } else {
            $employment_info->company_phone = '  ';
        }

        $employment_info->company_email =  strtolower($request['company_email']);
        $employment_info->country_code = $request['country_code'];
        $employment_info->company_address = $request['company_address'];
        $employment_info->job_title = $request['job_title'];
        $employment_info->contract_type = $request['contract_type'];
        $employment_info->probation_period = $request['probation_period'];
        $employment_info->annual_salary = (isset($request['annual_salary']) && $request['annual_salary']) ? $request['annual_salary'] : 0;
        $employment_info->annual_bonus = (isset($request['annual_bonus']) && $request['annual_bonus']) ? $request['annual_bonus'] : 0;
        $employment_info->name = $request['name'];
        $employment_info->position = $request['position'];
        $employment_info->timezone = $timezone;
        $employment_info->ref_link = config('global.frontSiteUrl') . ('/employment/' . Str::random(8) . '/' . Str::random(15));
        $employment_info->last_response_time = timeChangeAccordingToTimezoneForChasing($timezone, $chasingSetting->response_time, $chasingSetting->stalling_time);
        $employment_info->fill_status = 0;
        $employment_info->status = 1;
        $employment_info->agency_status = 1;
        $employment_info->notes_text = optional($request)['notes_text'];
        $employment_info->save();
        $referenceCount = $appInfo->total_references;
        $appInfo->total_references = $referenceCount + 1;
        $appInfo->save();
        event(new EmploymentAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => strtolower($request['company_email'])],
            'Add employment',
            'A new employment was added to the tenancy',
            'add',
            $appInfo->applicantbasic->email
        ));

        $agencyData = Agency::where('id', $appInfo->agency_id)->firstOrFail();
        $data = $this->emailTemplateData('ERE', $appInfo->applicantbasic, $appInfo->tenancies, $agencyData, null, $employment_info, null, null, null, null, null);
        Mail::to($employment_info['company_email'])->send(new EmploymentReferenceEmail($data, $agencyData, $employment_info, $appInfo));

        return response()->json(['saved' => true]);
    }

    /**
     * Delete a employment from a tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postDeleteEmploymentToTenancy(Request $request)
    {
        $authAgencyId = authAgencyId();
        $app_id = $request['applicant_id'];
        $appInfo = Applicant::where('id', $app_id)->firstOrFail();
        $employment = EmploymentReference::where('id', $request['employment_id'])->where('agency_id', $authAgencyId)->where('applicant_id', $request['applicant_id'])->firstOrFail();
        event(new EmploymentAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => $employment->email],
            'Remove employment',
            'Remove an employment from the tenancy',
            'delete',
            $appInfo->applicantbasic->email
        ));
        $employment->delete();
        $referenceCount = $appInfo->total_references;
        $appInfo->total_references = $referenceCount - 1;
        $appInfo->save();
        return response()->json(['saved' => true]);
    }

    /**
     * Add a new guarantor to a tenancy.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAddNewGuarantorToTenancy(Request $request)
    {
        $app_id = $request['applicant_id'];
        $appInfo = Applicant::where('id', $app_id)->firstOrFail();
        $chasingSetting = Chasing::where('agency_id', $appInfo->agency_id)->firstOrFail();
        $timezone = 'UTC';
        $guarantor_info = new GuarantorReference(['agency_id' => $appInfo->agency_id, 'applicant_id' => $app_id]);
        $guarantor_info->email = strtolower($request['email']);
        $guarantor_info->name = $request['name'];
        $guarantor_info->post_code = $request['post_code'];
        $guarantor_info->town = $request['town'];
        $guarantor_info->street = $request['street'];
        $guarantor_info->country = $request['country'];
        $guarantor_info->owner = $request['owner'];
        $guarantor_info->phone = (isset($request['phone']) && $request['phone']) ? $request['phone'] : ' ';
        $guarantor_info->guarantor_income = $request['guarantor_income'];
        $guarantor_info->country_code = $request['country_code'];
        $guarantor_info->owner = $request['owner'];
        $guarantor_info->relationship = $request['relationship'];
        $guarantor_info->occupation = $request['occupation'];
        $guarantor_info->employment_status = $request['employment_status'];
        $guarantor_info->company_name = $request['company_name'];
        $guarantor_info->company_address = $request['company_address'];
        $guarantor_info->hr_email = strtolower($request['hr_email']);
        $guarantor_info->least_income = $request['least_income'];
        $guarantor_info->notes_text = optional($request)['notes_text'];
        $guarantor_info->id_proof = $this->fileUploadHelperFunction("document",  null, $request['id_proof']);
        $guarantor_info->address_proof = $this->fileUploadHelperFunction("document",  null, $request['address_proof']);
        $guarantor_info->financial_proof = $this->fileUploadHelperFunction("document",  null, $request['financial_proof']);
        // $guarantor_info->other_document = $this->fileUploadArrayHelperFunction("document", null, $request['other_document']);
        $guarantor_info->signature = $this->fileUploadHelperFunction("signature",  null, $request['signaturePad']);
        $guarantor_info->fill_status = 0;
        $guarantor_info->status = 1;
        $guarantor_info->agency_status = 1;
        $guarantor_info->timezone = $timezone;
        $guarantor_info->ref_link = config('global.frontSiteUrl') . ('/guarantor/' . Str::random(8) . '/' . Str::random(15));
        $guarantor_info->last_response_time = timeChangeAccordingToTimezoneForChasing($timezone, $chasingSetting->response_time, $chasingSetting->stalling_time);
        $guarantor_info->save();
        $referenceCount = $appInfo->total_references;
        $appInfo->total_references = $referenceCount + 1;
        $appInfo->save();
        $docData = $request['other_document'];
        foreach ($docData as $doc) {
            $guarantor_info->guarantorRefOtherDocument()->create(
                ['doc' => $this->fileUploadHelperFunction("document",  null, $doc['doc'])]
            );
        }
        event(new GuarantorAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => strtolower($request['email'])],
            'Add guarantor',
            'A new guarantor was added to the tenancy',
            'add',
            $appInfo->applicantbasic->email
        ));

        $this->lastStaffAction('Add new guarantor');
        $agencyData = Agency::where('id', $appInfo->agency_id)->firstOrFail();
        $data = $this->emailTemplateData('GRE', $appInfo->applicantbasic, $appInfo->tenancies, $agencyData, null, null, $guarantor_info, null, null, null, null);
        Mail::to($guarantor_info['email'])->send(new GuarantorReferenceEmail($data, $agencyData, $guarantor_info, $appInfo));

        return response()->json(['saved' => true]);
    }

    /**
     * Delete a guarantor from a tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postDeleteGuarantorToTenancy(Request $request)
    {
        $authAgencyId = authAgencyId();
        $app_id = $request['applicant_id'];
        $appInfo = Applicant::where('id', $app_id)->firstOrFail();
        $guarantor = GuarantorReference::where('id', $request['guarantor_id'])->where('agency_id', $authAgencyId)->where('applicant_id', $request['applicant_id'])->firstOrFail();
        event(new GuarantorAddDeleteEvent(
            ['tenancy_id' => $appInfo->tenancies->id, 'email' => $guarantor->email],
            'Remove guarantor',
            'Remove an guarantor from the tenancy',
            'delete',
            $appInfo->applicantbasic->email
        ));
        $guarantor->guarantorRefOtherDocument()->delete();
        $guarantor->delete();
        $referenceCount = $appInfo->total_references;
        $appInfo->total_references = $referenceCount - 1;
        $appInfo->save();
        return response()->json(['saved' => true]);
    }

    /**
     * Change the tenancy negotiator for a specific tenancy.
     *
     * @param  \Illuminate\Http\ChangeTenancyNegotiatorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function changeTenancyNegotiator(ChangeTenancyNegotiatorRequest $request)
    {
        if (agencyAdmin() || $this->tenancyNegotiator()) {

            $authAgencyId = authAgencyId();
            $tenancy = Tenancy::where('id', $request['tenancy_id'])->where('agency_id', $authAgencyId)->firstOrFail();
            $staffMember = User::where('agency_id', $authAgencyId)->where('id', $request['staff_id'])->firstOrFail();

            if (!empty($tenancy) && !empty($staffMember)) {
                $tenancy->applicants()->update(['creator_id' => $request['staff_id']]);
                $tenancy->update(['creator_id' => $request['staff_id']]);

                $this->lastStaffAction('Change tenancy negotiator');
                return response()->json(['saved' => true]);
            }
            return response()->json(['saved' => false]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Delete a applicant from a tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postDeleteApplicantToTenancy(DeleteApplicantRequest $request)
    {
        if ((agencyAdmin() || $this->deleteApplicant()) && $applicant = Applicant::where('id', $request['id'])->where('agency_id', authAgencyId())->where('tenancy_id', $request['tenancy_id'])->first()) {

            event(new ApplicantAddDeleteEvent(
                ['tenancy_id' => $request['tenancy_id'], 'email' => $applicant->applicantbasic->email],
                'Remove applicant',
                'Remove an applicant from the tenancy',
                'delete',
                $applicant->applicantbasic->email
            ));

            $this->deleteSingleApplicant($applicant);

            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Review the tenancy agreement.
     *
     * @param  \Illuminate\Http\ReviewAgreementRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function reviewTenancyAgreement(ReviewAgreementRequest $request)
    {
        if ((agencyAdmin() || $this->reviewTenancy()) && $tenancy = Tenancy::where('id', $request['tenancy_id'])->where('agency_id', authAgencyId())->firstOrFail()) {

            if ($tenancy->review_agreement == 1) return response()->json(['saved' => true, 'statusCode' => 2320, 'reason' => 'You have already reviewed the agreement. Thank you']);

            if ($tenancy->review_agreement == 0) {

                $reviewer =  authUserData();
                $this->deleteFile('agreement_signature', $reviewer->agreement_signature);
                $image_name = $this->file_upload($request['agreement_signature'], "agreement_signature", null);
                $reviewer->update(['agreement_signature' => $image_name, 'signing_time' => timeChangeAccordingToTimezone($request['timezone']), 'ip_address' => $_SERVER['REMOTE_ADDR']]);
                // $this->deleteFile('agreement', $tenancy->agreement);
                $tenancy->update(['reviewer_id' => $reviewer->id, 'review_agreement' => 1]);

                $request['signing_date'] = $tenancy->signing_date;
                $request['generated_date'] = $tenancy->generated_date;
                $textCode = $request['text_code'];
                $paymentSchedules = [];
                foreach ($tenancy->applicants as $applicant) {
                    $applicantName = $applicant->applicantbasic->app_name . ' ' . $applicant->applicantbasic->l_name ?? '';
                    $paymentSchedules[$applicantName] = $applicant->paymentSchedule()->get()->toArray();
                }
                $this->agreementCreateHelper($this->textForSpecificAreaForAgreement($textCode, $tenancy, agencyData(), null, null, null, null, $request, $paymentSchedules), $request, null, null);
                unset($tenancy['signing_date']);
                unset($tenancy['generated_date']);

                if ($tenancy->agreement_type == 'new') {
                    $tenancy->update(['status' =>  11, 'renew_tenancy' => 1]); //tenancy status = Completed (Triggered when the Tenancy Agreement is signed by all applicants)
                    $tenancy->properties->update(['status' => 5]); //property status = Let  (Triggered as soon as the associated tenancy status changes to ‘Let’)
                    $tenancy->creator->notify(new TenancyNotification($tenancy, 11));
                }
                if ($tenancy->agreement_type == 'terminate') {
                    $tenancy->update(['status' => 10]);
                    $this->checkForPropertyStatus($tenancy);
                    $tenancy->creator->notify(new TenancyNotification($tenancy, 10));
                }
                if ($tenancy->agreement_type == 'extend') {
                    $tenancy->update(['status' => 7]);
                    $tenancy->creator->notify(new TenancyNotification($tenancy, 7));
                }
                $agencyData = agencyData();
                $timezone = '';
                foreach ($tenancy->applicants as $applicant) {

                    $data = $this->emailTemplateData('AFE', $applicant->applicantbasic, $tenancy, $agencyData, null, null, null, null, null, null, null);
                    Mail::to($applicant->applicantbasic->email)->send(new ApplicationFinilizeEmail($data, $agencyData));
                    $applicant->applicantbasic->notify(new ApplicationCompleteNotification(0, $tenancy));
                    if ($tenancy->agreement_type == 'terminate') {
                        $applicant->update(['review_agreement' => 1, 'status' => 6]);
                    } else {
                        $applicant->update(['review_agreement' => 1, 'status' => 7]); //Application Complete
                    }
                    $timezone = $applicant->applicantbasic->timezone;
                }

                touchTenancy($request['tenancy_id'], $timezone);

                foreach ($tenancy->applicants as $applicant) {
                    event(new AgreementEvent(
                        $tenancy->id,
                        'Agreement review',
                        'Agreement reviewed for this tenancy',
                        $applicant->applicantbasic->email
                    ));
                }
            }
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false, 'statusCode' => 2319, 'reason' => 'You do not have permission to review tenancy agreement']);
    }

    /**
     * Pause chasing emails for an applicant for a specified number of days.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pauseChasingEmails(Request $request)
    {
        $appInfo = Applicant::where('id', $request['applicant_id'])->where('agency_id', authAgencyId())->firstOrFail();
        $day = $request['number_of_days'];
        $appInfo->update([
            'is_paused' => true,
            'pause_start_date' => Carbon::today(),
            'pause_end_date' => Carbon::today()->addDays($day),
        ]);
        return response()->json(['saved' => true]);
    }

    /**
     * Resume chasing emails for an applicant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resumeChasingEmails(Request $request)
    {
        $appInfo = Applicant::where('id', $request['applicant_id'])->where('agency_id', authAgencyId())->firstOrFail();
        $appInfo->update([
            'is_paused' => false,
            'pause_start_date' => null,
            'pause_end_date' => null,
        ]);
        return response()->json(['saved' => true]);
    }

    /**
     * Resend an applicant email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendApplicantEmail(Request $request)
    {
        $app_info = Applicant::where('id', $request['applicant_id'])->with(['applicantbasic'])->firstOrFail();
        $agencyData = Agency::where('id', $app_info->agency_id)->firstOrFail();
        $data = $this->emailTemplateData('RE', $app_info->applicantbasic, $app_info->tenancies, $agencyData, null, null, null, null, null, null, null);
        Mail::to(strtolower($app_info->applicantbasic['email']))->send(new RegistrationEmail($data, $agencyData, $app_info));

        event(new ResendEmailEvent(
            ['tenancy_id' => $app_info->tenancies->id, 'email' => $app_info->applicantbasic['email']],
            'Resend Email to Applicant',
            'Resend Email to Applicant references',
            $app_info->applicantbasic['email']
        ));
        return response()->json(['saved' => true]);
    }

    /**
     * Resend an guarantor email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendGuarantorEmail(Request $request)
    {
        $app_info = Applicant::where('id', $request['applicant_id'])->firstOrFail();
        $gu_info = GuarantorReference::where('id', $request['guarantor_id'])->where('agency_id', $app_info->agency_id)->where('applicant_id', $request['applicant_id'])->first();
        $agencyData = Agency::where('id', $app_info->agency_id)->firstOrFail();
        $data = $this->emailTemplateData('GRE', $app_info->applicantbasic, $app_info->tenancies, $agencyData, null, null, $gu_info, null, null, null, null);
        Mail::to($gu_info['email'])->send(new GuarantorReferenceEmail($data, $agencyData, $gu_info, $app_info->applicantbasic));
        event(new ResendEmailEvent(
            ['tenancy_id' => $app_info->tenancies->id, 'email' => $gu_info['email']],
            'Resend Email to Guarantor',
            'Resend Email to Guarantor references',
            $app_info->applicantbasic['email']
        ));
        return response()->json(['saved' => true]);
    }

    /**
     * Resend an employment email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendEmploymentEmail(Request $request)
    {
        $app_info = Applicant::where('id', $request['applicant_id'])->firstOrFail();
        $emp_info = EmploymentReference::where('id', $request['employment_id'])->where('agency_id', $app_info->agency_id)->where('applicant_id', $request['applicant_id'])->first();
        $agencyData = Agency::where('id', $app_info->agency_id)->firstOrFail();
        $data = $this->emailTemplateData('ERE', $app_info->applicantbasic, $app_info->tenancies, $agencyData, null, $emp_info, null, null, null, null, null);
        Mail::to($emp_info['company_email'])->send(new EmploymentReferenceEmail($data, $agencyData, $emp_info, $app_info->applicantbasic));
        event(new ResendEmailEvent(
            ['tenancy_id' => $app_info->tenancies->id, 'email' => $emp_info['company_email']],
            'Resend Email to Employment',
            'Resend Email to Employment references',
            $app_info->applicantbasic['email']
        ));
        return response()->json(['saved' => true]);
    }

    /**
     * Resend an landlord email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendLandlordEmail(Request $request)
    {
        $app_info = Applicant::where('id', $request['applicant_id'])->firstOrFail();
        $landlord_info = LandlordReference::where('id', $request['landlord_id'])->where('agency_id', $app_info->agency_id)->where('applicant_id', $request['applicant_id'])->first();
        $agencyData = Agency::where('id', $app_info->agency_id)->firstOrFail();
        $data = $this->emailTemplateData('LRE', $app_info->applicantbasic, $app_info->tenancies, $agencyData, null, null, null, $landlord_info, null, null, null);
        Mail::to($landlord_info['email'])->send(new LandlordReferenceEmail($data, $agencyData, $landlord_info, $app_info->applicantbasic));
        event(new ResendEmailEvent(
            ['tenancy_id' => $app_info->tenancies->id, 'email' => $landlord_info['email']],
            'Resend Email to Landlord',
            'Resend Email to Landlord references',
            $app_info->applicantbasic['email']
        ));
        return response()->json(['saved' => true]);
    }

    /**
     * Retrieve interim inspections based on specified criteria.
     *
     * @param string $authAgencyId
     * @param string $id
     * @param bool $isDone
     * @param string|null $comparisonOperator
     * @param string|null $comparisonValue
     * @return \Illuminate\Http\Response
     */
    private function getInterimInspections($authAgencyId, $id, $isDone, $comparisonOperator = null, $comparisonValue = null)
    {
        $currentMonthFormatted = now()->format('F Y');

        $query = InterimInspection::where('agency_id', $authAgencyId)
            ->where('tenancy_id', $id)
            ->where('is_done', $isDone);

        if ($comparisonOperator && $comparisonValue) {
            $query->whereRaw("TO_DATE(inspection_month, 'Month YYYY') $comparisonOperator TO_DATE(?, 'Month YYYY')", [$comparisonValue]);
        }

        $inspections = $query->get();
        $tenancy = Tenancy::where('id', $id)->with(['properties', 'landlords:id,f_name,l_name,email'])->firstOrFail();
        $mm = EmailTemplate::where('agency_id', $authAgencyId)->where('mail_code', 'II')->first();

        $inspectionData = $inspections->map(function ($inspection) use ($tenancy) {
            $inspectionArray = $inspection->toArray();
            $inspectionArray['landlord_email'] = $tenancy->landlords->email;
            return $inspectionArray;
        });

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $inspectionData = $inspectionData->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingInterimInspectionVariables[request('sort_by')]) ? $this->sortingInterimInspectionVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);
        $data = $inspectionData->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return ['data' => $data, 'total' => $inspectionData->count(), 'mail_template' => $mm];
    }
    /**
     * Retrieve all interim inspections for the current month.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInterimInspection($id)
    {
        $authAgencyId = authAgencyId();
        return $this->getInterimInspections($authAgencyId, $id, false, '=', now()->format('F Y'));
    }

    /**
     * Retrieve all past interim inspections.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPastInterimInspection($id)
    {
        $authAgencyId = authAgencyId();
        return $this->getInterimInspections($authAgencyId, $id, false, '<', now()->format('F Y'));
    }

    /**
     * Retrieve all completed interim inspections.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDoneInterimInspection($id)
    {
        $authAgencyId = authAgencyId();
        return $this->getInterimInspections($authAgencyId, $id, true);
    }

    public function intrimInspection($id)
    {
        $current = $this->getInterimInspection($id);
        $past = $this->getPastInterimInspection($id);
        $done = $this->getDoneInterimInspection($id);
        return [
            "saved" => true,
            "current_month" => $current,
            "past_month" => $past,
            "done" => $done,
        ];
    }

    /**
     * Update the interim inspection details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function actionInterimInspection(Request $request)
    {
        $authAgencyId = authAgencyId();
        $inspection = InterimInspection::where('agency_id', $authAgencyId)
            ->where('tenancy_id', $request['tenancy_id'])
            ->where('id', $request['inspection_id'])
            ->firstOrFail();

        $inspectionDate = Carbon::createFromFormat('F Y', $inspection->inspection_month);
        if ($inspectionDate->isCurrentMonth() || $inspectionDate->lt(now()->startOfMonth())) {
            $inspection->update([
                'is_done' => true,
                'inspection_date' => dateFormat($request['inspection_date']),
                'comment' => $request['comment']
            ]);
            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false, 'error' => 'Your interim inspection month is ' . $inspection->inspection_month]);
        }
    }

    /**
     * Download applicant references as a PDF.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadApplicantRefrences($id)
    {
        $data = Applicant::where('id', $id)
            ->with(['applicantbasic', 'employmentReferences', 'guarantorReferences', 'quarterlyReferences', 'landlordReferences', 'studentReferences', 'paymentSchedule'])->firstOrFail();
        $tenancy_info = Tenancy::where('id', $data['tenancy_id'])->first();
        $applicants = $tenancy_info->applicants()->with('applicantBasic')->get();
        $agency = agencyDataFormId($tenancy_info->agency_id);
        try {
            $pdf = PDF::loadView('Pdf.applicantReferences', [
                'agency' => $agency, 'data' => $data, 'tenancyInfo' => $tenancy_info,   'applicants' => $applicants,  'applicant_privacy_statement' => $this->textForSpecificArea('AILPS', $data, $tenancy_info, $agency, null, null, null, null, null),
            ]);
            $filename = 'document_' . uniqid() . '.pdf';
            $path = 'public/pdfs/' . $filename;
            Storage::put($path, $pdf->output());
            $url = config('global.backSiteUrl') . Storage::url($path);
            return response()->json(['saved' => true, 'pdf_url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['saved' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Add agreement type in tenancy history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addAgreementTypeInTenancyHistory(Request $request)
    {
        $tenancy = Tenancy::where('agency_id', authAgencyId())->where('id', $request['tenancy_id'])->first();
        if (!$tenancy) {
            return response()->json(['saved' => false, 'error' => 'Tenancy not found']);
        }
        $agreementType = $request['agreement_type'] === 'extend' ? 'extend' : 'terminate';
        $tenancy->agreement_type = $agreementType;
        $tenancy->status = 17;
        $tenancy->agreement = '';
        $tenancy->save();
        $tenancyHistory = new TenancyHistory();
        $tenancyHistory->tenancy_id = $tenancy->id;
        $tenancyHistory->agency_id = authAgencyId();
        $tenancyHistory->agreement_type = $agreementType;
        $tenancyHistory->save();

        return response()->json(['saved' => true]);
    }
}
