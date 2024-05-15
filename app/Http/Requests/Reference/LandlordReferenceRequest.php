<?php

namespace App\Http\Requests\Reference;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LandlordReferenceRequest extends FormRequest
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
            'applicant_id' => 'required',
            'landlord_id' => 'required',
            'landlord_agent_name' => 'required',
            'rental_amount' => 'required',
            'starting_date' => 'required|before:ending_date',
            'ending_date' => 'required|after:starting_date',
            'company_name' => 'required',
            'landlord_position' => 'required',
            'signaturePad' => 'required|file_type_check'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
