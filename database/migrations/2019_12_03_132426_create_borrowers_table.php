<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBorrowersTable extends Migration
{
    public function up() {
        Schema::create('borrowers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('drivers_license_number')->nullable();
            $table->boolean('has_been_sued_last_ten_years')->default(false);
            $table->string('noke_id')->nullable();
            $table->date('submitted_at');
            $table->date('approved_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('borrowers');
    }
}
