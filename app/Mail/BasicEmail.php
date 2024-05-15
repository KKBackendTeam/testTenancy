<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BasicEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData;

    public function __construct($data, $agencyData)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
    }

    public function build()
    {
        return $this->subject('Basic email')->view('Mail.default_template');
    }
}
