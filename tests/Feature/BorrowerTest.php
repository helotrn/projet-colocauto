<?php

namespace Tests\Feature;

use App\Models\Borrower;
use Tests\TestCase;

class BorrowerTest extends TestCase
{
    public function testCreateBorrowers() {
        $data = [
            'drivers_license_number' => $this->faker->numberBetween($min = 1111111111, $max = 999999999),
            'has_been_sued_last_ten_years' => $this->faker->boolean,
            'noke_id' => $this->faker->numberBetween($min = 000000000, $max = 999999999),
        ];

        $response = $this->json('POST', route('borrowers.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowBorrowers() {
        $post = factory(Borrower::class)->create();
        
        $response = $this->json('GET', route('borrowers.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateBorrowers() {
        $post = factory(Borrower::class)->create();
        $data = [
            'drivers_license_number' => $this->faker->numberBetween($min = 1111111111, $max = 999999999),
        ];
        
        $response = $this->json('PUT', route('borrowers.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteBorrowers() {
        $post = factory(Borrower::class)->create();
        
        $response = $this->json('DELETE', route('borrowers.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListBorrowers() {
        $borrowers = factory(Borrower::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'drivers_license_number',
                'has_been_sued_last_ten_years',
                'noke_id',
            ]);
        });

        $response = $this->json('GET', route('borrowers.index'));

        $response->assertStatus(200)
                ->assertJson($borrowers->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'drivers_license_number',
                        'has_been_sued_last_ten_years',
                        'noke_id',
                    ],
                ]);
    }
}
