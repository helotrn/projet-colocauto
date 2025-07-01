<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class ImageTest extends TestCase
{
    private static $getImageResponseStructure = [
        "id",
        "path",
        "original_filename",
        "filename",
        "width",
        "height",
        "field",
        "sizes",
        "url",
        "created_at",
        "updated_at",
    ];

    public function testCreateImage()
    {
        $data = [
            "avatar" => UploadedFile::fake()->image('avatar.png'),
            "field" => "avatar",
        ];
        $response = $this->json("POST", "/api/v1/images", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$getImageResponseStructure)
            ->assertJson([
                "filename" => "avatar.png"
            ]);
    }
}
