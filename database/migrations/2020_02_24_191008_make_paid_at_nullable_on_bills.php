<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakePaidAtNullableOnBills extends Migration
{
    public function up() {
        Schema::table('bills', function (Blueprint $table) {
            $table->dateTimeTz('paid_at')->nullable()->change();
        });
    }

    public function down() {
        Schema::table('bills', function (Blueprint $table) {
            $table->dateTimeTz('paid_at')->change();
        });
    }
}
