<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'agency_confirm_link', 'status', 'media_logo', 'facebook', 'twitter', 'google_plus',
        'opening_time', 'closing_time', 'phone', 'address', 'total_credit', 'used_credit', 'isDefaultSetting',
        'last_login', 'country_code', 'schedule_time'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'agency_id');
    }
    public function agencyAdmin()
    {
        return $this->hasMany('App\Models\User', 'agency_id')->where('roleStatus', 1);
    }

    public function landlords()
    {
        return $this->hasMany('App\Models\Landloard');
    }

    public function properties()
    {
        return $this->hasMany('App\Models\Property');
    }

    public function tenancies()
    {
        return $this->hasMany('App\Models\Tenancy');
    }

    public function applicants()
    {
        return $this->hasMany('App\Models\Applicant');
    }

    public function employmentRef()
    {
        return $this->hasMany('App\Models\EmploymentReference');
    }

    public function guarantorRef()
    {
        return $this->hasMany('App\Models\GuarantorReference');
    }

    public function landlordRef()
    {
        return $this->hasMany('App\Models\LandlordReference');
    }

    public function quarterlyRef()
    {
        return $this->hasMany('App\Models\QuarterlyReference');
    }

    public function StudentRef()
    {
        return $this->hasMany('App\Models\StudentReference');
    }

    public function tenancyEvents()
    {
        return $this->hasMany('App\Models\TenancyEvents');
    }

    public function chasing()
    {
        return $this->hasOne('App\Models\Chasing');
    }

    public function mailServer()
    {
        return $this->hasOne('App\Models\MailServer');
    }

    public function defaultDocuments()
    {
        return $this->hasMany('App\Models\DefaultDocuments');
    }

    public function scheduleTime()
    {
        return $this->hasMany('App\Models\ScheduleTime');
    }
}
