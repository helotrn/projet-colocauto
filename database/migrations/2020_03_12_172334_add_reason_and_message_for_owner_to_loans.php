<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonAndMessageForOwnerToLoans extends Migration
{
    public function up() {
        Schema::table('loans', function (Blueprint $table) {
            $table->text('reason');
            $table->text('message_for_owner')->default('');
        });
    }

    public function down() {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('reason');
            $table->dropColumn('message_for_owner');
        });
    }
}
