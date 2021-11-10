<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\Registration\Submitted as RegistrationSubmitted;
use App\Models\User;
use Mail;

class TestRegistrationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:registration:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test registration emails locally';

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        $user = User::whereEmail('alexandre.toulemonde+loco1@gmail.com')->first();
        // php artisan email:registration:test 
        Mail::mailer("mandrill")
            ->to($user->email, $user->name . " " . $user->last_name)
            ->queue(new RegistrationSubmitted($user));

        $meta = $user->meta;
        $meta["sent_registration_submitted_email"] = true;
        $user->meta = $meta;

        $user->save();
    }
}
