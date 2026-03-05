<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGpsDistanceToLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            // On ajoute la colonne GPS après la colonne distance_driven
            $table->float('gps_measured_distance')->nullable()->after('distance_driven');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            // Si on annule, on supprime la colonne
            $table->dropColumn('gps_measured_distance');
        });
    }
}