<?php

namespace App\Http\Requests\Action;

use Illuminate\Foundation\Http\FormRequest;

class ExtendDedlineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'days' => 'bail|required|integer|between:1,1000'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
