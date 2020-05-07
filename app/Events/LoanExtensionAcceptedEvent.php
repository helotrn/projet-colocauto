<?php

namespace App\Events;

use App\Models\Extension;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanExtensionAcceptedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $extension;

    public function __construct(Extension $extension) {
        $this->extension = $extension;
    }
}
