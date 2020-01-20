<?php

namespace Tests\Feature;

use App\Models\Loan;
use Tests\TestCase;

class LoanTest extends TestCase
{
    public function testCreateLoans() {
        $this->markTestIncomplete();
        $data = [
            'departure_at' => $this->faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
            'duration' => $this->faker->randomNumber($nbDigits = null, $strict = false),
        ];

        $response = $this->json('POST', route('loans.create'), $data);

        $response->assertStatus(201)->assertJson($data);
    }

    public function testShowLoans() {
        $this->markTestIncomplete();
        $post = factory(Loan::class)->create();

        $response = $this->json('GET', route('loans.retrieve', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testUpdateLoans() {
        $this->markTestIncomplete();
        $post = factory(Loan::class)->create();
        $data = [
            'duration' => $this->faker->randomNumber($nbDigits = null, $strict = false),
        ];

        $response = $this->json('PUT', route('loans.update', $post->id), $data);

        $response->assertStatus(200)->assertJson($data);
    }

    public function testDeleteLoans() {
        $this->markTestIncomplete();
        $post = factory(Loan::class)->create();

        $response = $this->json('DELETE', route('loans.delete', $post->id), $data);

        $response->assertStatus(204)->assertJson($data);
    }

    public function testListLoans() {
        $this->markTestIncomplete();
        $loans = factory(Loan::class, 2)->create()->map(function ($post) {
            return $post->only([
                'id',
                'departure_at',
                'duration'
            ]);
        });

        $response = $this->json('GET', route('loans.index'));

        $response->assertStatus(200)
                ->assertJson($loans->toArray())
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'departure_at',
                        'duration',
                    ],
                ]);
    }
}
