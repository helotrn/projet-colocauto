<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommunityTypeEnum extends Migration
{
    public function up() {
        Schema::table('communities', function (Blueprint $table) {
            $table->enum('type', ['private', 'neighborhood', 'borough'])->default('neighborhood');
        });
    }

    public function down() {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
