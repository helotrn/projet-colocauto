<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarsTable extends Migration
{
    public function up() {
        Schema::create('cars', function (Blueprint $table) {
            $table->bigIncrements('id');

            //Loanable fields
            $table->string('name');
            $table->point('position');
            $table->text('location_description');
            $table->text('comments');
            $table->text('instructions');

            //Car-specific fields
            $table->string('brand');
            $table->string('model');
            $table->year('year_of_circulation');
            $table->enum('transmission_mode', ['automatic', 'manual']);
            $table->enum('fuel', ['fuel', 'diesel', 'electric', 'hybrid']);
            $table->string('plate_number');
            $table->boolean('is_value_over_fifty_thousand')->default(false);
            $table->enum('owners', ['yourself', 'dealership']); // QUESTION
            $table->enum('papers_location', ['in_the_car', 'to_request_with_car']);
            $table->boolean('has_accident_report')->default(false);
            $table->string('insurer');
            $table->boolean('has_informed_insurer')->default(false);
            // QUESTION calendrier d'indisponibilité ical
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('cars');
    }
}
