<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    public function test_deposit_endpoint_requires_authentication()
    {
        $response = $this->postJson('/api/transaction/deposit', [
            'amount' => 100.0
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_deposit()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $token = $response->json('token');

        $response = $this->postJson('/api/transaction/deposit', [
            'amount' => 1000.0
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'transaction' => [
                      'amount', 'btc_amount','type', 'status'   
                     ]
                 ]);

        $this->assertDatabaseHas('transactions', [
            'amount' => 1000.0,
            'btc_amount' => 0.00000000,
            'btc_price_at_time' => null,
            'type' => 'deposit',
            'status' => 'completed',
            'user_id' => $user->id
        ]);
    }
}
