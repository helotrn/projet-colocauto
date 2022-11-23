<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoanValidation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("loans", function (Blueprint $q) {
            $q->dateTimeTz("owner_validated_at")->nullable();
            $q->dateTimeTz("borrower_validated_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("loans", function (Blueprint $q) {
            $q->dropColumn("owner_validated_at");
            $q->dropColumn("borrower_validated_at");
        });
    }
}
