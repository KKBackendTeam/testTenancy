@extends('Mail.main_chasing')

@section('mail_content')

{!! $data !!}

@endsection

@section('mail_button')

@include('Mail.mail_button',['buttonName' => 'Verify Agency', 'buttonLink' => "$registerAgency->agency_confirm_link"])

@endsection
