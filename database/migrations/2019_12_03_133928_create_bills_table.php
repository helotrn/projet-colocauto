<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsTable extends Migration
{
    public function up() {
        Schema::create('bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('period');
            $table->string('payment_method');
            $table->decimal('total', 8, 2);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_id')->nullable();

            $table->dateTimeTz('paid_at');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('payment_method_id')
                ->references('id')->on('payment_methods')
                ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('bills');
    }
}
