<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billable_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Libellé : Obligatoire
            // Montant : Obligatoire
            // Date : La date où l’item facturable est effectif, obligatoire
            // Création : Date de création
            // Modification : Date de modification
            // Annulation : Date d’annulation
            // Item (​Paiement​) : L’item sur lequel porte le montant indiqué, optionnel

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
        Schema::dropIfExists('billable_items');
    }
}
