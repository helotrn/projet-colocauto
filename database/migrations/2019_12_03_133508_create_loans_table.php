<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        ● Emprunt​ : Un type d’​Objet
        */
        Schema::create('loans', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Date et heure de départ : Obligatoire
            // Durée : Obligatoire
            // Étape : L’étape du cycle de vie d’un emprunt, consultez le graphe d’états
            // Objet (​Objet​) : L’objet emprunté
            // Propriétaire (​Propriétaire​ ou ​Communauté​ par le biais de l’​Objet​) : La personne ou la communauté propriétaire de l’objet
            // Étapes (​Action​) : La séquence d’actions du début à la fin d’une réservation, cette séquence est déterminée par la combinaison entre le type d’objet et le type de propriétaire
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
        Schema::dropIfExists('loans');
    }
}
