<?php

namespace App\Traits;

use App\Models\TextForSpecificArea;
use Carbon\Carbon;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;

trait TextForSpecificAreaTrait
{
    public $arrayVaribales = [
        'applicant.firstname' => 'app_name',
        'applicant.lastname' => 'l_name',
        'applicant.email' => 'email',
        'applicant.password' => 'pass',
        'paymentSchedule.date' => 'date',
        'paymentSchedule.amount' => 'amount',
        'tenancy.reference' => 'reference',
        'property.address' => 'pro_address',
        'tenancy.restriction' => 'restriction',
        'tenancy.rent_include' => 'rent_include',
        'tenancy.parking' => 'parking',
        'tenancy.parking_cost' => 'parking_cost',
        'tenancy.start_date' => 't_start_date',
        'tenancy.end_date' => 't_end_date',
        'tenancy.monthly_amount' => 'monthly_amount',
        'tenancy.deposit_amount' => 'deposite_amount',
        'tenancy.holding_amount' => 'holding_amount',
        'tenancy.total_applicant' => 'no_applicant',
        'tenancy.negotiator_name' => 'name',   //name and l_name comes form User table (creator Name) and I don't check it
        'tenancy.total_rent' => 'total_rent',
        'agency.name' => 'name',
        'agency.email' => 'email',
        'user.firstname' => 'name',
        'user.lastname' => 'l_name',
        'user.email' => 'email',
        'user.otp' => 'otp',
        'staff.firstname' => 'name',
        'staff.lastname' => 'l_name',
        'staff.email' => 'email',
        'staff.password' => 'password',
        'staff.mobile' => 'mobile',
        'employment.company_name' => 'company_name',
        'employment.company_email' => 'company_email',
        'employment.company_phone' => 'company_phone',
        'guarantor.name' => 'name',
        'guarantor.email' => 'email',
        'guarantor.phone' => 'phone',
        'landlord.name' => 'name',
        'landlord.email' => 'email',
        'landlord.phone' => 'phone',
        'superadmin.name' => 'name',
        'superadmin.email' => 'email',
        'reference.name' => 'name',  //reference name in the NMDE
        'credit' => 'credit',  //for credit request
        'days' => 'days', //Extend deadline_days for tenancy
        'today_date' => 'today_date',
        'signing_date' => 'signing_date',
        'generated_date' => 'generated_date',
        'reference.need_more_detail_message' => 'need_more_detail_message'
    ];

    public $parkingStatusArray = ['', 'Yes', 'No'], $parkingArray = ['no' => 2, 'yes' => 1];

    public function emailTemplateData($code, $applicantData, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, $quarterlyData, $superAdminData, $requestData)
    {
        $agencyId = (in_array($code, ['SA_ARE', 'SA_CRE', 'SA_PRE'])) ? $superAdminData->id : $agencyData->id;
        $textFor = EmailTemplate::where('mail_code', $code)->where('agency_id', $agencyId)->first();

        if ($textFor !== null) {
            $data = json_decode($textFor->data, true);

            if ($data !== null) {
                return $this->replaceVaribleToStringHelperFunction($data['header']['text'], $applicantData, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, $quarterlyData, $superAdminData, $requestData);
            } else {
                // If JSON decoding fails
                // Log an error or set a default message
                Log::error('Failed to decode JSON data in emailTemplateData for code: ' . $code);
                return 'Error: Invalid data for the email template.';
            }
        } else {
            // If $textFor is null
            // Log an error or set a default message
            Log::error('No email template found for code: ' . $code . ' and agency ID: ' . $agencyId);
            return 'Error: Email template not found.';
        }
    }

    public function textForSpecificArea($code, $applicantData, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, $superAdminData)
    {
        $textFor = TextForSpecificArea::where('text_code', $code)->where('agency_id', $agencyData->id)->firstOrFail();
        $data = json_decode($textFor->data, true);

        return $this->replaceVaribleToStringHelperFunction($data['header']['text'], $applicantData, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, null, $superAdminData, null);
    }

    public function textForSpecificAreaForCustomTemplate($requestData, $applicantData, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, $superAdminData)
    {
        return $this->replaceVaribleToStringHelperFunction($requestData->message, $applicantData, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, null, $superAdminData, null);
    }

    public function replaceVaribleToStringHelperFunction($textForSpecificArea, $applicantData, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, $quarterlyData, $superAdminData, $requestData)
    {
        if (is_array($textForSpecificArea)) {
            $textForSpecificArea = json_encode($textForSpecificArea);
        }

        if (preg_match_all("/{{(.*?)}}/", $textForSpecificArea, $m)) {
            foreach ($m[1] as $i => $varname) {

                if (isset($this->arrayVaribales[trim($varname)])) {
                    if (trim($varname) == 'today_date') {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', now()->format('d/m/Y')), $textForSpecificArea);
                    } elseif (strpos($varname, 'applicant') !== false && !empty($applicantData) && strpos($varname, 'total') === false) {
                        if (strpos($varname, 'email') !== false) {
                            if (isset($applicantData->email)) {
                                $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $applicantData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                            } else {
                                $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $applicantData->applicantbasic->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                            }
                        } else {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $applicantData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                        }
                    } elseif ((strpos($varname, 'tenancy') !== false || strpos($varname, 'property') !== false) && strpos($varname, 'negotiator_name') === false && !empty($tenancyData)) {
                        if (strpos($varname, 'start_date') !== false || strpos($varname, 'end_date') !== false) {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', Carbon::parse($tenancyData->{$this->arrayVaribales[trim($varname)]})->format('d/m/Y')), $textForSpecificArea);
                        } else {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $tenancyData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                        }
                    } elseif (strpos($varname, 'agency') !== false && !empty($agencyData)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $agencyData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'negotiator_name') !== false && !empty($tenancyData)) {

                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $tenancyData->users->name . ' ' . $tenancyData->users->l_name), $textForSpecificArea);
                    } elseif ((strpos($varname, 'staff') !== false || strpos($varname, 'user') !== false) && !empty($userData)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $userData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'employment') !== false && !empty($employmentData)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $employmentData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'guarantor') !== false && !empty($guarantorData)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $guarantorData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'landlord') !== false && !empty($landlordData)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $landlordData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'superadmin') !== false && !empty($superAdminData)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $superAdminData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif ((strpos($varname, 'credit') !== false || strpos($varname, 'days') !== false) && !empty($requestData)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $requestData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'reference') !== false) {
                        if (!empty($employmentData)) {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $employmentData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                        } elseif (!empty($landlordData)) {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $landlordData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                        } elseif (!empty($guarantorData)) {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $guarantorData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                        } elseif (!empty($quarterlyData)) {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $quarterlyData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                        } else {
                        }
                    }
                }
            }
        }

        return $textForSpecificArea;
    }

    public function textForSpecificAreaForAgreement($code, $tenancyData, $agencyData, $userData, $employmentData, $guarantorData, $landlordData, $requestData, $paymentSchedules)
    {
        $textFor = TextForSpecificArea::where('text_code', $code)->where('agency_id', $tenancyData->agency_id)->firstOrFail();
        $data = json_decode($textFor->data, true);
        $textForSpecificArea = $data['header']['text'];
        $paymentScheduleText = '';
        if (!empty($paymentSchedules)) {
            foreach ($paymentSchedules as $applicantName => $applicantPaymentSchedule) {
                $paymentScheduleText .= "
                <table>
                    <thead>
                        <tr>
                            <th colspan='3'>Payment Schedule for $applicantName:</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><div style='font-weight: 700; margin: 10px 0'>S.No.</div></td>
                            <td><div style='font-weight: 700; margin: 10px 20px'>Date</div></td>
                            <td><div style='font-weight: 700; margin: 10px 20px'>Amount</div></td>
                        </tr>";
                $headerDisplayed = false;
                foreach ($applicantPaymentSchedule as $index =>$schedule) {
                    $date = Carbon::parse($schedule['date'])->format('d/m/Y');
                    $amount = $schedule['amount'];
                    if (!$headerDisplayed) {
                        $headerDisplayed = true;
                    } else {
                        $applicantName = '';
                    }

                    $paymentScheduleText .= "<tr>
                        <td><div style='font-weight: 500; margin: 10px 0'>" . ($index + 1) . "</div></td>
                        <td><div style='font-weight: 500; margin: 10px 20px'>$date</div></td>
                        <td><div style='font-weight: 500; margin: 10px 20px'>&pound;$amount</div></td>
                    </tr>";
                }
                $paymentScheduleText .= "</tbody></table><br>";
            }
        }
        $textForSpecificArea .= $paymentScheduleText;
        if (preg_match_all("/{{(.*?)}}/", $textForSpecificArea, $m)) {
            foreach ($m[1] as $i => $varname) {
                if (isset($this->arrayVaribales[trim($varname)])) {
                    if (trim($varname) == 'today_date') {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', now()->format('d/m/Y')), $textForSpecificArea);
                    } elseif ((strpos($varname, 'tenancy') !== false || strpos($varname, 'property') !== false) && strpos($varname, 'negotiator_name') === false) {
                        if (strpos($varname, 'start_date') !== false || strpos($varname, 'end_date') !== false) {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', Carbon::parse($tenancyData->{$this->arrayVaribales[trim($varname)]})->format('d/m/Y')), $textForSpecificArea);
                        } elseif (strpos($varname, 'parking') !== false && strpos($varname, 'parking_') === false) {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $this->parkingStatusArray[$tenancyData->{$this->arrayVaribales[trim($varname)]}]), $textForSpecificArea);
                        } else {
                            $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $tenancyData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                        }
                    } elseif (strpos($varname, 'agency') !== false) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $agencyData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'negotiator_name') !== false) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $tenancyData->users->name . ' ' . $tenancyData->users->l_name), $textForSpecificArea);
                    } elseif ((strpos($varname, 'staff') !== false || strpos($varname, 'user') !== false)) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $userData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'employment') !== false) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $employmentData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'guarantor') !== false) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $guarantorData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'landlord') !== false) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', $landlordData->{$this->arrayVaribales[trim($varname)]}), $textForSpecificArea);
                    } elseif (strpos($varname, 'signing_date') !== false || strpos($varname, 'generated_date') !== false) {
                        $textForSpecificArea = str_replace($m[0][$i], sprintf('%s', Carbon::parse($requestData->{$this->arrayVaribales[trim($varname)]})->format('d/m/Y')), $textForSpecificArea);
                    }
                }
            }
        }

        preg_match_all("/\[{-.*?\-}]/", $textForSpecificArea, $m);
        $previousText = $m[0];
        $replaceText = ["-----", "-----", "-----"];
        $newPhrase = str_replace($previousText, $replaceText, $textForSpecificArea);
        $splitData = explode("-----", $newPhrase);
        return $splitData;
    }

}
