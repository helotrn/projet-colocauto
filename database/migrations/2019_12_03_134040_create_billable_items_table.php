<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillableItemsTable extends Migration
{
    public function up() {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label');
            $table->decimal('amount', 8, 2);
            $table->unsignedBigInteger('invoice_id');
            $table->date('item_date');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('bill_items');
    }
}
