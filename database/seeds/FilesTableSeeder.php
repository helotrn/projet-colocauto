<?php

use App\Models\File;
use Illuminate\Database\Seeder;

class FilesTableSeeder extends Seeder
{
    public function run()
    {
        $files = [
            [
                "id" => 1,
                "fileable_type" => "App\Models\Pivots\CommunityUser",
                "fileable_id" => 6, // Emprunteur Petite Patrie
                "path" => "/seeds/1",
                "filename" => "preuve_de_résidence.png",
                "original_filename" => "preuve de résidence.png",
                "field" => "proof",
                "filesize" => 3817,
            ],
        ];

        foreach ($files as $file) {
            if (!File::where("id", $file["id"])->exists()) {
                File::create($file);
            } else {
                File::where("id", $file["id"])->update($file);
            }
        }

        \DB::statement(
            "SELECT setval('files_id_seq'::regclass, (SELECT MAX(id) FROM images) + 1)"
        );
    }
}
