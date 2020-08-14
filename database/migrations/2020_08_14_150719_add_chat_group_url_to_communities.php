<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChatGroupUrlToCommunities extends Migration
{
    public function up() {
        Schema::table('communities', function (Blueprint $table) {
            $table->string('chat_group_url')->nullable(true)->default(null);
        });
    }

    public function down() {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('chat_group_url');
        });
    }
}
