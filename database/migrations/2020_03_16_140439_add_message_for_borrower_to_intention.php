<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessageForBorrowerToIntention extends Migration
{
    public function up()
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->text('message_for_borrower')->default('');
        });
    }

    public function down()
    {
        Schema::table('intentions', function (Blueprint $table) {
            $table->dropColumn('message_for_borrower');
        });
    }
}
