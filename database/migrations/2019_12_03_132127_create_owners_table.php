<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Propriétaire​ : Un profil de propriétaire auquel sont associés des ​Objets​ partagés avec la communauté
        */
        Schema::create('owners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('submission_date');
            $table->date('approval_date');
            // Objets (​Objets​) : Les objets partagés avec la communauté, obligatoire
            // Fichiers (​Fichiers​ par le biais de l’​Utilisateur​) : les fichiers requis pour compléter le profil de propriétaire
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
        Schema::dropIfExists('owners');
    }
}
