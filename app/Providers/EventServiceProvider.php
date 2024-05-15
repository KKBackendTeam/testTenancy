<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\TenancyEvent' => [
            'App\Listeners\TenancyEventListener',
        ],
        'App\Events\ApplicantEvent' => [
            'App\Listeners\ApplicantEventListener'
        ],
        'App\Events\ReferenceEvent' => [
            'App\Listeners\ReferenceEventListener'
        ],
        'App\Events\SendEmailEvent' => [
            'App\Listeners\SendEmailEventListener'
        ],
        'App\Events\ApplicantAddDeleteEvent' => [
            'App\Listeners\ApplicantAddDeleteEventListener',
        ],
        'App\Events\ReviewReferenceEvent' => [
            'App\Listeners\ReviewReferenceEventListener',
        ],
        'App\Events\AgreementEvent' => [
            'App\Listeners\AgreementEventListner',
        ],
        'App\Events\EmploymentAddDeleteEvent' => [
            'App\Listeners\EmploymentAddDeleteListener',
        ],
        'App\Events\GuarantorAddDeleteEvent' => [
            'App\Listeners\GuarantorAddDeleteListener',
        ],
        'App\Events\LandlordAddDeleteEvent' => [
            'App\Listeners\LandlordAddDeleteListener',
        ],
        'App\Events\ResendEmailEvent' => [
            'App\Listeners\ResendEmailListener',
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
