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
                    <h2 style="color: #000000;">Employment Reference</h2>
                </td>
                <td style="text-align: right;background:#0000;border:0;padding:0;"><img
                        src="{{ asset('storage/agency/media_logo/' . $agency->media_logo) }}" width="80px" height="80px" alt="img" class="main-logo"></td>
            </tr>
        </table>

        <table style="width: 100%; background:#0000;">
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;">Name of Applicant: {{ $applicantInfo['applicantbasic']['app_name'] . ' ' . $applicantInfo['applicantbasic']['m_name'] . ' ' . $applicantInfo['applicantbasic']['l_name'] ?? 'N/A' }}
                </td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;"></td>
            </tr>
            <tr>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;"></td>
                <td style="background:#0000;border:0;width:200px"></td>
                <td style="background:#0000;border:0;color:#465d81;font-size:14px;white-space:nowrap;"></td>
            </tr>
        </table>

        <div style="padding: 20px 0px">
            <table>
                <tr>
                    <th>Employment Information</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Company name</div>
                        <div>{{ ucfirst($data['company_name'])  ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Company address</div>
                        <div>{{ ucfirst($data['company_address']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Company phone number</div>
                        <div>{{ countryToDialingCode($data['country_code']) ?? 'N/A' }} {{ $data['company_phone'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Job title</div>
                        <div>{{ ucfirst($data['job_title']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Annual salary</div>
                        <div>&pound; {{ $data['annual_salary'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Average annual bonus/commision</div>
                        <div>&pound; {{ $data['annual_bonus'] ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">In Probation period</div>
                        <div>{{  ucfirst($data['probation_period']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Contract type</div>
                        <div>{{ ucfirst($data['contract_type']) ?? 'N/A' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div>
            <table>
                <tr>
                    <th>Employee Information</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Name</div>
                        <div>{{ ucfirst($data['name']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Position</div>
                        <div>{{ ucfirst($data['position']) ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 700;">Sign date</div>
                        <div>{{ date('d/m/Y', strtotime($data['fill_date'] ?? 'N/A')) }}</div>
                    </td>
                </tr>
                @if ($data->signature)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">Employers Signature</div>
                            <div><a href="{{ asset('storage/applicant/signatures/' . $data->signature) }}" target="_blank">View</a>
                            </div>
                        </td>
                    </tr>
                @endif
            </table>
        </div>
        <div style="padding: 20px 0px; margin-top: 30px">
            <h2 style="text-align: center">Terms and Conditions</h2>
            <div>
                <?php
                    echo $text_for_specific_area;
                ?>
            </div>
        </div>
    </div>
</body>

</html>
