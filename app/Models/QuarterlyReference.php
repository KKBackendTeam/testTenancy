<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuarterlyReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id', 'applicant_id', 'fill_date', 'fill_status', 'status', 'agency_status',
        'decision_text', 'reference_action','notes',
    ];

    protected $casts = [
        'qu_doc' => 'array',
        'close_bal' => 'decimal:2',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function applicants()
    {
        return $this->belongsTo('App\Models\Applicant', 'applicant_id');
    }
}
