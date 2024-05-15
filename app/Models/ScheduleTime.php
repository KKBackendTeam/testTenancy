<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTime extends Model
{
    use HasFactory;
    protected $table = 'schedule_times';

    protected $fillable = ['day', 'opening_time', 'closing_time'];

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency');
    }
}
