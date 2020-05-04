<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPricingCategoryToCars extends Migration
{
    public function up() {
        Schema::table('cars', function (Blueprint $table) {
            $table->enum('pricing_category', ['small', 'large'])
                ->default('small');
        });
    }

    public function down() {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('pricing_category');
        });
    }
}
