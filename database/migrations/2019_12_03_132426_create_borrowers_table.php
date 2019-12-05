<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBorrowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        ● Emprunteur​ : Un profil d’emprunteur de ​Voiture​ couvert par les assurances
        */
        Schema::create('borrowers', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Numéro de permis de conduire : Optionnel
            // Poursuivi dans les 10 dernières années : Booléen, optionnel
            // Identifiant de clé NOKE : Optionnel
            // Soumission : Date de soumission, implicitement l’acceptation des conditions
            // Approbation : Date d’approbation
            // Création : Date de création
            // Modification : Date de modification
            // Suppression : Date de suppression
            // Fichiers (​Fichiers​ par le biais de l’​Utilisateur​) : les fichiers requis pour compléter le profil d’emprunteur, optionnels
            // Permis de conduire
            // Dossier de conduite de la SAAQ
            // Rapport de sinistre de la GAA
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
        Schema::dropIfExists('borrowers');
    }
}
