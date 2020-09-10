<?php

namespace App\Console\Commands;

use App\Mail\Loan\PrePaymentMissing as LoanPrePaymentMissing;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class EmailLoanPrePaymentMissing extends Command
{
    protected $signature = 'email:loan:pre_payment_missing
                            {--pretend : Do not send emails}';

    protected $description = 'Send emails when pre-payment is still missing in 24 hours';

    private $pretend = false;

    public function handle() {
        if ($this->option('pretend')) {
            $this->pretend = true;
        }

        $this->info('Fetching loans in 24 hours created '
          . 'at least three hours before now...');
        $threeHoursAgo = (new Carbon())->subtract(3, 'hours');

        $query = Loan::departureInLessThan(24, 'hours')
            ->where('loans.created_at', '<', $threeHoursAgo)
            ->whereHas('loanable', function ($q) {
                return $q->whereHas('owner');
            })
            ->whereHas('prePayment', function ($q) {
                return $q->where('pre_payments.status', '=', 'in_process');
            })
            ->where('meta->sent_loan_pre_payment_missing_email', null);

        $columnDefinitions = Loan::getColumnsDefinition();
        $query = $columnDefinitions['loan_status']($query);
        $query = $columnDefinitions['*']($query);

        $loans = $query
            ->havingRaw("{$columnDefinitions['loan_status']()} = 'in_process'")
            ->cursor();

        foreach ($loans as $loan) {
            $user = $loan->borrower->user;
            if (!$this->pretend) {
                $this->info("Sending email to $user->email");

                Mail::to($user->email, $user->name . ' ' . $user->last_name)
                  ->send(new LoanPrePaymentMissing($user, $loan));

                $loan->forceFill([
                    'meta' => [ 'sent_loan_pre_payment_missing_email' => true ],
                ])->save();
            } else {
                $this->info("Would have sent an email to {$user->email} for loan {$loan->id}");
            }
        }

        $this->info('Done.');
    }
}
