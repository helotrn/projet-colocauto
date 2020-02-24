<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillableItemsTable extends Migration
{
    public function up() {
        Schema::create('billable_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label');
            $table->decimal('amount', 8, 2);
            $table->unsignedBigInteger('bill_id');
            $table->date('item_date');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bill_id')
                ->references('id')->on('bills')
                ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('billable_items');
    }
}
