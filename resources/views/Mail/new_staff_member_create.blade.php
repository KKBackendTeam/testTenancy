@extends('Mail.main')

@section('mail_content')

{!! $data !!}

@endsection

@section('mail_button')

@include('Mail.mail_button',['buttonName' => 'Login to Dashboard', 'buttonLink' => config('global.frontSiteUrl').'/login'])

@endsection
