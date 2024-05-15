<?php

namespace App\Jobs;

use App\Models\ApplicantRequirement;
use App\Models\Chasing;
use App\Models\EmploymentRequirement;
use App\Models\FinancialConfiguration;
use App\Models\GuarantorRequirement;
use App\Models\LandlordRequirement;
use App\Models\TenancyRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DefaultSetting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $agency_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->agency_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Chasing::updateOrCreate(['agency_id' => $this->agency_id]);
        ApplicantRequirement::updateOrCreate(['agency_id' => $this->agency_id]);
        EmploymentRequirement::updateOrCreate(['agency_id' => $this->agency_id]);
        FinancialConfiguration::updateOrCreate(['agency_id' => $this->agency_id]);
        GuarantorRequirement::updateOrCreate(['agency_id' => $this->agency_id]);
        LandlordRequirement::updateOrCreate(['agency_id' => $this->agency_id]);
        TenancyRequirement::updateOrCreate(['agency_id' => $this->agency_id]);
    }
}
