<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLoanableTypeFromPadlocks extends Migration
{
    public function up() {
        Schema::table('padlocks', function (Blueprint $table) {
            $table->dropColumn('loanable_type');
        });
    }

    public function down() {
        Schema::table('padlocks', function (Blueprint $table) {
            $table->string('loanable_type')->nullable();
        });
    }
}
