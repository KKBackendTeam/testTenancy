<?php

namespace App\Traits;

trait ApplicantTrait
{
    public function referencesChangesOrNot($applicantInformation, $request, $referenceInformation)
    {
        $renewTenantReferenceStatus = 0;
        if ($applicantInformation->renew_status == 1 && $referenceInformation) {
            $refrenceForm = $request['reference_form'];
            $databaseEmailVariableName = 'email';

            if ($refrenceForm == 'guarantor_form') {
                $code = 'G';
                $requestEmailName = 'g_email';
            } elseif ($refrenceForm == 'employment_form') {
                $code = 'E';
                $requestEmailName = 'manage_email';
                $databaseEmailVariableName = 'company_email';
            } elseif ($refrenceForm == 'landlord_form') {
                $code = 'L';
                $requestEmailName = 'll_email';
            } elseif ($refrenceForm == 'quarterly_form') {
                $code = 'Q';
            } else {
                $code = null;
            }

            if ($code == 'Q' || $referenceInformation[$databaseEmailVariableName] == $request[$refrenceForm][$requestEmailName]) {
                $renewTenantReferenceStatus = 1;  //we can not send email to references
            } else {
                $referenceInformation->delete();  //and remove previous references and update applicant status
                $renewTenantReferenceStatus = 0;   //send, and work as usual
            }
        }
        return $renewTenantReferenceStatus;
    }
}
