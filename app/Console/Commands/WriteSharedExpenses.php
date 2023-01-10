<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Car;
use Log;

class WriteSharedExpenses extends Command
{
    protected $signature = 'expenses:write';

    protected $description = 'Write shared expenses in each user wallet for each loanable on a monthly basis';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info("Starting write expenses command...");

        Car::all()->each(function ($car) {
            $car->writeMonthlySharedExpenses();
        });

        Log::info("Completed write expenses command.");
    }
}
