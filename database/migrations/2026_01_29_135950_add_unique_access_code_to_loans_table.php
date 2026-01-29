<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueAccessCodeToLoansTable extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            // On ajoute la colonne après le statut.
            // Nullable car seules certaines communautés l'utilisent.
            $table->string('unique_access_code', 4)
                  ->nullable()
                  ->after('status')
                  ->comment('Code unique boite à clés (Café & UniLaSalle)');
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('unique_access_code');
        });
    }
}