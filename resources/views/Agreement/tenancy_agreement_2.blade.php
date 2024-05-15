<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
     .agent_signature .signature_sign {
        border-bottom: 1px solid #000 !important;
    }

    .agent_signature ul {
        padding-left: 0;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .agent_signature ul li {
        display: inline-block;
        list-style: none;
    }

    .table-layouts .dt{
        font-size: 13px;
        margin: 0;
        margin-top: 5px
    }

    @media print {
    .table-layouts ul li{
    border: 1px solid black;
  }
}

    .table-layouts ul li {

        float: left;
        border: 1px solid #000 !important;
        padding: 10px;
        list-style: none !important;
        border-bottom: 0;
        display: inline-block;
        align-items: center;
        height: 125px;
    }

    .table-layouts ul li:first-child {
        width: 55%;
        border-right: 0 !important;

    }

    .table-layouts ul li:last-child {
        width: 38%;

    }

    .font-size-class {
       font-size: 14px;

    }

    .table-layouts ul:last-child li {
        border-bottom: 1px solid #000 !important;

    }

    .table-layouts ul {
        padding-left: 0;
    }

    body {
        margin: 0;
        font-family: 'Times New Roman', Times, serif;
    }

    li {
        list-style: none;
    }

    a {
        text-decoration: none;
    }

    .Agreement_main {
        margin: 0 auto;
        width: 712px;
    }

    .main_heading {
        text-align: center;
    }

    .main_heading h3 {
        text-decoration: underline;
        font-size: 20px;
    }

    .main_heading p {
        font-size: 16px;
        font-weight: 700;
    }

    .between {
        text-align: center;
    }

    .between h3 {
        text-align: left;
    }

    .between li {
        font-size: 16px;
        line-height: 20px;
    }

    .between li span {
        font-size: 16px;
        font-weight: 700;
    }

    .background p {
        font-size: 16px;
    }

    .background h3.title {
        font-size: 16px;
    }

    .background ul li {
        list-style: decimal;
        font-size: 16px;
        line-height: 20px;
    }

    .witness_address {
        width: 50%;
        float: left;
    }

    .witness_name {
        width: 50%;
        float: left;

    }

    .signature .name1 {
        width: 50%;
        float: left;
    }

    .signature .name2 {
        width: 50%;
        float: left;
    }

    .let_property ul {
        padding-left: 20px !important;
    }

    .let_property ul li {
        list-style: decimal;
        font-size: 16px;
    }

    .let_property ul li ul li {
        list-style: decimal;
        font-size: 16px;
    }

    .let_property ul span {
        font-weight: 700;
    }

    .clearfix {
        clear: both;
    }
    .ul_class{
        padding-left: 0;

    }
</style>

</head>

<body>

<div class="Agreement_main">
    <div class="between">
        {!! $agreementFirst !!}

        <ul class="ul_class">
            <li>{{ $tenancy['landlords']['display_name'] }} </li>
            <li>Address: {{ $tenancy['landlords']['street'] }},
                {{ $tenancy['landlords']['town'] }}, {{ $tenancy['landlords']['country'] }}, {{ $tenancy['landlords']['post_code'] }}</li>
            <li>Telephone: {{ $tenancy['landlords']['mobile'] }}</li>
        </ul>

        <p>(the "Landlord")</p>

        <b>­- AND ­-</b>

        <ul class="ul_class">
            @foreach( $tenancy['applicants'] as $applicant)
                <li>{{ $applicant->applicantbasic['app_name'] }} {{ $applicant->applicantbasic['m_name'] }} {{ $applicant->applicantbasic['l_name'] }} 
            @endforeach
        </ul>

        <p>(collectively and individually the "Tenant") </p>
        <p>(individually the “Party” and collectively the “Parties”) </p>
    </div>
    <div class="clearfix"></div>
    <div class="background">
            {!! $agreementSecond !!}

    @if(!empty($terminateClause))<p><strong>Termination Clause:</strong> {!! $terminateClause !!}</p>@endif
    <div class="let_property">
            <h3 class="title">Address for Notice</h3>

            @foreach($tenancy['applicants'] as $applicant)
                <span class="" style="padding-bottom:20px;">Tenants contact details to be used before, during and after the tenancy:
                    <ul>
                        <li class="font-size-class">Name:
                            {{ $applicant->applicantbasic['app_name'] }} {{ $applicant->applicantbasic['m_name'] }} {{ $applicant->applicantbasic['l_name'] }}
                        </li>
                        <li class="font-size-class">Phone: {{ $applicant->applicantbasic['app_mobile'] }}</li>
                        <li class="font-size-class">Email: {{ $applicant->applicantbasic['email'] }}</li>
                    </ul>
                </span>

            @endforeach

            <span class="">For any matter relating to this tenancy, whether during or after this tenancy has been terminated,
                the Landlord address for notice is:
                <ul>
                    <li class="font-size-class">Company/Display Name: {{ $tenancy['landlords']['display_name'] }}
                    </li>
                    <li class="font-size-class">Address: {{ $tenancy['landlords']['street'] }},
                        {{ $tenancy['landlords']['town'] }},  {{ $tenancy['landlords']['country'] }}, {{ $tenancy['landlords']['post_code'] }}</li>

                    <p style="font-size:16px">The contact information for the Landlord is:</p>
                    <li class="font-size-class">Phone: {{ $tenancy['landlords']['mobile'] }}</li>
                    <li class="font-size-class">Email: {{ $tenancy['landlords']['email'] }}</li>
                </ul>
            </span>

            {!! $agreementThird!!}
        <hr>
        
        <div class="witness_address">
            <p>Signed by Tenants: </p>
        </div>
        <div class="clearfix"></div>

        <div class="table-layouts">
         @foreach($tenancy['applicants'] as $applicant)
                <ul>
                    <li>{{ $applicant->applicantbasic['app_name'] }} {{ $applicant->applicantbasic['m_name'] }} {{ $applicant->applicantbasic['l_name'] }}</li>
                    <li>
                        <div class="image_resize" style="    max-width:140px;width: 100%;height: 65px;">
                              @if($applicant->ta_status == 1)
                              <img 
                              src="{{ config('global.backSiteUrl')}}/fetch/storage/applicant/agreement_signature/{{$applicant['agreement_signature'] }}?token={{\JWTAuth::getToken()}}"
                              style="width:100%; height:80px;">
                              @endif
                              </div>
                              <br>
                              <p class="dt">
                               @if(!is_null($applicant->signing_time))
                                     {{ convertTimestampFormat($applicant->signing_time) }}
                              @endif
                              <br>
                               @if(!is_null($applicant->ip_address))
                                    {{ $applicant->ip_address }}
                              @endif
                              </p>
                    </li>
                </ul>
                 <div class="clearfix"></div>

            @endforeach
            </div>

  @if(!empty($tenancy['reviewer']) && $tenancy->review_agreement == 1)
        <div class="witness_address">
            <p>Signed by Agent: </p><br>
        </div>
        <div class="clearfix"></div>

        <div class="table-layouts">
                <ul>
                    <li>{{ $tenancy['reviewer']['name'] }} {{ $tenancy['reviewer']['l_name'] }}</li>
                    <li>
                        <div class="image_resize" style="max-width: 140px;width: 100%;height: 65px;">
                              <img src="{{ config('global.backSiteUrl')}}/fetch/storage/applicant/agreement_signature/{{ $tenancy['reviewer']['agreement_signature'] }}?token={{\JWTAuth::getToken()}}"
                         style="width:100%; height:80px;">
                            </div>
                              <br>
                              <p class="dt">
                               @if(!is_null($tenancy['reviewer']['signing_time']))
                                     {{ convertTimestampFormat($tenancy['reviewer']['signing_time']) }}
                              @endif
                              <br>
                               @if(!is_null($tenancy['reviewer']['ip_address']))
                                    {{ $tenancy['reviewer']['ip_address'] }}
                              @endif
                              </p>
                    </li>
                </ul>
                 <div class="clearfix"></div>
            </div>
            <br>
            @endif

            <div class="clearfix"></div>

        <div class="witness_name">
            @if(!is_null($tenancy['signature']))
                <img src="{{ config('global.backSiteUrl')}}/storage/applicant/signatures/{{ $tenancy['signature'] }}?token={{\JWTAuth::getToken()}}"
                     style="width:100px">
            @endif
        </div>
        <div class="clearfix"></div>

        <hr>

        {!! $agreementForth !!}

    </div>
</div>
</div>
</body>

</html>
