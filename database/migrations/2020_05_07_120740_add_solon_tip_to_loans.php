<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSolonTipToLoans extends Migration
{
    public function up() {
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('platform_tip', 8, 2)->default(0);
            $table->decimal('final_insurance', 8, 2)->nullable();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('platform_tip', 8, 2)->default(null)->change();
        });
    }

    public function down() {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('final_insurance');
            $table->dropColumn('platform_tip');
        });
    }
}
