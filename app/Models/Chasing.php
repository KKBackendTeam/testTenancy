<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chasing extends Model
{
    use HasFactory;
    protected $fillable = [
        'sms', 'email', 'cc', 'stalling_time', 'response_time','agency_id'
    ];

    protected $hidden = [
        'password', 'remember_token', 'agency_id'
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency');
    }
}
