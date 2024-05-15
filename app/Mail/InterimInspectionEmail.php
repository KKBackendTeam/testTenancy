<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class InterimInspectionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData, $inspection, $applicantInformation, $hashCode,$hashId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $agencyData, $inspection, $applicantInformation)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->inspection = $inspection;
        $this->applicantInformation = $applicantInformation;
        $this->hashCode = Str::random(80).'s'.Str::random(20);
        $this->hashId =  'super';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject($this->agencyData['name'] . ' - ' . $this->inspection['subject'] . $this->applicantInformation['app_name'] . ' ' . $this->applicantInformation['l_name'])->view('Mail.interim_inspection_email');
        return $email;
    }
}
