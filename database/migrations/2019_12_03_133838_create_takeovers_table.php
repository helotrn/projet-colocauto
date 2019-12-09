<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTakeoversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('takeovers', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Action fields
            $table->dateTimeTz('executed_at');
            $table->enum('status', ['in_process', 'canceled', 'completed']);

            // Takeover-specific fields
            $table->string('mileage_beginning');
            $table->string('fuel_beginning');
            $table->text('comments_on_vehicle')->nullable();
            $table->date('contested_at')->nullable();
            $table->text('comments_on_contestation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('takeovers');
    }
}
