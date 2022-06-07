<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddElectricToPricingCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE cars DROP CONSTRAINT cars_pricing_category_check"
        );

        $types = ["small", "large", "electric"];
        $result = join(
            ", ",
            array_map(function ($value) {
                return sprintf("'%s'::character varying", $value);
            }, $types)
        );

        DB::statement(
            "ALTER TABLE cars ADD CONSTRAINT cars_pricing_category_check CHECK (pricing_category::text = ANY (ARRAY[$result]::text[]))"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE cars DROP CONSTRAINT cars_pricing_category_check"
        );

        $types = ["small", "large"];
        $result = join(
            ", ",
            array_map(function ($value) {
                return sprintf("'%s'::character varying", $value);
            }, $types)
        );

        DB::statement(
            "ALTER TABLE cars ADD CONSTRAINT cars_pricing_category_check CHECK (pricing_category::text = ANY (ARRAY[$result]::text[]))"
        );
    }
}
