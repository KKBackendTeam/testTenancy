<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class DeadlineExtendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData,$hashCode,$hashId;

    public function __construct($data, $agencyData)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->hashCode = Str::random(80).'s'.Str::random(20);
        $this->hashId =  'super';
    }

    public function build()
    {
        return $this->subject($this->agencyData['name'] . ' - Application Deadline Temporarily Extended')->view('Mail.extend_deadline');
    }
}
