<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    public function up() {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('imageable_type')->nullable();
            $table->integer('imageable_id')->unsigned()->nullable();
            $table->string('path');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('field')->nullable();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->integer('orientation')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('images');
    }
}
