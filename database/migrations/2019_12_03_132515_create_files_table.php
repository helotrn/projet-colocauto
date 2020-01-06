<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    public function up() {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fileable_type')->nullable();
            $table->integer('fileable_id')->unsigned()->nullable();
            $table->string('path');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('filesize');
            $table->string('field')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('files');
    }
}
