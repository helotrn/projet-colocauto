<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Période : Période de la facture, obligatoire
            // Méthode de paiement : Une représentation textuelle de la méthode de paiement utilisée par ex. “Carte de crédit Visa” ou “Par chèque”, obligatoire
            // Paiement : Date de paiement
            // Total : Somme du montant des items facturables
            // Items facturables (​Item facturable​) : Tous les items facturables sur la facture
            // Méthode de paiement (​Méthode de paiement​) : La méthode de paiement utilisée pour cette facture, optionnel
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills');
    }
}
