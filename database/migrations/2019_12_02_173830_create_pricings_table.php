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
            $table->enum('variable', ['time', 'distance']);
            $table->text('rule');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('pricings');
    }
}
