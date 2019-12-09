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
    public function up() {
        Schema::create('incidents', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Action fields
            $table->dateTimeTz('executed_at');
            $table->enum('status', ['in_process', 'canceled', 'completed']);
            
            //Incident-specific fields
            $table->enum('incident_type', ['accident']);

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
        Schema::dropIfExists('incidents');
    }
}
