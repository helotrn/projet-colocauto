<?php

use App\Models\ExpenseTag;
use Illuminate\Database\Seeder;

class ExpenseTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [
            [
                "id" => 1,
                "name" => "Emprunt",
                "slug" => "loan",
                "color" => "primary",
            ],
            [
                "id" => 2,
                "name" => "Carburant",
                "slug" => "fuel",
                "color" => "success",
            ],
            [
                "id" => 3,
                "name" => "Dépense partagée",
                "slug" => "shared",
                "color" => "success",
            ],
            [
                "id" => 4,
                "name" => "Provisions",
                "slug" => "funds",
                "color" => "primary",
            ],
        ];

        foreach ($tags as $tag) {
            if (!ExpenseTag::where("id", $tag["id"])->exists()) {
                ExpenseTag::create($tag);
            } else {
                ExpenseTag::where("id", $tag["id"])->update($tag);
            }
        }

        \DB::statement(
            "SELECT setval('expense_tags_id_seq'::regclass, (SELECT MAX(id) FROM expense_tags) + 1)"
        );
    }
}
