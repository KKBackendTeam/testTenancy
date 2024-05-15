<?php

namespace App\Mail;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OTPVerificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data, $agencyData, $userInformation,$hashCode,$hashId;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $agencyData, $userInformation)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->userInformation = $userInformation;
        $this->hashCode = Str::random(80) . 's' . Str::random(20);
        $this->hashId = 'super';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->agencyData['name'] . ' - Login OTP')->view('Mail.otp-verification');
    }
}
