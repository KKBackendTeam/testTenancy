<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;
    protected $fillable = [
        'status', 'ref_agency_status', 'agreement', 'tenancy_id', 'agency_id', 'creator_id', 'ref_status', 'ref_agency_status',
        'renew_status', 'agreement_signature', 'ta_status', 'response_value', 'marketing_in_out', 'ref_code', 'type', 'step',
        'temporary_password', 'creatr_id', 'level_1', 'level_2', 'level_3', 'level_4', 'app_name', 'l_name', 'app_lookup', 'app_mobile',
        'country_code', 'm_name', 'password_link', 'notes_text', 'review_agreement', 'address', 'addresses', 'family_addresses',
        'total_references', 'fill_references', 'reference_tracker', 'addresses_text', 'signing_time', 'is_complete', 'ip_address','last_response_time','is_paused', 'pause_end_date', 'pause_start_date'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',  'remember_token'
    ];

    public function applicantbasic()
    {
        return $this->belongsTo('App\Models\Applicantbasic', 'applicant_id');
    }

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function landlords()
    {
        return $this->belongsTo('App\Models\Landlord');
    }

    public function tenancies()
    {
        return $this->belongsTo('App\Models\Tenancy', 'tenancy_id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }

    public function employmentReferences()
    {
        return $this->hasMany('App\Models\EmploymentReference')->latest();
    }

    public function guarantorReferences()
    {
        return $this->hasMany('App\Models\GuarantorReference')->latest();
    }

    public function landlordReferences()
    {
        return $this->hasMany('App\Models\LandlordReference')->latest();
    }

    public function quarterlyReferences()
    {
        return $this->hasMany('App\Models\QuarterlyReference')->latest();
    }

    public function studentReferences()
    {
        return $this->hasMany('App\Models\StudentReference')->latest();
    }

    public function paymentSchedule()
    {
        return $this->hasMany('App\Models\PaymentSchedule', 'applicant_id');
    }
}
