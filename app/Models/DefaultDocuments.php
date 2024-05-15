<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultDocuments extends Model
{
    protected $fillable = [
        'agency_id', 'title', 'doc'
    ];

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }
}
