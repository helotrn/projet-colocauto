<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Car;
use Log;

class WriteSharedExpenses extends Command
{
    protected $signature = 'expenses:write
                            {--dry-run : Do not actualy save expenses in database}';

    protected $description = 'Write shared expenses in each user wallet for each loanable on a monthly basis';
    
    private $dryrun = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option("dry-run")) {
            $this->dryrun = true;
        }
        Log::info("Starting write expenses command...");

        Car::where("availability_mode", "always")->each(function ($car) {
            $car->writeMonthlySharedExpenses(null, $this->dryrun);
        });

        Log::info("Completed write expenses command.");
    }
}
