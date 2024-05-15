<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class ApplicantChasingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData, $applicantData, $mailActionfor,$hashCode,$hashId;


    public function __construct($data, $agencyData, $applicantData, $mailActionfor)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->applicantData = $applicantData;
        $this->mailActionfor = $mailActionfor;
        $this->hashCode = Str::random(80).'a'.Str::random(20);
        $this->hashId =  $applicantData->id;
    }

    public function build()
    {
        if ($this->mailActionfor == 1) {
            return $this->from(config('mail.from.address'),config('mail.from.name'))->subject($this->agencyData['name'] . ' - Urgent Tenancy Agreement Signing Outstanding!')->view('Mail.applicant_chasing_email');
        } else {
            return $this->from(config('mail.from.address'),config('mail.from.name'))->subject($this->agencyData['name'] . ' - Urgent Application Form Outstanding!')->view('Mail.applicant_chasing_email');
        }
    }
}
