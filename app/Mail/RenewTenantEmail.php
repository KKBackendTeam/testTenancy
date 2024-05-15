<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class RenewTenantEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData, $applicantData,$hashCode,$hashId;

    public function __construct($data, $agencyData, $applicantData)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->applicantData = $applicantData;
        $this->hashCode = Str::random(80).'s'.Str::random(20);
        $this->hashId = 'super';
    }

    public function build()
    {
        return $this->subject($this->agencyData['name'] . ' - Renewal Registration Confirmation')->view('Mail.renew_tenant');
    }
}
