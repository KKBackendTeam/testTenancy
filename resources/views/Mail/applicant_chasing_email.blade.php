@extends('Mail.main_chasing')

@section('mail_content')

{!! $data !!}

@endsection

@if($mailActionfor ==  0)
    @section('mail_button')

    @include('Mail.mail_button',['buttonName' => 'Privacy Statement', 'buttonLink' => "$applicantData->app_url" ])

    @endsection 
    
@else 

@section('mail_button')

@include('Mail.mail_button',['buttonName' => 'Login to Dashboard', 'buttonLink' => config('global.frontSiteUrl').'/applicant/login'])

@endsection

@endif