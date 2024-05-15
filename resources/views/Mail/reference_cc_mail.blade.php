@extends('Mail.main')


@section('cc_mail_content')
<div align="center">
         <span style="font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;font-size:16px;line-height:32px;">

        This reminder email has been sent to your {{ $referenceType }} to complete. You have received this email so you are aware that this reference is still outstanding. Please follow this up with your  {{ $referenceType }} to complete this reference so we can process your application.

         </span>

</div>

@endsection

@section('mail_content')

{!! $data !!}

@endsection
