<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalPlatformTipToLoans extends Migration
{
    public function up() {
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('final_platform_tip', 8, 2)->nullable();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('final_platform_tip', 8, 2)->default(null)->change();
        });
    }

    public function down() {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('final_platform_tip');
        });
    }
}
