<?php

namespace App\Http\Requests\Reference;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmploymentReferenceRequest extends FormRequest
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
            'company_name' => 'required',
            'company_address' => 'required',
            'job_title' => 'required',
            'contract_type' => 'required',
            'annual_bonus' => 'required',
            'your_name' => 'required',
            'landlord_position' => 'required',
            'applicant_id' => 'required',
            'employment_id' => 'required',
            'signaturePad' => 'required|file_type_check'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
