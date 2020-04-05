<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessageForBorrowerToExtension extends Migration
{
    public function up() {
        Schema::table('extensions', function (Blueprint $table) {
            $table->text('message_for_borrower')->default('')->nullable();
        });
    }

    public function down() {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropColumn('message_for_borrower');
        });
    }
}
