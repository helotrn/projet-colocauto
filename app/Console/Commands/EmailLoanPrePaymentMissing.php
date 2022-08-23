<?php

namespace App\Console\Commands;

use App\Mail\Loan\PrePaymentMissing as LoanPrePaymentMissing;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;
use Log;

class EmailLoanPrePaymentMissing extends Command
{
    protected $signature = 'email:loan:pre_payment_missing
                            {--pretend : Do not send emails}';

    protected $description = "Send emails when pre-payment is still missing in 24 hours";

    private $pretend = false;

    public function handle()
    {
        Log::info(
            "Fetching loans in 24 hours created " .
                "at least three hours before now..."
        );
        $threeHoursAgo = (new Carbon())->subtract(3, "hours");

        $query = $this->getQuery(["created_at" => $threeHoursAgo]);

        $loans = $query->cursor();
        foreach ($loans as $loan) {
            $user = $loan->borrower->user;
            if (!$this->pretend) {
                Log::info("Sending email to $user->email");

                Mail::to($user->email, $user->full_name)->send(
                    new LoanPrePaymentMissing($user, $loan)
                );

                $meta = $loan->meta;
                $meta["sent_loan_pre_payment_missing_email"] = true;
                $loan->meta = $meta;

                $loan->save();
            } else {
                Log::info(
                    "Would have sent an email to {$user->email} for loan {$loan->id}"
                );
            }
        }

        Log::info("Done.");
    }

    public static function getQuery($queryParams)
    {
        $query = Loan::where("status", "=", "in_process")
            ->departureInLessThan(24, "hours")
            ->where("loans.created_at", "<", $queryParams["created_at"])
            ->whereHas("prePayment", function ($q) {
                return $q->where("pre_payments.status", "=", "in_process");
            })
            ->where("meta->sent_loan_pre_payment_missing_email", null);

        return $query;
    }
}
