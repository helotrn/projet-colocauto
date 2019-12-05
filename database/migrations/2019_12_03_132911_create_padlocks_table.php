<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePadlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        ● Cadenas​ : Un cadenas Noke associé à un objet
        */
        Schema::create('padlocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            // MAC : Adresse unique associée au cadenas ➢ Objet (​Objet)​ : L’objet barré par le cadenas
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
        Schema::dropIfExists('padlocks');
    }
}
