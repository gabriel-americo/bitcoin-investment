<?php

namespace App\Services;

use App\Models\Wallet;

class WalletService
{
    public function getUserWallet($userId)
    {
        return Wallet::where('user_id', $userId)->firstOrFail();
    }
}