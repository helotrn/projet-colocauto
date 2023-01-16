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
                "id" => 5,
                "imageable_type" => "App\Models\Car",
                "imageable_id" => 1004,
                "path" => "/seeds/8",
                "filename" => "Renault de Marcelle.jpg",
                "original_filename" => "Renault de Marcelle.jpg",
                "field" => "image",
                "width" => "1024",
                "height" => "768",
                "filesize" => 147245,
            ],
            [
                "id" => 6,
                "imageable_type" => "App\Models\Car",
                "imageable_id" => 1005,
                "path" => "/seeds/9",
                "filename" => "Peugeot de Christiane.jpg",
                "original_filename" => "Peugeot de Christiane.jpg",
                "field" => "image",
                "width" => "280",
                "height" => "183",
                "filesize" => 15939,
            ],
            [
                "id" => 7,
                "imageable_type" => "App\Models\User",
                // Pauline
                "imageable_id" => 2,
                "path" => "/seeds/10",
                "filename" => "Pauline Bonneau.jpg",
                "original_filename" => "Pauline Bonneau.jpg",
                "field" => "avatar",
                "width" => "384",
                "height" => "254",
                "filesize" => 25739,
            ],
            [
                "id" => 8,
                "imageable_type" => "App\Models\User",
                // Isabelle
                "imageable_id" => 3,
                "path" => "/seeds/11",
                "filename" => "Isabelle Deschamps.jpg",
                "original_filename" => "Isabelle Deschamps.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "511",
                "filesize" => 10000,
            ],
            [
                "id" => 9,
                "imageable_type" => "App\Models\User",
                // Stéphanie
                "imageable_id" => 4,
                "path" => "/seeds/12",
                "filename" => "Stéphanie Pineau.jpg",
                "original_filename" => "Stéphanie Pineau.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "741",
                "filesize" => 10000,
            ],
            [
                "id" => 10,
                "imageable_type" => "App\Models\User",
                // Joseph
                "imageable_id" => 5,
                "path" => "/seeds/13",
                "filename" => "Joseph Schmitt.jpg",
                "original_filename" => "Joseph Schmitt.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "333",
                "filesize" => 10000,
            ],
            [
                "id" => 11,
                "imageable_type" => "App\Models\User",
                // Suzanne
                "imageable_id" => 6,
                "path" => "/seeds/14",
                "filename" => "Suzanne Chauvet.jpg",
                "original_filename" => "Suzanne Chauvet.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "333",
                "filesize" => 10000,
            ],
            [
                "id" => 12,
                "imageable_type" => "App\Models\User",
                // Camille
                "imageable_id" => 7,
                "path" => "/seeds/15",
                "filename" => "Camille Dos Santos.jpg",
                "original_filename" => "Camille Dos Santos.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "333",
                "filesize" => 10000,
            ],
            [
                "id" => 13,
                "imageable_type" => "App\Models\User",
                // Marcelle
                "imageable_id" => 10,
                "path" => "/seeds/16",
                "filename" => "Marcelle Nicolas.jpg",
                "original_filename" => "Marcelle Nicolas.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "333",
                "filesize" => 10000,
            ],
            [
                "id" => 14,
                "imageable_type" => "App\Models\User",
                // Lucas
                "imageable_id" => 11,
                "path" => "/seeds/17",
                "filename" => "Lucas Ferrand.jpg",
                "original_filename" => "Lucas Ferrand.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "751",
                "filesize" => 10000,
            ],
            [
                "id" => 15,
                "imageable_type" => "App\Models\User",
                // Henriette
                "imageable_id" => 12,
                "path" => "/seeds/18",
                "filename" => "Henriette Schneider.jpg",
                "original_filename" => "Henriette Schneider.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "333",
                "filesize" => 10000,
            ],
            [
                "id" => 16,
                "imageable_type" => "App\Models\User",
                // Christiane
                "imageable_id" => 13,
                "path" => "/seeds/19",
                "filename" => "Christiane Girard.jpg",
                "original_filename" => "Christiane Girard.jpg",
                "field" => "avatar",
                "width" => "500",
                "height" => "749",
                "filesize" => 10000,
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
