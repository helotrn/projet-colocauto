<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Nom d’origine : obligatoire
            // Fonction : la fonction du fichier, obligatoire
            // Cible (​Utilisateur​ ou ​Action​ ou ​Objet)​ : L’entité possédant le fichier
            //$table->unsignedBigInteger('target_id');
            //$table->foreign('target_id')->references('id')->on('communities');
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
        Schema::dropIfExists('files');
    }
}
