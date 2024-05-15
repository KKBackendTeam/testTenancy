<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ConverFileToBase64;
use App\Models\Applicant;
use App\Models\GuarantorReference;
use App\Models\EmploymentReference;
use App\Models\LandlordReference;

class FileController extends Controller
{
    use ConverFileToBase64;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */

    public function getFileFromAgency($directory_name,$filename)
    {
        if (Storage::exists('/public/agency/' . $directory_name . '/' . $filename)) {

            return response()->file(storage_path('app/public/agency/' . $directory_name . '/' . $filename));

        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getFileFromAgency2($directory_name,$filename)
    {
        if (Storage::exists('/public/applicant/' . $directory_name . '/' . $filename)) {

            return response()->file(storage_path('app/public/applicant/' . $directory_name . '/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getFileFromApplicant($directory_name,$filename)
    {
        if (Storage::exists('/public/applicant/'   . $directory_name . '/' . $filename)) {

            return response()->file(storage_path('app/public/applicant/' . $directory_name . '/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getFileFromApplicant2($directory_name,$filename)
    {
        if (Storage::exists('/public/applicant/documents/'  . $directory_name . '/' . $filename)) {

            return response()->file(storage_path('app/public/applicant/documents/' . $directory_name . '/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getFileFromApplicant3($directory_name,$filename)
    {
        if (Storage::exists('/public/agency/' . $directory_name . '/' . $filename)) {

            return response()->file(storage_path('app/public/agency/' . $directory_name . '/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getFileFromApplicant4($filename)
    {
        if (Storage::exists('/public/applicant/documents/' . $filename)) {

            return response()->file(storage_path('app/public/applicant/documents/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getMediaLogo($filename)
    {
        if (Storage::exists('/public/agency/media_logo/' . $filename)) {
        return response()->file(storage_path('app/public/agency/media_logo/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getMediaLogoForAll($filename)
    {
        $access = 0;
        $hashValue = substr(request('hash'),80,1);

        if($hashValue == 's' && request('i') == 'super'){
            $access = 1;
        } else if($hashValue == 'a' && Applicant::where('id',request('i'))->where('agency_id',request('a'))->first()){
            $access = 1;
        } else if ($hashValue == 'g' && GuarantorReference::where('id',request('i'))->where('agency_id',request('a'))->first()){
            $access = 1;
        } else if($hashValue == 'e' && EmploymentReference::where('id',request('i'))->where('agency_id',request('a'))->first()){
            $access = 1;
        } else if($hashValue == 'l' && LandlordReference::where('id',request('i'))->where('agency_id',request('a'))->first()){
            $access = 1;
        } else {}

        if ($access == 1 && Storage::exists('/public/agency/media_logo/' . $filename)) {
            return response()->file(storage_path('app/public/agency/media_logo/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getAgreementSignature($filename)
    {
        if (Storage::exists('/public/applicant/agreement_signature/' . $filename)) {
           return response()->file(storage_path('app/public/applicant/agreement_signature/' . $filename));
        }
        return response()->json(["content" => "fileNotFound"]);
    }
}
