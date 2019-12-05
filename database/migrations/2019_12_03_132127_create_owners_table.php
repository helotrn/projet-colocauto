<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnersTable extends Migration
{
    public function up() {
        Schema::create('owners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('submitted_at');
            $table->date('approved_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('owners');
    }
}
