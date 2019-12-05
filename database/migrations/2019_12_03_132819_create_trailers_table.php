<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrailersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        ● Remorque​ : Un type d’​Objet
        */
        Schema::create('trailers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['Régulier', 'Électrique', 'Roue fixe']);
            $table->string('maximum_charge'); //units ?
            // Comment récupérer la clé : Obligatoire
            // Accessoires (​Mots-clés​ de type Accessoire) : Les accessoires pour la remorque
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
        Schema::dropIfExists('trailers');
    }
}
