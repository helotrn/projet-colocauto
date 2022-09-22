<?php

use App\Models\File;
use Faker\Generator as Faker;

$factory->define(File::class, function (Faker $faker) {
    $filename = $faker->word . "." . $faker->fileExtension;
    return [
        "path" => $faker->word,
        "filename" => $filename,
        "original_filename" => $filename,
        "filesize" => $faker->randomNumber(),
    ];
});
