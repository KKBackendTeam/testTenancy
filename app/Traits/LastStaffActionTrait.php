<?php

namespace App\Traits;

use App\Models\User;
use Carbon\Carbon;

trait LastStaffActionTrait
{
    public function lastStaffAction($lastAction)
    {
        User::find(auth()->id())->update(['last_action' => $lastAction, 'last_action_date' => now()]);
    }
}
