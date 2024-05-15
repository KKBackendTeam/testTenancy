<?php

namespace App\Traits;

trait DeleteDuplicateReferencesTrait
{
    public function checkReferenceIfExistsThenRemove($applicantInformation)
    {
        $applicantInformation->employmentReferences()->delete();
        $applicantInformation->guarantorReferences()->delete();
        $applicantInformation->landlordReferences()->delete();
        $applicantInformation->quarterlyReferences()->delete();
    }
}
