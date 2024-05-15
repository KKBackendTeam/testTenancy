<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuarantorReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_status', 'agency_id', 'applicant_id', 'status', 'addresses', 'notes_text', 'addresses_text', 'guarantor_income',
        'is_eighteen', 'decision_income_text', 'decision_id_text', 'decision_address_text', 'timezone',
        'fill_status', 'decision_income_action', 'decision_id_action', 'decision_address_action','last_response_time', 'other_document',
        'post_code', 'street', 'town' , 'country'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'other_document' => 'array'
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

    public function guarantorRefOtherDocument()
    {
        return $this->hasMany('App\Models\GuarantorRefOtherDocument', 'guarantor_ref_id');
    }
}
