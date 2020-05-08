<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxableToBillItems extends Migration
{
    public function up() {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->decimal('taxes_tps', 8, 2)->default(0);
            $table->decimal('taxes_tvq', 8, 2)->default(0);
        });

        Schema::table('bill_items', function (Blueprint $table) {
            $table->decimal('taxes_tps', 8, 2)->default(null)->change();
            $table->decimal('taxes_tvq', 8, 2)->default(null)->change();
        });
    }

    public function down() {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropColumn('taxes_tps');
            $table->dropColumn('taxes_tvq');
        });
    }
}
