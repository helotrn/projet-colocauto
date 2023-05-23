<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Car;
use Log;

class WriteSharedExpenses extends Command
{
    protected $signature = 'expenses:write
                            {--dry-run : Do not actualy save expenses in database}
                            {date? : pretend the current date is not now (d-m-Y format)}';

    protected $description = 'Write shared expenses in each user wallet for each loanable on a monthly basis';

    private $dryrun = false;
    private $date = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option("dry-run")) {
            $this->dryrun = true;
        }
        $this->date = $this->argument("date");
        Log::info("Starting write expenses command...");

        Car::where("availability_mode", "always")
        ->where("cost_per_month", ">", 0)
        ->each(function ($car) {
            $car->writeMonthlySharedExpenses($this->date, $this->dryrun);
        });

        Log::info("Completed write expenses command.");
    }
}
