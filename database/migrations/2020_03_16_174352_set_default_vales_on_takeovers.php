<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetDefaultValesOnTakeovers extends Migration
{
    public function up() {
        Schema::table('takeovers', function (Blueprint $table) {
            $table->string('mileage_beginning')->nullable()->change();
            $table->string('fuel_beginning')->nullable()->change();
        });
    }

    public function down() {
        Schema::table('takeovers', function (Blueprint $table) {
            $table->string('mileage_beginning')->nullable(false)->change();
            $table->string('fuel_beginning')->nullable(true)->change();
        });
    }
}
