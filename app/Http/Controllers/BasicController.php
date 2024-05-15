<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Applicantbasic;
use App\Models\Tenancy;
use App\Traits\WorkWithFile;

class BasicController extends Controller
{
    use WorkWithFile;

    public function welcome()
    {
        return view('welcome');
    }

    public function work()
    {
        $te = Applicantbasic::all();
        return $te;
    }

    public function work_tenancy()
    {
        // $te = Tenancy::all();

        // foreach ($te as $t) {
        //     if (in_array($t->status, [1, 2, 3, 4, 12, 13, 14, 15])) {
        //         $t->update(['status' => 2]);
        //     }

        //     if ($t->status == 16) {
        //         $t->update(['status' => 5]);
        //     }
        // }
        // return "Done!.";
    }

    public function work_applicant()
    {
        // $app = Applicant::all();
        // foreach ($app as $a) {
        //     if ($a->status == 3 || $a->status == 4) {
        //         if ($a->total_references > 0 && $a->fill_references > 0 && $a->total_references == $a->fill_references) {
        //             $a->update(['status' => 3]);
        //         } else {
        //             $a->update(['status' => 2]);
        //         }
        //     }

        //     if (in_array($a->status, [8, 9])) {
        //         $a->update(['status' => 7]);
        //     }

        //     if ($a->status == 6) {
        //         $this->deleteSingleApplicant($a);
        //     }
        // }

        // return $app;
    }
}
