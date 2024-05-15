<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class LandlordChasingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData, $referenceInformation, $applicationInformation,$hashCode,$hashId;

    public function __construct($data, $agencyData, $referenceInformation, $applicationInformation)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->referenceInformation = $referenceInformation;
        $this->applicationInformation = $applicationInformation;
        $this->hashCode = Str::random(80).'l'.Str::random(20);
        $this->hashId =  $referenceInformation->id;
    }

    public function build()
    {
        return $this->subject($this->agencyData['name'] . ' - Reminder: Urgent Landlord Reference for ' . $this->applicationInformation['app_name'] . ' ' . $this->applicationInformation['l_name'])->view('Mail.reference_chasing_email');
    }
}
