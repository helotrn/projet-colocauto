<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         Mot-clé​ : Un mot-clé pouvant être affecté à un grande catégorie
        */
        Schema::create('keywords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            // Type : Une liste déterminée d’avance de types disponibles, obligatoire
            // Cibles (​Objet​) : Les entités ayant un mot-clé, la sémantique étant déterminée par le contexte et le type de mot-clé
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
        Schema::dropIfExists('keywords');
    }
}
