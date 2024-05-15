<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'paid_rent', 'damage', 'move_out', 'recommended_tenant','agency_id'
    ];

    protected $hidden = [
        'password', 'remember_token','agency_id'
    ];
}
