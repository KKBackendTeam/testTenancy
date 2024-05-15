<?php

namespace App\Console\Commands;

use App\Notifications\RenewTenancyNotification;
use App\Models\Tenancy;
use Illuminate\Console\Command;
use App\Notifications\TenancyNotification;
use App\Models\Property;

class checkTenancyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tenancyStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is for check the tenancy status';

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
        $this->tenancyStatus();
        $this->propertyStatus();
    }

    public function tenancyStatus()
    {
        $tenancies = Tenancy::whereNotIn('status', [9, 10])->get();
        foreach ($tenancies as $tenancy) {

            if ($tenancy->applicants()->count() < 1) continue;
            $tenancyStatus = 0;
            $timezone = $tenancy->applicants()->count() > 0 ? (!empty($tenancy->timezone) ? $tenancy->timezone : 'UTC') : 'UTC';

            if (in_array($tenancy['status'], [11])) {
                if (
                    in_array($tenancy->properties->status, [4, 5, 6])
                    && $tenancy->isSection21 == 0
                ) {
                    $this->checkIfRenewAndSection21($tenancy, $timezone);
                    $tenancyStatus = 100;
                } elseif (isPastChecker($tenancy->t_end_date, $timezone)) {  //checking for tenancy expired
                    $tenancy->properties()->update(['status' => 1]);
                    $tenancy->applicants()->update(['status' => 9]);
                    $tenancy->update(['status' => 9]);
                    $tenancyStatus = 9;
                } else if (isPastChecker($tenancy->t_start_date, $timezone)) {
                    //$tenancy->applicants()->update(['status' => 5]);
                }
            }

            if ($tenancyStatus != 100 && $tenancyStatus < 11 && $tenancyStatus > 0) {
                $tenancy->creator->notify(new TenancyNotification($tenancy, $tenancyStatus));
            }
        }
        return "Done!";
    }

    public function checkIfRenewAndSection21($tenancy, $timezone)
    {
        if ($tenancy->properties->latestTenancy[0]->id == $tenancy->id) {
            $diff = date_diff(date_create($tenancy->t_end_date), date_create(now()->setTimezone(new \DateTimeZone($timezone))))->format("%a");
            if ($diff < 67) {
                $tenancy->creator->notify(new RenewTenancyNotification($tenancy));
                $tenancy->properties()->update(['status' => 2]);
                $tenancy->update(['renew_tenancy' => 1, 'isSection21' => 1]);
                return true;
            }
            return true;
        } else {
            $tenancy->update(['isSection21' => 1]);
            return true;
        }
    }

    public function propertyStatus()
    {
        $properties = Property::whereNotIn('status', [6])->get();

        foreach ($properties as $property) {

            if (!isset($property->latestTenancy[0])) continue;
            $timezone = $property->latestTenancy[0]->applicants()->count() > 0 ? (!empty($property->latestTenancy[0]->timezone) ? $property->latestTenancy[0]->timezone : "UTC") : 'UTC';

            if (isPastChecker($property->latestTenancy[0]->t_end_date, $timezone) && in_array($property->status, [2, 3])) {
                $property->update(['status' => 1]);
                $property->tenancies()->update(['renew_tenancy' => 0]);
            }
        }
    }
}
