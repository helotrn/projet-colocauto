<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveHasAccidentReport extends Migration
{
    public function up() {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('has_accident_report');
        });
    }

    public function down() {
        Schema::table('cars', function (Blueprint $table) {
            $table->boolean('has_accident_report')->default(false);
        });
    }
}
