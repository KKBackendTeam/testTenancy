<?php

namespace App\Http\Requests\Requirement;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApplicantRequirementRequest extends FormRequest
{
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
            'must_be_18' => 'required|boolean',
            'ae_less3_must_g' => 'required|boolean',
            'ae_least2' => 'required|boolean',
            'as_ir_pay_pqa' => 'required|boolean',
            'as_ukr_must_ukg' => 'required|boolean',
            'a_not_ukg_pqa' => 'required|boolean'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
