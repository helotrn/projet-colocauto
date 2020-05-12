<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFuelEndAndBeginning extends Migration
{
    public function up() {
        Schema::table('takeovers', function (Blueprint $table) {
            $table->dropColumn('fuel_beginning');
        });

        Schema::table('handovers', function (Blueprint $table) {
            $table->dropColumn('fuel_end');
        });
    }

    public function down() {
        Schema::table('takeovers', function (Blueprint $table) {
            $table->string('fuel_beginning');
        });

        Schema::table('handovers', function (Blueprint $table) {
            $table->string('fuel_end');
        });
    }
}
