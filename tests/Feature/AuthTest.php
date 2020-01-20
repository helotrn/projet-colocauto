<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testRegisterWithInvalidData() {
        $response = $this->json('POST', '/api/v1/auth/register', [
            'machin' => 'chouette',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(TestCase::$validationErrorStructure);
    }

    public function testRegister() {
        $data = [
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'molotov'
        ];
        $response = $this->json('POST', '/api/v1/auth/register', $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token'
            ]);
    }

    public function testUpdateFromAuthEndpoint() {
        $response = $this->json('GET', '/api/v1/auth/user');
        $response->assertStatus(200)->assertJson([
            'last_name' => $this->user->last_name,
        ]);

        $data = [
            'last_name' => 'ceci est un test',
        ];
        $response = $this->json('PUT', '/api/v1/auth/user', $data);
        $response->assertStatus(200)->assertJson($data);

        $response = $this->json('GET', '/api/v1/auth/user');
        $response->assertStatus(200)->assertJson($data);
    }
}
