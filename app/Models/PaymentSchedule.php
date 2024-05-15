<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
       'agency_id','applicant_id' ,'tenancy_id','date', 'amount'
    ];

    public function applicants()
    {
        return $this->belongsTo('App\Models\Applicant');
    }
}
