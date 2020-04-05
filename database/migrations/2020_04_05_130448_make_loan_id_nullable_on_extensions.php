<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeLoanIdNullableOnExtensions extends Migration
{
    public function up() {
        Schema::table('extensions', function (Blueprint $table) {
            $table->unsignedBigInteger('loan_id')->nullable()->change();
        });
    }

    public function down() {
        Schema::table('extensions', function (Blueprint $table) {
            $table->unsignedBigInteger('loan_id')->nullable(false)->change();
        });
    }
}
