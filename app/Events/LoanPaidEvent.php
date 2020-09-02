<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanPaidEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;
    public $user;

    public function __construct(User $user, Invoice $invoice) {
        $this->invoice = $invoice;
        $this->user = $user;
    }
}
