<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHandoversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('handovers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->dateTimeTz('executed_at');
            $table->enum('status', ['in_process', 'canceled', 'completed']);
            $table->unsignedBigInteger('loan_id');

            // Takeover-specific fields
            $table->string('mileage_end');
            $table->string('fuel_end');
            $table->text('comments_by_borrower')->nullable();
            $table->text('comments_by_owner')->nullable();
            $table->decimal('purchases_amount', 8, 2);
            $table->dateTimeTz('contested_at')->nullable();
            $table->text('comments_on_contestation')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loan_id')
                ->references('id')->on('loans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('handovers');
    }
}
