<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AddLogEntry extends Command
{
    protected $signature = "addlogentry";

    protected $description = "Just add a log entry to test calls from the scheduler.";

    public function handle()
    {
        $this->info("Writing info log entry.");
        log::info("Command AddLogEntry called!");
    }
}
