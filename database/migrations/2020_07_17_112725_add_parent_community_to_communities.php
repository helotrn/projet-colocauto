<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentCommunityToCommunities extends Migration
{
    public function up() {
        Schema::table('communities', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->foreign('parent_id')
                ->references('id')->on('communities')
                ->onDelete('restrict');
        });
    }

    public function down() {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
