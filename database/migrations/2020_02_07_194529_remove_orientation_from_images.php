<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveOrientationFromImages extends Migration
{
    public function up() {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('orientation');
        });
    }

    public function down() {
        Schema::table('images', function (Blueprint $table) {
            $table->integer('orientation')->unsigned();
        });
    }
}
