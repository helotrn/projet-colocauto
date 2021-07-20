<?php

namespace App\Events;

use App\Models\Handover;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanHandoverContestedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $handover;
    public $user;

    public function __construct(Handover $handover, User $user)
    {
        $this->handover = $handover;
        $this->user = $user;
    }
}
