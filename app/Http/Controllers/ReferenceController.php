<?php

namespace App\Http\Controllers;

use PDF;
use Mail;
use App\Models\Agency;
use App\Models\Property;
use App\Models\EmploymentRequirement;
use App\Models\GuarantorRequirement;
use App\Http\Requests\Reference\EmploymentReferenceRequest;
use App\Http\Requests\Reference\GuarantorReferenceRequest;
use App\Http\Requests\Reference\LandlordReferenceRequest;
use App\Models\LandlordRequirement;
use App\Notifications\Agency\ReferenceCompletedFormNotification;
use App\Notifications\Applicant\ApplicantNotification;
use App\Models\Tenancy;
use Carbon\Carbon;
use App\Models\Applicant;
use App\Models\EmploymentReference;
use App\Models\GuarantorReference;
use App\Models\LandlordReference;
use App\Mail\EmploymentReferenceEmail;
use App\Mail\GuarantorReferenceEmail;
use App\Mail\LandlordReferenceEmail;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile;
use App\Traits\TextForSpecificAreaTrait;
use App\Traits\TenancyApplicantIdsHelperTrait;
use App\Notifications\TenancyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ReferencesAddressesTrait;


class ReferenceController extends Controller
{
    use AllPermissions, WorkWithFile, TextForSpecificAreaTrait, ReferencesAddressesTrait, TenancyApplicantIdsHelperTrait;

    /**
     * Get guarantor form data for filling.
     *
     * @param string $first The first part of the URL.
     * @param string $second The second part of the URL.
     * @return \Illuminate\Http\JsonResponse The JSON response containing guarantor form data.
     */
    public function getGuarantorForm($first, $second)
    {
        $gu_info = GuarantorReference::where('ref_link', config('global.frontSiteUrl') . ('/guarantor/' . $first . '/' . $second))->first();

        if (!empty($gu_info) && $gu_info->fill_status == 0) {
            if ($gu_info->applicant->tenancies->status == 10) return response()->json(['saved' => false, 'statusCode' => 2323, 'reason' => 'Your tenancy application is cancelled']);
            $applicantInformation = $gu_info->applicant;
            $agency = agencyDataFormId($applicantInformation->agency_id);
            return response()
                ->json([
                    'saved' => true,
                    'agency' => $agency,
                    'applicant' => $applicantInformation = $gu_info->applicant,
                    'applicant_basic' => $applicantInformation->applicantbasic,
                    'tenancy' => Tenancy::where('id', $applicantInformation->tenancy_id)->first(),
                    'guarantor_info' => $gu_info,
                    'text_for_specific_area' => $this->textForSpecificArea('GRTFSA', $applicantInformation, $applicantInformation->tenancies, $agencyData = agencyDataFormId($applicantInformation->agency_id), null, null, $gu_info, null, null),
                    'terms_and_condtion' => $this->textForSpecificArea('GRTAC', $applicantInformation, $applicantInformation->tenancies, $agencyData, null, null, $gu_info, null, null),
                    'thank_you_page' => $this->textForSpecificArea('GRTYP', $applicantInformation, $applicantInformation->tenancies, $agencyData, null, null, $gu_info, null, null),
                    'gua_req' => GuarantorRequirement::where('agency_id', $applicantInformation->agency_id)->first(['must_be_18', 'living_in_uk', 'three_time_salary'])
                ]);
        } else {
            return response()->json(['saved' => false]);   //404 page or filled form already
        }
    }

    /**
     * Store guarantor information submitted via the form.
     *
     * @param \App\Http\Requests\GuarantorReferenceRequest $request The request containing guarantor data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function postGuarantorStoreInfo(GuarantorReferenceRequest $request)
    {
        $applicant_info = Applicant::where('id', $request['applicant_id'])->firstOrFail();
        if ($guarantor_info = GuarantorReference::where('applicant_id', $request['applicant_id'])->where('id', $request['guarantor_id'])->first()) {
            $guarantor_info->name = $request['guarantor_name'];
            $guarantor_info->tenancy_id = $applicant_info->tenancy_id;
            $guarantor_info->post_code = $request['post_code'];
            $guarantor_info->town = $request['town'];
            $guarantor_info->street = $request['street'];
            $guarantor_info->country = $request['country'];
            $guarantor_info->owner = $request['owner'];
            $guarantor_info->relationship = $request['applicant_relationship'];
            $guarantor_info->occupation = $request['guarantor_occupation'];
            $guarantor_info->employment_status = $request['is_employed'];
            $guarantor_info->guarantor_income = $request['guarantor_income'];
            $guarantor_info->is_eighteen = $request['is_eighteen'];
            $guarantor_info->is_living_uk = $request['is_living_uk'];

            if ($request['is_employed'] == "Yes") {
                $guarantor_info->company_name = $request['company_name'];
                $guarantor_info->company_address = $request['company_address'];
                $guarantor_info->hr_email = $request['company_hr_email'];
                $guarantor_info->least_income = $request['least_income'];
            }

            $guarantor_info->fill_date = now()->toDateString();
            $guarantor_info->id_proof = $this->fileUploadHelperFunction("document",  null, $request['id_proof']);
            $guarantor_info->address_proof = $this->fileUploadHelperFunction("document",  null, $request['address_proof']);
            $guarantor_info->financial_proof = $this->fileUploadHelperFunction("document",  null, $request['financial_proof']);
            // $guarantor_info->other_document =  $this->fileUploadArrayHelperFunction("document", null, $request['other_document']);
            $guarantor_info->signature = $this->fileUploadHelperFunction("signature",  null, $request['signaturePad']);
            $guarantor_info->fill_status = 1;
            $guarantor_info->status = 2; //complete
            $guarantor_info->agency_status = 1; //agency review is pending
            $guarantor_info->ref_link = null;
            $guarantor_info->agency_id = $applicant_info->agency_id;
            $guarantor_info->timezone = $request['timezone'];
            $guarantor_info->save();
            $docData = $request['other_document'];
            foreach ($docData as $doc) {
                $guarantor_info->guarantorRefOtherDocument()->create(
                    ['doc' => $this->fileUploadHelperFunction("document",  null, $doc['doc'])]
                );
            }
            $applicant_info->applicantbasic->notify(new ApplicantNotification($applicant_info->tenancies, $applicant_info, $guarantor_info, 'Guarantor'));  //notify applicant
            $applicant_info->tenancies->creator->notify(new ReferenceCompletedFormNotification($applicant_info->tenancies, $applicant_info, $guarantor_info, 'Guarantor'));

            $this->setStatus($applicant_info);
            $this->checkAllTheReferencesFormFill($applicant_info);
            touchTenancy($applicant_info->tenancy_id, $applicant_info->applicantbasic->timezone);

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Generate PDF of guarantor information.
     *
     * @param \Illuminate\Http\Request $request The request containing applicant and guarantor IDs.
     * @return \Illuminate\Http\JsonResponse The JSON response containing PDF URL.
     */
    public function getGuarantorInfoPdf(Request $request)
    {
        $applicant_info = Applicant::where('id', $request['applicant_id'])
            ->with(['applicantbasic', 'guarantorReferences'])->firstOrFail();
        $tenancy_info = Tenancy::where('id', $applicant_info['tenancy_id'])->first();
        $agency = agencyDataFormId($tenancy_info->agency_id);
        $data = GuarantorReference::where('applicant_id', $applicant_info['id'])
            ->where('id', $request['guarantor_id'])->with('guarantorRefOtherDocument')
            ->first();
        try {
            $pdf = PDF::loadView('Pdf.guarantorInfo', [
                'agency' => $agency, 'data' => $data, 'applicantInfo' => $applicant_info, 'tenancyInfo' => $tenancy_info,
                'terms_and_condtion' => $this->textForSpecificArea('GRTAC', $applicant_info, $applicant_info->tenancies, $agency, null, null, $data, null, null),
            ]);
            $filename = 'document_' . uniqid() . '.pdf';
            $path = 'public/pdfs/' . $filename;
            Storage::put($path, $pdf->output());
            $url = config('global.backSiteUrl') . Storage::url($path);
            return response()->json(['pdf_url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get employment form data for filling.
     *
     * @param string $first The first part of the URL.
     * @param string $second The second part of the URL.
     * @return \Illuminate\Http\JsonResponse The JSON response containing guarantor form data.
     */
    public function getEmploymentForm($first, $second)
    {
        $employment_info = EmploymentReference::where('ref_link',  config('global.frontSiteUrl') . ('/employment/' . $first . '/' . $second))->first();
        if (!empty($employment_info) && $employment_info->fill_status == 0) {

            if ($employment_info->applicant->tenancies->status == 10)
                return response()->json(['saved' => false, 'statusCode' => 2323, 'reason' => 'Your tenancy application is cancelled']);

            $applicantInformation = $employment_info->applicant;
            $emp_req = EmploymentRequirement::where('agency_id', $applicantInformation->agency_id)
                ->first(['probation_period', 'three_time_salary', 'contract']);
            $agency = agencyDataFormId($applicantInformation->agency_id);
            return response()
                ->json([
                    'saved' => true,
                    'agency' => $agency,
                    'applicant' => $applicantInformation,
                    'applicant_basic' => $applicantInformation->applicantbasic,
                    'emp_info' => $employment_info,
                    'emp_req' => $emp_req,
                    'text_for_specific_area' => $this->textForSpecificArea('ERTFSA', $applicantInformation, $applicantInformation->tenancies, agencyDataFormId($applicantInformation->agency_id), null, $employment_info, null, null, null),
                ]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Store employment information submitted via the form.
     *
     * @param \App\Http\Requests\GuarantorReferenceRequest $request The request containing guarantor data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function postEmploymentStoreInfo(EmploymentReferenceRequest $request)
    {
        $employment_info = EmploymentReference::where('applicant_id', $request['applicant_id'])->where('id', $request['employment_id'])->first();

        if ($employment_info && $employment_info->fill_status == 0) {

            $applicant_info = Applicant::where('id', $request['applicant_id'])->first();
            $employment_info->company_name = $request['company_name'];
            $employment_info->tenancy_id = $applicant_info->tenancy_id;
            $employment_info->company_address = $request['company_address'];
            $employment_info->job_title = $request['job_title'];
            $employment_info->probation_period = $request['probation_period'];
            $employment_info->contract_type = $request['contract_type'];
            $employment_info->annual_salary = $request['annual_salary'];
            $employment_info->annual_bonus = $request['annual_bonus'];
            $employment_info->name = $request['your_name'];
            $employment_info->position = $request['landlord_position'];
            $employment_info->fill_date = Carbon::now()->toDateString();
            $employment_info->signature = $this->fileUploadHelperFunction("signature",  null, $request['signaturePad']);
            $employment_info->fill_status = 1;
            $employment_info->status = 2; //complete
            $employment_info->agency_status = 1; //agency review is pending
            $employment_info->ref_link = null;
            $employment_info->agency_id = $applicant_info->agency_id;
            $employment_info->timezone = $request['timezone'];
            $employment_info->save();

            $applicant_info->applicantbasic->notify(new ApplicantNotification($applicant_info->tenancies, $applicant_info, $employment_info, 'Employment'));
            $applicant_info->tenancies->creator->notify(new ReferenceCompletedFormNotification($applicant_info->tenancies, $applicant_info, $employment_info, 'Employment'));

            $this->setStatus($applicant_info);
            $this->checkAllTheReferencesFormFill($applicant_info);
            touchTenancy($applicant_info->tenancy_id, $applicant_info->applicantbasic->timezone);

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Generate PDF of employment information.
     *
     * @param \Illuminate\Http\Request $request The request containing applicant and guarantor IDs.
     * @return \Illuminate\Http\JsonResponse The JSON response containing PDF URL.
     */
    public function getEmploymentInfoPdf(Request $request)
    {
        $applicant_info = Applicant::where('id', $request['applicant_id'])
            ->with(['applicantbasic', 'employmentReferences'])->firstOrFail();
        $tenancy_info = Tenancy::where('id', $applicant_info['tenancy_id'])->first();
        $agency = agencyDataFormId($tenancy_info->agency_id);
        $data = EmploymentReference::where('applicant_id', $request['applicant_id'])->where('id', $request['employment_id'])->first();
        try {
            $pdf = PDF::loadView('Pdf.employmentInfo', [
                'agency' => $agency, 'data' => $data, 'applicantInfo' => $applicant_info, 'tenancyInfo' => $tenancy_info,
                'text_for_specific_area' => $this->textForSpecificArea('ERTFSA', $applicant_info, $applicant_info->tenancies, $agency, null, $data, null, null, null),
            ]);
            $filename = 'document_' . uniqid() . '.pdf';
            $path = 'public/pdfs/' . $filename;
            Storage::put($path, $pdf->output());
            $url = config('global.backSiteUrl') . Storage::url($path);
            return response()->json(['pdf_url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get landlord form data for filling.
     *
     * @param string $first The first part of the URL.
     * @param string $second The second part of the URL.
     * @return \Illuminate\Http\JsonResponse The JSON response containing guarantor form data.
     */
    public function getLandlordForm($first, $second)
    {
        $landlord_info = LandlordReference::where('ref_link', config('global.frontSiteUrl') . ('/landlord/' . $first . '/' . $second))->first();

        if (!empty($landlord_info) && $landlord_info->fill_status == 0) {

            if ($landlord_info->applicant->tenancies->status == 10) return response()->json(['saved' => false, 'statusCode' => 2323, 'reason' => 'Your tenancy application is cancelled']);

            $applicantInformation = $landlord_info->applicant;
            $agency = agencyDataFormId($applicantInformation->agency_id);
            $property_info = Property::where('id', $applicantInformation->tenancies->property_id)->first();

            return response()
                ->json([
                    'saved' => true,
                    'agency' => $agency,
                    'applicant' => $applicantInformation,
                    'applicant_basic' => $applicantInformation->applicantbasic,
                    'property_info' => $property_info->only('id', 'property_ref', 'street', 'town', 'country', 'post_code'),
                    'land_info' => $landlord_info,
                    'land_req' => LandlordRequirement::where('agency_id', $applicantInformation->agency_id)->first(['paid_rent', 'damage', 'move_out', 'recommended_tenant']),
                    'text_for_specific_area' => $this->textForSpecificArea('LRTFSA', $applicantInformation, $applicantInformation->tenancies, agencyDataFormId($applicantInformation->agency_id), null, null, null, $landlord_info, null),
                ]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Store landlord information submitted via the form.
     *
     * @param \App\Http\Requests\GuarantorReferenceRequest $request The request containing guarantor data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function postLandlordStoreInfo(LandlordReferenceRequest $request)
    {
        $applicant_info = Applicant::where('id', $request['applicant_id'])->first();

        if ($landlord_info = LandlordReference::where('applicant_id', $request['applicant_id'])->where('id', $request['landlord_id'])->first()) {

            $rental_amount = intval($request['rental_amount']);
            if ($rental_amount <= 0 || $rental_amount > 1000000) {
                return response()->json(['saved' => false, 'error' => 'Invalid rental amount.']);
            }
            $landlord_info->name = $request['landlord_agent_name'];
            $landlord_info->tenancy_id = $applicant_info->tenancy_id;
            $landlord_info->rent_price = $rental_amount;
            $landlord_info->rent_price_value = $request['rental_amount'];
            $landlord_info->paid_status = $request['rent_paid'];
            if ($request['rent_paid'] == 'No') {
                $landlord_info->frequent_status = $request['late_by'];
            }
            $landlord_info->arrears_status = $request['arrears'];
            if ($request['arrears'] == 'Yes') {
                $arrears_amount = intval($request['arrears_amount']);
                if ($arrears_amount <= 0 || $arrears_amount > 1000000) {
                    return response()->json(['saved' => false, 'error' => 'Invalid arrears amount.']);
                }
                $landlord_info->paid_arrears = $arrears_amount;
                $landlord_info->paid_arrears_value = $request['arrears_amount'];
            }
            $landlord_info->damage_status = $request['damage'];
            if ($request['damage'] == 'Yes') {
                $landlord_info->damage_detail = $request['details_damage'];
            }
            $landlord_info->tenant_status = $request['tenants'];
            if ($request['tenants'] == 'No') {
                $landlord_info->why_not = $request['why_not'];
            }
            $landlord_info->post_code = $request['post_code'];
            $landlord_info->town = $request['town'];
            $landlord_info->street = $request['street'];
            $landlord_info->country = $request['country'];
            $landlord_info->moveout_status = $request['free_move_out'];
            $landlord_info->free_move_out_reason = $request['free_move_out_reason'];
            $landlord_info->t_s_date = $request['starting_date'];
            $landlord_info->t_e_date = $request['ending_date'];
            $landlord_info->company_name = $request['company_name'];
            $landlord_info->position = $request['landlord_position'];
            $landlord_info->fill_date = Carbon::now()->toDateString();
            $landlord_info->signature = $this->fileUploadHelperFunction("signature",  null, $request['signaturePad']);
            $landlord_info->fill_status = 1;
            $landlord_info->status = 2;    //complete
            $landlord_info->agency_status = 1;  //agency review is pending
            $landlord_info->ref_link = null;
            $landlord_info->agency_id = $applicant_info->agency_id;
            $landlord_info->timezone = $request['timezone'];
            $landlord_info->save();

            $applicant_info->applicantbasic->notify(new ApplicantNotification($applicant_info->tenancies, $applicant_info, $landlord_info, 'Landlord'));
            $applicant_info->tenancies->creator->notify(new ReferenceCompletedFormNotification($applicant_info->tenancies, $applicant_info, $landlord_info, 'Landlord'));

            $this->setStatus($applicant_info);
            $this->checkAllTheReferencesFormFill($applicant_info);
            touchTenancy($applicant_info->tenancy_id, $applicant_info->applicantbasic->timezone);

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Generate PDF of landlord information.
     *
     * @param \Illuminate\Http\Request $request The request containing applicant and guarantor IDs.
     * @return \Illuminate\Http\JsonResponse The JSON response containing PDF URL.
     */
    public function getLandlordInfoPdf(Request $request)
    {
        $applicant_info = Applicant::where('id', $request['applicant_id'])
            ->with(['applicantbasic', 'landlordReferences'])->firstOrFail();
        $tenancy_info = Tenancy::where('id', $applicant_info['tenancy_id'])->first();
        $agency = agencyDataFormId($tenancy_info->agency_id);
        $data = LandlordReference::where('applicant_id', $request['applicant_id'])->where('id', $request['landlord_id'])->first();

        try {
            $pdf = PDF::loadView('Pdf.landlordInfo', [
                'agency' => $agency, 'data' => $data, 'applicantInfo' => $applicant_info, 'tenancyInfo' => $tenancy_info,
                'text_for_specific_area' => $this->textForSpecificArea('LRTFSA', $applicant_info, $applicant_info->tenancies, $agency, null, null, null, $data, null),
            ]);
            $filename = 'document_' . uniqid() . '.pdf';
            $path = 'public/pdfs/' . $filename;
            Storage::put($path, $pdf->output());
            $url = config('global.backSiteUrl') . Storage::url($path);
            return response()->json(['pdf_url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update status based on the number of filled references and notify creator if all references are filled.
     *
     * @param \App\Applicant $applicant_info The applicant model instance.
     * @return void
     */
    public function setStatus($applicant_info)
    {
        $tenancy = Tenancy::findOrFail($applicant_info->tenancy_id);

        if ($applicant_info->total_references == ($applicant_info->fill_references + 1)) {
            $applicant_info->update(['ref_status' => 1, 'status' => 3, 'fill_references' => $applicant_info->total_references]);  //Reference returned
        } else {
            $applicant_info->increment('fill_references');
        }

        if ($tenancy->applicants->where('ref_status', '>', 0)->count() == $tenancy->applicants->count()) {
            $tenancy->creator->notify(new TenancyNotification($tenancy, 2));
        }
    }
}
