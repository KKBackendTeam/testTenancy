<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReference extends Model
{
    use HasFactory;
    protected $fillable = [
        'agency_id', 'applicant_id', 'uni_name', 'course_title', 'year_grad'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function ref()
    {
        return $this->belongsTo('App\Models\Applicant', 'id');
    }

    public function applicant()
    {
        return $this->belongsTo('App\Models\Applicant', 'applicant_id');
    }

    public function applicants()
    {
        return $this->belongsTo('App\Models\Applicant', 'applicant_id');
    }
}
