<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// use App\Mail\Registration\Submitted as RegistrationEmail;
use App\Mail\Registration\Approved as RegistrationEmail;
use App\Models\User;
use Mail;

class TestRegistrationEmails extends Command
{
    // Default settings for artisan commands
    // php artisan email:registration:test
    //
    protected $signature = "email:registration:test";
    protected $description = "Test registration emails locally";
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::whereEmail(
            "alexandre.toulemonde+loco1@gmail.com"
        )->first();
        Mail::mailer("mandrill")
            ->to($user->email, $user->name . " " . $user->last_name)
            ->queue(new RegistrationEmail($user));

        $meta = $user->meta;
        $meta["sent_registration_submitted_email"] = true;
        $user->meta = $meta;

        $user->save();
    }
}
