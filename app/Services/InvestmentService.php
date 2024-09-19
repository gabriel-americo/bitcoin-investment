<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class InvestmentService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function getInvestmentPosition()
    {
        $btcPurchases = Transaction::where('user_id', $this->user->id)
        ->whereIn('type', ['purchase', 'sale'])
        ->get();

        if ($btcPurchases->isEmpty()) {
            return ['status' => 'error', 'message' => 'Nenhum investimento encontrado.'];
        }

        $currentBitcoinPrice = $this->getBitcoinPrice('last');
        if (!$currentBitcoinPrice) {
            return ['status' => 'error', 'message' => 'Erro ao obter a cotação atual do Bitcoin.'];
        }

        $investmentPosition = $btcPurchases->map(function ($purchase) use ($currentBitcoinPrice) {
            if ($purchase->btc_price_at_time && $purchase->btc_price_at_time > 0) {
                $percentChange = (($currentBitcoinPrice - $purchase->btc_price_at_time) / $purchase->btc_price_at_time) * 100;
            } else {
                $percentChange = 0;
            }

            $currentInvestmentValue = $purchase->btc_amount * $currentBitcoinPrice;

            return [
                'data_compra' => $purchase->created_at->format('d/m/Y H:i'),
                'valor_investido' => number_format($purchase->amount, 2, ',', '.'),
                'btc_price_at_time' => number_format($purchase->btc_price_at_time, 2, ',', '.'),
                'percentual_variacao' => number_format($percentChange, 2, ',', '.') . '%',
                'valor_bruto_atual' => 'R$ ' . number_format($currentInvestmentValue, 2, ',', '.'),
            ];
        });

        return [
            'status' => 'success',
            'investments' => $investmentPosition,
            'btc_preco_atual' => 'R$ ' . number_format($currentBitcoinPrice, 2, ',', '.'),
        ];
    }

    protected function getBitcoinPrice(string $priceType)
    {
        try {
            $response = Http::get('https://www.mercadobitcoin.net/api/BTC/ticker/');
            return $response->successful() ? $response->json()['ticker'][$priceType] : false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
