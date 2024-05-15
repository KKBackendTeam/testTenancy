@extends('Mail.main')

@section('mail_content')

{!! $data !!}
    
@endsection

@section('mail_button')

@include('Mail.mail_button',['buttonName' => 'Verify Agency', 'buttonLink' => 'https://www.amazon.in/'])

@endsection