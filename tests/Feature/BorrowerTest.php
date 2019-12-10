<?php

namespace Tests\Feature;

use App\Borrower;
use Tests\TestCase;

class BorrowerTest extends TestCase
{
    public function testCreateBorrowers() {
        $data = [
            'drivers_license_number' => $this->faker->numberBetween($min = 1111111111, $max = 999999999),
            'has_been_sued_last_ten_years' => $this->faker->boolean,
            'noke_id' => $this->faker->numberBetween($min = 000000000, $max = 999999999),
        ];
        $this->post(route('borrowers.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateBorrowers() {
        $post = factory(Borrower::class)->create();
        $data = [
            'drivers_license_number' => $this->faker->numberBetween($min = 1111111111, $max = 999999999),
        ];
        $this->put(route('borrowers.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowBorrowers() {
        $post = factory(Borrower::class)->create();
        $this->get(route('borrowers.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteBorrowers() {
        $post = factory(Borrower::class)->create();
        $this->delete(route('borrowers.delete', $post->id))
            ->assertStatus(204);
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
        $this->get(route('borrowers'))
            ->assertStatus(200)
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
