<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarsTable extends Migration
{
    public function up() {
        Schema::create('cars', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->text('location_description');
            $table->text('comments');
            $table->text('instructions');

            $table->string('brand');
            $table->string('model');
            $table->year('year_of_circulation');
            $table->enum('transmission_mode', ['automatic', 'manual']);
            $table->enum('fuel', ['fuel', 'diesel', 'electric', 'hybrid']);
            $table->string('plate_number');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('cars');
    }
}
