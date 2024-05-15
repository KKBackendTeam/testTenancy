<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\Tenancy;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckApplicantStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ApplicantStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is for check the Applicant status and auto extend deadline';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->ApplicantStatus();
        $this->AutoExtendDeadline();
    }

    protected function ApplicantStatus()
    {
        $applicant = Applicant::with('applicantbasic')->with('tenancies')->with('agencies.chasing')->whereIn('status', [1, 2, 3, 4, 5])->get();

        foreach ($applicant as $app) {

            if (isPastChecker($app['tenancies']['deadline'], $app['applicantbasic']['timezone']) || $app['response_value'] >= $app['agencies']['chasing']['stalling_time']) {
                if ($app->status == 1) {
                    $app->update(['status' => 10]);
                } else if ($app->status == 5) {
                    $app->update(['status' => 12]);
                }
            }

            if ($app->status == 2) {
                $a = $b = $c = 0;
                if (!$app['employmentReferences']->isEmpty()) {
                    $a++;
                    if ($app['employmentReferences'][0]['response_value'] >= $app['agencies']['chasing']['stalling_time'] && $app['employmentReferences'][0]['fill_status'] == 0) {
                        $b++;
                    }
                    if ($app['employmentReferences'][0]['fill_status'] == 1) {
                        $c++;
                    }
                }
                if (!$app['landlordReferences']->isEmpty()) {
                    $a++;
                    if ($app['landlordReferences'][0]['response_value'] >= $app['agencies']['chasing']['stalling_time'] && $app['landlordReferences'][0]['fill_status'] == 0) {
                        $b++;
                    }
                    if ($app['landlordReferences'][0]['fill_status'] == 1) {
                        $c++;
                    }
                }
                if (!$app['guarantorReferences']->isEmpty()) {
                    $a++;
                    if ($app['guarantorReferences'][0]['response_value'] >= $app['agencies']['chasing']['stalling_time'] && $app['guarantorReferences'][0]['fill_status'] == 0) {
                        $b++;
                    }
                    if ($app['guarantorReferences'][0]['fill_status'] == 1) {
                        $c++;
                    }
                }
                if ($a > 0 && $b > 0  && $a != $c && $a == ($c + $b)) {
                    $app->update(['status' => 11]);
                }
            }
        }
        return "Done!";
    }

    protected function AutoExtendDeadline()
    {
        $tenancies = Tenancy::where('deadline', '<=', now())->whereIn('status', [2, 17, 5, 18])->with(['agencies.chasing'])->get();

        foreach ($tenancies as $tenancy) {
            $extendDeadline = Carbon::parse(now())->addDays($tenancy['agencies']['chasing']['response_time'] * $tenancy['agencies']['chasing']['stalling_time']);
            $tenancy->update(['deadline' => $extendDeadline]);

            foreach ($tenancy->applicants as $applicant) {
                $at = timeChangeAccordingToTimezoneForChasing(!empty($applicant->applicantbasic->timezone)
                    ? $applicant->applicantbasic->timezone : "UTC", $tenancy['agencies']['chasing']['response_time'], null);

                $NewStatus = $applicant->status;
                if ($applicant->status == 10) {
                    $NewStatus = 1;
                } else if ($applicant->status == 11) {
                    $NewStatus = 2;
                } else if ($applicant->status == 12) {
                    $NewStatus = 5;
                }

                $applicant->update(
                    [
                        'status' => $NewStatus, 'response_value' => 0, 'response_status' => 0, 'last_response_time' => $at
                    ]
                );
            }
        }
        return "Done!";
    }
}
