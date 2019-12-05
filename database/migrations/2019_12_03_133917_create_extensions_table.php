<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Rallonge​ : Un type d’​Action​ sur un ​Emprunt​, contenant les données relatives à la rallonge d’une réservation
        */
        Schema::create('extensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Nouvelle durée : Obligatoire
            // Commentaire sur la rallonge : Obligatoire
            // Contestation : Date de contestation
            // Commentaire sur la contestation : Optionnel
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
        Schema::dropIfExists('extensions');
    }
}
