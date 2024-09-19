<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\BtcPurchaseMail;
use App\Mail\BtcSellMail;
use App\Models\BtcQuote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BitcoinService
{
    protected $user;
    protected $apiUrl = 'https://www.mercadobitcoin.net/api/BTC/ticker/';

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function buyBitcoin(float $amount)
    {
        $wallet = Wallet::where('user_id', $this->user->id)->firstOrFail();

        if ($wallet->balance_reais < $amount) {
            return ['status' => 'error', 'message' => 'Saldo insuficiente!'];
        }

        $bitcoinPrice = $this->getBitcoinPrice('sell');

        $btcAmount = $amount / $bitcoinPrice;

        $transaction = Transaction::create([
            'amount' => $amount,
            'btc_amount' => $btcAmount,
            'btc_price_at_time' => $bitcoinPrice,
            'type' => 'purchase',
            'status' => 'completed',
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
        ]);

        $wallet->balance_reais -= $amount;
        $wallet->balance_btc += $btcAmount;
        $wallet->save();

        $formattedAmount = 'R$ ' . number_format($amount, 2, ',', '.');

        Mail::to($this->user->email)->send(new BtcPurchaseMail($this->user, $formattedAmount, $btcAmount));

        return [
            'status' => 'success',
            'transaction' => $transaction,
            'bitcoin_price' => $bitcoinPrice
        ];
    }

    public function sellBitcoin($btcAmount)
    {
        $wallet = Wallet::where('user_id', $this->user->id)->firstOrFail();

        if ($wallet->balance_btc < $btcAmount) {
            throw new \Exception('Saldo insuficiente de BTC!');
        }

        $bitcoinPrice = $this->getBitcoinPrice('buy');

        $btcPurchases = Transaction::where('user_id', $this->user->id)
        ->where('type', 'purchase')
        ->where('btc_amount', '>', 0)
        ->orderBy('created_at', 'asc')
        ->get();

        $btcAmountToSell = $btcAmount;
        $totalAmountSoldInReais = 0;
        $transactions = [];

        foreach ($btcPurchases as $purchase) {
            if ($btcAmountToSell <= 0) {
                break;
            }
    
            if ($purchase->btc_amount > 0) {
                if ($purchase->btc_amount >= $btcAmountToSell) {
                    $amountSoldInReais = $btcAmountToSell * $bitcoinPrice;
                    $totalAmountSoldInReais += $amountSoldInReais;
    
                    $purchase->btc_amount -= $btcAmountToSell;
                    $purchase->save();
    
                    $transaction = Transaction::create([
                        'amount' => $amountSoldInReais,
                        'btc_amount' => $btcAmountToSell,
                        'btc_price_at_time' => $bitcoinPrice,
                        'type' => 'sale',
                        'status' => 'completed',
                        'user_id' => $this->user->id,
                        'wallet_id' => $wallet->id,
                    ]);
    
                    $transactions[] = $transaction;
                    $btcAmountToSell = 0;
                } else {
                    $amountSoldInReais = $purchase->btc_amount * $bitcoinPrice;
                    $totalAmountSoldInReais += $amountSoldInReais;
    
                    $btcAmountSold = $purchase->btc_amount;
                    $purchase->btc_amount = 0;
                    $purchase->save();
    
                    $transaction = Transaction::create([
                        'amount' => $amountSoldInReais,
                        'btc_amount' => $btcAmountSold,
                        'btc_price_at_time' => $bitcoinPrice,
                        'type' => 'sale',
                        'status' => 'completed',
                        'user_id' => $this->user->id,
                        'wallet_id' => $wallet->id,
                    ]);
    
                    $transactions[] = $transaction;
                    $btcAmountToSell -= $btcAmountSold;
                }
            }
        }

        $wallet->balance_reais += $totalAmountSoldInReais;
        $wallet->balance_btc -= $btcAmount;
        $wallet->save();

        $formattedAmount = 'R$ ' . number_format($totalAmountSoldInReais, 2, ',', '.');

        Mail::to($this->user->email)->send(new BtcSellMail($this->user, $formattedAmount, $btcAmount));

        return [
            'transactions' => $transactions,
            'total_sold_reais' => $totalAmountSoldInReais,
            'current_bitcoin_price' => $bitcoinPrice
        ];
    }

    public function getBitcoinPriceCurrent()
    {
        return Cache::remember('bitcoin_price', 60, function () {
            $response = Http::get($this->apiUrl);
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'buy' => $data['ticker']['buy'],
                    'sell' => $data['ticker']['sell'],
                    'last' => $data['ticker']['last'],
                ];
            }
            throw new \Exception('Erro ao buscar a cotação do Bitcoin.');
        });
    }

    public function getBitcoinPrice(string $priceType)
    {
        try {
            $response = Http::get($this->apiUrl);
            if ($response->successful()) {
                return $response->json()['ticker'][$priceType];
            }
            throw new \Exception('Erro ao obter a cotação do Bitcoin.');
        } catch (\Exception $e) {
            throw new \Exception('Erro ao obter a cotação do Bitcoin: ' . $e->getMessage());
        }
    }

    public function getBitcoinPriceHistory()
    {
        $startDate = Carbon::now()->subDay();

        return BtcQuote::where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at', 'asc')
            ->get(['buy_price', 'sell_price', 'recorded_at']);
    }
}
