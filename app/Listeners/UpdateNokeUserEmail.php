<?php

namespace App\Listeners;

use App\Events\UserEmailUpdated;
use App\Services\NokeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateNokeUserEmail
{
    private $service;

    public function __construct(NokeService $service)
    {
        $this->service = $service;
    }

    public function handle(UserEmailUpdated $event)
    {
        $nokeUser = $this->service->findUserByEmail(
            $event->previousEmail,
            true
        );

        if ($nokeUser) {
            $nokeUser->username = $event->newEmail;
            $response = $this->service->updateUser($nokeUser);
        }
    }
}
