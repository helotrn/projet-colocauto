<?php

use App\Models\File;
use Illuminate\Database\Seeder;

class FilesTableSeeder extends Seeder
{
    public function run()
    {
        $files = [];

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
