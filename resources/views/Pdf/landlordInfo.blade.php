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
                    <h2 style="color: #000000;">Landlord Reference</h2>
                </td>
                <td style="text-align: right;background:#0000;border:0;padding:0;"><img
                        src="{{ asset('storage/agency/media_logo/' . $agency->media_logo) }}" width="80px"
                        height="80px" alt="img" class="main-logo"></td>
            </tr>
        </table>

        <table style="width: 100%; background:#0000;">
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Name of Applicant: {{ ucfirst($applicantInfo['applicantbasic']['app_name']) . ' ' . ucfirst($applicantInfo['applicantbasic']['m_name']) . ' ' . ucfirst($applicantInfo['applicantbasic']['l_name']) ?? 'N/A' }}
                </td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Sign Date:
                    {{ date('d/m/Y', strtotime($data['fill_date'] ?? 'N/A')) }}</td>
            </tr>
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Landlord/Agent
                    Name: {{ $data['name'] ?? 'N/A' }}</td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Phone Number:
                    {{ countryToDialingCode($data['country_code']) ?? 'N/A' }} {{ $data['phone'] ?? 'N/A' }}</td>
            </tr>
        </table>
        <div style="padding: 20px 0px">
            <table>
                <tr>
                    <th>Landlord Information</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Property rented with you</div>
                        <div>{{ ucfirst($data['street']) ?? 'N/A' }} {{ ucfirst($data['town']) ?? 'N/A' }} {{ ucfirst($data['country']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Rental amount PCM</div>
                        <div> &pound; {{ $data['rent_price'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Tenancy start date</div>
                        <div>{{ date('d/m/Y', strtotime($data['t_s_date'] ?? 'N/A')) }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Tenancy end date</div>
                        <div>{{ date('d/m/Y', strtotime($data['t_e_date'] ?? 'N/A')) }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Company name</div>
                        <div>{{ ucfirst($data['company_name']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Position</div>
                        <div>{{ ucfirst($data['position']) ?? 'N/A' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div>
            <table>
                <tr>
                    <th>Landlord Questionnaire</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Rent Paid On Time ?</div>
                        <div>{{ ucfirst($data['paid_status']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                @if ($data['paid_status'] == 'No')
                    <tr>
                        <td>
                            <div style="font-weight: 700;">How frequently were they late ?</div>
                            <div>{{ ucfirst($data['frequent_status']) ?? 'N/A' }}</div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <div style="font-weight: 700;">Any damage?</div>
                        <div>{{ ucfirst($data['damage_status']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                @if ($data['damage_status'] == 'Yes')
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Damage Detail</div>
                            <div>{{ ucfirst($data['damage_detail']) ?? 'N/A' }}</div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <div style="font-weight: 700;">Would you recommend them as tenant?</div>
                        <div>{{ ucfirst($data['tenant_status']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                @if ($data['tenant_status'] == 'No')
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Why not ?</div>
                            <div>{{ ucfirst($data['why_not']) ?? 'N/A' }}</div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <div style="font-weight: 700;">Is the applicant free to move out?</div>
                        <div>{{ ucfirst($data['moveout_status']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                @if ($data['moveout_status'] == 'No')
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Why is the applicant not free to move out?</div>
                            <div>{{ ucfirst($data['free_move_out_reason']) ?? 'N/A' }}</div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <div style="font-weight: 700;">Arrears status</div>
                        <div>{{ ucfirst($data['arrears_status']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Paid Arrears</div>
                        <div>&pound; {{ $data['paid_arrears'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                @if ($data->signature)
                    <tr>
                        <td>
                            <div style="font-weight: 700;" class="upload-img">Landlords Signature</div>
                            <div><a href="{{ asset('storage/applicant/signatures/' . $data->signature) }}"
                                    target="_blank">View</a>
                            </div>
                        </td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
    </div>
</body>

</html>
