<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class GuarantorReferenceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData, $referenceInformation, $applicantInformation,$hashCode,$hashId;

    public function __construct($data, $agencyData, $referenceInformation, $applicantInformation)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->referenceInformation = $referenceInformation;
        $this->applicantInformation = $applicantInformation;
        $this->hashCode = Str::random(80).'s'.Str::random(20);
        $this->hashId =  'super';
    }

    public function build()
    {
        return $this->subject($this->agencyData['name'] . ' - Urgent Guarantor Reference for ' . $this->applicantInformation['app_name'] . ' ' . $this->applicantInformation['l_name'])->view('Mail.guarantor_ref_email');
    }
}
