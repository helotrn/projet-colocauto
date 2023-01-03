<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            
            $table->string("name");
            $table->decimal("amount", 8, 2);
            $table->enum("type", ["debit", "credit"]);
            $table->dateTimeTz("executed_at")->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger("user_id");
            $table
                ->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");
            // Cannot create foreign key to loanable since its more than one table
            $table->unsignedBigInteger("loanable_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
