<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuarantorRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id','must_be_18','living_in_uk','three_time_salary'
    ];

    protected $hidden = ['agency_id'];
}
