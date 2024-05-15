<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuarterlyRequirement extends Model
{
    use HasFactory;
    protected $fillable = [
        'monthly_3x', 'agency_id'
    ];

    protected $hidden = [
        'agency_id'
    ];
}
