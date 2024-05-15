@extends('Mail.main')

@section('mail_content')

{!! $data !!}
    
@endsection

@section('mail_button')

@include('Mail.mail_button',['buttonName' => 'Complete Reference Form', 'buttonLink' => "$referenceInformation->ref_link"])

@endsection