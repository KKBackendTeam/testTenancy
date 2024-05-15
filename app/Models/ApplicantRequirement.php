<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantRequirement extends Model
{
    use HasFactory;
    protected $fillable = [
        'agency_id', 'must_be_18', 'ae_less3_must_g', 'ae_least2', 'as_ukr_must_ukg', 'as_ir_pay_pqa', 'a_not_ukg_pqa'
    ];

    protected $hidden = [
        'password', 'remember_token', 'agency_id'
    ];
}
