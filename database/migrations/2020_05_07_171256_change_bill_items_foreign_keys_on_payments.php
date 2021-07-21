<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBillItemsForeignKeysOnPayments extends Migration
{
    public function up()
    {
        Schema::table("payments", function (Blueprint $table) {
            $table->dropColumn("bill_item_id");

            $table->unsignedBigInteger("owner_invoice_id")->nullable();
            $table
                ->foreign("owner_invoice_id")
                ->references("id")
                ->on("invoices")
                ->onDelete("cascade");

            $table->unsignedBigInteger("borrower_invoice_id")->nullable();
            $table
                ->foreign("borrower_invoice_id")
                ->references("id")
                ->on("invoices")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::table("payments", function (Blueprint $table) {
            $table->dropColumn("borrower_invoice_id");
            $table->dropColumn("owner_invoice_id");

            $table->unsignedBigInteger("bill_item_id")->nullable();
            $table
                ->foreign("bill_item_id")
                ->references("id")
                ->on("bill_items")
                ->onDelete("cascade");
        });
    }
}
