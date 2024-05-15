<?php

namespace App\Http\Requests\ActionRequire;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendCustomEmailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'applicant_id' => 'required',
            'send_code' => 'required|in:A,E,G,L',
            'subject' => 'required',
            'message' => 'required'
        ];

        if ($this->toArray()['send_code'] == 'A') {
            $rules += ['email' => 'required|exists:applicantbasics'];
        } else if ($this->toArray()['send_code'] == 'E') {
            $rules += ['email' => 'required|exists:employment_references,company_email'];
        } else if ($this->toArray()['send_code'] == 'G') {
            $rules += ['email' => 'required|exists:guarantor_references'];
        } else {
            $rules += ['email' => 'required|exists:landlord_references'];
        }
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
