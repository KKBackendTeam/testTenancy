@extends('Mail.main_chasing')

@section('mail_content')

{!! $data !!}
    
@endsection

@section('mail_button')

@include('Mail.mail_button',['buttonName' => 'Reset Passaword', 'buttonLink' => "$userInformation->password_link"])

@endsection
