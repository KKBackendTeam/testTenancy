<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenancyRequirement extends Model
{
    use HasFactory;
    protected $fillable = [
        'agency_id','no_pets', 'no_student', 'no_family','no_professional', 'tenancy_max_length', 'start_month', 'end_month',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
