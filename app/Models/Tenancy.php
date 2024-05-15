<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenancy extends Model
{
    use HasFactory;
    protected $fillable = [
        'status', 'notes', 'agency_id', 'creator_id', 'agreement', 'ta_status', 'signing_date', 'tc_status', 'deadline', 't_end_date',
        'creator_id', 'applicants_ids', 'days_to_complete', 'pro_address', 'monthly_amount', 'deposite_amount', 'reference', 'parking',
        'parking_cost', 'restriction', 'rent_include', 't_start_date', 'type', 'no_applicant', 'notes', 'notes_text',
        'review_agreement', 'total_rent', 'renew_tenancy', 'reviewer_id', 'isSection21', 'updated_at', 'generated_date', 'timezone',
        'terminated_date'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    public $timestamps = false;

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function landlords()
    {
        return $this->belongsTo('App\Models\Landloard', 'landlord_id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\Models\User', 'reviewer_id');
    }

    public function applicants()
    {
        return $this->hasMany('App\Models\Applicant', 'tenancy_id');
    }

    public function properties()
    {
        return $this->belongsTo('App\Models\Property', 'property_id');
    }

    public function tenancyEvent()
    {
        return $this->hasMany('App\Models\TenancyEvents');
    }

    public function latest_update()
    {
        return $this->hasMany('App\Models\TenancyEvents')->latest()->take(1);
    }

    public function tenancyHistory()
    {
        return $this->hasMany('App\Models\TenancyHistory');
    }

    public function tenancyInterimInspection()
    {
        return $this->hasMany('App\Models\InterimInspection');
    }
}
