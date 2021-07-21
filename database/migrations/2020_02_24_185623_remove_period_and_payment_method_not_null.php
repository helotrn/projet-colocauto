<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePeriodAndPaymentMethodNotNull extends Migration
{
    public function up()
    {
        Schema::table("invoices", function (Blueprint $table) {
            $table->dropColumn("payment_method");
            $table->dropColumn("total");
            $table
                ->string("period")
                ->default("")
                ->change();
        });
    }

    public function down()
    {
        Schema::table("invoices", function (Blueprint $table) {
            $table->string("payment_method");
            $table->decimal("total", 8, 2);
            $table->string("period")->change();
        });
    }
}
