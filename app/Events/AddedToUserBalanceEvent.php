<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddedToUserBalanceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;
    public $user;

    public function __construct(User $user, $invoice) {
        $this->invoice = $invoice;
        $this->user = $user;
    }
}
