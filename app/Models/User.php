<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'agency_link', 'agency_address', 'agency_status', 'agency_id',
        'roleStatus', 'email_status', 'is_active', 'last_action', 'last_action_date', 'defaltPassword',
        'selfie_pic', 'last_login', 'country_code', 'agreement_signature', 'signing_time', 'ip_address', 'timezone',
        'otp', 'otp_created_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function landloards()
    {
        return $this->hasMany('App\Models\Landloard', 'creator_id');
    }

    public function tenancies()
    {
        return $this->hasMany('App\Models\Tenancy', 'creator_id');
    }

    public function applicants()
    {
        return $this->hasMany('App\Models\Applicant', 'creator_id');
    }

    public function properties()
    {
        return $this->hasMany('App\Models\Property', 'creator_id');
    }

    public static function staff_member()
    {
        return User::where('agency_id', JWTAuth::parseToken()->authenticate()->agency_id)->where('roleStatus', 0)->get();
    }
}
