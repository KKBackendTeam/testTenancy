<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TenancyHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenancy_id' , 'agency_id' , 'agreement_type', 'signing_date', 'agreement', 'text_code', 'generated_date'
    ];

    public function tenancies()
    {
        return $this->belongsTo('App\Models\Tenancy', 'tenancy_id');
    }
}
