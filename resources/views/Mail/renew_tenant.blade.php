@extends('Mail.main')

@section('mail_content')

{!! $data !!}
    
@endsection

@section('mail_button')

@include('Mail.mail_button',['buttonName' => 'Privacy Statement', 'buttonLink' => "$applicantData->app_url" ])

@endsection
