<?php

namespace App\Traits;

use Storage;

trait ConverFileToBase64
{
    public $mimeType = [
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'jpg' => 'image/jpg',
        'pdf' => 'application/pdf',
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    public function base64Converter($type, $fileName)
    {
        if ($type == 'media_logo')  $image_path = "public/agency/media_logo/" . $fileName;
        elseif ($type == "document") $image_path = "public/applicant/documents/" . $fileName;
        elseif ($type == "signatures" || $type == "signature") $image_path = "public/applicant/signatures/" . $fileName;
        elseif ($type == 'app_selfie_pic')  $image_path = "public/applicant/documents/selfie_pics/" . $fileName;
        elseif ($type == 'agreement') $image_path = "public/agency/agreement/" . $fileName;
        elseif ($type == 'selfie_pic' || $type == 'selfie_pics') $image_path = "public/agency/selfie_pics/" . $fileName;
        elseif ($type == "defaultDocuments") $image_path = "public/agency/default_documents/" . $fileName;
        elseif ($type == "agreement_signature") $image_path = "public/applicant/agreement_signature/" . $fileName;
        elseif ($type == "test") $image_path = "public/test/" . $fileName;
        else $image_path = "public/" . $fileName;

        $filePath = storage_path('app/' . $image_path);

        if (!Storage::exists($image_path)) {
            return response()->json(['saved' => false]);
        }
        return response()->json([
            'saved' => true,
            'type' => $type = pathinfo($filePath, PATHINFO_EXTENSION),
            'mime_type' => $mime_type = isset($this->mimeType[$type]) ? $this->mimeType[$type] : 'png',
            'data' =>  'data:' . $mime_type . ';base64,' . base64_encode(file_get_contents($filePath))
        ]);
    }
}
