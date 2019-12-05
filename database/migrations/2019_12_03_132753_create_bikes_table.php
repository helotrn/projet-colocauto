<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        ● Bicyclette​ : Un type d’​Objet
        */
        Schema::create('bikes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model');
            $table->enum('type', ['Régulier', 'Électrique', 'Roue fixe']);
            $table->enum('size', ['Grand', 'Moyen', 'Petit', 'Enfant']);
            // Accessoires (​Mots-clés​ de type Accessoire) : Les accessoires sur le vélo
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
        Schema::dropIfExists('bikes');
    }
}
