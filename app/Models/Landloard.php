<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landloard extends Model
{
    use HasFactory;
    protected $fillable = [
        'agency_id', 'creator_id', 'f_name', 'l_name', 'post_code', 'street', 'town', 'country', 'mobile', 'email', 'self_ref',
        'creator_id'
    ];


    protected $hidden = [
        'password', 'remember_token'
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }

    public function applicants()
    {
        return $this->hasMany('App\Models\Applicant', 'landlord_id');
    }

    public function tenancies()
    {
        return $this->hasMany('App\Models\Tenancy', 'landlord_id');
    }

    public function properties()
    {
        return $this->hasMany('App\Models\Property', 'landlord_id');
    }
}
