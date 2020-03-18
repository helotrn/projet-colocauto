<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionIdToUsers extends Migration
{
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('transaction_id')->default(1);
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('is_default')->default(false);
        });
    }

    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}
