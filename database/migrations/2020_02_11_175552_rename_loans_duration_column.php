<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameLoansDurationColumn extends Migration
{
    public function up() {
        Schema::table('loans', function (Blueprint $table) {
            $table->renameColumn('duration_in_minutes', 'duration');
        });
    }

    public function down() {
        Schema::table('loans', function (Blueprint $table) {
            $table->renameColumn('duration', 'duration_in_minutes');
        });
    }
}
