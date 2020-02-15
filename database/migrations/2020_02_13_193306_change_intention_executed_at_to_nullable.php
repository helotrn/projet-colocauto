<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIntentionExecutedAtToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('intentions', function (Blueprint $table) {
            $table->dateTimeTz('executed_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('intentions', function (Blueprint $table) {
            $table->dateTimeTz('executed_at')->nullable(false)->change();
        });
    }
}
