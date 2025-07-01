<?php

namespace Tests\Integration;

use App\Models\Bike;
use App\Models\Car;
use App\Models\Report;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class ReportTest extends TestCase
{
    private static $getReportResponseStructure = [
        "id",
        "location",
        "loanable_id",
        "details",
        "pictures",
        "created_at",
        "updated_at",
    ];

    public function testCreateReport()
    {
        $image1 = json_decode($this->json("POST", "/api/v1/images", [
            "report_picture" => UploadedFile::fake()->image('front1.png'),
            "field" => "report_picture",
        ])->getContent());
        $image2 = json_decode($this->json("POST", "/api/v1/images", [
            "report_picture" => UploadedFile::fake()->image('front2.png'),
            "field" => "report_picture",
        ])->getContent());

        $car = factory(Car::class)->create();
        $data = [
            "location" => "front",
            "details" => $this->faker->paragraph,
            "loanable_id" => $car->id,
            "pictures" => [$image1,$image2],
        ];

        $response = $this->json("POST", "/api/v1/reports?fields=*,pictures.*", $data);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(static::$getReportResponseStructure);

        $pic_filenames = array_map(fn($pic) => $pic->filename, json_decode($response->getContent())->pictures);
        $this->assertContains('front1.png', $pic_filenames);
        $this->assertContains('front2.png', $pic_filenames);
    }

    public function testUpdateReport()
    {
        $image1 = json_decode($this->json("POST", "/api/v1/images", [
            "report_picture" => UploadedFile::fake()->image('front1.png'),
            "field" => "report_picture",
        ])->getContent());
        $image2 = json_decode($this->json("POST", "/api/v1/images", [
            "report_picture" => UploadedFile::fake()->image('front2.png'),
            "field" => "report_picture",
        ])->getContent());

        $car = factory(Car::class)->create();
        $data = [
            "location" => "front",
            "details" => $this->faker->paragraph,
            "loanable_id" => $car->id,
            "pictures" => [$image1],
        ];

        $report = json_decode($this->json("POST", "/api/v1/reports?fields=*,pictures.*", $data)->getContent());
        $response = $this->json("PUT", "/api/v1/reports/{$report->id}?fields=*,pictures.*", [
            "pictures" => [$image2],
        ]);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(static::$getReportResponseStructure)
            ->assertJson([
                "pictures" => [
                    ["filename" => "front2.png"]
                ]
            ])
            ->assertJsonMissing([
                "pictures" => [
                    ["filename" => "front1.png"]
                ]
            ]);
    }

    public function testUserCannotAccessReport()
    {
        $report = factory(Report::class)->create();
        $user = factory(User::class)->create();

        $this->actAs($user);
        $response = $this->json("GET", "/api/v1/reports/{$report->id}?fields=*,pictures.*");
         $response->assertStatus(404);
    }
}
