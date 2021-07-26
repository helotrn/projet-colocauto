<?php

namespace App\Console\Commands;

use App\Mail\Loan\Upcoming as LoanUpcoming;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class EmailLoanUpcoming extends Command
{
    protected $signature = 'email:loan:upcoming
                            {--pretend : Do not send emails}';

    protected $description = "Send loan upcoming emails (in three hours)";

    private $pretend = false;

    public function handle()
    {
        if ($this->option("pretend")) {
            $this->pretend = true;
        }

        $this->info(
            "Fetching loans in three hours created " .
                "at least three hours before now..."
        );
        $threeHoursAgo = (new Carbon())->subtract(3, "hours");

        $query = $this->getQuery(["created_at" => $threeHoursAgo]);

        $loans = $query->cursor();
        foreach ($loans as $loan) {
            $user = $loan->borrower->user;
            if (!$this->pretend) {
                $this->info("Sending email to $user->email");

                Mail::to(
                    $user->email,
                    $user->name . " " . $user->last_name
                )->send(new LoanUpcoming($user, $loan));

                if ($loan->owner) {
                    $ownerUser = $loan->owner->user;

                    $this->info("Sending email to $ownerUser->email");

                    Mail::to(
                        $ownerUser->email,
                        $ownerUser->name . " " . $ownerUser->last_name
                    )->send(new LoanUpcoming($user, $loan));
                }

                $meta = $loan->meta;
                $meta["sent_loan_upcoming_email"] = true;
                $loan->meta = $meta;

                $loan->save();
            } else {
                $this->info(
                    "Would have sent an email to {$user->email} for loan {$loan->id}"
                );

                if ($loan->owner) {
                    $ownerUser = $loan->owner->user;
                    $this->info(
                        "Would have sent an email to {$ownerUser->email} " .
                            "for loan {$loan->id}"
                    );
                }
            }
        }

        $this->info("Done.");
    }

    public static function getQuery($queryParams)
    {
        $query = Loan::departureInLessThan(3, "hours")
            ->where("loans.created_at", "<", $queryParams["created_at"])
            ->where("meta->sent_loan_upcoming_email", null);

        $columnDefinitions = Loan::getColumnsDefinition();
        $query = $columnDefinitions["loan_status"]($query);
        $query = $columnDefinitions["*"]($query);

        $query->where($columnDefinitions["loan_status"](), "=", "in_process");

        return $query;
    }
}
