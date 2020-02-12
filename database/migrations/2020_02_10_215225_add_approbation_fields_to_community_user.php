<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprobationFieldsToCommunityUser extends Migration
{
    public function up() {
        Schema::table('community_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTimeTz('approved_at')->nullable();
            $table->dateTimeTz('suspended_at')->nullable();
        });
    }

    public function down() {
        Schema::table('community_user', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropColumn('approved_at');
            $table->dropColumn('suspended_at');
        });
    }
}
