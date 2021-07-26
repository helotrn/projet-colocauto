<?php

namespace App\Listeners;

use App\Events\ClaimedUserBalanceEvent;
use App\Models\User;
use App\Mail\UserClaimedBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendClaimedUserBalanceEmails
{
    public function handle(ClaimedUserBalanceEvent $event)
    {
        $admins = User::whereRole("admin")
            ->select("name", "last_name", "email")
            ->get()
            ->toArray();

        foreach ($admins as $admin) {
            Mail::to(
                $admin["email"],
                $admin["name"] . " " . $admin["last_name"]
            )->queue(new UserClaimedBalance($event->user));
        }
    }
}
