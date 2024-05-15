<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [

    ];

    protected $hidden = [
        'remember_token','agency_id'
    ];
}
