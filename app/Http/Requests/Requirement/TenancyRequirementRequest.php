<?php

namespace App\Http\Requests\Requirement;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TenancyRequirementRequest extends FormRequest
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
            'no_pets' => 'required|boolean',
            'no_student' => 'required|boolean',
            'no_family' => 'required|boolean',
            'no_professional' => 'required|boolean',
            'tenancy_max_length' => 'required|integer|min:1',
            'start_month' => 'required|boolean',
            'end_month' => 'required|boolean'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
