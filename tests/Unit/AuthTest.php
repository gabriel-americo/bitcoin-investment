<?php

namespace Tests\Unit;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_it_registers_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Novo usuario',
            'email' => 'usuario@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'message',
                'data' => ['name', 'email']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'usuario@example.com',
        ]);
    }

    public function test_it_logs_in_api()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'usuario@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['user' => ['name', 'email'], 'token']
            ]);

        $this->assertAuthenticated();
    }

    public function test_it_logs_out_api()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout')
            ->assertStatus(200);
    }
}
