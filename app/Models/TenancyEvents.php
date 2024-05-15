<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenancyEvents extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id', 'event_type'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

}
