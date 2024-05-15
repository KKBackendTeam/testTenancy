<?php

namespace App\Http\Requests\ApplicantViewTenancy;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddNewApplicantRequest extends FormRequest
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
        $rules = [
            'tenancy_id' => 'required',
            'applicant.app_f_name' => 'required',
            'applicant.app_l_name' => 'required',
            'applicant.app_mobile' => 'required',
            'applicant.app_renew_tenant' => 'required|integer|in:0,1',
        ];
        if ((integer)$this->toArray()['applicant']['app_renew_tenant'] == 0) {
            $rules += ['applicant.app_email' => 'required|email']; //unique:applicantbasics,email
        } else {
            $rules += ['applicant.app_email' => 'required|email']; //|exists:applicantbasics,email
        }
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['saved' => false, 'errors' => $validator->errors()]));
    }
}
