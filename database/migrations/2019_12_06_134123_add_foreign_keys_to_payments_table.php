<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->unsignedBigInteger('billable_item_id')->nullable();
            $table->foreign('billable_item_id')->references('id')->on('billable_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['loan_id']);
            $table->dropColumn('loan_id');

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->dropForeign(['billable_item_id']);
            $table->dropColumn('billable_item_id');
        });
    }
}
