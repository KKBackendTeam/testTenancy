<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class AgencyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData, $registerAgency,$hashCode,$hashId;

    public function __construct($data, $agencyData, $registerAgency)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->registerAgency = $registerAgency;
        $this->hashCode = Str::random(80).'s'.Str::random(20);
        $this->hashId = 'super';
    }

    public function build()
    {
        return $this->subject($this->agencyData['name'] . ' - New Agency Registered')->view('Mail.agency_register');
    }
}
