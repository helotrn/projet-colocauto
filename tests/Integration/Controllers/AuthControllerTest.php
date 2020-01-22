<?php

namespace Tests\Integration\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function testBasicLogin() {
        $this->createTestUser();

        $data = [
            'email' => 'emile@molotov.ca',
            'password' => 'molotov'
        ];
        $response = $this->json('POST', '/api/v1/auth/login', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token'
            ]);
    }

    public function testLoginWithNonExistentUser() {
        $data = [
            'email' => 'asdf@molotov.ca',
            'password' => 'molotov'
        ];
        $response = $this->json('POST', '/api/v1/auth/login', $data);

        $response->assertStatus(401)
            ->assertJsonStructure(TestCase::$validationErrorStructure);
    }

    public function testLoginWithInvalidData() {
        $this->createTestUser();

        $data = [
            'email' => 'emile@molotov.ca',
            'password' => 'laskjdflaksd'
        ];
        $response = $this->json('POST', '/api/v1/auth/login', $data);

        $response->assertStatus(401)
            ->assertJsonStructure(TestCase::$validationErrorStructure);
    }

    public function testLoginWithInvalidJson() {
        $this->createTestUser();

        $data = [
            'email' => 'emile@molotov.ca',
            'password' => 'molotov',
            'what' => 'asdfsdaf',
        ];
        $response = $this->json('POST', '/api/v1/auth/login', $data);

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

    public function testRegisterWithInvalidData() {
        $response = $this->json('POST', '/api/v1/auth/register', [
            'machin' => 'chouette',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(TestCase::$validationErrorStructure);
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

    private function createTestUser() {
        $user = new User();
        $user->email = 'emile@molotov.ca';
        $user->password = Hash::make('molotov');
        $user->save();

        return $user;
    }
}
