<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        ● Incident​ : Un type d’​Action​ sur un ​Emprunt​, contenant les données relatives à un incident
        */
        Schema::create('incidents', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Type d’incident : Un parmi “Accident”, obligatoire
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
        Schema::dropIfExists('incidents');
    }
}
