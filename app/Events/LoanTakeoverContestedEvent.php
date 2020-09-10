<?php

namespace App\Events;

use App\Models\Takeover;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanTakeoverContestedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $takeover;
    public $user;

    public function __construct(Takeover $takeover, User $user) {
        $this->takeover = $takeover;
        $this->user = $user;
    }
}
