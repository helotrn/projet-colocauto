<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunitiesTable extends Migration
{
    public function up() {
        Schema::create('communities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->polygon('territory')->nullable();
            $table->timestamps();
            $table->softDeletes();

            /*
             Membres (​Utilisateurs​), optionnels
                ○ Propriétaires (​Utilisateurs​ ayant un ​Propriétaire​)
                ○ Emprunteurs (​Utilisateurs​ ayant un ​Emprunteur​)
                ○ Ambassadeurs (​Utilisateurs​ dont le membership est qualifié comme tel)
                ○ Modérateurs (​Utilisateurs​ dont le membership est qualifié comme tel)
                ○ Administrateurs (​Utilisateurs​ dont le membership est qualifié comme
                tel)

            ➢ Objets (​Objets​) : Les objets de la communauté, optionnel
            ➢ Tarifications (​Tarifications​) : Les tarifications associées à cette communauté,
            obligatoire
            */
        });
    }

    public function down() {
        Schema::dropIfExists('communities');
    }
}
