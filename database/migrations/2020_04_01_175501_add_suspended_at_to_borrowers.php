<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuspendedAtToBorrowers extends Migration
{
    public function up() {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->dateTimeTz('suspended_at')->nullable();
        });
    }

    public function down() {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn('suspended_at');
        });
    }
}
