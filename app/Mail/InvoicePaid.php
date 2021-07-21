<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class InvoicePaid extends BaseMailable
{
    use Queueable, SerializesModels;

    public $user;
    public $invoice;
    public $title;
    public $text;
    public $subject;

    public function __construct(
        User $user,
        array $invoice,
        $title = null,
        $text = null,
        $subject = null
    ) {
        $this->user = $user;
        $this->invoice = $invoice;

        $this->title = $title;
        $this->text = $text;
        $this->subject = $subject ?: "Locomotion - $this->title";
    }

    public function build()
    {
        return $this->view("emails.invoice.paid")
            ->subject($this->subject)
            ->text("emails.invoice.paid_text");
    }
}
