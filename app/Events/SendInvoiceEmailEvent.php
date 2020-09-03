<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class SendInvoiceEmailEvent
{
    public $invoice;
    public $user;
    public $title;
    public $text;
    public $subject;
}
