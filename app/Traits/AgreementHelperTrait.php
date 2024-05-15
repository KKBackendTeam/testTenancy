<?php

namespace App\Traits;

use PDF;
use App\Models\Tenancy;
use App\Models\TenancyHistory;
use Storage;
use Illuminate\Support\Str;

trait AgreementHelperTrait
{
    public function agreementCreateHelper($jsonData, $requestData, $dataFor, $singleApplicant)
    {
        $jsonData = array_pad($jsonData, 4, null);
        $tenancy_id = $requestData['tenancy_id'] ?? '';
        $terminateClause = $requestData['terminate_clause'] ?? '';
        $pdf = PDF::loadView(
            'Agreement.tenancy_agreement_2',
            [
                'tenancy' => $tenancyObject = $this->tenancyAgreementFullDataHelperFunction($tenancy_id, $dataFor, $singleApplicant),
                'agreementFirst' => $jsonData[0],
                'agreementSecond' => $jsonData[1],
                'agreementThird' => $jsonData[2],
                'agreementForth' => $jsonData[3],
                'signing_date' => $tenancyObject->signing_date,
                'terminateClause' => $terminateClause
            ]
        );

        $agreement_name = $tenancyObject->reference . '_' . Str::random(8) . '_agreement.pdf';
        Storage::put('public/agency/agreement/' . $agreement_name, $pdf->output());
        $tenancyObject->update(['agreement' => $agreement_name]);
        if($requestData['tenancy_history_id']) {
            $tenancyHistory = TenancyHistory::where('id', $requestData['tenancy_history_id'])
                                            ->where('tenancy_id', $requestData['tenancy_id'])
                                            ->firstOrFail();
            $tenancyHistory->update([
                'agreement' => $tenancyObject->agreement,
                'signing_date' => $requestData['signing_date'],
                'text_code' => $requestData['text_code'],
                'generated_date' => $requestData['generated_date'],
                'terminated_date' => $requestData['terminated_date'] ?? null,
            ]);
        }

        return true;
    }


    public function agreementCreateHelperForApplicant($jsonData, $requestData, $dataFor, $singleApplicant)
    {
        $tenancy_id = $requestData['tenancy_id'] ?? '';
        $pdf = PDF::loadView(
            'Agreement.tenancy_agreement_3',
            [
                'tenancy' => $tenancyObject = $this->tenancyAgreementFullDataHelperFunction($tenancy_id, $dataFor, $singleApplicant),
                'agreementFirst' => $jsonData[0],
                'agreementSecond' => $jsonData[1],
                'agreementThird' => $jsonData[2],
                'agreementForth' => $jsonData[3],
                'signing_date' => $tenancyObject->signing_date
            ]
        );

        $agreement_name = $tenancyObject->reference . '_' . Str::random(8) . '_agreement.pdf';
        Storage::put('public/agency/agreement/' . $agreement_name, $pdf->output());

        $tenancyObject->update(['agreement' => $agreement_name]);
        if($requestData['tenancy_history_id']) {
            $tenancyHistory = TenancyHistory::where('id', $requestData['tenancy_history_id'])
                                            ->where('tenancy_id', $requestData['tenancy_id'])
                                            ->firstOrFail();
            $tenancyHistory->update([
                'agreement' => $tenancyObject->agreement,
            ]);
        }

        return true;
    }

    public function tenancyAgreementFullDataHelperFunction($id, $dataFor, $singleApplicant)
    {
        $tenancy = Tenancy::where('agency_id', authAgencyId())
            ->with(['landlords'])
            ->with(['applicants.employmentReferences'])
            ->with(['applicants.guarantorReferences'])
            ->with(['applicants.landlordReferences'])
            ->with(['applicants.studentReferences'])
            ->with(['reviewer'])
            ->findOrFail($id);
        return $tenancy;
    }
}
