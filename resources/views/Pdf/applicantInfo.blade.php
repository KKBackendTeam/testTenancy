<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" href="%PUBLIC_URL%/favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="Web site created using create-react-app" />
    <meta charset="UTF-8">
    <link rel="apple-touch-icon" href="%PUBLIC_URL%/logo192.png" />
    <link rel="manifest" href="%PUBLIC_URL%/manifest.json" />
    <title>Document</title>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #AAB3C0;
            text-align: left;
            padding: 8px;
        }

        th:first-child {
            color: #000000;
            background-color: #B5BDC8;
        }

        .upload-img img {
            width: 100px;
            height: 80px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div style="margin:auto; width: 100%; font-family: system-ui; color: #465d81;">
        <table style="width: 100%; background:#0000;">
            <tr>
                <td style="background:#0000;border:0;padding:0;">
                    <h2 style="color: #000000;">Applicant Details</h2>
                </td>
                <td style="text-align: right;background:#0000;border:0;padding:0;"><img
                        src="{{ asset('storage/agency/media_logo/' . $agency->media_logo) }}" width="80px"
                        height="80px" alt="img" class="main-logo"></td>
            </tr>
        </table>

        <table style="width: 100%; background:#0000;">
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Name of
                    Applicant:
                    {{ $data['applicantbasic']['app_name'] . ' ' . $data['applicantbasic']['m_name'] . ' ' . $data['applicantbasic']['l_name'] ?? 'N/A' }}
                </td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">National
                    Insurance Number: {{ $data['applicantbasic']['app_ni_number'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Phone Number:
                    {{ countryToDialingCode($data['applicantbasic']['country_code']) ?? 'N/A' }}
                    {{ $data['applicantbasic']['app_mobile'] ?? 'N/A' }}</td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Date of Birth:
                    {{ date('d/m/Y', strtotime($data['applicantbasic']['dob'] ?? 'N/A')) }}</td>
            </tr>
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Email:
                    {{ $data['applicantbasic']['email'] ?? 'N/A' }}</td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;"></td>
            </tr>
        </table>
        <div style="padding: 20px 0px">
            <table>
                <tr>
                    <th>Property Details</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Property Address</div>
                        <div>{{ $tenancyInfo['pro_address'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Holding Fee (Whole Tenancy Amount)</div>
                        <div>&pound; {{ $tenancyInfo['holding_amount'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Monthly Rent (Whole Tenancy Amount)</div>
                        <div>&pound; {{ $tenancyInfo['monthly_amount'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Parking Cost (PCM)</div>
                        <div>&pound; {{ $tenancyInfo['parking_cost'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Total Amount (Whole Tenancy Amount)</div>
                        <div>&pound; {{ $tenancyInfo['total_rent'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Deposite Amount</div>
                        <div>&pound; {{ $tenancyInfo['deposite_amount'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Tenancy Term</div>
                        <div>{{ date('d/m/Y', strtotime($tenancyInfo['t_start_date'] ?? 'N/A')) }} To
                            {{ date('d/m/Y', strtotime($tenancyInfo['t_end_date'] ?? 'N/A')) }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Applicants in this Tenancy</div>
                        <div>{{ $tenancyInfo['no_applicant'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Name of Applicants</div>
                        @foreach ($applicants as $app)
                            <div>
                                {{ $app['applicantbasic']['app_name'] . ' ' . $app['applicantbasic']['m_name'] . ' ' . $app['applicantbasic']['l_name'] ?? 'N/A' }}
                            </div>
                        @endforeach
                    </td>
                </tr>
            </table>
        </div>
        @if (
            !empty($data->studentReferences) &&
                (is_iterable($data->studentReferences) || is_array($data->studentReferences)) &&
                $data->studentReferences->isNotEmpty())
            <div>
                <table>
                    <tr>
                        <th>Student Details</th>
                    </tr>
                    @foreach ($data->studentReferences as $studentReference)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Name of University</div>
                                <div>{{ $studentReference['uni_name'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Course Title</div>
                                <div>{{ $studentReference['course_title'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Year of Graduation</div>
                                <div>{{ $studentReference['year_grad'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
        <div style="padding: 20px 0px;">
            <table>
                <tr>
                    <th>Applicant Questionnaire</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Employment Status</div>
                        <div>
                            @if ($data['level_1'] == 1)
                                Student
                            @elseif($data['level_1'] == 2)
                                Employed
                            @else
                                Neither
                            @endif
                        </div>
                    </td>
                </tr>
                @if ($data['level_1'] == 1)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Permanent Residency</div>
                            <div>
                                @if ($data['level_2'] == 1)
                                    UK Resident
                                @else
                                    International Resident
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="font-weight: 700;">UK based guarantor that has a net income of 3x your share of
                                the monthly rent ?</div>
                            <div>
                                @if ($data['level_3'] == 1)
                                    Yes
                                @else
                                    No
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if ($data['level_3'] != 1)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Pay 3x monthly rent in advance and provide income proof
                                    to
                                    support this?</div>
                                <div>
                                    @if ($data['level_4'] == 1)
                                        Yes
                                    @else
                                        No
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                    <!-- student-tab-closed -->
                    <!-- employed-tab-open -->
                @elseif($data['level_1'] == 2)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Is your net Income 3x your share of the rent ?</div>
                            <div>
                                @if ($data['level_2'] == 1)
                                    Yes
                                @else
                                    No
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="font-weight: 700;">UK based guarantor that has a net income of 3x your share of
                                the monthly rent ?</div>
                            <div>
                                @if ($data['level_3'] == 1)
                                    Yes
                                @else
                                    No
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if ($data['level_3'] == 2)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">pay 3x monthly rent in advance and provide income proof
                                    to support this ?</div>
                                <div>
                                    @if ($data['level_4'] == 1)
                                        Yes
                                    @else
                                        No
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @else
                    <!-- employed-tab-closed -->
                    <!-- neither-tab-open -->

                    <tr>
                        <td>
                            <div style="font-weight: 700;">UK based guarantor that has a net income of 3x your share of
                                the monthly rent ?</div>
                            <div>
                                @if ($data['level_2'] == 1)
                                    Yes
                                @else
                                    No
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if ($data['level_2'] == 1)
                        <!-- Yes-tab-open -->
                        <tr>
                            <td>
                                <div style="font-weight: 700;">pay 3x monthly rent in advance and provide income proof
                                    to support this ?</div>
                                <div>
                                    @if ($data['level_3'] == 1)
                                        Yes
                                    @else
                                        No
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @else
                        <!-- yes-tab-closed -->
                        <!-- no-tab-open -->
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Can pay whole terms rent in advance and provide income
                                    proof to support this ?</div>
                                <div>
                                    @if ($data['level_3'] == 1)
                                        Yes
                                    @else
                                        No
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                    <!-- no-tab-closed -->
                    <!-- neither-tab-open -->
                @endif
            </table>
        </div>
        @if (!empty($data->addresses))
            <div style="padding: 20px 0px;">
                <table>
                    <tr>
                        <th>Direct Family / Next of Kin Address</th>
                    </tr>
                    @php
                        $addressesArray = json_decode($data['addresses'], true);
                    @endphp
                    @foreach ($addressesArray as $address)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Postcode Lookup</div>
                                <div>{{ $address['postcode'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Street</div>
                                <div>{{ $address['street'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Town/City</div>
                                <div>{{ $address['town'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Country</div>
                                <div>{{ $address['country'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if (
            !empty($data->employmentReferences) &&
                (is_iterable($data->employmentReferences) || is_array($data->employmentReferences)) &&
                $data->employmentReferences->isNotEmpty())
            <div style="padding: 20px 0px;">
                <table>
                    <tr>
                        <th>Employment details</th>
                    </tr>
                    @foreach ($data['employmentReferences'] as $employmentReference)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Company Name</div>
                                <div>{{ $employmentReference['company_name'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Managers Telephone Number</div>
                                <div>{{ countryToDialingCode($employmentReference['country_code']) ?? 'N/A' }}
                                    {{ $employmentReference['company_phone'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Managers Email Address</div>
                                <div>{{ $employmentReference['company_email'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if (
            !empty($data->quarterlyReference) &&
                (is_iterable($data->quarterlyReference) || is_array($data->quarterlyReference)) &&
                $data->quarterlyReference->isNotEmpty())
            <div style="padding: 20px 0px;">
                <table>
                    @foreach ($data['quarterlyReferences'] as $quarterlyReference)
                        @if ($quarterlyReference->type == 'quarterly')
                            <tr>
                                <th>Quarterly Payment Proof</th>
                            </tr>
                        @else
                            <tr>
                                <th>Full Terms Payment Proof</th>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                <div style="font-weight: 700;">What is the closing balance in your bank account</div>
                                <div>{{ $quarterlyReference['close_bal'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if (
            !empty($data->guarantorReferences) &&
                (is_iterable($data->guarantorReferences) || is_array($data->guarantorReferences)) &&
                $data->guarantorReferences->isNotEmpty())
            <div style="padding: 20px 0px;">
                <table>
                    <tr>
                        <th>Guarantor Details</th>
                    </tr>
                    @foreach ($data['guarantorReferences'] as $guarantorReference)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Name</div>
                                <div>{{ $guarantorReference['name'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Phone Number</div>
                                <div>{{ countryToDialingCode($guarantorReference['country_code']) ?? 'N/A' }}
                                    {{ $guarantorReference['phone'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Email Address</div>
                                <div>{{ $guarantorReference['email'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if (
            !empty($data->landlordReferences) &&
                (is_iterable($data->landlordReferences) || is_array($data->landlordReferences)) &&
                $data->landlordReferences->isNotEmpty())
            <div style="padding: 20px 0px;">
                <table>
                    <tr>
                        <th>Landlord details</th>
                    </tr>
                    @foreach ($data['landlordReferences'] as $landlordReference)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Landlord/Agent Name</div>
                                <div>{{ $landlordReference['name'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Landlord/Agent Phone Number</div>
                                <div>{{ countryToDialingCode($landlordReference['country_code']) ?? 'N/A' }}
                                    {{ $landlordReference['phone'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Landlord/Agent Email Address</div>
                                <div>{{ $landlordReference['email'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Address whilst renting with this Landlord/Agent</div>
                                <div>{{ $landlordReference['street'] ?? 'N/A' }}
                                    {{ $landlordReference['town'] ?? 'N/A' }}
                                    {{ $landlordReference['country'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Date your Tenancy started with this Landlord/Agent</div>
                                <div>{{ date('d/m/Y', strtotime($landlordReference['t_s_date'] ?? 'N/A')) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Date your Tenancy will end with this Landlord/Agent</div>
                                <div>{{ date('d/m/Y', strtotime($landlordReference['t_e_date'] ?? 'N/A')) }}</div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if (
            !empty($data->paymentSchedule) &&
                (is_iterable($data->paymentSchedule) || is_array($data->paymentSchedule)) &&
                $data->paymentSchedule->isNotEmpty())
            <div style="padding: 20px 0px;">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align: start;">Payment Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="font-weight: 700;">S.No.</div>
                            </td>
                            <td>
                                <div style="font-weight: 700;">Date</div>
                            </td>
                            <td>
                                <div style="font-weight: 700;">Amount</div>
                            </td>
                        </tr>
                        @foreach ($data['paymentSchedule'] as $key => $paymentSchedule)
                            <tr>
                                <td>
                                    <div style="font-weight: 500;"> {{ $key + 1 }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">
                                        {{ date('d/m/Y', strtotime($paymentSchedule['date'] ?? 'N/A')) }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">&pound;{{ $paymentSchedule['amount'] ?? 'N/A' }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>
                <p style="font-weight: 700; padding-top: 0"> Terms and Condition</p>
                <p style="font-weight: 400;">Payment schedule is subject to change. Final Payment Schedule will be
                    provided after the application and tenancy has been completed.</p>
            </div>
        @endif

        @if ($data->selfie_passport_document)
            <div style="padding: 20px 0px">
                <table>
                    <tr>
                        <th>Documents</th>
                    </tr>
                    @if ($data->selfie_passport_document)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Selfie Holding the Passport Document</div>
                                <div><a href="{{ asset('storage/applicant/documents/' . $data->selfie_passport_document) }}"
                                        target="_blank">View</a>
                                </div>
                            </td>
                        </tr>
                    @endif
                    @if ($data->passport_document)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Passport Document</div>
                                <div><a href="{{ asset('storage/applicant/documents/' . $data->passport_document) }}"
                                        target="_blank">View</a>
                                </div>
                            </td>
                        </tr>
                    @endif
                    @if ($data->signature)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">Applicant Signature</div>
                                <div><a href="{{ asset('storage/applicant/signatures/' . $data->signature) }}"
                                        target="_blank">View</a>
                                </div>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        @endif
        <div style="padding: 20px 0px; margin-top: 15px">
            <h2 style="text-align: center">Terms and Conditions</h2>
            <div>
                <p>If you are a UK student, even if you are employed, you need to please provide details for a UK-based
                    guarantor. <br>I confirm that I am over 18 years of age and the information I will give is true and
                    accurate. I confirm that no one will be living in the property except anyone who was named on this
                    application form. I understand that if any of the information in this application form is false or
                    misleading I may not be entitled to a refund of any holding deposit taken in relation to this
                    application.<br>I for more information about how to answer these questions, please see this page on
                    our company website.
                </p>
            </div>
        </div>
        <div style="padding: 20px 0px; margin-top: 15px">
            <h2 style="text-align: center">Privacy Statement</h2>
            <div>
                <?php
                echo $applicant_privacy_statement;
                ?>
            </div>
        </div>
    </div>
</body>

</html>
