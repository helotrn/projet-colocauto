<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHandoversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Remise​ : Un type d’​Action​ sur un ​Emprunt​, contenant les données relatives à la remise d’une voiture
        */
        Schema::create('handovers', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Km à la fin: Obligatoire
            // Essence à la fin : Obligatoire
            // Message de l’emprunteur : Optionnel
            // Message du propriétaire : Optionnel
            // Montant des achats : Optionnel
            // Contestation : Date de contestation
            // Commentaire sur la contestation : Optionnel
            // Photos (​Image​) : Preuves photographique, optionnel
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
        Schema::dropIfExists('handovers');
    }
}
