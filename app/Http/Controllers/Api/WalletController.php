<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function showBalance()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getUserWallet($user->id);

        return new WalletResource($wallet);
    }
}
