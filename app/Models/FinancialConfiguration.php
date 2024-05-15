<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'amount', 'period', 'method', 'agency_id'
    ];

    protected $hidden = [
        'password', 'remember_token', 'agency_id'
    ];
}
