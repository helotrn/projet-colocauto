<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command("noke:sync:locks")->dailyAt("02:00:00");
        $schedule->command("noke:sync:users")->dailyAt("02:30:00");
        $schedule->command("noke:sync:loans")->everyFiveMinutes();
        $schedule->command("actions:complete")->hourly();
        $schedule->command("email:loan:upcoming")->everyMinute();
        $schedule->command("email:loan:pre_payment_missing")->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . "/Commands");

        require base_path("routes/console.php");
    }
}
