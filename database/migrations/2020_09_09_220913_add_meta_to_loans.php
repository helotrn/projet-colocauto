<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;

class AddMetaToLoans extends Migration
{
    public function up() {
        Schema::table('loans', function (Blueprint $table) {
            $table->jsonb('meta')->default(new Expression("'{}'::json"));
        });
    }

    public function down() {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
