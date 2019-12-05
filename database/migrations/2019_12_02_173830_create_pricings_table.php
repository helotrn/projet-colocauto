<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingsTable extends Migration
{
    public function up() {
        Schema::create('pricings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('object_type');
            $table->enum('variable', ['Temps', 'Kilométrage']);
            $table->text('rule');

            $table->unsignedBigInteger('community_id');
            $table->foreign('community_id')->references('id')->on('communities');

            $table->timestamps();
            $table->softDeletes();

            /*
            ○ Équation et condition : Calcul logique permettant de déterminer un tarif, doit
            considérer les tarifications linéaires (Ax + B * M), les tarifications par palier (SI x > Y1 ALORS T1 SINON SI x > Y2 ALORS T2 SINON TB * M) ou une combinaison des deux, où A est une constante; B, T1, T2 sont des tarifs constants, Y1, Y2 sont des constantes comparées à la variable et M est un facteur de majoration.
            */
        });
    }

    public function down() {
        Schema::dropIfExists('pricings');
    }
}
