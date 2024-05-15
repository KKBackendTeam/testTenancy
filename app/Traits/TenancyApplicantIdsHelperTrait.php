<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Events\ReviewReferenceEvent;
use App\Notifications\Applicant\AgencyActionOnReferenceNotification;
use App\Models\Applicant;
use App\Notifications\TenancyNotification;

trait TenancyApplicantIdsHelperTrait
{
    public function tenancyApplicantsHelper($tenancy, $newApplicantsIds)
    {
        return implode(',', array_merge(explode(',', $tenancy->applicants_ids), $newApplicantsIds));
    }

    public function changeApplicantStatus($requestStatus, $tenancy)
    {
        switch ($requestStatus) {
            case '3':
                $tenancy->applicants()->update(['status' => 5]);
                break;
            case '8':
                $tenancy->applicants()->update(['status' => 7]);
                break;
            default:
                return null;
                break;
        }
        return true;
    }

    public function checkNoOfApplicants($tenancy, $noOfApplicant)
    {
        if ($noOfApplicant > $tenancy->properties->bedroom * 2) {
            return 1;
        } else if ($noOfApplicant < $tenancy->applicants()->count()) {
            return 2;
        } else {
            return 0;
        }
    }

    public function statusCheckBeforeTenancyEdit($tenancy, $newStatus)
    {
        if ($tenancy->status == $newStatus) {
            return true;
        } elseif ($newStatus == 10) {
            return true;
        } elseif (in_array($tenancy->status, [11]) && in_array($newStatus, [7])) {
            return true;
        } elseif (in_array($tenancy->status, [17]) && in_array($newStatus, [5])) {
            return true;
        } else {
            return false;
        }
    }

    public function reviewReferenceEvent($applicantInformation, $reference, $description, $referenceStatus, $referenceDetail, $applicantEmail)
    {
        event(new ReviewReferenceEvent(
            $applicantInformation->tenancy_id,
            $applicantInformation->applicantbasic->app_name . ' - Review ' . $reference . ' reference',
            $description,
            $referenceStatus,
            $referenceDetail,
            $applicantEmail
        ));
        return true;
    }

    public function tenancyValidation($request, $actionFor, $authAgencyId, $tenancyRequirements)
    {
        $first = $end = '|';
        if ($tenancyRequirements->start_month) $first = '|first_date_of_month|';
        if ($tenancyRequirements->end_month) $end = '|end_date_of_month|';
        $dateMonthDifference = Carbon::parse($request['t_start_date'])->diff(Carbon::parse($request['t_end_date'])->addDay());
        $totalMonths = $dateMonthDifference->y * 12 + $dateMonthDifference->m;

        $rules = [
            'pro_address' => 'required',
            'parking' => 'required|numeric',
            'monthly_amount' => 'required|numeric',
            'deposite_amount' => 'required|numeric',
            'holding_amount' => 'required|numeric',
            'landlord_id' => 'required',
            't_start_date' => 'bail|required|before:t_end_date' . $first,
            'no_applicant' => 'required|integer|between:1,20'
        ];
        if ($request['type'] == 1) {
            $rules['t_end_date'] = 'bail|required|after:t_start_date' . $end . '|month_length:' . $tenancyRequirements->tenancy_max_length . ',' . $totalMonths;
        }

        if ($actionFor == "new") $rules += ['property_id' => 'required|exists:properties,id', 'applicants.*' => 'required'];
        if ($actionFor == "edit") $rules += ['id' => 'required||exists:tenancies,id'];
        if (!empty($request['restrictionArray'])) $rules += ['restrictionArray' => 'required|array'];

        $validator = validator($request, $rules);
        return $validator;
    }

    public function checkAllTheReferencesStatusAndNotifyToClinet($applicantData, $referenceData, $data, $actionStatus, $referenceType)
    {
        $applicantData->applicantbasic->notify(new AgencyActionOnReferenceNotification($applicantData->tenancies, $referenceData, $applicantData, $data['message'], $referenceType));

        $applicants = Applicant::where('tenancy_id', $applicantData->tenancy_id)
            ->with(['employmentReferences', 'guarantorReferences', 'quarterlyReferences', 'landlordReferences'])
            ->oldest()->get();

        $allReferences = 0;
        $acceptedReferences = 0;
        $isChanged = true;
        foreach ($applicants as $app) {
            if ($app->log_status == 0) $isChanged = false;
            $allReferences += $app->total_references;
            if (!empty($app['employmentReferences'])){
                foreach ($app['employmentReferences'] as $employmentReference) {
                    if($employmentReference['agency_status'] == 4) {
                       $acceptedReferences++;
                   }
               }
            }
            if (!empty($app['guarantorReferences'])){
                foreach ($app['guarantorReferences'] as $guarantorReference) {
                    if($guarantorReference['agency_status'] == 4) {
                       $acceptedReferences++;
                   }
               }
            }
            if (!empty($app['landlordReferences'])){
                foreach ($app['landlordReferences'] as $landlordReference) {
                    if($landlordReference['agency_status'] == 4) {
                        $acceptedReferences++;
                    }
                }
            }
            if (!empty($app['quarterlyReferences'])){
                foreach ($app['quarterlyReferences'] as $quarterlyReference) {
                    if($quarterlyReference['agency_status'] == 4) {
                       $acceptedReferences++;
                   }
               }
            }
            if (!empty($app['quarterlyReferences'])){
                foreach ($app['quarterlyReferences'] as $quarterlyReference) {
                    if($quarterlyReference['agency_status'] == 4) {
                       $acceptedReferences++;
                   }
               }
            }
        }
        if ($isChanged && $allReferences != 0 && $acceptedReferences != 0 && $allReferences == $acceptedReferences) {
            $this->changeStatusOfTAP($applicantData);
        }

        return true;
    }

    public function changeStatusOfTAP($applicantData)
    {
        $applicantData->tenancies->update(['status' => 17]);
        $applicantData->tenancies->creator->notify(new TenancyNotification($applicantData->tenancies, 17));
        return true;
    }

    public function checkAllTheReferencesFormFill($applicantData)
    {
        $applicants = Applicant::where('tenancy_id', $applicantData->tenancy_id)
            ->with(['employmentReferences', 'guarantorReferences', 'quarterlyReferences', 'landlordReferences'])
            ->oldest()->get();
        $totalApplicants = $applicants->count();
        $totalReferences = 0;
        $acceptedReferences = 0;
        $isChanged = true;
        foreach ($applicants as $app) {
            if ($app->log_status == 0) $isChanged = false;
            $totalReferences += $app->employmentReferences->count() +
                                $app->guarantorReferences->count() +
                                $app->landlordReferences->count();
            if (!empty($app['employmentReferences'])){
                foreach ($app['employmentReferences'] as $employmentReference) {
                    if($employmentReference['ref_link'] == null && $employmentReference['fill_status'] == 1) {
                       $acceptedReferences++;
                   }
               }
            }
            if (!empty($app['guarantorReferences'])){
                foreach ($app['guarantorReferences'] as $guarantorReference) {
                    if($guarantorReference['ref_link'] == null && $guarantorReference['fill_status'] == 1) {
                       $acceptedReferences++;
                   }
               }
            }
            if (!empty($app['landlordReferences'])){
                foreach ($app['landlordReferences'] as $landlordReference) {
                    if($landlordReference['ref_link'] == null && $landlordReference['fill_status'] == 1) {
                        $acceptedReferences++;
                    }
                }
            }
        }
        if ($isChanged &&  $acceptedReferences != 0 && $acceptedReferences == $totalReferences) {
            $this->changeStatusOfTenancy($applicantData);
        }

        return true;
    }

    public function changeStatusOfTenancy($applicantData)
    {
        $applicantData->tenancies->update(['status' => 3]);
        $applicantData->tenancies->creator->notify(new TenancyNotification($applicantData->tenancies, 3));
        return true;
    }

    public function checkForPropertyStatus($tenancy)
    {
        $tenancyCount = $tenancy->properties->tenancies()->count();
        $propertyPreviousStatus = $tenancy->properties->previous_status;
        if ($tenancyCount == 1) {
            $tenancy->properties()->update(['status' => $propertyPreviousStatus = 1, 'available_from' => now()]);
        }

        if ($tenancyCount > 1 && in_array($tenancy->status, [2, 17, 5, 18, 7])) {

            if ($tenancy->status == 11) $tenancy->properties()->update(['status' => 1, 'available_from' => now()]);
            elseif ($tenancy->t_start_date >= now()) {
                $tenancy->properties()->update(['status' => $propertyPreviousStatus == 1 ? 1 : 3, 'available_from' => $tenancy->t_start_date]);
            }
        }
        return true;
    }

    public function paymentSchedule($scheduleData)
    {
        $schedule = [];
        if (is_array($scheduleData)) {
            foreach ($scheduleData as $payment) {
                $schedule[] = [
                    'date' => $payment['date'] ?? null,
                    'rental_amount' => $payment['rental_amount'] ?? null,
                ];
            }
        }

        return json_encode($schedule);
    }
}
