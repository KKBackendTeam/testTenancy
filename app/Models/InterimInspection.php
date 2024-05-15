<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterimInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'reference',
        'address',
        'inspection_month',
        'inspection_date',
        'email_date',
        'comment',
        'is_done'
    ];

    protected $casts = [
        'comment' => 'array'
    ];

    public function tenancy()
    {
        return $this->belongsTo('App\Models\Tenancy', 'tenancy_id');
    }
}
