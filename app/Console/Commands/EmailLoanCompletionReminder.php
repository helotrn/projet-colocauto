<?php

namespace App\Console\Commands;

use App\Mail\Loan\CompletionReminder as LoanCompletionReminder;
use App\Models\Loan;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Log;
use Mail;

class EmailLoanCompletionReminder extends Command
{
    protected $signature = 'email:loan:completion_reminder
                            {--pretend : Do not send emails}';

    protected $description = "Send email to remind user that its loan needs attention after 24 hours of inactivity";

    private $pretend = false;

    public function handle()
    {
        if ($this->option("pretend")) {
            $this->pretend = true;
        }
        Log::info("Starting loan completion reminder command...");

        $loanReminderTime = CarbonImmutable::now()->subHours(24);
        $loans = self::getActiveLoansScheduledToReturnBefore(
            $loanReminderTime
        );

        foreach ($loans as $loan) {
            // Remind nothing if loan is contested
            if (
                ($loan->handover && $loan->handover->isContested()) ||
                ($loan->takeover && $loan->takeover->isContested())
            ) {
                continue;
            }

            if ( ($loan->takeover && $loan->takeover->status === "in_process")
                || ($loan->handover && $loan->handover->status === "in_process") ) {

                $user = $loan->borrower->user;
                if (!$this->pretend) {
                    if (!isset($loan->meta["sent_loan_completion_reminder_email"])) {
                        Log::info(
                            "Sending LoanCompletionReminder email to borrower at: $user->email"
                        );

                        Mail::to($user->email, $user->full_name)->send(
                            new LoanCompletionReminder($user, $loan)
                        );

                        $meta = $loan->meta;
                        $meta["sent_loan_completion_reminder_email"] = true;
                        $loan->meta = $meta;

                        $loan->save();
                    }
                } else {
                    Log::info(
                        "Would have sent LoanCompletionReminder email to borrower at: {$user->email}" .
                            " for loan with id: {$loan->id}"
                    );
                    $mail = new LoanCompletionReminder($user, $loan);
                    Log::info($mail->render());
                }
                continue;
            }
        }
        Log::info("Done.");

    }

    public static function getActiveLoansScheduledToReturnBefore($datetime)
    {
        return Loan::where("status", "=", "in_process")
            ->where("actual_return_at", "<=", $datetime)
            ->get();
    }
}
