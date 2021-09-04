<?php

namespace App\Events;

use App\Models\User;

class AddedToUserBalanceEvent extends SendInvoiceEmailEvent
{
    public function __construct(User $user, array $invoice)
    {
        $this->user = $user;
        $this->invoice = $invoice;

        $this->title = "Facture payée";
        $this->text =
            "Vous trouvez ci-contre le relevé de votre plus récent paiement" .
            " sur LocoMotion.";
    }
}
