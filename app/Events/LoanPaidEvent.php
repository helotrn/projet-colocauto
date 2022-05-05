<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanPaidEvent extends SendInvoiceEmailEvent
{
    public static $defaultText = <<<TEXT
Cela signifie que vous ne pouvez plus modifier les informations du trajet (km départ,
km retour, achat carburant). Si vous vous rendez compte d’une erreur, dites-le à la personne
qui vous a prêté son auto ou vélo et contactez info@locomotion.app, on vous aidera à
ajuster vos factures.

Merci d'avoir utilisé LocoMotion!
TEXT;
    public function __construct(
        User $user,
        array $invoice,
        $title = null,
        $text = null
    ) {
        $this->user = $user;
        $this->invoice = $invoice;

        $this->title = $title ?: "Facture de votre plus récent emprunt";
        $this->text = $text ?: static::$defaultText;
    }
}
