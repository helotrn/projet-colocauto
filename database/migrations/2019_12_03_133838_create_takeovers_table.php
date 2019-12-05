<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTakeoversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Prise de possession​ : Un type d’​Action​ sur un ​Emprunt​, contenant les données relatives à la prise de possession d’une voiture
        */
        Schema::create('takeovers', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Km au début : Obligatoire
            // Essence au début : Obligatoire
            // Commentaires sur l’état du véhicule : Optionnel
            // Contestation : Date de contestation
            // Commentaire sur la contestation : Optionnel
            // Photos (​Image​) : Preuves photographiques, optionnel
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
        Schema::dropIfExists('takeovers');
    }
}
