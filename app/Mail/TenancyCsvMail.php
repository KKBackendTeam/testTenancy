<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class TenancyCsvMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agencyData, $csvFileName, $hashCode, $hashId;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($agencyData, $csvFileName)
    {
        $this->agencyData = $agencyData;
        $this->csvFileName = $csvFileName;
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
        return $this->subject($this->agencyData['name'] . ' - Tenancy CSV File')->view('Mail.applicant-csv')->attach($this->csvFileName, [
            'as' => 'Tenancy.csv',
            'mime' => 'text/csv',
        ]);
    }
}
