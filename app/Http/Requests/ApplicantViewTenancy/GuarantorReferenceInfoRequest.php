<?php

namespace App\Http\Requests\ApplicantViewTenancy;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuarantorReferenceInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'income_proof_status' => 'required',
            'id_proof_status' => 'required',
            'address_proof_status' => 'required',
            'income_proof_text' => 'required',
            'id_proof_text' => 'required',
            'address_proof_text' => 'required',
            'applicant_id' => 'required',
            'ref_email' => 'required|email',
            'ref_name' => 'required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
