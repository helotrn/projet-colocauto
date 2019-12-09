<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('borrower_id')->nullable();
            $table->foreign('borrower_id')->references('id')->on('borrowers');

            $table->string('loanable_type')->nullable();
            $table->unsignedBigInteger('loanable_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['borrower_id']);
            $table->dropColumn('borrower_id');

            $table->dropColumn('loanable_id');
            $table->dropColumn('loanable_type');
        });
    }
}
