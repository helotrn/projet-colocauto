<?php

namespace Tests\Feature;

use App\Loan;
use Tests\TestCase;

class LoanTest extends TestCase
{
    public function testCreateLoans() {
        $data = [
            'duration' => $this->faker->randomNumber($nbDigits = null, $strict = false),
        ];
        $this->post(route('loans.store'), $data)
            ->assertStatus(201)
            ->assertJson($data);
    }

    public function testUpdateLoans() {
        $post = factory(Loan::class)->create();
        $data = [
            'duration' => $this->faker->randomNumber($nbDigits = null, $strict = false),
        ];
        $this->put(route('loans.update', $post->id), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }

    public function testShowLoans() {
        $post = factory(Loan::class)->create();
        $this->get(route('loans.show', $post->id))
            ->assertStatus(200);
    }

    public function testDeleteLoans() {
        $post = factory(Loan::class)->create();
        $this->delete(route('loans.delete', $post->id))
            ->assertStatus(204);
    }

    public function testListLoans() {
        $loans = factory(Loan::class, 2)->create()->map(function ($post) {
            return $post->only(['id', 'duration']);
        });
        $this->get(route('loans'))
            ->assertStatus(200)
            ->assertJson($loans->toArray())
            ->assertJsonStructure([
                '*' => [ 'id', 'duration' ],
            ]);
    }
}
