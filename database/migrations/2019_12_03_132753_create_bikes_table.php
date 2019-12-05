<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBikesTable extends Migration
{
    public function up() {
        Schema::create('bikes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->text('location_description');
            $table->text('comments');
            $table->text('instructions');

            $table->string('model');
            $table->enum('type', ['Régulier', 'Électrique', 'Roue fixe']);
            $table->enum('size', ['Grand', 'Moyen', 'Petit', 'Enfant']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('bikes');
    }
}
