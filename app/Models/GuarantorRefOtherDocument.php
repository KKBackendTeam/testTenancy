<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuarantorRefOtherDocument extends Model
{
    use HasFactory;

    protected $table = 'guarantor_ref_other_documents';

    protected $fillable = ['doc', 'decision_action', 'decision_text'];

    public function guarantorReference()
    {
        return $this->belongsTo('App\Models\GuarantorReference', 'id');
    }
}
