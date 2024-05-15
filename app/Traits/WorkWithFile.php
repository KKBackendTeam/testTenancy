<?php

namespace App\Traits;

use Storage;
use Illuminate\Support\Str;

trait WorkWithFile
{
    public function findDirectoryLocation($type)
    {
        if ($type == "signature") $fileLocation = "/public/applicant/signatures/";
        elseif ($type == "agreement_signature") $fileLocation = "/public/applicant/agreement_signature/";
        elseif ($type == "document") $fileLocation = "/public/applicant/documents/";
        elseif ($type == "media_logo") $fileLocation = "/public/agency/media_logo/";
        elseif ($type == "selfie_pic") $fileLocation = "/public/agency/selfie_pics/";
        elseif ($type == "app_selfie_pic") $fileLocation = "/public/applicant/documents/selfie_pics/";
        elseif ($type == "agreement") $fileLocation = "/public/agency/agreement/";
        elseif ($type == "defaultDocuments") $fileLocation = "/public/agency/default_documents/";
        elseif ($type == "test") $fileLocation = "/public/test/";
        else $fileLocation = "/public/agency/";

        return $fileLocation;
    }

    public function file_upload($fileBase64Url, $type, $file_name)
    {
        $pos = strpos($fileBase64Url, ';');
        $filetype = $pos !== false ? explode('/', substr($fileBase64Url, 0, $pos))[1] : null;
        $contents = file_get_contents($fileBase64Url);
        if (!is_null($file_name)) {
            $pieces = explode(".", $file_name);

            if (count($pieces) >= 2) {
                $unique_name = $pieces[0] . '_' . Str::random(7) . '.' . $pieces[1];
            } else {
                $unique_name = substr(md5(Str::random(20)) . time(), 0, 15);
                $ext = $filetype;
                $unique_name = $unique_name . '.' . $ext;
            }
        } else {
            $unique_name = substr(md5(Str::random(20)) . time(), 0, 15);
            $ext = $filetype;
            $unique_name = $unique_name . '.' . $ext;
        }

        Storage::put($this->findDirectoryLocation($type) . $unique_name, $contents);

        return $unique_name;
    }


    public function deleteFile($type, $image_name)
    {
        if (!empty($image_name)) {
            $image_path = $this->findDirectoryLocation($type) . $image_name;
            if (Storage::exists($image_path)) {
                Storage::delete($image_path);
            }
        }
        return true;
    }

    public function fileExistOnNotValidatorHelper($type, $fileName, $requestObject, $variableName)
    {
        if (!empty($fileName) && !Storage::exists($this->findDirectoryLocation($type) . $fileName)) {
            $validate = validator($requestObject, [
                $variableName => 'file_type_check'
            ]);
            if ($validate->fails()) return $validate;
        }
        return false;
    }

    public function fileUploadHelperFunction($type, $seondName, $fileNameorFileBase64Url)
    {
        if (!empty($fileNameorFileBase64Url) && !Storage::exists($this->findDirectoryLocation($type) . $fileNameorFileBase64Url)) {
            return $this->file_upload($fileNameorFileBase64Url, $type, $seondName);
        }
        return $fileNameorFileBase64Url;
    }

    public function fileUploadArrayHelperFunction($type, $seondName, $fileNameorFileBase64Url)
    {
        $imageNames = [];

        foreach ($fileNameorFileBase64Url as $index => $dataUri) {
            if (!empty($dataUri) && !Storage::exists($this->findDirectoryLocation($type) . $dataUri)) {
                $uploadedFileName = $this->file_upload($dataUri, $type, $seondName);
                $imageNames[] = $uploadedFileName;
            } else {
                $imageNames[] = $dataUri;
            }
        }
        return $imageNames;
    }

    public function checkFileExistOrNot($type, $fileName)
    {
        if (!empty($fileName)) {
            $image_path = $this->findDirectoryLocation($type) . $fileName;
            return Storage::exists($image_path) ? true : false;
        }
        return false;
    }

    public function deleteTenancyRecords($tenancy)
    {
        foreach ($tenancy->applicants as  $app) {
            $this->deleteSingleApplicant($app);
        }
        isset($tenancy->agreement) ? $this->deleteFile('agreement', $tenancy->agreement) : null;
        return true;
    }

    public function deleteSingleApplicant($app)
    {
        if (!$app['employmentReferences']->isEmpty()) {
            foreach($app['employmentReferences'] as $employmentReference){
                isset($employmentReference['signature']) ? $this->deleteFile('signature', $employmentReference['signature']) : null;
            }
        }
        if (!$app['quarterlyReferences']->isEmpty()) {
            foreach($app['quarterlyReferences'] as $quaterlyReference){
                if (isset($quaterlyReference['qu_doc']) && is_array($quaterlyReference['qu_doc'])) {
                    foreach ($quaterlyReference['qu_doc'] as $docPath) {
                        $this->deleteFile('document', $docPath);
                    }
                }
            }
        }
        if (!$app['landlordReferences']->isEmpty()) {
            foreach($app['landlordReferences'] as $landlordReference){
                isset($landlordReference['signature']) ? $this->deleteFile('signature', $landlordReference['signature']) : null;
            }
        }
        if (!$app['guarantorReferences']->isEmpty()) {
            foreach($app['guarantorReferences'] as $guarantorReference){
                isset($guarantorReference['signature']) ? $this->deleteFile('signature', $guarantorReference['signature']) : null;
                isset($guarantorReference['address_proof']) ? $this->deleteFile('document', $guarantorReference['address_proof']) : null;
                isset($guarantorReference['id_proof']) ? $this->deleteFile('document', $guarantorReference['id_proof']) : null;
                isset($guarantorReference['financial_proof']) ? $this->deleteFile('document', $guarantorReference['financial_proof']) : null;
                if (isset($guarantorReference['other_document']) && is_array($guarantorReference['other_document'])) {
                    foreach ($guarantorReference['other_document'] as $docPath) {
                        $this->deleteFile('document', $docPath);
                    }
                }
            }

        }
        if ($app) {
            isset($app['signature']) ? $this->deleteFile('signature', $app['signature']) :  null;
            isset($app['selfie_pic']) ? $this->deleteFile('app_selfie_pic', $app['selfie_pic']) :  null;
            isset($app['front_doc']) ? $this->deleteFile('document', $app['front_doc']) :  null;
            isset($app['back_doc']) ? $this->deleteFile('document', $app['back_doc']) :  null;
            isset($app['agreement_signature']) ? $this->deleteFile('agreement_signature', $app['agreement_signature']) :  null;
            isset($app['passport_document']) ? $this->deleteFile('document', $app['passport_document']) :  null;
            isset($app['selfie_passport_document']) ? $this->deleteFile('document', $app['selfie_passport_document']) :  null;
            isset($app['selfie_resident_card']) ? $this->deleteFile('document', $app['selfie_resident_card']) :  null;

            if ($app->applicantbasic->applicants()->count() <= 1) { //remove applicant information from applicant basic
                $app->applicantbasic->delete();
            }
            $app->delete();
        }
        return true;
    }
}
