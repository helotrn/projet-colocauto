<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;

class AddMetaToUsers extends Migration
{
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->json('meta')->default(new Expression("'{}'::json"));
        });
    }

    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
