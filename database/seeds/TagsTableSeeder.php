<?php

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    public function run() {
        $tags = [
            [
                'id' => 1,
                'name' => 'Ambassadeur.rice de la communauté',
                'slug' => 'ambassador',
                'type' => 'tag',
            ],
            [
                'id' => 2,
                'name' => 'Pionnier.ère Locomotion',
                'slug' => 'early_adopter',
                'type' => 'tag',
            ],
        ];

        foreach ($tags as $tag) {
            if (!Tag::where('id', $tag['id'])->exists()) {
                Tag::create($tag);
            } else {
                Tag::where('id', $tag['id'])->update($tag);
            }
        }

        \DB::statement("SELECT setval('tags_id_seq'::regclass, (SELECT MAX(id) FROM tags) + 1)");
    }
}
