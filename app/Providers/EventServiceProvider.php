<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [SendEmailVerificationNotification::class],
        "App\Events\InvitationCreatedEvent" => [
            "App\Listeners\SendInvitationCreatedEmails",
        ],
        "App\Events\AddedToUserBalanceEvent" => [
            "App\Listeners\SendInvoiceEmail",
        ],
        "App\Events\ClaimedUserBalanceEvent" => [
            "App\Listeners\SendClaimedUserBalanceEmails",
        ],
        "App\Events\Loan\CanceledEvent" => [
            "App\Listeners\SendLoanCanceledEmails",
        ],
        "App\Events\LoanCompletedEvent" => [
            "App\Listeners\RegisterLoanExpenses",
            "App\Listeners\SendLoanCompletedEmails"
        ],
        "App\Events\LoanCreatedEvent" => [
            "App\Listeners\SendLoanCreatedEmails",
        ],
        "App\Events\LoanIntentionAcceptedEvent" => [
            "App\Listeners\SendLoanIntentionAcceptedEmails",
        ],
        "App\Events\LoanIntentionRejectedEvent" => [
            "App\Listeners\SendLoanIntentionRejectedEmails",
        ],
        "App\Events\LoanIncidentCreatedEvent" => [
            "App\Listeners\SendLoanIncidentCreatedEmails",
        ],
        "App\Events\LoanIncidentResolvedEvent" => [
            "App\Listeners\SendLoanIncidentResolvedEmails",
        ],
        "App\Events\LoanExtensionCreatedEvent" => [
            "App\Listeners\SendLoanExtensionCreatedEmails",
        ],
        "App\Events\LoanExtensionAcceptedEvent" => [
            "App\Listeners\SendLoanExtensionAcceptedEmails",
        ],
        "App\Events\LoanExtensionRejectedEvent" => [
            "App\Listeners\SendLoanExtensionRejectedEmails",
        ],
        "App\Events\LoanHandoverContestationResolvedEvent" => [
            "App\Listeners\SendLoanHandoverContestationResolvedEmails",
        ],
        "App\Events\LoanHandoverContestedEvent" => [
            "App\Listeners\SendLoanHandoverContestedEmails",
        ],
        "App\Events\LoanTakeoverContestedEvent" => [
            "App\Listeners\SendLoanTakeoverContestedEmails",
        ],
        "App\Events\LoanTakeoverContestationResolvedEvent" => [
            "App\Listeners\SendLoanTakeoverContestationResolvedEmails",
        ],
        "App\Events\LoanPaidEvent" => ["App\Listeners\SendInvoiceEmail"],
        "App\Events\RegistrationSubmittedEvent" => [
            "App\Listeners\SendRegistrationSubmittedEmails",
            "App\Listeners\AddUserToNewsletterIfNotRegistered",
        ],
        "App\Events\RegistrationRejectedEvent" => [
            "App\Listeners\SendRegistrationRejectedEmails",
        ],
        "App\Events\BorrowerCompletedEvent" => [
            "App\Listeners\SendBorrowerCompletedEmails",
        ],
        "App\Events\LoanableCreatedEvent" => [
            "App\Listeners\SendLoanableCreatedEmails",
        ],
        "App\Events\BorrowerSuspendedEvent" => [
            "App\Listeners\CancelFutureLoans",
            "App\Listeners\SendBorrowerSuspendedEmails",
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
