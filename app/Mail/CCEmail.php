<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class CCEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData, $referenceInformation, $applicationInformation, $referenceType,$hashCode,$hashId;

    public function __construct($data, $agencyData, $referenceInformation, $applicationInformation, $referenceType)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->referenceInformation = $referenceInformation;
        $this->applicationInformation = $applicationInformation;
        $this->referenceType = $referenceType;
        $this->hashCode = Str::random(80).'a'.Str::random(20);
        $this->hashId =  $applicationInformation->id;
    }

    public function build()
    {
        return $this->subject($this->agencyData['name'] . ' - Reminder: Urgent ' .  $this->referenceType . ' Reference for ' . $this->applicationInformation['app_name'] . ' ' . $this->applicationInformation['l_name'])->view('Mail.reference_cc_mail');
    }
}
