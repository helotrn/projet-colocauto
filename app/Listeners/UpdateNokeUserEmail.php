<?php

namespace App\Listeners;

use App\Events\UserEmailUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Noke;

class UpdateNokeUserEmail
{
    private $service;

    public function handle(UserEmailUpdated $event)
    {
        if (app()->environment() !== "production") {
            return;
        }

        $nokeUser = Noke::findUserByEmail($event->previousEmail, true);

        if ($nokeUser) {
            $nokeUser->username = $event->newEmail;
            Noke::updateUser($nokeUser);
        }
    }
}
