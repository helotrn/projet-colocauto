<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFilesizeToImages extends Migration
{
    public function up() {
        Schema::table('images', function (Blueprint $table) {
            $table->string('filesize');
        });
    }

    public function down() {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('filesize');
        });
    }
}
