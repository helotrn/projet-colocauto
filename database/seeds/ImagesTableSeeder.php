<?php

use App\Models\Image;
use Illuminate\Database\Seeder;
use Intervention\Image\ImageManager as ImageManager;

class ImagesTableSeeder extends Seeder
{
    public function run()
    {
        $images = [
            [
                "id" => 1,
                "imageable_type" => "App\Models\User",
                // Joseph
                "imageable_id" => 5,
                "path" => "/seeds/4",
                "filename" => "avatarM1.png",
                "original_filename" => "avatarM1.png",
                "field" => "avatar",
                "width" => "200",
                "height" => "200",
                "filesize" => 18929,
            ],
            [
                "id" => 2,
                "imageable_type" => "App\Models\User",
                // Pauline
                "imageable_id" => 2,
                "path" => "/seeds/5",
                "filename" => "avatarF2.png",
                "original_filename" => "avatarF2.png",
                "field" => "avatar",
                "width" => "200",
                "height" => "200",
                "filesize" => 19043,
            ],
            [
                "id" => 3,
                "imageable_type" => "App\Models\User",
                // Mathieu
                "imageable_id" => 8,
                "path" => "/seeds/6",
                "filename" => "avatarM3.png",
                "original_filename" => "avatarM3.png",
                "field" => "avatar",
                "width" => "200",
                "height" => "200",
                "filesize" => 14092,
            ],
            [
                "id" => 4,
                "imageable_type" => "App\Models\User",
                // Isabelle
                "imageable_id" => 3,
                "path" => "/seeds/7",
                "filename" => "avatarF4.png",
                "original_filename" => "avatarF4.png",
                "field" => "avatar",
                "width" => "200",
                "height" => "200",
                "filesize" => 12512,
            ],
        ];

        foreach ($images as $image) {
            if (!Image::where("id", $image["id"])->exists()) {
                $manager = new ImageManager(["driver" => "imagick"]);
                $imageData = $manager->make('public' . $image["path"] . DIRECTORY_SEPARATOR . $image["filename"]);
                Image::store('storage' . $image["path"] . DIRECTORY_SEPARATOR . $image["filename"], $imageData);
                Image::store('storage' . $image["path"] . DIRECTORY_SEPARATOR . 'thumbnail_' . $image["filename"], $imageData);
                Image::create($image);
            } else {
                Image::where("id", $image["id"])->update($image);
            }
        }

        \DB::statement(
            "SELECT setval('images_id_seq'::regclass, (SELECT MAX(id) FROM images) + 1)"
        );
    }
}
