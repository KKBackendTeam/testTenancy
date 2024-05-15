<?php

namespace App\Http\Controllers;

use App\Models\ApplicantRequirement;
use App\Models\EmploymentRequirement;
use App\Models\GuarantorRequirement;
use App\Models\TenancyRequirement;
use App\Models\LandlordRequirement;
use App\Http\Requests\Requirement\ApplicantRequirementRequest;
use App\Http\Requests\Requirement\EmploymentRequirementRequest;
use App\Http\Requests\Requirement\GuarantorRequirementRequest;
use App\Http\Requests\Requirement\LandlordRequirementRequest;
use App\Http\Requests\Requirement\TenancyRequirementRequest;
use App\Traits\AllPermissions;
use App\Traits\ConfigrationTrait;
use App\Models\QuarterlyRequirement;
use App\Http\Requests\Requirement\QuarterlyRequirementRequest;

class RequirementController extends Controller
{
    use AllPermissions, ConfigrationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getRequirements()
    {
        $agencyId = authAgencyId();
        $lr = !is_null($lr_data = $this->landlordRequirement($agencyId)) ? $lr_data : ['id' => 0];   //id=0 means the agency does not set their requirement yet.
        $er = !is_null($er_data = $this->employmentRequirement($agencyId)) ? $er_data : ['id' => 0];
        $gr = !is_null($gr_data = $this->guarantorRequirement($agencyId)) ? $gr_data : ['id' => 0];
        $tr = !is_null($tr_data = $this->tenancyRequirement($agencyId)) ? $tr_data : ['id' => 0];
        $qr = !is_null($qr_data = $this->quarterlyRequirement($agencyId)) ? $qr_data : ['id' => 0];
        $ar = !is_null($ar_data = $this->applicantRequirement($agencyId)) ? $ar_data : ['id' => 0];
        $cs = !is_null($cs_data = $this->chasingSetting($agencyId)) ? $cs_data : ['id' => 0];

        return response()
            ->json([
                'saved' => true, 'landlordRequirement' => $lr, 'employmentRequirement' => $er,
                'guarantorRequirement' => $gr, 'quarterlyRequirement' => $qr, 'tenancyRequirement' => $tr, 'applicantRequirement' => $ar, 'chasing' => $cs
            ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getLandlordRequirement()
    {
        return response()->json(['saved' => true, 'landlordRequirement' => $this->landlordRequirement(authAgencyId())]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getEmploymentRequirement()
    {
        return response()->json(['saved' => true, 'employmentRequirement' => $this->employmentRequirement(authAgencyId())]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getApplicantRequirement()
    {
        return response()->json(['saved' => true, 'applicantRequirement' => $this->applicantRequirement(authAgencyId())]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getTenancyRequirement()
    {
        return response()->json(['saved' => true, 'tenancyRequirement' => $this->tenancyRequirement(authAgencyId())]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getGuarantorRequirement()
    {
        return response()->json(['saved' => true, 'guarantorRequirement' => $this->guarantorRequirement(authAgencyId())]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getQuarterlyRequirement()
    {
        return response()->json(['saved' => true, 'quarterlyRequirement' => $this->quarterlyRequirement(authAgencyId())]);
    }

    /**
     * Update an existing LandlordRequirement or create a new LandlordRequirement if not found.
     *
     * @param  \Illuminate\Http\LandlordRequirementRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function postLandlordRequirement(LandlordRequirementRequest $request)
    {
        $agencyId = authAgencyId();
        $lr = LandlordRequirement::updateOrCreate(['agency_id' => $agencyId], addAgencyIdInRequest($request, $agencyId));
        return response()->json(['saved' => true, 'landlordRequirement' => $lr]);
    }

    /**
     * Update an existing EmploymentRequirement or create a new EmploymentRequirement if not found.
     *
     * @param  \Illuminate\Http\EmploymentRequirementRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function postEmploymentRequirement(EmploymentRequirementRequest $request)
    {
        $agencyId = authAgencyId();
        $er = EmploymentRequirement::updateOrCreate(['agency_id' => authAgencyId()], addAgencyIdInRequest($request, $agencyId));
        return response()->json(['saved' => true, 'employmentRequirement' => $er]);
    }

    /**
     * Update an existing GuarantorRequirement or create a new GuarantorRequirement if not found.
     *
     * @param  \Illuminate\Http\GuarantorRequirementRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function postGuarantorRequirement(GuarantorRequirementRequest $request)
    {
        $agencyId = authAgencyId();
        $gr = GuarantorRequirement::updateOrCreate(['agency_id' => authAgencyId()], addAgencyIdInRequest($request, $agencyId));
        return response()->json(['saved' => true, 'guarantorRequirement' => $gr]);
    }

    /**
     * Update an existing TenancyRequirement or create a new TenancyRequirement if not found.
     *
     * @param  \Illuminate\Http\TenancyRequirementRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function postTenancyRequirement(TenancyRequirementRequest $request)
    {
        $agencyId = authAgencyId();
        $tr = TenancyRequirement::updateOrCreate(['agency_id' => $agencyId], addAgencyIdInRequest($request, $agencyId));
        return response()->json(['saved' => true, 'tenancyRequirement' => $tr]);
    }

    /**
     * Update an existing QuarterlyRequirementRequest or create a new QuarterlyRequirementRequest if not found.
     *
     * @param  \Illuminate\Http\QuarterlyRequirementRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function postQuarterlyRequirement(QuarterlyRequirementRequest $request)
    {
        $agencyId = authAgencyId();
        $tr = QuarterlyRequirement::updateOrCreate(['agency_id' => $agencyId], addAgencyIdInRequest($request, $agencyId));
        return response()->json(['saved' => true, 'quarterlyRequirement' => $tr]);
    }

    /**
     * Update an existing ApplicantRequirement or create a new ApplicantRequirement if not found.
     *
     * @param  \Illuminate\Http\ApplicantRequirementRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function postApplicantRequirement(ApplicantRequirementRequest $request)
    {
        $agencyId = authAgencyId();
        $tr = ApplicantRequirement::updateOrCreate(['agency_id' => $agencyId], addAgencyIdInRequest($request, $agencyId));
        return response()->json(['saved' => true, 'applicantRequirement' => $tr]);
    }
}
