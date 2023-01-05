<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_tags', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("slug");
            $table->string("color")->default('primary');
            $table->timestamps();
        });
        Schema::table("expenses", function (Blueprint $table) {
            $table->unsignedBigInteger("expense_tag_id")->nullable();
            $table
                ->foreign("expense_tag_id")
                ->nullable()
                ->references("id")
                ->on("expense_tags")
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("expenses", function (Blueprint $table) {
            $table->dropForeign("expenses_expense_tag_id_foreign");
            $table->dropColumn("expense_tag_id");
        });
        Schema::dropIfExists('expense_tags');
    }
}
