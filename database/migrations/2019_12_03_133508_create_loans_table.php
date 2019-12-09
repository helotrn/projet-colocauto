<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        /*
        ● Emprunt​ : Un type d’​Objet
        */
        Schema::create('loans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTimeTz('departure_at');
            $table->unsignedInteger('duration');//in minutes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('loans');
    }
}
