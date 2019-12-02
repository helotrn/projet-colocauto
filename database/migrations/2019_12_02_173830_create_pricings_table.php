<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingsTable extends Migration
{
    public function up() {
        Schema::create('pricings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('object_type');
            $table->enum('variable', ['distance', 'time']);
            $table->text('rule');

            $table->unsignedBigInteger('community_id');
            $table->foreign('community_id')->references('id')->on('communities');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('pricings');
    }
}
