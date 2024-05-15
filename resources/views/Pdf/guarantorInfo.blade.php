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

        tr:first-child {
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
                    <h2 style="color: #000000;">Guarantor Reference</h2>
                </td>
                <td style="text-align: right;background:#0000;border:0;padding:0;"><img
                        src="{{ asset('storage/agency/media_logo/' . $agency->media_logo) }}" width="80px"
                        height="80px" alt="img" class="main-logo"></td>
            </tr>
        </table>

        <table style="width: 100%; background:#0000;">
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Name of Applicant: {{ $applicantInfo['applicantbasic']['app_name'] . ' ' . $applicantInfo['applicantbasic']['m_name'] . ' ' . $applicantInfo['applicantbasic']['l_name'] ?? 'N/A' }}
                </td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Rent Split Amount PCM:
                    &pound;{{ number_format(($tenancyInfo['total_rent'] ?? 0) / ($tenancyInfo['no_applicant'] ?? 1), 2) }}</td>
            </tr>
            <tr >
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:wrap;">Property Address: {{ $tenancyInfo['pro_address'] ?? 'N/A' }}</td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap; vertical-align: top">Phone Number:
                    {{ countryToDialingCode($data['country_code']) ?? 'N/A' }} {{ $data['phone'] ?? 'N/A' }}</td>
            </tr>
        </table>

        <div style="padding: 20px 0px">
            <table>
                <tr>
                    <th>Guarantor Information</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Guarantor Name</div>
                        <div>{{ $data['name'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Address</div>
                        <div>{{ $data['post_code'] ?? 'N/A' }}, {{ $data['street'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Town</div>
                        <div>{{ $data['town'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Country</div>
                        <div>{{ $data['country'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Occupation</div>
                        <div>{{ $data['occupation'] ?? 'N/A' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div>
            <table>
                <tr>
                    <th>Guarantor Questionnaire</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Home Owner</div>
                        <div>{{ $data['owner'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Are you Employed?</div>
                        <div>{{ $data['employment_status'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                @if ($data['employment_status'])
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Company Name</div>
                            <div>{{ $data['company_name'] ?? 'N/A' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Company Address</div>
                            <div>{{ $data['company_address'] ?? 'N/A' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Company HR Email Address(for employment reference)</div>
                            <div>{{ $data['hr_email'] ?? 'N/A' }}</div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <div style="font-weight: 700;">Are You Living in UK ?</div>
                        <div>
                            @if ($data['is_living_uk'] === 1)
                                Yes
                            @else
                                No
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Are you 18 years old ?</div>
                        <div>
                            @if ($data['is_eighteen'] === 1)
                                Yes
                            @else
                                No
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Relationship with the Applicant</div>
                        <div>{{ $data['relationship'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Net income at least 3x the rental amount you are Guaranteeing?
                        </div>
                        <div>
                            @if ($data['least_income'] === 1)
                                Yes
                            @else
                                No
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Net Annual Income (after taxes)</div>
                        <div>&pound; {{ $data['guarantor_income'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                @if ($data->address_proof)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Address Proof</div>
                            <div><a href="{{ asset('storage/applicant/documents/' . $data->address_proof) }}"
                                    target="_blank">View</a>
                            </div>
                        </td>
                    </tr>
                @endif
                @if ($data->id_proof)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Photo ID</div>
                            <div><a href="{{ asset('storage/applicant/documents/' . $data->id_proof) }}">View</a>
                            </div>
                        </td>
                    </tr>
                @endif
                @if ($data->financial_proof)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Financial Proof</div>
                            <div><a href="{{ asset('storage/applicant/documents/' . $data->financial_proof) }}"
                                    target="_blank">View</a>
                            </div>
                        </td>
                    </tr>
                @endif
                @if (!empty($data->guarantorRefOtherDocument))
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Other Documents</div>
                            @foreach ($data->guarantorRefOtherDocument as $otherDoc)
                                <div><a href="{{ asset('storage/applicant/documents/' . $otherDoc->doc) }}"
                                        target="_blank">View</a>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @endif
                @if ($data->signature)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Guarantors Signature</div>
                            <div><a href="{{ asset('storage/applicant/signatures/' . $data->signature) }}"
                                    target="_blank">View</a>
                            </div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td> 
                        <div style="font-weight: 700;">Sign Date:</div>
                        <div> {{ date('d/m/Y', strtotime($data['fill_date'] ?? 'N/A')) }}</div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="padding: 20px 0px; margin-top: 30px">
            <h2 style="text-align: center">Terms and Conditions</h2>
            <div>
                <?php
                echo $terms_and_condtion;
                ?>
            </div>
        </div>
    </div>

</body>

</html>
