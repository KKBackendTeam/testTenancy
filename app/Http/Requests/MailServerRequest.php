<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class MailServerRequest extends FormRequest
{

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'driver' => 'required',
            'host' => 'required',
            'port' => 'required',
            'from_name' => 'required',
            'from_address' => 'required',
            'encryption' => 'required',
            'username' => 'required',
            'password' => 'required',
        ];
    }
}
