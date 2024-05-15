<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'probation_period', 'three_time_salary', 'contract', 'agency_id', 'ae_least2'
    ];

    protected $hidden = [
        'password', 'remember_token', 'agency_id'
    ];
}
