<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WalletTest extends TestCase
{
    public function test_user_can_view_wallet_balance()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $token = $response->json('token');

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_reais' => 1000.00,
            'balance_btc' => 0.00000000,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/wallet');

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'balance_reais' => 'R$ 1.000,00',
                'balance_btc' => '0.00000000',
            ]
        ]);
    }
}
