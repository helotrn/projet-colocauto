<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        ● Action​ : Une action sur un ​Emprunt​, dont l’objectif est d’indiquer toutes les opérations effectuées dans le temps
        */
        Schema::create('actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Date et heure : Obligatoire
            // Statut : Un parmi “En cours”, “Annulée” ou “Complétée”, obligatoire
            // Création : Date de création
            // Modification : Date de modification
            // Suppression : Date de suppression
            // Acteur (​Utilisateur​) : La personne ayant effectué l’action
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
        Schema::dropIfExists('actions');
    }
}
