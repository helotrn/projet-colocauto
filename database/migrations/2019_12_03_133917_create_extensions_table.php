<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionsTable extends Migration
{
    public function up() {
        Schema::create('extensions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->dateTimeTz('executed_at');
            $table->enum('status', ['in_process', 'canceled', 'completed']);
            $table->unsignedBigInteger('loan_id');

            // Extension-specific fields
            $table->unsignedInteger('new_duration'); // in minutes
            $table->text('comments_on_extension');
            $table->dateTimeTz('contested_at')->nullable();
            $table->text('comments_on_contestation')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loan_id')
                ->references('id')->on('loans')
                ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('extensions');
    }
}
