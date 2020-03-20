<?php

use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ImagesTableSeeder extends Seeder
{
    public function run() {
        $images = [
            [
                'id' => 1,
                'imageable_type' => 'App\Models\Pivots\CommunityUser',
                'imageable_id' => 1,
                'path' => '/seeds/1',
                'filename' => 'preuve de résidence.png',
                'original_filename' => 'preuve de résidence.png',
                'field' => 'proof',
                'width' => '640',
                'height' => '400',
                'filesize' => 3817,
            ],
            [
                'id' => 2,
                'imageable_type' => 'App\Models\User',
                'imageable_id' => 2,
                'path' => '/seeds/2',
                'filename' => '2020-03-11-162228.jpg',
                'original_filename' => '2020-03-11-162228.jpg',
                'field' => 'avatar',
                'width' => '640',
                'height' => '480',
                'filesize' => 54941,
            ],
            [
                'id' => 3,
                'imageable_type' => 'App\Models\Bike',
                'imageable_id' => 1,
                'path' => '/seeds/3',
                'filename' => 'velo_tandem.jpg',
                'original_filename' => 'velo_tandem.jpg',
                'field' => 'image',
                'width' => '1000',
                'height' => '800',
                'filesize' => 104311,
            ],
        ];

        foreach ($images as $image) {
            if (!Image::where('id', $image{'id'})->exists()) {
                Image::create($image);
            } else {
                Image::where('id', $image['id'])->update($image);
            }
        }

        \DB::statement("SELECT setval('images_id_seq'::regclass, (SELECT MAX(id) FROM images) + 1)");
    }
}
