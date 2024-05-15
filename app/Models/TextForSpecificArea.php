<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextForSpecificArea extends Model
{
    use HasFactory;
    protected $fillable = [
        'text_code', 'agency_id', 'data'
    ];
}
