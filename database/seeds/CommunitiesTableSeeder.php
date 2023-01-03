<?php

use App\Models\Community;
use Illuminate\Database\Seeder;

class CommunitiesTableSeeder extends Seeder
{
    public function run()
    {
        $communities = [
            [
                "id" => 1,
                "name" => "POC - Asso Eolien",
                "description" =>
                    "",
                "created_at" => "2020-05-14 17:37:53",
                "updated_at" => "2021-03-01 18:47:22",
                "type" => "private",
            ],
            [
                "id" => 2,
                "name" => "POC - Groupe Citoyen",
                "description" => "",
                "created_at" => "2020-05-14 17:37:53",
                "updated_at" => "2021-09-30 16:14:30",
                "type" => "private",
            ],
        ];

        foreach ($communities as $community) {
            if (!Community::where("id", $community["id"])->exists()) {
                Community::create($community);
            } else {
                Community::where("id", $community["id"])->update($community);
            }
        }

        \DB::statement(
            "SELECT setval('communities_id_seq'::regclass, (SELECT MAX(id) FROM communities) + 1)"
        );
    }
}
