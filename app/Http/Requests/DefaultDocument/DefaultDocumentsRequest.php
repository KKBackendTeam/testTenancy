<?php

namespace App\Http\Requests\DefaultDocument;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DefaultDocumentsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [];
        if ($this->toArray()['status'] == 'edit') {
            $rules += ['id' => 'required|exists:default_documents,id', 'title' => 'required'];
        } else {
            $rules += ['title' => 'required', 'file' => 'required|document_check'];
        }
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
