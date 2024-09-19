<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $transaction = $this->transactionService->deposit($request->amount);
            return response()->json([
                'message' => 'Depósito realizado com sucesso!',
                'transaction' => new TransactionResource($transaction)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function withdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $transaction = $this->transactionService->withdrawal($request->amount);
            return response()->json(['message' => 'Retirada realizada com sucesso!', 'transaction' => $transaction], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function extract(Request $request)
    {
        try {
            $result = $this->transactionService->extract($request->input('start_date'), $request->input('end_date'));

            if (empty($result['transactions'])) {
                return response()->json([
                    'message' => $result['message'],
                    'start_date' => $result['start_date'],
                    'end_date' => $result['end_date'],
                ], 200);
            }

            return response()->json([
                'message' => 'Extrato recuperado com sucesso!',
                'transactions' => $result['transactions'],
                'start_date' => $result['start_date'],
                'end_date' => $result['end_date'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function bitcoinVolume()
    {
        try {
            $volume = $this->transactionService->bitcoinVolume();

            return response()->json([
                'message' => 'Volume de Bitcoin no dia corrente recuperado com sucesso!',
                'btc_purchased' => $volume['btc_purchased'],
                'btc_sold' => $volume['btc_sold'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
