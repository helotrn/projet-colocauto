<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToTags extends Migration
{
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('slug');
        });
    }

    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
