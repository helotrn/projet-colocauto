<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('extensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Action fields
            $table->dateTimeTz('executed_at');
            $table->enum('status', ['in_process', 'canceled', 'completed']);

            // Extension-specific fields
            $table->unsignedInteger('new_duration');//in minutes
            $table->text('comments_on_extension');
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
        Schema::dropIfExists('extensions');
    }
}
