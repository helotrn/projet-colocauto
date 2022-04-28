<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // the type will determine whether the invoice is a debit or a credit :
        // - debit: cost to be paid by the user
        // - credit: gains to be added in the user's balance
        Schema::table('invoices', function (Blueprint $table) {
            $table
                ->enum("type", ["debit", "credit"])
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn("type");
        });
    }
}
