<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTypeFieldsOnSubLoanables extends Migration
{
    public function up() {
        Schema::table('bikes', function (Blueprint $table) {
            $table->renameColumn('type', 'bike_type');
        });

        Schema::table('trailers', function (Blueprint $table) {
            $table->removeColumn('type');
        });
    }

    public function down() {
        Schema::table('bikes', function (Blueprint $table) {
            $table->renameColumn('bike_type', 'type');
        });

        Schema::table('trailers', function (Blueprint $table) {
            $table->enum('type', ['regular', 'electric', 'fixed_wheel']);
        });
    }
}
