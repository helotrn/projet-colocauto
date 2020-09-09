<?php

namespace App\Events;

use App\Models\Community;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistrationRejectedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $community;

    public function __construct(User $user, Community $community) {
        $this->user = $user;
        $this->community = $community;
    }
}
