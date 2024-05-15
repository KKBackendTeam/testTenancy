<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordReference extends Model
{
    use HasFactory;
    protected $fillable = [
        'agency_status', 'agency_id', 'applicant_id', 'status', 'addresses', 'notes_text', 'addresses_text',
        'decision_text', 'timezone', 'fill_status', 'reference_action','last_response_time', 'post_code', 'street', 'town' , 'country'
    ];

    protected $hidden = [
        'password', 'remember_token', 'agency_id'
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
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
