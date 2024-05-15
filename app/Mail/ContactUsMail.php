<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class ContactUsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $agencyData,$hashCode,$hashId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $agencyData)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->hashCode = Str::random(80).'s'.Str::random(20);
        $this->hashId = 'super';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->agencyData['name'] . '-' . $this->data['subject'])->view('Mail.contact_us');
    }
}
