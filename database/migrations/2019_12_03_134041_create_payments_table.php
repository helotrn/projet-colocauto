<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    public function up() {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->dateTimeTz('executed_at');
            $table->enum('status', ['in_process', 'canceled', 'completed']);
            $table->unsignedBigInteger('loan_id');

            $table->unsignedBigInteger('bill_item_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loan_id')
                ->references('id')->on('loans')
                ->onDelete('cascade');
            $table->foreign('bill_item_id')
                ->references('id')->on('bill_items')
                ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('payments');
    }
}
