<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsOnIncidentDefault extends Migration
{
    public function up() {
        Schema::table('incidents', function (Blueprint $table) {
            $table->text('comments_on_incident')->default('')->change();
        });
    }

    public function down() {
        // noop
    }
}
