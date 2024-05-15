<?php

namespace App\Providers;

use Carbon\Carbon;
use Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('file_type_check', function ($attribute, $value, $params, $validator) {
            $pos = strpos($value, ';');
            if ($pos < 10) {
                return false;
            }
            $filetype = explode('/', substr($value, 0, $pos))[1];
            $typeArray = ['png', 'jpeg', 'jpg', 'pdf', 'doc', 'vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (in_array($filetype, $typeArray)) {
                return true;
            } else {
                return false;
            }
        });

        Validator::extend('document_check', function ($attribute, $value, $params, $validator) {
            $pos = strpos($value, ';');
            if ($pos < 10) {
                return false;
            }
            $filetype = explode('/', substr($value, 0, $pos))[1];
            $typeArray = ['png', 'jpeg', 'jpg', 'pdf', 'doc', 'vnd.openxmlformats-officedocument.wordprocessingml.document', 'xls', 'csv'];
            if (in_array($filetype, $typeArray)) {
                return true;
            } else {
                return false;
            }
        });

        Validator::extend('only_image', function ($attribute, $value, $params, $validator) {
            $pos = strpos($value, ';');
            if ($pos < 10) {
                return false;
            }
            $filetype = explode('/', substr($value, 0, $pos))[1];
            $typeArray = ['png', 'jpeg', 'jpg'];
            if (in_array($filetype, $typeArray)) {
                return true;
            } else {
                return false;
            }
        });

        Validator::extend('phone_number', function ($attribute, $value, $parameters) {
            return substr($value, 0, 2) == '01';
        });

        Validator::extend('older_than', function ($attribute, $value, $parameters) {
            date_diff(date_create(Carbon::now()->format('Y-m-d')), date_create($value))->y > 18 ? $response = true : $response = false;
            return $response;
        });

        Validator::extend('first_date_of_month', function ($attribute, $value, $parameters) {
            date_diff(date_create($value), date_create(Carbon::parse($value)->startOfMonth()))->d == 0 ? $response = true : $response = false;
            return $response;
        });

        Validator::extend('end_date_of_month', function ($attribute, $value, $parameters) {

            date_diff(date_create($value), date_create(Carbon::parse($value)->endOfMonth()))->d == 0 ? $response = true : $response = false;
            return $response;
        });

        Validator::extend('month_length', function ($attribute, $value, $parameters, $validator) {
            $validator->addReplacer('month_length', function ($message, $attribute, $rule, $parameters) {
                return str_replace([':difference'], $parameters, $message);
            });
            $parameters[1] >= $parameters[0] ? $response = true : $response = false;
            return $response;
        });
        Validator::extend("array_emails", function ($attribute, $value, $parameters) {

            foreach ($value as $key => $email) {
                if ($email['code'] == 'Applicant') $rules = ['email' => 'required|email|exists:applicants,email'];
                else if ($email['code'] == 'Guarantor') $rules = ['email' => 'required|email|exists:guarantor_references,email'];
                else if ($email['code'] == 'Landlord') $rules = ['email' => 'required|email|exists:landlord_references,email'];
                else if ($email['code'] == 'Employment') $rules = ['email' => 'required|email|exists:employment_references,company_email'];
                else $rules = ['email' => 'required|email'];
                $validator = Validator::make($email, $rules);
                if ($validator->fails()) {
                    return false;
                }
            }
            return true;
        });
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
