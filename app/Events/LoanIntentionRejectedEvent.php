<?php

namespace App\Events;

use App\Models\Intention;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanIntentionRejectedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $intention;

    public function __construct(Intention $intention)
    {
        $this->intention = $intention;
    }
}
