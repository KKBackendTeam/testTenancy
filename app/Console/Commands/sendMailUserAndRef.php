<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\Applicant;
use App\Mail\ApplicantChasingEmail;
use App\Notifications\ReferenceResponseNotificationToAgency;
use App\Notifications\ResponseNotificationToAgency;
use Illuminate\Console\Command;
use Mail;
use Illuminate\Support\Str;
use App\Traits\TextForSpecificAreaTrait;
use App\Traits\RunTimeEmailConfigrationTrait;
use App\Events\SendEmailEvent;
use App\Mail\CCEmail;
use Illuminate\Support\Facades\Artisan;

class sendMailUserAndRef extends Command
{
    use TextForSpecificAreaTrait, RunTimeEmailConfigrationTrait;

    public $referenceClassArray = [
        'employmentReferences' => 'App\Mail\EmploymentChasingEmail',
        'guarantorReferences' => 'App\Mail\GuarantorChasingEmail',
        'landlordReferences' => 'App\Mail\LandlordChasingEmail'
    ];

    public $referenceTypeArray = [
        'employmentReferences' => 'Employment',
        'guarantorReferences' => 'Guarantor',
        'landlordReferences' => 'Landlord'
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendMailToUserAndRef';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command usually send a chasing email to the applicant and users';

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
        $this->sendResponsesToApplicant();
        $this->sendResponsesToApplicantForAgreementSign();
        $this->sendResponsesToReferences();

        return true;
    }

    protected function sendResponsesToApplicant()
    {
        $applicant = Applicant::where('log_status', 0)->orWhere('is_complete', 1)
            ->with('applicantbasic')->with('agencies.chasing')->with('agencies.mailServer')->get();

        foreach ($applicant as $app) {

            if ($app['response_value'] > $app['agencies']['chasing']['stalling_time']) continue;  //can not perform any action, bcz we already send an alert notification to the agency.

            if ($app->is_paused && now() >= $app->pause_end_date) {
                $app->update(['is_paused' => false, 'pause_end_date' => null]);
            }

            if ($app->is_paused && now() < $app->pause_end_date) {
                echo nl2br("Application statement: Skipping applicant {$app->id} due to pause status. Pause end date: {$app->pause_end_date}\n");
                continue;
            }

            if ($app['response_value'] >= $app['agencies']['chasing']['stalling_time']) {
                $app->increment('response_value');
                $app->tenancies->creator->notify(new ResponseNotificationToAgency($app->tenancies, $app));
            } else {

                $endTime = strtotime($app['last_response_time']);
                $timezoneNow = strtotime(now()->setTimezone(new \DateTimeZone(!empty($app['applicantbasic']['timezone']) ?  $app['applicantbasic']['timezone'] : "UTC")));

                if ($endTime > $timezoneNow) continue;

                $app->app_url = is_null($app->app_url) ? config('global.frontSiteUrl') . ('/applicant/initial_login$email=' . $app['applicantbasic']['email'] . '&code=' . Str::random(15)) : $app->app_url;
                $app->response_value = $app['response_value'] + 1;   //increment response value
                $app->last_response_time = timeChangeAccordingToTimezoneForChasing((!empty($app['applicantbasic']['timezone']) ?  $app['applicantbasic']['timezone'] : "UTC"), $app['agencies']['chasing']['response_time'], $app['agencies']['chasing']['stalling_time']);   //last response time update for the applicant

                $app->save();
                echo nl2br("Application statement : Sending Mail to this user id : " . $app['id'] . "\n");
                $this->runTimeEmailConfiguration($app->agency_id);

                $agencyData = Agency::where('id', $app->agency_id)->firstOrFail();
                $data = $this->emailTemplateData('ACE', $app['applicantbasic'], $app->tenancies, $agencyData, null, null, null, null, null, null, null);

                if ($app['agencies']['chasing']['cc'] == true) {
                    Mail::to($app['applicantbasic']['email'])->send(new ApplicantChasingEmail($data, $agencyData, $app, 0));
                } else {
                    Mail::to($app['applicantbasic']['email'])->send(new ApplicantChasingEmail($data, $agencyData, $app, 0));
                }
                event(new SendEmailEvent($app->tenancy_id, 'Chase email', 'Chase email sent to applicants and references', $app['applicantbasic']['email'], $data, $app->agency_id));
                Artisan::call('config:cache');
            }
        }
        return "Done!";
    }

    protected function sendResponsesToApplicantForAgreementSign()
    {
        $applicant = Applicant::where('status', 5)
            ->where('ta_status', 0)->with('applicantbasic')->with('agencies.chasing')->with('agencies.mailServer')->get();

        foreach ($applicant as $app) {

            if ($app['response_value'] > $app['agencies']['chasing']['stalling_time']) continue;  //can not perform any action, bcz we already send an alert notification to the agency.

            if ($app['response_value'] >= $app['agencies']['chasing']['stalling_time']) {
                $app->increment('response_value');
                $app->tenancies->creator->notify(new ResponseNotificationToAgency($app->tenancies, $app));
            } else {

                $endTime = strtotime($app['last_response_time']);
                $timezoneNow = strtotime(now()->setTimezone(new \DateTimeZone(!empty($app['applicantbasic']['timezone']) ?  $app['applicantbasic']['timezone'] : "UTC")));

                if ($endTime > $timezoneNow) continue;

                $app->response_value = $app['response_value'] + 1;
                $app->last_response_time = timeChangeAccordingToTimezoneForChasing((!empty($app['applicantbasic']['timezone']) ?  $app['applicantbasic']['timezone'] : "UTC"), $app['agencies']['chasing']['response_time'], $app['agencies']['chasing']['stalling_time']);   //last response time update for the applicant

                $app->save();

                echo nl2br("Signing Mail : Sending Mail to this user id : " . $app['id'] . "\n");
                $this->runTimeEmailConfiguration($app->agency_id);

                $agencyData = Agency::where('id', $app->agency_id)->firstOrFail();
                $data = $this->emailTemplateData('ACE', $app['applicantbasic'], $app->tenancies, $agencyData, null, null, null, null, null, null, null);

                if ($app['agencies']['chasing']['cc'] == true) {
                    Mail::to($app['applicantbasic']['email'])->send(new ApplicantChasingEmail($data, $agencyData, $app, 1));
                } else {
                    Mail::to($app['applicantbasic']['email'])->send(new ApplicantChasingEmail($data, $agencyData, $app, 1));
                }
                event(new SendEmailEvent($app->tenancy_id, 'Chase email', 'Chase email sent to applicants and references', $app['applicantbasic']['email'], $data, $app->agency_id));
                Artisan::call('config:cache');
            }
        }
        return "Done!";
    }

    protected function sendResponsesToReferences()
    {
        $fillStatusCondition = function ($query) {
            $query->where('fill_status', 0)->get();
        };

        $applicantRef = Applicant::where('log_status', 1)
            ->with('applicantbasic')->with('agencies.chasing')->with('agencies.mailServer')
            ->with(['employmentReferences' => $fillStatusCondition])
            ->with(['guarantorReferences' => $fillStatusCondition])
            ->with(['landlordReferences' => $fillStatusCondition])
            ->get();

        foreach ($applicantRef as $app) {

            if (!$this->isConditionTrue($app, 'employmentReferences')) {

                if ($app['employmentReferences'][0]['response_value'] > $app['agencies']['chasing']['stalling_time']) continue;

                if (!$this->isTheLastResponse($app, 'employmentReferences')) {
                    $this->sendMailToTheReferences($app, 'employmentReferences', 'employment', 'company_email', 'ERCE');
                }
            }

            if (!$this->isConditionTrue($app, 'guarantorReferences')) {

                if ($app['guarantorReferences'][0]['response_value'] > $app['agencies']['chasing']['stalling_time']) continue;

                if (!$this->isTheLastResponse($app, 'guarantorReferences')) {
                    $this->sendMailToTheReferences($app, 'guarantorReferences', 'guarantor', 'email', 'GRCE');
                }
            }

            if (!$this->isConditionTrue($app, 'landlordReferences')) {

                if ($app['landlordReferences'][0]['response_value'] > $app['agencies']['chasing']['stalling_time']) continue;

                if (!$this->isTheLastResponse($app, 'landlordReferences')) {
                    $this->sendMailToTheReferences($app, 'landlordReferences', 'landlord', 'email', 'LRCE');
                }
            }
        }
        return "Done!";
    }

    public function isConditionTrue($app, $referenceType)
    {
        return $app[$referenceType]->isEmpty();
    }

    public function isTheLastResponse($app, $referenceType)
    {
        if ($app[$referenceType][0]['response_value'] >= $app['agencies']['chasing']['stalling_time']) {

            $app[$referenceType][0]['response_value'] = $app[$referenceType][0]['response_value'] + 1;
            $app[$referenceType][0]->save();
            $app->tenancies->creator->notify(new ReferenceResponseNotificationToAgency($app->tenancies, $app[$referenceType][0], $app, $referenceType));
            return true;
        }
        return false;
    }

    public function sendMailToTheReferences($app, $referenceType, $referenceName, $emailName, $emailType)
    {
        $endTime = strtotime($app[$referenceType][0]['last_response_time']);
        $timezoneNow = strtotime(now()->setTimezone(new \DateTimeZone(!empty($app[$referenceType][0]->timezone) ? $app[$referenceType][0]->timezone : "UTC")));

        if ($endTime > $timezoneNow) return false;

        $refEmail = $app[$referenceType][0][$emailName];
        $app[$referenceType][0]->ref_link = $this->referenceLinkGenerator($app, $referenceType, $referenceName);
        $app[$referenceType][0]->response_value = $app[$referenceType][0]['response_value'] + 1;
        $app[$referenceType][0]->last_response_time = timeChangeAccordingToTimezoneForChasing((!empty($app[$referenceType][0]->timezone) ? $app[$referenceType][0]->timezone : "UTC"), $app['agencies']['chasing']['response_time'], $app['agencies']['chasing']['stalling_time']);
        $app[$referenceType][0]->save();

        $this->runTimeEmailConfiguration($app->agency_id);
        $agencyData = Agency::where('id', $app->agency_id)->firstOrFail();

        if ($referenceType == 'employmentReferences') {
            $data = $this->emailTemplateData($emailType, $app['applicantbasic'], $app->tenancies, $agencyData, null, $app[$referenceType][0], null, null, null, null, null);
        } elseif ($referenceType == 'guarantorReferences') {
            $data = $this->emailTemplateData($emailType, $app['applicantbasic'], $app->tenancies, $agencyData, null, null, $app[$referenceType][0], null, null, null, null);
        } else {
            $data = $this->emailTemplateData($emailType, $app['applicantbasic'], $app->tenancies, $agencyData, null, null, null, $app[$referenceType][0], null, null, null);
        }

        if ($app['agencies']['chasing']['cc'] == true) {

            Mail::to($refEmail)->send(new $this->referenceClassArray[$referenceType]($data, $agencyData, $app[$referenceType][0], $app));
            Mail::to($app['applicantbasic']['email'])->send(new CCEmail($data, $agencyData, $app[$referenceType][0], $app, $this->referenceTypeArray[$referenceType]));
        } else {
            Mail::to($refEmail)->send(new $this->referenceClassArray[$referenceType]($data, $agencyData, $app[$referenceType][0], $app));
        }
        event(new SendEmailEvent($app->tenancy_id, 'Chase email', 'Chase email sent to applicants and references', $refEmail, $data, $app->agency_id));
        Artisan::call('config:cache');
        return true;
    }

    public function referenceLinkGenerator($app, $referenceType, $linkFor)
    {
        return is_null($app[$referenceType][0]['ref_link']) ? config('global.frontSiteUrl') . ('/' . $linkFor . '/' . Str::random(8) . '/' . Str::random(15)) : $app[$referenceType][0]['ref_link'];
    }
}
