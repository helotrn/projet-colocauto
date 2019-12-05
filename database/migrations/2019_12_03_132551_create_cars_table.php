<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('brand');
            $table->string('model');
            $table->year('year_of_circulation');
            $table->enum('transmission_mode', ['Automatique', 'Manuelle']);
            $table->enum('fuel', ['Essence', 'Diesel', 'Électrique', 'Hybride']);
            $table->string('plate_number');
            // Valeur à l’origine au-dessus de 50 000$ : Optionnel
            // Qui sont les propriétaires : Un parmi “Vous-même”, “Location longue durée / concessionnaire” ou une données spécifiée, obligatoire
            // Où se trouvent les papiers : Un parmi “Dans la voiture” ou “À récupérer avec la voiture”, obligatoire
            // Avez-vous un constat à l’amiable : Booléen, obligatoire
            // Assureur : Obligatoire
            // Avez-vous informé votre assureur que vous participez à Locomotion : Booléen, obligatoire
            // Calendrier d’indisponibilité : Définition iCal, https://www.kanzaki.com/docs/ical/
            // Photos du véhicules (​Images​) : Toutes les photos pertinentes du véhicule
            // Rapport d’inspection (​Fichier​) : Un rapport d’inspection
            // Accessoires (​Mots-clés​ de type Accessoire) : Les accessoires disponibles dans la voiture
            $table->timestamps();
            $table->softDeletes();
        });
        /*
         Voiture​ : Un type d’​Objet
        ○ 

        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cars');
    }
}
