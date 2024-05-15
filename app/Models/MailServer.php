<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailServer extends Model
{
    use HasFactory;

    protected $table = 'mail_servers';

    protected $fillable = [
        'driver', 'host', 'port', 'from_name', 'from_address', 'encryption', 'mailServer', 'username', 'password', 'agency_id'
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency');
    }
}
