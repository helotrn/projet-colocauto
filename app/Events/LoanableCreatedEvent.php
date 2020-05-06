<?php

namespace App\Events;

use App\Models\Loanable;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanableCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $loanable;

    public function __construct(User $user, Loanable $loanable) {
        $this->user = $user;
        $this->loanable = $loanable;
    }
}
