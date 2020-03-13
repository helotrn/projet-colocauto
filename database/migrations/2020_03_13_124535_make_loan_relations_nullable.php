<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeLoanRelationsNullable extends Migration
{
    public function up() {
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('borrower_id')->nullable()->change();
        });
    }

    public function down() {
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('borrower_id')->change();
        });
    }
}
