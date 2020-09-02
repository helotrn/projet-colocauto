<?php

namespace App\Console\Commands;

use App\Mail\Registration\Stalled as RegistrationStalled;
use App\Models\User;
use Illuminate\Console\Command;
use Mail;

class EmailRegistrationStalled extends Command
{
    protected $signature = 'email:registration:stalled
                            {--pretend : Do not send emails}';

    protected $description = 'Send user registration stalled emails';

    private $pretend = false;

    public function handle() {
        if ($this->option('pretend')) {
            $this->pretend = true;
        }

        $this->info('Fetching users stalled at registration...');
        $users = User::stalledAtRegistration()
            ->where('meta->sent_stalled_email', null)
            ->cursor();

        foreach ($users as $user) {
            if (!$this->pretend) {
                $this->info("Sending email to $user->email");

                Mail::to($user->email, $user->name . ' ' . $user->last_name)
                  ->send(new RegistrationStalled($user));

                $user->forceFill([
                    'meta' => [ 'sent_stalled_email' => true ],
                ])->save();
            } else {
                $this->info("Would have sent an email to $user->email");
            }
        }

        $this->info('Done.');
    }
}
