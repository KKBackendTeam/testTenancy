<?php

namespace App\Http\Requests\Reference;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuarantorReferenceRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'guarantor_name' => 'required',
            'post_code' => 'required',
            'street' => 'required',
            'town' => 'required',
            'country' => 'required',
            'owner' => 'required',
            'applicant_relationship' => 'required',
            'guarantor_occupation' => 'required',
            'is_employed' => 'required',
            'address_proof' => 'required',
            'id_proof' => 'required',
            'financial_proof' => 'required',
            'signaturePad' => 'required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
