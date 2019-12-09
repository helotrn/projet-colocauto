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

            //Loanable fields
            $table->string('name');
            $table->point('position');
            $table->text('location_description');
            $table->text('comments');
            $table->text('instructions');

            //Trailer-specific fields
            $table->enum('type', ['regular', 'electric', 'fixed_wheel']);
            $table->string('maximum_charge');
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
