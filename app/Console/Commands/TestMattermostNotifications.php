<?php

namespace App\Console\Commands;
use Mattermosthelper;
use ThibaudDauce\Mattermost\Message;
use Illuminate\Console\Command;

class TestMattermostNotifications extends Command
{
    // Default settings for artisan commands
    // php artisan notifications:mattermost:test
    //
    protected $signature = "notifications:mattermost:test";
    protected $description = "Test Mattermost Notifications";
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $message = (new Message())->text("This is a *test*.")->channel("test");
        Mattermosthelper::sendNotification($message);
    }
}
