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
        if (!in_array(app()->environment(), ["production", "testing"])) {
            return;
        }

        $nokeUser = Noke::findUserByEmail($event->previousEmail);

        if ($nokeUser) {
            $nokeUser->username = $event->newEmail;
            Noke::updateUser($nokeUser);
        }
    }
}
