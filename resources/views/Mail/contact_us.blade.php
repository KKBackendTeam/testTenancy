@extends('Mail.main_chasing')

@section('mail_content')

@if (!empty($data))
<div style="font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:100%; padding-right: 20px; padding-left: 20px; padding-top: 10px; padding-bottom: 10px;">
    <p>Hi Admin,</p>
    <br/>
    <p>You have received a new query from the following user:</p>  
    <p>User's Name: {{ $data['first_name'] }} {{ $data['last_name'] }}</p>    
    <p>User's Email: {{ $data['email'] }}</p>
    <p>Message: {{ $data['message'] }}</p>
    <br/>
    <p>Please take necessary action to address the user's query promptly.</p>  
</div>
@endif

@endsection