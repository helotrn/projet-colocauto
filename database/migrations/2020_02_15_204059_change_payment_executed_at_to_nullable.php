<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePaymentExecutedAtToNullable extends Migration
{
    public function up() {
        Schema::table('payments', function (Blueprint $table) {
            $table->dateTimeTz('executed_at')->nullable()->change();
        });
    }

    public function down() {
        Schema::table('payments', function (Blueprint $table) {
            $table->dateTimeTz('executed_at')->nullable(false)->change();
        });
    }
}
