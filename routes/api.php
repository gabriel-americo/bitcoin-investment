<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\BitcoinController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth:sanctum")->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login')->withoutMiddleware('auth:sanctum');
        Route::post('register', 'register')->withoutMiddleware('auth:sanctum');
        Route::post('logout', 'logout');
    });

    Route::post('/transaction/deposit', [TransactionController::class, 'deposit']);
    Route::post('/transaction/withdrawal', [TransactionController::class, 'withdrawal']);
    Route::get('/transaction/extract', [TransactionController::class, 'extract']);
    Route::get('/transaction/volume', [TransactionController::class, 'bitcoinVolume']);

    Route::get('/wallet', [WalletController::class, 'showBalance']);

    Route::get('/investment/position', [InvestmentController::class, 'position']);

    Route::get('/bitcoin/price', [BitcoinController::class, 'getCurrentPrice']);
    Route::post('/bitcoin/purchase', [BitcoinController::class, 'buyBitcoin']);
    Route::post('/bitcoin/sell', [BitcoinController::class, 'sellBitcoin']);
    Route::get('/bitcoin/history', [BitcoinController::class, 'getBitcoinPriceHistory']);
});
