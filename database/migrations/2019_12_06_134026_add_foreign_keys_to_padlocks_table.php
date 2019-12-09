<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToPadlocksTable extends Migration
{  
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('padlocks', function (Blueprint $table) {
            $table->string('loanable_type');
            $table->unsignedBigInteger('loanable_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('padlocks', function (Blueprint $table) {
            $table->dropColumn('loanable_type');
            $table->dropColumn('loanable_id');
        });
    }
}
