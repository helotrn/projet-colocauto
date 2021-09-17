<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEmailUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $previousEmail;
    public $newEmail;

    public function __construct(
        User $user,
        string $previousEmail,
        string $newEmail
    ) {
        $this->user = $user;
        $this->previousEmail = $previousEmail;
        $this->newEmail = $newEmail;
    }
}
