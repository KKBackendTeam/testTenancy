<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class CustomEmail extends Mailable
{
    use Queueable, SerializesModels;


    public $data, $agencyData, $document, $requestData,$hashCode,$hashId;

    public function __construct($data, $agencyData, $document, $requestData)
    {
        $this->data = $data;
        $this->agencyData = $agencyData;
        $this->document = $document;
        $this->requestData = $requestData;
        $this->hashCode = Str::random(80).'s'.Str::random(20);
        $this->hashId =  'super';
    }

    public function build()
    {
        $email = $this->subject($this->requestData['subject'])->view('Mail.custom_email');

        foreach ($this->document as $key => $file) {
            $email->attach(
                storage_path("app/public/test/" . $file["file"]),
                [
                    "as" => $file["name"]
                ]
            );
        }
        return $email;
    }
}
