<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Objet​ : Un objet pouvant être emprunté
        */
        Schema::create('loanables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            // Position géographique : Obligatoire
            // Description de localisation : Optionnel
            // Informations utiles et commentaires : Optionnel
            // Comment récupérer la clé : Obligatoire
            // Emprunts (​Emprunt​) : La liste des emprunts de tout statut
            // Propriétaire (​Utilisateur​ ou ​Communauté​) : L’entité possédant l’objet, les objets de la communauté n’ayant pas le processus d’approbation de l’emprunt
            // Cadenas (​Cadenas​) : Les détails sur le cadenas Noke de l’objet
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
        Schema::dropIfExists('loanables');
    }
}
