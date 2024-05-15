<?php

namespace App\Traits;

use App\Models\LandlordRequirement;
use App\Models\EmploymentRequirement;
use App\Models\GuarantorRequirement;
use App\Models\TenancyRequirement;
use App\Models\ApplicantRequirement;
use App\Models\Chasing;
use App\Models\Agency;
use App\Models\FinancialConfiguration;
use App\Models\QuarterlyRequirement;

trait ConfigrationTrait
{
    public function landlordRequirement($id)
    {
        return LandlordRequirement::where('agency_id', $id)->first();
    }

    public function employmentRequirement($id)
    {
        return EmploymentRequirement::where('agency_id', $id)->first();
    }

    public function guarantorRequirement($id)
    {
        return GuarantorRequirement::where('agency_id', $id)->first();
    }

    public function quarterlyRequirement($id)
    {
        return QuarterlyRequirement::where('agency_id', $id)->first();
    }

    public function tenancyRequirement($id)
    {
        return TenancyRequirement::where('agency_id', $id)->first();
    }

    public function applicantRequirement($id)
    {
        return ApplicantRequirement::where('agency_id', $id)->first();
    }

    public function chasingSetting($id)
    {
        return Chasing::where('agency_id', $id)->first();
    }

    public function agencyCredit()
    {
        return Agency::where('id', authAgencyId())->firstOrFail(['total_credit', 'used_credit']);
    }

    public function financialConfiguration()
    {
        return FinancialConfiguration::where('agency_id', authAgencyId())->first();
    }

    public function agencyInformation()
    {
        return Agency::where('id', authAgencyId())->first();
    }
}
