<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'status', 'available_from', 'creator_id', 'monthly_rent', 'total_rent', 'previous_status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function landlords()
    {
        return $this->belongsTo('App\Models\Landloard', 'landlord_id');
    }

    public function tenancies()
    {
        return $this->hasMany('App\Models\Tenancy');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'creator_id');
    }

    public function latestTenancies()
    {
        return $this->hasMany('App\Models\Tenancy')->where('status', '!=', 10)->latest();
    }

    public function latestTenancy()
    {
        return $this->hasMany('App\Models\Tenancy')->where('status', '!=', 10)->latest()->take(1);
    }

    public function getTotalAttribute()
    {
        return $this->hasMany('App\Models\Landloard')->count();
    }
}
