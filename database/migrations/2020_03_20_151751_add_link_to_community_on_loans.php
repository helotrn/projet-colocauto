<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkToCommunityOnLoans extends Migration
{
    public function up()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->unsignedBigInteger("community_id")->nullable();

            $table
                ->foreign("community_id")
                ->references("id")
                ->on("communities")
                ->onDelete("cascade");
        });
    }

    public function down()
    {
        Schema::table("loans", function (Blueprint $table) {
            $table->dropColumn("community_id");
        });
    }
}
