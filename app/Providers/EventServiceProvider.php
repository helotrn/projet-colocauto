<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\AddedToUserBalanceEvent' => [
            'App\Listeners\SendAddedToUserBalanceEmails',
        ],
        'App\Events\ClaimedUserBalanceEvent' => [
            'App\Listeners\SendClaimedUserBalanceEmails',
        ],
        'App\Events\LoanCreatedEvent' => [
            'App\Listeners\SendLoanCreatedEmails',
        ],
        'App\Events\RegistrationSubmittedEvent' => [
            'App\Listeners\SendRegistrationSubmittedEmails',
        ],
        'App\Events\LoanableCreatedEvent' => [
            'App\Listeners\SendLoanableCreatedEmails',
        ],
    ];

    public function boot() {
        parent::boot();
    }
}
