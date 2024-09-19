<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BitcoinResource;
use App\Http\Resources\TransactionResource;
use App\Services\BitcoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitcoinController extends Controller
{
    public function __construct(
        protected BitcoinService $bitcoinService
    ) {}

    public function buyBitcoin(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $result = $this->bitcoinService->buyBitcoin($request->amount);

        if ($result['status'] == 'error') {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'message' => 'Compra realizada com sucesso!',
            'data' => new BitcoinResource($result),
        ], 201);
    }

    public function sellBitcoin(Request $request)
    {
        $request->validate([
            'btc_amount' => 'required|numeric|min:0.00000001',
        ]);

        $btcAmount = $request->input('btc_amount');

        try {
            $result = $this->bitcoinService->sellBitcoin($btcAmount);

            return response()->json([
                'message' => 'Venda realizada com sucesso!',
                'transactions' => TransactionResource::collection($result['transactions']),
                'total_sold_reais' => 'R$ ' . number_format($result['total_sold_reais'], 2, ',', '.'),
                'current_bitcoin_price' => 'R$ ' . number_format($result['current_bitcoin_price'], 2, ',', '.')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getCurrentPrice()
    {
        try {
            $priceData = $this->bitcoinService->getBitcoinPriceCurrent();
            if ($priceData === false) {
                return response()->json(['message' => 'Erro ao obter a cotação do Bitcoin.'], 500);
            }

            return response()->json([
                'message' => 'Cotação do Bitcoin obtida com sucesso.',
                'data' => $priceData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getBitcoinPriceHistory()
    {
        $history = $this->bitcoinService->getBitcoinPriceHistory();

        return response()->json([
            'message' => 'Histórico de preços do Bitcoin recuperado com sucesso!',
            'history' => $history
        ], 200);
    }
}
